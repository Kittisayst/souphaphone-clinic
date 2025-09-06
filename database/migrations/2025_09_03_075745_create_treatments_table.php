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
            Schema::create('treatments', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດການປິ່ນປົວ (Primary Key)');

                  // ຄວາມສຳພັນຫຼັກ
                  $table->unsignedBigInteger('queue_service_id')
                        ->comment('ລະຫັດຄິວບໍລິການ (Foreign Key)');
                  $table->unsignedBigInteger('room_id')
                        ->comment('ລະຫັດຫ້ອງ (Foreign Key)');
                  $table->unsignedBigInteger('doctor_id')
                        ->comment('ລະຫັດທ່ານໝໍ (Foreign Key)');

                  // Phase 1: ການກວດເບື້ອງຕົ້ນ
                  $table->text('examination_notes')->nullable()
                        ->comment('ບັນທຶກການກວດຮ່າງກາຍ');
                  $table->text('findings')->nullable()
                        ->comment('ສິ່ງທີ່ພົບຈາກການກວດ');
                  $table->text('medical_history_notes')->nullable()
                        ->comment('ປະຫວັດການປ່ວຍທີ່ເກີ່ຍວຂ້ອງ');

                  // Phase 2: ການວິນິໄຈ & ວາງແຜນ (ຫຼັງຈາກມີຜົນ Lab)
                  $table->text('diagnosis')->nullable()
                        ->comment('ການວິນິໄຈໂລກສຸດທ້າຍ');
                  $table->text('treatment_plan')->nullable()
                        ->comment('ແຜນການປິ່ນປົວ');

                  // ການຕິດຕາມ
                  $table->boolean('follow_up_required')->default(false)
                        ->comment('ຕ້ອງມາຕິດຕາມບໍ່');
                  $table->date('follow_up_date')->nullable()
                        ->comment('ວັນນັດຕິດຕາມ');
                  $table->text('follow_up_notes')->nullable()
                        ->comment('ໝາຍເຫດການຕິດຕາມ');

                  // ສະຖານະການປິ່ນປົວ
                  $table->enum('status', [
                        'In_Progress',           // ກຳລັງກວດຢູ່
                        'Waiting_Lab_Results',   // ລໍຖ້າຄນົນ Lab
                        'Lab_Results_Ready',     // ຜົນ Lab ພ້ອມແລ້ວ
                        'Completed',             // ສຳເລັດການປິ່ນປົວ
                        'Cancelled'              // ຍົກເລີກ
                  ])->default('In_Progress')
                        ->comment('ສະຖານະການປິ່ນປົວ');

                  // ການຕິດຕາມການອັບເດດ
                  $table->unsignedBigInteger('updated_by')->nullable()
                        ->comment('ຜູ້ອັບເດດຄັ້ງຫຼ້າສຸດ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Foreign Keys
                  $table->foreign('queue_service_id')->references('id')->on('queue_services')
                        ->onDelete('cascade')->comment('ເຊື່ອມໂຍງກັບຄິວບໍລິການ');
                  $table->foreign('room_id')->references('id')->on('rooms')
                        ->comment('ເຊື່ອມໂຍງກັບຫ້ອງ');
                  $table->foreign('doctor_id')->references('id')->on('users')
                        ->comment('ເຊື່ອມໂຍງກັບທ່ານໝໍ');
                  $table->foreign('updated_by')->references('id')->on('users')
                        ->onDelete('set null')->comment('ເຊື່ອມໂຍງກັບຜູ້ອັບເດດ');

                  // Indexes
                  $table->index(['queue_service_id', 'status'], 'idx_queue_service_status');
                  $table->index(['doctor_id', 'created_at'], 'idx_doctor_date');
                  $table->index('status', 'idx_status');
                  $table->index('follow_up_date', 'idx_follow_up_date');
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
