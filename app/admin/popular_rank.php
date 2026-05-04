<?php
/*
 * /admin/popular_rank — gnuboard 의 adm/popular_rank.php 패스스루.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target = 'popular_rank.php';
require __DIR__.'/_legacy_passthrough.php';
