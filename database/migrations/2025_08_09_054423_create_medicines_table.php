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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('medicine_name');
            $table->string('medicine_code', 50)->unique();
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('strength', 50)->nullable()->comment('500mg, 10ml');
            $table->enum('dosage_form', ['tablet', 'capsule', 'syrup', 'injection', 'cream', 'drops', 'other']);
            
            // ລາຄາ ແລະ ສາງ
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->nullable()->comment('ລາຄາຕົ້ນທຶນ');
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->integer('maximum_stock')->nullable();
            
            // ວັນໝົດອາຍຸ
            $table->date('expiry_date')->nullable();
            $table->string('batch_number', 50)->nullable();
            
            // ຂໍ້ມູນຜູ້ຈຳໜ່າຍ
            $table->string('supplier')->nullable();
            $table->text('supplier_contact')->nullable();
            
            // ໝວດໝູ່ ແລະ ຄຳອະທິບາຍ
            $table->string('category', 100)->nullable();
            $table->string('therapeutic_class', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('usage_instructions')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('contraindications')->nullable();
            
            // ສະຖານະ
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_prescription')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index('medicine_code');
            $table->index('medicine_name');
            $table->index('expiry_date');
            $table->index(['stock_quantity', 'minimum_stock']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
