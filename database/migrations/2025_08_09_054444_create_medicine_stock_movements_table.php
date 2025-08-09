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
        Schema::create('medicine_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->enum('movement_type', ['in', 'out', 'adjustment', 'expired']);
            $table->integer('quantity');
            $table->integer('remaining_stock');
            $table->enum('reference_type', ['purchase', 'sale', 'adjustment', 'disposal', 'treatment']);
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ອ້າງອີງຫາ treatment_id ຫຼື invoice_id');
            $table->text('notes')->nullable();
            $table->foreignId('moved_by')->constrained('users');
            $table->timestamp('moved_at');
            
            // Indexes
            $table->index(['medicine_id', 'moved_at']);
            $table->index(['movement_type', 'moved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_stock_movements');
    }
};
