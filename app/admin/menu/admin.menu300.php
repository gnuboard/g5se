<?php
// 300 — 게시판관리 (gnuboard adm/admin.menu300.php 와 동일 구조)
if (!defined('_GNUBOARD_')) exit;
return [
    'group' => '게시판관리',
    'items' => [
        ['key' => 'bbs_board',     'code' => '300100', 'label' => '게시판관리',     'url' => '/admin/board_list',     'level' => '',      'icon' => '📋'],
        ['key' => 'bbs_group',     'code' => '300200', 'label' => '게시판그룹관리', 'url' => '/admin/boardgroup_list','level' => 'super', 'icon' => '📁'],
        ['key' => 'bbs_poplist',   'code' => '300300', 'label' => '인기검색어관리', 'url' => '/admin/popular_list',   'level' => 'super', 'icon' => '🔥'],
        ['key' => 'bbs_poprank',   'code' => '300400', 'label' => '인기검색어순위', 'url' => '/admin/popular_rank',   'level' => 'super', 'icon' => '📊'],
        ['key' => 'qa',            'code' => '300500', 'label' => '1:1문의설정',    'url' => '/admin/qa_config',      'level' => 'super', 'icon' => '💬'],
        ['key' => 'scf_contents',  'code' => '300600', 'label' => '내용관리',       'url' => '/admin/contentlist',    'level' => 'super', 'icon' => '📄'],
        ['key' => 'scf_faq',       'code' => '300700', 'label' => 'FAQ관리',        'url' => '/admin/faqmasterlist',  'level' => 'super', 'icon' => '❓'],
        ['key' => 'scf_writecount','code' => '300820', 'label' => '글,댓글 현황',   'url' => '/admin/write_count',    'level' => 'super', 'icon' => '📝'],
    ],
];
