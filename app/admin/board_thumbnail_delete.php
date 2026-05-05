<?php
$sub_menu = '300100';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

auth_check_menu($auth, $sub_menu, 'w');

if (!$board['bo_table']) {
    alert('존재하지 않는 게시판입니다.');
}

$g5['title'] = $board['bo_subject'] . ' 게시판 썸네일 삭제';
admin_layout_start($g5['title'], 'board');
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
$dir = G5_DATA_PATH . '/file/' . $bo_table;

$cnt = 0;
if (is_dir($dir)) {
    echo '<ul>';
    $files = glob($dir . '/thumb-*');
    if (is_array($files)) {
        foreach ($files as $thumbnail) {
            $cnt++;
            @unlink($thumbnail);

            echo '<li>' . $thumbnail . '</li>' . PHP_EOL;

            flush();

            if (($cnt % 10) == 0) {
                echo PHP_EOL;
            }
        }
    }

    echo '<li>완료됨</li></ul>' . PHP_EOL;
    echo '<div class="local_desc01 local_desc"><p><strong>썸네일 ' . $cnt . '건의 삭제 완료됐습니다.</strong></p></div>' . PHP_EOL;
} else {
    echo '<p>첨부파일 디렉토리가 존재하지 않습니다.</p>';
}
?>

<div class="btn_confirm01 btn_confirm"><a href="<?php echo G5_ADMIN_URL ?>/board_form?w=u&amp;bo_table=<?php echo $bo_table; ?>&amp;<?php echo $qstr; ?>">게시판 수정으로 돌아가기</a></div>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/board_thumbnail_delete — modern shell wrap end
