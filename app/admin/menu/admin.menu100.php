<?php
// 100 — 회원
if (!defined('_GNUBOARD_')) exit;
return [
    'group' => '회원',
    'items' => [
        ['key' => 'members',     'label' => '회원 관리',   'url' => '/admin/member_list',  'level' => 'super',
         'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
        ['key' => 'point_list',  'label' => '포인트 내역', 'url' => '/admin/point_list',   'level' => 'super',
         'icon' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>'],
    ],
];
