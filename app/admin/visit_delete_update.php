<?php
/*
 * /admin/visit_delete_update — gnuboard 의 adm/visit_delete_update.php 패스스루 (POST).
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$visit_target  = 'visit_delete_update.php';
$visit_is_post = true;
require __DIR__.'/_visit_passthrough.php';
