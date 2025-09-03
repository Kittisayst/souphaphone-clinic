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
        Schema::create('treatments', function (Blueprint $table) {
           $table->id()->comment('ລະຫັດການປິ່ນປົວ (Primary Key)');
            $table->unsignedBigInteger('queue_service_id')
                  ->comment('ລະຫັດບໍລິການໃນຄິວ (Foreign Key)');
            $table->unsignedBigInteger('room_id')->nullable()
                  ->comment('ຫ້ອງທີ່ໃຊ້ໃນການກວດ');
            $table->unsignedBigInteger('performed_by')
                  ->comment('ຜູ້ເຮັດການກວດ/ປິ່ນປົວ (ທ່ານໝໍ ຫຼື ຜູ້ຊ່ຽວຊານ)');
            $table->timestamp('treatment_started_at')->nullable()
                  ->comment('ເວລາເລີ່ມການປິ່ນປົວ/ກວດ');
            $table->timestamp('treatment_ended_at')->nullable()
                  ->comment('ເວລາສິ້ນສຸດການປິ່ນປົວ/ກວດ');
            $table->text('examination_notes')->nullable()
                  ->comment('ບັນທຶກການກວດ/ການສັງເກດ');
            $table->text('findings')->nullable()
                  ->comment('ສິ່ງທີ່ພົບຈາກການກວດ');
            $table->text('recommendations')->nullable()
                  ->comment('ຂໍ້ແນະນຳ/ການປິ່ນປົວ');
            $table->enum('status', ['In_Progress', 'Completed', 'Cancelled'])
                  ->default('In_Progress')->comment('ສະຖານະການປິ່ນປົວ');
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            // Foreign Keys
            $table->foreign('queue_service_id')->references('id')->on('queue_services')->onDelete('cascade')
                  ->comment('ເຊື່ອມໂຍງກັບບໍລິການໃນຄິວ');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null')
                  ->comment('ເຊື່ອມໂຍງກັບຫ້ອງທີ່ໃຊ້');
            $table->foreign('performed_by')->references('id')->on('users')
                  ->comment('ເຊື່ອມໂຍງກັບຜູ້ເຮັດການປິ່ນປົວ');

            // Indexes
            $table->index(['performed_by', 'treatment_started_at'])->comment('Index ສຳລັບຄົ້ນຫາການປິ່ນປົວຂອງແຕ່ລະຄົນ');
            $table->index('status')->comment('Index ສຳລັບຄົ້ນຫາຕາມສະຖານະ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
