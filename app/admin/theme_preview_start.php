<?php
/*
 * /admin/theme_preview_start — 세션 ss_theme_preview 설정 + redirect /
 * 입력: GET theme + token. POST 만 받음.
 */
require_once __DIR__.'/_common.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') alert('잘못된 접근입니다.');

if ($member['mb_id'] !== $config['cf_admin']) alert('최고 관리자만 접근 가능합니다.');

check_admin_token();

$theme = isset($_POST['theme']) ? trim($_POST['theme']) : '';
$theme_dir = get_theme_dir();
if ($theme === '' || !in_array($theme, $theme_dir, true)) {
    alert('테마가 존재하지 않거나 올바르지 않습니다.');
}

$_SESSION['ss_theme_preview'] = $theme;
// device 는 시작 시 비워둠 (gnuboard 기본 user-agent 감지로 시작). bar 에서 토글.
unset($_SESSION['ss_theme_preview_device']);

header('Location: '.G5_URL.'/', true, 303);
exit;
