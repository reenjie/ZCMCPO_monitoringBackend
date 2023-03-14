<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Roles;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fillroles = [
            'Admin',
            'MMS_User',
            'User',
            'Supervisor'
        ];
        foreach ($fillroles as $items) {
            Roles::create([
                'roles' => $items
            ]);
        }
    }
}
