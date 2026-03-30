<?php

namespace Database\Factories;

use App\Models\HelpdeskTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HelpdeskTicket>
 */
class HelpdeskTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(4),
            'status' => 'open',
            'last_message_at' => now(),
        ];
    }
}
