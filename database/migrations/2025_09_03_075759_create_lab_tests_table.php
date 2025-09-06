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
            Schema::create('lab_tests', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດການກວດ Lab (Primary Key)');

                  // ຄວາມສຳພັນຫຼັກ
                  $table->unsignedBigInteger('treatment_id')
                        ->comment('ລະຫັດການປິ່ນປົວ (Foreign Key)');
                  $table->unsignedBigInteger('queue_service_id')->nullable()
                        ->comment('ຄິວບໍລິການສຳລັບ Lab (ຖ້າແຍກຄິວ)');

                  // ຂໍ້ມູນການກວດ
                  $table->string('lab_test_code', 50)
                        ->comment('ລະຫັດການກວດ (CBC, FBS, etc.)');
                  $table->string('lab_test_name', 200)
                        ->comment('ຊື່ການກວດເປັນພາສາລາວ');

                  // ຜົນການກວດ
                  $table->json('test_result_values')->nullable()
                        ->comment('ຜົນການກວດ (JSON format)');
                  $table->string('reference_range', 100)->nullable()
                        ->comment('ຄ່າມາດຕະຖານ');
                  $table->enum('abnormal_flag', ['Normal', 'High', 'Low', 'Critical'])->nullable()
                        ->comment('ສະຖານະຜົນກວດ');
                  $table->text('interpretation')->nullable()
                        ->comment('ການຕີຄວາມໝາຍຜົນກວດ');

                  // ການເກັບຕົວຢ່າງ
                  $table->string('sample_type', 50)->nullable()
                        ->comment('ປະເພດຕົວຢ່າງ (ເລືອດ, ປັດສະວະ, etc.)');
                  $table->timestamp('sample_collected_at')->nullable()
                        ->comment('ເວລາເກັບຕົວຢ່າງ');
                  $table->unsignedBigInteger('sample_collected_by')->nullable()
                        ->comment('ຜູ້ເກັບຕົວຢ່າງ');

                  // ການກວດ & ການກວດສອບ
                  $table->timestamp('tested_at')->nullable()
                        ->comment('ເວລາເຮັດການກວດ');
                  $table->unsignedBigInteger('tested_by')->nullable()
                        ->comment('ຜູ້ເຮັດການກວດ');
                  $table->unsignedBigInteger('reviewed_by')->nullable()
                        ->comment('ຜູ້ກວດສອບຜົນ');
                  $table->timestamp('reviewed_at')->nullable()
                        ->comment('ເວລາກວດສອບຜົນ');

                  // ໝາຍເຫດ
                  $table->text('technician_notes')->nullable()
                        ->comment('ໝາຍເຫດຈາກເຕັກນິກ');
                  $table->text('doctor_notes')->nullable()
                        ->comment('ໝາຍເຫດຈາກທ່ານໝໍ');

                  // ສະຖານະ
                  $table->enum('status', [
                        'Ordered',          // ສັ່ງແລ້ວ
                        'Sample_Collected', // ເກັບຕົວຢ່າງແລ້ວ
                        'Testing',          // ກຳລັງກວດ
                        'Completed',        // ສຳເລັດການກວດ
                        'Reviewed',         // ກວດສອບແລ້ວ
                        'Cancelled'         // ຍົກເລີກ
                  ])->default('Ordered')
                        ->comment('ສະຖານະການກວດ Lab');

                  $table->timestamps();
                  $table->softDeletes();

                  // Foreign Keys
                  $table->foreign('treatment_id')->references('id')->on('treatments')
                        ->onDelete('cascade')->comment('ເຊື່ອມໂຍງກັບການປິ່ນປົວ');
                  $table->foreign('queue_service_id')->references('id')->on('queue_services')
                        ->onDelete('set null')->comment('ເຊື່ອມໂຍງກັບຄິວບໍລິການ Lab');
                  $table->foreign('sample_collected_by')->references('id')->on('users')
                        ->onDelete('set null')->comment('ເຊື່ອມໂຍງກັບຜູ້ເກັບຕົວຢ່າງ');
                  $table->foreign('tested_by')->references('id')->on('users')
                        ->onDelete('set null')->comment('ເຊື່ອມໂຍງກັບຜູ້ເຮັດການກວດ');
                  $table->foreign('reviewed_by')->references('id')->on('users')
                        ->onDelete('set null')->comment('ເຊື່ອມໂຍງກັບຜູ້ກວດສອບ');

                  // Indexes
                  $table->index(['treatment_id', 'status'], 'idx_treatment_status');
                  $table->index('lab_test_code', 'idx_lab_test_code');
                  $table->index(['status', 'created_at'], 'idx_status_date');
                  $table->index(['reviewed_by', 'reviewed_at'], 'idx_reviewed');
            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('lab_tests');
      }
};
