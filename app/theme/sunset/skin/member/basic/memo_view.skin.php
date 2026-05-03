<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');

$nick = get_sideview($mb['mb_id'], $mb['mb_nick'], $mb['mb_email'], $mb['mb_homepage']);
if ($kind == "recv") { $kind_str = "보낸"; $kind_date = "받은"; }
else                 { $kind_str = "받는"; $kind_date = "보낸"; }
?>

<!-- 쪽지보기 시작 { -->
<div class="m-popup">
    <header class="m-popup-head">
        <h1 class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            쪽지 내용
        </h1>
    </header>

    <nav class="m-memo-tabs">
        <a href="/memo?kind=recv" class="m-memo-tab<?php echo $kind == 'recv' ? ' is-active' : '' ?>">받은쪽지</a>
        <a href="/memo?kind=send" class="m-memo-tab<?php echo $kind == 'send' ? ' is-active' : '' ?>">보낸쪽지</a>
        <a href="/memo_form" class="m-memo-tab m-memo-tab-write">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            쪽지쓰기
        </a>
    </nav>

    <article class="m-memo-view">
        <header class="m-memo-view-head">
            <div class="m-memo-view-from">
                <span class="m-memo-avatar"><?php echo get_member_profile_img($mb['mb_id']); ?></span>
                <div class="m-memo-view-meta">
                    <div class="m-memo-view-nick"><?php echo $nick ?></div>
                    <div class="m-memo-view-time">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span class="sound_only"><?php echo $kind_date ?>시간 </span><?php echo $memo['me_send_datetime'] ?>
                    </div>
                </div>
            </div>
            <div class="m-memo-view-actions">
                <a href="<?php echo $list_link ?>" class="m-icon-btn" title="목록">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    <span>목록</span>
                </a>
                <a href="<?php echo $del_link; ?>" onclick="del(this.href); return false;" class="m-icon-btn m-icon-btn-danger" title="삭제">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                    <span>삭제</span>
                </a>
            </div>
        </header>

        <div class="m-memo-view-content"><?php echo conv_content($memo['me_memo'], 0) ?></div>

        <nav class="m-memo-view-nav">
            <?php if ($prev_link) { ?>
            <a href="<?php echo $prev_link ?>" class="m-memo-view-nav-prev">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                이전 쪽지
            </a>
            <?php } else { ?>
            <span class="m-memo-view-nav-prev is-disabled">이전 쪽지 없음</span>
            <?php } ?>
            <?php if ($next_link) { ?>
            <a href="<?php echo $next_link ?>" class="m-memo-view-nav-next">
                다음 쪽지
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <?php } else { ?>
            <span class="m-memo-view-nav-next is-disabled">다음 쪽지 없음</span>
            <?php } ?>
        </nav>
    </article>

    <div class="m-popup-actions">
        <?php if ($kind == 'recv') { ?>
        <a href="/memo_form?me_recv_mb_id=<?php echo $mb['mb_id'] ?>&amp;me_id=<?php echo $memo['me_id'] ?>" class="m-btn" style="width:auto; padding:10px 22px;">답장</a>
        <?php } ?>
        <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:9px 20px;">창닫기</button>
    </div>
</div>

<style>
.m-memo-view {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-lg);
    overflow: hidden;
}
.m-memo-view-head {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--m-border);
    background: var(--m-surface-2);
}
.m-memo-view-from { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
.m-memo-view-from .m-memo-avatar img {
    width: 40px; height: 40px; border-radius: 50%;
    border: 1px solid var(--m-border); object-fit: cover;
}
.m-memo-view-meta { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.m-memo-view-nick { font-size: var(--m-text-md); font-weight: 600; color: var(--m-text); }
.m-memo-view-time {
    display: flex; align-items: center; gap: 4px;
    font-size: var(--m-text-xs); color: var(--m-text-muted);
}
.m-memo-view-actions { display: flex; gap: 6px; flex-shrink: 0; }
.m-memo-view-actions .m-icon-btn { width: auto; padding: 6px 12px; gap: 4px; font-size: var(--m-text-sm); }

.m-memo-view-content {
    padding: 20px 18px;
    font-size: var(--m-text-md); line-height: var(--m-leading-relaxed);
    color: var(--m-text); word-break: break-word;
}

.m-memo-view-nav {
    display: grid; grid-template-columns: 1fr 1fr;
    border-top: 1px solid var(--m-border);
}
.m-memo-view-nav > * {
    display: flex; align-items: center; gap: 6px;
    padding: 12px 16px;
    text-decoration: none;
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    border-right: 1px solid var(--m-border);
    transition: background 0.15s, color 0.15s;
}
.m-memo-view-nav > *:last-child { border-right: 0; }
.m-memo-view-nav-next { justify-content: flex-end; }
a.m-memo-view-nav-prev:hover, a.m-memo-view-nav-next:hover {
    background: var(--m-surface-2); color: var(--m-text);
}
.m-memo-view-nav .is-disabled { color: var(--m-text-faint); cursor: default; }
</style>
<!-- } 쪽지보기 끝 -->
