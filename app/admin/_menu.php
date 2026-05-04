<?php
/*
 * Admin 좌측 메뉴 빌더 — 데이터-driven, 외부 확장 가능.
 *
 * 새 메뉴 그룹을 추가하려면:
 *   app/admin/menu.d/admin.menu{N}.php  파일 한 개만 추가하면 끝.
 *   N 은 정렬 번호 — 파일이름의 숫자 오름차순으로 좌측 nav 에 노출된다.
 *   예: 100(회원) ↔ 200(게시판) 사이에 'admin.menu150.php' 를 두면 그 자리에 삽입.
 *
 * 각 파일은 PHP 배열 한 개를 `return` 한다:
 *   return [
 *       'group' => '...',
 *       'items' => [
 *           ['key' => 'foo', 'label' => '...', 'url' => '/admin/...', 'level' => 'super', 'icon' => '<svg paths>'],
 *           ...
 *       ],
 *   ];
 *
 *   level: 'super' = 최고관리자만, '' = 모든 admin (그룹/게시판 관리자 포함)
 *
 * 결과는 $_admin_nav 배열로 _layout.php 가 사용한다.
 */
if (!defined('_GNUBOARD_')) exit;

$_admin_nav = [];
$_menu_files = glob(__DIR__.'/menu.d/admin.menu*.php');

// 파일이름의 숫자 (admin.menu{N}.php) 오름차순으로 정렬 — 새 파일이 중간에 자유롭게 끼어듦
usort($_menu_files, function ($a, $b) {
    $na = (preg_match('#admin\.menu(\d+)\.php$#', basename($a), $m) ? (int)$m[1] : 0);
    $nb = (preg_match('#admin\.menu(\d+)\.php$#', basename($b), $m) ? (int)$m[1] : 0);
    return $na <=> $nb;
});

foreach ($_menu_files as $_f) {
    $_entry = require $_f;
    if (is_array($_entry) && !empty($_entry['items'])) {
        $_admin_nav[] = $_entry;
    }
}

unset($_menu_files, $_f, $_entry);
