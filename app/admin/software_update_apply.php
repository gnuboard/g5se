<?php
/*
 * /admin/software_update_apply — GitHub Releases 업데이트 적용.
 */
ini_set('display_errors', '0');
@set_time_limit(0);

$sub_menu = '100415';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
require_once G5_LIB_PATH.'/github_release_update.lib.php';

if ($is_admin !== 'super') {
    alert('최고관리자만 접근 가능합니다.', G5_ADMIN_URL);
}

check_admin_token();

$g5['title'] = '프로그램 업데이트 적용';
$result = null;
$error = '';

try {
    $result = g5se_update_apply_latest_release();
} catch (Throwable $e) {
    g5se_update_log('failed ' . $e->getMessage());
    $error = $e->getMessage();
}

admin_layout_start($g5['title'], 'software_update');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">

<?php if ($error) { ?>
    <div class="local_desc01 local_desc">
        <p><strong>업데이트에 실패했습니다.</strong></p>
        <p><?php echo get_text($error) ?></p>
        <p>자세한 기록은 <strong>data/update/update.log</strong>를 확인하십시오.</p>
    </div>
<?php } else { ?>
    <div class="local_desc01 local_desc">
        <p><strong>업데이트가 완료됐습니다.</strong></p>
        <p>적용 버전: <?php echo get_text($result['release']['latest_version']) ?></p>
        <p>백업 파일: <?php echo get_text(str_replace(g5se_update_root_path().'/', '', $result['backup'])) ?></p>
        <p>DB 변경이 포함된 릴리스라면 <a href="<?php echo G5_ADMIN_URL ?>/dbupgrade.php">DB 업그레이드</a>를 실행하십시오.</p>
    </div>
<?php } ?>

<div class="btn_confirm01 btn_confirm">
    <a href="<?php echo G5_ADMIN_URL ?>/software_update.php" class="btn btn_02">업데이트 화면으로 돌아가기</a>
</div>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
