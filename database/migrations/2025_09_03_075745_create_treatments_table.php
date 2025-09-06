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
                  $table->unsignedBigInteger('queue_service_id')->comment('ລະຫັດບໍລິການໃນຄິວ (Foreign Key)');
                  $table->unsignedBigInteger('room_id')->nullable()->comment('ຫ້ອງທີ່ໃຊ້ໃນການກວດ');
                  $table->unsignedBigInteger('performed_by')->comment('ຜູ້ເຮັດການກວດ/ປິ່ນປົວ');

                  // ເວລາ
                  $table->timestamp('treatment_started_at')->nullable()->comment('ເວລາເລີ່ມການປິ່ນປົວ/ກວດ');
                  $table->timestamp('treatment_ended_at')->nullable()->comment('ເວລາສິ້ນສຸດການປິ່ນປົວ/ກວດ');

                  // ຂໍ້ມູນການກວດທົ່ວໄປ
                  $table->text('medical_history_notes')->nullable()->comment('ປະຫວັດການປ່ວຍເພີ່ມເຕີມທີ່ທ່ານໝໍຖາມ');
                  $table->text('current_symptoms')->nullable()->comment('ອາການປັດຈຸບັນທີ່ຄົນໄຂ້ບອກ');
                  $table->text('physical_examination')->nullable()->comment('ການກວດທາງກາຍະພາບ (ກວດດູ, ຈັບ, ຟັງ)');
                  $table->text('examination_notes')->nullable()->comment('ບັນທຶກການກວດ/ການສັງເກດ');

                  // ການວິເຄາະແລະການວິນິໄຈ
                  $table->text('initial_assessment')->nullable()->comment('ການປະເມີນເບື້ອງຕົ້ນຈາກທ່ານໝໍ');
                  $table->text('findings')->nullable()->comment('ສິ່ງທີ່ພົບຈາກການກວດ');
                  $table->text('diagnosis')->nullable()->comment('ການວິນິໄຈໂລກສຸດທ້າຍ');

                  // ການປິ່ນປົວແລະຄຳແນະນຳ
                  $table->text('treatment_plan')->nullable()->comment('ແຜນການປິ່ນປົວ/ການຮັກສາ');
                  $table->text('recommendations')->nullable()->comment('ຄຳແນະນຳຈາກທ່ານໝໍ');
                  $table->text('doctor_recommendations')->nullable()->comment('ຄຳແນະນຳພິເສດຈາກທ່ານໝໍ');

                  // ການຕິດຕາມ
                  $table->boolean('follow_up_required')->default(false)->comment('ຕ້ອງມາຕິດຕາມບໍ່');
                  $table->date('follow_up_date')->nullable()->comment('ວັນນັດຕິດຕາມ');
                  $table->text('follow_up_notes')->nullable()->comment('ໝາຍເຫດການຕິດຕາມ');

                  $table->enum('status', [
                        'In_Progress',          // ກຳລັງກວດ
                        'Waiting_Lab_Results',  // ລໍຖ້າຜົນແລັບ
                        'Completed',            // ສຳເລັດ
                        'Cancelled'             // ຍົກເລີກ
                  ])->default('In_Progress')->comment('ສະຖານະການປິ່ນປົວ');

                  $table->unsignedBigInteger('updated_by')->nullable()->comment('ຜູ້ອັບເດດຫຼ້າສຸດ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Foreign Keys
                  $table->foreign('queue_service_id')->references('id')->on('queue_services')->onDelete('cascade');
                  $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
                  $table->foreign('performed_by')->references('id')->on('users');
                  $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

                  // Indexes
                  $table->index(['performed_by', 'treatment_started_at']);
                  $table->index(['room_id', 'status']);
                  $table->index('queue_service_id');
                  $table->index('status');
                  $table->index('treatment_started_at');
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
