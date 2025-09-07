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
            Schema::create('payments', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດການຈ່າຍເງິນ (Primary Key)');

                  $table->unsignedBigInteger('queue_id')
                        ->comment('ລະຫັດຄິວ (Foreign Key)');

                  $table->string('receipt_number', 20)->unique()
                        ->comment('ເລກທີ່ໃບເກັບເງິນ');
                  // ຈຳນວນເງິນ
                  $table->decimal('discount_amount', 10, 2)->default(0)
                        ->comment('ຈຳນວນສ່ວນຫຼຸດ');
                  $table->decimal('tax_amount', 10, 2)->default(0)
                        ->comment('ຈຳນວນພາສີ');
                  $table->decimal('total_queue_services', 10, 2)
                        ->comment('ລາຄາບໍລິການທັງໝົດ');
                  $table->decimal('total_medication', 10, 2)
                        ->comment('ລາຄາຢາທັງໝົດ');
                  $table->decimal('total_amount', 10, 2)
                        ->comment('ລາຄາລວມສຸດທ້າຍ');

                  // ການຈ່າຍເງິນ
                  $table->enum('payment_method', [
                        'Cash',      // ເງິນສົດ
                        'Transfer',  // ໂອນເງິນ
                        'Card',      // ບັດເຄຣດິດ
                        'Insurance'  // ປະກັນໄພ
                  ])->comment('ວິທີການຈ່າຍເງິນ');

                  // ສະຖານະການຈ່າຍເງິນ
                  $table->enum('payment_status', [
                        'Pending',   // ລໍຖ້າ
                        'Paid',      // ຈ່າຍແລ້ວ
                        'Refunded',  // ຄືນເງິນແລ້ວ
                        'Cancelled'  // ຍົກເລີກ
                  ])->default('Pending')->comment('ສະຖານະການຈ່າຍເງິນ');

                  $table->decimal('paid_amount', 10, 2)
                        ->comment('ກອນໂທນທີ່ຈ່າຍ');
                  $table->decimal('change_amount', 10, 2)->default(0)
                        ->comment('ເງິນທອນ');

                  // ຜູ້ດຳເນີນການ
                  $table->unsignedBigInteger('cashier_id')
                        ->comment('ພະນັກງານເກັບເງິນ');
                  $table->timestamp('paid_at')->nullable()
                        ->comment('ເວລາຈ່າຍເງິນ');

                  // ໃບເກັບເງິນ
                  $table->text('notes')->nullable()
                        ->comment('ໝາຍເຫດການຈ່າຍເງິນ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->index('queue_id');
                  $table->index(['cashier_id', 'paid_at']);
                  $table->index('payment_method');
                  $table->index('payment_status');

                  // Foreign Keys
                  $table->foreign('queue_id')->references('id')->on('queues')->onDelete('cascade');
                  $table->foreign('cashier_id')->references('id')->on('users');
            });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
            Schema::dropIfExists('payments');
      }
};
