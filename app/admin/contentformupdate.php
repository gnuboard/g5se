<?php
/*
 * /admin/contentformupdate — gnuboard 의 adm/contentformupdate.php 패스스루 (POST).
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target  = 'contentformupdate.php';
$legacy_is_post = true;
require __DIR__.'/_legacy_passthrough.php';
