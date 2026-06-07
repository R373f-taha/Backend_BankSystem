<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->unique();
            $table->string('email')->unique();
            $table->enum('status', ['active', 'un_active', 'frozen'])->default('active');
            $table->string('account_code')->unique();
            $table->decimal('balance', 15, 2)->default(0.00);
            // Adding new fields for account freezing functionality
            $table->timestamp('frozen_at')->nullable();
            $table->string('frozen_reason')->nullable();
            $table->timestamp('unfrozen_at')->nullable();
            $table->string('unfrozen_reason')->nullable();
            //...
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
