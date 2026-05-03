<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- FAQ 시작 { -->
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">

            <?php if ($himg_src) { ?>
            <div class="m-faq-himg"><img src="<?php echo $himg_src ?>" alt=""></div>
            <?php } ?>

            <?php if (trim((string) $fm['fm_head_html']) !== '') { ?>
            <div class="m-faq-html"><?php echo conv_content($fm['fm_head_html'], 1) ?></div>
            <?php } ?>

            <header class="m-faq-head">
                <h1 class="m-faq-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <?php echo get_text($fm['fm_subject']) ?>
                </h1>
                <p class="m-faq-sub">총 <strong><?php echo number_format($total_count) ?></strong>개의 FAQ</p>
            </header>

            <form name="faq_search_form" method="get" class="m-faq-search">
                <input type="hidden" name="fm_id" value="<?php echo $fm_id ?>">
                <svg class="m-faq-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="stx" value="<?php echo get_text($stx) ?>" id="stx" required class="m-input m-faq-search-input" maxlength="60" placeholder="궁금한 내용을 검색해 보세요">
                <button type="submit" class="m-btn m-faq-search-btn">검색</button>
            </form>

            <?php if (count($faq_master_list)) { ?>
            <nav class="m-faq-cates">
                <?php foreach ($faq_master_list as $v) { ?>
                <a href="<?php echo $category_href ?>?fm_id=<?php echo $v['fm_id'] ?>"
                   class="m-faq-cate<?php echo $v['fm_id'] == $fm_id ? ' is-active' : '' ?>">
                    <?php echo get_text($v['fm_subject']) ?>
                </a>
                <?php } ?>
            </nav>
            <?php } ?>

            <?php if (count($faq_list)) { ?>
            <ol class="m-faq-list">
                <?php foreach ($faq_list as $v) { if (empty($v)) continue; ?>
                <li class="m-faq-item">
                    <button type="button" class="m-faq-q" onclick="faq_open(this); return false;">
                        <span class="m-faq-q-mark">Q</span>
                        <span class="m-faq-q-text"><?php echo conv_content($v['fa_subject'], 1) ?></span>
                        <svg class="m-faq-chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="m-faq-a" hidden>
                        <span class="m-faq-a-mark">A</span>
                        <div class="m-faq-a-text"><?php echo conv_content($v['fa_content'], 1) ?></div>
                    </div>
                </li>
                <?php } ?>
            </ol>
            <?php } else { ?>
            <div class="m-card m-faq-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <?php if ($stx) { ?>
                <p><strong><?php echo get_text($stx) ?></strong> 에 대한 검색 결과가 없습니다.</p>
                <?php } else { ?>
                <p>등록된 FAQ 가 없습니다.</p>
                <?php if ($is_admin) { ?>
                <p class="m-faq-empty-admin">
                    <a href="<?php echo G5_ADMIN_URL ?>/faqmasterlist.php">FAQ 관리 메뉴</a> 에서 새로 등록할 수 있습니다.
                </p>
                <?php } ?>
                <?php } ?>
            </div>
            <?php } ?>

            <div class="m-pagination">
                <?php echo get_paging($page_rows, $page, $total_page, '/faq?'.$qstr.'&amp;page='); ?>
            </div>

            <?php if (trim((string) $fm['fm_tail_html']) !== '') { ?>
            <div class="m-faq-html"><?php echo conv_content($fm['fm_tail_html'], 1) ?></div>
            <?php } ?>

            <?php if ($timg_src) { ?>
            <div class="m-faq-timg"><img src="<?php echo $timg_src ?>" alt=""></div>
            <?php } ?>

            <?php if ($admin_href) { ?>
            <a href="<?php echo $admin_href ?>" class="m-faq-admin" title="FAQ 수정">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                FAQ 수정
            </a>
            <?php } ?>

        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>

    <?php require G5_THEME_PATH.'/modern/_footer.inc.php'; ?>
</div>

<style>
.m-faq-head { margin-bottom: 14px; }
.m-faq-title {
    display: flex; align-items: center; gap: 8px;
    margin: 0 0 4px;
    font-size: var(--m-text-2xl); font-weight: 700;
    color: var(--m-text); letter-spacing: -0.01em;
}
.m-faq-title svg { color: var(--m-primary); flex-shrink: 0; }
.m-faq-sub { margin: 0; font-size: var(--m-text-sm); color: var(--m-text-muted); }
.m-faq-sub strong { color: var(--m-text); font-weight: 600; }

.m-faq-himg, .m-faq-timg { margin: 0 0 14px; text-align: center; }
.m-faq-himg img, .m-faq-timg img { max-width: 100%; border-radius: var(--m-radius); }
.m-faq-html { margin: 0 0 14px; color: var(--m-text-soft); font-size: var(--m-text-sm); }

.m-faq-search {
    display: flex; align-items: center; gap: 6px;
    background: var(--m-surface);
    border: 1px solid var(--m-border-hover);
    border-radius: var(--m-radius);
    padding: 4px 4px 4px 12px;
    margin: 0 0 16px;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.m-faq-search:focus-within {
    border-color: var(--m-primary);
    box-shadow: 0 0 0 3px var(--m-primary-soft);
}
.m-faq-search-icon { color: var(--m-text-faint); flex-shrink: 0; }
.m-faq-search-input {
    flex: 1; min-width: 0;
    border: 0 !important; padding: 8px 0 !important;
    background: transparent !important; box-shadow: none !important;
    font-size: var(--m-text-md);
}
.m-faq-search-input:focus { border: 0 !important; box-shadow: none !important; }
.m-faq-search-btn { width: auto !important; padding: 8px 16px; flex-shrink: 0; }

.m-faq-cates {
    display: flex; flex-wrap: wrap; gap: 6px;
    margin: 0 0 16px;
}
.m-faq-cate {
    display: inline-flex; align-items: center;
    padding: 7px 14px;
    background: var(--m-surface); border: 1px solid var(--m-border);
    border-radius: 999px;
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    text-decoration: none;
    transition: all 0.15s;
}
.m-faq-cate:hover { border-color: var(--m-primary); color: var(--m-primary); }
.m-faq-cate.is-active {
    background: var(--m-primary); border-color: var(--m-primary); color: #fff;
}

.m-faq-list {
    list-style: none; margin: 0 0 18px; padding: 0;
    display: flex; flex-direction: column; gap: 6px;
    counter-reset: faq;
}
.m-faq-item {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    overflow: hidden;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.m-faq-item.is-open { border-color: var(--m-primary); box-shadow: var(--m-shadow); }

.m-faq-q {
    display: flex; align-items: center; gap: 12px;
    width: 100%; padding: 14px 16px;
    background: transparent; border: 0; text-align: left; cursor: pointer;
    font-family: inherit;
}
.m-faq-q:hover { background: var(--m-surface-2); }
.m-faq-q-mark, .m-faq-a-mark {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; flex-shrink: 0;
    border-radius: 50%; font-weight: 700; font-size: var(--m-text-sm);
}
.m-faq-q-mark { background: var(--m-primary-soft); color: var(--m-primary); }
.m-faq-a-mark { background: var(--m-surface-2); color: var(--m-text-soft); }
.m-faq-q-text {
    flex: 1; min-width: 0;
    font-size: var(--m-text-md); font-weight: 500; color: var(--m-text);
    line-height: var(--m-leading);
}
.m-faq-chev {
    color: var(--m-text-faint); flex-shrink: 0;
    transition: transform 0.2s, color 0.15s;
}
.m-faq-item.is-open .m-faq-chev { transform: rotate(180deg); color: var(--m-primary); }

.m-faq-a {
    display: flex; gap: 12px;
    padding: 14px 16px;
    border-top: 1px solid var(--m-border);
    background: var(--m-surface-2);
}
/* class 가 display:flex 라서 [hidden] (=display:none) 이 덮어써짐 — 명시적으로 강제 */
.m-faq-a[hidden] { display: none !important; }
.m-faq-a-text {
    flex: 1; min-width: 0;
    font-size: var(--m-text-md); line-height: var(--m-leading-relaxed);
    color: var(--m-text-soft); word-break: break-word;
}
.m-faq-a-text img { max-width: 100%; height: auto; }

.m-faq-empty {
    padding: 60px 24px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.m-faq-empty svg { color: var(--m-text-faint); margin-bottom: 4px; }
.m-faq-empty p { margin: 0; color: var(--m-text-muted); font-size: var(--m-text-md); }
.m-faq-empty p strong { color: var(--m-text); font-weight: 600; }
.m-faq-empty-admin { font-size: var(--m-text-sm) !important; }
.m-faq-empty-admin a { color: var(--m-primary); text-decoration: none; }
.m-faq-empty-admin a:hover { text-decoration: underline; }

.m-faq-admin {
    display: inline-flex; align-items: center; gap: 6px;
    margin: 18px 0 0;
    padding: 8px 14px;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    text-decoration: none;
}
.m-faq-admin:hover { border-color: var(--m-primary); color: var(--m-primary); }

/* highlight (search_font) — gnuboard 가 strong 으로 감싸 출력 */
.m-faq-q-text strong, .m-faq-a-text strong {
    background: var(--m-primary-soft); color: var(--m-primary);
    padding: 0 2px; border-radius: 2px; font-weight: 700;
}
</style>

<script src="<?php echo G5_JS_URL ?>/viewimageresize.js"></script>
<script>
function faq_open(btn) {
    var item = btn.closest('.m-faq-item');
    var ans  = item.querySelector('.m-faq-a');
    var open = !ans.hasAttribute('hidden');
    // 다른 항목 모두 닫기 (한 번에 하나만 열림)
    document.querySelectorAll('.m-faq-item.is-open').forEach(function(el){
        if (el !== item) {
            el.classList.remove('is-open');
            var a = el.querySelector('.m-faq-a');
            if (a) a.setAttribute('hidden', '');
        }
    });
    if (open) {
        ans.setAttribute('hidden', '');
        item.classList.remove('is-open');
    } else {
        ans.removeAttribute('hidden');
        item.classList.add('is-open');
        if (window.jQuery && jQuery(ans).viewimageresize2) jQuery(ans).viewimageresize2();
    }
    return false;
}
</script>
<!-- } FAQ 끝 -->
