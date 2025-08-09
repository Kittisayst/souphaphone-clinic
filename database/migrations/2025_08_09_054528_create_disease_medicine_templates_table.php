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
         Schema::create('disease_medicine_templates', function (Blueprint $table) {
            $table->id();
            $table->string('disease_name');
            $table->string('icd10_code', 10)->nullable();
            $table->json('template_medicines')->comment('ແມ່ແບບຢາສຳຫລັບໂລກນີ້');
            $table->text('instructions')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('disease_name');
            $table->index('icd10_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disease_medicine_templates');
    }
};
