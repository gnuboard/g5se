<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 포인트 내역 시작 { -->
<div class="m-popup">
    <header class="m-popup-head">
        <h1 class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            포인트 내역
        </h1>
    </header>

    <section class="m-point-summary">
        <span class="m-point-summary-label">보유 포인트</span>
        <span class="m-point-summary-value"><?php echo number_format($member['mb_point']); ?></span>
    </section>

    <ul class="m-point-list">
        <?php
        $sum_point1 = $sum_point2 = 0;
        $i = 0;
        foreach ((array) $list as $row) {
            $is_plus = ($row['po_point'] > 0);
            if ($is_plus) {
                $sum_point1 += $row['po_point'];
                $delta_text = '+' . number_format($row['po_point']);
            } else {
                $sum_point2 += $row['po_point'];
                $delta_text = number_format($row['po_point']);
            }
            $expired = ($row['po_expired'] == 1);
        ?>
        <li class="m-point-item<?php echo $is_plus ? ' is-plus' : ' is-minus' ?><?php echo $expired ? ' is-expired' : '' ?>">
            <div class="m-point-row">
                <span class="m-point-content"><?php echo $row['po_content']; ?></span>
                <span class="m-point-delta"><?php echo $delta_text; ?></span>
            </div>
            <div class="m-point-meta">
                <span class="m-point-time">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php echo $row['po_datetime']; ?>
                </span>
                <span class="m-point-expire">
                    <?php if ($expired) { ?>
                        <span class="m-point-expire-tag">만료</span> <?php echo substr(str_replace('-', '', $row['po_expire_date']), 2); ?>
                    <?php } else if ($row['po_expire_date'] != '9999-12-31') { ?>
                        만료 예정 <?php echo $row['po_expire_date']; ?>
                    <?php } ?>
                </span>
            </div>
        </li>
        <?php
            $i++;
        }
        if ($i == 0) {
        ?>
        <li class="m-point-empty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            <p>포인트 내역이 없습니다.</p>
        </li>
        <?php } ?>
    </ul>

    <?php if ($i > 0) { ?>
    <section class="m-point-totals">
        <span class="m-point-totals-label">소계</span>
        <span class="m-point-totals-plus">+<?php echo number_format($sum_point1); ?></span>
        <span class="m-point-totals-minus"><?php echo number_format($sum_point2); ?></span>
    </section>
    <?php } ?>

    <div class="m-pagination">
        <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '/point?'.$qstr.'&amp;page='); ?>
    </div>

    <div class="m-popup-actions">
        <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:9px 20px;">창닫기</button>
    </div>
</div>

<style>
.m-point-summary {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; margin-bottom: 12px;
    background: linear-gradient(135deg, var(--m-primary), var(--m-primary-hover));
    color: #fff;
    border-radius: var(--m-radius-lg);
    box-shadow: var(--m-shadow);
}
.m-point-summary-label { font-size: var(--m-text-sm); font-weight: 500; opacity: 0.9; }
.m-point-summary-value { font-size: var(--m-text-2xl); font-weight: 700; letter-spacing: -0.02em; font-feature-settings: "tnum"; }

.m-point-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
.m-point-item {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    padding: 10px 12px;
    transition: border-color 0.15s;
}
.m-point-item:hover { border-color: var(--m-border-hover); }
.m-point-item.is-expired { opacity: 0.6; }
.m-point-row {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 4px;
}
.m-point-content {
    flex: 1; min-width: 0;
    font-size: var(--m-text-md); color: var(--m-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-point-delta {
    flex-shrink: 0; font-weight: 700;
    font-size: var(--m-text-md); font-feature-settings: "tnum";
}
.m-point-item.is-plus .m-point-delta { color: var(--m-primary); }
.m-point-item.is-minus .m-point-delta { color: #ef4444; }

.m-point-meta {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    font-size: var(--m-text-xs); color: var(--m-text-muted);
}
.m-point-time { display: inline-flex; align-items: center; gap: 4px; color: var(--m-text-faint); }
.m-point-expire {
    display: inline-flex; align-items: center; gap: 6px;
    color: var(--m-text-faint);
}
.m-point-expire-tag {
    display: inline-flex; align-items: center;
    padding: 1px 6px; border-radius: 999px;
    background: rgba(239,68,68,0.12); color: #ef4444;
    font-size: 10px; font-weight: 700;
}

.m-point-empty {
    padding: 50px 20px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    background: var(--m-surface); border: 1px dashed var(--m-border);
    border-radius: var(--m-radius);
}
.m-point-empty svg { color: var(--m-text-faint); }
.m-point-empty p { margin: 0; color: var(--m-text-muted); font-size: var(--m-text-sm); }

.m-point-totals {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 14px; margin-top: 10px;
    background: var(--m-surface-2); border-radius: var(--m-radius);
    font-size: var(--m-text-sm); color: var(--m-text-soft);
}
.m-point-totals-label { font-weight: 600; color: var(--m-text); }
.m-point-totals-plus { font-weight: 700; color: var(--m-primary); font-feature-settings: "tnum"; }
.m-point-totals-minus { font-weight: 700; color: #ef4444; font-feature-settings: "tnum"; }
</style>
<!-- } 포인트 내역 끝 -->
