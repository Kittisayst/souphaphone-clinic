<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Room::class;
    public function definition(): array
    {
        $roomTypes = ['Consultation', 'X_Ray', 'Ultrasound', 'Laboratory', 'General'];
        $roomType = $this->faker->randomElement($roomTypes);

        // ອຸປະກອນຕາມປະເພດຫ້ອງ
        $equipment = [
            'Consultation' => 'ເກົ້າອີ້, ໂຕະກວດ, ເຄື່ອງວັດຄວາມດັນ, ໂຄມໄຟ',
            'X_Ray' => 'ເຄື່ອງຖ່າຍ X-Ray, ເສື້ອນຳ, ແຜ່ນຟີມ',
            'Ultrasound' => 'ເຄື່ອງ Ultrasound, ເຈວ, ກະດາດເຊັດ',
            'Laboratory' => 'ກ້ອງຈຸລະທັດ, ອຸປະກອນເຈາະເລືອດ, ເຄື່ອງປັ່ນ',
            'General' => 'ອຸປະກອນທົ່ວໄປ'
        ];

        return [
            'room_code' => strtoupper(substr($roomType, 0, 3)) . str_pad($this->faker->unique()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'room_name' => 'ຫ້ອງ ' . $this->faker->randomElement([
                'ກວດທົ່ວໄປ',
                'X-Ray',
                'Ultrasound',
                'ແລັບ',
                'ທົ່ວໄປ'
            ]) . ' ' . $this->faker->numberBetween(1, 10),
            'room_type' => $roomType,
            'capacity' => $this->faker->randomElement([1, 2, 4]),
            'equipment_list' => $equipment[$roomType],
            'is_available' => $this->faker->boolean(80), // 80% ຫ້ອງວ່າງ
            'current_user_id' => null,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    // State: ຫ້ອງກວດທົ່ວໄປ
    public function consultation(): static
    {
        return $this->state(fn(array $attributes) => [
            'room_type' => 'Consultation',
            'room_name' => 'ຫ້ອງກວດທົ່ວໄປ ' . $this->faker->numberBetween(1, 5),
            'equipment_list' => 'ເກົ້າອີ້ກວດ, ໂຕະກວດ, ເຄື່ອງວັດຄວາມດັນ, ໂຄມໄຟກວດ, ເຄື່ອງຊ່ວຍຟັງ',
        ]);
    }

    // State: ຫ້ອງ X-Ray
    public function xray(): static
    {
        return $this->state(fn(array $attributes) => [
            'room_type' => 'X_Ray',
            'room_name' => 'ຫ້ອງຖ່າຍ X-Ray ' . $this->faker->numberBetween(1, 2),
            'equipment_list' => 'ເຄື່ອງຖ່າຍ X-Ray ດິຈິຕອນ, ໂຕະກວດ, ເສື້ອນຳ, ແຜ່ນປ້ອງກັນ',
        ]);
    }

    // State: ຫ້ອງແລັບ
    public function laboratory(): static
    {
        return $this->state(fn(array $attributes) => [
            'room_type' => 'Laboratory',
            'room_name' => 'ຫ້ອງແລັບ ' . $this->faker->numberBetween(1, 3),
            'equipment_list' => 'ກ້ອງຈຸລະທັດ, ເຄື່ອງປັ່ນເລືອດ, ອຸປະກອນເຈາະເລືອດ, ເຄື່ອງວັດນ້ຳຕານ',
        ]);
    }
}
