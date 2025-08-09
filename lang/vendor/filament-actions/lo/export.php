<?php

return [

    'label' => 'ສົ່ງອອກ :label',

    'modal' => [

        'heading' => 'ສົ່ງອອກ :label',

        'form' => [

            'columns' => [

                'label' => 'ຖັນຂໍ້ມູນ',

                'form' => [

                    'is_enabled' => [
                        'label' => ':column ເປີດໃຊ້ງານ',
                    ],

                    'label' => [
                        'label' => 'ປ້າຍຊື່ :column',
                    ],

                ],

            ],

        ],

        'actions' => [

            'export' => [
                'label' => 'ສົ່ງອອກ',
            ],

        ],

    ],

    'notifications' => [

        'completed' => [

            'title' => 'ການສົ່ງອອກສຳເລັດແລ້ວ',

            'actions' => [

                'download_csv' => [
                    'label' => 'ດາວໂຫລດ .csv',
                ],

                'download_xlsx' => [
                    'label' => 'ດາວໂຫລດ .xlsx',
                ],

            ],

        ],

        'max_rows' => [
            'title' => 'ຂໍ້ມູນສົ່ງອອກໃຫຍ່ເກີນໄປ',
            'body' => 'ບໍ່ສາມາດສົ່ງອອກໄດ້ຫຼາຍກວ່າ 1 ແຖວໃນຄັ້ງດຽວ|ບໍ່ສາມາດສົ່ງອອກໄດ້ຫຼາຍກວ່າ :count ແຖວໃນຄັ້ງດຽວ',
        ],

        'started' => [
            'title' => 'ການສົ່ງອອກເລີ່ມຕົ້ນແລ້ວ',
            'body' => 'ການສົ່ງອອກໄດ້ເລີ່ມຕົ້ນແລ້ວ ແລະ 1 ແຖວຈະຖືກປະມວນຜົນໃນເບື້ອງຫຼັງ|ການສົ່ງອອກໄດ້ເລີ່ມຕົ້ນແລ້ວ ແລະ :count ແຖວຈະຖືກປະມວນຜົນໃນເບື້ອງຫຼັງ',
        ],

    ],

    'file_name' => 'export-:export_id-:model',

];