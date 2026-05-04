<?php
/*
 * /admin/config_form — 사이트 기본 환경설정.
 * gnuboard adm/config_form.php (1854 라인) 를 _legacy_passthrough 로 modern shell 에 wrap.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target   = 'config_form.php';
$legacy_menu_key = 'config';
// form 에 action 이 없는 경우 자동 주입
$legacy_form_replace = [
    '<form name="fconfigform" id="fconfigform" method="post"' => '/admin/config_form_update',
];
require __DIR__.'/_legacy_passthrough.php';
