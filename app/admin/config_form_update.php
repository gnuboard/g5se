<?php
/*
 * /admin/config_form_update — gnuboard 의 config_form_update.php 를 chdir+require.
 * 마지막 goto_url('./config_form.php') 를 /admin/config_form 로 변환.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?config_form\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/config_form'.($m[1] ?? ''), true, 302);
        exit;
    }
}, 10);

chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/config_form_update.php';
