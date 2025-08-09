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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->string('group', 50)->default('general')->comment('general, clinic, billing, etc.');
            $table->string('type', 20)->default('text')->comment('text, number, boolean, json');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false)->comment('ສາມາດເຂົ້າເຖິງໂດຍບໍ່ຕ້ອງ login');
            $table->timestamps();
            
            // Indexes
            $table->index('group');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
