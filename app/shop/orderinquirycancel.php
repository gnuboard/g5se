<?php
include_once('./_common.php');

$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';

$cancel_reasons = [
    'change_mind'      => ['구매자 사유', '상품이 필요하지 않게 됨', '상품이 필요하지 않게 되었어요'],
    'wrong_order'      => ['구매자 사유', '상품 또는 옵션을 잘못 선택함', '상품이나 옵션을 잘못 선택했어요'],
    'delivery_change'  => ['구매자 사유', '배송 정보 변경 필요', '배송 정보를 변경하고 싶어요'],
    'payment_change'   => ['구매자 사유', '결제수단 변경 후 재주문', '다른 결제수단으로 다시 주문할게요'],
    'out_of_stock'     => ['판매자 사유', '재고 부족 안내', '재고 부족 안내를 받았어요'],
    'delivery_delay'   => ['판매자 사유', '배송 지연', '배송이 예상보다 늦어졌어요'],
    'product_mismatch' => ['판매자 사유', '안내된 상품 정보 불일치', '안내받은 상품 정보가 달라요'],
    'seller_agreement' => ['협의 취소', '판매자와 취소 협의', '판매자와 취소를 협의했어요'],
    'other'            => ['기타 사유', '기타', '다른 사유가 있어요'],
];
$cancel_reason = isset($_POST['cancel_reason']) ? $_POST['cancel_reason'] : '';
$cancel_detail = isset($_POST['cancel_detail']) ? trim(strip_tags($_POST['cancel_detail'])) : '';
if (!isset($cancel_reasons[$cancel_reason]) || $cancel_detail === '') {
    alert('취소 사유와 상세 사유를 입력해 주세요.');
}
$cancel_detail = mb_substr($cancel_detail, 0, 60, 'UTF-8');
$cancel_memo = '['.$cancel_reasons[$cancel_reason][0].'] '.$cancel_reasons[$cancel_reason][1];
if ($cancel_detail !== $cancel_reasons[$cancel_reason][2]) {
    $cancel_memo .= ' - '.$cancel_detail;
}

// 세션에 저장된 토큰과 폼으로 넘어온 토큰을 비교하여 틀리면 에러
if ($token && get_session("ss_token") == $token) {
    // 맞으면 세션을 지워 다시 입력폼을 통해서 들어오도록 한다.
    set_session("ss_token", "");
} else {
    set_session("ss_token", "");
    alert("토큰 에러", G5_SHOP_URL);
}

$od = sql_pdo_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = :od_id and mb_id = :mb_id ",
                   [':od_id' => $od_id, ':mb_id' => $member['mb_id']]);

if (! (isset($od['od_id']) && $od['od_id'])) {
    alert("존재하는 주문이 아닙니다.");
}

// 주문상품의 상태가 주문인지 체크
$ct = sql_pdo_fetch(" select SUM(IF(ct_status = '주문', 1, 0)) as od_count2,
                            COUNT(*) as od_count1
                       from {$g5['g5_shop_cart_table']}
                      where od_id = :od_id ",
                   [':od_id' => $od_id]);

$uid = function_exists('get_shop_uid') ? get_shop_uid('order', $od['od_id'], $od['od_time'], $od['od_ip']) : md5($od['od_id'].$od['od_time'].$od['od_ip']);

if($od['od_cancel_price'] > 0 || $ct['od_count1'] != $ct['od_count2']) {
    alert("취소할 수 있는 주문이 아닙니다.", G5_SHOP_URL."/orderinquiryview.php?od_id=$od_id&amp;uid=$uid");
}

// PG 결제 취소
if($od['od_tno']) {
    switch($od['od_pg']) {
        case 'lg':
            require_once('./settle_lg.inc.php');
            $LGD_TID    = $od['od_tno'];        //LG유플러스으로 부터 내려받은 거래번호(LGD_TID)

            $xpay = new XPay($configPath, $CST_PLATFORM);

            // Mert Key 설정
            $xpay->set_config_value('t'.$LGD_MID, $config['cf_lg_mert_key']);
            $xpay->set_config_value($LGD_MID, $config['cf_lg_mert_key']);
            $xpay->Init_TX($LGD_MID);

            $xpay->Set("LGD_TXNAME", "Cancel");
            $xpay->Set("LGD_TID", $LGD_TID);

            if ($xpay->TX()) {
                //1)결제취소결과 화면처리(성공,실패 결과 처리를 하시기 바랍니다.)
                /*
                echo "결제 취소요청이 완료되었습니다.  <br>";
                echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
                echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";
                */
            } else {
                //2)API 요청 실패 화면처리
                $msg = "결제 취소요청이 실패하였습니다.\\n";
                $msg .= "TX Response_code = " . $xpay->Response_Code() . "\\n";
                $msg .= "TX Response_msg = " . $xpay->Response_Msg();

                alert($msg);
            }
            break;
        case 'toss':
            $cancel_msg = '주문자 본인 취소-'.$cancel_memo;
            include_once(G5_SHOP_PATH.'/toss/toss_cancel.php');
            break;
        case 'inicis':
            include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');
            $cancel_msg = '주문자 본인 취소-'.$cancel_memo;

            $args = array(
                'paymethod' => get_type_inicis_paymethod($od['od_settle_case']),
                'tid' => $od['od_tno'],
                'msg' => $cancel_msg
            );

            $response = inicis_tid_cancel($args);
            $result = json_decode($response, true);

            $res_cd = '';
            $res_msg = 'curl 로 데이터를 받지 못했습니다.';

            if (isset($result['resultCode'])) {
                $res_cd = $result['resultCode'];
                $res_msg = $result['resultMsg'];
            } else {
                $res_cd = '';
                $res_msg = 'curl 로 데이터를 받지 못했습니다.';
            }

            if($res_cd != '00') {
                alert($res_msg.' 코드 : '.$res_cd);
            }
            break;
        case 'nicepay':
            include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');
            $cancel_msg = '주문자 본인 취소-'.$cancel_memo;

            $tno = $od['od_tno'];

            $cancelAmt = $od['od_receipt_price'];

            // 0:전체 취소, 1:부분 취소(별도 계약 필요)
            $partialCancelCode = 0;

            include G5_SHOP_PATH.'/nicepay/cancel_process.php';

            $res_cd = '';
            $res_msg = 'curl 로 데이터를 받지 못하거나 통신에 실패했습니다.';
            
            if (isset($result['ResultCode'])) {

                $res_cd = $result['ResultCode'];

                // 실패했다면
                if ($result['ResultCode'] !== '2001') {
                    $res_msg = $result['ResultMsg'];
                }
            }

            if($res_cd != '2001') {
                alert($res_msg.' 코드 : '.$res_cd);
            }
            break;
        default:
            require_once('./settle_kcp.inc.php');

            $_POST['tno'] = $od['od_tno'];
            $_POST['req_tx'] = 'mod';
            $_POST['mod_type'] = 'STSC';
            if($od['od_escrow']) {
                $_POST['req_tx'] = 'mod_escrow';
                $_POST['mod_type'] = 'STE2';
                if($od['od_settle_case'] == '가상계좌')
                    $_POST['mod_type'] = 'STE5';
            }
            $_POST['mod_desc'] = iconv("utf-8", "euc-kr", '주문자 본인 취소-'.$cancel_memo);
            $_POST['site_cd'] = $default['de_kcp_mid'];

            // 취소내역 한글깨짐방지
            setlocale(LC_CTYPE, 'ko_KR.euc-kr');

            include G5_SHOP_PATH.'/kcp/pp_ax_hub.php';

            // locale 설정 초기화
            setlocale(LC_CTYPE, '');
    }
}

// 장바구니 자료 취소
sql_pdo_query(" update {$g5['g5_shop_cart_table']} set ct_status = '취소' where od_id = :od_id ", [':od_id' => $od_id]);

// 주문 취소
$cancel_memo  = strip_tags($cancel_memo);
$cancel_price = $od['od_cart_price'];

sql_pdo_query(" update {$g5['g5_shop_order_table']}
                  set od_send_cost = '0', od_send_cost2 = '0',
                      od_receipt_price = '0', od_receipt_point = '0', od_misu = '0',
                      od_cancel_price = :cancel_price,
                      od_cart_coupon = '0', od_coupon = '0', od_send_coupon = '0',
                      od_status = '취소',
                      od_shop_memo = concat(od_shop_memo, :memo)
                where od_id = :od_id and od_cancel_price = 0 ",
              [
                  ':cancel_price' => $cancel_price,
                  ':memo'         => "\n주문자 본인 직접 취소 - ".G5_TIME_YMDHIS." (취소이유 : {$cancel_memo})",
                  ':od_id'        => $od_id,
              ]);

// 주문취소 회원의 포인트를 되돌려 줌
// get_sql_affected_rows 함수가 존재하지 않으면 포인트를 돌려주는것을 실행 할수 없음
$affected = function_exists('get_sql_affected_rows') ? get_sql_affected_rows() : 0;

if ($od['od_receipt_point'] > 0 && $affected) {
    insert_point($member['mb_id'], $od['od_receipt_point'], "주문번호 $od_id 본인 취소", '@shop_order', $od_id, 'cancel');
}

goto_url(G5_SHOP_URL."/orderinquiryview.php?od_id=$od_id&amp;uid=$uid");
