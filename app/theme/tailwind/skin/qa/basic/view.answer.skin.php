<?php
if (!defined('_GNUBOARD_')) exit;
?>

<!-- 답변 카드 시작 { -->
<section class="m-qa-answer">
    <header class="m-qa-answer-head">
        <span class="m-qa-answer-badge">답변</span>
        <h2 class="m-qa-answer-title"><?php echo get_text($answer['qa_subject']) ?></h2>
        <span class="m-qa-answer-time">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?php echo $answer['qa_datetime'] ?>
        </span>
        <?php if ($answer_update_href || $answer_delete_href) { ?>
        <div class="m-view-kebab m-qa-answer-kebab">
            <button type="button" class="m-icon-btn btn_more_opt is_view_btn" aria-label="답변 옵션">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
            </button>
            <ul class="m-view-kebab-menu more_opt is_view_btn" hidden>
                <?php if ($answer_update_href) { ?>
                <li><a href="<?php echo $answer_update_href ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span>답변 수정</span>
                </a></li>
                <?php } ?>
                <?php if ($answer_delete_href) { ?>
                <li><a href="<?php echo $answer_delete_href ?>" onclick="del(this.href); return false;" class="is-danger">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                    <span>답변 삭제</span>
                </a></li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
    </header>

    <div class="m-qa-answer-body">
        <?php if (isset($answer['img_count']) && $answer['img_count']) { ?>
        <div class="m-view-images">
            <?php for ($i = 0; $i < $answer['img_count']; $i++) {
                echo get_view_thumbnail($answer['img_file'][$i], $qaconfig['qa_image_width']);
            } ?>
        </div>
        <?php } ?>

        <div class="m-view-content">
            <?php echo get_view_thumbnail(conv_content($answer['qa_content'], $answer['qa_html']), $qaconfig['qa_image_width']) ?>
        </div>

        <?php if (isset($answer['download_count']) && $answer['download_count']) { ?>
        <section class="m-view-files">
            <h3 class="m-view-section-title">첨부파일</h3>
            <ul>
                <?php for ($i = 0; $i < $answer['download_count']; $i++) { ?>
                <li>
                    <a href="<?php echo $answer['download_href'][$i] ?>" class="m-view-file" download>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <strong><?php echo get_text($answer['download_source'][$i]) ?></strong>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </section>
        <?php } ?>
    </div>

    <footer class="m-qa-answer-foot">
        <a href="<?php echo $rewrite_href ?>" class="m-btn m-btn-ghost" style="width:auto; padding:8px 16px;">추가질문 작성</a>
    </footer>
</section>

<style>
.m-qa-answer {
    margin: 24px 28px;
    background: var(--m-primary-soft);
    border: 1px solid var(--m-primary);
    border-radius: var(--m-radius-lg);
    overflow: hidden;
}
.m-qa-answer-head {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 14px 18px;
    background: var(--m-primary);
    color: #fff;
}
.m-qa-answer-badge {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 999px;
    background: rgba(255,255,255,0.25); color: #fff;
    font-size: var(--m-text-xs); font-weight: 700; letter-spacing: 0.04em;
}
.m-qa-answer-title {
    margin: 0; flex: 1; min-width: 0;
    font-size: var(--m-text-md); font-weight: 600; color: #fff;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-qa-answer-time {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: var(--m-text-xs); color: rgba(255,255,255,0.85);
    flex-shrink: 0;
}
.m-qa-answer-kebab .m-icon-btn { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.3); color: #fff; }
.m-qa-answer-kebab .m-icon-btn:hover { background: rgba(255,255,255,0.25); }

.m-qa-answer-body {
    padding: 20px 24px;
    background: var(--m-surface);
}
.m-qa-answer-body .m-view-content { margin: 0; padding: 0; font-size: var(--m-text-md); line-height: var(--m-leading-relaxed); color: var(--m-text); }
.m-qa-answer-foot {
    display: flex; justify-content: flex-end;
    padding: 12px 18px;
    background: var(--m-surface);
    border-top: 1px solid var(--m-border);
}
</style>
<!-- } 답변 카드 끝 -->
