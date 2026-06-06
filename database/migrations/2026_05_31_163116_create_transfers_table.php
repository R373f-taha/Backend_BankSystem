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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number',50)->unique();
            $table->foreignId('sender_id')->constrained('customers','id')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('customers','id')->onDelete('cascade');
            $table->decimal('amount',15);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'rejected'])->default('pending');
           $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
