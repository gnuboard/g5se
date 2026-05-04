<?php
/*
 * /admin/boardgroupmember_update — gnuboard 의 boardgroupmember_update.php 를 chdir+require.
 * w='ld' → list 에서의 일괄삭제, w='d' → form 에서의 일괄삭제, w='' → form 의 그룹 추가.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?boardgroupmember_list\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/boardgroupmember_list'.($m[1] ?? ''), true, 302); exit;
    }
    if (preg_match('#^\.?/?boardgroupmember_form\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/boardgroupmember_form'.($m[1] ?? ''), true, 302); exit;
    }
}, 10);

chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/boardgroupmember_update.php';
