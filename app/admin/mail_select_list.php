<?php
/*
 * /admin/mail_select_list — 메일 발송 대상 (선택된 회원) 목록 + 발송 폼.
 */
$sub_menu = "200300";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
auth_check_menu($auth, $sub_menu, 'r');

$ma_id = isset($_REQUEST['ma_id']) ? (int) $_REQUEST['ma_id'] : 0;

$ma_last_option = "";

$sql_common = " from {$g5['member_table']} ";
$sql_where = " where (1) ";
$where_params = [];

$mb_id1         = isset($_POST['mb_id1'])       ? (int) $_POST['mb_id1'] : 1;
$mb_id1_from    = isset($_POST['mb_id1_from'])  ? clean_xss_tags(stripslashes($_POST['mb_id1_from']), 1, 1, 30) : '';
$mb_id1_to      = isset($_POST['mb_id1_to'])    ? clean_xss_tags(stripslashes($_POST['mb_id1_to']), 1, 1, 30) : '';
$mb_email       = isset($_POST['mb_email'])     ? clean_xss_tags(stripslashes($_POST['mb_email']), 1, 1, 100) : '';
$mb_mailling    = isset($_POST['mb_mailling'])  ? clean_xss_tags(stripslashes($_POST['mb_mailling']), 1, 1, 100) : '';
$mb_level_from  = isset($_POST['mb_level_from'])? (int) $_POST['mb_level_from'] : 1;
$mb_level_to    = isset($_POST['mb_level_to'])  ? (int) $_POST['mb_level_to'] : 10;
$gr_id           = isset($_POST['gr_id'])        ? clean_xss_tags(stripslashes($_POST['gr_id']), 1, 1, 20) : '';

// 회원ID ..에서 ..까지
if ($mb_id1 != 1) {
    $sql_where .= " and mb_id between :mb_id1_from and :mb_id1_to ";
    $where_params[':mb_id1_from'] = $mb_id1_from;
    $where_params[':mb_id1_to']   = $mb_id1_to;
}

// E-mail에 특정 단어 포함
if ($mb_email != "") {
    $sql_where .= " and mb_email like :mb_email ";
    $where_params[':mb_email'] = '%'.$mb_email.'%';
}

// 메일링
if ($mb_mailling != "") {
    $sql_where .= " and mb_mailling = :mb_mailling ";
    $where_params[':mb_mailling'] = $mb_mailling;
}

// 권한
$sql_where .= " and mb_level between :mb_level_from and :mb_level_to ";
$where_params[':mb_level_from'] = $mb_level_from;
$where_params[':mb_level_to']   = $mb_level_to;

// 게시판그룹회원
if ($gr_id) {
    $stmt2 = sql_pdo_query(" select mb_id from {$g5['group_member_table']} where gr_id = :gr_id order by mb_id ", [':gr_id' => $gr_id]);
    $group_placeholders = [];
    while ($row2 = sql_pdo_fetch_array($stmt2)) {
        $key = ':gm_'.count($group_placeholders);
        $group_placeholders[] = $key;
        $where_params[$key] = $row2['mb_id'];
    }

    if (empty($group_placeholders)) {
        alert('선택하신 게시판 그룹회원이 한명도 없습니다.');
    }

    $sql_where .= " and mb_id in (".implode(',', $group_placeholders).") ";
}

// 탈퇴, 차단된 회원은 제외
$sql_where .= " and mb_leave_date = '' and mb_intercept_date = '' ";

$row = sql_pdo_fetch(" select COUNT(*) as cnt {$sql_common} {$sql_where} ", $where_params);
$cnt = $row['cnt'];
if ($cnt == 0) {
    alert('선택하신 내용으로는 해당되는 회원자료가 없습니다.');
}

// 마지막 옵션을 저장합니다.
$ma_last_option .= "mb_id1={$mb_id1}";
$ma_last_option .= "||mb_id1_from={$mb_id1_from}";
$ma_last_option .= "||mb_id1_to={$mb_id1_to}";
$ma_last_option .= "||mb_email={$mb_email}";
$ma_last_option .= "||mb_mailling={$mb_mailling}";
$ma_last_option .= "||mb_level_from={$mb_level_from}";
$ma_last_option .= "||mb_level_to={$mb_level_to}";
$ma_last_option .= "||gr_id={$gr_id}";

sql_pdo_query(" update {$g5['mail_table']} set ma_last_option = :ma_last_option where ma_id = :ma_id ",
              [':ma_last_option' => $ma_last_option, ':ma_id' => $ma_id]);

$g5['title'] = "메일발송 대상 회원";
admin_layout_start($g5['title'], 'mail');
?>
<main class="mail-recipient-list-page flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">

<form name="fmailselectlist" id="fmailselectlist" method="post" action="<?php echo G5_ADMIN_URL; ?>/mail_select_update" data-floating-actions="off">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="ma_id" value="<?php echo get_text($ma_id); ?>">

    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th scope="col" class="mail-col-number">번호</th>
                    <th scope="col" class="mail-col-id">회원아이디</th>
                    <th scope="col" class="mail-col-name">이름</th>
                    <th scope="col" class="mail-col-nick">닉네임</th>
                    <th scope="col" class="mail-col-email">E-mail</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = " select mb_id, mb_name, mb_nick, mb_email, mb_datetime $sql_common $sql_where order by mb_id ";
                $result = sql_pdo_query($sql, $where_params);
                $i = 0;
                $ma_list = "";
                $cr = "";
                while ($row = sql_pdo_fetch_array($result)) {
                    $i++;
                    $ma_list .= $cr . $row['mb_email'] . "||" . $row['mb_id'] . "||" . get_text($row['mb_name']) . "||" . $row['mb_nick'] . "||" . $row['mb_datetime'];
                    $cr = "\n";

                    $bg = 'bg' . ($i % 2);
                    ?>
                    <tr class="<?php echo $bg; ?>">
                        <td class="td_num mail-col-number"><?php echo $i ?></td>
                        <td class="td_mbid mail-col-id"><?php echo $row['mb_id'] ?></td>
                        <td class="td_mbname mail-col-name"><?php echo get_text($row['mb_name']); ?></td>
                        <td class="td_mbname mail-col-nick"><?php echo $row['mb_nick'] ?></td>
                        <td class="mail-col-email"><?php echo $row['mb_email'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <textarea name="ma_list" style="display:none"><?php echo html_purifier($ma_list); ?></textarea>
    </div>

    <div class="btn_confirm01 btn_confirm mail-recipient-actions">
        <a href="<?php echo G5_ADMIN_URL ?>/mail_select_form?ma_id=<?php echo $ma_id ?>" class="btn btn_02">뒤로</a>
        <input type="submit" value="메일보내기" class="btn_submit btn">
    </div>

</form>

<?php
?>
</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/mail_select_list — modern shell wrap end
