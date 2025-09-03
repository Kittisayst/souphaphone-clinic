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
           $table->id()->comment('ລະຫັດຄິວ (Primary Key)');
            $table->unsignedBigInteger('patient_id')
                  ->comment('ລະຫັດຄົນໄຂ້ (Foreign Key)');
            $table->integer('queue_number')
                  ->comment('ເລກຄິວໃນວັນນັ້ນ (1, 2, 3...)');
            $table->date('queue_date')->comment('ວັນທີ່ມາກວດ');
            $table->text('initial_complaint')->nullable()
                  ->comment('ອາການເບື້ອງຕົ້ນທີ່ແຈ້ງມາ (ປວດຫົວ, ໄຂ້)');
            $table->unsignedBigInteger('doctor_id')->nullable()
                  ->comment('ທ່ານໝໍທີ່ຮັບຄິວນີ້');
            $table->enum('queue_status', [
                'Registered',    // ລົງທະບຽນແລ້ວ
                'Vital_Checked', // ກວດເບື້ອງຕົ້ນແລ້ວ  
                'With_Doctor',   // ກຳລັງຢູ່ກັບທ່ານໝໍ
                'Lab_Testing',   // ກຳລັງກວດແລັບ
                'Results_Ready', // ຜົນກວດພ້ອມແລ້ວ
                'Completed',     // ສຳເລັດ
                'Cancelled'      // ຍົກເລີກ
            ])->default('Registered')->comment('ສະຖານະຄິວໃນແຕ່ລະຂັ້ນຕອນ');
            $table->timestamp('vital_checked_at')->nullable()
                  ->comment('ເວລາທີ່ກວດເບື້ອງຕົ້ນແລ້ວ');
            $table->timestamp('doctor_start_at')->nullable()
                  ->comment('ເວລາທີ່ທ່ານໝໍເລີ່ມກວດ');
            $table->timestamp('completed_at')->nullable()
                  ->comment('ເວລາທີ່ສຳເລັດການກວດທັງໝົດ');
            $table->enum('priority_level', ['Normal', 'Urgent', 'Emergency'])->default('Normal')
                  ->comment('ລະດັບຄວາມສຳຄັນ: ປົກກະຕິ, ຮີບ, ສຸກເສີນ');
            $table->unsignedBigInteger('created_by')
                  ->comment('ຜູ້ສ້າງຄິວ (ພະນັກງານຮັບບ້ານ)');
            $table->timestamps(); // created_at = ເວລາສ້າງຄິວ, updated_at
            $table->softDeletes(); // deleted_at

            // Foreign Keys
            $table->foreign('patient_id')->references('id')->on('patients')
                  ->comment('ເຊື່ອມໂຍງກັບຕາຕະລາງຄົນໄຂ້');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('set null')
                  ->comment('ເຊື່ອມໂຍງກັບທ່ານໝໍທີ່ຮັບຄິວ');
            $table->foreign('created_by')->references('id')->on('users')
                  ->comment('ເຊື່ອມໂຍງກັບຜູ້ສ້າງຄິວ');

            // Indexes
            $table->index(['queue_date', 'queue_number'])->comment('Index ສຳລັບຄົ້ນຫາຄິວຕາມວັນແລະເລກຄິວ');
            $table->index(['queue_date', 'queue_status'])->comment('Index ສຳລັບຄົ້ນຫາຄິວຕາມວັນແລະສະຖານະ');
            $table->index('doctor_id')->comment('Index ສຳລັບຄົ້ນຫາຄິວຂອງທ່ານໝໍ');
            $table->unique(['queue_date', 'queue_number'])->comment('ບໍ່ໃຫ້ເລກຄິວຊ້ຳໃນວັນດຽວກັນ');
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
