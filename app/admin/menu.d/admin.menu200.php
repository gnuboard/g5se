<?php
// 200 — 게시판
if (!defined('_GNUBOARD_')) exit;
return [
    'group' => '게시판',
    'items' => [
        ['key' => 'boards', 'label' => '게시판 관리',  'url' => '/admin/board_list',     'level' => '',
         'icon' => '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>'],
        ['key' => 'groups', 'label' => '그룹 관리',    'url' => '/admin/boardgroup_list','level' => 'super',
         'icon' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>'],
        ['key' => 'qa',     'label' => '1:1 문의',     'url' => '/admin/qaconfig_form',  'level' => 'super',
         'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
    ],
];
