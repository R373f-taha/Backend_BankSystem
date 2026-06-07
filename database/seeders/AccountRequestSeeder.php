<?php

namespace Database\Seeders;

use App\Models\AccountRequest;
use Illuminate\Database\Seeder;

class AccountRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AccountRequest::factory()->count(30)->create();
    }
}