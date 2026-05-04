<?php
/*
 * /admin/mail_select_form — gnuboard 의 adm/mail_select_form.php 패스스루.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target = 'mail_select_form.php';
require __DIR__.'/_legacy_passthrough.php';
