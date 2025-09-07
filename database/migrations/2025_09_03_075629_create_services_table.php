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

                  $table->decimal('price', 10, 2)->nullable()
                        ->comment('ລາຄາພື້ນຖານ (ກີບ)');
                  $table->integer('duration_minutes')->nullable()
                        ->comment('ໄລຍະເວລາປະມານ (ນາທີ)');

                  $table->boolean('is_active')->default(true)
                        ->comment('ສະຖານະການໃຊ້ງານ');
                  $table->text('notes')->nullable()
                        ->comment('ຄຳອະທິບາຍບໍລິການ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->index('service_code');
                  $table->index(['service_category', 'is_active']);
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
