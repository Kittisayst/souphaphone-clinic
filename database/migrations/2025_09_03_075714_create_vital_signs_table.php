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
        Schema::create('vital_signs', function (Blueprint $table) {
           $table->id()->comment('ລະຫັດການກວດເບື້ອງຕົ້ນ (Primary Key)');
            $table->unsignedBigInteger('queue_id')
                  ->comment('ລະຫັດຄິວ (Foreign Key)');
            $table->decimal('temperature', 4, 1)->nullable()
                  ->comment('ອຸນຫະພູມຮ່າງກາຍ (°C - ເຊັ່ນ 36.5)');
            $table->decimal('weight', 5, 2)->nullable()
                  ->comment('ນ້ຳໜັກ (kg - ເຊັ່ນ 65.50)');
            $table->decimal('height', 5, 2)->nullable()
                  ->comment('ຄວາມສູງ (cm - ເຊັ່ນ 170.00)');
            $table->integer('blood_pressure_sys')->nullable()
                  ->comment('ຄວາມດັນເລືອດຕົວເທິງ/Systolic (mmHg)');
            $table->integer('blood_pressure_dia')->nullable()
                  ->comment('ຄວາມດັນເລືອດຕົວລຸ່ມ/Diastolic (mmHg)');
            $table->integer('heart_rate')->nullable()
                  ->comment('ການເຕັ້ນຂອງຫົວໃຈ (ຄັ້ງ/ນາທີ)');
            $table->unsignedBigInteger('recorded_by')
                  ->comment('ຜູ້ບັນທຶກ (ພະຍາບານ ຫຼື ແອັດມິນ)');
            $table->text('notes')->nullable()
                  ->comment('ໝາຍເຫດເພີ່ມເຕີມຈາກການກວດ');
            $table->timestamps(); // created_at = ເວລາກວດ, updated_at
            $table->softDeletes(); // deleted_at

            // Foreign Keys
            $table->foreign('queue_id')->references('id')->on('queues')->onDelete('cascade')
                  ->comment('ເຊື່ອມໂຍງກັບຄິວ - ລືບຄິວແລ້ວຈະລືບການກວດດ້ວຍ');
            $table->foreign('recorded_by')->references('id')->on('users')
                  ->comment('ເຊື່ອມໂຍງກັບຜູ້ບັນທຶກ');

            // Indexes
            $table->unique('queue_id')->comment('1 ຄິວມີ 1 ການກວດເບື້ອງຕົ້ນເທົ່ານັ້ນ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
