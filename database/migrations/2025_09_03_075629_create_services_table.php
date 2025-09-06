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
            Schema::create('services', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດບໍລິການ (Primary Key)');

                  $table->string('service_code', 20)->unique()
                        ->comment('ລະຫັດບໍລິການ (CONS01, LAB01, XRAY01)');
                  $table->string('service_name', 200)
                        ->comment('ຊື່ບໍລິການ');
                  $table->enum('service_category', [
                        'Consultation',   // ປຶກສາ
                        'Laboratory',     // ແລັບ
                        'X_Ray',         // X-Ray
                        'Ultrasound',    // ອັນຕາຊາວ
                        'Blood_Test',    // ກວດເລືອດ
                        'Urine_Test',    // ກວດປັດສະວະ
                        'ECG',           // ເອັກຊີຈີ
                        'Treatment',     // ການຮັກສາ
                        'Imaging',       // ຖ່າຍພາບ
                        'Pharmacy',      // ຢາ
                        'Other'          // ອື່ນໆ
                  ])->comment('ປະເພດບໍລິການ');

                  $table->decimal('base_price', 10, 2)->nullable()
                        ->comment('ລາຄາພື້ນຖານ (ກີບ)');
                  $table->text('description')->nullable()
                        ->comment('ຄຳອະທິບາຍບໍລິການ');
                  $table->integer('duration_minutes')->nullable()
                        ->comment('ໄລຍະເວລາປະມານ (ນາທີ)');

                  // ການເຊື່ອມໂຍງກັບຫ້ອງ (ໃໝ່)
                  $table->unsignedBigInteger('room_id')->nullable()
                        ->comment('ຫ້ອງທີ່ໃຊ້ເຮັດບໍລິການນີ້');

                  // Lab configuration
                  $table->boolean('has_lab_result')->default(false)
                        ->comment('ມີຜົນກວດບໍ່: true=ມີ, false=ບໍ່ມີ');
                  $table->json('lab_test_types')->nullable()
                        ->comment('ປະເພດການກວດ Lab (JSON array)');

                  $table->boolean('is_active')->default(true)
                        ->comment('ສະຖານະການໃຊ້ງານ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->index('service_code');
                  $table->index(['service_category', 'is_active']);
                  $table->index('has_lab_result');
                  $table->index('room_id');

                  // Foreign Keys
                  $table->foreign('room_id')->references('id')->on('rooms');
            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('services');
      }
};
