<?php
/*
 * /admin/visit_month — gnuboard 의 adm/visit_month.php 패스스루.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$visit_target = 'visit_month.php';
require __DIR__.'/_visit_passthrough.php';
