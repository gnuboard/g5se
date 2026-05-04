<?php
/*
 * /admin/qa_config — 1:1 문의 설정.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target   = 'qa_config.php';
$legacy_menu_key = 'qa';
$legacy_form_replace = [
    '<form name="fqaconfigform" id="fqaconfigform" method="post"' => '/admin/qa_config_update',
];
require __DIR__.'/_legacy_passthrough.php';
