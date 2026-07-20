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
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
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
$listall = '<a href="'.G5_ADMIN_URL.'/visit_search">처음</a>'; //페이지 처음으로 (초기화용도)
$sql_search = '';

if(isset($sfl) && $sfl && !in_array($sfl, array('vi_ip','vi_date','vi_time','vi_referer','vi_agent','vi_browser','vi_os','vi_device')) ) {
    $sfl = '';
}
?>

<div class="local_sch local_sch01">
    <form name="fvisit" method="get" onsubmit="return fvisit_submit(this);">
    <?php echo $listall?>
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
        <th scope="col">IP</th>
        <th scope="col">접속 경로</th>
        <th scope="col">브라우저</th>
        <th scope="col">OS</th>
        <th scope="col">접속기기</th>
        <th scope="col">일시</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql_common = " from {$g5['visit_table']} ";
    if ($sfl) {
        if($sfl=='vi_ip' || $sfl=='vi_date'){
            $sql_search = " where $sfl like '$stx%' ";
        }else{
            $sql_search = " where $sfl like '%$stx%' ";
        }
    }
    $sql = " select count(*) as cnt
                {$sql_common}
                {$sql_search} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    $rows = $config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $sql = " select *
                {$sql_common}
                {$sql_search}
                order by vi_id desc
                limit {$from_record}, {$rows} ";
    $result = sql_pdo_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++) {
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
        <td class="td_id"><a href="<?php echo G5_ADMIN_URL ?>/visit_search?sfl=vi_ip&amp;stx=<?php echo $ip; ?>"><?php echo $ip; ?></a></td>
        <td class="td_left"><?php echo $link.$title; ?><?php echo $link ? '</a>' : ''; ?></td>
        <td class="td_idsmall td_category1"><?php echo $brow; ?></td>
        <td class="td_idsmall td_category3"><?php echo $os; ?></td>
        <td class="td_idsmall td_category2"><?php echo $device; ?></td>
        <td class="td_datetime"><a href="<?php echo G5_ADMIN_URL ?>/visit_search?sfl=vi_date&amp;stx=<?php echo $row['vi_date']; ?>"><?php echo $row['vi_date']; ?></a> <?php echo $row['vi_time']; ?></td>
    </tr>
    <?php } ?>
    <?php if ($i == 0) echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>'; ?>
    </tbody>
    </table>
</div>

<?php
$domain = isset($domain) ? $domain : '';
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, G5_ADMIN_URL.'/visit_search?'.$qstr.'&amp;domain='.$domain.'&amp;page=');
if ($pagelist) {
    echo $pagelist;
}
?>

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
</script>

<?php
?>
</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/visit_search — modern shell wrap end
