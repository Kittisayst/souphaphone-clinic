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
        Schema::create('labs', function (Blueprint $table) {
            $table->id()->comment('ລະຫັດຜົນກວດ (Primary Key)');
            $table->unsignedBigInteger('queue_service_id')
                  ->comment('ລະຫັດບໍລິການໃນຄິວ (Foreign Key)');
            $table->string('lab_code', 30)->unique()
                  ->comment('ລະຫັດຜົນກວດ (ເຊັ່ນ LAB001, XRAY001)');
            $table->json('test_results')->nullable()
                  ->comment('ຜົນການກວດແບບ JSON ຕາມ template ຂອງແຕ່ລະບໍລິການ');
            $table->text('result_summary')->nullable()
                  ->comment('ສະຫຼຸບຜົນການກວດແບບຫຍໍ້');
            $table->text('reference_values')->nullable()
                  ->comment('ຄ່າອ້າງອີງປົກກະຕິ/ມາດຕະຖານ');
            $table->text('interpretation')->nullable()
                  ->comment('ການຕີຄວາມ/ການວິເຄາະຼົນ');
            $table->json('images_attachments')->nullable()
                  ->comment('ຮູບພາບຜົນກວດ ຫຼື ໄຟລ໌ແນບ (file paths)');
            $table->unsignedBigInteger('performed_by')->nullable()
                  ->comment('ຜູ້ເຮັດການກວດ (ຊ່າງແລັບ, ຊ່າງ X-Ray)');
            $table->timestamp('performed_at')->nullable()
                  ->comment('ເວລາທີ່ເຮັດການກວດ');
            $table->unsignedBigInteger('reviewed_by_doctor_id')->nullable()
                  ->comment('ທ່ານໝໍທີ່ເບິ່ງ/ອານຜົນກວດ');
            $table->timestamp('reviewed_at')->nullable()
                  ->comment('ເວລາທີ່ທ່ານໝໍເບິ່ງຼົນ');
            $table->boolean('patient_notified')->default(false)
                  ->comment('ແຈ້ງຼົນໃຫ້ຄົນໄຂ້ແລ້ວຫຼືຍັງ');
            $table->timestamp('notified_at')->nullable()
                  ->comment('ເວລາທີ່ແຈ້ງຼົນໃຫ້ຄົນໄຂ້');
            $table->enum('lab_status', [
                'Pending',          // ລໍຖ້າເຮັດການກວດ
                'In_Progress',      // ກຳລັງເຮັດການກວດ
                'Completed',        // ການກວດສຳເລັດ
                'Doctor_Reviewed',  // ທ່ານໝໍເບິ່ງແລ້ວ
                'Patient_Notified'  // ແຈ້ງຄົນໄຂ້ແລ້ວ
            ])->default('Pending')->comment('ສະຖານະຂອງຼົນກວດໃນແຕ່ລະຂັ້ນຕອນ');
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            // Foreign Keys  
            $table->foreign('queue_service_id')->references('id')->on('queue_services')->onDelete('cascade')
                  ->comment('ເຊື່ອມໂຍງກັບບໍລິການໃນຄິວ');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null')
                  ->comment('ເຊື່ອມໂຍງກັບຜູ້ເຮັດການກວດ');
            $table->foreign('reviewed_by_doctor_id')->references('id')->on('users')->onDelete('set null')
                  ->comment('ເຊື່ອມໂຍງກັບທ່ານໝໍທີ່ເບິ່ງຼົນ');

            // Indexes
            $table->index('lab_code')->comment('Index ສຳລັບຄົ້ນຫາດ້ວຍລະຫັດຼົນກວດ');
            $table->index(['lab_status', 'performed_at'])->comment('Index ສຳລັບຄົ້ນຫາຕາມສະຖານະແລະເວລາ');
            $table->index('reviewed_by_doctor_id')->comment('Index ສຳລັບຄົ້ນຫາຜົນທີ່ທ່ານໝໍຕ້ອງເບິ່ງ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
