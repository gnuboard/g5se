<?php
/*
 * /admin/boardgroup_form — gnuboard 의 adm/boardgroup_form.php 패스스루.
 * 기능은 원본 그대로, 디자인만 modern shell + 컴포넌트 레이어로 적용.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target   = 'boardgroup_form.php';
$legacy_menu_key = 'groups';
require __DIR__.'/_legacy_passthrough.php';
