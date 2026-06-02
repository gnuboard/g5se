<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 스크랩 시작 { -->
<div class="m-popup">
    <header class="m-popup-head">
        <h1 class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            스크랩하기
        </h1>
    </header>

    <form name="f_scrap_popin" action="<?php echo G5_URL ?>/scrap_popin_update" method="post" class="m-scrap-form">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">

        <section class="m-scrap-target">
            <span class="m-scrap-target-label">스크랩할 글</span>
            <span class="m-scrap-target-subject"><?php echo get_text(cut_str($write['wr_subject'], 255)) ?></span>
        </section>

        <div class="m-scrap-field">
            <label for="wr_content" class="m-label">댓글 작성 <span class="m-scrap-optional">(선택)</span></label>
            <textarea name="wr_content" id="wr_content" class="m-input m-scrap-textarea" placeholder="스크랩하면서 남길 감사·격려의 댓글을 작성하실 수 있습니다."></textarea>
        </div>

        <p class="m-popup-hint">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            스크랩하면서 감사·격려의 댓글을 함께 남길 수 있습니다.
        </p>

        <div class="m-popup-actions">
            <button type="submit" class="m-btn" style="width:auto; padding:10px 22px;">스크랩 확인</button>
            <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:9px 20px;">취소</button>
        </div>
    </form>
</div>

<style>
.m-scrap-form { display: flex; flex-direction: column; gap: 12px; }

.m-scrap-target {
    display: flex; flex-direction: column; gap: 4px;
    padding: 12px 14px;
    background: var(--m-surface-2);
    border-radius: var(--m-radius);
    border-left: 3px solid var(--m-primary);
}
.m-scrap-target-label { font-size: var(--m-text-xs); color: var(--m-text-muted); font-weight: 500; }
.m-scrap-target-subject { font-size: var(--m-text-md); color: var(--m-text); font-weight: 600; word-break: break-word; }

.m-scrap-field { display: flex; flex-direction: column; gap: 4px; }
.m-scrap-optional { font-size: var(--m-text-xs); color: var(--m-text-faint); font-weight: 400; }
.m-scrap-textarea {
    min-height: 120px; padding: 10px;
    font-family: inherit; resize: vertical;
    line-height: var(--m-leading-relaxed);
}
</style>
<!-- } 스크랩 끝 -->
