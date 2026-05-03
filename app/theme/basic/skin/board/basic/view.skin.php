<?php
if (!defined("_GNUBOARD_")) exit;
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">
            <article class="m-card m-view">

                <header class="m-view-head">
                    <?php if ($category_name && !empty($view['ca_name'])) { ?>
                    <span class="m-cate-tag" style="margin-bottom: 8px;"><?php echo $view['ca_name'] ?></span>
                    <?php } ?>
                    <h1 class="m-view-title"><?php echo cut_str(get_text($view['wr_subject']), 70) ?></h1>

                    <div class="m-view-meta">
                        <span class="m-view-author"><?php echo get_member_profile_img($view['mb_id']) ?></span>
                        <strong class="m-view-name"><?php echo $view['name'] ?><?php if ($is_ip_view) echo ' ('.$ip.')' ?></strong>
                        <span class="m-view-meta-sep">·</span>
                        <span class="m-view-meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <a href="#bo_vc"><?php echo number_format($view['wr_comment']) ?></a>
                        </span>
                        <span class="m-view-meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <?php echo number_format($view['wr_hit']) ?>
                        </span>
                        <span class="m-view-meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <?php echo date("Y-m-d H:i", strtotime($view['wr_datetime'])) ?>
                        </span>
                    </div>

                    <div id="bo_v_top" class="m-view-actions">
                        <?php ob_start(); ?>
                        <a href="<?php echo $list_href ?>" class="m-icon-btn" title="목록">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                            <span>목록</span>
                        </a>
                        <?php if ($reply_href) { ?>
                        <a href="<?php echo $reply_href ?>" class="m-icon-btn" title="답변">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                            <span>답변</span>
                        </a>
                        <?php } ?>
                        <?php if ($write_href) { ?>
                        <a href="<?php echo $write_href ?>" class="m-icon-btn" title="글쓰기">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            <span>글쓰기</span>
                        </a>
                        <?php } ?>
                        <?php if ($update_href || $delete_href || $copy_href || $move_href || $search_href) { ?>
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
                                <?php if ($copy_href) { ?>
                                <li><a href="<?php echo $copy_href ?>" onclick="board_move(this.href); return false;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    <span>복사</span>
                                </a></li>
                                <?php } ?>
                                <?php if ($move_href) { ?>
                                <li><a href="<?php echo $move_href ?>" onclick="board_move(this.href); return false;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="5 9 2 12 5 15"/><polyline points="9 5 12 2 15 5"/><polyline points="15 19 12 22 9 19"/><polyline points="19 9 22 12 19 15"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="12" y1="2" x2="12" y2="22"/></svg>
                                    <span>이동</span>
                                </a></li>
                                <?php } ?>
                                <?php if ($search_href) { ?>
                                <li><a href="<?php echo $search_href ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    <span>검색</span>
                                </a></li>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php }
                        $link_buttons = ob_get_contents();
                        ob_end_flush();
                        ?>
                    </div>
                </header>

                <section class="m-view-body">
                    <?php if (file_exists(G5_SNS_PATH."/view.sns.skin.php")) { ?>
                    <div class="m-view-share">
                        <?php @include_once(G5_SNS_PATH."/view.sns.skin.php"); ?>
                    </div>
                    <?php } ?>

                    <?php
                    if (count($view['file'])) {
                        $img_html = '';
                        foreach ($view['file'] as $view_file) $img_html .= get_file_thumbnail($view_file);
                        if (trim($img_html)) echo '<div id="bo_v_img" class="m-view-images">'.$img_html.'</div>';
                    }
                    ?>

                    <div id="bo_v_atc">
                        <div id="bo_v_con" class="m-view-content"><?php echo get_view_thumbnail($view['content']) ?></div>
                    </div>

                    <?php if ($is_signature) { ?>
                    <div class="m-view-signature"><?php echo $signature ?></div>
                    <?php } ?>

                    <?php if ($scrap_href) { ?>
                    <div class="m-view-scrap">
                        <a href="<?php echo $scrap_href ?>" target="_blank" class="m-icon-btn" onclick="win_scrap(this.href); return false;" title="스크랩">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                            <span>스크랩</span>
                        </a>
                    </div>
                    <?php } ?>

                    <?php if ($good_href || $nogood_href) { ?>
                    <div class="m-view-react">
                        <?php if ($good_href) { ?>
                        <a href="<?php echo $good_href.'&amp;'.$qstr ?>" id="good_button" class="m-react-btn m-react-good">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                            <span>추천</span>
                            <strong><?php echo number_format($view['wr_good']) ?></strong>
                        </a>
                        <b id="bo_v_act_good" class="m-react-toast"></b>
                        <?php } ?>
                        <?php if ($nogood_href) { ?>
                        <a href="<?php echo $nogood_href.'&amp;'.$qstr ?>" id="nogood_button" class="m-react-btn m-react-nogood">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zM17 2h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"/></svg>
                            <span>비추천</span>
                            <strong><?php echo number_format($view['wr_nogood']) ?></strong>
                        </a>
                        <b id="bo_v_act_nogood" class="m-react-toast"></b>
                        <?php } ?>
                    </div>
                    <?php } else if ($board['bo_use_good'] || $board['bo_use_nogood']) { ?>
                    <div class="m-view-react">
                        <?php if ($board['bo_use_good']) { ?>
                        <span class="m-react-btn m-react-good is-disabled">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                            <span>추천</span>
                            <strong><?php echo number_format($view['wr_good']) ?></strong>
                        </span>
                        <?php } ?>
                        <?php if ($board['bo_use_nogood']) { ?>
                        <span class="m-react-btn m-react-nogood is-disabled">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zM17 2h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"/></svg>
                            <span>비추천</span>
                            <strong><?php echo number_format($view['wr_nogood']) ?></strong>
                        </span>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </section>

                <?php
                $attach_cnt = 0;
                if ($view['file']['count']) {
                    for ($i = 0; $i < count($view['file']); $i++) {
                        if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) $attach_cnt++;
                    }
                }
                ?>
                <?php if ($attach_cnt) { ?>
                <section class="m-view-files">
                    <h2 class="m-view-section-title">첨부파일</h2>
                    <ul>
                        <?php for ($i = 0; $i < count($view['file']); $i++) {
                            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) { ?>
                        <li>
                            <a href="<?php echo $view['file'][$i]['href'] ?>" class="view_file_download m-view-file">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                <strong><?php echo $view['file'][$i]['source'] ?></strong>
                                <span class="m-view-file-meta"><?php echo $view['file'][$i]['size'] ?> · <?php echo $view['file'][$i]['download'] ?>회 다운로드 · <?php echo $view['file'][$i]['datetime'] ?></span>
                            </a>
                        </li>
                        <?php } } ?>
                    </ul>
                </section>
                <?php } ?>

                <?php if (isset($view['link']) && array_filter($view['link'])) { ?>
                <section class="m-view-links">
                    <h2 class="m-view-section-title">관련링크</h2>
                    <ul>
                        <?php for ($i = 1; $i <= count($view['link']); $i++) {
                            if (!$view['link'][$i]) continue;
                            $link = cut_str($view['link'][$i], 70); ?>
                        <li>
                            <a href="<?php echo $view['link_href'][$i] ?>" target="_blank" class="m-view-link">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                <strong><?php echo $link ?></strong>
                                <span class="m-view-file-meta"><?php echo $view['link_hit'][$i] ?>회 연결</span>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </section>
                <?php } ?>

                <nav class="m-view-nav">
                    <?php if ($prev_href) { ?>
                    <a href="<?php echo $prev_href ?>" class="m-view-nav-item m-view-nav-prev">
                        <span class="m-view-nav-label">▲ 이전글</span>
                        <span class="m-view-nav-title"><?php echo $prev_wr_subject ?></span>
                        <span class="m-view-nav-date"><?php echo str_replace('-', '.', substr($prev_wr_date, 2, 8)) ?></span>
                    </a>
                    <?php } else { ?>
                    <div class="m-view-nav-item m-view-nav-prev is-empty">
                        <span class="m-view-nav-label">▲ 이전글</span>
                        <span class="m-view-nav-empty">이전글이 없습니다.</span>
                    </div>
                    <?php } ?>
                    <?php if ($next_href) { ?>
                    <a href="<?php echo $next_href ?>" class="m-view-nav-item m-view-nav-next">
                        <span class="m-view-nav-label">▼ 다음글</span>
                        <span class="m-view-nav-title"><?php echo $next_wr_subject ?></span>
                        <span class="m-view-nav-date"><?php echo str_replace('-', '.', substr($next_wr_date, 2, 8)) ?></span>
                    </a>
                    <?php } else { ?>
                    <div class="m-view-nav-item m-view-nav-next is-empty">
                        <span class="m-view-nav-label">▼ 다음글</span>
                        <span class="m-view-nav-empty">다음글이 없습니다.</span>
                    </div>
                    <?php } ?>
                </nav>

                <?php
                // 댓글 (view_comment.skin.php 가 출력 — 별도 모던화 예정)
                include_once(G5_BBS_PATH.'/view_comment.php');
                ?>
            </article>
        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>
    <?php require G5_THEME_PATH.'/modern/_footer.inc.php'; ?>
</div>

<style>
.m-view { padding: 0; overflow: hidden; }

.m-view-head {
    padding: 24px 28px 18px;
    border-bottom: 1px solid var(--m-border);
}
.m-view-title {
    font-size: var(--m-text-2xl); font-weight: 700;
    color: var(--m-text); margin-bottom: 12px;
    word-break: break-word;
}
.m-view-meta {
    display: flex; align-items: center; gap: 6px;
    flex-wrap: wrap;
    font-size: var(--m-text-sm); color: var(--m-text-muted);
}
.m-view-author { display: inline-flex; align-items: center; }
.m-view-author img {
    width: 28px; height: 28px; border-radius: 50%;
    border: 1px solid var(--m-border);
    margin-right: 4px; vertical-align: middle;
}
.m-view-name { color: var(--m-text); font-weight: 600; font-size: var(--m-text-base); }
.m-view-meta-sep { color: var(--m-text-faint); }
.m-view-meta-item {
    display: inline-flex; align-items: center; gap: 4px;
    color: var(--m-text-muted);
}
.m-view-meta-item a { color: inherit; text-decoration: none; }
.m-view-meta-item a:hover { color: var(--m-primary); }

.m-view-actions {
    display: flex; flex-wrap: wrap; gap: 6px;
    margin-top: 14px;
    justify-content: flex-end;
}
.m-view-actions .m-icon-btn {
    width: auto; padding: 6px 12px;
    display: inline-flex; align-items: center; gap: 4px;
    font-size: var(--m-text-sm); color: var(--m-text-soft);
}
.m-view-actions .m-icon-btn span { font-size: var(--m-text-sm); }
.m-icon-btn-danger:hover {
    background: rgba(239,68,68,0.1) !important;
    border-color: rgba(239,68,68,0.4) !important;
    color: #ef4444 !important;
}

.m-view-kebab { position: relative; }
.m-view-kebab-menu {
    position: absolute; top: calc(100% + 4px); right: 0;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    box-shadow: var(--m-shadow-md);
    list-style: none; padding: 4px; margin: 0;
    min-width: 130px; z-index: 100;
}
.m-view-kebab-menu li a {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px;
    border-radius: var(--m-radius-sm);
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    text-decoration: none;
}
.m-view-kebab-menu li a svg { flex-shrink: 0; }
.m-view-kebab-menu li a:hover { background: var(--m-surface-2); color: var(--m-text); }
.m-view-kebab-menu li a.is-danger { color: #ef4444; }
.m-view-kebab-menu li a.is-danger:hover { background: rgba(239,68,68,0.1); color: #ef4444; }

.m-view-body { padding: 24px 28px 28px; }

.m-view-share {
    display: flex; flex-wrap: wrap; gap: 8px;
    margin-bottom: 18px; padding-bottom: 14px;
    border-bottom: 1px dashed var(--m-border);
}
.m-view-share .m-icon-btn { width: auto; padding: 6px 12px; gap: 4px; }
.m-view-share .m-icon-btn span { font-size: var(--m-text-sm); }

.m-view-scrap {
    display: flex; justify-content: flex-end;
    margin: 14px 0 0;
}
.m-view-scrap .m-icon-btn { width: auto; padding: 6px 12px; gap: 4px; font-size: var(--m-text-sm); color: var(--m-text-soft); }
.m-view-scrap .m-icon-btn span { font-size: var(--m-text-sm); }

.m-view-images {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px; margin-bottom: 18px;
}
.m-view-images img { max-width: 100%; height: auto; border-radius: var(--m-radius); }

.m-view-content {
    font-size: var(--m-text-md); line-height: var(--m-leading-relaxed);
    color: var(--m-text); word-break: break-word;
    min-height: 180px; padding: 8px 0 24px;
}
.m-view-content img { max-width: 100%; height: auto; border-radius: var(--m-radius-sm); }
.m-view-content a { color: var(--m-primary); }
.m-view-content blockquote {
    border-left: 3px solid var(--m-border-hover);
    padding: 4px 0 4px 14px; margin: 12px 0;
    color: var(--m-text-soft);
}

.m-view-signature {
    margin-top: 18px; padding: 12px 14px;
    background: var(--m-surface-2); border-radius: var(--m-radius);
    font-size: var(--m-text-sm); color: var(--m-text-muted);
}

.m-view-react {
    display: flex; gap: 12px; justify-content: center;
    margin: 24px 0 8px; padding: 18px 0;
    border-top: 1px solid var(--m-border);
}
.m-react-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 18px; border-radius: 999px;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    color: var(--m-text-soft); text-decoration: none;
    font-size: var(--m-text-sm); cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.m-react-btn:hover:not(.is-disabled) { background: var(--m-primary-soft); border-color: var(--m-primary); color: var(--m-primary); }
.m-react-btn.is-disabled { cursor: default; opacity: 0.7; }
.m-react-btn strong { font-weight: 700; color: var(--m-text); }
.m-react-toast { font-size: var(--m-text-xs); color: var(--m-text-muted); display: none; }

.m-view-files, .m-view-links {
    margin: 18px 28px; padding: 14px 16px;
    background: var(--m-surface-2); border-radius: var(--m-radius);
}
.m-view-section-title {
    font-size: var(--m-text-sm); font-weight: 600;
    color: var(--m-text-soft); margin: 0 0 10px;
}
.m-view-files ul, .m-view-links ul { list-style: none; padding: 0; margin: 0; }
.m-view-files li, .m-view-links li { margin-bottom: 8px; }
.m-view-files li:last-child, .m-view-links li:last-child { margin-bottom: 0; }
.m-view-file, .m-view-link {
    display: flex; flex-wrap: wrap; align-items: center; gap: 6px;
    color: var(--m-text); text-decoration: none;
    padding: 8px 10px; border-radius: var(--m-radius-sm);
    transition: background 0.15s;
}
.m-view-file:hover, .m-view-link:hover { background: var(--m-surface); color: var(--m-primary); }
.m-view-file strong, .m-view-link strong { font-size: var(--m-text-md); font-weight: 500; }
.m-view-file-meta {
    flex-basis: 100%; font-size: var(--m-text-xs); color: var(--m-text-faint);
    margin-left: 22px;
}

.m-view-nav {
    display: grid; grid-template-columns: 1fr 1fr; gap: 0;
    border-top: 1px solid var(--m-border);
}
@media (max-width: 540px) { .m-view-nav { grid-template-columns: 1fr; } }
.m-view-nav-item {
    display: flex; flex-direction: column; gap: 4px;
    padding: 14px 20px;
    text-decoration: none; color: var(--m-text);
    transition: background 0.15s;
    border-right: 1px solid var(--m-border);
}
.m-view-nav-item:last-child { border-right: 0; }
.m-view-nav-item.is-empty { cursor: default; }
.m-view-nav-item.is-empty:hover { background: transparent; }
a.m-view-nav-item:hover { background: var(--m-surface-2); }
.m-view-nav-next { text-align: right; }
.m-view-nav-label { font-size: var(--m-text-xs); color: var(--m-text-faint); font-weight: 600; }
.m-view-nav-title {
    font-size: var(--m-text-base); color: var(--m-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-view-nav-date { font-size: var(--m-text-xs); color: var(--m-text-faint); }
.m-view-nav-empty { font-size: var(--m-text-base); color: var(--m-text-faint); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var moreBtn  = document.querySelector('.btn_more_opt.is_view_btn');
    var moreMenu = document.querySelector('.m-view-more-menu');
    if (moreBtn && moreMenu) {
        moreBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            moreMenu.hidden = !moreMenu.hidden;
        });
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.is_view_btn')) moreMenu.hidden = true;
        });
    }
});

<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if (!g5_is_member) {
            alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
            return false;
        }
        var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";
        if (confirm(msg)) {
            $(this).attr("href", $(this).attr("href") + "&js=on");
            return true;
        }
        return false;
    });
});
<?php } ?>

function board_move(href) {
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}

$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });

    $("#good_button, #nogood_button").click(function() {
        var $tx = (this.id === "good_button") ? $("#bo_v_act_good") : $("#bo_v_act_nogood");
        excute_good(this.href, $(this), $tx);
        return false;
    });

    $("#bo_v_atc").viewimageresize();
});

function excute_good(href, $el, $tx) {
    $.post(href, { js: "on" }, function(data) {
        if (data.error) { alert(data.error); return false; }
        if (data.count) {
            $el.find("strong").text(number_format(String(data.count)));
            $tx.text($tx.attr("id").indexOf("nogood") > -1 ? "이 글을 비추천하셨습니다." : "이 글을 추천하셨습니다.");
            $tx.fadeIn(200).delay(2500).fadeOut(200);
        }
    }, "json");
}

// 케밥 메뉴 토글 — 버튼 클릭 시 hidden 속성 토글, 바깥 클릭 시 닫기
document.addEventListener("click", function(e) {
    var btn = e.target.closest(".m-view-kebab .btn_more_opt");
    if (btn) {
        e.preventDefault();
        var menu = btn.parentNode.querySelector(".m-view-kebab-menu");
        var isOpen = !menu.hasAttribute("hidden");
        document.querySelectorAll(".m-view-kebab-menu").forEach(function(m){ m.setAttribute("hidden",""); });
        if (!isOpen) menu.removeAttribute("hidden");
        return;
    }
    if (!e.target.closest(".m-view-kebab")) {
        document.querySelectorAll(".m-view-kebab-menu").forEach(function(m){ m.setAttribute("hidden",""); });
    }
});
</script>
<!-- } 게시물 읽기 끝 -->
