<?php
/*
 * /admin/qa_config_update — gnuboard 의 qa_config_update.php 를 chdir+require.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?qa_config\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/qa_config'.($m[1] ?? ''), true, 302);
        exit;
    }
}, 10, 1);

chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/qa_config_update.php';
