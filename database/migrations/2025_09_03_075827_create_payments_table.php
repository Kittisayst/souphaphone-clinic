<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id()->comment('ລະຫັດການຈ່າຍເງິນ (Primary Key)');
            $table->unsignedBigInteger('queue_id')
                  ->comment('ລະຫັດຄິວ (Foreign Key) - 1 ຄິວມີ 1 ການຈ່າຍເງິນ');
            $table->decimal('service_total', 10, 2)->default(0)
                  ->comment('ລວມຄ່າບໍລິການທັງໝົດ (ກີບ)');
            $table->decimal('medicine_total', 10, 2)->default(0)
                  ->comment('ລວມຄ່າຢາທັງໝົດ (ກີບ)');
            $table->decimal('subtotal', 10, 2)
                  ->comment('ຍອດລວມກ່ອນຫັກສ່ວນຫຼຸດ ແລະ ພາສີ (service_total + medicine_total)');
            $table->decimal('discount_amount', 10, 2)->default(0)
                  ->comment('ຈຳນວນເງິນສ່ວນຫຼຸດ (ກີບ)');
            $table->decimal('tax_amount', 10, 2)->default(0)
                  ->comment('ຈຳນວນພາສີ VAT (ກີບ)');
            $table->decimal('final_amount', 10, 2)
                  ->comment('ຍອດລວມສຸດທ້າຍທີ່ຄົນໄຂ້ຕ້ອງຈ່າຍ (ກີບ)');
            $table->enum('payment_method', ['Cash', 'Card', 'Transfer', 'Insurance'])->nullable()
                  ->comment('ວິທີການຈ່າຍເງິນ: ເງິນສົດ, ບັດ, ໂອນ, ປະກັນສຸຂະພາບ');
            $table->enum('payment_status', ['Pending', 'Paid', 'Refunded'])->default('Pending')
                  ->comment('ສະຖານະການຈ່າຍເງິນ: ລໍຖ້າຈ່າຍ, ຈ່າຍແລ້ວ, ຄືນເງິນ');
            $table->timestamp('paid_at')->nullable()
                  ->comment('ເວລາທີ່ຈ່າຍເງິນ');
            $table->unsignedBigInteger('received_by')->nullable()
                  ->comment('ຜູ້ຮັບເງິນ (ພະນັກງານການເງິນ)');
            $table->string('receipt_number', 50)->nullable()->unique()
                  ->comment('ເລກທີ່ໃບຮັບເງິນ');
            $table->json('payment_details')->nullable()
                  ->comment('ລາຍລະອຽດການຄຳນວນເງິນ (breakdown ຂອງແຕ່ລະໜ່ວຍ)');
            $table->text('notes')->nullable()
                  ->comment('ໝາຍເຫດການຈ່າຍເງິນ');
            $table->timestamps(); // created_at = ເວລາສ້າງບິນ, updated_at
            $table->softDeletes(); // deleted_at

            // Foreign Keys
            $table->foreign('queue_id')->references('id')->on('queues')->onDelete('cascade')
                  ->comment('ເຊື່ອມໂຍງກັບຄິວ');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null')
                  ->comment('ເຊື່ອມໂຍງກັບຜູ້ຮັບເງິນ');

            // Indexes
            $table->unique('queue_id')->comment('1 ຄິວມີ 1 ການຈ່າຍເງິນເທົ່ານັ້ນ');
            $table->index(['payment_status', 'paid_at'])->comment('Index ສຳລັບລາຍງານການເງິນ');
            $table->index('receipt_number')->comment('Index ສຳລັບຄົ້ນຫາໃບຮັບເງິນ');
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
