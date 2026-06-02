<?php
/*
 * /admin/theme_preview_apply — POST theme + token. g5_config.cf_theme 영구 저장
 * + 세션 unset + 안내 alert.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
require_once __DIR__.'/admin.lib.php';
admin_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') alert('잘못된 접근입니다.');
if ($member['mb_id'] !== $config['cf_admin']) alert('최고 관리자만 접근 가능합니다.');

check_admin_token();

$theme = isset($_POST['theme']) ? trim($_POST['theme']) : '';
$theme_dir = get_theme_dir();
if ($theme === '' || !in_array($theme, $theme_dir, true)) {
    alert('테마가 존재하지 않거나 올바르지 않습니다.');
}

sql_pdo_query("UPDATE `".G5_TABLE_PREFIX."config` SET cf_theme = ? LIMIT 1", [$theme]);

unset($_SESSION['ss_theme_preview']);

alert('테마 \''.$theme.'\' 가 적용되었습니다.', G5_ADMIN_URL.'/theme_preview');
