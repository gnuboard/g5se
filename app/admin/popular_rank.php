<?php
/*
 * /admin/popular_rank — 인기검색어 순위 분석.
 */
$sub_menu = "300400";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
auth_check_menu($auth, $sub_menu, 'r');

$fr_date = isset($_REQUEST['fr_date']) ? $_REQUEST['fr_date'] : '';
$to_date = isset($_REQUEST['to_date']) ? $_REQUEST['to_date'] : '';

if (empty($fr_date) || !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date)) {
    $fr_date = G5_TIME_YMD;
}
if (empty($to_date) || !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date)) {
    $to_date = G5_TIME_YMD;
}

$qstr = "fr_date={$fr_date}&amp;to_date={$to_date}";

$sql_common = " from {$g5['popular_table']} a ";
$sql_search = " where trim(pp_word) <> '' and pp_date between '{$fr_date}' and '{$to_date}' ";
$sql_group = " group by pp_word ";
$sql_order = " order by cnt desc ";

$sql = " select pp_word {$sql_common} {$sql_search} {$sql_group} ";
$result = sql_pdo_query($sql);
$total_count = sql_num_rows($result);

$rows = $config['cf_page_rows'];
$total_page  = max(1, (int)ceil($total_count / max(1, $rows)));  // 전체 페이지 계산
if ($page < 1) {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$page = min($page, $total_page);
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select pp_word, count(*) as cnt {$sql_common} {$sql_search} {$sql_group} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_pdo_query($sql);

$listall = '<a href="'.G5_ADMIN_URL.'/popular_rank" class="ov_listall">전체목록</a>';
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$page_url = static function ($target) use ($fr_date, $to_date): string {
    return G5_ADMIN_URL.'/popular_rank?'.http_build_query([
        'fr_date' => $fr_date,
        'to_date' => $to_date,
        'page' => $target,
    ]);
};
$page_input_url = str_replace('__PAGE__', '', $page_url('__PAGE__'));

$g5['title'] = '인기검색어순위';
admin_layout_start($g5['title'], 'popular_rank');
?>
<main class="popular-rank-page flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">
<?php
require_once G5_PLUGIN_PATH . '/jquery-ui/datepicker.php';

$colspan = 3;
?>

<script>
    $(function() {
        $("#fr_date, #to_date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            showButtonPanel: true,
            yearRange: "c-99:c+99",
            maxDate: "+0d"
        });
    });
</script>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">건수</span><span class="ov_num"> <?php echo number_format($total_count) ?>개</span></span>
</div>

<form name="fsearch" id="fsearch" class="popular-rank-search local_sch02 local_sch" method="get">
    <div class="popular-rank-search-fields sch_last">
        <strong>기간별검색</strong>
        <div class="popular-rank-date-range">
            <label for="fr_date" class="sound_only">시작일</label>
            <input type="text" name="fr_date" value="<?php echo $h($fr_date) ?>" id="fr_date" class="frm_input" size="11" maxlength="10">
            <span aria-hidden="true">~</span>
            <label for="to_date" class="sound_only">종료일</label>
            <input type="text" name="to_date" value="<?php echo $h($to_date) ?>" id="to_date" class="frm_input" size="11" maxlength="10">
        </div>
        <input type="submit" class="btn_sch2" value="검색">
    </div>
</form>

<form name="fpopularrank" id="fpopularrank" class="popular-rank-list" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">

    <div class="popular-rank-scroll tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th scope="col">순위</th>
                    <th scope="col">검색어</th>
                    <th scope="col">검색회수</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_pdo_fetch_array($result); $i++) {
                    $word = get_text($row['pp_word']);
                    $rank = ($i + 1 + ($rows * ($page - 1)));
                    ?>
                    <tr class="popular-rank-card">
                        <td class="td_num popular-rank-number" data-label="순위"><?php echo $rank ?></td>
                        <td class="td_left popular-rank-word" data-label="검색어"><?php echo $word ?></td>
                        <td class="td_num popular-rank-count" data-label="검색횟수"><?php echo number_format((int)$row['cnt']) ?></td>
                    </tr>
                    <?php
                }

                if ($i == 0) {
                    echo '<tr class="popular-rank-empty"><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

</form>

<?php
?>
<div class="popular-rank-desktop-pagination">
    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, G5_ADMIN_URL.'/popular_rank?'.$qstr.'&amp;page='); ?>
</div>
<?php if ($total_page > 1): ?>
<nav class="popular-rank-mobile-pagination" aria-label="인기검색어 순위 페이지 이동">
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
document.querySelectorAll('.popular-rank-mobile-pagination .current-page input').forEach(function (input) {
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
</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/popular_rank — modern shell wrap end
