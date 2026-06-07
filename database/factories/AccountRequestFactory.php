<?php

namespace Database\Factories;

use App\Models\AccountRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountRequestFactory extends Factory
{
    protected $model = AccountRequest::class;

    public function definition(): array
    {
        $adminId = User::whereIn('role', ['admin', 'employee'])->inRandomOrder()->first()?->id;

        $status = $this->faker->randomElement(['pending', 'accepted', 'rejected']);

        return [
            'full_name'         => $this->faker->name(),
            'date_of_birth'     => $this->faker->date('Y-m-d', '-18 years'),
            'gender'            => $this->faker->randomElement(['male', 'female']),
            'marital_status'    => $this->faker->randomElement(['single', 'married', 'divorced', 'widowed']),
            'identity_number'   => $this->faker->unique()->numerify('##########'),
            'address'           => $this->faker->address(),
            'occupation'        => $this->faker->jobTitle(),
            'deposit_amount'    => $this->faker->randomFloat(2, 100, 50000),
            
            'status'            => $status,
            'verification_code' => $status === 'pending' ? $this->faker->numerify('######') : null,
            'admin_id'          => $status !== 'pending' ? $adminId : null, 
            
            'email'             => $this->faker->unique()->safeEmail(),
            'admin_notes'       => $status === 'rejected' ? $this->faker->sentence() : null,
            'verified_at'       => $status !== 'pending' ? now() : null,
            
            'created_at'        => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at'        => now(),
        ];
    }
}