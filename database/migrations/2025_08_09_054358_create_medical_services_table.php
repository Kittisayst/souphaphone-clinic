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
        Schema::create('medical_services', function (Blueprint $table) {
            $table->id();
            $table->string('service_name', 100);
            $table->string('service_code', 20)->unique()->comment('BLOOD01, ULTRA01');
            $table->enum('service_category', ['examination', 'laboratory', 'imaging', 'procedure']);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->json('template_fields')->comment('ໂຄງສ້າງ form ການກວດ');
            $table->json('document_template')->nullable()->comment('template ສຳຫລັບພິມ');
            $table->integer('estimated_duration')->nullable()->comment('ເວລາປະມານ (ນາທີ)');
            $table->boolean('requires_preparation')->default(false);
            $table->text('preparation_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            // Indexes
            $table->index('service_category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_services');
    }
};
