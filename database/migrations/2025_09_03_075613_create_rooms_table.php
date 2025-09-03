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
        Schema::create('rooms', function (Blueprint $table) {
             $table->id()->comment('ລະຫັດຫ້ອງ (Primary Key)');
            $table->string('room_code', 20)->unique()
                  ->comment('ລະຫັດຫ້ອງ (ເຊັ່ນ R001, XRAY01)');
            $table->string('room_name', 100)->comment('ຊື່ຫ້ອງ');
            $table->enum('room_type', ['Consultation', 'X_Ray', 'Ultrasound', 'Laboratory', 'General'])
                  ->comment('ປະເພດຫ້ອງ: ກວດທົ່ວໄປ, X-Ray, Ultrasound, ແລັບ, ທົ່ວໄປ');
            $table->integer('capacity')->default(1)
                  ->comment('ຄວາມຈຸຄົນໃນຫ້ອງ');
            $table->text('equipment_list')->nullable()
                  ->comment('ລາຍການອຸປະກອນໃນຫ້ອງ');
            $table->boolean('is_available')->default(true)
                  ->comment('ສະຖານະວ່າງ: true=ວ່າງ, false=ບໍ່ວ່າງ');
            $table->unsignedBigInteger('current_user_id')->nullable()
                  ->comment('ຜູ້ໃຊ້ຫ້ອງໃນປັດຈຸບັນ');
            $table->text('notes')->nullable()->comment('ໝາຍເຫດເພີ່ມເຕີມ');
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            // Foreign Keys
            $table->foreign('current_user_id')->references('id')->on('users')->onDelete('set null')
                  ->comment('ເຊື່ອມໂຍງກັບຜູ້ໃຊ້ທີ່ກຳລັງໃຊ້ຫ້ອງ');

            // Indexes
            $table->index('room_code')->comment('Index ສຳລັບຄົ້ນຫາດ້ວຍລະຫັດຫ້ອງ');
            $table->index(['room_type', 'is_available'])->comment('Index ສຳລັບຄົ້ນຫາຫ້ອງວ່າງຕາມປະເພດ');
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
