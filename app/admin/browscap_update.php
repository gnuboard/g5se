<?php
// ajax 응답이 깨지지 않게 PHP warning/notice 가 본문으로 새지 않도록 차단.
// (display_errors=1 환경에서 'Constant ... already defined' 같은 경고가 응답에 섞이면
//  browscap.php 의 success 핸들러가 빈 문자열 비교 실패 → alert 띄우고 완료 처리 안 됨.)
ini_set('display_errors', '0');
error_reporting(0);
ini_set('memory_limit', '-1');

$sub_menu = "100510";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

// clean the output buffer
while (ob_get_level() > 0) { ob_end_clean(); }

if (!(version_compare(phpversion(), '5.3.0', '>=') && defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE)) {
    die('사용할 수 없는 기능입니다.');
}

if ($is_admin != 'super') {
    die('최고관리자만 접근 가능합니다.');
}

require_once G5_PLUGIN_PATH . '/browscap/Browscap.php';

$browscap = new phpbrowscap\Browscap(G5_DATA_PATH . '/cache');
$browscap->updateMethod = 'cURL';
$browscap->cacheFilename = 'browscap_cache.php';
$browscap->updateCache();

die('');
