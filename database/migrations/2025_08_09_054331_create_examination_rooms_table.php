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
        Schema::create('examination_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_name', 50);
            $table->string('room_code', 10)->unique()->comment('R001, R002');
            $table->enum('room_type', ['general', 'specialist', 'laboratory', 'ultrasound'])->default('general');
            $table->enum('status', ['available', 'occupied', 'maintenance', 'closed'])->default('available');
            $table->foreignId('current_patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->integer('capacity')->default(1);
            $table->json('equipment')->nullable()->comment('ອຸປະກອນໃນຫ້ອງ');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('room_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examination_rooms');
    }
};
