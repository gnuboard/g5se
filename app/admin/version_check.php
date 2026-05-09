<?php
/*
 * /admin/version_check - GitHub Releases 기반 버전 확인.
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

$g5['title'] = '버전 확인';
$releases = array();
$error = '';

try {
    $releases = g5se_update_recent_releases(5);
} catch (Throwable $e) {
    $error = $e->getMessage();
}

$latest_release = isset($releases[0]) ? $releases[0] : null;
if (!$error && !$latest_release) {
    $error = 'GitHub Releases에 게시된 릴리스가 없습니다.';
}

admin_layout_start($g5['title'], 'version_check');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">

<div class="local_desc02 local_desc">
    <p>GitHub Releases 기준으로 현재 설치 버전과 공식 저장소의 최신 릴리스 정보를 확인합니다.</p>
    <p>서버 환경마다 업데이트 방식이 다를 수 있으므로 이 화면에서는 자동 적용을 제공하지 않고, 새 버전 여부와 변경 내용을 안내합니다.</p>
    <p>비공개 저장소를 사용한다면 <strong>G5_GITHUB_UPDATE_TOKEN</strong> 환경변수 또는 상수에 GitHub fine-grained token을 설정해야 합니다.</p>
</div>

<?php if ($error) { ?>
    <div class="local_desc01 local_desc">
        <p><strong>버전 정보를 확인할 수 없습니다.</strong></p>
        <p><?php echo get_text($error) ?></p>
        <p>저장소에 published Release가 없거나, 비공개 저장소인데 토큰이 설정되지 않은 경우에도 이 오류가 발생합니다.</p>
    </div>
<?php } else { ?>
    <section class="tbl_frm01 tbl_wrap">
        <table>
        <caption>버전 확인 정보</caption>
        <tbody>
        <tr>
            <th scope="row">저장소</th>
            <td>
                <a href="<?php echo get_text(g5se_update_repository_url()) ?>" target="_blank" rel="noopener">
                    <?php echo get_text(G5SE_UPDATE_REPOSITORY) ?>
                </a>
            </td>
        </tr>
        <tr>
            <th scope="row">GitHub 토큰</th>
            <td><?php echo g5se_update_token_is_configured() ? '설정됨' : '미설정' ?></td>
        </tr>
        <tr>
            <th scope="row">현재 버전</th>
            <td><?php echo get_text(g5se_update_current_version()) ?></td>
        </tr>
        <tr>
            <th scope="row">최신 릴리스</th>
            <td>
                <?php echo get_text($latest_release['latest_version']) ?>
                <?php if ($latest_release['latest_name']) { ?>
                    <span class="text-gray-500 dark:text-gray-400">/ <?php echo get_text($latest_release['latest_name']) ?></span>
                <?php } ?>
                <?php if ($latest_release['html_url']) { ?>
                    <a href="<?php echo get_text($latest_release['html_url']) ?>" target="_blank" rel="noopener" class="btn_frmline">GitHub에서 보기</a>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row">게시일</th>
            <td><?php echo get_text($latest_release['published_at']) ?></td>
        </tr>
        <tr>
            <th scope="row">상태</th>
            <td>
                <?php if ($latest_release['has_update']) { ?>
                    <strong class="text-admin-primary-700 dark:text-admin-primary-300">새 버전이 있습니다.</strong>
                <?php } else { ?>
                    최신 버전입니다.
                <?php } ?>
            </td>
        </tr>
        </tbody>
        </table>
    </section>

    <section class="tbl_frm01 tbl_wrap">
        <table>
        <caption>최근 릴리스 변경 내용</caption>
        <tbody>
        <?php foreach ($releases as $item) { ?>
        <tr>
            <th scope="row">
                <?php echo get_text($item['latest_version']) ?>
                <?php if ($item['prerelease']) { ?>
                    <span class="sound_only">프리릴리스</span>
                <?php } ?>
            </th>
            <td>
                <div class="mb-2">
                    <strong><?php echo get_text($item['latest_name'] ?: $item['latest_version']) ?></strong>
                    <?php if ($item['published_at']) { ?>
                        <span class="text-gray-500 dark:text-gray-400"><?php echo get_text($item['published_at']) ?></span>
                    <?php } ?>
                    <?php if ($item['html_url']) { ?>
                        <a href="<?php echo get_text($item['html_url']) ?>" target="_blank" rel="noopener" class="btn_frmline">보기</a>
                    <?php } ?>
                </div>
                <?php if ($item['body']) { ?>
                    <div class="leading-relaxed whitespace-pre-wrap"><?php echo nl2br(get_text($item['body'])) ?></div>
                <?php } else { ?>
                    <p>등록된 변경 내용이 없습니다.</p>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </section>
<?php } ?>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
