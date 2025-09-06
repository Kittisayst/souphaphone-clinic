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

                  $table->unsignedBigInteger('queue_service_id')
                        ->comment('ລະຫັດຄິວບໍລິການ (Foreign Key)');
                  $table->unsignedBigInteger('room_id')
                        ->comment('ລະຫັດຫ້ອງ (Foreign Key)');
                  $table->unsignedBigInteger('doctor_id')
                        ->comment('ລະຫັດທ່ານໝໍ (Foreign Key)');

                  // ຂໍ້ມູນການກວດ
                  $table->text('examination_notes')->nullable()
                        ->comment('ບັນທຶກການກວດຮ່າງກາຍ');
                  $table->text('findings')->nullable()
                        ->comment('ສິ່ງທີ່ພົບຈາກການກວດ');
                  $table->text('medical_history_notes')->nullable()
                        ->comment('ບັນທຶກປະຫວັດການປ່ວຍ');

                  // ການວິນິໄຈ ແລະ ແຜນການຮັກສາ
                  $table->text('diagnosis')->nullable()
                        ->comment('ການວິນິໄຈພະຍາດ');
                  $table->text('treatment_plan')->nullable()
                        ->comment('ແຜນການປິ່ນປົວ');

                  // ການຕິດຕາມ
                  $table->boolean('follow_up_required')->default(false)
                        ->comment('ຕ້ອງການຕິດຕາມບໍ່');
                  $table->date('follow_up_date')->nullable()
                        ->comment('ວັນນັດຕິດຕາມ');
                  $table->text('follow_up_notes')->nullable()
                        ->comment('ໝາຍເຫດການຕິດຕາມ');

                  // ສະຖານະການປິ່ນປົວ
                  $table->enum('status', [
                        'In_Progress',  // ກຳລັງກວດຢູ່
                        'Completed',    // ສຳເລັດແລ້ວ
                        'Cancelled'     // ຍົກເລີກ
                  ])->default('In_Progress')->comment('ສະຖານະການປິ່ນປົວ');

                  // ຂໍ້ມູນການເກັບເງິນ
                  $table->json('billing_items')->nullable()
                        ->comment('ລາຍການໃບເກັບເງິນ (JSON)');
                  $table->decimal('total_amount', 10, 2)->default(0)
                        ->comment('ລາຄາລວມທັງໝົດ');

                  // ຜູ້ອັບເດດ
                  $table->unsignedBigInteger('updated_by')->nullable()
                        ->comment('ຜູ້ອັບເດດຂໍ້ມູນຄັ້ງຫຼ້າສຸດ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->unique('queue_service_id');
                  $table->index(['doctor_id', 'status']);
                  $table->index(['room_id', 'created_at']);
                  $table->index('follow_up_date');
                  $table->index('status');

                  // Foreign Keys
                  $table->foreign('queue_service_id')->references('id')->on('queue_services');
                  $table->foreign('room_id')->references('id')->on('rooms');
                  $table->foreign('doctor_id')->references('id')->on('users');
                  $table->foreign('updated_by')->references('id')->on('users');
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
