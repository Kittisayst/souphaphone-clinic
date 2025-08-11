<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_service_id')->constrained()->onDelete('cascade');

            // Field properties
            $table->string('field_name')->comment('ຊື່ field ໃນ database');
            $table->string('field_label')->comment('ຊື່ທີ່ສະແດງໃຫ້ເຫັນ');
            $table->enum('field_type', [
                'text',
                'textarea',
                'number',
                'select',
                'checkbox',
                'date',
                'time',
                'datetime'
            ])->comment('ປະເພດ field');

            // Field configuration
            $table->json('field_options')->nullable()->comment('ຕົວເລືອກສຳລັບ select, checkbox, ແລະອື່ນໆ');
            $table->json('validation_rules')->nullable()->comment('ກົດລະບຽບການກວດສອບ');
            $table->json('normal_range')->nullable()->comment('ຄ່າປົກກະຕິ ສຳລັບ number');

            // Display properties
            $table->integer('sort_order')->default(0)->comment('ລຳດັບການສະແດງ');
            $table->boolean('is_required')->default(false)->comment('ບັງຄັບຕ້ອງໃສ່');
            $table->string('placeholder')->nullable()->comment('ຂໍ້ຄວາມຕົວຢ່າງ');
            $table->text('help_text')->nullable()->comment('ຂໍ້ຄວາມຊ່ວຍເຫຼືອ');

            // Field grouping
            $table->string('field_group')->nullable()->comment('ກຸ່ມ field');
            $table->boolean('is_active')->default(true)->comment('ເປີດໃຊ້ງານ');

            $table->timestamps();

            // Indexes
            $table->index(['medical_service_id', 'sort_order']);
            $table->index(['medical_service_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_form_fields');
    }
};
