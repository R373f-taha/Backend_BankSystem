<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employee = User::create([

            'name'  => 'Ahmed Employee',

            'email' => 'employee@bank.com',

            'role' => 'employee',

            'password' => Hash::make('password123'),

        ]);

    }
}
