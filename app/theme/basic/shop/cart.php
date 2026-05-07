<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$g5['title'] = '장바구니';
$cart_action_url = G5_SHOP_URL.'/cartupdate';

include_once('./_head.php');
?>

<script src="<?php echo G5_JS_URL; ?>/shop.js?ver=<?php echo G5_JS_VER; ?>"></script>
<script src="<?php echo G5_JS_URL; ?>/shop.override.js?ver=<?php echo G5_JS_VER; ?>"></script>

<?php
$cart_items = array();
$tot_point = 0;
$tot_sell_price = 0;
$send_cost = 0;
$continue_ca_id = '';

$result = sql_pdo_query(" select a.ct_id, a.it_id, a.it_name, a.ct_price, a.ct_point, a.ct_qty,
                                  a.ct_status, a.ct_send_cost,
                                  b.ca_id, b.ca_id2, b.ca_id3
                             from {$g5['g5_shop_cart_table']} a
                             left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
                            where a.od_id = :od_id
                            group by a.it_id
                            order by a.ct_id ",
                        [':od_id' => $s_cart_id]);

for ($i = 0; $row = sql_fetch_array($result); $i++) {
    $sum = sql_pdo_fetch(" select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                                  SUM(ct_point * ct_qty) as point,
                                  SUM(ct_qty) as qty
                             from {$g5['g5_shop_cart_table']}
                            where it_id = :it_id and od_id = :od_id ",
                        [':it_id' => $row['it_id'], ':od_id' => $s_cart_id]);

    if ($i == 0) {
        $continue_ca_id = $row['ca_id'];
    }

    switch ($row['ct_send_cost']) {
        case 1:
            $ct_send_cost = '착불';
            break;
        case 2:
            $ct_send_cost = '무료';
            break;
        default:
            $ct_send_cost = '선불';
            break;
    }

    $row['_idx'] = $i;
    $row['_qty'] = (int) $sum['qty'];
    $row['_point'] = (int) $sum['point'];
    $row['_sell_price'] = (int) $sum['price'];
    $row['_send_cost_label'] = $ct_send_cost;
    $row['_options'] = print_item_options($row['it_id'], $s_cart_id);
    $cart_items[] = $row;

    $tot_point += (int) $sum['point'];
    $tot_sell_price += (int) $sum['price'];
}

if (count($cart_items) > 0) {
    $send_cost = get_sendcost($s_cart_id, 0);
}
$tot_price = $tot_sell_price + $send_cost;
?>

<style>
.shop-content > h1:first-child { display: none; }
.m-cart { display: flex; flex-direction: column; gap: 18px; }
.m-cart-head {
    display: flex; align-items: flex-end; justify-content: space-between; gap: 16px;
    padding-bottom: 16px; border-bottom: 1px solid var(--m-border);
}
.m-cart-title { margin: 0; font-size: 28px; line-height: 1.2; color: var(--m-text); }
.m-cart-count { color: var(--m-primary); font-weight: 800; }
.m-cart-sub { margin: 6px 0 0; color: var(--m-text-soft); font-size: var(--m-text-sm); }
.m-cart-btn, a.m-cart-btn, a.m-cart-btn:link, a.m-cart-btn:visited {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px;
    min-height: 40px; padding: 0 15px; border: 1px solid var(--m-border);
    border-radius: var(--m-radius-sm); background: var(--m-surface-2);
    color: var(--m-text); text-decoration: none; font-weight: 700; cursor: pointer;
}
.m-cart-btn:hover, a.m-cart-btn:hover { border-color: var(--m-primary); color: var(--m-primary); }
/* primary — !important 로 default_shop.css a{color:#000} 등 cascade 차단 */
a.m-cart-btn-primary, a.m-cart-btn-primary:link, a.m-cart-btn-primary:visited,
a.m-cart-btn-primary:hover, a.m-cart-btn-primary:active,
button.m-cart-btn-primary, .m-cart-btn-primary {
    background: var(--m-primary) !important;
    border-color: var(--m-primary) !important;
    color: #fff !important;
}
a.m-cart-btn-primary:hover, button.m-cart-btn-primary:hover { filter: brightness(0.96); }
.m-cart-btn-danger:hover { border-color: #ef4444; color: #ef4444; }
.m-cart-list-wrap { min-width: 0; width: 100%; }
.m-cart-checkline { display: inline-flex; align-items: center; gap: 8px; color: var(--m-text-soft); font-weight: 700; }
.m-cart-checkbox { width: 20px; height: 20px; margin: 0; accent-color: var(--m-primary); }
.m-cart-delete-actions { display: flex; gap: 8px; }
.m-cart-table-wrap {
    overflow-x: auto; border: 1px solid var(--m-border); border-radius: var(--m-radius);
    background: var(--m-surface); box-shadow: var(--m-shadow);
}
.m-cart-table { width: 100%; min-width: 960px; border-collapse: collapse; table-layout: fixed; }
.m-cart-table th {
    height: 58px; padding: 0 14px; border-bottom: 1px solid var(--m-border);
    color: var(--m-text); font-size: 17px; font-weight: 800; text-align: center;
    background: var(--m-surface-2);
}
.m-cart-table td {
    padding: 22px 14px; border-bottom: 1px solid var(--m-border);
    color: var(--m-text); font-size: 17px; text-align: center; vertical-align: middle;
}
.m-cart-table tbody tr:last-child td { border-bottom: 0; }
.m-cart-table .m-cart-col-check { width: 56px; }
.m-cart-table .m-cart-col-product { width: auto; }
.m-cart-table .m-cart-col-qty { width: 86px; }
.m-cart-table .m-cart-col-price,
.m-cart-table .m-cart-col-point,
.m-cart-table .m-cart-col-delivery { width: 104px; }
.m-cart-table .m-cart-col-total { width: 126px; }
.m-cart-product { display: grid; grid-template-columns: 96px minmax(0, 1fr); gap: 18px; align-items: start; text-align: left; }
.m-cart-thumb {
    display: block; width: 96px; aspect-ratio: 1 / 1; overflow: hidden;
    border-radius: var(--m-radius-sm); background: var(--m-surface-2);
}
.m-cart-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.m-cart-info { min-width: 0; display: flex; flex-direction: column; gap: 8px; }
.m-cart-name { color: var(--m-text); text-decoration: none; font-size: 17px; font-weight: 800; line-height: 1.35; }
.m-cart-name:hover { color: var(--m-primary); }
.m-cart-options {
    color: var(--m-text); font-size: 16px; line-height: 1.6;
}
.m-cart-options ul, .m-cart-options ol { margin: 0; padding-left: 18px; }
.m-cart-options li { margin: 3px 0; }
.m-cart-option-action { margin-top: 2px; }
.m-cart-option-action .mod_options {
    height: 34px; padding: 0 12px; border: 1px solid var(--m-border);
    border-radius: var(--m-radius-sm); background: transparent; color: var(--m-text-soft);
    font-size: 14px; font-weight: 700; cursor: pointer;
}
.m-cart-option-action .mod_options:hover { color: var(--m-primary); border-color: var(--m-primary); }
.m-cart-table-num { font-weight: 400; white-space: nowrap; }
.m-cart-table-total { font-size: 17px; font-weight: 900; white-space: nowrap; }
.m-cart-table-actions { display: flex; gap: 8px; padding-top: 12px; }
.m-cart-summary-wrap { width: 100%; padding-top: 14px; border-top: 1px solid var(--m-border); }
.m-cart-summary {
    display: grid; grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0; padding: 0; border: 1px solid var(--m-border);
    border-radius: var(--m-radius); overflow: hidden; box-shadow: var(--m-shadow);
}
.m-cart-summary-row {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    min-height: 92px; padding: 0 38px;
    background: var(--m-surface-2); color: var(--m-text); font-size: var(--m-text-md);
    border-right: 1px solid var(--m-border);
}
.m-cart-summary-row:last-child { border-right: 0; }
.m-cart-summary-row dt { font-weight: 700; }
.m-cart-summary-row dd { margin: 0; white-space: nowrap; }
.m-cart-summary-row strong { color: var(--m-text); font-size: 20px; font-weight: 900; }
.m-cart-summary-total { background: var(--m-text); color: var(--m-bg); border-right: 0; }
.m-cart-summary-total strong { color: #fff; font-size: 24px; }
[data-theme="dark"] .m-cart-summary-total { background: var(--m-bg); color: var(--m-text); }
.m-cart-actions {
    display: flex; justify-content: center; align-items: center; gap: 8px;
    padding-top: 20px;
}
.m-cart-actions .m-cart-btn { min-width: 220px; min-height: 50px; font-size: var(--m-text-md); }
.m-cart-empty {
    grid-column: 1 / -1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;
    min-height: 320px; border: 1px dashed var(--m-border); border-radius: var(--m-radius);
    background: var(--m-surface); color: var(--m-text-soft); text-align: center;
}
.m-cart-empty strong { color: var(--m-text); font-size: var(--m-text-lg); }
.cart-naverpay { margin-top: 4px; }
#mod_option_frm {
    position: absolute; z-index: 10000; max-width: min(520px, calc(100vw - 32px));
    padding: 16px; border: 1px solid var(--m-border); border-radius: var(--m-radius);
    background: var(--m-surface) !important; box-shadow: var(--m-shadow-md); color: var(--m-text) !important;
}
#mod_option_frm *,
#mod_option_frm *::before,
#mod_option_frm *::after {
    border-color: var(--m-border) !important;
}
#mod_option_frm h2,
#mod_option_frm h3 {
    background: transparent !important;
    color: var(--m-text) !important;
    border-color: var(--m-border) !important;
}
#mod_option_frm form,
#mod_option_frm .option_wr,
#mod_option_frm #sit_sel_option,
#mod_option_frm #sit_opt_added,
#mod_option_frm #sit_opt_added li {
    background: var(--m-surface) !important;
    color: var(--m-text) !important;
    border-color: var(--m-border) !important;
}
#mod_option_frm .sit_opt_subj,
#mod_option_frm .sit_opt_prc,
#mod_option_frm #sit_tot_price {
    color: var(--m-text) !important;
}
#mod_option_frm #sit_tot_price,
#mod_option_frm #sit_tot_price span,
#mod_option_frm #sit_tot_price strong {
    display: none !important;
}
#mod_option_frm .opt_name,
#mod_option_frm .opt_count {
    background: transparent !important;
    color: var(--m-text) !important;
}
#mod_option_frm select,
#mod_option_frm input[type="text"],
#mod_option_frm .num_input {
    background: var(--m-surface-2) !important;
    border: 1px solid var(--m-border) !important;
    color: var(--m-text) !important;
}
#mod_option_frm .sit_qty_minus,
#mod_option_frm .sit_qty_plus,
#mod_option_frm .sit_opt_del,
#mod_option_frm .btn_close {
    background: var(--m-surface-2) !important;
    border: 1px solid var(--m-border) !important;
    color: var(--m-text-soft) !important;
}
#mod_option_frm .sit_qty_minus:hover,
#mod_option_frm .sit_qty_plus:hover,
#mod_option_frm .sit_opt_del:hover,
#mod_option_frm .btn_close:hover {
    color: var(--m-primary) !important;
    border-color: var(--m-primary) !important;
}
#mod_option_frm .btn_submit {
    background: var(--m-primary) !important;
    border-color: var(--m-primary) !important;
    color: #fff !important;
}
#mod_option_frm .btn_close {
    position: static !important;
    width: 42px !important;
    height: 42px !important;
}
#mod_option_frm .btn_confirm {
    display: flex !important;
    justify-content: flex-end !important;
    gap: 8px !important;
    background: transparent !important;
    border-color: var(--m-border) !important;
}
.mod_option_bg {
    position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.4);
}
@media (max-width: 860px) {
    .m-cart-head { align-items: flex-start; flex-direction: column; }
    .m-cart-delete-actions { width: 100%; display: grid; grid-template-columns: 1fr 1fr; }
    .m-cart-table-wrap { overflow: visible; border: 0; background: transparent; box-shadow: none; }
    .m-cart-table, .m-cart-table thead, .m-cart-table tbody, .m-cart-table tr, .m-cart-table th, .m-cart-table td {
        display: block;
    }
    .m-cart-table { min-width: 0; border-collapse: separate; }
    .m-cart-table colgroup, .m-cart-table thead { display: none; }
    .m-cart-table tbody { display: flex; flex-direction: column; gap: 12px; }
    .m-cart-table td {
        padding: 0; border: 0; text-align: left; font-size: 16px;
    }
    .m-cart-table .m-cart-row {
        display: grid; grid-template-columns: 32px minmax(0, 1fr); gap: 12px;
        padding: 14px; border: 1px solid var(--m-border); border-radius: var(--m-radius);
        background: var(--m-surface); box-shadow: var(--m-shadow);
    }
    .m-cart-row td:first-child { grid-column: 1; grid-row: 1 / span 2; padding-top: 3px; }
    .m-cart-row td:nth-child(2) { grid-column: 2; }
    .m-cart-row td[data-label] {
        grid-column: 2; display: flex; justify-content: space-between; gap: 12px;
        padding-top: 8px; border-top: 1px solid var(--m-border);
        color: var(--m-text); font-weight: 500;
    }
    .m-cart-row td[data-label]::before {
        content: attr(data-label); color: var(--m-text-soft); font-weight: 700;
    }
    .m-cart-row td.m-cart-table-total { font-size: 20px; font-weight: 900; }
    .m-cart-product { grid-template-columns: 82px minmax(0, 1fr); gap: 12px; }
    .m-cart-thumb { width: 82px; }
    .m-cart-name { font-size: 18px; }
    .m-cart-options { font-size: 15px; }
    .m-cart-table-actions { display: grid; grid-template-columns: 1fr 1fr; }
    .m-cart-summary { grid-template-columns: 1fr; }
    .m-cart-summary-row {
        min-height: 66px; padding: 0 18px; border-right: 0; border-bottom: 1px solid var(--m-border);
    }
    .m-cart-summary-row:last-child { border-bottom: 0; }
    .m-cart-actions { display: grid; grid-template-columns: 1fr; }
    .m-cart-actions .m-cart-btn { width: 100%; min-width: 0; }
}
</style>

<div class="m-cart">
    <header class="m-cart-head">
        <p class="m-cart-sub">구매할 상품을 선택한 뒤 주문을 진행하세요.</p>
        <a href="<?php echo $continue_ca_id ? shop_category_url($continue_ca_id) : G5_SHOP_URL; ?>" class="m-cart-btn">쇼핑 계속하기</a>
    </header>

    <?php if (count($cart_items) > 0) { ?>
    <form name="frmcartlist" id="sod_bsk_list" class="2017_renewal_itemform m-cart-list-wrap" method="post" action="<?php echo $cart_action_url; ?>">
        <div class="m-cart-table-wrap">
            <table class="m-cart-table">
                <colgroup>
                    <col class="m-cart-col-check">
                    <col class="m-cart-col-product">
                    <col class="m-cart-col-qty">
                    <col class="m-cart-col-price">
                    <col class="m-cart-col-point">
                    <col class="m-cart-col-delivery">
                    <col class="m-cart-col-total">
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col">
                            <input type="checkbox" name="ct_all" value="1" id="ct_all" checked="checked" class="m-cart-checkbox">
                            <label for="ct_all" class="sound_only">상품 전체 선택</label>
                        </th>
                        <th scope="col">상품명</th>
                        <th scope="col">총수량</th>
                        <th scope="col">판매가</th>
                        <th scope="col">포인트</th>
                        <th scope="col">배송비</th>
                        <th scope="col">소계</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cart_items as $row) {
                    $idx = (int) $row['_idx'];
                    $item_url = shop_item_url($row['it_id']);
                    $image = get_it_image($row['it_id'], 160, 160, '', '', stripslashes($row['it_name']));
                ?>
                    <tr class="m-cart-row">
                        <td>
                            <input type="checkbox" name="ct_chk[<?php echo $idx; ?>]" value="1" id="ct_chk_<?php echo $idx; ?>" checked="checked" class="m-cart-checkbox">
                            <label for="ct_chk_<?php echo $idx; ?>" class="sound_only">상품 선택</label>
                        </td>
                        <td>
                            <div class="m-cart-product">
                                <a href="<?php echo $item_url; ?>" class="m-cart-thumb"><?php echo $image; ?></a>

                                <div class="m-cart-info">
                                    <input type="hidden" name="it_id[<?php echo $idx; ?>]" value="<?php echo $row['it_id']; ?>">
                                    <input type="hidden" name="it_name[<?php echo $idx; ?>]" value="<?php echo get_text($row['it_name']); ?>">
                                    <a href="<?php echo $item_url; ?>" class="m-cart-name"><?php echo stripslashes($row['it_name']); ?></a>

                                    <?php if ($row['_options']) { ?>
                                    <div class="m-cart-options"><?php echo $row['_options']; ?></div>
                                    <div class="m-cart-option-action">
                                        <button type="button" class="mod_options">선택사항수정</button>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                        <td class="m-cart-table-num" data-label="총수량"><?php echo number_format($row['_qty']); ?></td>
                        <td class="m-cart-table-num" data-label="판매가"><?php echo number_format($row['ct_price']); ?></td>
                        <td class="m-cart-table-num" data-label="포인트"><?php echo number_format($row['_point']); ?></td>
                        <td class="m-cart-table-num" data-label="배송비"><?php echo $row['_send_cost_label']; ?></td>
                        <td class="m-cart-table-total" data-label="소계"><span id="sell_price_<?php echo $idx; ?>"><?php echo number_format($row['_sell_price']); ?></span></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="m-cart-table-actions">
            <button type="button" onclick="return form_check('seldelete');" class="m-cart-btn m-cart-btn-danger">선택삭제</button>
            <button type="button" onclick="return form_check('alldelete');" class="m-cart-btn m-cart-btn-danger">비우기</button>
        </div>

        <input type="hidden" name="url" value="<?php echo G5_SHOP_URL; ?>/orderform">
        <input type="hidden" name="records" value="<?php echo count($cart_items); ?>">
        <input type="hidden" name="act" value="">
    </form>

    <section class="m-cart-summary-wrap" aria-label="주문 요약">
        <dl class="m-cart-summary">
            <div class="m-cart-summary-row">
                <dt>배송비</dt>
                <dd><strong><?php echo number_format($send_cost); ?></strong> 원</dd>
            </div>
            <div class="m-cart-summary-row">
                <dt>적립 포인트</dt>
                <dd><strong><?php echo number_format($tot_point); ?></strong> 점</dd>
            </div>
            <div class="m-cart-summary-row m-cart-summary-total">
                <dt>총계 가격</dt>
                <dd><strong><?php echo number_format($tot_price); ?></strong> 원</dd>
            </div>
        </dl>
        <div class="m-cart-actions">
            <a href="<?php echo $continue_ca_id ? shop_category_url($continue_ca_id) : G5_SHOP_URL; ?>" class="m-cart-btn">쇼핑 계속하기</a>
            <button type="button" onclick="return form_check('buy');" class="m-cart-btn m-cart-btn-primary">주문하기</button>
        </div>
        <?php if ($naverpay_button_js) { ?>
        <div class="cart-naverpay"><?php echo $naverpay_request_js.$naverpay_button_js; ?></div>
        <?php } ?>
    </section>
    <?php } else { ?>
        <div class="m-cart-empty">
            <strong>장바구니에 담긴 상품이 없습니다.</strong>
            <span>상품 목록에서 원하는 상품을 장바구니에 담아보세요.</span>
            <a href="<?php echo G5_SHOP_URL; ?>" class="m-cart-btn m-cart-btn-primary">상품 보러가기</a>
        </div>
    <?php } ?>
</div>

<script>
$(function() {
    var close_btn_idx;

    $(".mod_options").click(function() {
        var it_id = $(this).closest("tr").find("input[name^=it_id]").val();
        var $this = $(this);
        close_btn_idx = $(".mod_options").index($(this));

        $.post(
            "<?php echo G5_SHOP_URL; ?>/cartoption",
            { it_id: it_id },
            function(data) {
                $("#mod_option_frm").remove();
                $(".mod_option_bg").remove();
                $this.after("<div id=\"mod_option_frm\"></div><div class=\"mod_option_bg\"></div>");
                $("#mod_option_frm").html(data);
                price_calculate();
            }
        );
    });

    $("input[name=ct_all]").click(function() {
        $("input[name^=ct_chk]").prop("checked", $(this).is(":checked"));
    });

    $(document).on("click", "#mod_option_close, .mod_option_bg", function() {
        $("#mod_option_frm, .mod_option_bg").remove();
        $(".mod_options").eq(close_btn_idx).focus();
    });
});

function fsubmit_check(f) {
    if($("input[name^=ct_chk]:checked").length < 1) {
        alert("구매하실 상품을 하나이상 선택해 주십시오.");
        return false;
    }

    return true;
}

function form_check(act) {
    var f = document.frmcartlist;

    if (!f) return false;

    if (act == "buy") {
        if($("input[name^=ct_chk]:checked").length < 1) {
            alert("주문하실 상품을 하나이상 선택해 주십시오.");
            return false;
        }

        f.act.value = act;
        f.submit();
    } else if (act == "alldelete") {
        f.act.value = act;
        f.submit();
    } else if (act == "seldelete") {
        if($("input[name^=ct_chk]:checked").length < 1) {
            alert("삭제하실 상품을 하나이상 선택해 주십시오.");
            return false;
        }

        f.act.value = act;
        f.submit();
    }

    return true;
}
</script>

<?php
include_once('./_tail.php');
