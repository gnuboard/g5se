<?php
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target  = 'theme_update.php';
$legacy_is_post = true;
require __DIR__.'/_legacy_passthrough.php';
