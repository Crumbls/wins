<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Win;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Win>
 */
class WinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $recordedAt = now('UTC');

        return [
            'user_id' => User::factory(),
            'content' => fake()->sentence(),
            'created_at' => $recordedAt,
            'updated_at' => $recordedAt,
        ];
    }
}
