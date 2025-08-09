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
       Schema::create('medical_examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('queue_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('service_id')->constrained('medical_services');
            $table->foreignId('room_id')->nullable()->constrained('examination_rooms')->onDelete('set null');
            $table->date('examination_date');
            $table->time('examination_time');
            
            // Vital Signs (ບັນທຶກທຸກຄັ້ງ)
            $table->json('vital_signs')->nullable()->comment('ນ້ຳໜັກ, ຄວາມດັນ, ອຸນຫະພູມ, ຫົວໃຈ');
            
            // ຜົນການກວດ
            $table->json('examination_results')->nullable()->comment('ຜົນການກວດຕາມ template');
            
            // ສະຖານະ ແລະ ຜູ້ດຳເນີນການ
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('conducted_by')->nullable()->constrained('users')->onDelete('set null')->comment('ພະຍາບານ/ເທັກນິກ');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null')->comment('ໝໍ');
            
            // ເວລາ
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            // ໝາຍເຫດ
            $table->text('notes')->nullable();
            $table->text('technician_notes')->nullable()->comment('ໝາຍເຫດຜູ້ເຮັດການກວດ');
            $table->text('doctor_notes')->nullable()->comment('ໝາຍເຫດໝໍ');
            
            // ໄຟລ์ແນບ (ຖ້າມີ)
            $table->json('attachments')->nullable()->comment('ໄຟລ์ເອກະສານແນບ');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['patient_id', 'examination_date']);
            $table->index('status');
            $table->index('examination_date');
            $table->index(['service_id', 'examination_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_examinations');
    }
};
