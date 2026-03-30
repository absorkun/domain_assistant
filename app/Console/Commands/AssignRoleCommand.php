<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

#[Signature('role:assign')]
#[Description('Assign role for a user')]
class AssignRoleCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = (string) $this->ask('Masukkan username');

        $user = User::query()->where('name', $username)->firstOrFail();

        $roles = Role::pluck('name')->toArray();

        $this->info("Role yang tersedia " . implode(', ', $roles));
        
        $roleChoise = (string) $this->ask('Role yang ingin ditambahkan ke user');

        $user->assignRole([$roleChoise]);

        $this->info('Selesai');
    }
}
