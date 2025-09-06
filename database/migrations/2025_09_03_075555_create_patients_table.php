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
            Schema::create('patients', function (Blueprint $table) {
                  $table->id()->comment('ລະຫັດຄົນໄຂ້ (Primary Key)');

                  // ຂໍ້ມູນພື້ນຖານ
                  $table->string('patient_code', 20)->unique()
                        ->comment('ລະຫັດຄົນໄຂ້ (P001, P002...)');
                  $table->string('first_name', 100)
                        ->comment('ຊື່ຕົວຈິງ');
                  $table->string('last_name', 100)
                        ->comment('ນາມສະກຸນ');
                  $table->string('full_name', 200)->virtualAs("CONCAT(first_name, ' ', last_name)")
                        ->comment('ຊື່ເຕັມ (Generated)');

                  // ຂໍ້ມູນສ່ວນຕົວ
                  $table->enum('gender', ['Male', 'Female', 'Other'])
                        ->comment('ເພດ: ຊາຍ/ຍິງ/ອື່ນໆ');
                  $table->date('birth_date')->nullable()
                        ->comment('ວັນເດືອນປີເກີດ');
                  $table->string('phone', 20)->nullable()
                        ->comment('ເບີໂທລະສັບ');
                  $table->string('email', 100)->nullable()
                        ->comment('ອີເມວ');

                  // ທີ່ຢູ່
                  $table->text('address')->nullable()
                        ->comment('ທີ່ຢູ່ປັດຈຸບັນ');
                  $table->string('village', 100)->nullable()
                        ->comment('ບ້ານ');
                  $table->string('district', 100)->nullable()
                        ->comment('ເມືອງ');
                  $table->string('province', 100)->nullable()
                        ->comment('ແຂວງ');

                  // ຂໍ້ມູນສຸກເສີນ
                  $table->string('emergency_contact_name', 200)->nullable()
                        ->comment('ຊື່ຜູ້ຕິດຕໍ່ສຸກເສີນ');
                  $table->string('emergency_contact_phone', 20)->nullable()
                        ->comment('ເບີຜູ້ຕິດຕໍ່ສຸກເສີນ');
                  $table->string('emergency_contact_relationship', 100)->nullable()
                        ->comment('ຄວາມສຳພັນ');

                  // ຂໍ້ມູນການປິ່ນປົວ
                  $table->text('medical_history')->nullable()
                        ->comment('ປະຫວັດການປ່ວຍ');
                  $table->text('allergies')->nullable()
                        ->comment('ການແພ້ຢາ/ອາຫານ');
                  $table->text('chronic_conditions')->nullable()
                        ->comment('ພະຍາດເຮື້ອຮັງ');

                  // ສະຖານະ
                  $table->boolean('is_active')->default(true)
                        ->comment('ສະຖານະການໃຊ້ງານ');
                  $table->unsignedBigInteger('created_by')
                        ->comment('ຜູ້ສ້າງຂໍ້ມູນ');

                  $table->timestamps();
                  $table->softDeletes();

                  // Indexes
                  $table->index('patient_code');
                  $table->index(['first_name', 'last_name']);
                  $table->index('phone');
                  $table->index('is_active');

                  // Foreign Keys
                  $table->foreign('created_by')->references('id')->on('users');
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
