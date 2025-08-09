<?php

return [

    'label' => 'ການນຳທາງການແບ່ງໜ້າ',

    'overview' => '{1} ສະແດງ 1 ລາຍການ|[2,*] ສະແດງ :first ເຖິງ :last ຈາກ :total ລາຍການ',

    'fields' => [

        'records_per_page' => [

            'label' => 'ລາຍການຕໍ່ໜ້າ',

            'options' => [
                'all' => 'ທັງໝົດ',
            ],

        ],

    ],

    'actions' => [

        'first' => [
            'label' => 'ໜ້າທຳອິດ',
        ],

        'go_to_page' => [
            'label' => 'ໄປໜ້າ :page',
        ],

        'last' => [
            'label' => 'ໜ້າສຸດທ້າຍ',
        ],

        'next' => [
            'label' => 'ຕໍ່ໄປ',
        ],

        'previous' => [
            'label' => 'ໜ້າກ່ອນ',
        ],

    ],

];