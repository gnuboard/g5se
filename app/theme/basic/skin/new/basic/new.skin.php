<?php
if (!defined("_GNUBOARD_")) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 전체게시물 시작 { -->
<div class="m-shell">

    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">

            <!-- 검색 폼 -->
            <section class="m-card m-new-form-card">
                <h1 class="m-new-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    전체 게시물
                </h1>

                <form name="fnew" method="get" class="m-new-search-form">
                    <div class="m-new-search-row">
                        <?php echo $group_select ?>

                        <select name="view" id="view" class="m-input m-new-select">
                            <option value="">전체게시물</option>
                            <option value="w">원글만</option>
                            <option value="c">댓글만</option>
                        </select>

                        <input type="text" name="mb_id" value="<?php echo $mb_id ?>" id="mb_id" required class="m-input m-new-input" placeholder="회원 아이디 검색">

                        <button type="submit" class="m-btn m-new-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            검색
                        </button>
                    </div>
                    <p class="m-new-hint">회원 아이디로만 검색할 수 있습니다.</p>
                </form>

                <script>
                document.getElementById("gr_id").value = "<?php echo $gr_id ?>";
                document.getElementById("view").value = "<?php echo $view ?>";
                </script>
            </section>

            <!-- 목록 -->
            <form name="fnewlist" id="fnewlist" method="post" action="#" onsubmit="return fnew_submit(this);">
                <input type="hidden" name="sw"       value="move">
                <input type="hidden" name="view"     value="<?php echo $view; ?>">
                <input type="hidden" name="sfl"      value="<?php echo $sfl; ?>">
                <input type="hidden" name="stx"      value="<?php echo $stx; ?>">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
                <input type="hidden" name="page"     value="<?php echo $page; ?>">
                <input type="hidden" name="pressed"  value="">

                <?php if ($is_admin && count($list)) { ?>
                <div class="m-new-bulk-row">
                    <button type="submit" onclick="document.pressed=this.title" title="선택삭제" class="m-bulk-btn">선택삭제</button>
                </div>
                <?php } ?>

                <section class="m-card m-new-list-card">
                    <div class="m-new-list-wrap">
                        <table class="m-new-list">
                            <thead>
                                <tr>
                                    <?php if ($is_admin) { ?>
                                    <th scope="col" class="m-new-col-chk">
                                        <input type="checkbox" id="all_chk">
                                    </th>
                                    <?php } ?>
                                    <th scope="col" class="m-new-col-group">그룹</th>
                                    <th scope="col" class="m-new-col-board">게시판</th>
                                    <th scope="col" class="m-new-col-subject">제목</th>
                                    <th scope="col" class="m-new-col-name">이름</th>
                                    <th scope="col" class="m-new-col-date">일시</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                for ($i=0; $i<count($list); $i++) {
                                    $num = $total_count - ($page - 1) * $config['cf_page_rows'] - $i;
                                    $gr_subject = cut_str($list[$i]['gr_subject'], 20);
                                    $bo_subject = cut_str($list[$i]['bo_subject'], 20);
                                    $wr_subject = get_text(cut_str($list[$i]['wr_subject'], 80));
                                ?>
                                <tr>
                                    <?php if ($is_admin) { ?>
                                    <td class="m-new-col-chk">
                                        <input type="checkbox" name="chk_bn_id[]" value="<?php echo $i; ?>" id="chk_bn_id_<?php echo $i; ?>">
                                        <input type="hidden" name="bo_table[<?php echo $i; ?>]" value="<?php echo $list[$i]['bo_table']; ?>">
                                        <input type="hidden" name="wr_id[<?php echo $i; ?>]" value="<?php echo $list[$i]['wr_id']; ?>">
                                    </td>
                                    <?php } ?>
                                    <td class="m-new-col-group"><a href="/new?gr_id=<?php echo $list[$i]['gr_id'] ?>"><?php echo $gr_subject ?></a></td>
                                    <td class="m-new-col-board"><a href="<?php echo get_pretty_url($list[$i]['bo_table']); ?>"><?php echo $bo_subject ?></a></td>
                                    <td class="m-new-col-subject">
                                        <a href="<?php echo $list[$i]['href'] ?>" class="m-new-subject-link">
                                            <?php if ($list[$i]['comment']) { ?>
                                            <span class="m-new-cmt-mark">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                            </span>
                                            <?php } ?>
                                            <?php echo $wr_subject ?>
                                        </a>
                                    </td>
                                    <td class="m-new-col-name"><?php echo $list[$i]['name'] ?></td>
                                    <td class="m-new-col-date"><?php echo $list[$i]['datetime2'] ?></td>
                                </tr>
                                <?php } ?>

                                <?php if ($i == 0) { ?>
                                <tr>
                                    <td colspan="<?php echo $is_admin ? 6 : 5 ?>" class="m-new-empty">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <p>게시물이 없습니다.</p>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <?php echo $write_pages ?>

                <?php if ($is_admin && count($list)) { ?>
                <div class="m-new-bulk-row m-new-bulk-bottom">
                    <button type="submit" onclick="document.pressed=this.title" title="선택삭제" class="m-bulk-btn">선택삭제</button>
                </div>
                <?php } ?>
            </form>

            <?php if ($is_admin) { ?>
            <script>
            $(function(){
                $('#all_chk').click(function(){
                    $('[name="chk_bn_id[]"]').prop('checked', this.checked);
                });
            });
            function fnew_submit(f) {
                f.pressed.value = document.pressed;
                var cnt = 0;
                for (var i=0; i<f.length; i++)
                    if (f.elements[i].name == "chk_bn_id[]" && f.elements[i].checked) cnt++;
                if (!cnt) { alert(document.pressed+"할 게시물을 하나 이상 선택하세요."); return false; }
                if (!confirm("선택한 게시물을 정말 "+document.pressed+" 하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다")) return false;
                f.action = "/bbs/new_delete.php";
                return true;
            }
            </script>
            <?php } ?>

        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>

    <?php require G5_THEME_PATH.'/modern/_footer.inc.php'; ?>
</div>

<style>
.m-new-form-card { padding: 22px 24px; margin-bottom: 18px; }
.m-new-title {
    display: flex; align-items: center; gap: 8px;
    font-size: var(--m-text-xl); font-weight: 700;
    color: var(--m-text); margin: 0 0 14px;
}
.m-new-title svg { color: var(--m-primary); }
.m-new-search-form { margin: 0; }
.m-new-search-row {
    display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
}
.m-new-search-row select { width: auto; min-width: 110px; }
.m-new-input { flex: 1; min-width: 180px; }
.m-new-select { flex: 0 0 auto; }
.m-new-btn {
    display: inline-flex; align-items: center; gap: 6px;
    width: auto; padding: 10px 18px;
    flex-shrink: 0;
}
.m-new-hint { margin: 10px 0 0; font-size: var(--m-text-sm); color: var(--m-text-muted); }

.m-new-list-card { padding: 0; overflow: hidden; }
.m-new-list-wrap { overflow-x: auto; }
.m-new-list {
    width: 100%; border-collapse: collapse;
    font-size: var(--m-text-sm); color: var(--m-text);
}
.m-new-list th {
    padding: 12px 14px; text-align: left;
    font-size: var(--m-text-xs); font-weight: 600; color: var(--m-text-muted);
    border-bottom: 1px solid var(--m-border); background: var(--m-surface-2);
    white-space: nowrap;
}
.m-new-list td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--m-border);
    vertical-align: middle;
}
.m-new-list tbody tr:last-child td { border-bottom: 0; }
.m-new-list tbody tr:hover td { background: var(--m-surface-2); }

.m-new-col-chk { width: 36px; text-align: center; }
.m-new-col-chk input { accent-color: var(--m-primary); }
.m-new-col-group, .m-new-col-board { width: 110px; white-space: nowrap; }
.m-new-col-group a, .m-new-col-board a {
    color: var(--m-text-soft); text-decoration: none;
    font-size: var(--m-text-xs);
    padding: 3px 8px; border-radius: 999px;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    display: inline-block;
}
.m-new-col-group a:hover, .m-new-col-board a:hover {
    color: var(--m-primary); border-color: var(--m-primary);
}
.m-new-col-subject { min-width: 240px; }
.m-new-subject-link {
    display: inline-flex; align-items: center; gap: 6px;
    color: var(--m-text); text-decoration: none;
}
.m-new-subject-link:hover { color: var(--m-primary); }
.m-new-cmt-mark { color: var(--m-text-faint); display: inline-flex; }
.m-new-col-name { width: 110px; color: var(--m-text-soft); }
.m-new-col-date { width: 120px; color: var(--m-text-muted); white-space: nowrap; }

.m-new-empty {
    padding: 60px 20px !important; text-align: center !important;
}
.m-new-empty svg { color: var(--m-text-faint); margin-bottom: 8px; display: inline-block; }
.m-new-empty p { margin: 0; color: var(--m-text-muted); }

.m-new-bulk-row {
    display: flex; justify-content: flex-end; gap: 6px;
    margin-bottom: 8px;
}
.m-new-bulk-bottom { margin: 12px 0 0; }

@media (max-width: 720px) {
    .m-new-col-group, .m-new-col-board, .m-new-col-name { display: none; }
    .m-new-list th.m-new-col-group, .m-new-list th.m-new-col-board, .m-new-list th.m-new-col-name { display: none; }
}
</style>
<!-- } 전체게시물 끝 -->
