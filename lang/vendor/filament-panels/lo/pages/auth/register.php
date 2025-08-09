<?php

return [

    'title' => 'ລົງທະບຽນ',

    'heading' => 'ລົງທະບຽນ',

    'actions' => [

        'login' => [
            'before' => 'ຫຼື',
            'label' => 'ເຂົ້າສູ່ລະບົບບັນຊີຂອງທ່ານ',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'ທີ່ຢູ່ອີເມວ',
        ],

        'name' => [
            'label' => 'ຊື່',
        ],

        'password' => [
            'label' => 'ລະຫັດຜ່ານ',
            'validation_attribute' => 'password',
        ],

        'password_confirmation' => [
            'label' => 'ຢືນຢັນລະຫັດຜ່ານ',
        ],

        'actions' => [

            'register' => [
                'label' => 'ລົງທະບຽນ',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'ຈຳນວນຄັ້ງໃນການພະຍາຍາມລົງທະບຽນໄດ້ຖິງຂີດຈຳກັດແລ້ວ',
            'body' => 'ກະລຸນາລອງໃໝ່ອີກ :seconds ວິນາທີ',
        ],

    ],

];