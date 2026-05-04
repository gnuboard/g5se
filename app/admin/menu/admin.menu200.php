<?php
// 200 — 회원관리 (gnuboard adm/admin.menu200.php 와 동일 구조)
if (!defined('_GNUBOARD_')) exit;
return [
    'group' => '회원관리',
    'items' => [
        ['key' => 'mb_list',    'code' => '200100', 'label' => '회원관리',         'url' => '/admin/member_list',           'level' => 'super',
         'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
        ['key' => 'mb_exel',    'code' => '200400', 'label' => '회원관리파일',     'url' => '/admin/member_list_exel',      'level' => 'super',
         'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>'],
        ['key' => 'mb_mail',    'code' => '200300', 'label' => '회원메일발송',     'url' => '/admin/mail_list',             'level' => 'super',
         'icon' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>'],
        ['key' => 'mb_visit',   'code' => '200800', 'label' => '접속자집계',       'url' => '/admin/visit_list',            'level' => 'super',
         'icon' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>'],
        ['key' => 'mb_search',  'code' => '200810', 'label' => '접속자검색',       'url' => '/admin/visit_search',          'level' => 'super',
         'icon' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>'],
        ['key' => 'mb_delete',  'code' => '200820', 'label' => '접속자로그삭제',   'url' => '/admin/visit_delete',          'level' => 'super',
         'icon' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>'],
        ['key' => 'mb_connect', 'code' => '200999', 'label' => '현재접속자',       'url' => '/admin/connect_list',          'level' => 'super',
         'icon' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>'],
        ['key' => 'mb_point',   'code' => '200200', 'label' => '포인트관리',       'url' => '/admin/point_list',            'level' => 'super',
         'icon' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>'],
        ['key' => 'mb_poll',    'code' => '200900', 'label' => '투표관리',         'url' => '/admin/poll_list',             'level' => 'super',
         'icon' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'],
    ],
];
