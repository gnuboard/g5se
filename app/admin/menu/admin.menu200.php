<?php
// 200 — 회원관리 (gnuboard adm/admin.menu200.php 와 동일 구조)
if (!defined('_GNUBOARD_')) exit;
return [
    'group' => '회원관리',
    'items' => [
        ['key' => 'mb_list',    'code' => '200100', 'label' => '회원관리',         'url' => '/admin/member_list',           'level' => 'super', 'icon' => '👥'],
        ['key' => 'mb_exel',    'code' => '200400', 'label' => '회원관리파일',     'url' => '/admin/member_list_exel',      'level' => 'super', 'icon' => '📄'],
        ['key' => 'mb_mail',    'code' => '200300', 'label' => '회원메일발송',     'url' => '/admin/mail_list',             'level' => 'super', 'icon' => '✉️'],
        ['key' => 'mb_visit',   'code' => '200800', 'label' => '접속자집계',       'url' => '/admin/visit_list',            'level' => 'super', 'icon' => '📈'],
        ['key' => 'mb_search',  'code' => '200810', 'label' => '접속자검색',       'url' => '/admin/visit_search',          'level' => 'super', 'icon' => '🔍'],
        ['key' => 'mb_delete',  'code' => '200820', 'label' => '접속자로그삭제',   'url' => '/admin/visit_delete',          'level' => 'super', 'icon' => '🗑️'],
        ['key' => 'mb_connect', 'code' => '200999', 'label' => '현재접속자',       'url' => '/admin/connect_list',          'level' => 'super', 'icon' => '🟢'],
        ['key' => 'mb_point',   'code' => '200200', 'label' => '포인트관리',       'url' => '/admin/point_list',            'level' => 'super', 'icon' => '💎'],
        ['key' => 'mb_poll',    'code' => '200900', 'label' => '투표관리',         'url' => '/admin/poll_list',             'level' => 'super', 'icon' => '✅'],
    ],
];
