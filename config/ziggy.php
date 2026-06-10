<?php

$common = [
    'logout',
    'profile.*',
    'notifications.*',
    'storage.local',
    'storage.local.upload',
];

return [
    'groups' => [

        // Halaman publik & auth (belum login)
        'guest' => [
            'sanctum.csrf-cookie',
            'pwa.manifest',
            'welcome',
            'tentang',
            'galeri',
            'ekskul',
            'ekskul.show',
            'berita.index',
            'berita.show',
            'ppdb.index',
            'ppdb.create',
            'ppdb.store',
            'ppdb.check',
            'letters.verify',
            'receipt.verify',
            'honor.verify',
            'report-cards.verify',
            'midtrans.callback',
            'login',
            'password.request',
            'password.email',
            'password.reset',
            'password.store',
            'verification.notice',
            'verification.verify',
            'verification.send',
            'password.confirm',
            'password.update',
            'logout',
            'storage.local',
        ],

        // Per-role: hanya routes yang relevan
        'kamad'    => [...$common, 'kamad.*'],
        'operator' => [...$common, 'operator.*', 'storage.local'],
        'keuangan' => [...$common, 'keuangan.*'],
        'guru'     => [...$common, 'guru.*'],
        'siswa'    => [...$common, 'siswa.*'],

    ],
];
