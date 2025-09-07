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
            Schema::create('rooms', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດຫ້ອງ (Primary Key)');

                  $table->string('room_code', 20)->unique()
                        ->comment('ລະຫັດຫ້ອງ (R001, LAB01, XRAY01)');
                  $table->string('room_name', 100)
                        ->comment('ຊື່ຫ້ອງ');
                  $table->enum('room_type', [
                        'Consultation',   // ຫ້ອງປຶກສາ
                        'Laboratory',     // ຫ້ອງແລັບ
                        'X_Ray',         // ຫ້ອງ X-Ray
                        'Ultrasound',    // ຫ້ອງອັນຕາຊາວ
                        'Procedure',     // ຫ້ອງຜ່າຕັດນ້ອຍ
                        'Pharmacy',      // ຫ້ອງຢາ
                        'Cashier',       // ຫ້ອງເກັບເງິນ
                        'General'        // ຫ້ອງທົ່ວໄປ
                  ])->comment('ປະເພດຫ້ອງ');

                  // ສະຖານະຫ້ອງ
                  $table->enum('room_status', [
                        'Available',     // ວ່າງ
                        'Occupied',      // ມີຄົນໃຊ້
                        'Testing',       // ກຳລັງກວດ
                        'Cleaning',      // ກຳລັງທຳຄວາມສະອາດ
                        'Maintenance'    // ບຳລຸງຮັກສາ
                  ])->default('Available')->comment('ສະຖານະຫ້ອງ');

                  //is_available
                  $table->boolean('is_available')->default(true)
                        ->comment('ສະຖານະພ້ອມໃຊ້ງານ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->index('room_code');
                  $table->index(['room_type', 'room_status']);

            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('rooms');
      }
};
