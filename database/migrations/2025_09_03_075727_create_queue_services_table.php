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
            Schema::create('queue_services', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດຄິວບໍລິການ (Primary Key)');

                  $table->unsignedBigInteger('queue_id')
                        ->comment('ລະຫັດຄິວ (Foreign Key)');
                  $table->unsignedBigInteger('service_id')
                        ->comment('ລະຫັດບໍລິການ (Foreign Key)');
                  $table->unsignedBigInteger('room_id')->nullable()
                        ->comment('ລະຫັດຫ້ອງກວດ (Foreign Key)');

                  // ຜູ້ຮັບຜິດຊອບ
                  $table->unsignedBigInteger('doctor_id')->nullable()
                        ->comment('ຜູ້ທີ່ຖືກມອບໝາຍໃຫ້ເຮັດບໍລິການນີ້');

                  // Timestamps
                  $table->timestamp('started_at')->nullable()
                        ->comment('ເວລາທີ່ເລີ່ມເຮັດບໍລິການ');
                  $table->timestamp('completed_at')->nullable()
                        ->comment('ເວລາທີ່ສຳເລັດບໍລິການ');

                  // ສະຖານະບໍລິການ
                  $table->enum('service_status', [
                        'Added',        // ເພີ່ມແລ້ວ
                        'In_Progress',  // ກຳລັງເຮັດ
                        'Completed',    // ສຳເລັດແລ້ວ
                        'Cancelled'     // ຍົກເລີກ
                  ])->default('Added')->comment('ສະຖານະຂອງບໍລິການໃນຄິວນີ້');

                  // ລາຄາ
                  $table->decimal('service_price', 10, 2)->nullable()
                        ->comment('ລາຄາບໍລິການ (ອາດແຕກຕ່າງຈາກລາຄາມາດຕະຖານ)');

                  // ລາຍລະອຽດ
                  $table->text('notes')->nullable()
                        ->comment('ໝາຍເຫດສຳລັບບໍລິການນີ້');

                  $table->unsignedBigInteger('created_by')
                        ->comment('ຜູ້ເພີ່ມບໍລິການ (ພະນັກງານຮັບບ້ານ ຫຼື ທ່ານໝໍ)');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->unique(['queue_id', 'service_id'], 'unique_queue_service');
                  $table->index(['queue_id', 'service_status']);
                  $table->index(['doctor_id', 'service_status']);
                  $table->index(['service_status', 'created_at']);
                  $table->index('room_id');

                  // Foreign Keys
                  $table->foreign('queue_id')->references('id')->on('queues')->onDelete('cascade');
                  $table->foreign('service_id')->references('id')->on('services');
                  $table->foreign('room_id')->references('id')->on('rooms');
                  $table->foreign('doctor_id')->references('id')->on('users');
                  $table->foreign('created_by')->references('id')->on('users');
            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('queue_services');
      }
};
