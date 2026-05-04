<?php
/*
 * /admin/visit_delete — gnuboard 의 adm/visit_delete.php 패스스루.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$visit_target = 'visit_delete.php';
require __DIR__.'/_visit_passthrough.php';
