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
            Schema::create('medications', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດການສັ່ງຢາ (Primary Key)');

                  $table->unsignedBigInteger('queue_id')
                        ->comment('ລະຫັດຄິວ (Foreign Key)');
                  $table->unsignedBigInteger('medicine_id')
                        ->comment('ລະຫັດຢາ (Foreign Key)');

                  $table->integer('qty')
                        ->comment('ຈຳນວນທັງໝົດທີ່ຕ້ອງການ');
                  // ລາຄາ
                  $table->decimal('total_price', 10, 2)
                        ->comment('ລາຄາລວມ');
                  $table->decimal('unit_price', 10, 2)
                        ->comment('ລາຄາຢາ');

                  // ຄຳແນະນຳການໃຊ້
                  $table->text('notes')->nullable()
                        ->comment('ຄຳແນະນຳພິເສດ (ກິນກ່ອນອາຫານ, ຫຼັງອາຫານ)');

                  $table->unsignedBigInteger('created_by')
                        ->comment('ທ່ານໝໍທີ່ສັ່ງ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->index('queue_id');
                  $table->index('medicine_id');
                  $table->index('created_by');

                  // Foreign Keys
                  $table->foreign('queue_id')->references('id')->on('queues')->onDelete('cascade');
                  $table->foreign('medicine_id')->references('id')->on('medicines');
                  $table->foreign('created_by')->references('id')->on('users');
            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('medication');
      }
};
