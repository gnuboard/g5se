<?php
/*
 * /admin/software_update — GitHub Releases 기반 웹 업데이트.
 */
$sub_menu = '100415';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

if ($is_admin !== 'super') {
    header('Location: '.G5_ADMIN_URL, true, 302);
    exit;
}

require_once __DIR__.'/admin.lib.php';
require_once G5_LIB_PATH.'/github_release_update.lib.php';

$g5['title'] = '프로그램 업데이트';
$release = null;
$error = '';

try {
    $release = g5se_update_latest_release();
} catch (Throwable $e) {
    $error = $e->getMessage();
}

admin_layout_start($g5['title'], 'software_update');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">

<div class="local_desc02 local_desc">
    <p>GitHub Releases 기준으로 최신 버전을 확인하고, git 명령 없이 릴리스 ZIP 파일을 내려받아 업데이트합니다.</p>
    <p>업데이트 전 현재 파일 백업 ZIP을 <strong>data/update/backups</strong>에 생성합니다. <strong>data</strong>, <strong>.git</strong>, <strong>.env</strong> 디렉터리/파일은 업데이트 대상에서 제외됩니다.</p>
    <p>비공개 저장소를 사용한다면 <strong>G5_GITHUB_UPDATE_TOKEN</strong> 환경변수 또는 상수에 GitHub fine-grained token을 설정해야 합니다.</p>
</div>

<?php if ($error) { ?>
    <div class="local_desc01 local_desc">
        <p><strong>업데이트 정보를 확인할 수 없습니다.</strong></p>
        <p><?php echo get_text($error) ?></p>
        <p>저장소에 published Release가 없거나, 비공개 저장소인데 토큰이 설정되지 않은 경우에도 이 오류가 발생합니다.</p>
    </div>
<?php } else { ?>
    <section class="tbl_frm01 tbl_wrap">
        <table>
        <caption>프로그램 업데이트 정보</caption>
        <tbody>
        <tr>
            <th scope="row">저장소</th>
            <td><?php echo get_text($release['repository']) ?></td>
        </tr>
        <tr>
            <th scope="row">현재 버전</th>
            <td><?php echo get_text($release['current_version']) ?></td>
        </tr>
        <tr>
            <th scope="row">최신 릴리스</th>
            <td>
                <?php echo get_text($release['latest_version']) ?>
                <?php if ($release['html_url']) { ?>
                    <a href="<?php echo get_text($release['html_url']) ?>" target="_blank" rel="noopener" class="btn_frmline">릴리스 보기</a>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row">게시일</th>
            <td><?php echo get_text($release['published_at']) ?></td>
        </tr>
        <tr>
            <th scope="row">상태</th>
            <td>
                <?php if ($release['has_update']) { ?>
                    <strong class="text-admin-primary-700 dark:text-admin-primary-300">업데이트 가능</strong>
                <?php } else { ?>
                    최신 버전입니다.
                <?php } ?>
            </td>
        </tr>
        </tbody>
        </table>
    </section>

    <?php if ($release['has_update']) { ?>
        <form method="post" action="<?php echo G5_ADMIN_URL ?>/software_update_apply" onsubmit="return confirm('업데이트를 적용하시겠습니까? 진행 중에는 브라우저를 닫지 마십시오.');">
            <input type="hidden" name="token" value="<?php echo get_admin_token() ?>">
            <div class="btn_confirm01 btn_confirm">
                <button type="submit" class="btn btn_01">업데이트 적용</button>
            </div>
        </form>
    <?php } ?>
<?php } ?>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
