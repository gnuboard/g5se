<?php
/*
 * /admin/thumbnail_file_delete — 썸네일 일괄삭제 (file/editor/item).
 */
$sub_menu = '100920';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

if ($is_admin !== 'super') {
    header('Location: '.G5_URL, true, 302);
    exit;
}

require_once __DIR__.'/admin.lib.php';

$g5['title'] = '썸네일 일괄삭제';
admin_layout_start($g5['title'], 'thumbnail_file_delete');
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
$directory = array();
$dl = array('file', 'editor');

if (defined('G5_USE_SHOP') && G5_USE_SHOP) {
    $dl[] = 'item';
}

foreach($dl as $val) {
    if($handle = opendir(G5_DATA_PATH.'/'.$val)) {
        while(false !== ($entry = readdir($handle))) {
            if($entry == '.' || $entry == '..')
                continue;

            $path = G5_DATA_PATH.'/'.$val.'/'.$entry;

            if(is_dir($path))
                $directory[] = $path;
        }
    }
}

flush();

if (empty($directory)) {
    echo '<p>썸네일디렉토리를 열지못했습니다.</p>';
}

$cnt=0;
echo '<ul>'.PHP_EOL;

foreach($directory as $dir) {
    $files = glob($dir.'/thumb-*');
    if (is_array($files)) {
        foreach($files as $thumbnail) {
            $cnt++;
            @unlink($thumbnail);

            echo '<li>'.$thumbnail.'</li>'.PHP_EOL;

            flush();

            if ($cnt%10==0)
                echo PHP_EOL;
        }
    }
}

echo '<li>완료됨</li></ul>'.PHP_EOL;
echo '<div class="local_desc01 local_desc"><p><strong>썸네일 '.$cnt.'건의 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>'.PHP_EOL;
?>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/thumbnail_file_delete — modern shell wrap end