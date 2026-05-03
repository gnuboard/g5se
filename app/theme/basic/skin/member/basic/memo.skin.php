<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 쪽지 목록 시작 { -->
<div class="m-popup">
    <header class="m-popup-head">
        <h1 class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            쪽지함
        </h1>
        <p class="m-popup-sub">전체 <strong><?php echo $kind_title ?></strong>쪽지 <strong><?php echo $total_count ?></strong>통</p>
    </header>

    <nav class="m-memo-tabs">
        <a href="/memo?kind=recv" class="m-memo-tab<?php echo $kind == 'recv' ? ' is-active' : '' ?>">받은쪽지</a>
        <a href="/memo?kind=send" class="m-memo-tab<?php echo $kind == 'send' ? ' is-active' : '' ?>">보낸쪽지</a>
        <a href="/memo_form" class="m-memo-tab m-memo-tab-write">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            쪽지쓰기
        </a>
    </nav>

    <ul class="m-memo-list">
        <?php
        for ($i=0; $i<count($list); $i++) {
            $is_read = (substr($list[$i]['me_read_datetime'], 0, 1) != 0);
            $memo_preview = utf8_strcut(strip_tags($list[$i]['me_memo']), 60, '..');
        ?>
        <li class="m-memo-item<?php echo $is_read ? ' is-read' : '' ?>">
            <a href="<?php echo $list[$i]['view_href']; ?>" class="m-memo-item-link">
                <span class="m-memo-avatar"><?php echo get_member_profile_img($list[$i]['mb_id']); ?></span>
                <span class="m-memo-body">
                    <span class="m-memo-meta">
                        <span class="m-memo-name"><?php echo get_text($list[$i]['mb_nick']); ?></span>
                        <?php if (!$is_read) { ?><span class="m-memo-badge">NEW</span><?php } ?>
                        <span class="m-memo-time">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <?php echo $list[$i]['send_datetime']; ?>
                        </span>
                    </span>
                    <span class="m-memo-preview"><?php echo $memo_preview; ?></span>
                </span>
            </a>
            <a href="<?php echo $list[$i]['del_href']; ?>" onclick="del(this.href); return false;" class="m-memo-del" title="삭제">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
            </a>
        </li>
        <?php } ?>

        <?php if ($i == 0) { ?>
        <li class="m-memo-empty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <p>쪽지가 없습니다.</p>
        </li>
        <?php } ?>
    </ul>

    <div class="m-pagination"><?php echo $write_pages; ?></div>

    <p class="m-popup-hint">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        쪽지 보관일수는 최장 <strong><?php echo $config['cf_memo_del'] ?></strong>일 입니다.
    </p>

    <div class="m-popup-actions">
        <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:9px 20px;">창닫기</button>
    </div>
</div>

<style>
/* 목록 — popup 공통 CSS 는 _head.inc.php */
.m-memo-list { list-style: none; padding: 0; margin: 0; }
.m-memo-item {
    display: flex; align-items: stretch;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    margin-bottom: 6px;
    transition: border-color 0.15s, background 0.15s;
}
.m-memo-item:hover { border-color: var(--m-border-hover); }
.m-memo-item.is-read { opacity: 0.7; }

.m-memo-item-link {
    flex: 1; min-width: 0;
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px;
    color: var(--m-text); text-decoration: none;
}
.m-memo-avatar { display: inline-flex; align-items: center; flex-shrink: 0; }
.m-memo-avatar img {
    width: 32px; height: 32px; border-radius: 50%;
    border: 1px solid var(--m-border); object-fit: cover;
}
.m-memo-body { min-width: 0; flex: 1; display: flex; flex-direction: column; gap: 2px; }
.m-memo-meta {
    display: flex; align-items: center; gap: 6px;
    min-width: 0; flex-wrap: nowrap;
}
.m-memo-name {
    font-size: var(--m-text-base); font-weight: 600; color: var(--m-text);
    flex-shrink: 0;
    /* sideview wrapper 가 닉만 보이도록 자식 메뉴 제외 너비 자동 */
    max-width: 50%;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
/* sideview 가 .m-memo-name 안에 들어와도 닉만 한 줄로 */
.m-memo-name .sv_wrap, .m-memo-name .sv_member, .m-memo-name .sv_guest {
    display: inline !important;
    white-space: nowrap;
}
.m-memo-badge {
    flex-shrink: 0; line-height: 1;
    font-size: 9px; font-weight: 700; letter-spacing: 0.05em;
    padding: 3px 7px; border-radius: 999px;
    background: var(--m-primary); color: #fff;
}
.m-memo-time {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: var(--m-text-xs); color: var(--m-text-faint);
    margin-left: auto; flex-shrink: 0; white-space: nowrap;
}
.m-memo-preview {
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    min-width: 0;
}
.m-memo-del {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0 14px; flex-shrink: 0;
    color: var(--m-text-faint); transition: color 0.15s, background 0.15s;
    border-left: 1px solid var(--m-border);
}
.m-memo-del:hover { color: #ef4444; background: rgba(239,68,68,0.08); }

.m-memo-empty {
    padding: 50px 20px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.m-memo-empty svg { color: var(--m-text-faint); }
.m-memo-empty p { margin: 0; color: var(--m-text-muted); font-size: var(--m-text-sm); }
</style>
<!-- } 쪽지 목록 끝 -->
