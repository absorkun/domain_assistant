<?php

namespace Database\Factories;

use App\Enums\HelpdeskStatus;
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
            'domain' => fake()->domainName(),
            'subject' => fake()->sentence(4),
            'status' => HelpdeskStatus::Open,
            'last_message_at' => now(),
        ];
    }
}
