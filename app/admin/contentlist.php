<?php
/*
 * /admin/contentlist — 내용(컨텐츠) 목록.
 */
$sub_menu = '300600';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
auth_check_menu($auth, $sub_menu, "r");

if (!isset($g5['content_table'])) {
    die('<meta charset="utf-8">/data/dbconfig.php 파일에 <strong>$g5[\'content_table\'] = G5_TABLE_PREFIX.\'content\';</strong> 를 추가해 주세요.');
}
//내용(컨텐츠)정보 테이블이 있는지 검사한다.
if (!sql_pdo_query(" DESCRIBE {$g5['content_table']} ", false)) {
    if (sql_pdo_query(" DESCRIBE {$g5['g5_shop_content_table']} ", false)) {
        sql_pdo_query(" ALTER TABLE {$g5['g5_shop_content_table']} RENAME TO `{$g5['content_table']}` ;", false);
    } else {
        $query_cp = sql_pdo_query(
            " CREATE TABLE IF NOT EXISTS `{$g5['content_table']}` (
                      `co_id` varchar(20) NOT NULL DEFAULT '',
                      `co_html` tinyint(4) NOT NULL DEFAULT '0',
                      `co_subject` varchar(255) NOT NULL DEFAULT '',
                      `co_content` longtext NOT NULL,
                      `co_hit` int(11) NOT NULL DEFAULT '0',
                      `co_include_head` varchar(255) NOT NULL,
                      `co_include_tail` varchar(255) NOT NULL,
                      PRIMARY KEY (`co_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ",
            true
        );

        // 내용관리 생성
        sql_pdo_query(" insert into `{$g5['content_table']}` set co_id = 'company', co_html = '1', co_subject = '회사소개', co_content= '<p align=center><b>회사소개에 대한 내용을 입력하십시오.</b></p>' ", false);
        sql_pdo_query(" insert into `{$g5['content_table']}` set co_id = 'privacy', co_html = '1', co_subject = '개인정보 처리방침', co_content= '<p align=center><b>개인정보 처리방침에 대한 내용을 입력하십시오.</b></p>' ", false);
        sql_pdo_query(" insert into `{$g5['content_table']}` set co_id = 'provision', co_html = '1', co_subject = '서비스 이용약관', co_content= '<p align=center><b>서비스 이용약관에 대한 내용을 입력하십시오.</b></p>' ", false);
    }
}

$g5['title'] = '내용관리';
admin_layout_start($g5['title'], 'content');
?>
<main class="content-list-page flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">
<?php

$sql_common = " from {$g5['content_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_pdo_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = max(1, (int)ceil($total_count / max(1, $rows)));  // 전체 페이지 계산
if ($page < 1) {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$page = min($page, $total_page);
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by co_id limit $from_record, {$config['cf_page_rows']} ";
$result = sql_pdo_query($sql);
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$page_url = static fn($target): string => G5_ADMIN_URL.'/contentlist?'.http_build_query(['page' => $target]);
$page_input_url = str_replace('__PAGE__', '', $page_url('__PAGE__'));
?>

<div class="content-list-overview local_ov01 local_ov">
    <?php if ($page > 1) { ?><a href="<?php echo G5_ADMIN_URL ?>/contentlist">처음으로</a><?php } ?>
    <span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
</div>

<div class="content-list-add btn_fixed_top">
    <a href="<?php echo G5_ADMIN_URL ?>/contentform" class="btn btn_01">내용 추가</a>
</div>

<div class="content-list-scroll tbl_head01 tbl_wrap">
    <table>
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">제목</th>
                <th scope="col">관리</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $row = sql_pdo_fetch_array($result); $i++) {
                $bg = 'bg' . ($i % 2);
            ?>
                <tr class="<?php echo $bg; ?> content-list-card">
                    <td class="td_id content-list-id" data-label="ID"><?php echo $h($row['co_id']); ?></td>
                    <td class="td_left content-list-subject" data-label="제목"><?php echo htmlspecialchars2($row['co_subject']); ?></td>
                    <td class="td_mng td_mng_l content-list-manage">
                        <a href="<?php echo G5_ADMIN_URL ?>/contentform?w=u&amp;co_id=<?php echo urlencode($row['co_id']); ?>" class="btn btn_03"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span>수정</a>
                        <a href="<?php echo get_pretty_url('content', $row['co_id']); ?>" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span> 보기</a>
                        <a href="<?php echo G5_ADMIN_URL ?>/contentformupdate?w=d&amp;co_id=<?php echo urlencode($row['co_id']); ?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span>삭제</a>
                    </td>
                </tr>
            <?php
            }
            if ($i == 0) {
                echo '<tr class="content-list-empty"><td colspan="3" class="empty_table">자료가 한건도 없습니다.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<div class="content-list-desktop-pagination">
    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, G5_ADMIN_URL.'/contentlist?'.$qstr.'&amp;page='); ?>
</div>
<?php if ($total_page > 1): ?>
<nav class="content-list-mobile-pagination" aria-label="내용 목록 페이지 이동">
    <?php if ($page > 1): ?>
        <a href="<?php echo $h($page_url(1)) ?>">처음</a>
        <a href="<?php echo $h($page_url($page - 1)) ?>">이전</a>
    <?php else: ?>
        <span class="is-disabled">처음</span>
        <span class="is-disabled">이전</span>
    <?php endif; ?>
    <label class="current-page">
        <input type="number" value="<?php echo (int)$page ?>" min="1" max="<?php echo (int)$total_page ?>"
               inputmode="numeric" data-current-page="<?php echo (int)$page ?>"
               data-page-url="<?php echo $h($page_input_url) ?>" aria-label="이동할 페이지">
    </label>
    <?php if ($page < $total_page): ?>
        <a href="<?php echo $h($page_url($page + 1)) ?>">다음</a>
        <a href="<?php echo $h($page_url($total_page)) ?>">맨끝</a>
    <?php else: ?>
        <span class="is-disabled">다음</span>
        <span class="is-disabled">맨끝</span>
    <?php endif; ?>
</nav>
<?php endif; ?>

<script>
document.querySelectorAll('.content-list-mobile-pagination .current-page input').forEach(function (input) {
    function moveToPage() {
        var current = Number(input.dataset.currentPage);
        var target = Number(input.value);
        var max = Number(input.max);

        if (!Number.isInteger(target) || target < 1 || target > max) {
            input.value = current;
            input.classList.add('is-invalid');
            window.setTimeout(function () { input.classList.remove('is-invalid'); }, 700);
            return;
        }
        if (target !== current) window.location.href = input.dataset.pageUrl + target;
    }

    input.addEventListener('change', moveToPage);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            moveToPage();
        }
    });
});
</script>

<?php
?>
</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/contentlist — modern shell wrap end
