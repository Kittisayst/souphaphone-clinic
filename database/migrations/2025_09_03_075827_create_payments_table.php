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

                  $table->unsignedBigInteger('treatment_id')
                        ->comment('ລະຫັດການປິ່ນປົວ (Foreign Key)');

                  // ລາຍການທີ່ຈ່າຍເງິນ
                  $table->json('payment_items')
                        ->comment('ລາຍການທີ່ຈ່າຍເງິນ (JSON)');

                  // ຈຳນວນເງິນ
                  $table->decimal('subtotal_amount', 10, 2)
                        ->comment('ກອນໂທນລວມ');
                  $table->decimal('discount_amount', 10, 2)->default(0)
                        ->comment('ຈຳນວນສ່ວນຫຼຸດ');
                  $table->decimal('tax_amount', 10, 2)->default(0)
                        ->comment('ຈຳນວນພາສີ');
                  $table->decimal('total_amount', 10, 2)
                        ->comment('ກອນໂທນລວມສຸດທ້າຍ');

                  // ການຈ່າຍເງິນ
                  $table->enum('payment_method', [
                        'Cash',      // ເງິນສົດ
                        'Transfer',  // ໂອນເງິນ
                        'Card',      // ບັດເຄຣດິດ
                        'Insurance'  // ປະກັນໄພ
                  ])->comment('ວິທີການຈ່າຍເງິນ');

                  $table->decimal('paid_amount', 10, 2)
                        ->comment('ກອນໂທນທີ່ຈ່າຍ');
                  $table->decimal('change_amount', 10, 2)->default(0)
                        ->comment('ເງິນທອນ');

                  // ສະຖານະການຈ່າຍເງິນ
                  $table->enum('payment_status', [
                        'Pending',   // ລໍຖ້າ
                        'Paid',      // ຈ່າຍແລ້ວ
                        'Refunded',  // ຄືນເງິນແລ້ວ
                        'Cancelled'  // ຍົກເລີກ
                  ])->default('Pending')->comment('ສະຖານະການຈ່າຍເງິນ');

                  // ຜູ້ດຳເນີນການ
                  $table->unsignedBigInteger('cashier_id')
                        ->comment('ພະນັກງານເກັບເງິນ');
                  $table->timestamp('paid_at')->nullable()
                        ->comment('ເວລາຈ່າຍເງິນ');

                  // ໃບເກັບເງິນ
                  $table->string('receipt_number', 20)->unique()
                        ->comment('ເລກທີ່ໃບເກັບເງິນ');
                  $table->text('notes')->nullable()
                        ->comment('ໝາຍເຫດການຈ່າຍເງິນ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->index('treatment_id');
                  $table->index(['cashier_id', 'paid_at']);
                  $table->index('payment_method');
                  $table->index('payment_status');

                  // Foreign Keys
                  $table->foreign('treatment_id')->references('id')->on('treatments');
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
