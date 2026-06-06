<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_requests', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->string('full_name', 255);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed']);
            $table->string('identity_number', 50)->unique();
            $table->text('address');
            $table->string('occupation', 255);
            $table->decimal('deposit_amount', 15, 2);

            // Status & Tracking
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->string('verification_code', 10)->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users');

            // Additional
            $table->string('email', 255)->nullable();
            $table->text('admin_notes')->nullable();
         //   $table->string('unique_link', 64)->unique()->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_requests');
    }
};
