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

                  // ຂໍ້ມູນພື້ນຖານ
                  $table->unsignedBigInteger('patient_id')
                        ->comment('ລະຫັດຄົນໄຂ້ (Foreign Key)');
                  $table->integer('queue_number')
                        ->comment('ເລກຄິວໃນວັນນັ້ນ (1, 2, 3...)');
                  $table->integer('waiting_number')->default(0)
                        ->comment('ເລກລໍຖ້າປັດຈຸບັນ (0=ສຳເລັດແລ້ວ)');
                  $table->date('queue_date')
                        ->comment('ວັນທີ່ມາກວດ');
                  // ສະຖານະຄິວ (ໃໝ່)
                  $table->enum('queue_status', [
                        'Registered',           // 1. ລົງທະບຽນແລ້ວ
                        'Vital_Checked',        // 2. ກວດ vital signs ແລ້ວ
                        'With_Doctor',          // 3. ຢູ່ກັບທ່ານໝໍ
                        'Waiting_Test_Results', // 4. ລໍຖ້າຜົນກວດ
                        'Results_Ready',        // 5a. ຜົນກວດພ້ອມ
                        'Ready_For_Payment',    // 5b. ພ້ອມຈ່າຍເງິນ
                        'Completed',            // 6. ສຳເລັດ
                        'Cancelled'             // ຍົກເລີກ
                  ])->default('Registered')->comment('ສະຖານະຄິວ');

                  // ຜູ້ອັບເດດ
                  $table->unsignedBigInteger('created_by')
                        ->comment('ຜູ້ສ້າງຄິວ');
                  $table->unsignedBigInteger('updated_by')->nullable()
                        ->comment('ຜູ້ອັບເດດຄັ້ງຫຼ້າສຸດ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->unique(['queue_date', 'queue_number']);
                  $table->index(['queue_date', 'queue_status']);
                  $table->index('waiting_number');

                  // Foreign Keys
                  $table->foreign('patient_id')->references('id')->on('patients');
                  $table->foreign('created_by')->references('id')->on('users');
                  $table->foreign('updated_by')->references('id')->on('users');
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
