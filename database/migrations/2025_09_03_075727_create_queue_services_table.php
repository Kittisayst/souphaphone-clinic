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

                  // ຜູ້ຮັບຜິດຊອບ
                  $table->unsignedBigInteger('added_by_id')
                        ->comment('ຜູ້ເພີ່ມບໍລິການ (ພະນັກງານຮັບບ້ານ ຫຼື ທ່ານໝໍ)');
                  $table->unsignedBigInteger('assigned_to_id')->nullable()
                        ->comment('ຜູ້ທີ່ຖືກມອບໝາຍໃຫ້ເຮັດບໍລິການນີ້');

                  // ສະຖານະບໍລິການ
                  $table->enum('service_status', [
                        'Added',        // ເພີ່ມແລ້ວ
                        'In_Progress',  // ກຳລັງເຮັດ
                        'Completed',    // ສຳເລັດແລ້ວ
                        'Cancelled'     // ຍົກເລີກ
                  ])->default('Added')->comment('ສະຖານະຂອງບໍລິການໃນຄິວນີ້');

                  // ຫ້ອງທີ່ມອບໝາຍ (ອັດຕະໂນມັດຈາກ service.room_id)
                  $table->unsignedBigInteger('assigned_room_id')->nullable()
                        ->comment('ຫ້ອງທີ່ຖືກມອບໝາຍສຳລັບບໍລິການນີ້');

                  // Timestamps
                  $table->timestamp('started_at')->nullable()
                        ->comment('ເວລາທີ່ເລີ່ມເຮັດບໍລິການ');
                  $table->timestamp('completed_at')->nullable()
                        ->comment('ເວລາທີ່ສຳເລັດບໍລິການ');
                  $table->integer('actual_duration')->nullable()
                        ->comment('ເວລາທີ່ໃຊ້ຈິງ (ນາທີ)');

                  // ລາຍລະອຽດ
                  $table->text('notes')->nullable()
                        ->comment('ໝາຍເຫດສຳລັບບໍລິການນີ້');
                  $table->json('service_details')->nullable()
                        ->comment('ລາຍລະອຽດເພີ່ມເຕີມ (JSON)');

                  // ລາຄາ
                  $table->decimal('service_price', 10, 2)->nullable()
                        ->comment('ລາຄາບໍລິການ (ອາດແຕກຕ່າງຈາກລາຄາມາດຕະຖານ)');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->unique(['queue_id', 'service_id'], 'unique_queue_service');
                  $table->index(['queue_id', 'service_status']);
                  $table->index(['assigned_to_id', 'service_status']);
                  $table->index(['service_status', 'created_at']);
                  $table->index('assigned_room_id');

                  // Foreign Keys
                  $table->foreign('queue_id')->references('id')->on('queues')->onDelete('cascade');
                  $table->foreign('service_id')->references('id')->on('services');
                  $table->foreign('added_by_id')->references('id')->on('users');
                  $table->foreign('assigned_to_id')->references('id')->on('users');
                  $table->foreign('assigned_room_id')->references('id')->on('rooms');
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
