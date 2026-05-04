<?php
/*
 * Admin 메뉴 정의 — 한 곳에서 array 로 관리.
 * 새 메뉴 항목 추가는 적절한 그룹 'items' 에 한 줄 append 하면 됨.
 *
 * key:   안전한 path 세그먼트 (current page 비교용)
 * label: 좌측 nav 표시 텍스트
 * url:   클릭 시 이동 (절대경로)
 * icon:  inline SVG 24x24 viewBox (Feather)
 * level: 'super' = 최고관리자만, '' = 모든 admin (그룹/게시판 관리자 포함)
 */
if (!defined('_GNUBOARD_')) exit;

$_admin_nav = [
    [
        'group' => '대시보드',
        'items' => [
            ['key' => 'home', 'label' => '대시보드', 'url' => '/admin', 'level' => '',
             'icon' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
        ],
    ],
    [
        'group' => '회원',
        'items' => [
            ['key' => 'members',     'label' => '회원 관리',   'url' => '/admin/member_list',  'level' => 'super',
             'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['key' => 'point_list',  'label' => '포인트 내역', 'url' => '/admin/point_list',   'level' => 'super',
             'icon' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>'],
        ],
    ],
    [
        'group' => '게시판',
        'items' => [
            ['key' => 'boards',     'label' => '게시판 관리',     'url' => '/admin/board_list',     'level' => '',
             'icon' => '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>'],
            ['key' => 'groups',     'label' => '그룹 관리',       'url' => '/admin/boardgroup_list','level' => 'super',
             'icon' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>'],
            ['key' => 'qa',         'label' => '1:1 문의',        'url' => '/admin/qaconfig_form',   'level' => 'super',
             'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
        ],
    ],
    [
        'group' => '콘텐츠',
        'items' => [
            ['key' => 'contents', 'label' => '내용 관리', 'url' => '/admin/content_list', 'level' => 'super',
             'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>'],
            ['key' => 'menus',    'label' => '메뉴 설정', 'url' => '/admin/menu_list',    'level' => 'super',
             'icon' => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>'],
            ['key' => 'faq',      'label' => 'FAQ 관리',  'url' => '/admin/faqmasterlist','level' => 'super',
             'icon' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
        ],
    ],
    [
        'group' => '환경',
        'items' => [
            ['key' => 'config',  'label' => '기본 환경',  'url' => '/admin/config_form',   'level' => 'super',
             'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
            ['key' => 'visit',   'label' => '방문자 통계','url' => '/admin/visit_search',  'level' => 'super',
             'icon' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>'],
            ['key' => 'connect', 'label' => '현재 접속자','url' => '/admin/connect_list',  'level' => 'super',
             'icon' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>'],
        ],
    ],
];
