<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransferCompany;

class TransferCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'Al-Haram', 'is_active' => true],
            ['name' => 'Al-Fouad', 'is_active' => true],
        ];

        foreach ($companies as $company) {
            TransferCompany::updateOrCreate(['name' => $company['name']], $company);
        }
    }
}