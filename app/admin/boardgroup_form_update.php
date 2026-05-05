<?php
/*
 * /admin/boardgroup_form_update — gnuboard 의 boardgroup_form_update.php 를 chdir+require.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?(boardgroup_form|boardgroup_list)\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/'.$m[1].($m[2] ?? ''), true, 302);
        exit;
    }
}, 10, 1);

chdir(G5_PATH.'/adm');
require G5_PATH.'/adm'.'/boardgroup_form_update.php';
