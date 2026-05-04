<?php
// 300 — 게시판관리 (gnuboard adm/admin.menu300.php 와 동일 구조)
if (!defined('_GNUBOARD_')) exit;
return [
    'group' => '게시판관리',
    'items' => [
        ['key' => 'bbs_board',     'label' => '게시판관리',     'url' => '/admin/board_list',     'level' => '',
         'icon' => '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>'],
        ['key' => 'bbs_group',     'label' => '게시판그룹관리', 'url' => '/admin/boardgroup_list','level' => 'super',
         'icon' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>'],
        ['key' => 'bbs_poplist',   'label' => '인기검색어관리', 'url' => '/admin/popular_list',   'level' => 'super',
         'icon' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>'],
        ['key' => 'bbs_poprank',   'label' => '인기검색어순위', 'url' => '/admin/popular_rank',   'level' => 'super',
         'icon' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>'],
        ['key' => 'qa',            'label' => '1:1문의설정',    'url' => '/admin/qa_config',      'level' => 'super',
         'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
        ['key' => 'scf_contents',  'label' => '내용관리',       'url' => '/admin/contentlist',    'level' => 'super',
         'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>'],
        ['key' => 'scf_faq',       'label' => 'FAQ관리',        'url' => '/admin/faqmasterlist',  'level' => 'super',
         'icon' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
        ['key' => 'scf_writecount','label' => '글,댓글 현황',   'url' => '/admin/write_count',    'level' => 'super',
         'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>'],
    ],
];
