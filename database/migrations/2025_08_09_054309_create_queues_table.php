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
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            
            // ຂໍ້ມູນພື້ນຖານ
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('queue_number', 10)->comment('A001, A002, B001...');
            $table->date('queue_date');
            $table->enum('priority', ['normal', 'urgent'])->default('normal');
            
            // ສະຖານະຫຼັກ ແລະ ຂັ້ນຕອນການເຮັດວຽກ
            $table->enum('status', [
                'waiting',           // ລໍຖ້າ
                'called',            // ຖືກເອີ້ນ
                'in_progress',       // ກຳລັງດຳເນີນການ
                'waiting_results',   // ລໍຖ້າຜົນ
                'completed',         // ສຳເລັດ
                'cancelled'          // ຍົກເລີກ
            ])->default('waiting');
            
            $table->enum('current_stage', [
                'registration',      // ລົງທະບຽນ/ຮັບຄິວ
                'basic_check',       // ກວດພື້ນຖານ (ເຄົ້າເຕີ)
                'waiting_room',      // ລໍຖ້າເຂົ້າຫ້ອງ
                'examination',       // ການກວດພິເສດ (ເລືອດ, Scan)
                'waiting_results',   // ລໍຖ້າຜົນກວດ
                'consultation',      // ພົບໝໍ/ການປຶກສາ
                'treatment',         // ການຮັກສາ/ສັ່ງຢາ
                'payment',           // ຈ່າຍເງິນ
                'completed'          // ສຳເລັດ
            ])->default('registration');
            
            // ຫ້ອງທີ່ຖືກມອບໝາຍ
            $table->foreignId('assigned_room_id')
                  ->nullable()
                  ->constrained('examination_rooms')
                  ->nullOnDelete();
            
            // ເວລາສຳຄັນໃນແຕ່ລະຂັ້ນຕອນ
            $table->timestamp('called_at')->nullable()->comment('ເວລາເອີ້ນຄິວ');
            $table->timestamp('basic_check_at')->nullable()->comment('ເວລາເລີ່ມກວດພື້ນຖານ');
            $table->timestamp('room_assigned_at')->nullable()->comment('ເວລາມອບໝາຍຫ້ອງ');
            $table->timestamp('examination_started_at')->nullable()->comment('ເວລາເລີ່ມກວດພິເສດ');
            $table->timestamp('results_ready_at')->nullable()->comment('ເວລາຜົນກວດພ້ອມ');
            $table->timestamp('consultation_started_at')->nullable()->comment('ເວລາເລີ່ມພົບໝໍ');
            $table->timestamp('treatment_completed_at')->nullable()->comment('ເວລາສຳເລັດການຮັກສາ');
            $table->timestamp('payment_completed_at')->nullable()->comment('ເວລາສຳເລັດການຊຳລະ');
            $table->timestamp('completed_at')->nullable()->comment('ເວລາສຳເລັດທັງໝົດ');
            
            // ຂໍ້ມູນການກວດພື້ນຖານ (Vital Signs)
            $table->json('basic_vitals')->nullable()->comment('ນ້ຳໜັກ, ສ່ວນສູງ, ຄວາມດັນ, ອຸນຫະພູມ, ຊີບຈອນ');
            
            // ອາການເບື້ອງຕົ້ນ
            $table->text('initial_symptoms')->nullable()->comment('ອາການທີ່ຄົນໄຂ້ບອກເບື້ອງຕົ້ນ');
            
            // ໝາຍເຫດ
            $table->text('notes')->nullable()->comment('ໝາຍເຫດເພີ່ມເຕີມ');
            
            // ຜູ້ປະຕິບັດງານໃນແຕ່ລະຂັ້ນຕອນ
            $table->foreignId('created_by')->constrained('users')->comment('ຜູ້ສ້າງຄິວ');
            $table->foreignId('basic_check_by')->nullable()->constrained('users')->comment('ຜູ້ກວດພື້ນຖານ');
            $table->foreignId('examination_by')->nullable()->constrained('users')->comment('ຜູ້ສັ່ງກວດພິເສດ');
            $table->foreignId('consultation_by')->nullable()->constrained('users')->comment('ໝໍທີ່ປຶກສາ');
            $table->foreignId('payment_processed_by')->nullable()->constrained('users')->comment('ແຄຊເຍີທີ່ຮັບເງິນ');
            
            // ຄ່າໃຊ້ຈ່າຍ (ຄຳນວນລ່ວງໜ້າ)
            $table->decimal('estimated_cost', 10, 2)->default(0)->comment('ຄ່າໃຊ້ຈ່າຍປະມານ');
            $table->decimal('final_cost', 10, 2)->nullable()->comment('ຄ່າໃຊ້ຈ່າຍຈິງ');
            
            // ການຕິດຕາມ
            $table->boolean('requires_follow_up')->default(false)->comment('ຕ້ອງມາຕິດຕາມບໍ່');
            $table->date('follow_up_date')->nullable()->comment('ວັນນັດຕິດຕາມ');
            
            $table->timestamps();
            
            // Indexes ສຳລັບ Performance
            $table->unique(['queue_number', 'queue_date'], 'unique_queue_per_date');
            $table->index(['queue_date', 'status'], 'idx_date_status');
            $table->index(['queue_date', 'current_stage'], 'idx_date_stage');
            $table->index(['patient_id', 'queue_date'], 'idx_patient_date');
            $table->index(['assigned_room_id', 'status'], 'idx_room_status');
            $table->index(['status', 'priority', 'queue_number'], 'idx_status_priority_number');
            $table->index(['current_stage', 'called_at'], 'idx_stage_called');
            $table->index(['created_at'], 'idx_created_at');
            $table->index(['priority', 'created_at'], 'idx_priority_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};