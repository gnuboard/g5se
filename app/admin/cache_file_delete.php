<?php
/*
 * /admin/cache_file_delete — 캐시파일 일괄삭제 (스트리밍 출력).
 */
$sub_menu = '100900';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

if ($is_admin !== 'super') {
    header('Location: '.G5_URL, true, 302);
    exit;
}

require_once __DIR__.'/admin.lib.php';
@require_once G5_PATH.'/adm/safe_check.php';
if (function_exists('social_log_file_delete')) {
    social_log_file_delete();
}

run_event('adm_cache_file_delete_before');

$g5['title'] = '캐시파일 일괄삭제';
admin_layout_start($g5['title'], 'cache_file_delete');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">

<div class="local_desc02 local_desc">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    </p>
</div>

<?php
flush();

if (!$dir = @opendir(G5_DATA_PATH . '/cache')) {
    echo '<p>캐시디렉토리를 열지못했습니다.</p>';
}

$cnt = 0;
echo '<ul class="session_del">' . PHP_EOL;

$files = glob(G5_DATA_PATH . '/cache/latest-*');
$content_files = glob(G5_DATA_PATH . '/cache/content-*');

$files = array_merge($files, $content_files);
if (is_array($files)) {
    foreach ($files as $cache_file) {
        $cnt++;
        unlink($cache_file);
        echo '<li>' . $cache_file . '</li>' . PHP_EOL;

        flush();

        if ($cnt % 10 == 0) {
            echo PHP_EOL;
        }
    }
}

run_event('adm_cache_file_delete');

echo '<li>완료됨</li></ul>' . PHP_EOL;
echo '<div class="local_desc01 local_desc"><p><strong>최신글 캐시파일 ' . $cnt . '건 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>' . PHP_EOL;
?>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/cache_file_delete — modern shell wrap end
