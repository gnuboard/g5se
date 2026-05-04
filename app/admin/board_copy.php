<?php
/*
 * /admin/board_copy — 게시판 복사 (popup).
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target   = 'board_copy.php';
$legacy_menu_key = 'bbs_board';
require __DIR__.'/_legacy_passthrough.php';
