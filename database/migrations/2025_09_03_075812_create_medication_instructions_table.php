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

                  $table->unsignedBigInteger('treatment_id')
                        ->comment('ລະຫັດການປິ່ນປົວ (Foreign Key)');
                  $table->unsignedBigInteger('medicine_id')
                        ->comment('ລະຫັດຢາ (Foreign Key)');

                  // ຂໍ້ມູນການສັ່ງຢາ
                  $table->string('dosage', 100)->nullable()
                        ->comment('ຂະໜາດຢາຕໍ່ເທື່ອ (500mg, 1 ແຜັດ)');
                  $table->string('frequency', 100)->nullable()
                        ->comment('ຄວາມຖີ່ (ວັນລະ 2 ເທື່ອ, ກິນຕອນເຊົ້າ)');
                  $table->string('duration', 100)->nullable()
                        ->comment('ໄລຍະເວລາ (7 ວັນ, 2 ອາທິດ)');
                  $table->integer('total_quantity')
                        ->comment('ຈຳນວນທັງໝົດທີ່ຕ້ອງການ');

                  // ຄຳແນະນຳການໃຊ້
                  $table->text('administration_route')->nullable()
                        ->comment('ວິທີການໃຊ້ (ກິນ, ທາ, ສັກ)');
                  $table->text('special_instructions')->nullable()
                        ->comment('ຄຳແນະນຳພິເສດ (ກິນກ່ອນອາຫານ, ຫຼັງອາຫານ)');
                  $table->text('warnings')->nullable()
                        ->comment('ຄຳເຕືອນ');

                  // ລາຄາ
                  $table->decimal('unit_price', 10, 2)
                        ->comment('ລາຄາຕໍ່ຫົວໜ່ວຍ');
                  $table->decimal('total_price', 10, 2)
                        ->comment('ລາຄາລວມ');

                  // ສະຖານະການຈ່າຍຢາ
                  $table->enum('dispensing_status', [
                        'Prescribed',  // ສັ່ງແລ້ວ
                        'Dispensed',   // ຈ່າຍແລ້ວ
                        'Cancelled'    // ຍົກເລີກ
                  ])->default('Prescribed')->comment('ສະຖານະການຈ່າຍຢາ');

                  $table->unsignedBigInteger('prescribed_by')
                        ->comment('ທ່ານໝໍທີ່ສັ່ງ');
                  $table->unsignedBigInteger('dispensed_by')->nullable()
                        ->comment('ເ຀ມີທີ່ຈ່າຍຢາ');
                  $table->timestamp('dispensed_at')->nullable()
                        ->comment('ເວລາທີ່ຈ່າຍຢາ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->index(['treatment_id', 'dispensing_status']);
                  $table->index('medicine_id');
                  $table->index('prescribed_by');
                  $table->index('dispensed_by');

                  // Foreign Keys
                  $table->foreign('treatment_id')->references('id')->on('treatments');
                  $table->foreign('medicine_id')->references('id')->on('medicines');
                  $table->foreign('prescribed_by')->references('id')->on('users');
                  $table->foreign('dispensed_by')->references('id')->on('users');
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
