<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
      /**
       * Run the migrations.
       */
      public function up(): void
      {
            Schema::create('queues', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດຄິວ (Primary Key)');
                  $table->unsignedBigInteger('patient_id')->comment('ລະຫັດຄົນໄຂ້ (Foreign Key)');
                  $table->integer('queue_number')->comment('ເລກຄິວໃນວັນນັ້ນ (1, 2, 3...)');
                  $table->integer('waiting_number')->default(0)->comment('ເລກລໍຖ້າປັດຈຸບັນ (0=ສຳເລັດແລ້ວ)');
                  $table->date('queue_date')->comment('ວັນທີ່ມາກວດ');
                  $table->text('initial_complaint')->nullable()->comment('ອາການເບື້ອງຕົ້ນທີ່ແຈ້ງມາ');
                  $table->unsignedBigInteger('doctor_id')->nullable()->comment('ທ່ານໝໍທີ່ຮັບຄິວນີ້');

                  // ການຈັດການຫ້ອງ
                  $table->unsignedBigInteger('assigned_room_id')->nullable()->comment('ຫ້ອງທີ່ຖືກມອບໝາຍໃຫ້ຄິວນີ້');
                  $table->timestamp('room_assigned_at')->nullable()->comment('ເວລາທີ່ມອບໝາຍຫ້ອງ');

                  $table->enum('queue_status', [
                        'Registered',    // ລົງທະບຽນແລ້ວ
                        'Vital_Checked', // ກວດເບື້ອງຕົ້ນແລ້ວ  
                        'With_Doctor',   // ກຳລັງຢູ່ກັບທ່ານໝໍ
                        'Lab_Testing',   // ກຳລັງກວດແລັບ
                        'Results_Ready', // ຜົນກວດພ້ອມແລ້ວ
                        'Completed',     // ສຳເລັດ
                        'Cancelled'      // ຍົກເລີກ
                  ])->default('Registered')->comment('ສະຖານະຄິວໃນແຕ່ລະຂັ້ນຕອນ');

                  // Timestamps ສຳລັບແຕ່ລະຂັ້ນຕອນ
                  $table->timestamp('vital_checked_at')->nullable()->comment('ເວລາທີ່ກວດເບື້ອງຕົ້ນແລ້ວ');
                  $table->timestamp('doctor_start_at')->nullable()->comment('ເວລາທີ່ທ່ານໝໍເລີ່ມກວດ');
                  $table->timestamp('lab_start_at')->nullable()->comment('ເວລາທີ່ເລີ່ມກວດແລັບ');
                  $table->timestamp('results_ready_at')->nullable()->comment('ເວລາທີ່ຜົນກວດພ້ອມ');
                  $table->timestamp('completed_at')->nullable()->comment('ເວລາທີ່ສຳເລັດການກວດທັງໝົດ');

                  $table->enum('priority_level', ['Normal', 'Urgent', 'Emergency'])->default('Normal')
                        ->comment('ລະດັບຄວາມສຳຄັນ: ປົກກະຕິ, ຮີບ, ສຸກເສີນ');
                  $table->unsignedBigInteger('created_by')->comment('ຜູ້ສ້າງຄິວ');
                  $table->unsignedBigInteger('updated_by')->nullable()->comment('ຜູ້ອັບເດດຄິວຫຼ້າສຸດ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Foreign Keys
                  $table->foreign('patient_id')->references('id')->on('patients');
                  $table->foreign('doctor_id')->references('id')->on('users')->onDelete('set null');
                  $table->foreign('assigned_room_id')->references('id')->on('rooms')->onDelete('set null');
                  $table->foreign('created_by')->references('id')->on('users');
                  $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

                  // Indexes
                  $table->index('waiting_number');
                  $table->index(['queue_date', 'queue_number']);
                  $table->index(['queue_date', 'queue_status']);
                  $table->index(['queue_date', 'waiting_number']);
                  $table->index('assigned_room_id');
                  $table->unique(['queue_date', 'queue_number']);
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
