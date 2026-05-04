<?php
/*
 * /admin/board_copy_update — gnuboard 의 board_copy_update.php 를 chdir+require.
 * 마지막 alert(..., './board_copy.php?...') 의 .php 상대 URL 은 router 가
 * /admin/foo.php = /admin/foo 로 받아들이므로 별도 변환 불필요.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/board_copy_update.php';
