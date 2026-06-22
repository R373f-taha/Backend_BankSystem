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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique()->nullable();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['credit', 'debit', 'withdrawal']);
            $table->string('description', 500)->nullable();
            //adding new fields for transaction status and approval process
            $table->enum('status', ['pending', 'pending_approval', 'completed', 'failed', 'rejected'])
                ->default('completed');
            $table->boolean('needs_approval')->default(false);
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason')->nullable();
            //$table->renameColumn('type', 'transaction_type');
            //...
         //    $table->timestamp('approval_requested_at')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->foreignId('transfer_id')->nullable()->constrained('transfers', 'id')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
