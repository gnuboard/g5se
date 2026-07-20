<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
if (!defined("_ORDERINQUIRY_")) exit;

// g5se: 주문내역 목록 — modern card list (legacy tbl_head03 폐기)
$_status_map = [
    '주문' => ['label' => '입금확인중', 'tone' => 'pending'],
    '입금' => ['label' => '입금완료',   'tone' => 'paid'],
    '준비' => ['label' => '상품준비중', 'tone' => 'preparing'],
    '배송' => ['label' => '상품배송',   'tone' => 'shipping'],
    '완료' => ['label' => '배송완료',   'tone' => 'done'],
];
?>

<style>
.m-oq-list { display: flex; flex-direction: column; gap: 8px; margin: 0 0 20px; }
.m-oq-card {
    display: grid;
    grid-template-columns: 180px 1fr 130px 110px 90px;
    gap: 14px;
    align-items: center;
    padding: 12px 16px;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    transition: border-color 0.15s;
    font-size: var(--m-text-sm);
}
.m-oq-card:hover { border-color: var(--m-primary); }
.m-oq-head { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.m-oq-no {
    font-weight: 700; color: var(--m-text);
    text-decoration: none;
    font-feature-settings: "tnum";
    letter-spacing: -0.01em;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-oq-no:hover { color: var(--m-primary); }
.m-oq-time {
    font-size: var(--m-text-xs);
    color: var(--m-text-faint);
}
.m-oq-status {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 4px 10px;
    border: 1px solid transparent;
    border-radius: 999px;
    font-size: var(--m-text-xs); font-weight: 700;
    line-height: 1;
    justify-self: end;
}
.m-oq-status.is-pending   { border-color:#64748b; background:rgba(100,116,139,.14); color:#475569; }
.m-oq-status.is-paid      { border-color:#0284c7; background:rgba(14,165,233,.14); color:#0369a1; }
.m-oq-status.is-preparing { border-color:#ef4444; background:rgba(239,68,68,.16); color:#dc2626; }
.m-oq-status.is-shipping  { border-color:#d97706; background:rgba(245,158,11,.15); color:#b45309; }
.m-oq-status.is-done      { border-color:#059669; background:rgba(16,185,129,.15); color:#047857; }
.m-oq-status.is-cancel    { border-color:#ef4444; background:rgba(239,68,68,.14); color:#ef4444; }
[data-theme="dark"] .m-oq-status.is-pending   { color:#cbd5e1; }
[data-theme="dark"] .m-oq-status.is-paid      { color:#7dd3fc; }
[data-theme="dark"] .m-oq-status.is-preparing { border-color:#fb7185; background:rgba(239,68,68,.2); color:#fda4af; }
[data-theme="dark"] .m-oq-status.is-shipping  { color:#fcd34d; }
[data-theme="dark"] .m-oq-status.is-done      { color:#6ee7b7; }

.m-oq-product {
    display: flex; align-items: center; gap: 6px;
    color: var(--m-text);
    text-decoration: none;
    min-width: 0;
}
.m-oq-product:hover .m-oq-product-name { color: var(--m-primary); }
.m-oq-product-name {
    font-weight: 600;
    color: var(--m-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    flex: 1; min-width: 0;
    transition: color 0.15s;
}
.m-oq-product-more {
    flex-shrink: 0;
    font-size: var(--m-text-xs);
    color: var(--m-text-soft);
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    padding: 1px 7px;
    border-radius: 999px;
    font-weight: 600;
    line-height: 1.4;
}
.m-oq-product-empty { color: var(--m-text-faint); font-size: var(--m-text-sm); }

.m-oq-amount, .m-oq-misu {
    text-align: right;
    font-weight: 700;
    color: var(--m-text);
    font-feature-settings: "tnum";
    white-space: nowrap;
}
.m-oq-misu { color: #ef4444; font-weight: 600; }
.m-oq-misu.is-zero { color: var(--m-text-faint); font-weight: 500; }
.m-oq-misu .label,
.m-oq-amount .label {
    display: none; /* 데스크탑: 라벨 숨김 (헤더 행에서 표시) */
}

/* 헤더 행 (테이블 헤더 역할) */
.m-oq-thead {
    display: grid;
    grid-template-columns: 180px 1fr 130px 110px 90px;
    gap: 14px;
    padding: 0 16px 8px;
    font-size: var(--m-text-xs);
    color: var(--m-text-faint);
    font-weight: 600;
    text-transform: none;
    border-bottom: 1px solid var(--m-border);
    margin-bottom: 8px;
}
.m-oq-thead > span:nth-child(3),
.m-oq-thead > span:nth-child(4) { text-align: right; }
.m-oq-thead > span:nth-child(5) { text-align: center; }

@media (max-width: 768px) {
    /* 모바일: 헤더 숨기고 카드 stacked layout */
    .m-oq-thead { display: none; }
    .m-oq-card {
        grid-template-columns: 1fr auto;
        gap: 4px 12px;
        padding: 12px 14px;
    }
    .m-oq-head { grid-column: 1; grid-row: 1; }
    .m-oq-status { grid-column: 2; grid-row: 1; }
    .m-oq-product { grid-column: 1 / -1; grid-row: 2; margin-top: 4px; }
    .m-oq-amount, .m-oq-misu {
        grid-column: 1 / -1; grid-row: auto;
        text-align: left; font-weight: 600;
        font-size: var(--m-text-xs);
        color: var(--m-text-soft);
        display: flex; gap: 6px;
    }
    .m-oq-amount .label, .m-oq-misu .label {
        display: inline; color: var(--m-text-faint); font-weight: 500;
    }
    .m-oq-amount strong { color: var(--m-text); font-weight: 700; font-size: var(--m-text-sm); }
    .m-oq-misu strong { color: #ef4444; font-weight: 700; font-size: var(--m-text-sm); }
}

.m-oq-empty {
    padding: 60px 20px;
    text-align: center;
    background: var(--m-surface); border: 1px dashed var(--m-border);
    border-radius: var(--m-radius);
    display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.m-oq-empty svg { color: var(--m-text-faint); }
.m-oq-empty p { margin: 0; color: var(--m-text-muted); font-size: var(--m-text-sm); }

.m-oq-count {
    margin: 0 0 12px;
    color: var(--m-text-soft);
    font-size: var(--m-text-sm);
}
.m-oq-count strong { color: var(--m-text); font-weight: 700; }

@media (max-width: 600px) {
    .m-oq-card { padding: 14px 16px; }
    .m-oq-head { gap: 6px; }
}
</style>

<?php
// $limit 은 "$from_record, $rows" 형태로 호출자에서 (int) cast 후 보간된 string — PDO 바인딩 불가 (LIMIT 은 prepared 못 받음)
// orderinquiry.php 가 만든 $sql_common (검색 WHERE 포함) + $inquiry_params 재사용
$result = sql_pdo_query(
    " select * $sql_common order by od_id desc $limit ",
    $inquiry_params
);
$_orders = [];
while ($row = sql_pdo_fetch_array($result)) $_orders[] = $row;
$_total_visible = count($_orders);

// 페이지 합계 — 현재 페이지에 보이는 주문들의 주문금액/미입금액 합
$_page_sum_total = 0;
$_page_sum_misu  = 0;
foreach ($_orders as $r) {
    $_page_sum_total += (int)$r['od_cart_price'] + (int)$r['od_send_cost'] + (int)$r['od_send_cost2'];
    $_page_sum_misu  += (int)$r['od_misu'];
}

// 각 주문의 첫 상품명 + 추가 품목 수 (외 N건) — 한 번의 SQL 로 일괄 조회
$_order_items = [];
if ($_total_visible > 0) {
    $_od_ids = array_column($_orders, 'od_id');
    $_in_ph  = implode(',', array_fill(0, count($_od_ids), '?'));
    // 주문별 distinct it_id 카운트
    $_r = sql_pdo_query(" select od_id, count(distinct it_id) as ic
                            from {$g5['g5_shop_cart_table']}
                           where od_id IN ($_in_ph)
                           group by od_id ", $_od_ids);
    while ($_row = sql_pdo_fetch_array($_r)) {
        $_order_items[$_row['od_id']]['count'] = (int)$_row['ic'];
    }
    // 주문별 가장 빠른 ct_id 의 it_name (대표 상품)
    $_r = sql_pdo_query(" select c.od_id, c.it_name
                            from {$g5['g5_shop_cart_table']} c
                            inner join (
                                select od_id, min(ct_id) as min_ct
                                  from {$g5['g5_shop_cart_table']}
                                 where od_id IN ($_in_ph)
                                 group by od_id
                            ) m on m.od_id = c.od_id and m.min_ct = c.ct_id ", $_od_ids);
    while ($_row = sql_pdo_fetch_array($_r)) {
        $_order_items[$_row['od_id']]['name'] = $_row['it_name'];
    }
}
?>

<div class="m-oq-page-head">
    <h1 class="m-oq-page-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
        주문내역
    </h1>
    <?php if ($_total_visible > 0) { ?>
    <span class="m-oq-page-count"><?php echo number_format($_total_visible); ?> 건</span>
    <?php } ?>
</div>

<form method="get" class="m-oq-search">
    <label class="m-oq-search-field">
        <span>주문서번호</span>
        <input type="text" name="s_od_id" value="<?php echo htmlspecialchars($s_od_id ?? ''); ?>" placeholder="번호 일부 입력" inputmode="numeric" maxlength="20">
    </label>
    <label class="m-oq-search-field">
        <span>주문일자</span>
        <input type="date" name="s_fr" value="<?php echo htmlspecialchars($s_fr ?? ''); ?>">
    </label>
    <label class="m-oq-search-field">
        <span>~</span>
        <input type="date" name="s_to" value="<?php echo htmlspecialchars($s_to ?? ''); ?>">
    </label>
    <button type="submit" class="m-oq-search-btn">검색</button>
    <?php if (!empty($has_search)) { ?>
    <a href="<?php echo G5_SHOP_URL ?>/orderinquiry" class="m-oq-search-clear">초기화</a>
    <?php } ?>
</form>

<style>
.m-oq-search {
    display: flex; flex-wrap: wrap; align-items: end; gap: 8px;
    margin: 0 0 16px;
    padding: 12px 14px;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
}
.m-oq-search-field {
    display: flex; flex-direction: column; gap: 4px;
    font-size: var(--m-text-xs);
    color: var(--m-text-soft);
}
.m-oq-search-field input {
    padding: 6px 10px;
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-sm);
    background: var(--m-bg);
    color: var(--m-text);
    font-size: var(--m-text-sm);
}
.m-oq-search-field input[type="date"] { min-width: 145px; }
.m-oq-search-field input[type="text"] { min-width: 180px; }
.m-oq-search-btn, a.m-oq-search-clear, a.m-oq-search-clear:link, a.m-oq-search-clear:visited {
    display: inline-flex; align-items: center;
    padding: 7px 16px;
    border-radius: var(--m-radius-sm);
    font-size: var(--m-text-sm); font-weight: 600;
    text-decoration: none; cursor: pointer;
    border: 1px solid var(--m-primary);
}
.m-oq-search-btn {
    background: var(--m-primary) !important;
    color: #fff !important;
}
.m-oq-search-btn:hover { filter: brightness(0.96); }
a.m-oq-search-clear {
    background: var(--m-surface-2) !important;
    color: var(--m-text) !important;
    border-color: var(--m-border);
}
a.m-oq-search-clear:hover { border-color: var(--m-primary); color: var(--m-primary) !important; }
@media (max-width: 600px) {
    .m-oq-search-field input[type="text"], .m-oq-search-field input[type="date"] { min-width: 0; width: 100%; }
    .m-oq-search-field { flex: 1 1 calc(50% - 8px); }
    .m-oq-search-btn, a.m-oq-search-clear { flex: 1; justify-content: center; }
}

.m-oq-page-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--m-border);
}
.m-oq-page-title {
    display: flex; align-items: center; gap: 8px;
    margin: 0;
    font-size: 1.3em; font-weight: 700; color: var(--m-text);
}
.m-oq-page-title svg { color: var(--m-primary); }
.m-oq-page-count {
    display: inline-flex; align-items: center;
    padding: 4px 10px;
    background: var(--m-primary-soft);
    color: var(--m-primary);
    border-radius: 999px;
    font-size: var(--m-text-sm); font-weight: 600;
}

/* 페이지 합계 */
.m-oq-summary {
    display: grid;
    grid-template-columns: 180px 1fr 130px 110px 90px;
    gap: 14px;
    align-items: center;
    margin-top: 8px;
    padding: 12px 16px;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    font-size: var(--m-text-sm);
    font-weight: 700;
    color: var(--m-text);
}
.m-oq-summary-label { display: flex; align-items: center; gap: 6px; color: var(--m-text-soft); }
.m-oq-summary-spacer { /* 상품 column placeholder */ }
.m-oq-summary-amount { text-align: right; font-feature-settings: "tnum"; }
.m-oq-summary-misu   { text-align: right; font-feature-settings: "tnum"; color: #ef4444; }
.m-oq-summary-misu.is-zero { color: var(--m-text-faint); font-weight: 500; }
.m-oq-summary-end { /* 상태 column placeholder */ }
@media (max-width: 768px) {
    .m-oq-summary { grid-template-columns: 1fr auto; }
    .m-oq-summary-spacer, .m-oq-summary-end { display: none; }
    .m-oq-summary-amount, .m-oq-summary-misu {
        grid-column: 1 / -1;
        text-align: left;
        display: flex; gap: 8px;
        font-size: var(--m-text-xs);
        color: var(--m-text-soft);
        font-weight: 600;
    }
    .m-oq-summary-amount strong, .m-oq-summary-misu strong { color: var(--m-text); font-size: var(--m-text-sm); font-weight: 700; }
    .m-oq-summary-misu strong { color: #ef4444; }
</style>

<?php if ($_total_visible === 0) { ?>
<div class="m-oq-empty">
    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
    <p><?php echo !empty($has_search) ? '검색 조건에 맞는 주문 내역이 없습니다.' : '아직 주문 내역이 없습니다.'; ?></p>
</div>
<?php } else { ?>
<div class="m-oq-thead" aria-hidden="true">
    <span>주문서번호 / 일시</span>
    <span>상품</span>
    <span>주문금액</span>
    <span>미입금</span>
    <span>상태</span>
</div>
<div class="m-oq-list">
    <?php foreach ($_orders as $row) {
        $uid = function_exists('get_shop_uid') ? get_shop_uid('order', $row['od_id'], $row['od_time'], $row['od_ip']) : md5($row['od_id'].$row['od_time'].$row['od_ip']);
        $st = $_status_map[$row['od_status']] ?? ['label' => '주문취소', 'tone' => 'cancel'];
        $od_total = (int)$row['od_cart_price'] + (int)$row['od_send_cost'] + (int)$row['od_send_cost2'];
        $od_misu  = (int)$row['od_misu'];
        $od_view_url = G5_SHOP_URL.'/orderinquiryview?od_id='.$row['od_id'].'&amp;uid='.$uid;

        $_oi = $_order_items[$row['od_id']] ?? [];
        $_first_name = isset($_oi['name']) ? get_text($_oi['name']) : '';
        $_item_more  = max(0, (int)($_oi['count'] ?? 0) - 1);
    ?>
    <article class="m-oq-card">
        <div class="m-oq-head">
            <a class="m-oq-no" href="<?php echo $od_view_url; ?>"><?php echo $row['od_id']; ?></a>
            <span class="m-oq-time"><?php echo substr($row['od_time'], 2, 14); ?> (<?php echo get_yoil($row['od_time']); ?>)</span>
        </div>

        <?php if ($_first_name) { ?>
        <a class="m-oq-product" href="<?php echo $od_view_url; ?>">
            <span class="m-oq-product-name"><?php echo $_first_name; ?></span>
            <?php if ($_item_more > 0) { ?>
            <span class="m-oq-product-more">외 <?php echo $_item_more; ?> 건</span>
            <?php } ?>
        </a>
        <?php } else { ?>
        <span class="m-oq-product-empty">—</span>
        <?php } ?>

        <span class="m-oq-amount"><span class="label">주문금액 </span><strong><?php echo display_price($od_total); ?></strong></span>
        <span class="m-oq-misu<?php echo $od_misu > 0 ? '' : ' is-zero'; ?>"><span class="label">미입금 </span><strong><?php echo display_price($od_misu); ?></strong></span>

        <span class="m-oq-status is-<?php echo $st['tone']; ?>"><?php echo $st['label']; ?></span>
    </article>
    <?php } ?>
</div>

<!-- 페이지 합계 -->
<div class="m-oq-summary">
    <div class="m-oq-summary-label">이 페이지 합계 (<?php echo $_total_visible; ?>건)</div>
    <div class="m-oq-summary-spacer"></div>
    <div class="m-oq-summary-amount"><span class="label">주문금액 </span><strong><?php echo display_price($_page_sum_total); ?></strong></div>
    <div class="m-oq-summary-misu<?php echo $_page_sum_misu > 0 ? '' : ' is-zero'; ?>"><span class="label">미입금 </span><strong><?php echo display_price($_page_sum_misu); ?></strong></div>
    <div class="m-oq-summary-end"></div>
</div>
<?php } ?>
