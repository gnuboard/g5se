<?php
/*
 * /admin/theme_preview_exit — 세션 unset + redirect admin/theme_preview
 * GET token. 멱등.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
require_once __DIR__.'/admin.lib.php';
admin_require_login();

if ($member['mb_id'] !== $config['cf_admin']) alert('최고 관리자만 접근 가능합니다.');

check_admin_token();

unset($_SESSION['ss_theme_preview']);

header('Location: '.G5_ADMIN_URL.'/theme_preview', true, 303);
exit;
