<?php
// 900 — 환경 (가장 아래)
if (!defined('_GNUBOARD_')) exit;
return [
    'group' => '환경',
    'items' => [
        ['key' => 'config',     'label' => '기본 환경',    'url' => '/admin/config_form',  'level' => 'super',
         'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
        ['key' => 'auth',       'label' => '권한 설정',    'url' => '/admin/auth_list',    'level' => 'super',
         'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
        ['key' => 'theme',      'label' => '테마 설정',    'url' => '/admin/theme',        'level' => 'super',
         'icon' => '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125 0-.94.746-1.688 1.688-1.688H16.5c3.04 0 5.5-2.46 5.5-5.5C22 6.04 17.52 2 12 2z"/>'],
        ['key' => 'visit',      'label' => '방문자 통계',  'url' => '/admin/visit_search', 'level' => 'super',
         'icon' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>'],
        ['key' => 'connect',    'label' => '현재 접속자',  'url' => '/admin/connect_list', 'level' => 'super',
         'icon' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>'],
        ['key' => 'newwinlist', 'label' => '팝업 레이어',  'url' => '/admin/newwinlist',   'level' => 'super',
         'icon' => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>'],
        ['key' => 'mail',       'label' => '회원 메일',    'url' => '/admin/mail_list',    'level' => 'super',
         'icon' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>'],
        ['key' => 'sendmail',   'label' => '메일 테스트',  'url' => '/admin/sendmail_test','level' => 'super',
         'icon' => '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>'],
        ['key' => 'cleanup',    'label' => '캐시·세션 정리','url' => '/admin/cache_file_delete', 'level' => 'super',
         'icon' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>'],
        ['key' => 'phpinfo',    'label' => 'phpinfo()',    'url' => '/admin/phpinfo',      'level' => 'super',
         'icon' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>'],
    ],
];
