<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<script src="<?php echo G5_JS_URL ?>/viewimageresize.js"></script>

<!-- 1:1 문의 보기 시작 { -->
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">
            <article class="m-card m-view">
                <header class="m-view-head">
                    <?php if ($view['category']) { ?>
                    <span class="m-cate-tag" style="margin-bottom: 8px;"><?php echo get_text($view['category']) ?></span>
                    <?php } ?>
                    <h1 class="m-view-title"><?php echo get_text($view['subject']) ?></h1>

                    <div class="m-view-meta">
                        <span class="m-view-name"><strong><?php echo get_text($view['name']) ?></strong></span>
                        <span class="m-view-meta-sep">·</span>
                        <span class="m-view-meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <?php echo $view['datetime'] ?>
                        </span>
                        <?php if ($view['email']) { ?>
                        <span class="m-view-meta-sep">·</span>
                        <span class="m-view-meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <?php echo get_text($view['email']) ?>
                        </span>
                        <?php } ?>
                        <?php if ($view['hp']) { ?>
                        <span class="m-view-meta-sep">·</span>
                        <span class="m-view-meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.33 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <?php echo get_text($view['hp']) ?>
                        </span>
                        <?php } ?>

                        <?php if (!$view['qa_type']) { ?>
                        <span class="m-view-meta-sep">·</span>
                        <?php if ($view['qa_status'] && isset($answer['qa_id']) && $answer['qa_id']) { ?>
                        <span class="m-qa-status m-qa-status-done">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            답변완료
                        </span>
                        <?php } else { ?>
                        <span class="m-qa-status m-qa-status-rdy">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            답변대기
                        </span>
                        <?php } ?>
                        <?php } ?>
                    </div>

                    <div id="bo_v_top" class="m-view-actions">
                        <a href="<?php echo $list_href ?>" class="m-icon-btn" title="목록">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                            <span>목록</span>
                        </a>
                        <?php if ($write_href) { ?>
                        <a href="/qa/write" class="m-icon-btn" title="문의등록">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            <span>문의등록</span>
                        </a>
                        <?php } ?>
                        <?php if ($update_href || $delete_href) { ?>
                        <div class="m-view-kebab">
                            <button type="button" class="m-icon-btn btn_more_opt is_view_btn" aria-label="더보기">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                            </button>
                            <ul class="m-view-kebab-menu more_opt is_view_btn" hidden>
                                <?php if ($update_href) { ?>
                                <li><a href="<?php echo $update_href ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    <span>수정</span>
                                </a></li>
                                <?php } ?>
                                <?php if ($delete_href) { ?>
                                <li><a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;" class="is-danger">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                                    <span>삭제</span>
                                </a></li>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php } ?>
                    </div>
                </header>

                <section class="m-view-body">
                    <?php if ($view['img_count']) { ?>
                    <div id="bo_v_img" class="m-view-images">
                        <?php for ($i = 0; $i < $view['img_count']; $i++) {
                            echo get_view_thumbnail($view['img_file'][$i], $qaconfig['qa_image_width']);
                        } ?>
                    </div>
                    <?php } ?>

                    <div id="bo_v_atc">
                        <div id="bo_v_con" class="m-view-content"><?php echo get_view_thumbnail($view['content'], $qaconfig['qa_image_width']) ?></div>
                    </div>

                    <?php if ($view['qa_type']) { ?>
                    <div class="m-qa-addq">
                        <a href="<?php echo $rewrite_href ?>" class="m-btn">추가질문 작성</a>
                    </div>
                    <?php } ?>

                    <?php if ($view['download_count']) { ?>
                    <section class="m-view-files">
                        <h2 class="m-view-section-title">첨부파일</h2>
                        <ul>
                            <?php for ($i = 0; $i < $view['download_count']; $i++) { ?>
                            <li>
                                <a href="<?php echo $view['download_href'][$i] ?>" class="m-view-file" download>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    <strong><?php echo get_text($view['download_source'][$i]) ?></strong>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </section>
                    <?php } ?>
                </section>

                <?php
                // 답변 영역 — 질문(qa_type=0)일 때만 표시
                if (!$view['qa_type']) {
                    if ($view['qa_status'] && isset($answer['qa_id']) && $answer['qa_id']) {
                        include_once($qa_skin_path.'/view.answer.skin.php');
                    } else {
                        include_once($qa_skin_path.'/view.answerform.skin.php');
                    }
                }
                ?>

                <?php if ($prev_href || $next_href) { ?>
                <nav class="m-view-nav">
                    <?php if ($prev_href) { ?>
                    <a href="<?php echo $prev_href ?>" class="m-view-nav-item m-view-nav-prev">
                        <span class="m-view-nav-label">▲ 이전글</span>
                        <span class="m-view-nav-empty">이전 문의로 이동</span>
                    </a>
                    <?php } else { ?>
                    <div class="m-view-nav-item m-view-nav-prev is-empty">
                        <span class="m-view-nav-label">▲ 이전글</span>
                        <span class="m-view-nav-empty">이전 문의 없음</span>
                    </div>
                    <?php } ?>
                    <?php if ($next_href) { ?>
                    <a href="<?php echo $next_href ?>" class="m-view-nav-item m-view-nav-next">
                        <span class="m-view-nav-label">▼ 다음글</span>
                        <span class="m-view-nav-empty">다음 문의로 이동</span>
                    </a>
                    <?php } else { ?>
                    <div class="m-view-nav-item m-view-nav-next is-empty">
                        <span class="m-view-nav-label">▼ 다음글</span>
                        <span class="m-view-nav-empty">다음 문의 없음</span>
                    </div>
                    <?php } ?>
                </nav>
                <?php } ?>
            </article>

            <?php if ($view['rel_count']) { ?>
            <section class="m-card m-qa-rel">
                <h2 class="m-qa-rel-title">연관 질문</h2>
                <ul>
                    <?php for ($i = 0; $i < $view['rel_count']; $i++) { ?>
                    <li class="m-qa-rel-item">
                        <a href="<?php echo $rel_list[$i]['view_href'] ?>" class="m-qa-rel-link">
                            <?php if ($rel_list[$i]['category']) { ?>
                            <span class="m-qa-cate-tag"><?php echo get_text($rel_list[$i]['category']) ?></span>
                            <?php } ?>
                            <span class="m-qa-rel-subject"><?php echo $rel_list[$i]['subject'] ?></span>
                        </a>
                        <span class="m-qa-rel-meta">
                            <span class="m-qa-rel-date"><?php echo $rel_list[$i]['date'] ?></span>
                            <?php if ($rel_list[$i]['qa_status']) { ?>
                            <span class="m-qa-status m-qa-status-done">답변완료</span>
                            <?php } else { ?>
                            <span class="m-qa-status m-qa-status-rdy">답변대기</span>
                            <?php } ?>
                        </span>
                    </li>
                    <?php } ?>
                </ul>
            </section>
            <?php } ?>
        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>

    <?php require G5_THEME_PATH.'/modern/_footer.inc.php'; ?>
</div>

<style>
.m-qa-addq { margin: 20px 0; text-align: center; }
.m-qa-addq .m-btn { width: auto; padding: 10px 22px; }

.m-qa-rel { padding: 20px 24px; margin-top: 18px; }
.m-qa-rel-title {
    margin: 0 0 12px;
    font-size: var(--m-text-base); font-weight: 600;
    color: var(--m-text-soft); letter-spacing: 0.02em;
}
.m-qa-rel ul { list-style: none; margin: 0; padding: 0; }
.m-qa-rel-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 4px;
    border-bottom: 1px solid var(--m-border);
}
.m-qa-rel-item:last-child { border-bottom: 0; }
.m-qa-rel-link {
    flex: 1; min-width: 0;
    display: inline-flex; align-items: center; gap: 6px;
    color: var(--m-text); text-decoration: none;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-qa-rel-link:hover .m-qa-rel-subject { color: var(--m-primary); }
.m-qa-rel-subject {
    font-size: var(--m-text-md);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-qa-rel-meta { display: inline-flex; align-items: center; gap: 8px; flex-shrink: 0; font-size: var(--m-text-xs); }
.m-qa-rel-date { color: var(--m-text-faint); }
</style>

<script>
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.m-view-kebab .btn_more_opt');
    if (btn) {
        e.preventDefault();
        var menu = btn.parentNode.querySelector('.m-view-kebab-menu');
        var isOpen = !menu.hasAttribute('hidden');
        document.querySelectorAll('.m-view-kebab-menu').forEach(function(m){ m.setAttribute('hidden',''); });
        if (!isOpen) menu.removeAttribute('hidden');
        return;
    }
    if (!e.target.closest('.m-view-kebab')) {
        document.querySelectorAll('.m-view-kebab-menu').forEach(function(m){ m.setAttribute('hidden',''); });
    }
});
</script>
<!-- } 1:1 문의 보기 끝 -->
