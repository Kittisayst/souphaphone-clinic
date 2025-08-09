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
            $table->id();
            $table->string('patient_code', 20)->unique()->comment('ລະຫັດຄົນໄຂ້ PT001, PT002');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('id_card_number', 20)->nullable();
            $table->json('emergency_contact')->nullable()->comment('ຂໍ້ມູນຕິດຕໍ່ສຸກເສີນ');
            $table->json('allergies')->nullable()->comment('ປະຫວັດແພ້ຢາ');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('patient_code');
            $table->index('phone');
            $table->index(['first_name', 'last_name']);
            $table->index('birth_date');
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
