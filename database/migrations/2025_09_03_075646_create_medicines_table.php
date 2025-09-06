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
            Schema::create('medicines', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດຢາ (Primary Key)');

                  $table->string('medicine_code', 50)->unique()
                        ->comment('ລະຫັດຢາ (MED001, PARA500)');
                  $table->string('medicine_name', 200)
                        ->comment('ຊື່ຢາ');
                  $table->string('generic_name', 200)->nullable()
                        ->comment('ຊື່ສານເຄມີ');
                  $table->string('brand_name', 200)->nullable()
                        ->comment('ຊື່ຍີ່ຫໍ້');

                  // ປະເພດແລະຮູບແບບ
                  $table->enum('medicine_type', [
                        'Tablet',     // ເມັດ
                        'Capsule',    // ແຄັບຊູນ
                        'Syrup',      // ນ້ຳຢາ
                        'Injection',  // ຢາສັກ
                        'Cream',      // ຄຣີມ
                        'Drops',      // ຢາຫຍົດ
                        'Spray',      // ສະເປ
                        'Other'       // ອື່ນໆ
                  ])->comment('ປະເພດຢາ');

                  $table->string('strength', 50)->nullable()
                        ->comment('ຄວາມແຮງ (500mg, 250mg/5ml)');
                  $table->string('unit', 20)->nullable()
                        ->comment('ຫົວໜ່ວຍ (mg, ml, tablet)');

                  // ລາຄາ
                  $table->decimal('unit_price', 10, 2)->default(0)
                        ->comment('ລາຄາຕໍ່ຫົວໜ່ວຍ');
                  $table->decimal('wholesale_price', 10, 2)->default(0)
                        ->comment('ລາຄາຊື້ເຂົ້າ');

                  // ສາງເກັບ
                  $table->integer('current_stock')->default(0)
                        ->comment('ສາງປັດຈຸບັນ');
                  $table->integer('minimum_stock')->default(0)
                        ->comment('ສາງຕ່ຳສຸດ');
                  $table->integer('maximum_stock')->default(0)
                        ->comment('ສາງສູງສຸດ');

                  // ວັນໝົດອາຍຸ
                  $table->date('expiry_date')->nullable()
                        ->comment('ວັນໝົດອາຍຸ');
                  $table->string('batch_number', 50)->nullable()
                        ->comment('ເລກ batch');

                  // ຄຳແນະນຳ
                  $table->text('dosage_instructions')->nullable()
                        ->comment('ວິທີການກິນ/ໃຊ້');
                  $table->text('side_effects')->nullable()
                        ->comment('ຜົນຂ້າງຄຽງ');
                  $table->text('contraindications')->nullable()
                        ->comment('ຂໍ້ຫ້າມໃຊ້');

                  // ຜູ້ຜະລິດ
                  $table->string('manufacturer', 200)->nullable()
                        ->comment('ບໍລິສັດຜູ້ຜະລິດ');
                  $table->string('supplier', 200)->nullable()
                        ->comment('ຜູ້ສະໜອງ');

                  // ສະຖານະ
                  $table->boolean('requires_prescription')->default(false)
                        ->comment('ຕ້ອງການໃບສັ່ງຢາບໍ່');
                  $table->boolean('is_active')->default(true)
                        ->comment('ສະຖານະການໃຊ້ງານ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->index('medicine_code');
                  $table->index(['medicine_name', 'is_active']);
                  $table->index('medicine_type');
                  $table->index('current_stock');
                  $table->index('expiry_date');
            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('medicines');
      }
};
