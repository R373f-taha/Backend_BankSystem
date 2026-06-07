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
            $table->string('reference_number', 50)->unique();
            $table->foreignId('sender_id')->constrained('customers', 'id')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('customers', 'id')->onDelete('cascade');
            $table->decimal('amount', 15);
            $table->text('notes')->nullable();
            //adding new fields for transfer status and approval process
            $table->enum('status', ['pending', 'pending_approval', 'completed', 'failed', 'rejected'])
                ->default('pending');
            $table->boolean('needs_approval')->default(false);
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason')->nullable();
            //...
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
