<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

#[Signature('role:add')]
#[Description('Command description')]
class AddRoleCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = (string) $this->ask('Name');
        if ($name) {
            $role = Role::create(['name' => $name]);

            if ($role) {
                $this->info("Role $role->name telah berhasil dibuat.");
            }
        }

        $this->info('Selesai');
    }
}
