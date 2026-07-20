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
// g5se: modern 토큰 + .m-popup 컴포넌트 로드
if(defined('G5_THEME_PATH') && is_file(G5_THEME_PATH.'/modern/_head.inc.php')) {
    require_once(G5_THEME_PATH.'/modern/_head.inc.php');
}

$result = sql_pdo_query(
    " select cp_id, cp_subject, cp_method, cp_target, cp_start, cp_end, cp_type, cp_price
        from {$g5['g5_shop_coupon_table']}
       where mb_id IN (:mb_id, '전체회원')
         and cp_start <= :today
         and cp_end >= :today
       order by cp_no ",
    [':mb_id' => $member['mb_id'], ':today' => G5_TIME_YMD]
);

// 사용 가능 쿠폰만 미리 모음 (사용된 것 제외)
$_coupons = [];
while ($row = sql_pdo_fetch_array($result)) {
    if (is_used_coupon($member['mb_id'], $row['cp_id'])) continue;

    if ($row['cp_method'] == 1) {
        $ca = sql_pdo_fetch(
            " select ca_name from {$g5['g5_shop_category_table']} where ca_id = :ca_id ",
            [':ca_id' => $row['cp_target']]
        );
        $row['_target_label'] = ($ca['ca_name'] ?: '카테고리').' 상품할인';
    } else if ($row['cp_method'] == 2) {
        $row['_target_label'] = '결제금액 할인';
    } else if ($row['cp_method'] == 3) {
        $row['_target_label'] = '배송비 할인';
    } else {
        $it = get_shop_item($row['cp_target'], true);
        $row['_target_label'] = ($it['it_name'] ?: '상품').' 상품할인';
    }
    if ($row['cp_type']) {
        $row['_price_label'] = $row['cp_price'].'%';
    } else {
        $row['_price_label'] = number_format($row['cp_price']).'원';
    }
    $_coupons[] = $row;
}
$_cp_total = count($_coupons);
?>

<!-- 쿠폰 내역 시작 { -->
<div class="m-popup">
    <header class="m-popup-head">
        <h1 class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg>
            쿠폰 내역
        </h1>
    </header>

    <section class="m-coupon-summary">
        <span class="m-coupon-summary-label">사용 가능 쿠폰</span>
        <span class="m-coupon-summary-value"><?php echo number_format($_cp_total); ?> <em>장</em></span>
    </section>

    <ul class="m-coupon-list">
        <?php foreach ($_coupons as $row) { ?>
        <li class="m-coupon-item">
            <div class="m-coupon-row">
                <span class="m-coupon-subject"><?php echo $row['cp_subject']; ?></span>
                <span class="m-coupon-price"><?php echo $row['_price_label']; ?></span>
            </div>
            <div class="m-coupon-meta">
                <span class="m-coupon-target"><?php echo $row['_target_label']; ?></span>
                <span class="m-coupon-date">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php echo substr($row['cp_start'], 2, 8); ?> ~ <?php echo substr($row['cp_end'], 2, 8); ?>
                </span>
            </div>
        </li>
        <?php } ?>

        <?php if ($_cp_total === 0) { ?>
        <li class="m-coupon-empty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg>
            <p>사용할 수 있는 쿠폰이 없습니다.</p>
        </li>
        <?php } ?>
    </ul>

    <div class="m-popup-actions">
        <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:9px 20px;">창닫기</button>
    </div>
</div>

<style>
.m-coupon-summary {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; margin-bottom: 12px;
    background: linear-gradient(135deg, var(--m-primary), var(--m-primary-hover));
    color: #fff;
    border-radius: var(--m-radius-lg);
    box-shadow: var(--m-shadow);
}
.m-coupon-summary-label { font-size: var(--m-text-sm); font-weight: 500; opacity: 0.9; }
.m-coupon-summary-value { font-size: var(--m-text-2xl); font-weight: 700; letter-spacing: -0.02em; font-feature-settings: "tnum"; }
.m-coupon-summary-value em { font-style: normal; font-size: 0.55em; font-weight: 600; opacity: 0.85; margin-left: 2px; }

.m-coupon-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
.m-coupon-item {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    padding: 10px 12px;
    transition: border-color 0.15s;
}
.m-coupon-item:hover { border-color: var(--m-border-hover); }
.m-coupon-row {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 4px;
}
.m-coupon-subject {
    flex: 1; min-width: 0;
    font-size: var(--m-text-md); font-weight: 600; color: var(--m-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-coupon-price {
    flex-shrink: 0; font-weight: 700;
    font-size: var(--m-text-md); color: var(--m-primary);
    font-feature-settings: "tnum";
}

.m-coupon-meta {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    font-size: var(--m-text-xs); color: var(--m-text-muted);
}
.m-coupon-target { color: var(--m-text-faint); }
.m-coupon-date { display: inline-flex; align-items: center; gap: 4px; color: var(--m-text-faint); }

.m-coupon-empty {
    padding: 50px 20px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    background: var(--m-surface); border: 1px dashed var(--m-border);
    border-radius: var(--m-radius);
}
.m-coupon-empty svg { color: var(--m-text-faint); }
.m-coupon-empty p { margin: 0; color: var(--m-text-muted); font-size: var(--m-text-sm); }
</style>
<!-- } 쿠폰 내역 끝 -->

<?php
include_once(G5_PATH.'/tail.sub.php');
