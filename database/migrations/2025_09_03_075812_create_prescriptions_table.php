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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id()->comment('ລະຫັດໃບສັ່ງຢາ (Primary Key)');
            $table->unsignedBigInteger('queue_id')
                  ->comment('ລະຫັດຄິວ (Foreign Key)');
            $table->unsignedBigInteger('medicine_id')
                  ->comment('ລະຫັດຢາ (Foreign Key)');
            $table->string('dosage', 100)->nullable()
                  ->comment('ຂະໜາດຢາຕໍ່ເທື່ອ (ເຊັ່ນ 500mg, 1 ແຜັດ)');
            $table->string('frequency', 100)->nullable()
                  ->comment('ຄວາມຖີ່ໃນການກິນ (ເຊັ່ນ ວັນລະ 2 ເທື່ອ, ກິນຕອນເຊົ້າ-ແລງ)');
            $table->string('duration', 100)->nullable()
                  ->comment('ໄລຍະເວລາກິນຢາ (ເຊັ່ນ 5 ວັນ, 1 ສັບປະດາ)');
            $table->decimal('quantity', 8, 2)
                  ->comment('ຈຳນວນຢາທີ່ສັ່ງ (ຕາມຫົວໜ່ວຍຂອງຢານັ້ນ)');
            $table->text('instructions')->nullable()
                  ->comment('ວິທີການໃຊ້ຢາ ແລະ ຂໍ້ແນະນຳ');
            $table->unsignedBigInteger('prescribed_by')
                  ->comment('ທ່ານໝໍທີ່ສັ່ງຢາ');
            $table->unsignedBigInteger('dispensed_by')->nullable()
                  ->comment('ຜູ້ຈ່າຍຢາໃຫ້ຄົນໄຂ້');
            $table->decimal('dispensed_quantity', 8, 2)->nullable()
                  ->comment('ຈຳນວນຢາທີ່ຈ່າຍຈິງ');
            $table->timestamp('dispensed_at')->nullable()
                  ->comment('ເວລາທີ່ຈ່າຍຢາ');
            $table->decimal('unit_price', 8, 2)->nullable()
                  ->comment('ລາຄາຕໍ່ຫົວໜ່ວຍ ໃນເວລາສັ່ງ (ກີບ)');
            $table->decimal('total_price', 10, 2)->nullable()
                  ->comment('ລາຄາລວມຂອງຢາຊະນິດນີ້ (quantity × unit_price)');
            $table->enum('status', ['Prescribed', 'Dispensed', 'Cancelled'])
                  ->default('Prescribed')->comment('ສະຖານະໃບສັ່ງຢາ: ສັ່ງແລ້ວ, ຈ່າຍແລ້ວ, ຍົກເລີກ');
            $table->timestamps(); // created_at = ເວລາສັ່ງຢາ, updated_at
            $table->softDeletes(); // deleted_at

            // Foreign Keys
            $table->foreign('queue_id')->references('id')->on('queues')->onDelete('cascade')
                  ->comment('ເຊື່ອມໂຍງກັບຄິວ');
            $table->foreign('medicine_id')->references('id')->on('medicines')
                  ->comment('ເຊື່ອມໂຍງກັບຂໍ້ມູນຢາ');
            $table->foreign('prescribed_by')->references('id')->on('users')
                  ->comment('ເຊື່ອມໂຍງກັບທ່ານໝໍທີ່ສັ່ງຢາ');
            $table->foreign('dispensed_by')->references('id')->on('users')->onDelete('set null')
                  ->comment('ເຊື່ອມໂຍງກັບຜູ້ຈ່າຍຢາ');

            // Indexes
            $table->index(['queue_id', 'status'])->comment('Index ສຳລັບຄົ້ນຫາໃບສັ່ງຢາຕາມຄິວແລະສະຖານະ');
            $table->index('prescribed_by')->comment('Index ສຳລັບຄົ້ນຫາໃບສັ່ງຢາຂອງທ່ານໝໍ');
            $table->index('medicine_id')->comment('Index ສຳລັບສະຖິຕິການໃຊ້ຢາ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
