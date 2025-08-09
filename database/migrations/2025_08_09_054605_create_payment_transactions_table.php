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
       Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->enum('transaction_type', ['payment', 'refund']);
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'transfer', 'credit_card']);
            $table->string('reference_number', 100)->nullable();
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamp('processed_at');
            $table->text('notes')->nullable();
            
            // Indexes
            $table->index(['invoice_id', 'processed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
