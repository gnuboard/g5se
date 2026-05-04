<?php
/*
 * /admin/point_list_delete — gnuboard 의 point_list_delete.php 를 chdir+require.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?point_list\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/point_list'.($m[1] ?? ''), true, 302);
        exit;
    }
}, 10);

chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/point_list_delete.php';
