<?php
/*
 * /admin/board_list_update — gnuboard 의 board_list_update.php 를 chdir+require.
 * 마지막 goto_url('./board_list.php?...') 를 /admin/board_list 로 변환.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?(board_list|board_form)\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/'.$m[1].($m[2] ?? ''), true, 302);
        exit;
    }
}, 10, 1);

chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/board_list_update.php';
