<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$g5['title'] = "위시리스트";
include_once('./_head.php');

$items = array();
$sql  = " select a.wi_id, a.wi_time, b.* from {$g5['g5_shop_wish_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id ) ";
$sql .= " where a.mb_id = :mb_id order by a.wi_id desc ";
$result = sql_pdo_query($sql, [':mb_id' => $member['mb_id']]);
for ($i = 0; $row = sql_pdo_fetch_array($result); $i++) {
    $out_cd = '';
    $tmp = sql_pdo_fetch(
        " select count(*) as cnt from {$g5['g5_shop_item_option_table']} where it_id = :it_id and io_type = '0' ",
        [':it_id' => $row['it_id']]
    );
    if (isset($tmp['cnt']) && $tmp['cnt']) {
        $out_cd = 'no';
    }
    if ($row['it_tel_inq']) {
        $out_cd = 'tel_inq';
    }
    $row['_idx'] = $i;
    $row['_out_cd'] = $out_cd;
    $row['_soldout'] = is_soldout($row['it_id']);
    $items[] = $row;
}

$wish_count = count($items);
?>

<style>
.m-wishlist { display: flex; flex-direction: column; gap: 18px; }
.m-wishlist-head {
    display: flex; align-items: flex-end; justify-content: space-between; gap: 16px;
    padding-bottom: 16px; border-bottom: 1px solid var(--m-border);
}
.m-wishlist-title { margin: 0; font-size: 28px; line-height: 1.2; color: var(--m-text); }
.m-wishlist-count { color: var(--m-primary); font-weight: 700; }
.m-wishlist-sub { margin: 6px 0 0; color: var(--m-text-soft); font-size: var(--m-text-sm); }
.m-wishlist-list { display: flex; flex-direction: column; gap: 12px; margin: 0; padding: 0; list-style: none; }
.m-wishlist-item {
    display: grid; grid-template-columns: 40px 112px 1fr auto; gap: 16px; align-items: center;
    padding: 14px; border: 1px solid var(--m-border); border-radius: var(--m-radius);
    background: var(--m-surface); box-shadow: var(--m-shadow);
}
.m-wishlist-check { display: flex; justify-content: center; }
.m-wishlist-checkbox {
    width: 20px; height: 20px; margin: 0; accent-color: var(--m-primary);
}
.m-wishlist-thumb {
    display: block; width: 112px; aspect-ratio: 1 / 1; overflow: hidden;
    border-radius: var(--m-radius-sm); background: var(--m-surface-2);
}
.m-wishlist-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.m-wishlist-info { min-width: 0; display: flex; flex-direction: column; gap: 7px; }
.m-wishlist-name {
    color: var(--m-text); text-decoration: none; font-size: var(--m-text-md); font-weight: 700;
    line-height: 1.35;
}
.m-wishlist-name:hover { color: var(--m-primary); }
.m-wishlist-basic { margin: 0; color: var(--m-text-soft); font-size: var(--m-text-sm); line-height: 1.5; }
.m-wishlist-meta { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; color: var(--m-text-faint); font-size: var(--m-text-xs); }
.m-wishlist-price { color: var(--m-text); font-weight: 800; font-size: var(--m-text-lg); white-space: nowrap; }
.m-wishlist-actions { display: flex; align-items: center; justify-content: flex-end; gap: 8px; }
.m-wishlist-btn, a.m-wishlist-btn, a.m-wishlist-btn:link, a.m-wishlist-btn:visited {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px;
    height: 38px; padding: 0 14px; border-radius: var(--m-radius-sm);
    border: 1px solid var(--m-border); background: var(--m-surface-2);
    color: var(--m-text); text-decoration: none; font-weight: 700; cursor: pointer;
}
.m-wishlist-btn:hover, a.m-wishlist-btn:hover { border-color: var(--m-primary); color: var(--m-primary); }
/* primary — !important 로 default_shop.css a{color:#000} 등 cascade 차단 */
a.m-wishlist-btn-primary, a.m-wishlist-btn-primary:link, a.m-wishlist-btn-primary:visited,
a.m-wishlist-btn-primary:hover, a.m-wishlist-btn-primary:active,
button.m-wishlist-btn-primary, .m-wishlist-btn-primary {
    background: var(--m-primary) !important;
    border-color: var(--m-primary) !important;
    color: #fff !important;
}
a.m-wishlist-btn-primary:hover, button.m-wishlist-btn-primary:hover { filter: brightness(0.96); }
.m-wishlist-delete {
    width: 38px; padding: 0; color: var(--m-text-soft);
}
.m-wishlist-delete:hover { color: #ef4444; border-color: #ef4444; }
.m-wishlist-state {
    display: inline-flex; align-items: center; height: 24px; padding: 0 9px;
    border-radius: 999px; background: var(--m-surface-2); color: var(--m-text-soft);
    font-size: var(--m-text-xs); font-weight: 700;
}
.m-wishlist-state-soldout { color: #ef4444; background: rgba(239, 68, 68, 0.12); }
.m-wishlist-footer {
    position: sticky; bottom: 0; z-index: 5;
    display: flex; justify-content: space-between; align-items: center; gap: 12px;
    margin-top: 4px; padding: 12px; border: 1px solid var(--m-border);
    border-radius: var(--m-radius); background: color-mix(in srgb, var(--m-surface) 92%, transparent);
    backdrop-filter: blur(10px); box-shadow: var(--m-shadow);
}
.m-wishlist-footer-actions { display: flex; gap: 8px; }
.m-wishlist-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;
    min-height: 320px; border: 1px dashed var(--m-border); border-radius: var(--m-radius);
    background: var(--m-surface); color: var(--m-text-soft); text-align: center;
}
.m-wishlist-empty strong { color: var(--m-text); font-size: var(--m-text-lg); }
@media (max-width: 760px) {
    .m-wishlist-head { align-items: flex-start; flex-direction: column; }
    .m-wishlist-item { grid-template-columns: 32px 88px 1fr; gap: 12px; align-items: start; }
    .m-wishlist-thumb { width: 88px; }
    .m-wishlist-actions { grid-column: 2 / -1; justify-content: flex-start; }
    .m-wishlist-price { font-size: var(--m-text-md); }
    .m-wishlist-footer { align-items: stretch; flex-direction: column; }
    .m-wishlist-footer-actions { display: grid; grid-template-columns: 1fr 1fr; width: 100%; }
}
</style>

<div class="m-wishlist">
    <header class="m-wishlist-head">
        <div>
            <h1 class="m-wishlist-title">위시리스트 <span class="m-wishlist-count"><?php echo number_format($wish_count); ?></span></h1>
            <p class="m-wishlist-sub">담아둔 상품을 선택해 장바구니에 넣거나 바로 주문할 수 있습니다.</p>
        </div>
        <a href="<?php echo G5_SHOP_URL; ?>" class="m-wishlist-btn">쇼핑 계속하기</a>
    </header>

    <?php if ($wish_count > 0) { ?>
    <form name="fwishlist" method="post" action="<?php echo G5_SHOP_URL; ?>/cartupdate">
        <input type="hidden" name="act" value="multi">
        <input type="hidden" name="sw_direct" value="">
        <input type="hidden" name="prog" value="wish">

        <ul class="m-wishlist-list">
        <?php foreach ($items as $row) {
            $idx = (int) $row['_idx'];
            $it_id = $row['it_id'];
            $item_url = shop_item_url($it_id);
            $image = get_it_image($it_id, 224, 224, '', '', stripslashes($row['it_name']));
            $can_check = !$row['_soldout'];
        ?>
            <li class="m-wishlist-item">
                <div class="m-wishlist-check">
                    <?php if ($can_check) { ?>
                    <input type="checkbox"
                           name="chk_it_id[<?php echo $idx; ?>]"
                           value="1"
                           id="chk_it_id_<?php echo $idx; ?>"
                           class="m-wishlist-checkbox"
                           onclick="out_cd_check(this, '<?php echo $row['_out_cd']; ?>');">
                    <?php } else { ?>
                    <span class="m-wishlist-state m-wishlist-state-soldout">품절</span>
                    <?php } ?>
                    <input type="hidden" name="it_id[<?php echo $idx; ?>]" value="<?php echo $it_id; ?>">
                    <input type="hidden" name="io_type[<?php echo $it_id; ?>][0]" value="0">
                    <input type="hidden" name="io_id[<?php echo $it_id; ?>][0]" value="">
                    <input type="hidden" name="io_value[<?php echo $it_id; ?>][0]" value="<?php echo get_text($row['it_name']); ?>">
                    <input type="hidden" name="ct_qty[<?php echo $it_id; ?>][0]" value="1">
                </div>

                <a href="<?php echo $item_url; ?>" class="m-wishlist-thumb"><?php echo $image; ?></a>

                <div class="m-wishlist-info">
                    <a href="<?php echo $item_url; ?>" class="m-wishlist-name"><?php echo stripslashes($row['it_name']); ?></a>
                    <?php if (!empty($row['it_basic'])) { ?>
                        <p class="m-wishlist-basic"><?php echo stripslashes($row['it_basic']); ?></p>
                    <?php } ?>
                    <div class="m-wishlist-meta">
                        <span>담은 날 <?php echo substr($row['wi_time'], 0, 10); ?></span>
                        <?php if ($row['_out_cd'] === 'no') { ?><span class="m-wishlist-state">옵션 선택 필요</span><?php } ?>
                        <?php if ($row['_out_cd'] === 'tel_inq') { ?><span class="m-wishlist-state">전화문의</span><?php } ?>
                    </div>
                </div>

                <div class="m-wishlist-actions">
                    <strong class="m-wishlist-price"><?php echo display_price(get_price($row), $row['it_tel_inq']); ?></strong>
                    <a href="<?php echo G5_SHOP_URL; ?>/wishupdate?w=d&amp;wi_id=<?php echo $row['wi_id']; ?>" class="m-wishlist-btn m-wishlist-delete" title="삭제">
                        <i class="fa fa-trash" aria-hidden="true"></i><span class="sound_only">삭제</span>
                    </a>
                </div>
            </li>
        <?php } ?>
        </ul>

        <div class="m-wishlist-footer">
            <label style="display:inline-flex; align-items:center; gap:8px; color:var(--m-text-soft); font-weight:700;">
                <input type="checkbox" class="m-wishlist-checkbox" onclick="toggle_wishlist_all(this);"> 전체 선택
            </label>
            <div class="m-wishlist-footer-actions">
                <button type="submit" class="m-wishlist-btn" onclick="return fwishlist_check(document.fwishlist,'');">장바구니 담기</button>
                <button type="submit" class="m-wishlist-btn m-wishlist-btn-primary" onclick="return fwishlist_check(document.fwishlist,'direct_buy');">주문하기</button>
            </div>
        </div>
    </form>
    <?php } else { ?>
        <div class="m-wishlist-empty">
            <strong>보관함이 비었습니다.</strong>
            <span>상품 목록에서 하트를 눌러 관심 상품을 담아보세요.</span>
            <a href="<?php echo G5_SHOP_URL; ?>" class="m-wishlist-btn m-wishlist-btn-primary">상품 보러가기</a>
        </div>
    <?php } ?>
</div>

<script>
function out_cd_check(fld, out_cd)
{
    if (out_cd == 'no') {
        alert("옵션이 있는 상품입니다.\n\n상품을 클릭하여 상품페이지에서 옵션을 선택한 후 주문하십시오.");
        fld.checked = false;
        return;
    }

    if (out_cd == 'tel_inq') {
        alert("이 상품은 전화로 문의해 주십시오.\n\n장바구니에 담아 구입하실 수 없습니다.");
        fld.checked = false;
        return;
    }
}

function toggle_wishlist_all(source)
{
    var form = document.fwishlist;
    if (!form) return;
    var checks = form.querySelectorAll("input[name^='chk_it_id']");
    for (var i = 0; i < checks.length; i++) {
        if (!checks[i].disabled) checks[i].checked = source.checked;
    }
}

function fwishlist_check(f, act)
{
    var k = 0;
    var checks = f.querySelectorAll("input[name^='chk_it_id']");

    for (var i = 0; i < checks.length; i++) {
        if (checks[i].checked) k++;
    }

    if (k == 0) {
        alert("상품을 하나 이상 체크 하십시오");
        return false;
    }

    f.sw_direct.value = (act == "direct_buy") ? 1 : 0;

    return true;
}
</script>

<?php
include_once('./_tail.php');
