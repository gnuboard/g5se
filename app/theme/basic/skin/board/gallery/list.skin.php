<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container<?php echo empty($g5['m_board_bare_side']) ? ' m-with-sidebar' : ''; ?>" style="padding: 32px 20px 64px;<?php echo $g5["m_board_width_style"] ?? ""; ?>">
        <div class="m-main-col">
            <?php if (!empty($g5['board_content_head_html']) && empty($g5['board_content_head_rendered'])) {
                $g5['board_content_head_rendered'] = true;
                echo '<div class="m-board-content m-board-content-head">'.$g5['board_content_head_html'].'</div>';
            } ?>
            <div class="m-gallery-head">
                <div>
                    <h1><?php echo $board['bo_subject'] ?></h1>
                    <p>Total <strong><?php echo number_format($total_count) ?></strong>건 · <?php echo $page ?> 페이지</p>
                </div>
                <div class="m-gallery-actions">
                    <button type="button" class="m-icon-btn btn_bo_sch" aria-label="검색" title="검색">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                    <?php if ($rss_href) { ?>
                    <a href="<?php echo $rss_href ?>" class="m-icon-btn" title="RSS">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 11a9 9 0 0 1 9 9"/><path d="M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1"/></svg>
                    </a>
                    <?php } ?>
                    <?php if ($admin_href) { ?>
                    <a href="<?php echo $admin_href ?>" class="m-icon-btn" title="관리자">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15 1.65 1.65 0 0 0 3.09 14H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6 1.65 1.65 0 0 0 10 3.09V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.14.32.48.6.91.6H21a2 2 0 0 1 0 4h-.69c-.43 0-.77.28-.91.6z"/></svg>
                    </a>
                    <?php } ?>
                    <?php if ($write_href) { ?>
                    <a href="<?php echo $write_href ?>" class="m-btn m-btn-primary m-gallery-write">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        글쓰기
                    </a>
                    <?php } ?>
                </div>
            </div>

            <?php if ($is_category) { ?>
            <nav class="m-gallery-categories"><?php echo $category_option ?></nav>
            <?php } ?>

            <div class="m-gallery-search-drawer" hidden>
                <form name="fsearch" method="get" class="m-gallery-search-form">
                    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
                    <input type="hidden" name="sca" value="<?php echo $sca ?>">
                    <input type="hidden" name="sop" value="and">
                    <select name="sfl" id="sfl" class="m-input"><?php echo get_board_sfl_select_options($sfl); ?></select>
                    <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required class="m-input" placeholder="검색어 입력" maxlength="20">
                    <button type="submit" class="m-btn m-btn-primary">검색</button>
                    <button type="button" class="m-icon-btn bo_sch_cls" aria-label="닫기">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </form>
            </div>

            <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update" onsubmit="return fboardlist_submit(this);" method="post">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                <input type="hidden" name="spt" value="<?php echo $spt ?>">
                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                <input type="hidden" name="page" value="<?php echo $page ?>">
                <input type="hidden" name="sw" value="">

                <?php if (($is_admin == 'super' || $is_auth) && $is_checkbox) { ?>
                <div class="m-gallery-bulk">
                    <label class="m-gallery-checkall"><input type="checkbox" id="chkall" onclick="all_checked(this.checked)"> 전체선택</label>
                    <button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value">선택삭제</button>
                    <button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value">선택복사</button>
                    <button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value">선택이동</button>
                </div>
                <?php } ?>

                <div class="m-gallery-grid">
                    <?php for ($i=0; $i<count($list); $i++) {
                        $thumb = array('src' => '', 'alt' => '');
                        if (!$list[$i]['is_notice']) {
                            $thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);
                        }
                    ?>
                    <article class="m-gallery-card<?php echo ($wr_id && $wr_id == $list[$i]['wr_id']) ? ' is-current' : '' ?>">
                        <?php if ($is_checkbox) { ?>
                        <label class="m-gallery-select">
                            <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
                            <span class="sound_only"><?php echo $list[$i]['subject'] ?> 선택</span>
                        </label>
                        <?php } ?>
                        <a href="<?php echo $list[$i]['href'] ?>" class="m-gallery-thumb">
                            <?php if ($list[$i]['is_notice']) { ?>
                            <span class="m-gallery-notice">공지</span>
                            <?php } else if (!empty($thumb['src'])) { ?>
                            <img src="<?php echo $thumb['src'] ?>" alt="<?php echo $thumb['alt'] ?>">
                            <?php } else { ?>
                            <span class="m-gallery-empty">no image</span>
                            <?php } ?>
                        </a>
                        <div class="m-gallery-body">
                            <?php if ($is_category && $list[$i]['ca_name']) { ?>
                            <a href="<?php echo $list[$i]['ca_name_href'] ?>" class="m-gallery-cate"><?php echo $list[$i]['ca_name'] ?></a>
                            <?php } ?>
                            <a href="<?php echo $list[$i]['href'] ?>" class="m-gallery-title">
                                <?php echo $list[$i]['subject'] ?>
                                <?php if ($list[$i]['icon_new']) { ?><span class="m-gallery-pill">N</span><?php } ?>
                                <?php if ($list[$i]['comment_cnt']) { ?><span class="m-gallery-comment"><?php echo $list[$i]['wr_comment']; ?></span><?php } ?>
                            </a>
                            <p class="m-gallery-desc"><?php echo utf8_strcut(strip_tags($list[$i]['wr_content']), 68, '..'); ?></p>
                            <div class="m-gallery-meta">
                                <span><?php echo $list[$i]['name'] ?></span>
                                <span><?php echo $list[$i]['datetime2'] ?></span>
                                <span>조회 <?php echo $list[$i]['wr_hit'] ?></span>
                            </div>
                        </div>
                    </article>
                    <?php } ?>
                    <?php if (count($list) == 0) { ?>
                    <div class="m-card m-gallery-none">게시물이 없습니다</div>
                    <?php } ?>
                </div>

                <div class="m-board-pagination"><?php echo $write_pages; ?></div>
            </form>
            <?php if (!empty($g5['board_content_tail_html']) && empty($g5['board_content_tail_rendered'])) {
                $g5['board_content_tail_rendered'] = true;
                echo '<div class="m-board-content m-board-content-tail">'.$g5['board_content_tail_html'].'</div>';
            } ?>
        </div>

        <?php if (empty($g5['m_board_bare_side'])) { ?>
        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
        <?php } ?>
    </main>

    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>

<style>
.m-board-content { padding:16px 18px; margin-bottom:18px; background:var(--m-surface); border:1px solid var(--m-border); border-radius:var(--m-radius); color:var(--m-text); box-shadow:var(--m-shadow); }
.m-board-content-tail { margin-top:18px; margin-bottom:0; }
.m-board-content img { max-width:100%; height:auto; }
.m-board-content p:last-child { margin-bottom:0; }
.m-gallery-head { display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--m-border); }
.m-gallery-head h1 { font-size:22px; margin-bottom:4px; }
.m-gallery-head p { font-size:13px; color:var(--m-text-muted); }
.m-gallery-head strong { color:var(--m-text); }
.m-gallery-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.m-gallery-write { width:auto; padding:9px 14px; }
.m-gallery-write svg { margin-right:4px; }
.m-icon-btn { width:36px; height:36px; padding:0; background:var(--m-surface); border:1px solid var(--m-border); border-radius:var(--m-radius); color:var(--m-text-soft); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; text-decoration:none; transition:background .15s,color .15s,border-color .15s; }
.m-icon-btn:hover { background:var(--m-surface-2); color:var(--m-text); border-color:var(--m-border-hover); }
.m-gallery-categories { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; padding:12px; background:var(--m-surface-2); border-radius:var(--m-radius); }
.m-gallery-categories ul { display:contents; }
.m-gallery-categories li { list-style:none; }
.m-gallery-categories a { display:inline-block; padding:6px 12px; background:var(--m-surface); border:1px solid var(--m-border); border-radius:999px; font-size:13px; color:var(--m-text-soft); text-decoration:none; }
.m-gallery-categories a:hover, .m-gallery-categories a#bo_cate_on { border-color:var(--m-primary); color:var(--m-primary); }
.m-gallery-search-drawer { margin-bottom:16px; padding:14px; background:var(--m-surface-2); border:1px solid var(--m-border); border-radius:var(--m-radius); }
.m-gallery-search-form { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.m-gallery-search-form select { width:auto; flex:0 0 auto; }
.m-gallery-search-form input[type=text] { flex:1; min-width:160px; }
.m-gallery-search-form .m-btn { width:auto; padding:10px 18px; }
.m-gallery-bulk { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:12px; }
.m-gallery-bulk button, .m-gallery-checkall { padding:6px 10px; border:1px solid var(--m-border); border-radius:var(--m-radius-sm); background:transparent; color:var(--m-text-muted); font-size:var(--m-text-sm); font-family:inherit; }
.m-gallery-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:16px; min-width:0; }
.m-gallery-card { position:relative; min-width:0; overflow:hidden; background:var(--m-surface); border:1px solid var(--m-border); border-radius:var(--m-radius-lg); box-shadow:var(--m-shadow); transition:transform .15s,border-color .15s; }
.m-gallery-card:hover { transform:translateY(-2px); border-color:var(--m-border-hover); }
.m-gallery-card.is-current { border-color:var(--m-primary); }
.m-gallery-select { position:absolute; top:10px; left:10px; z-index:2; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.9); border:1px solid var(--m-border); border-radius:var(--m-radius-sm); }
.m-gallery-thumb { display:flex; align-items:center; justify-content:center; aspect-ratio:4/3; background:var(--m-surface-2); color:var(--m-text-faint); text-decoration:none; overflow:hidden; }
.m-gallery-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.m-gallery-notice { display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border-radius:50%; background:var(--m-primary-soft); color:var(--m-primary); font-weight:700; }
.m-gallery-empty { font-size:var(--m-text-sm); color:var(--m-text-faint); }
.m-gallery-body { padding:14px; min-width:0; }
.m-gallery-cate { display:inline-flex; margin-bottom:8px; padding:3px 7px; border-radius:999px; background:var(--m-primary-soft); color:var(--m-primary) !important; font-size:var(--m-text-xs); text-decoration:none; }
.m-gallery-title { display:flex; align-items:center; gap:5px; min-width:0; color:var(--m-text) !important; font-size:var(--m-text-md); font-weight:700; line-height:1.4; text-decoration:none; word-break:break-word; }
.m-gallery-title:hover { color:var(--m-primary) !important; }
.m-gallery-pill, .m-gallery-comment { flex-shrink:0; padding:1px 5px; border-radius:var(--m-radius-sm); font-size:10px; line-height:16px; font-weight:700; }
.m-gallery-pill { background:#dcfce7; color:#15803d; }
.m-gallery-comment { background:var(--m-surface-2); color:var(--m-primary); }
.m-gallery-desc { margin-top:7px !important; color:var(--m-text-muted); font-size:var(--m-text-sm); line-height:1.5; min-height:3em; }
.m-gallery-meta { display:flex; flex-wrap:wrap; gap:6px 10px; margin-top:12px; color:var(--m-text-faint); font-size:var(--m-text-xs); }
.m-gallery-none { grid-column:1 / -1; text-align:center; color:var(--m-text-faint); }
.m-board-pagination { margin-top:24px; display:flex; justify-content:center; }
@media (max-width:560px) {
    .m-gallery-grid { grid-template-columns:1fr; gap:12px; }
    .m-gallery-head { align-items:flex-start; }
    .m-gallery-actions { width:100%; }
    .m-gallery-write { flex:1; }
}
</style>

<script>
$(".btn_bo_sch").on("click", function() {
    $(".m-gallery-search-drawer").prop("hidden", function(_, hidden) { return !hidden; });
});
$(".bo_sch_cls").on("click", function() {
    $(".m-gallery-search-drawer").prop("hidden", true);
});
<?php if ($is_checkbox) { ?>
function all_checked(sw) {
    var f = document.fboardlist;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]") f.elements[i].checked = sw;
    }
}
function fboardlist_submit(f) {
    var chk_count = 0;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked) chk_count++;
    }
    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }
    if (document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }
    if (document.pressed == "선택이동") {
        select_copy("move");
        return;
    }
    if (document.pressed == "선택삭제" && !confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다")) {
        return false;
    }
    f.removeAttribute("target");
    f.action = g5_bbs_url + "/board_list_update";
}
function select_copy(sw) {
    var f = document.fboardlist;
    var str = sw == "copy" ? "복사" : "이동";
    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");
    f.sw.value = sw;
    f.target = "move";
    f.action = g5_bbs_url + "/move";
    f.submit();
}
<?php } else { ?>
function fboardlist_submit(f) { return true; }
<?php } ?>
</script>
