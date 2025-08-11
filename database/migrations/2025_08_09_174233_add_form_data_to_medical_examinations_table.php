<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_examinations', function (Blueprint $table) {
            // ເພີ່ມ column ເພື່ອເກັບຂໍ້ມູນຈາກ dynamic form
            $table->json('dynamic_form_data')->nullable()->after('examination_results')
                  ->comment('ຂໍ້ມູນຈາກ dynamic form fields');
        });
    }

    public function down(): void
    {
        Schema::table('medical_examinations', function (Blueprint $table) {
            $table->dropColumn('dynamic_form_data');
        });
    }
};
