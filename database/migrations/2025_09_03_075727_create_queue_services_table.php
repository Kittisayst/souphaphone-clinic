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
        Schema::create('queue_services', function (Blueprint $table) {
                       $table->id()->comment('ລະຫັດການເລືອກບໍລິການ (Primary Key)');
            
            // ຄວາມສຳພັນຫຼັກ
            $table->unsignedBigInteger('queue_id')
                  ->comment('ລະຫັດຄິວ (Foreign Key)');
            $table->unsignedBigInteger('service_id')
                  ->comment('ລະຫັດບໍລິການ (Foreign Key)');
            
            // ການມອບໝາຍ
            $table->unsignedBigInteger('added_by_id')
                  ->comment('ຜູ້ເພີ່ມບໍລິການ (ພະນັກງານຮັບບ້ານ ຫຼື ທ່ານໝໍ)');
            $table->unsignedBigInteger('assigned_to_id')->nullable()
                  ->comment('ຜູ້ທີ່ຖືກມອບໝາຍໃຫ້ເຮັດບໍລິການນີ້');
            
            // ສະຖານະ
            $table->enum('service_status', [
                'Added',        // ຖືກເພີ່ມແລ້ວ
                'In_Progress',  // ກຳລັງເຮັດ
                'Completed',    // ສຳເລັດ
                'Cancelled'     // ຍົກເລີກ
            ])->default('Added')->comment('ສະຖານະຂອງບໍລິການໃນຄິວນີ້');
            
            // ການຈັດການເວລາ
            $table->timestamp('started_at')->nullable()
                  ->comment('ເວລາທີ່ເລີ່ມເຮັດບໍລິການ');
            $table->timestamp('completed_at')->nullable()
                  ->comment('ເວລາທີ່ສຳເລັດບໍລິການ');
            $table->integer('actual_duration')->nullable()
                  ->comment('ເວລາທີ່ໃຊ້ຈິງ (ນາທີ)');
            
            // ໝາຍເຫດ
            $table->text('notes')->nullable()
                  ->comment('ໝາຍເຫດສຳລັບບໍລິການນີ້');
            
            $table->timestamps(); // created_at = ເວລາເພີ່ມບໍລິການ, updated_at
            $table->softDeletes(); // deleted_at

            // Foreign Keys
            $table->foreign('queue_id')->references('id')->on('queues')->onDelete('cascade')
                  ->comment('ເຊື່ອມໂຍງກັບຄິວ - ລືບຄິວແລ້ວຈະລືບບໍລິການທັງໝົດ');
            $table->foreign('service_id')->references('id')->on('services')
                  ->comment('ເຊື່ອມໂຍງກັບຂໍ້ມູນບໍລິການ');
            $table->foreign('added_by_id')->references('id')->on('users')
                  ->comment('ເຊື່ອມໂຍງກັບຜູ້ເພີ່ມບໍລິການ');
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('set null')
                  ->comment('ເຊື່ອມໂຍງກັບຜູ້ຮັບມອບໝາຍ');

            // Indexes
            $table->index(['queue_id', 'service_status'], 'idx_queue_service_status');
            $table->index(['assigned_to_id', 'service_status'], 'idx_assigned_status');
            $table->index(['service_status', 'created_at'], 'idx_status_created');
            $table->unique(['queue_id', 'service_id'], 'unique_queue_service')
                  ->comment('ບໍ່ໃຫ້ເພີ່ມບໍລິການຊ້ຳໃນຄິວດຽວກັນ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_services');
    }
};
