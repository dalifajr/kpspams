<?php

return [
    'branding' => [
        'community_name' => 'TIRTA TAMA',
        'region_name' => 'Tanjung Makmur',
        'app_code' => 'FBG1207',
        'app_version' => '6.7.22',
        'support_whatsapp' => '0822 6924 5660',
        'support_whatsapp_link' => 'https://wa.me/6282269245660',
        'support_telegram' => 'https://t.me/pams_operator',
    ],
    'defaults' => [
        'areas' => [
            'Zona Utama',
            'Zona Barat',
            'Zona Timur',
        ],
    ],
    'menus' => [
        'admin' => [
            ['slug' => 'user', 'label' => 'User', 'icon' => 'person', 'color' => '#2ECC71'],
            ['slug' => 'area', 'label' => 'Area', 'icon' => 'grid_view', 'color' => '#2980B9'],
            ['slug' => 'golongan', 'label' => 'Golongan', 'icon' => 'layers', 'color' => '#7F8C8D'],
            ['slug' => 'data-meter', 'label' => 'Data Meter', 'icon' => 'speed', 'color' => '#1ABC9C'],
            ['slug' => 'setting', 'label' => 'Setting', 'icon' => 'settings', 'color' => '#8E44AD'],
            ['slug' => 'print-tes', 'label' => 'Print Tes', 'icon' => 'print', 'color' => '#0B3C5D'],
            ['slug' => 'logs-perubahan', 'label' => 'Logs Perubahan', 'icon' => 'history', 'color' => '#2C3E50'],
            ['slug' => 'biaya-lain', 'label' => 'Biaya Lain', 'icon' => 'sell', 'color' => '#17A589'],
            ['slug' => 'biaya-aplikasi', 'label' => 'Biaya Aplikasi', 'icon' => 'vpn_key', 'color' => '#82E0AA'],
        ],
        'operator' => [
            ['slug' => 'keuangan', 'label' => 'Keuangan', 'icon' => 'account_balance_wallet', 'color' => '#17202A'],
            ['slug' => 'data-pelanggan', 'label' => 'Data Pelanggan', 'icon' => 'people', 'color' => '#8E44AD'],
            ['slug' => 'catat-meter', 'label' => 'Catat Meter', 'icon' => 'edit_note', 'color' => '#2E86C1'],
            ['slug' => 'loket', 'label' => 'Loket', 'icon' => 'storefront', 'color' => '#5D6D7E'],
            ['slug' => 'laporan', 'label' => 'Laporan', 'icon' => 'description', 'color' => '#C0392B'],
        ],
        'user' => [
            ['slug' => 'tagihan', 'label' => 'Tagihan Air', 'icon' => 'receipt_long', 'color' => '#0F9D58'],
        ],
    ],
];
