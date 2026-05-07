<?php
include_once('./_common.php');

// 테마에 coupon.php 있으면 include
if(defined('G5_THEME_SHOP_PATH')) {
    $theme_coupon_file = G5_THEME_SHOP_PATH.'/coupon.php';
    if(is_file($theme_coupon_file)) {
        include_once($theme_coupon_file);
        return;
        unset($theme_coupon_file);
    }
}

if ($is_guest)
    alert_close('회원만 조회하실 수 있습니다.');

$g5['title'] = $member['mb_nick'].' 님의 쿠폰 내역';
include_once(G5_PATH.'/head.sub.php');
// gnu5se: modern 토큰 (var(--m-*)) 로드 — head.sub.php 가 자동 로드 안 하므로 명시적 require
if(defined('G5_THEME_PATH') && is_file(G5_THEME_PATH.'/modern/_head.inc.php')) {
    require_once(G5_THEME_PATH.'/modern/_head.inc.php');
}

$sql = " select cp_id, cp_subject, cp_method, cp_target, cp_start, cp_end, cp_type, cp_price
            from {$g5['g5_shop_coupon_table']}
            where mb_id IN ( '{$member['mb_id']}', '전체회원' )
              and cp_start <= '".G5_TIME_YMD."'
              and cp_end >= '".G5_TIME_YMD."'
            order by cp_no ";
$result = sql_query($sql);
?>
<style>
/* gnu5se: 쿠폰 popup — modern card list */
#coupon {
    background: var(--m-bg);
    color: var(--m-text);
    padding: 24px 20px;
    min-height: 100vh;
    box-sizing: border-box;
}
#coupon #win_title {
    background: transparent !important;
    color: var(--m-text) !important;
    box-shadow: none !important;
    height: auto !important;
    line-height: 1.3 !important;
    padding: 0 !important;
    font-size: 1.5em;
    font-weight: 700;
    margin: 0 0 20px;
    border: 0;
}
#coupon ul {
    list-style: none;
    margin: 0 0 20px;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
#coupon li {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: 10px;
    padding: 16px 18px;
    position: relative;
}
#coupon li::before {
    /* 좌측 primary accent */
    content: "";
    position: absolute;
    left: 0; top: 12px; bottom: 12px;
    width: 3px;
    background: var(--m-primary);
    border-radius: 2px;
}
#coupon .cou_top {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
    margin-bottom: 8px;
}
#coupon .cou_tit {
    font-size: 1.05em;
    font-weight: 700;
    color: var(--m-text);
    flex: 1;
    min-width: 0;
}
#coupon .cou_pri {
    font-size: 1.2em;
    font-weight: 800;
    color: var(--m-primary);
    white-space: nowrap;
}
#coupon li > div:not(.cou_top) {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    font-size: 0.9em;
    color: var(--m-text-soft);
}
#coupon .cou_target {
    color: var(--m-text-soft);
}
#coupon .cou_target i { display: none; }
#coupon .cou_date {
    color: var(--m-text-faint);
}
#coupon .cou_date i {
    margin-right: 4px;
    color: var(--m-text-faint);
}
#coupon .empty_li {
    text-align: center;
    padding: 40px 20px;
    color: var(--m-text-soft);
}
#coupon .empty_li::before { display: none; }
#coupon .btn_close {
    display: block;
    width: 100%;
    padding: 12px 16px;
    background: var(--m-surface-2);
    color: var(--m-text);
    border: 1px solid var(--m-border);
    border-radius: 8px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
}
#coupon .btn_close:hover {
    background: var(--m-primary);
    color: #fff;
    border-color: var(--m-primary);
}

@media (max-width: 480px) {
    #coupon { padding: 16px 14px; }
    #coupon li { padding: 14px 16px; }
    #coupon .cou_top { flex-direction: column; align-items: flex-start; gap: 4px; }
}
</style>

<!-- 쿠폰 내역 시작 { -->
<div id="coupon" class="new_win">
    <h1 id="win_title"><?php echo $g5['title'] ?></h1>
    <ul>
    <?php
    $cp_count = 0;
    for($i=0; $row=sql_fetch_array($result); $i++) {
        if(is_used_coupon($member['mb_id'], $row['cp_id']))
            continue;

        if($row['cp_method'] == 1) {
            $sql = " select ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$row['cp_target']}' ";
            $ca = sql_fetch($sql);
            $cp_target = $ca['ca_name'].'의 상품할인';
        } else if($row['cp_method'] == 2) {
            $cp_target = '결제금액 할인';
        } else if($row['cp_method'] == 3) {
            $cp_target = '배송비 할인';
        } else {
            $it = get_shop_item($row['cp_target'], true);
            $cp_target = $it['it_name'].' 상품할인';
        }

        if($row['cp_type'])
            $cp_price = $row['cp_price'].'%';
        else
            $cp_price = number_format($row['cp_price']).'원';

        $cp_count++;
    ?>
    <li>
        <div class="cou_top">
            <span class="cou_tit"><?php echo $row['cp_subject']; ?></span>
            <span class="cou_pri"><?php echo $cp_price; ?></span>
        </div>
        <div>
            <span class="cou_target"><?php echo $cp_target; ?></span>
            <span class="cou_date"><i class="fa fa-clock-o" aria-hidden="true"></i><?php echo substr($row['cp_start'], 2, 8); ?> ~ <?php echo substr($row['cp_end'], 2, 8); ?></span>
        </div>
    </li>
    <?php
    }

    if(!$cp_count)
        echo '<li class="empty_li">사용할 수 있는 쿠폰이 없습니다.</li>';
    ?>
    </ul>
    <button type="button" onclick="window.close();" class="btn_close">창닫기</button>
</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
