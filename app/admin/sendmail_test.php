<?php
/*
 * /admin/sendmail_test — gnuboard 의 adm/sendmail_test.php 패스스루.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target = 'sendmail_test.php';
require __DIR__.'/_legacy_passthrough.php';
