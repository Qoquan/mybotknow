<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    public function definition(): array
    {
        $models = [
            'openai/gpt-4o-mini',
            'openai/gpt-4o',
            'anthropic/claude-3-haiku',
            'google/gemini-2.0-flash-001',
        ];

        return [
            'user_id'      => User::factory(),
            'title'        => $this->faker->sentence(4),
            'model'        => $this->faker->randomElement($models),
            'total_tokens' => $this->faker->numberBetween(100, 2000),
        ];
    }
}
