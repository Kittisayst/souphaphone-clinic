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
       Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users');
            $table->json('examination_ids')->nullable()->comment('ອ້າງອີງການກວດ [1,2,3]');
            
            // ການວິນິໄສ
            $table->text('chief_complaint')->nullable()->comment('ອາການສຳຄັນ');
            $table->json('diagnosis')->comment('ການວິນິໄສໂລກ');
            $table->text('diagnosis_notes')->nullable();
            
            // ແຜນການຮັກສາ
            $table->json('treatment_plan')->nullable()->comment('ແຜນການຮັກສາ');
            $table->text('treatment_notes')->nullable();
            
            // ຢາທີ່ສັ່ງ
            $table->json('prescribed_medicines')->nullable()->comment('ຢາທີ່ສັ່ງ');
            
            // ການນັດຕິດຕາມ
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            
            // ສະຖານະ
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['patient_id', 'created_at']);
            $table->index(['doctor_id', 'created_at']);
            $table->index('follow_up_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
