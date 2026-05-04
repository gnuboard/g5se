<?php
/*
 * /admin/contentform — gnuboard 의 adm/contentform.php 패스스루.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target   = 'contentform.php';
$legacy_menu_key = 'scf_contents';
require __DIR__.'/_legacy_passthrough.php';
