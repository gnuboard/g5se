<?php
/*
 * /admin/captcha_file_delete — gnuboard 의 adm/captcha_file_delete.php 패스스루.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target = 'captcha_file_delete.php';
require __DIR__.'/_legacy_passthrough.php';
