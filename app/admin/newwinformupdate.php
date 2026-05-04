<?php
/*
 * /admin/newwinformupdate — gnuboard 의 adm/newwinformupdate.php 패스스루.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target = 'newwinformupdate.php';
require __DIR__.'/_legacy_passthrough.php';
