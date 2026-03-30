<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use function Laravel\Prompts\text;

#[Signature('user:add')]
#[Description('Add new user')]
class AddUserCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $full_name = (string) $this->ask('Full Name');
        $name = (string) $this->ask('Username');
        $email = text(
            label: 'Email',
            validate: fn($value) => $this->validatePrompt($value, 'required|string|email'),
        );
        $password = (string) $this->ask('Password');


        User::create([
            'full_name' => $full_name,
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->info('Selesai');
    }
}
