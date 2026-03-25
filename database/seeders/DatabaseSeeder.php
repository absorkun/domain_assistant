<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'superadmin',
            'full_name' => 'Super Admin',
            'password' => bcrypt('password'),
        ]);

        if ($user) {
            $domain = Domain::firstOrCreate(['domain' => 'komdigi.go.id'], [
                'user_id' => $user->id,
                'name_srv' => 'ns1.domain.go.id, ns1.domain.go.id',
                'status' => 'active',
                'dnssec' => true,
                'tgl_reg' => today(),
                'tgl_exp' => today()->addYear(),
                'tgl_upd' => today(),
                'dns_a' => '127.0.0.1',
                'website' => null,
            ]);

            $this->command->info("Domain dengan nama $domain->domain telah dibuat oleh user $user->name");
        }

        $this->call(DomainFromCSVSeeder::class);
    }
}
