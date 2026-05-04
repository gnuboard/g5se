<?php
/*
 * /admin/poll_form_update — gnuboard 의 adm/poll_form_update.php 패스스루.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target = 'poll_form_update.php';
require __DIR__.'/_legacy_passthrough.php';
