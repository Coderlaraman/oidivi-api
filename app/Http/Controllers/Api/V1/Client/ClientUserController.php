<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Http\Resources\ClientAuthResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClientUserController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => __('messages.user_data_retrieved'),
            'data' => new ClientAuthResource($user),
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|min:7|max:15',
                'address' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:10',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);

            $user->update(array_filter($validatedData));

            return response()->json([
                'success' => true,
                'message' => __('messages.profile_updated'),
                'data' => new ClientAuthResource($user),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('messages.validation_error'),
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => __('messages.general_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadProfilePhoto(Request $request)
    {
        return $this->handleFileUpload(
            $request,
            'profile_photo_url',
            'profile_photos',
            __('messages.profile_photo_updated')
        );
    }

    public function uploadProfileVideo(Request $request)
    {
        return $this->handleFileUpload(
            $request,
            'profile_video_url',
            'profile_videos',
            __('messages.profile_video_updated'),
            ['mimes:mp4,mov,avi,wmv', 'max:10240'] // Reglas específicas para videos
        );
    }

    private function handleFileUpload(Request $request, $fieldName, $directory, $successMessage, $rules = ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'])
    {
        try {
            $request->validate([
                $fieldName => 'required|' . implode('|', $rules),
            ]);

            $user = Auth::user();

            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs($directory, $filename, 'public');

                // Eliminar el archivo anterior si existe
                $oldFile = $user->{$fieldName};
                if ($oldFile) {
                    Storage::disk('public')->delete($oldFile);
                }

                $user->{$fieldName} = $filePath;
                $user->save();

                return response()->json([
                    'message' => $successMessage,
                    $fieldName => asset('storage/' . $filePath),
                ]);
            }

            return response()->json([
                'message' => __('messages.invalid_file_provided'),
            ], 400);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('messages.validation_error'),
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => __('messages.general_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
