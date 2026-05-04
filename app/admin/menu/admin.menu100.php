<?php
// 100 — 환경설정 (gnuboard adm/admin.menu100.php 와 동일 구조)
// 'code' 는 gnuboard 의 \$sub_menu 코드 — 페이지가 자동으로 매칭됨 (수동 \$active_key 불필요)
if (!defined('_GNUBOARD_')) exit;
return [
    'group' => '환경설정',
    'items' => [
        ['key' => 'config',     'code' => '100100', 'label' => '기본 환경설정',     'url' => '/admin/config_form',           'level' => 'super', 'icon' => '⚙️'],
        ['key' => 'auth',       'code' => '100200', 'label' => '관리권한설정',       'url' => '/admin/auth_list',             'level' => 'super', 'icon' => '🛡️'],
        ['key' => 'theme',      'code' => '100280', 'label' => '테마설정',           'url' => '/admin/theme',                 'level' => 'super', 'icon' => '🎨'],
        ['key' => 'menus',      'code' => '100290', 'label' => '메뉴설정',           'url' => '/admin/menu_list',             'level' => 'super', 'icon' => '📑'],
        ['key' => 'sendmail',   'code' => '100300', 'label' => '메일 테스트',        'url' => '/admin/sendmail_test',         'level' => 'super', 'icon' => '✉️'],
        ['key' => 'newwin',     'code' => '100310', 'label' => '팝업레이어관리',     'url' => '/admin/newwinlist',            'level' => 'super', 'icon' => '🪟'],
        ['key' => 'sess_del',   'code' => '100800', 'label' => '세션파일 일괄삭제',   'url' => '/admin/session_file_delete',   'level' => 'super', 'icon' => '🗑️'],
        ['key' => 'cache_del',  'code' => '100900', 'label' => '캐시파일 일괄삭제',   'url' => '/admin/cache_file_delete',     'level' => 'super', 'icon' => '🗑️'],
        ['key' => 'cap_del',    'code' => '100910', 'label' => '캡챠파일 일괄삭제',   'url' => '/admin/captcha_file_delete',   'level' => 'super', 'icon' => '🗑️'],
        ['key' => 'th_del',     'code' => '100920', 'label' => '썸네일파일 일괄삭제', 'url' => '/admin/thumbnail_file_delete', 'level' => 'super', 'icon' => '🗑️'],
        ['key' => 'mb_del',     'code' => '100930', 'label' => '회원관리파일 일괄삭제','url' => '/admin/member_list_file_delete','level' => 'super', 'icon' => '🗑️'],
        ['key' => 'phpinfo',    'code' => '100500', 'label' => 'phpinfo()',          'url' => '/admin/phpinfo',               'level' => 'super', 'icon' => 'ℹ️'],
        ['key' => 'browscap',   'code' => '100510', 'label' => 'Browscap 업데이트',  'url' => '/admin/browscap',              'level' => 'super', 'icon' => '🔄'],
        ['key' => 'browscap_c', 'code' => '100520', 'label' => '접속로그 변환',      'url' => '/admin/browscap_convert',      'level' => 'super', 'icon' => '🔁'],
        ['key' => 'dbupgrade',  'code' => '100410', 'label' => 'DB업그레이드',       'url' => '/admin/dbupgrade',             'level' => 'super', 'icon' => '🛢️'],
        ['key' => 'service',    'code' => '100400', 'label' => '부가서비스',         'url' => '/admin/service',               'level' => 'super', 'icon' => '🛠️'],
    ],
];
