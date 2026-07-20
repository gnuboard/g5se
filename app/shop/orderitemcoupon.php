<?php
include_once('./_common.php');

if($is_guest)
    exit;

// 상품정보
$pattern = '#[/\'\"%=*\#\(\)\|\+\&\!\$~\{\}\[\]`;:\?\^\,]#';
$it_id  = isset($_POST['it_id']) ? preg_replace($pattern, '', $_POST['it_id']) : '';
$sw_direct = isset($_POST['sw_direct']) ? clean_xss_tags($_POST['sw_direct'], 1, 1) : '';
$it = get_shop_item($it_id, true);

// 상품 총 금액
if($sw_direct)
    $cart_id = get_session('ss_cart_direct');
else
    $cart_id = get_session('ss_cart_id');

$sql = " select SUM( IF(io_type = '1', io_price * ct_qty, (ct_price + io_price) * ct_qty)) as sum_price
            from {$g5['g5_shop_cart_table']}
            where od_id = '$cart_id'
              and it_id = '$it_id' ";
$ct = sql_fetch($sql);
$item_price = $ct['sum_price'];

// 쿠폰정보
$sql = " select *
            from {$g5['g5_shop_coupon_table']}
            where mb_id IN ( '{$member['mb_id']}', '전체회원' )
              and cp_start <= '".G5_TIME_YMD."'
              and cp_end >= '".G5_TIME_YMD."'
              and cp_minimum <= '$item_price'
              and (
                    ( cp_method = '0' and cp_target = '{$it['it_id']}' )
                    OR
                    ( cp_method = '1' and ( cp_target IN ( '{$it['ca_id']}', '{$it['ca_id2']}', '{$it['ca_id3']}' ) ) )
                  ) ";
$result = sql_pdo_query($sql);
$count = sql_num_rows($result);
?>

<!-- 쿠폰 선택 시작 { -->
<div class="od_coupon_wrap">
	<div id="cp_frm" class="od_coupon">
    <h3>쿠폰선택</h3>
    
	    <?php if($count > 0) { ?>
	    <div class="tbl_head02 tbl_wrap">
	        <table>
	        <caption>쿠폰 선택</caption>
	        <thead>
	        <tr>
	            <th scope="col">쿠폰명</th>
	            <th scope="col">할인금액</th>
	            <th scope="col">적용</th>
	        </tr>
	        </thead>
	        <tbody>
	        <?php
	        for($i=0; $row=sql_fetch_array($result); $i++) {
	            // 사용한 쿠폰인지 체크
	            if(is_used_coupon($member['mb_id'], $row['cp_id']))
	                continue;
	
	            $dc = 0;
	            if($row['cp_type']) {
	                $dc = floor(($item_price * ($row['cp_price'] / 100)) / $row['cp_trunc']) * $row['cp_trunc'];
	            } else {
	                $dc = $row['cp_price'];
	            }
	
	            if($row['cp_maximum'] && $dc > $row['cp_maximum'])
	                $dc = $row['cp_maximum'];
	        ?>
	        <tr>
	            <td>
	                <input type="hidden" name="f_cp_id[]" value="<?php echo $row['cp_id']; ?>">
	                <input type="hidden" name="f_cp_prc[]" value="<?php echo $dc; ?>">
	                <input type="hidden" name="f_cp_subj[]" value="<?php echo $row['cp_subject']; ?>">
	                <?php echo get_text($row['cp_subject']); ?>
	            </td>
	            <td class="td_numbig"><?php echo number_format($dc); ?></td>
	            <td class="td_mngsmall"><button type="button" class="cp_apply">적용</button></td>
	        </tr>
	        <?php
	        }
	        ?>
	        </tbody>
	        </table>
	    </div>
	    <?php
	    } else {
	        echo '<div class="empty_list">사용할 수 있는 쿠폰이 없습니다.</div>';
	    }
	    ?>
	    <div class="btn_confirm">
	        <button type="button" id="cp_close" class="btn_close"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></button>
	    </div>
    </div>
</div>
<!-- } 쿠폰 선택 끝 -->