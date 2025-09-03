<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Patient::class;
    public function definition(): array
    {
        $laoFirstNames = [
            'ສົມຊາຍ',
            'ສົມໃຈ',
            'ສົມພອນ',
            'ບຸນມີ',
            'ຄຳພອນ',
            'ວິລະຍຸດ',
            'ທອງດີ',
            'ບຸນທັນ',
            'ສົມຍິງ',
            'ສົມດາວ',
            'ສົມແສງ',
            'ບຸນລາ',
            'ແສງດາວ',
            'ມະນີ',
            'ວັນທີ',
            'ທອງຄຳ'
        ];

        $laoLastNames = [
            'ໂພທິສານ',
            'ຈັນທະວົງ',
            'ບຸນນາງ',
            'ສີມູນ',
            'ວິເສດ',
            'ພອນສະຫວັນ',
            'ຂັນຕີ',
            'ໄຊຍະສິດ',
            'ສິນໄຊ',
            'ຄຳຟອງ',
            'ທອງໄສ',
            'ບັນດິດ',
            'ແກ້ວສີ',
            'ຟ້າເດືອນ',
            'ນາກສີ',
            'ປັນຍາ'
        ];

        return [
            'patient_code' => 'P' . str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'first_name' => $this->faker->randomElement($laoFirstNames),
            'last_name' => $this->faker->randomElement($laoLastNames),
            'date_of_birth' => $this->faker->dateTimeBetween('-80 years', '-1 year'),
            'gender' => $this->faker->randomElement(['M', 'F']),
            'phone_number' => '020' . $this->faker->numerify('########'),
            'address' => 'ບ້ານ ' . $this->faker->randomElement([
                'ໜອງຄາຍ',
                'ນາຄຳ',
                'ດົງມະຟື',
                'ຫາດຊາຍຟອງ',
                'ວັດຕະກ',
                'ໂນນສະຫວ່າງ',
                'ສີສັດຕະນາກ'
            ]) . ', ເມືອງ ' . $this->faker->randomElement([
                            'ໄຊເສດຖາ',
                            'ຈັນທະບູລີ',
                            'ສີໂຄດຕະບອງ',
                            'ປາກງື້ມ',
                            'ສິຂອດຕະບອງ'
                        ]) . ', ນະຄອນຫລວງວຽງຈັນ',
            'emergency_contact' => $this->faker->name,
            'emergency_phone' => '020' . $this->faker->numerify('########'),
            'blood_type' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'allergies' => $this->faker->optional(0.3)->randomElement([
                'ແພ້ Penicillin',
                'ແພ້ກຸ້ງ',
                'ແພ້ໄຂ່',
                'ແພ້ນົມ',
                'ແພ້ Aspirin'
            ]),
            'medical_history' => $this->faker->optional(0.4)->randomElement([
                'ເປັນເບົາວານມາ 5 ປີ',
                'ເຄີຍຜ່າຕັດຊ່ອງທ້ອງ',
                'ມີປະຫວັດຄວາມດັນສູງ',
                'ເຄີຍແຂກຂາ'
            ]),
        ];
    }

    // State: ເດັກນ້ອຍ
    public function child(): static
    {
        return $this->state(fn(array $attributes) => [
            'date_of_birth' => $this->faker->dateTimeBetween('-15 years', '-1 year'),
            'emergency_contact' => 'ພໍ່ແມ່',
        ]);
    }

    // State: ຄົນແກ່
    public function elderly(): static
    {
        return $this->state(fn(array $attributes) => [
            'date_of_birth' => $this->faker->dateTimeBetween('-80 years', '-60 years'),
            'medical_history' => 'ມີປະຫວັດເປັນໂລກເຮື້ອຮັງ',
        ]);
    }
}
