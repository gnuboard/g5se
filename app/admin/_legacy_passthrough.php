<?php
/*
 * 일반 admin 페이지 패스스루 헬퍼 (visit 외).
 *
 * 호출 예 (admin/auth_list.php 에서):
 *   $legacy_target = 'auth_list.php';
 *   require __DIR__.'/_legacy_passthrough.php';
 *
 * - $legacy_target: G5_ADMIN_PATH 안의 실제 페이지 파일명
 * - $legacy_is_post (선택): POST 핸들러 → ob_start 없이 require
 * - $legacy_form_replace (선택): action 이 없는 form 에 클린 URL 주입할 때
 *   ['<form name="xxx"' => '/admin/xxx_update'] 형식 array
 */
if (!defined('_GNUBOARD_')) exit;

require_once G5_PATH.'/adm/admin.lib.php';

// goto_url 의 ./foo.php 또는 ./bar.php?... 를 /admin/foo, /admin/bar?... 로 변환
add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?([a-z][a-z0-9_]*)\.php(\?.*)?$#i', $u, $m)) {
        header('Location: /admin/'.$m[1].($m[2] ?? ''), true, 302);
        exit;
    }
}, 10);

if (!empty($legacy_is_post)) {
    chdir(G5_ADMIN_PATH);
    require G5_ADMIN_PATH.'/'.$legacy_target;
    return;
}

ob_start();
chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/'.$legacy_target;
$html = ob_get_clean();

// (1) form action 이 명시적으로 없는 경우 클린 URL 주입
if (!empty($legacy_form_replace) && is_array($legacy_form_replace)) {
    foreach ($legacy_form_replace as $needle => $action) {
        $html = str_replace($needle, $needle.' action="'.$action.'"', $html);
    }
}

// (2) 모든 ./foo.php 와 ./foo.php?... 의 href / form action / window.open 을 클린 URL 로 변환
$html = preg_replace_callback(
    '#(href|action)="\./([a-z][a-z0-9_]*)\.php([^"]*)"#i',
    static fn($m) => $m[1].'="/admin/'.$m[2].$m[3].'"',
    $html
);

echo $html;
