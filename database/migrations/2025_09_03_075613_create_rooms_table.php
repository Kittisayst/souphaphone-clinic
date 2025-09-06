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
                  $table->string('room_code', 20)->unique()->comment('ລະຫັດຫ້ອງ (ເຊັ່ນ R001, XRAY01)');
                  $table->string('room_name', 100)->comment('ຊື່ຫ້ອງ');
                  $table->enum('room_type', [
                        'Consultation',  // ຫ້ອງກວດທົ່ວໄປ
                        'X_Ray',        // ຫ້ອງ X-Ray
                        'Ultrasound',   // ຫ້ອງອຸນຕາຊາວ
                        'Laboratory',   // ຫ້ອງແລັບ
                        'General',      // ຫ້ອງທົ່ວໄປ
                        'Procedure',    // ຫ້ອງຫັດຖະກຳ
                        'Recovery'      // ຫ້ອງພັກຟື້ນ
                  ])->comment('ປະເພດຫ້ອງ');

                  $table->integer('capacity')->default(1)->comment('ຄວາມຈຸຄົນໃນຫ້ອງ');
                  $table->text('equipment_list')->nullable()->comment('ລາຍການອຸປະກອນໃນຫ້ອງ');
                  $table->boolean('is_available')->default(true)->comment('ສະຖານະວ່າງ: true=ວ່າງ, false=ບໍ່ວ່າງ');
                  $table->unsignedBigInteger('current_user_id')->nullable()->comment('ຜູ້ໃຊ້ຫ້ອງໃນປັດຈຸບັນ');
                  $table->text('notes')->nullable()->comment('ໝາຍເຫດເພີ່ມເຕີມ');
                  $table->integer('version')->default(1)->comment('ເລກເວີເຊີນສຳລັບ optimistic locking');

                  $table->timestamps();
                  $table->softDeletes();

                  // Foreign Keys
                  $table->foreign('current_user_id')->references('id')->on('users')->onDelete('set null');

                  // Indexes
                  $table->index('room_code');
                  $table->index(['room_type', 'is_available']);
                  $table->index('is_available');
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
