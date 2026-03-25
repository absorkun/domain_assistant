<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DomainFromCSVSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('dns_server.csv');
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return;
        }

        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);

            return;
        }

        $existingUserIds = DB::table('users')->pluck('id')->all();
        $existingUserIds = array_fill_keys($existingUserIds, true);
        $missingUsers = [];
        $domains = [];
        $batchSize = 500;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);

            if ($data === false || empty($data['domain'])) {
                continue;
            }

            $userId = (int) $data['user_id'];

            if (! isset($existingUserIds[$userId])) {
                $existingUserIds[$userId] = true;
                $fullName = fake()->name();
                $missingUsers[] = [
                    'id' => $userId,
                    'name' => $this->makeUsername($fullName),
                    'full_name' => $fullName,
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $domains[] = [
                'id' => (int) $data['id'],
                'domain' => Str::lower($data['domain']),
                'user_id' => $userId,
                'name_srv' => $data['name_srv'] ?: null,
                'status' => $data['status'] ?: null,
                'dnssec' => filter_var($data['dnssec'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
                'tgl_reg' => $this->parseDate($data['tgl_reg']),
                'tgl_exp' => $this->parseDate($data['tgl_exp']),
                'tgl_upd' => $this->parseDate($data['tgl_upd']),
                'dns_a' => $data['dns_a'] ?: null,
                'website' => $data['website'] ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($domains) >= $batchSize) {
                $this->flushUsers($missingUsers);
                $this->flushDomains($domains);
            }
        }

        $this->flushUsers($missingUsers);
        $this->flushDomains($domains);

        fclose($handle);
    }

    private function flushUsers(array &$missingUsers): void
    {
        if ($missingUsers === []) {
            return;
        }

        DB::table('users')->insertOrIgnore($missingUsers);
        $missingUsers = [];
    }

    private function flushDomains(array &$domains): void
    {
        if ($domains === []) {
            return;
        }

        DB::table('domains')->insertOrIgnore($domains);
        $domains = [];
    }

    private function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }

    private function makeUsername(string $value): string
    {
        $username = Str::lower(Str::ascii($value));
        $username = preg_replace('/[^a-z0-9]+/', '_', $username);
        $username = trim($username, '_');

        if ($username === '') {
            $username = 'user';
        }

        return $username;
    }
}
