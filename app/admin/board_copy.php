<?php
/*
 * /admin/board_copy — 게시판 복사 (popup). gnuboard 의 adm/board_copy.php 를 그대로 호출.
 * form action 만 클린 URL 로 치환.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

ob_start();
chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/board_copy.php';
$html = ob_get_clean();

$html = str_replace('action="./board_copy_update.php"', 'action="/admin/board_copy_update"', $html);

echo $html;
