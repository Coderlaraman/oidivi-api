<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('Password1'),
            'is_active' => fake()->boolean(90), // 90% de probabilidad de que sea activo
            'accepted_terms' => true, // Simulando que aceptaron términos
            'profile_photo_url' => fake()->imageUrl(200, 200, 'people'), // Foto de perfil ficticia
            'profile_video_url' => fake()->url(), // URL ficticia para video de perfil
            'phone' => fake()->phoneNumber(), // Número de teléfono ficticio
            'phone_verified_at' => fake()->boolean(70)? now() : null, // 70% de probabilidad de que sea verificado
            'address' => fake()->address(),
            'zip_code' => fake()->postcode(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);
    }
}
