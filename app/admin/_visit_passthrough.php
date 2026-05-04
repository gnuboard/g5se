<?php
/*
 * Visit 통계 페이지 패스스루 헬퍼.
 *
 * 14개 visit_*.php 가 같은 패턴 (chdir+require + visit.sub.php 의 anchor 서브메뉴) 을
 * 반복해서 갖고 있어, 각 admin wrapper 가 이 파일 하나만 require 하면 끝.
 *
 * 호출 예 (admin/visit_list.php 에서):
 *   $visit_target = 'visit_list.php';
 *   require __DIR__.'/_visit_passthrough.php';
 *
 * - $visit_target: G5_ADMIN_PATH 안의 실제 페이지 파일명
 * - $visit_is_post (선택): POST 핸들러일 때 true (visit_delete_update 등)
 *   → ob_start 없이 그대로 require (HTML 출력 없음, goto_url 만 처리)
 */
if (!defined('_GNUBOARD_')) exit;

require_once G5_PATH.'/adm/admin.lib.php';

// goto_url 로 visit_*.php 가 들어오면 클린 URL 로 변환
add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?(visit_[a-z_]+)\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/'.$m[1].($m[2] ?? ''), true, 302);
        exit;
    }
}, 10);

if (!empty($visit_is_post)) {
    chdir(G5_ADMIN_PATH);
    require G5_ADMIN_PATH.'/'.$visit_target;
    return;
}

ob_start();
chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/'.$visit_target;
$html = ob_get_clean();

// visit.sub.php 의 anchor (./visit_xxx.php) 와 form action 을 클린 URL 로 치환
$html = preg_replace_callback(
    '#(href|action)="\./(visit_[a-z_]+)\.php([^"]*)"#i',
    static fn($m) => $m[1].'="/admin/'.$m[2].$m[3].'"',
    $html
);

echo $html;
