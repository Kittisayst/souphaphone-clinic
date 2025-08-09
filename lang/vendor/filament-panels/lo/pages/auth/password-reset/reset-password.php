<?php

return [

    'title' => 'ຣີເຊັດລະຫັດຜ່ານ',

    'heading' => 'ຣີເຊັດລະຫັດຜ່ານ',

    'form' => [

        'email' => [
            'label' => 'ທີ່ຢູ່ອີແມັວ',
        ],

        'password' => [
            'label' => 'ລະຫັດຜ່ານ',
            'validation_attribute' => 'password',
        ],

        'password_confirmation' => [
            'label' => 'ຢືນຢັນລະຫັດຜ່ານ',
        ],

        'actions' => [

            'reset' => [
                'label' => 'ຣີເຊັດລະຫັດຜ່ານ',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'ຈຳນວນຄັ້ງໃນການພະຍາຍາມຣີເຊັດລະຫັດຜ່ານໄດ້ເກີນກຳນົດແລ້ວ',
            'body' => 'ກະລຸນາລອງໃໝ່ອີກ :seconds ວິນາທີ',
        ],

    ],

];
