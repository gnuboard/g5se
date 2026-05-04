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
        ['key' => 'poll',   'label' => '투표 관리',    'url' => '/admin/poll_list',      'level' => 'super',
         'icon' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'],
        ['key' => 'qa',         'label' => '1:1 문의',     'url' => '/admin/qa_config',     'level' => 'super',
         'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
        ['key' => 'popular',    'label' => '인기 검색어',  'url' => '/admin/popular_list',  'level' => 'super',
         'icon' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>'],
        ['key' => 'writecount', 'label' => '글·댓글 현황', 'url' => '/admin/write_count',   'level' => 'super',
         'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>'],
    ],
];
