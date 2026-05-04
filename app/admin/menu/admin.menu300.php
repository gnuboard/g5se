<?php
// 300 — 콘텐츠
if (!defined('_GNUBOARD_')) exit;
return [
    'group' => '콘텐츠',
    'items' => [
        ['key' => 'contents', 'label' => '내용 관리', 'url' => '/admin/contentlist',   'level' => 'super',
         'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>'],
        ['key' => 'menus',    'label' => '메뉴 설정', 'url' => '/admin/menu_list',     'level' => 'super',
         'icon' => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>'],
        ['key' => 'faq',      'label' => 'FAQ 관리',  'url' => '/admin/faqmasterlist', 'level' => 'super',
         'icon' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
    ],
];
