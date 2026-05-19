<?php
include_once('./_common.php');

define("_ORDERINQUIRY_", true);

$order_info = array();
$request_pwd = isset($_POST['od_pwd']) ? $_POST['od_pwd'] : '';
$od_pwd = get_encrypt_string($request_pwd);
$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';

$inquiry_params = [];

// g5se: 회원 전용 검색 (주문서번호 contains + 주문일자 from/to)
$s_od_id = isset($_GET['s_od_id']) ? preg_replace('/[^0-9]/', '', $_GET['s_od_id']) : '';
$s_fr    = isset($_GET['s_fr']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['s_fr']) ? $_GET['s_fr'] : '';
$s_to    = isset($_GET['s_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['s_to']) ? $_GET['s_to'] : '';
$has_search = ($s_od_id !== '' || $s_fr !== '' || $s_to !== '');

// 회원인 경우
if ($is_member)
{
    $sql_common = " from {$g5['g5_shop_order_table']} where mb_id = :mb_id ";
    $inquiry_params[':mb_id'] = $member['mb_id'];
    if ($s_od_id !== '') {
        $sql_common .= " and od_id LIKE :s_od_id ";
        $inquiry_params[':s_od_id'] = '%'.$s_od_id.'%';
    }
    if ($s_fr !== '') {
        $sql_common .= " and od_time >= :s_fr ";
        $inquiry_params[':s_fr'] = $s_fr.' 00:00:00';
    }
    if ($s_to !== '') {
        $sql_common .= " and od_time <= :s_to ";
        $inquiry_params[':s_to'] = $s_to.' 23:59:59';
    }
}
else if ($od_id && $od_pwd) // 비회원인 경우 주문서번호와 비밀번호가 넘어왔다면
{
    if( defined('G5_MYSQL_PASSWORD_LENGTH') && strlen($od_pwd) === G5_MYSQL_PASSWORD_LENGTH ) {
        $sql_common = " from {$g5['g5_shop_order_table']} where od_id = :od_id and od_pwd = :od_pwd ";
        $inquiry_params[':od_id']  = $od_id;
        $inquiry_params[':od_pwd'] = $od_pwd;
    } else {
        $sql_common = " from {$g5['g5_shop_order_table']} where od_id = :od_id ";
        $inquiry_params[':od_id'] = $od_id;

        $order_info = get_shop_order_data($od_id);
        if (!check_password($request_pwd, $order_info['od_pwd'])) {
            run_event('password_is_wrong', 'shop', $order_info);
            alert('주문이 존재하지 않습니다.');
            exit;
        }

    }
}
else // 그렇지 않다면 로그인으로 가기
{
    goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/orderinquiry.php'));
}

// 테이블의 전체 레코드수만 얻음
$row = sql_pdo_fetch(" select count(*) as cnt " . $sql_common, $inquiry_params);
$total_count = $row['cnt'];

// 비회원 주문확인시 비회원의 모든 주문이 다 출력되는 오류 수정
// 조건에 맞는 주문서가 없다면
if ($total_count == 0)
{
    if (!$is_member) {
        // 비회원 → 이전 페이지로
        alert('주문이 존재하지 않습니다.');
    }
}

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


// 비회원 주문확인의 경우 바로 주문서 상세조회로 이동
if (!$is_member)
{
    if( defined('G5_MYSQL_PASSWORD_LENGTH') && strlen($od_pwd) === G5_MYSQL_PASSWORD_LENGTH ) {
        $row = sql_pdo_fetch(" select od_id, od_time, od_ip from {$g5['g5_shop_order_table']} where od_id = :od_id and od_pwd = :od_pwd ",
                            [':od_id' => $od_id, ':od_pwd' => $od_pwd]);
    } else if( $order_info ){
        if (check_password($request_pwd, $order_info['od_pwd'])) {
            $row = $order_info;
        }
    }

    if ($row['od_id']) {
        $uid = function_exists('get_shop_uid') ? get_shop_uid('order', $row['od_id'], $row['od_time'], $row['od_ip']) : md5($row['od_id'].$row['od_time'].$row['od_ip']);
        set_session('ss_orderview_uid', $uid);
        goto_url(G5_SHOP_URL.'/orderinquiryview?od_id='.$row['od_id'].'&uid='.$uid);
    }
}

$g5['title'] = '주문내역조회';
include_once('./_head.php');
?>

<!-- 주문 내역 시작 { -->
<div id="sod_v">
    <?php
    $limit = " limit $from_record, $rows ";
    include "./orderinquiry.sub.php";
    ?>

    <?php
    // 검색 파라미터를 페이지 링크에 보존
    $_search_qs = http_build_query(array_filter([
        's_od_id' => $s_od_id,
        's_fr'    => $s_fr,
        's_to'    => $s_to,
    ], 'strlen'), '', '&amp;');
    $_paging_qs = trim($qstr.($qstr && $_search_qs ? '&amp;' : '').$_search_qs, '&');
    echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$_paging_qs&amp;page=");
    ?>
</div>
<!-- } 주문 내역 끝 -->

<?php
include_once('./_tail.php');
