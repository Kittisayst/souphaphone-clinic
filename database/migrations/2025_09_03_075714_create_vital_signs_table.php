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
            Schema::create('vital_signs', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດການກວດເບື້ອງຕົ້ນ (Primary Key)');

                  $table->unsignedBigInteger('queue_id')
                        ->comment('ລະຫັດຄິວ (Foreign Key)');

                  // ຂໍ້ມູນ Vital Signs
                  $table->decimal('temperature', 4, 1)->nullable()
                        ->comment('ອຸນຫະພູມຮ່າງກາຍ (°C - ເຊັ່ນ 36.5)');
                  $table->decimal('weight', 5, 2)->nullable()
                        ->comment('ນ້ຳໜັກ (kg - ເຊັ່ນ 65.50)');
                  $table->decimal('height', 5, 2)->nullable()
                        ->comment('ຄວາມສູງ (cm - ເຊັ່ນ 170.00)');
                  $table->string('blood_pressure', 10)
                        ->nullable()
                        ->comment('ຄວາມດັນເລືອດ');
                  $table->integer('heart_rate')->nullable();

                  // ໝາຍເຫດ
                  $table->text('notes')->nullable()
                        ->comment('ໝາຍເຫດເພີ່ມເຕີມຈາກການກວດ');

                  // ຜູ້ບັນທຶກ
                  $table->unsignedBigInteger('created_by')
                        ->comment('ຜູ້ບັນທຶກ (ພະຍາບານ ຫຼື ແອັດມິນ)');


                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->unique('queue_id')->comment('1 ຄິວມີ 1 ການກວດເບື້ອງຕົ້ນເທົ່ານັ້ນ');
                  $table->index('created_by');

                  // Foreign Keys
                  $table->foreign('queue_id')->references('id')->on('queues')->onDelete('cascade');
                  $table->foreign('created_by')->references('id')->on('users');
            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('vital_signs');
      }
};
