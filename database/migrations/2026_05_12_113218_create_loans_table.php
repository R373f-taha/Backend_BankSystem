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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('loan_type', 100);
            $table->decimal('amount', 15, 2);
            $table->decimal('interest_rate', 5, 2)->default(0.00);
            $table->integer('term_months')->unsigned();
            $table->decimal('monthly_payment', 15, 2)->nullable();
            $table->decimal('credit_score', 10, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->date('applied_at')->useCurrent();
            $table->date('approved_at')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
