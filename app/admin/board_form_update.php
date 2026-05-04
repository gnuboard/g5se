<?php
/*
 * /admin/board_form_update — gnuboard 의 board_form_update.php 를 chdir+require.
 * 마지막 goto_url('./board_form.php?w=u&...') / './board_list.php' 를 /admin/* 로 변환.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?(board_form|board_list)\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/'.$m[1].($m[2] ?? ''), true, 302);
        exit;
    }
}, 10, 1);

chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/board_form_update.php';
