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
       Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 20)->unique()->comment('INV-2024-001');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            
            // ລາຍການ
            $table->json('examination_ids')->nullable()->comment('ອ້າງອີງການກວດ [1,2,3]');
            $table->foreignId('treatment_id')->nullable()->constrained()->onDelete('set null');
            $table->json('items')->comment('ລາຍການສິນຄ້າ/ບໍລິການ');
            
            // ຈຳນວນເງິນ
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            
            // ການຊຳລະ
            $table->enum('payment_method', ['cash', 'transfer', 'credit_card', 'insurance']);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'cancelled', 'refunded'])->default('pending');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->timestamp('payment_date')->nullable();
            $table->string('payment_reference', 100)->nullable()->comment('ເລກທີໂອນເງິນ');
            
            // ຜູ້ດຳເນີນການ
            $table->foreignId('cashier_id')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // ໝາຍເຫດ
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_number');
            $table->index(['patient_id', 'created_at']);
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
