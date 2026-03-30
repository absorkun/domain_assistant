<?php

namespace Database\Factories;

use App\Models\HelpdeskMessage;
use App\Models\HelpdeskTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HelpdeskMessage>
 */
class HelpdeskMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'helpdesk_ticket_id' => HelpdeskTicket::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_staff' => false,
            'sent_at' => now(),
        ];
    }
}
