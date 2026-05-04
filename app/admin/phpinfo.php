<?php
/*
 * /admin/phpinfo — gnuboard 의 adm/phpinfo.php 패스스루.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target = 'phpinfo.php';
require __DIR__.'/_legacy_passthrough.php';
