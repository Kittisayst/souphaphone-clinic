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
                  $table->string('service_code', 20)->unique()->comment('ລະຫັດບໍລິການ (ເຊັ່ນ CONS01, XRAY01)');
                  $table->string('service_name', 200)->comment('ຊື່ບໍລິການ');
                  $table->enum('service_category', [
                        'Consultation',  // ການປຶກສາ
                        'X_Ray',        // ຖ່າຍ X-Ray
                        'Ultrasound',   // ອຸນຕາຊາວ
                        'Blood_Test',   // ກວດເລືອດ
                        'Urine_Test',   // ກວດປັດສະວະ
                        'ECG',          // ວັດຫົວໃຈ
                        'Laboratory',   // ແລັບອື່ນໆ
                        'Treatment',    // ການປິ່ນປົວ
                        'Imaging',      // ຖ່າຍພາບວິນິໄຈ
                        'Other'         // ອື່ນໆ
                  ])->comment('ປະເພດບໍລິການ');

                  $table->decimal('base_price', 10, 2)->nullable()->comment('ລາຄາພື້ນຖານ (ກີບ)');
                  $table->text('description')->nullable()->comment('ຄຳອະທິບາຍບໍລິການ');
                  $table->integer('duration_minutes')->nullable()->comment('ໄລຍະເວລາປະມານ (ນາທີ)');
                  $table->boolean('requires_room')->default(false)->comment('ຕ້ອງການຫ້ອງພິເສດ: true=ຕ້ອງການ, false=ບໍ່ຕ້ອງການ');
                  $table->string('room_type_required', 50)->nullable()->comment('ປະເພດຫ້ອງທີ່ຕ້ອງການ (ຖ້າ requires_room = true)');
                  $table->json('template_lab')->nullable()->comment('ແມ່ແບບຜົນກວດ (JSON Schema format)');
                  $table->boolean('has_lab_result')->default(false)->comment('ມີຜົນກວດບໍ່: true=ມີ, false=ບໍ່ມີ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->index('service_code');
                  $table->index(['service_category', 'deleted_at']);
                  $table->index('requires_room');
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
