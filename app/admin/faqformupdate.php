<?php
/*
 * /admin/faqformupdate — gnuboard 의 faqformupdate.php 를 chdir+require.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?faqlist\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/faqlist'.($m[1] ?? ''), true, 302); exit;
    }
    if (preg_match('#^\.?/?faqform\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/faqform'.($m[1] ?? ''), true, 302); exit;
    }
}, 10, 1);

chdir(G5_PATH.'/adm');
require G5_PATH.'/adm'.'/faqformupdate.php';
