<?php
/*
 * /admin/content_form_update — gnuboard 의 contentformupdate.php 를 chdir+require.
 * 마지막 goto_url('./contentlist.php') / './contentform.php?...' 를 /admin/* 로 변환.
 * 삭제 (?w=d) 도 동일 핸들러가 처리.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?contentlist\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/content_list'.($m[1] ?? ''), true, 302);
        exit;
    }
    if (preg_match('#^\.?/?contentform\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/content_form'.($m[1] ?? ''), true, 302);
        exit;
    }
}, 10);

chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/contentformupdate.php';
