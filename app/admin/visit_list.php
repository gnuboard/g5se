<?php
$sub_menu = "200800";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

auth_check_menu($auth, $sub_menu, 'r');

$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['fr_date']) : G5_TIME_YMD;
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['to_date']) : G5_TIME_YMD;

$g5['title'] = '접속자집계';
admin_layout_start($g5['title'], 'visit');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">
<?php
include __DIR__.'/visit.sub.php';

$colspan = 6;

$sql_common = " from {$g5['visit_table']} ";
$sql_search = " where vi_date between :fr_date and :to_date ";
$sql_params = array(':fr_date' => $fr_date, ':to_date' => $to_date);
if (isset($domain) && $domain !== '') {
    $sql_search .= " and vi_referer like :domain ";
    $sql_params[':domain'] = '%'.$domain.'%';
}

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search} ";
$row = sql_pdo_fetch($sql, $sql_params);
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
$result = sql_pdo_query($sql, $sql_params);
?>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
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
    for ($i=0; $row=sql_pdo_fetch_array($result); $i++) {
        $brow = $row['vi_browser'];
        if(!$brow)
            $brow = get_brow($row['vi_agent']);

        $os = $row['vi_os'];
        if(!$os)
            $os = get_os($row['vi_agent']);

        $device = $row['vi_device'];

        $link = '';
        $link2 = '';
        $referer = '';
        $title = '';
        if ($row['vi_referer']) {

            $referer = get_text(cut_str($row['vi_referer'], 255, ''));
            $referer = urldecode($referer);

            if (!is_utf8($referer)) {
                $referer = iconv_utf8($referer);
            }

            $title = str_replace(array('<', '>', '&'), array("&lt;", "&gt;", "&amp;"), $referer);
            $link = '<a href="'.get_text($row['vi_referer']).'" target="_blank">';
            $link = str_replace('&', "&amp;", $link);
            $link2 = '</a>';
        }

        if ($is_admin == 'super')
            $ip = $row['vi_ip'];
        else
            $ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", G5_IP_DISPLAY, $row['vi_ip']);

        if ($brow == '기타') { $brow = '<span title="'.get_text($row['vi_agent']).'">'.$brow.'</span>'; }
        if ($os == '기타') { $os = '<span title="'.get_text($row['vi_agent']).'">'.$os.'</span>'; }

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_category"><?php echo $ip ?></td>
        <td><?php echo $link ?><?php echo $title ?><?php echo $link2 ?></td>
        <td class="td_category td_category1"><?php echo $brow ?></td>
        <td class="td_category td_category3"><?php echo $os ?></td>
        <td class="td_category td_category2"><?php echo $device; ?></td>
        <td class="td_datetime"><?php echo $row['vi_date'] ?> <?php echo $row['vi_time'] ?></td>
    </tr>

    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없거나 관리자에 의해 삭제되었습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<?php
if (isset($domain))
    $qstr .= "&amp;domain=$domain";
$qstr .= "&amp;page=";

$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, G5_ADMIN_URL.'/visit_list?'.$qstr);
echo $pagelist;

?>
<form class="local_sch01 local_sch" method="get" action="<?php echo G5_ADMIN_URL; ?>/visit_excel_download">
    <input type="hidden" name="mode" value="list">
    <input type="hidden" name="fr_date" value="<?php echo htmlspecialchars($fr_date, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="to_date" value="<?php echo htmlspecialchars($to_date, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if (isset($domain) && $domain !== '') { ?>
    <input type="hidden" name="domain" value="<?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?>">
    <?php } ?>
    <button type="submit" class="btn btn_02">엑셀 다운로드</button>
</form>
</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
