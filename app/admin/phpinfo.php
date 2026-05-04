<?php
/*
 * /admin/phpinfo — gnuboard 의 adm/phpinfo.php 패스스루.
 * phpinfo() 출력은 흰 배경 + 검은 글자 가정의 인라인 스타일이라 다크모드에서 가독성이
 * 떨어짐. legacy_force_light=true 로 본문만 라이트 톤 고정.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target      = 'phpinfo.php';
$legacy_force_light = true;
require __DIR__.'/_legacy_passthrough.php';
