<?php
/*
 * /admin/theme_preview_device?d=pc|mobile — 세션 디바이스 토글 + 같은 페이지 새로고침
 * GET d + token. referer 로 돌아가되 없으면 /.
 */
require_once __DIR__.'/_common.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if ($member['mb_id'] !== $config['cf_admin']) alert('최고 관리자만 접근 가능합니다.');

check_admin_token();

if (empty($_SESSION['ss_theme_preview'])) alert('미리보기가 활성 상태가 아닙니다.');

$d = isset($_GET['d']) ? (string)$_GET['d'] : '';
if ($d !== 'pc' && $d !== 'mobile') alert('잘못된 디바이스입니다.');

$_SESSION['ss_theme_preview_device'] = $d;

$ref = isset($_SERVER['HTTP_REFERER']) ? (string)$_SERVER['HTTP_REFERER'] : '';
// referer 가 같은 호스트인지 검증 (open redirect 방지)
$home = G5_URL;
if ($ref === '' || strpos($ref, $home) !== 0) {
    $ref = G5_URL.'/';
}
header('Location: '.$ref, true, 303);
exit;
