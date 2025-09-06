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
            Schema::create('medication_instructions', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດການສັ່ງຢາ (Primary Key)');

                  // ຄວາມສຳພັນຫຼັກ
                  $table->unsignedBigInteger('treatment_id')
                        ->comment('ລະຫັດການປິ່ນປົວ (Foreign Key)');
                  $table->unsignedBigInteger('medicine_id')
                        ->comment('ລະຫັດຢາ (Foreign Key)');

                  // ຄຳສັ່ງການໃຊ້ຢາ
                  $table->string('dosage', 100)->nullable()
                        ->comment('ຂະໜາດຢາຕໍ່ເທື່ອ (500mg, 1 ແຜັດ)');
                  $table->string('frequency', 100)->nullable()
                        ->comment('ຄວາມຖີ່ (ວັນລະ 2 ເທື່ອ, ກິນຕອນເຊົ້າ-ແລງ)');
                  $table->string('duration', 100)->nullable()
                        ->comment('ໄລຍະເວລາ (5 ວັນ, 1 ສັບປະດາ)');
                  $table->decimal('quantity', 8, 2)
                        ->comment('ຈຳນວນທີ່ສັ່ງ');
                  $table->text('instructions')->nullable()
                        ->comment('ຄຳແນະນຳການໃຊ້ຢາ');

                  // ລາຄາ
                  $table->decimal('unit_price', 8, 2)->nullable()
                        ->comment('ລາຄາຕໍ່ຫົວໜ່ວຍ');
                  $table->decimal('total_price', 10, 2)->nullable()
                        ->comment('ລາຄາລວມ');

                  // ການສັ່ງຢາ
                  $table->unsignedBigInteger('prescribed_by')
                        ->comment('ທ່ານໝໍທີ່ສັ່ງຢາ');
                  $table->timestamp('prescribed_at')->useCurrent()
                        ->comment('ເວລາສັ່ງຢາ');

                  // ການຈ່າຍຢາ
                  $table->unsignedBigInteger('dispensed_by')->nullable()
                        ->comment('ຜູ້ຈ່າຍຢາ');
                  $table->decimal('dispensed_quantity', 8, 2)->nullable()
                        ->comment('ຈຳນວນທີ່ຈ່າຍຈິງ');
                  $table->timestamp('dispensed_at')->nullable()
                        ->comment('ເວລາຈ່າຍຢາ');

                  // ສະຖານະ
                  $table->enum('status', [
                        'Prescribed',   // ສັ່ງແລ້ວ
                        'Dispensed',    // ຈ່າຍແລ້ວ
                        'Cancelled'     // ຍົກເລີກ
                  ])->default('Prescribed')
                        ->comment('ສະຖານະການສັ່ງຢາ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Foreign Keys
                  $table->foreign('treatment_id')->references('id')->on('treatments')
                        ->onDelete('cascade')->comment('ເຊື່ອມໂຍງກັບການປິ່ນປົວ');
                  $table->foreign('medicine_id')->references('id')->on('medicines')
                        ->comment('ເຊື່ອມໂຍງກັບຂໍ້ມູນຢາ');
                  $table->foreign('prescribed_by')->references('id')->on('users')
                        ->comment('ເຊື່ອມໂຍງກັບທ່ານໝໍທີ່ສັ່ງຢາ');
                  $table->foreign('dispensed_by')->references('id')->on('users')
                        ->onDelete('set null')->comment('ເຊື່ອມໂຍງກັບຜູ້ຈ່າຍຢາ');

                  // Indexes
                  $table->index(['treatment_id', 'status'], 'idx_treatment_status');
                  $table->index('prescribed_by', 'idx_prescribed_by');
                  $table->index(['medicine_id', 'created_at'], 'idx_medicine_date');
                  $table->index(['dispensed_by', 'dispensed_at'], 'idx_dispensed');
            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('medication_instructions');
      }
};
