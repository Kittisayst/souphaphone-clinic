<?php

return [

    'title' => 'ເຂົ້າສູ່ລະບົບ',

    'heading' => 'ເຂົ້າສູ່ລະບົບ',

    'actions' => [

        'register' => [
            'before' => 'ຫຼື',
            'label' => 'ສະໝັກບັນຊີ',
        ],

        'request_password_reset' => [
            'label' => 'ລືມລະຫັດຜ່ານຫຍັງ',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'ທີ່ຢູ່ອີເມວ',
        ],

        'password' => [
            'label' => 'ລະຫັດຜ່ານ',
        ],

        'remember' => [
            'label' => 'ຈົດຈຳຂ້ອຍ',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'ເຂົ້າສູ່ລະບົບ',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'ຂໍ້ມູນນີ້ບໍ່ຕົງກັບບັນທຶກໃນລະບົບ',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'ຈຳນວນຄັ້ງໃນການພະຍາຍາມເຂົ້າສູ່ລະບົບໄດ້ຖິງຂີດຈຳກັດແລ້ວ',
            'body' => 'ກະລຸນາລອງໃໝ່ອີກ :seconds ວິນາທີ',
        ],

    ],

];