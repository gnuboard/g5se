<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');

$colspan = 5;
if ($is_checkbox) $colspan++;
?>

<!-- 1:1 문의 목록 시작 { -->
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">
            <header class="m-board-head">
                <div class="m-qa-head-text">
                    <h1 class="m-qa-head-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span><?php echo get_text($qaconfig['qa_title']) ?></span>
                    </h1>
                    <p class="m-qa-head-sub">
                        Total <strong><?php echo number_format($total_count) ?></strong>건 · <?php echo $page ?> 페이지
                    </p>
                </div>
                <div class="m-board-actions">
                    <button type="button" class="m-icon-btn btn_bo_sch" aria-label="검색" title="검색">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                    <?php if ($admin_href) { ?>
                    <a href="<?php echo $admin_href ?>" class="m-icon-btn" title="관리자">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </a>
                    <?php } ?>
                    <?php if ($write_href) { ?>
                    <a href="/qa/write" class="m-btn m-btn-primary" style="width:auto; padding:8px 14px; gap:4px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        문의 등록
                    </a>
                    <?php } ?>
                </div>
            </header>

            <div class="m-board-search-drawer" hidden>
                <form name="fsearch" method="get" class="m-board-search-form">
                    <input type="hidden" name="sca" value="<?php echo get_text($sca) ?>">
                    <input type="hidden" name="sop" value="and">
                    <select name="sfl" id="sfl" class="m-input" style="width: auto; flex: 0 0 auto;">
                        <?php echo get_qa_sfl_select_options($sfl); ?>
                    </select>
                    <input type="text" name="stx" value="<?php echo get_text(stripslashes($stx)) ?>" required class="m-input" placeholder="검색어 입력" maxlength="60">
                    <button type="submit" class="m-btn" style="width:auto; padding:9px 16px;">검색</button>
                    <button type="button" class="bo_sch_cls m-btn m-btn-ghost" style="width:auto; padding:8px 12px;">닫기</button>
                </form>
            </div>

            <?php if ($category_option) { ?>
            <nav class="m-qa-cates">
                <?php
                // gnuboard 의 $category_option 은 이미 <li><a>...</a></li> 형태 — 그대로 사용
                echo '<ul>'.$category_option.'</ul>';
                ?>
            </nav>
            <?php } ?>

            <form name="fqalist" id="fqalist" action="/qa/delete" onsubmit="return fqalist_submit(this);" method="post">
                <input type="hidden" name="stx"   value="<?php echo get_text($stx) ?>">
                <input type="hidden" name="sca"   value="<?php echo get_text($sca) ?>">
                <input type="hidden" name="page"  value="<?php echo $page ?>">
                <input type="hidden" name="token" value="<?php echo get_text($token) ?>">

                <div class="m-card" style="padding: 0;">
                    <table class="m-board-table m-qa-table">
                        <thead>
                            <tr>
                                <?php if ($is_checkbox) { ?>
                                <th class="m-col-chk">
                                    <input type="checkbox" id="chkall" onclick="all_checked(this.checked);">
                                </th>
                                <?php } ?>
                                <th class="m-col-num">번호</th>
                                <th class="m-col-subject">제목</th>
                                <th class="m-col-name">글쓴이</th>
                                <th class="m-col-date">등록일</th>
                                <th class="m-col-status">상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($list); $i++) { ?>
                            <tr>
                                <?php if ($is_checkbox) { ?>
                                <td class="m-col-chk">
                                    <input type="checkbox" name="chk_qa_id[]" value="<?php echo $list[$i]['qa_id'] ?>">
                                </td>
                                <?php } ?>
                                <td class="m-col-num"><?php echo $list[$i]['num']; ?></td>
                                <td class="m-col-subject">
                                    <?php if ($list[$i]['category']) { ?>
                                    <span class="m-qa-cate-tag"><?php echo get_text($list[$i]['category']) ?></span>
                                    <?php } ?>
                                    <a href="/qa/<?php echo (int)$list[$i]['qa_id'] ?>" class="m-board-subject"><?php echo $list[$i]['subject'] ?></a>
                                    <?php if ($list[$i]['icon_file']) { ?>
                                    <svg class="m-icon-mini" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                    <?php } ?>
                                </td>
                                <td class="m-col-name"><?php echo $list[$i]['name']; ?></td>
                                <td class="m-col-date"><?php echo $list[$i]['date']; ?></td>
                                <td class="m-col-status">
                                    <?php if ($list[$i]['qa_status']) { ?>
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
                                </td>
                            </tr>
                            <?php } ?>

                            <?php if ($i == 0) { ?>
                            <tr><td colspan="<?php echo $colspan ?>" class="m-empty">
                                <div class="m-qa-empty">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    <p>등록된 문의가 없습니다.</p>
                                </div>
                            </td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="m-pagination"><?php echo $list_pages ?></div>

                <?php if ($is_checkbox) { ?>
                <div class="m-qa-bulk-row">
                    <button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="m-bulk-btn">선택삭제</button>
                </div>
                <?php } ?>
            </form>
        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>

    <?php require G5_THEME_PATH.'/modern/_footer.inc.php'; ?>
</div>

<style>
/* board-common 레이아웃 규칙 — board/basic/list.skin 에만 있던 것을 여기서 재사용 위해 복제 */
.m-board-head {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; margin-bottom: 16px; flex-wrap: wrap;
}
.m-board-head h1 { margin: 0; }
.m-board-actions { display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0; }
.m-board-actions .m-icon-btn { width: 36px; height: 36px; padding: 0; }
.m-board-actions .m-btn { display: inline-flex; align-items: center; }

.m-board-search-drawer {
    margin: 0 0 14px; padding: 14px 16px;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
}
.m-board-search-form {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.m-board-search-form .m-input { flex: 1; min-width: 180px; }

.m-board-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.m-board-table thead th {
    padding: 12px 10px; text-align: center; font-weight: 600;
    font-size: 12px; color: var(--m-text-muted);
    background: var(--m-surface-2); border-bottom: 1px solid var(--m-border);
    text-transform: uppercase; letter-spacing: 0.04em;
}
.m-board-table tbody tr { border-bottom: 1px solid var(--m-border); transition: background 0.1s; }
.m-board-table tbody tr:hover { background: var(--m-surface-2); }
.m-board-table tbody tr:last-child { border-bottom: 0; }
.m-board-table td { padding: 14px 10px; vertical-align: middle; text-align: center; }
.m-board-table thead th.m-col-subject,
.m-board-table tbody td.m-col-subject { text-align: left; padding-left: 16px; }
.m-board-table thead th.m-col-name,
.m-board-table tbody td.m-col-name { text-align: left; }

.m-col-chk { width: 36px; }
.m-col-num { width: 70px; color: var(--m-text-faint); font-size: 13px; }
.m-col-name { width: 110px; color: var(--m-text-soft); font-size: 13px; }
.m-col-date { width: 110px; color: var(--m-text-faint); font-size: 12px; }
.m-board-subject { color: var(--m-text); text-decoration: none; font-weight: 500; }
.m-board-subject:hover { color: var(--m-primary); }
.m-icon-mini { color: var(--m-text-faint); display: inline-flex; vertical-align: -2px; margin-left: 4px; }
.m-empty { padding: 0 !important; }

.m-bulk-btn {
    padding: 6px 12px;
    background: transparent; border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    cursor: pointer;
}
.m-bulk-btn:hover { border-color: var(--m-primary); color: var(--m-primary); }

/* 헤더 — 1:1문의 타이틀과 아이콘이 inline 으로 (UnoCSS reset 의 svg{display:block} 회피) */
.m-qa-head-text { min-width: 0; }
.m-qa-head-title {
    display: flex; align-items: center; gap: 8px;
    margin: 0 0 4px;
    font-size: var(--m-text-2xl); font-weight: 700; color: var(--m-text);
    letter-spacing: -0.01em;
}
.m-qa-head-title svg { color: var(--m-primary); flex-shrink: 0; }
.m-qa-head-sub { margin: 0; font-size: var(--m-text-sm); color: var(--m-text-muted); }
.m-qa-head-sub strong { color: var(--m-text); font-weight: 600; }

/* 빈 상태 — svg 가 display:block 이라 아이콘이 좌측에 박히던 문제 fix (flex column + center) */
.m-qa-empty {
    padding: 60px 20px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.m-qa-empty svg { color: var(--m-text-faint); }
.m-qa-empty p { margin: 0; font-size: var(--m-text-sm); color: var(--m-text-muted); }

/* 카테고리 nav (gnuboard 의 ul/li 마크업을 토큰 pill 로 재스타일) */
.m-qa-cates ul { list-style: none; margin: 0 0 14px; padding: 0; display: flex; flex-wrap: wrap; gap: 6px; }
.m-qa-cates li { margin: 0; }
.m-qa-cates li a {
    display: inline-flex; align-items: center;
    padding: 6px 12px;
    background: var(--m-surface); border: 1px solid var(--m-border);
    border-radius: 999px;
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    text-decoration: none;
    transition: all 0.15s;
}
.m-qa-cates li a:hover { border-color: var(--m-primary); color: var(--m-primary); }
.m-qa-cates li#bo_cate_on a, .m-qa-cates li a#bo_cate_on,
.m-qa-cates li.bo_cate_on a {
    background: var(--m-primary); border-color: var(--m-primary); color: #fff;
}

/* 분류 태그 (목록 row 안) */
.m-qa-cate-tag {
    display: inline-block; padding: 2px 8px; margin-right: 6px;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    border-radius: 999px;
    font-size: var(--m-text-xs); color: var(--m-text-muted);
    vertical-align: 1px;
}

/* 상태 pill */
.m-col-status { width: 100px; }
.m-qa-status {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 999px;
    font-size: var(--m-text-xs); font-weight: 600;
}
.m-qa-status-rdy {
    background: var(--m-surface-2);
    color: var(--m-text-muted);
    border: 1px solid var(--m-border);
}
.m-qa-status-done {
    background: var(--m-primary-soft);
    color: var(--m-primary);
    border: 1px solid var(--m-primary);
}

.m-qa-bulk-row { display: flex; justify-content: flex-end; gap: 6px; margin: 14px 0 0; }

/* 모바일 — 글쓴이 칼럼 숨김 (제목 + 상태 + 등록일만 노출) */
@media (max-width: 720px) {
    .m-qa-table .m-col-name, .m-qa-table .m-col-num { display: none; }
}
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
    var f = document.fqalist;
    for (var i = 0; i < f.length; i++) {
        if (f.elements[i].name == "chk_qa_id[]") f.elements[i].checked = sw;
    }
}
function fqalist_submit(f) {
    var cnt = 0;
    for (var i = 0; i < f.length; i++) {
        if (f.elements[i].name == "chk_qa_id[]" && f.elements[i].checked) cnt++;
    }
    if (!cnt) { alert(document.pressed + "할 게시물을 하나 이상 선택하세요."); return false; }
    if (document.pressed == "선택삭제" && !confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다")) return false;
    return true;
}
<?php } ?>
</script>
<!-- } 1:1 문의 목록 끝 -->
