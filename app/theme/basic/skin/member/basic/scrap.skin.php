<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 스크랩 목록 시작 { -->
<div class="m-popup">
    <header class="m-popup-head">
        <h1 class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            <strong><?php echo get_text($member['mb_nick']) ?></strong>님의 스크랩
        </h1>
    </header>

    <ul class="m-scrap-list">
        <?php for ($i = 0; $i < count($list); $i++) { ?>
        <li class="m-scrap-item">
            <div class="m-scrap-row">
                <a href="<?php echo $list[$i]['opener_href_wr_id'] ?>" class="m-scrap-subject"
                   target="_blank"
                   onclick="opener.document.location.href='<?php echo $list[$i]['opener_href_wr_id'] ?>'; return false;">
                    <?php echo $list[$i]['subject'] ?>
                </a>
                <a href="<?php echo $list[$i]['del_href']; ?>" onclick="del(this.href); return false;" class="m-scrap-del" title="삭제">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                </a>
            </div>
            <div class="m-scrap-meta">
                <a href="<?php echo $list[$i]['opener_href'] ?>" class="m-scrap-board"
                   target="_blank"
                   onclick="opener.document.location.href='<?php echo $list[$i]['opener_href'] ?>'; return false;"><?php echo $list[$i]['bo_subject'] ?></a>
                <span class="m-scrap-time">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php echo $list[$i]['ms_datetime'] ?>
                </span>
            </div>
        </li>
        <?php } ?>

        <?php if ($i == 0) { ?>
        <li class="m-scrap-empty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            <p>스크랩한 게시글이 없습니다.</p>
        </li>
        <?php } ?>
    </ul>

    <div class="m-pagination">
        <?php echo get_paging($config['cf_write_pages'], $page, $total_page, '/scrap?'.$qstr.'&amp;page='); ?>
    </div>

    <div class="m-popup-actions">
        <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:9px 20px;">창닫기</button>
    </div>
</div>

<style>
.m-scrap-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
.m-scrap-item {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    padding: 10px 12px;
    transition: border-color 0.15s;
}
.m-scrap-item:hover { border-color: var(--m-border-hover); }
.m-scrap-row {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 4px;
}
.m-scrap-subject {
    flex: 1; min-width: 0;
    font-size: var(--m-text-md); font-weight: 500;
    color: var(--m-text); text-decoration: none;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-scrap-subject:hover { color: var(--m-primary); }
.m-scrap-del {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; flex-shrink: 0;
    color: var(--m-text-faint); border-radius: var(--m-radius-sm);
    transition: color 0.15s, background 0.15s;
}
.m-scrap-del:hover { color: #ef4444; background: rgba(239,68,68,0.1); }
.m-scrap-meta {
    display: flex; align-items: center; gap: 8px;
    font-size: var(--m-text-xs); color: var(--m-text-muted);
}
.m-scrap-board {
    display: inline-flex; align-items: center;
    padding: 2px 8px; border-radius: 999px;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    color: var(--m-text-soft); text-decoration: none;
    font-size: var(--m-text-xs);
}
.m-scrap-board:hover { color: var(--m-primary); border-color: var(--m-primary); }
.m-scrap-time {
    display: inline-flex; align-items: center; gap: 4px;
    color: var(--m-text-faint); margin-left: auto;
}

.m-scrap-empty {
    padding: 50px 20px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    background: var(--m-surface); border: 1px dashed var(--m-border);
    border-radius: var(--m-radius);
}
.m-scrap-empty svg { color: var(--m-text-faint); }
.m-scrap-empty p { margin: 0; color: var(--m-text-muted); font-size: var(--m-text-sm); }
</style>
<!-- } 스크랩 목록 끝 -->
