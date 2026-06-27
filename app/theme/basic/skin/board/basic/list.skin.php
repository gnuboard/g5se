<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');

$colspan = 5;
if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;
?>

<!-- 게시판 목록 시작 { -->
<?php // bo_use_list_view 로 view.php 뒤에 올 땐 골격(m-shell)을 이미 view.skin 이 열어놨으므로 재사용 (중복 방지)
if (empty($g5['m_board_chrome_open'])) { ?>
<div class="m-shell">

    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">
<?php } ?>
        <?php if (!empty($g5['board_content_head_html']) && empty($g5['board_content_head_rendered'])) {
            $g5['board_content_head_rendered'] = true;
            echo '<div class="m-board-content m-board-content-head">'.$g5['board_content_head_html'].'</div>';
        } ?>

        <!-- 상단바 -->
        <div class="m-board-head">
            <div>
                <h1 style="font-size: 22px; margin-bottom: 4px;"><?php echo $board['bo_subject'] ?></h1>
                <p style="font-size: 13px; color: var(--m-text-muted);">
                    Total <strong style="color: var(--m-text);"><?php echo number_format($total_count) ?></strong>건 · <?php echo $page ?> 페이지
                </p>
            </div>
            <div class="m-board-actions">
                <button type="button" class="m-icon-btn btn_bo_sch" aria-label="검색" title="검색">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
                <?php if ($rss_href) { ?>
                <a href="<?php echo $rss_href ?>" class="m-icon-btn" title="RSS">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"/><path d="M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1"/></svg>
                </a>
                <?php } ?>
                <?php if ($admin_href) { ?>
                <a href="<?php echo $admin_href ?>" class="m-icon-btn" title="관리자">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                </a>
                <?php } ?>
                <?php if ($write_href) { ?>
                <a href="<?php echo $write_href ?>" class="m-btn m-btn-primary" style="width: auto; padding: 9px 14px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 4px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    글쓰기
                </a>
                <?php } ?>
            </div>
        </div>

        <!-- 카테고리 -->
        <?php if ($is_category) { ?>
        <nav class="m-board-categories"><?php echo $category_option ?></nav>
        <?php } ?>

        <!-- 검색 드로어 -->
        <div class="m-board-search-drawer" hidden>
            <form name="fsearch" method="get" class="m-board-search-form">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
                <input type="hidden" name="sca" value="<?php echo $sca ?>">
                <input type="hidden" name="sop" value="and">
                <select name="sfl" id="sfl" class="m-input" style="width: auto; flex: 0 0 auto;">
                    <?php echo get_board_sfl_select_options($sfl); ?>
                </select>
                <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required class="m-input" placeholder="검색어 입력" maxlength="20">
                <button type="submit" class="m-btn m-btn-primary" style="width: auto; padding: 10px 18px;">검색</button>
                <button type="button" class="m-icon-btn bo_sch_cls" aria-label="닫기">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </form>
        </div>

        <!-- 목록 -->
        <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update" onsubmit="return fboardlist_submit(this);" method="post">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
            <input type="hidden" name="stx" value="<?php echo $stx ?>">
            <input type="hidden" name="spt" value="<?php echo $spt ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sst" value="<?php echo $sst ?>">
            <input type="hidden" name="sod" value="<?php echo $sod ?>">
            <input type="hidden" name="page" value="<?php echo $page ?>">
            <input type="hidden" name="sw" value="">

            <?php if (($is_admin == 'super' || $is_auth) && $is_checkbox) { ?>
            <div class="m-board-bulk">
                <button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="m-bulk-btn">선택삭제</button>
                <button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" class="m-bulk-btn">선택복사</button>
                <button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" class="m-bulk-btn">선택이동</button>
            </div>
            <?php } ?>

            <div class="m-card" style="padding: 0;">
                <table class="m-board-table">
                    <thead>
                        <tr>
                            <?php if ($is_checkbox) { ?>
                            <th class="m-col-chk">
                                <input type="checkbox" id="chkall" onclick="all_checked(this.checked)" aria-label="전체선택">
                            </th>
                            <?php } ?>
                            <th class="m-col-num">번호</th>
                            <th class="m-col-subject">제목</th>
                            <th class="m-col-name">글쓴이</th>
                            <th class="m-col-meta"><?php echo subject_sort_link('wr_hit', $qstr2, 1) ?>조회</a></th>
                            <?php if ($is_good)   { ?><th class="m-col-meta"><?php echo subject_sort_link('wr_good',   $qstr2, 1) ?>추천</a></th><?php } ?>
                            <?php if ($is_nogood) { ?><th class="m-col-meta"><?php echo subject_sort_link('wr_nogood', $qstr2, 1) ?>비추천</a></th><?php } ?>
                            <th class="m-col-date"><?php echo subject_sort_link('wr_datetime', $qstr2, 1) ?>날짜</a></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < count($list); $i++) { ?>
                        <tr class="<?php echo $list[$i]['is_notice'] ? 'm-row-notice' : '' ?> <?php echo ($wr_id == $list[$i]['wr_id']) ? 'm-row-current' : '' ?>">
                            <?php if ($is_checkbox) { ?>
                            <td class="m-col-chk">
                                <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" aria-label="선택">
                            </td>
                            <?php } ?>
                            <td class="m-col-num">
                                <?php
                                if ($list[$i]['is_notice']) echo '<span class="m-badge m-badge-notice">공지</span>';
                                else if ($wr_id == $list[$i]['wr_id']) echo '<span class="m-badge m-badge-current">열람중</span>';
                                else echo $list[$i]['num'];
                                ?>
                            </td>
                            <td class="m-col-subject" style="padding-left: <?php echo $list[$i]['reply'] ? (16 + strlen($list[$i]['wr_reply']) * 10) : 16; ?>px;">
                                <?php if ($is_category && $list[$i]['ca_name']) { ?>
                                <a href="<?php echo $list[$i]['ca_name_href'] ?>" class="m-cate-tag"><?php echo $list[$i]['ca_name'] ?></a>
                                <?php } ?>
                                <a href="<?php echo $list[$i]['href'] ?>" class="m-subject-link">
                                    <?php echo $list[$i]['icon_reply'] ?>
                                    <?php if (isset($list[$i]['icon_secret'])) echo rtrim($list[$i]['icon_secret']); ?>
                                    <span class="m-subject-text"><?php echo $list[$i]['subject'] ?></span>
                                    <?php if ($list[$i]['comment_cnt']) { ?>
                                    <span class="m-comment-count"><?php echo $list[$i]['wr_comment'] ?></span>
                                    <?php } ?>
                                </a>
                                <?php
                                $icons = '';
                                if ($list[$i]['icon_new'])         $icons .= '<span class="m-icon-pill m-icon-new">N</span>';
                                if (!empty($list[$i]['icon_hot']))  $icons .= '<span class="m-icon-pill m-icon-hot">HOT</span>';
                                if (!empty($list[$i]['icon_file'])) $icons .= '<span class="m-icon-mini" title="첨부"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg></span>';
                                if (!empty($list[$i]['icon_link'])) $icons .= '<span class="m-icon-mini" title="링크"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></span>';
                                if ($icons) echo '<span class="m-row-icons">'.$icons.'</span>';
                                ?>
                            </td>
                            <td class="m-col-name"><?php echo $list[$i]['name'] ?></td>
                            <td class="m-col-meta"><?php echo $list[$i]['wr_hit'] ?></td>
                            <?php if ($is_good)   { ?><td class="m-col-meta"><?php echo $list[$i]['wr_good']   ?></td><?php } ?>
                            <?php if ($is_nogood) { ?><td class="m-col-meta"><?php echo $list[$i]['wr_nogood'] ?></td><?php } ?>
                            <td class="m-col-date"><?php echo $list[$i]['datetime2'] ?></td>
                        </tr>
                        <?php } ?>
                        <?php if (count($list) == 0) { ?>
                        <?php $is_search_empty = (!empty($stx) || !empty($sca)); ?>
                        <tr><td colspan="<?php echo $colspan ?>" class="m-empty">
                            <div style="padding: 60px 20px; text-align: center; color: var(--m-text-faint);">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5; display: block; margin: 0 auto 8px;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <div style="font-size: 14px;"><?php echo $is_search_empty ? '검색 결과가 없습니다' : '게시물이 없습니다'; ?></div>
                                <?php if ($is_search_empty) { ?>
                                <div style="margin-top: 16px;">
                                    <a href="<?php echo get_pretty_url($bo_table) ?>" class="m-btn" style="width: auto; padding: 9px 16px; display: inline-flex;">전체 목록 보기</a>
                                </div>
                                <?php } ?>
                            </div>
                        </td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="m-board-pagination"><?php echo $write_pages; ?></div>
        </form>
        <?php if (!empty($g5['board_content_tail_html']) && empty($g5['board_content_tail_rendered'])) {
            $g5['board_content_tail_rendered'] = true;
            echo '<div class="m-board-content m-board-content-tail">'.$g5['board_content_tail_html'].'</div>';
        } ?>
        </div><!-- /m-main-col -->

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>
    <?php require G5_THEME_PATH.'/modern/_footer.inc.php'; ?>
</div>

<style>
.m-board-head {
    display: flex; justify-content: space-between; align-items: flex-end;
    flex-wrap: wrap; gap: 16px; margin-bottom: 20px;
    padding-bottom: 14px; border-bottom: 1px solid var(--m-border);
}
.m-board-actions { display: flex; align-items: center; gap: 8px; }
.m-board-content {
    padding: 16px 18px; margin-bottom: 18px;
    background: var(--m-surface); border: 1px solid var(--m-border);
    border-radius: var(--m-radius); color: var(--m-text);
    box-shadow: var(--m-shadow);
}
.m-board-content-tail { margin-top: 18px; margin-bottom: 0; }
.m-board-content img { max-width: 100%; height: auto; }
.m-board-content p:last-child { margin-bottom: 0; }

.m-icon-btn {
    width: 36px; height: 36px; padding: 0;
    background: var(--m-surface); border: 1px solid var(--m-border);
    border-radius: var(--m-radius); color: var(--m-text-soft);
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; text-decoration: none;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.m-icon-btn:hover { background: var(--m-surface-2); color: var(--m-text); border-color: var(--m-border-hover); }

.m-board-categories {
    display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px;
    padding: 12px; background: var(--m-surface-2); border-radius: var(--m-radius);
}
.m-board-categories ul { display: contents; }
.m-board-categories li { list-style: none; }
.m-board-categories a {
    display: inline-block; padding: 6px 12px;
    background: var(--m-surface); border: 1px solid var(--m-border);
    border-radius: 999px; font-size: 13px; color: var(--m-text-soft); text-decoration: none;
}
.m-board-categories a:hover { border-color: var(--m-primary); color: var(--m-primary); }
.m-board-categories a.on, .m-board-categories a.bo_cate_on {
    background: var(--m-primary); border-color: var(--m-primary); color: white;
}

.m-board-search-drawer {
    margin-bottom: 16px; padding: 14px;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border); border-radius: var(--m-radius);
}
.m-board-search-form { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.m-board-search-form .m-input { flex: 1; min-width: 160px; }

.m-board-bulk { margin-bottom: 12px; display: flex; gap: 6px; }
.m-bulk-btn {
    padding: 5px 10px; border-radius: var(--m-radius-sm);
    background: transparent; border: 1px solid var(--m-border);
    color: var(--m-text-faint);
    font-size: var(--m-text-sm); font-weight: 500; font-family: inherit;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.m-bulk-btn:hover { background: var(--m-surface-2); border-color: var(--m-border-hover); color: var(--m-text-soft); }

.m-board-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.m-board-table thead th {
    padding: 12px 10px; text-align: center; font-weight: 600;
    font-size: 12px; color: var(--m-text-muted);
    background: var(--m-surface-2); border-bottom: 1px solid var(--m-border);
    text-transform: uppercase; letter-spacing: 0.04em;
}
.m-board-table thead th a { color: inherit; text-decoration: none; }
.m-board-table thead th a:hover { color: var(--m-text); }
.m-board-table tbody tr { border-bottom: 1px solid var(--m-border); transition: background 0.1s; }
.m-board-table tbody tr:hover { background: var(--m-surface-2); }
.m-board-table tbody tr:last-child { border-bottom: 0; }
.m-board-table td { padding: 14px 10px; vertical-align: middle; text-align: center; }

.m-col-chk { width: 36px; }
.m-col-num { width: 80px; color: var(--m-text-faint); font-size: 13px; }
/* 텍스트 칼럼(제목·글쓴이) 은 좌측 정렬, 숫자/날짜 칼럼은 가운데. */
.m-board-table thead th.m-col-subject,
.m-board-table tbody td.m-col-subject { text-align: left; padding-left: 16px !important; }
.m-board-table thead th.m-col-name,
.m-board-table tbody td.m-col-name { text-align: left; }
.m-col-name { width: 110px; color: var(--m-text-soft); font-size: 13px; }
.m-col-meta { width: 70px; color: var(--m-text-muted); font-size: 13px; }
.m-col-date { width: 110px; color: var(--m-text-faint); font-size: 12px; padding-right: 16px !important; }

@media (max-width: 720px) {
    .m-col-name, .m-col-meta { display: none; }
    .m-board-table .m-col-num { display: none; }
}

.m-row-notice { background: rgba(37,99,235,0.04); }
.m-row-notice:hover { background: rgba(37,99,235,0.08) !important; }
.m-row-current { background: rgba(245,158,11,0.06); }

.m-badge {
    display: inline-block; padding: 2px 8px;
    border-radius: 999px; font-size: 11px; font-weight: 600;
}
.m-badge-notice  { background: var(--m-primary-soft); color: var(--m-primary); }
.m-badge-current { background: rgba(245,158,11,0.15); color: #d97706; }

.m-subject-link {
    color: var(--m-text); text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px; font-weight: 500;
}
.m-subject-link:hover .m-subject-text { color: var(--m-primary); text-decoration: underline; }
.m-subject-text { word-break: break-word; }
.m-comment-count {
    display: inline-block; padding: 1px 7px;
    background: var(--m-surface-2); border-radius: 999px;
    font-size: 11px; color: var(--m-text-muted); font-weight: 600; margin-left: 4px;
}
.m-cate-tag {
    display: inline-block; padding: 2px 8px;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    border-radius: 4px; font-size: 11px; color: var(--m-text-soft);
    text-decoration: none; margin-right: 6px;
}
.m-row-icons { display: inline-flex; align-items: center; gap: 4px; margin-left: 6px; }
.m-icon-pill {
    display: inline-block; padding: 1px 5px;
    border-radius: 3px; font-size: 10px; font-weight: 700; line-height: 1.4;
}
.m-icon-new { background: #ef4444; color: white; }
.m-icon-hot { background: #f59e0b; color: white; }
.m-icon-mini { color: var(--m-text-faint); display: inline-flex; }

/* 페이지네이션 컨테이너만 — pg_* 자체 스타일은 _head.inc.php 의 글로벌 규칙이 처리 */
.m-board-pagination { margin-top: 24px; display: flex; justify-content: center; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.querySelector('.btn_bo_sch');
    var drawer    = document.querySelector('.m-board-search-drawer');
    var closeBtn  = document.querySelector('.bo_sch_cls');
    if (toggleBtn && drawer) {
        toggleBtn.addEventListener('click', function() {
            drawer.hidden = !drawer.hidden;
            if (!drawer.hidden) drawer.querySelector('input[name=stx]').focus();
        });
    }
    if (closeBtn && drawer) closeBtn.addEventListener('click', function() { drawer.hidden = true; });
});

<?php if ($is_checkbox) { ?>
function all_checked(sw) {
    var f = document.fboardlist;
    for (var i = 0; i < f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]") f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;
    for (var i = 0; i < f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked) chk_count++;
    }
    if (!chk_count) { alert(document.pressed + "할 게시물을 하나 이상 선택하세요."); return false; }
    if (document.pressed == "선택복사") { select_copy("copy"); return; }
    if (document.pressed == "선택이동") { select_copy("move"); return; }
    if (document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;
        f.removeAttribute("target");
        f.action = g5_bbs_url + "/board_list_update";
    }
    return true;
}

function select_copy(sw) {
    var f = document.fboardlist;
    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");
    f.sw.value = sw;
    f.target = "move";
    f.action = g5_bbs_url + "/move";
    f.submit();
}
<?php } ?>
</script>
<!-- } 게시판 목록 끝 -->
