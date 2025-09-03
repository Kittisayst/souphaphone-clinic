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
        Schema::create('patients', function (Blueprint $table) {
             $table->id()->comment('ລະຫັດຄົນໄຂ້ (Primary Key)');
            $table->string('patient_code', 20)->unique()
                  ->comment('ລະຫັດຄົນໄຂ້ (ສຳລັບສະແດງ - ເຊັ່ນ P001, P002)');
            $table->string('first_name', 50)->comment('ຊື່');
            $table->string('last_name', 50)->comment('ນາມສະກຸນ');
            $table->date('date_of_birth')->nullable()->comment('ວັນເດືອນປີເກີດ');
            $table->enum('gender', ['M', 'F', 'Other'])->nullable()
                  ->comment('ເພດ: M=ຊາຍ, F=ຍິງ, Other=ອື່ນໆ');
            $table->string('phone_number', 20)->nullable()->comment('ເບີໂທລະສັບ');
            $table->text('address')->nullable()->comment('ທີ່ຢູ່');
            $table->string('emergency_contact', 100)->nullable()
                  ->comment('ຜູ້ຕິດຕໍ່ກໍລະນີສຸກເສີນ');
            $table->string('emergency_phone', 20)->nullable()
                  ->comment('ເບີໂທສຸກເສີນ');
            $table->string('blood_type', 5)->nullable()
                  ->comment('ກຸ່ມເລືອດ (A, B, AB, O, Rh+/-)');
            $table->text('allergies')->nullable()
                  ->comment('ການແພ້ຢາ ຫຼື ອາຫານ');
            $table->text('medical_history')->nullable()
                  ->comment('ປະຫວັດການແພດ');
            $table->timestamps(); // created_at = ວັນທີ່ລົງທະບຽນ, updated_at
            $table->softDeletes(); // deleted_at

            // Indexes for better performance
            $table->index('patient_code')->comment('Index ສຳລັບຄົ້ນຫາດ້ວຍລະຫັດຄົນໄຂ້');
            $table->index('phone_number')->comment('Index ສຳລັບຄົ້ນຫາດ້ວຍເບີໂທ');
            $table->index(['first_name', 'last_name'])->comment('Index ສຳລັບຄົ້ນຫາດ້ວຍຊື່');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
