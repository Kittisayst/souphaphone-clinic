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

                  // ຄວາມສຳພັນຫຼັກ
                  $table->unsignedBigInteger('treatment_id')
                        ->comment('ລະຫັດການປິ່ນປົວ (Foreign Key)');

                  // ລາຍການຄ່າໃຊ້ຈ່າຍ
                  $table->decimal('consultation_fee', 10, 2)->default(0)
                        ->comment('ຄ່າກວດ');
                  $table->decimal('lab_fees', 10, 2)->default(0)
                        ->comment('ຄ່າກວດ Lab');
                  $table->decimal('medication_fees', 10, 2)->default(0)
                        ->comment('ຄ່າຢາ');
                  $table->decimal('other_fees', 10, 2)->default(0)
                        ->comment('ຄ່າໃຊ້ຈ່າຍອື່ນໆ');

                  // ລວມ & ສ່ວນຫຼຸດ
                  $table->decimal('subtotal', 10, 2)
                        ->comment('ລວມຍ່ອຍ');
                  $table->decimal('discount_amount', 10, 2)->default(0)
                        ->comment('ຍອດສ່ວນຫຼຸດ');
                  $table->decimal('total_amount', 10, 2)
                        ->comment('ຍອດລວມສຸດທ້າຍ');

                  // ການຈ່າຍເງິນ
                  $table->enum('payment_method', ['Cash', 'Transfer', 'Card', 'Insurance'])
                        ->comment('ວິທີການຈ່າຍເງິນ');
                  $table->decimal('paid_amount', 10, 2)
                        ->comment('ຍອດທີ່ຈ່າຍ');
                  $table->decimal('change_amount', 10, 2)->default(0)
                        ->comment('ເງິນທອນ');

                  // ການບັນທຶກ
                  $table->unsignedBigInteger('cashier_id')
                        ->comment('ພະນັກງານເກັບເງິນ');
                  $table->timestamp('paid_at')->useCurrent()
                        ->comment('ເວລາຈ່າຍເງິນ');
                  $table->string('receipt_number', 20)->unique()
                        ->comment('ເລກທີ່ໃບເກັບເງິນ');

                  // ໝາຍເຫດ
                  $table->text('notes')->nullable()
                        ->comment('ໝາຍເຫດການຈ່າຍເງິນ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Foreign Keys
                  $table->foreign('treatment_id')->references('id')->on('treatments')
                        ->onDelete('cascade')->comment('ເຊື່ອມໂຍງກັບການປິ່ນປົວ');
                  $table->foreign('cashier_id')->references('id')->on('users')
                        ->comment('ເຊື່ອມໂຍງກັບພະນັກງານເກັບເງິນ');

                  // Indexes
                  $table->index('treatment_id', 'idx_treatment');
                  $table->index('receipt_number', 'idx_receipt_number');
                  $table->index(['cashier_id', 'paid_at'], 'idx_cashier_date');
                  $table->index('payment_method', 'idx_payment_method');
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
