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
        Schema::create('medicines', function (Blueprint $table) {
           $table->id()->comment('ລະຫັດຢາ (Primary Key)');
            $table->string('medicine_code', 30)->unique()
                  ->comment('ລະຫັດຢາ (ເຊັ່ນ MED001, PARA500)');
            $table->string('medicine_name', 200)
                  ->comment('ຊື່ຢາທາງການຄ້າ');
            $table->string('generic_name', 200)->nullable()
                  ->comment('ຊື່ຢາທາງເຄມີ/ຊື່ສາມັນ');
            $table->string('medicine_type', 100)->nullable()
                  ->comment('ປະເພດຢາ (ແກ້ໄຂ້, ແກ້ປວດ, ຊ້ອນ)');
            $table->string('unit', 50)->nullable()
                  ->comment('ຫົວໜ່ວຍ (ແຜັດ, ແຄັບຊູນ, ໂມ, ແກ້ວ)');
            $table->string('strength', 50)->nullable()
                  ->comment('ຄວາມແຮງຂອງຢາ (500mg, 10ml, 250mg)');
            $table->string('manufacturer', 100)->nullable()
                  ->comment('ບໍລິສັດຜະລິດ');
            $table->decimal('stock_quantity', 10, 2)->default(0)
                  ->comment('ຈຳນວນຢາໃນສະຕ໋ອກ');
            $table->decimal('min_stock_level', 10, 2)->default(10)
                  ->comment('ລະດັບຕ່ຳສຸດທີ່ຕ້ອງມີ (ເພື່ອແຈ້ງເຕືອນ)');
            $table->decimal('unit_price', 8, 2)->nullable()
                  ->comment('ລາຄາຕໍ່ຫົວໜ່ວຍ (ກີບ)');
            $table->date('expiry_date')->nullable()
                  ->comment('ວັນໝົດອາຍຸ');
            $table->text('storage_condition')->nullable()
                  ->comment('ເງື່ອນໄຂການເກັບຮັກສາ');
            $table->timestamps(); // created_at, updated_at  
            $table->softDeletes(); // deleted_at

            // Indexes
            $table->index('medicine_code')->comment('Index ສຳລັບຄົ້ນຫາດ້ວຍລະຫັດຢາ');
            $table->index('medicine_name')->comment('Index ສຳລັບຄົ້ນຫາດ້ວຍຊື່ຢາ');
            $table->index('expiry_date')->comment('Index ສຳລັບຄົ້ນຫາຢາໃກ້ໝົດອາຍຸ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
