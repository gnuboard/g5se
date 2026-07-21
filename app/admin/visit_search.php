<?php
/*
 * /admin/visit_search — 접속자 검색.
 */
$sub_menu = '200810';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
require_once G5_LIB_PATH.'/visit.lib.php';

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '접속자검색';
admin_layout_start($g5['title'], 'visit_search');
?>
<main class="visit-search-page flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<!-- jquery-ui datepicker (modern shell 은 add_stylesheet/javascript queue 안 흘리므로 직접 주입) -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="<?php echo G5_PLUGIN_URL ?>/jquery-ui/style.css">
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<div class="legacy-admin-content space-y-4">
<?php

$colspan = 6;
$sql_search = '';

if(isset($sfl) && $sfl && !in_array($sfl, array('vi_ip','vi_date','vi_time','vi_referer','vi_agent','vi_browser','vi_os','vi_device')) ) {
    $sfl = '';
}
?>

<div class="local_sch local_sch01">
    <form name="fvisit" method="get" onsubmit="return fvisit_submit(this);">
    <label for="sch_sort" class="sound_only">검색분류</label>
    <select name="sfl" id="sch_sort" class="search_sort">
        <option value="vi_ip"<?php echo get_selected($sfl, 'vi_ip'); ?>>IP</option>
        <option value="vi_referer"<?php echo get_selected($sfl, 'vi_referer'); ?>>접속경로</option>
        <option value="vi_date"<?php echo get_selected($sfl, 'vi_date'); ?>>날짜</option>
    </select>
    <label for="sch_word" class="sound_only">검색어</label>
    <input type="text" name="stx" size="20" value="<?php echo stripslashes($stx); ?>" id="sch_word" class="frm_input">
    <input type="submit" value="검색" class="btn_submit">
    </form>
</div>

<div class="tbl_wrap tbl_head01">
    <table>
    <thead>
    <tr>
        <th scope="col" class="visit-col-ip">IP</th>
        <th scope="col" class="visit-col-referer">접속 경로</th>
        <th scope="col" class="visit-col-browser">브라우저</th>
        <th scope="col" class="visit-col-os">OS</th>
        <th scope="col" class="visit-col-device">접속기기</th>
        <th scope="col" class="visit-col-datetime">일시</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql_common = " from {$g5['visit_table']} ";
    $sql_params = array();
    if ($sfl) {
        if($sfl=='vi_ip' || $sfl=='vi_date'){
            $sql_search = " where $sfl like :stx ";
            $sql_params[':stx'] = $stx.'%';
        }else{
            $sql_search = " where $sfl like :stx ";
            $sql_params[':stx'] = '%'.$stx.'%';
        }
    }
    $sql = " select count(*) as cnt
                {$sql_common}
                {$sql_search} ";
    $row = sql_pdo_fetch($sql, $sql_params);
    $total_count = $row['cnt'];

    $rows = $config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    if ($total_page > 0 && $page > $total_page) $page = $total_page;
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $sql = " select *
                {$sql_common}
                {$sql_search}
                order by vi_id desc
                limit {$from_record}, {$rows} ";
    $result = sql_pdo_query($sql, $sql_params);

    for ($i=0; $row=sql_pdo_fetch_array($result); $i++) {
        $brow = $row['vi_browser'];
        if(!$brow)
            $brow = get_brow($row['vi_agent']);

        $os = $row['vi_os'];
        if(!$os)
            $os = get_os($row['vi_agent']);

        $device = $row['vi_device'];

        $link = "";
        $referer = "";
        $title = "";
        if ($row['vi_referer']) {

            // referer 는 URL-인코딩된 페이로드를 담을 수 있으므로 디코딩을 먼저 한 뒤
            // 마지막에 이스케이프한다(get_text 가 따옴표까지 이스케이프하여 속성 컨텍스트에서 안전).
            $referer = urldecode($row['vi_referer']);

            if (!is_utf8($referer)) {
                $referer = iconv('euc-kr', 'utf-8', $referer);
            }

            $title = get_text(cut_str($referer, 255, ""));
            $link = '<a href="'.get_text($referer).'" target="_blank" title="'.$title.'">';
        }

        if ($is_admin == 'super')
            $ip = $row['vi_ip'];
        else
            $ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", G5_IP_DISPLAY, $row['vi_ip']);

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_id visit-col-ip"><a href="<?php echo G5_ADMIN_URL ?>/visit_search?sfl=vi_ip&amp;stx=<?php echo $ip; ?>"><?php echo $ip; ?></a></td>
        <td class="td_left visit-col-referer"><?php echo $link.$title; ?><?php echo $link ? '</a>' : ''; ?></td>
        <td class="td_idsmall td_category1 visit-col-browser"><?php echo $brow; ?></td>
        <td class="td_idsmall td_category3 visit-col-os"><?php echo $os; ?></td>
        <td class="td_idsmall td_category2 visit-col-device"><?php echo $device; ?></td>
        <td class="td_datetime visit-col-datetime"><a href="<?php echo G5_ADMIN_URL ?>/visit_search?sfl=vi_date&amp;stx=<?php echo $row['vi_date']; ?>"><?php echo $row['vi_date']; ?></a> <?php echo $row['vi_time']; ?></td>
    </tr>
    <?php } ?>
    <?php if ($i == 0) echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>'; ?>
    </tbody>
    </table>
</div>

<?php
$domain = isset($domain) ? $domain : '';
$paging_query = http_build_query([
    'sfl' => (string) $sfl,
    'stx' => (string) $stx,
    'domain' => (string) $domain,
]);
$paging_url = G5_ADMIN_URL.'/visit_search?'.htmlspecialchars($paging_query, ENT_QUOTES, 'UTF-8').'&amp;page=';
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $paging_url);
if ($pagelist) {
    echo '<div class="visit-desktop-pagination">'.$pagelist.'</div>';
}
if ($total_page > 1) {
    $first_url = $paging_url.'1';
    $prev_url = $paging_url.max(1, $page - 1);
    $next_url = $paging_url.min($total_page, $page + 1);
    $last_url = $paging_url.$total_page;
    ?>
    <nav class="visit-mobile-pagination" aria-label="접속자 검색 페이지 이동">
        <?php if ($page > 1) { ?>
            <a href="<?php echo $first_url; ?>">처음</a>
            <a href="<?php echo $prev_url; ?>">이전</a>
        <?php } else { ?>
            <span class="is-disabled">처음</span>
            <span class="is-disabled">이전</span>
        <?php } ?>
        <label class="current-page">
            <span class="sound_only">이동할 페이지</span>
            <input type="number"
                   class="current-page-input rounded"
                   value="<?php echo (int) $page; ?>"
                   min="1"
                   max="<?php echo (int) $total_page; ?>"
                   inputmode="numeric"
                   data-current-page="<?php echo (int) $page; ?>"
                   data-page-url="<?php echo $paging_url; ?>"
                   aria-label="이동할 페이지">
        </label>
        <?php if ($page < $total_page) { ?>
            <a href="<?php echo $next_url; ?>">다음</a>
            <a href="<?php echo $last_url; ?>">맨끝</a>
        <?php } else { ?>
            <span class="is-disabled">다음</span>
            <span class="is-disabled">맨끝</span>
        <?php } ?>
    </nav>
    <?php
}
?>

<form class="local_sch01 local_sch" method="get" action="<?php echo G5_ADMIN_URL; ?>/visit_excel_download">
    <input type="hidden" name="mode" value="search">
    <input type="hidden" name="sfl" value="<?php echo htmlspecialchars((string)$sfl, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="stx" value="<?php echo htmlspecialchars((string)$stx, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" class="btn btn_02">엑셀 다운로드</button>
</form>

<script>
$(function(){
    $("#sch_sort").change(function(){ // select #sch_sort의 옵션이 바뀔때
        if($(this).val()=="vi_date"){ // 해당 value 값이 vi_date이면
            $("#sch_word").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); // datepicker 실행
        }else{ // 아니라면
            $("#sch_word").datepicker("destroy"); // datepicker 미실행
        }
    });

    if($("#sch_sort option:selected").val()=="vi_date"){ // select #sch_sort 의 옵션중 selected 된것의 값이 vi_date라면
        $("#sch_word").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); // datepicker 실행
    }
});

function fvisit_submit(f)
{
    return true;
}

document.querySelectorAll('.visit-mobile-pagination .current-page input').forEach(function(input) {
    function moveToPage() {
        var current = Number(input.dataset.currentPage);
        var target = Number(input.value);
        var max = Number(input.max);

        if (!Number.isInteger(target) || target < 1 || target > max) {
            input.value = current;
            input.classList.add('is-invalid');
            window.setTimeout(function() { input.classList.remove('is-invalid'); }, 700);
            return;
        }

        if (target !== current) {
            window.location.href = input.dataset.pageUrl + target;
        }
    }

    input.addEventListener('change', moveToPage);
    input.addEventListener('keydown', function(event) {
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
// /admin/visit_search — modern shell wrap end
