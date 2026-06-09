<?php

namespace Database\Factories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'role'            => $this->faker->randomElement(['user', 'assistant']),
            'content'         => $this->faker->paragraph(3),
            'model_used'      => null,
            'tokens_used'     => $this->faker->numberBetween(10, 300),
        ];
    }

    public function user(): static
    {
        return $this->state(['role' => 'user', 'model_used' => null]);
    }

    public function assistant(string $model = 'openai/gpt-4o-mini'): static
    {
        return $this->state(['role' => 'assistant', 'model_used' => $model]);
    }
}
