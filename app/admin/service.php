<?php
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target = 'service.php';
require __DIR__.'/_legacy_passthrough.php';
