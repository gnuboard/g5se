<?php
if (!defined("_GNUBOARD_")) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 전체검색 시작 { -->
<div class="m-shell">

    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">

            <!-- 검색 폼 -->
            <section class="m-card m-search-form-card">
                <h1 class="m-search-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    전체 검색
                </h1>

                <form name="fsearch" onsubmit="return fsearch_submit(this);" method="get" class="m-search-form">
                    <input type="hidden" name="srows" value="<?php echo $srows ?>">

                    <div class="m-search-row">
                        <?php echo $group_select ?>
                        <script>document.getElementById("gr_id").value = "<?php echo $gr_id ?>";</script>

                        <select name="sfl" id="sfl" class="m-input m-search-select">
                            <option value="wr_subject||wr_content"<?php echo get_selected($sfl, "wr_subject||wr_content") ?>>제목+내용</option>
                            <option value="wr_subject"<?php echo get_selected($sfl, "wr_subject") ?>>제목</option>
                            <option value="wr_content"<?php echo get_selected($sfl, "wr_content") ?>>내용</option>
                            <option value="mb_id"<?php echo get_selected($sfl, "mb_id") ?>>회원아이디</option>
                            <option value="wr_name"<?php echo get_selected($sfl, "wr_name") ?>>이름</option>
                        </select>

                        <input type="text" name="stx" value="<?php echo $text_stx ?>" id="stx" required class="m-input m-search-input" placeholder="검색어를 입력하세요 (2자 이상)">

                        <button type="submit" class="m-btn m-search-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            검색
                        </button>
                    </div>

                    <div class="m-search-options">
                        <label class="m-search-radio">
                            <input type="radio" value="and" <?php echo ($sop == "and") ? "checked" : ""; ?> id="sop_and" name="sop">
                            <span>AND</span>
                        </label>
                        <label class="m-search-radio">
                            <input type="radio" value="or" <?php echo ($sop == "or") ? "checked" : ""; ?> id="sop_or" name="sop">
                            <span>OR</span>
                        </label>
                    </div>
                </form>

                <script>
                function fsearch_submit(f) {
                    var stx = f.stx.value.trim();
                    if (stx.length < 2) { alert("검색어는 두글자 이상 입력하십시오."); f.stx.select(); f.stx.focus(); return false; }
                    var cnt = 0; for (var i = 0; i < stx.length; i++) if (stx.charAt(i) == ' ') cnt++;
                    if (cnt > 1) { alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다."); f.stx.select(); f.stx.focus(); return false; }
                    f.stx.value = stx;
                    f.action = "";
                    return true;
                }
                </script>
            </section>

            <!-- 검색 결과 -->
            <?php if ($stx) { ?>
                <?php if ($board_count) { ?>
                <!-- 결과 요약 -->
                <section class="m-card m-search-summary">
                    <p>
                        <strong><?php echo $stx ?></strong> 전체검색 결과
                    </p>
                    <ul class="m-search-summary-stats">
                        <li>게시판 <strong><?php echo $board_count ?></strong>개</li>
                        <li>게시물 <strong><?php echo number_format($total_count) ?></strong>개</li>
                        <li><strong><?php echo number_format($page) ?></strong>/<?php echo number_format($total_page) ?> 페이지</li>
                    </ul>
                </section>

                <!-- 게시판 필터 -->
                <nav class="m-search-board-filter">
                    <a href="?<?php echo $search_query ?>&amp;gr_id=<?php echo $gr_id ?>" <?php echo $sch_all ?>>전체게시판</a>
                    <?php echo $str_board_list; ?>
                </nav>

                <!-- 결과 목록 -->
                <?php
                $k=0;
                for ($idx=$table_index; $idx<count($search_table) && $k<$rows; $idx++) {
                ?>
                <section class="m-card m-search-result-card">
                    <header class="m-search-result-head">
                        <h2 class="m-search-result-title">
                            <a href="<?php echo get_pretty_url($search_table[$idx], '', $search_query); ?>"><?php echo $bo_subject[$idx] ?></a>
                            <span class="m-search-result-suffix">게시판 내 결과</span>
                        </h2>
                        <a href="<?php echo get_pretty_url($search_table[$idx], '', $search_query); ?>" class="m-search-more">
                            더보기
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </header>

                    <ul class="m-search-result-list">
                        <?php
                        for ($i=0; $i<count($list[$idx]) && $k<$rows; $i++, $k++) {
                            $is_cmt = $list[$idx][$i]['wr_is_comment'];
                            $cmt_href = $is_cmt ? '#c_'.$list[$idx][$i]['wr_id'] : '';
                        ?>
                        <li class="m-search-item">
                            <div class="m-search-item-head">
                                <a href="<?php echo $list[$idx][$i]['href'].$cmt_href ?>" class="m-search-item-title">
                                    <?php if ($is_cmt) { ?>
                                    <svg class="m-search-item-cmt" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    <?php } ?>
                                    <?php echo $list[$idx][$i]['subject'] ?>
                                </a>
                                <a href="<?php echo $list[$idx][$i]['href'].$cmt_href ?>" target="_blank" class="m-search-item-popup" title="새창으로 열기">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>
                            </div>
                            <p class="m-search-item-excerpt"><?php echo $list[$idx][$i]['content'] ?></p>
                            <div class="m-search-item-meta">
                                <span class="m-search-item-name"><?php echo $list[$idx][$i]['name'] ?></span>
                                <span class="m-search-item-sep">·</span>
                                <span class="m-search-item-time">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <?php echo $list[$idx][$i]['wr_datetime'] ?>
                                </span>
                            </div>
                        </li>
                        <?php } ?>
                    </ul>
                </section>
                <?php } ?>

                <div class="m-pagination"><?php echo $write_pages ?></div>

                <?php } else { ?>
                <section class="m-card m-search-empty">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <p><strong><?php echo $stx ?></strong> 에 대한 검색 결과가 없습니다.</p>
                    <p class="m-search-empty-hint">다른 검색어를 입력하거나 검색조건을 바꿔보세요.</p>
                </section>
                <?php } ?>

            <?php } ?>

        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>

    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>

<style>
.m-search-form-card { padding: 22px 24px; margin-bottom: 18px; }
.m-search-title {
    display: flex; align-items: center; gap: 8px;
    font-size: var(--m-text-xl); font-weight: 700;
    color: var(--m-text); margin: 0 0 14px;
}
.m-search-title svg { color: var(--m-primary); }
.m-search-form { margin: 0; }
.m-search-row {
    display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
}
/* gnuboard 가 만드는 #gr_id select 도 모던 .m-input 스타일로 — 클래스를 못 박아서 id 로 매김 */
.m-search-row #gr_id,
.m-search-row select { width: auto; min-width: 110px; }
.m-search-row #gr_id {
    padding: 10px 12px; box-sizing: border-box;
    background: var(--m-surface); color: var(--m-text);
    border: 1px solid var(--m-border-hover); border-radius: var(--m-radius);
    font-size: var(--m-text-md); font-family: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
    outline: none;
}
.m-search-row #gr_id:focus {
    border-color: var(--m-primary);
    box-shadow: 0 0 0 3px var(--m-primary-soft);
}
.m-search-input { flex: 1; min-width: 200px; }
.m-search-select { flex: 0 0 auto; }
.m-search-btn {
    display: inline-flex; align-items: center; gap: 6px;
    width: auto; padding: 10px 18px;
    flex-shrink: 0;
}
.m-search-options {
    display: flex; gap: 14px; margin-top: 12px;
    font-size: var(--m-text-sm); color: var(--m-text-muted);
}
.m-search-radio { display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
.m-search-radio input { accent-color: var(--m-primary); }

.m-search-summary {
    padding: 16px 22px; margin-bottom: 14px;
    display: flex; flex-wrap: wrap; align-items: center; gap: 16px;
}
.m-search-summary p { margin: 0; font-size: var(--m-text-md); color: var(--m-text); }
.m-search-summary p strong { color: var(--m-primary); }
.m-search-summary-stats {
    list-style: none; margin: 0; padding: 0;
    display: flex; gap: 14px; flex-wrap: wrap;
    font-size: var(--m-text-sm); color: var(--m-text-muted);
}
.m-search-summary-stats li strong { color: var(--m-text); font-weight: 600; }

.m-search-board-filter {
    display: flex; flex-wrap: wrap; gap: 6px;
    margin-bottom: 14px;
}
.m-search-board-filter a {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px;
    background: var(--m-surface); border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    text-decoration: none; transition: all 0.15s;
}
.m-search-board-filter a:hover {
    border-color: var(--m-primary); color: var(--m-primary);
    background: var(--m-primary-soft);
}
.m-search-board-filter a.sch_on,
.m-search-board-filter a[class*="sch_on"] {
    background: var(--m-primary); border-color: var(--m-primary); color: #fff;
}
/* 게시판별 결과 갯수 뱃지 (gnuboard 가 <span class="cnt_cmt">N</span> 으로 출력) */
.m-search-board-filter .cnt_cmt {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 18px; padding: 1px 6px;
    background: var(--m-surface-2); color: var(--m-text-muted);
    border-radius: 999px;
    font-size: 10px; font-weight: 700;
    font-feature-settings: "tnum";
}
.m-search-board-filter a:hover .cnt_cmt { background: var(--m-surface); color: var(--m-primary); }
.m-search-board-filter a.sch_on .cnt_cmt,
.m-search-board-filter a[class*="sch_on"] .cnt_cmt {
    background: rgba(255,255,255,0.25); color: #fff;
}

.m-search-result-card { padding: 18px 22px; margin-bottom: 14px; }
.m-search-result-head {
    display: flex; align-items: baseline; justify-content: space-between;
    gap: 12px; margin-bottom: 12px;
    padding-bottom: 10px; border-bottom: 1px solid var(--m-border);
}
.m-search-result-title { margin: 0; font-size: var(--m-text-lg); font-weight: 600; }
.m-search-result-title a { color: var(--m-text); text-decoration: none; }
.m-search-result-title a:hover { color: var(--m-primary); }
.m-search-result-suffix { font-size: var(--m-text-sm); color: var(--m-text-muted); font-weight: 400; }
.m-search-more {
    display: inline-flex; align-items: center; gap: 4px;
    flex-shrink: 0; font-size: var(--m-text-sm);
    color: var(--m-text-muted); text-decoration: none;
}
.m-search-more:hover { color: var(--m-primary); }

.m-search-result-list { list-style: none; margin: 0; padding: 0; }
.m-search-item {
    padding: 12px 0;
    border-bottom: 1px dashed var(--m-border);
}
.m-search-item:last-child { border-bottom: 0; }
.m-search-item-head {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 4px;
}
.m-search-item-title {
    flex: 1; min-width: 0;
    display: inline-flex; align-items: center; gap: 6px;
    font-size: var(--m-text-md); font-weight: 500;
    color: var(--m-text); text-decoration: none;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-search-item-title:hover { color: var(--m-primary); }
.m-search-item-cmt { color: var(--m-text-faint); flex-shrink: 0; }
.m-search-item-popup {
    color: var(--m-text-faint); display: inline-flex;
    padding: 4px; border-radius: var(--m-radius-sm);
    flex-shrink: 0;
}
.m-search-item-popup:hover { color: var(--m-primary); background: var(--m-primary-soft); }
.m-search-item-excerpt {
    margin: 0 0 6px; font-size: var(--m-text-sm); color: var(--m-text-soft);
    line-height: var(--m-leading);
    overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
.m-search-item-excerpt em, .m-search-item-title em {
    font-style: normal; background: var(--m-primary-soft); color: var(--m-primary);
    padding: 0 2px; border-radius: 2px;
}
.m-search-item-meta {
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    font-size: var(--m-text-xs); color: var(--m-text-muted);
}
.m-search-item-sep { color: var(--m-text-faint); }
.m-search-item-time { display: inline-flex; align-items: center; gap: 4px; }

.m-search-empty {
    padding: 60px 24px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.m-search-empty svg { color: var(--m-text-faint); margin-bottom: 8px; }
.m-search-empty p { margin: 0; color: var(--m-text-soft); font-size: var(--m-text-md); }
.m-search-empty p strong { color: var(--m-text); font-weight: 600; }
.m-search-empty-hint { font-size: var(--m-text-sm) !important; color: var(--m-text-muted) !important; }
</style>
<!-- } 전체검색 끝 -->
