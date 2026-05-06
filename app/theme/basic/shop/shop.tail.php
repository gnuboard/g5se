<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MSHOP_PATH.'/shop.tail.php');
    return;
}

$admin = get_admin("super");
$is_index = defined('_INDEX_') && _INDEX_;
?>
            </div><!-- .shop-content 끝 -->
        </div><!-- .m-main-col 끝 -->

        <?php if ($is_index) { ?>
        <aside class="m-side-col" style="display: flex; flex-direction: column; gap: 16px;">
            <!-- 카테고리 -->
            <section class="m-card" style="padding: 16px;">
                <h2 style="font-size: var(--m-text-md); margin-bottom: 10px; color: var(--m-text);">카테고리</h2>
                <?php include_once(G5_SHOP_SKIN_PATH.'/boxcategory.skin.php'); ?>
            </section>

            <?php if($default['de_type4_list_use']) { ?>
            <section class="m-card" style="padding: 16px;">
                <h2 style="font-size: var(--m-text-md); margin-bottom: 10px;">
                    <a href="<?php echo shop_type_url('4'); ?>" style="color: var(--m-text); text-decoration: none;">인기상품 →</a>
                </h2>
                <?php
                $list = new item_list();
                $list->set_type(4);
                $list->set_view('it_id', false);
                $list->set_view('it_name', true);
                $list->set_view('it_basic', false);
                $list->set_view('it_cust_price', false);
                $list->set_view('it_price', true);
                $list->set_view('it_icon', false);
                $list->set_view('sns', false);
                $list->set_view('star', true);
                echo $list->run();
                ?>
            </section>
            <?php } ?>

            <?php $_banner = display_banner('왼쪽', 'boxbanner.skin.php');
                  if (trim((string)$_banner) !== '') { ?>
                <section class="m-card" style="padding: 12px;"><?php echo $_banner; ?></section>
            <?php } ?>

            <section class="m-card" style="padding: 16px;">
                <?php echo poll('theme/shop_basic'); // 설문조사 ?>
            </section>
        </aside>
        <?php } ?>
    </main>

    <!-- 푸터 -->
    <footer class="m-footer" style="margin-top: auto; padding: 32px 0 24px; border-top: 1px solid var(--m-border); background: var(--m-surface);">
        <div class="m-container" style="padding: 0 20px;">
            <ul style="display: flex; flex-wrap: wrap; align-items: center; gap: 14px; padding: 0; margin: 0 0 18px; list-style: none; font-size: var(--m-text-base);">
                <li><a href="<?php echo get_pretty_url('content', 'company'); ?>" style="color: var(--m-text-soft); text-decoration: none;">회사소개</a></li>
                <li style="color: var(--m-text-faint);">·</li>
                <li><a href="<?php echo get_pretty_url('content', 'provision'); ?>" style="color: var(--m-text-soft); text-decoration: none;">서비스이용약관</a></li>
                <li style="color: var(--m-text-faint);">·</li>
                <li><a href="<?php echo get_pretty_url('content', 'privacy'); ?>" style="color: var(--m-text); font-weight: 600; text-decoration: none;">개인정보처리방침</a></li>
                <li style="color: var(--m-text-faint);">·</li>
                <li><a href="<?php echo get_device_change_url(); ?>" style="color: var(--m-text-soft); text-decoration: none;">모바일버전</a></li>
            </ul>

            <div style="display: grid; gap: 24px; grid-template-columns: 2fr 1fr 1fr;">
                <div>
                    <h2 style="font-size: var(--m-text-base); font-weight: 700; color: var(--m-text); margin: 0 0 10px;">사이트 정보</h2>
                    <dl style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px 18px; margin: 0; font-size: var(--m-text-sm); color: var(--m-text-muted); line-height: 1.6;">
                        <div><dt style="display: inline; font-weight: 600; color: var(--m-text-soft);">회사명</dt> <dd style="display: inline; margin: 0;"><?php echo $default['de_admin_company_name']; ?></dd></div>
                        <div><dt style="display: inline; font-weight: 600; color: var(--m-text-soft);">대표</dt> <dd style="display: inline; margin: 0;"><?php echo $default['de_admin_company_owner']; ?></dd></div>
                        <div style="grid-column: 1/3;"><dt style="display: inline; font-weight: 600; color: var(--m-text-soft);">주소</dt> <dd style="display: inline; margin: 0;"><?php echo $default['de_admin_company_addr']; ?></dd></div>
                        <div><dt style="display: inline; font-weight: 600; color: var(--m-text-soft);">사업자번호</dt> <dd style="display: inline; margin: 0;"><?php echo $default['de_admin_company_saupja_no']; ?></dd></div>
                        <div><dt style="display: inline; font-weight: 600; color: var(--m-text-soft);">통신판매번호</dt> <dd style="display: inline; margin: 0;"><?php echo $default['de_admin_tongsin_no']; ?></dd></div>
                        <div><dt style="display: inline; font-weight: 600; color: var(--m-text-soft);">전화</dt> <dd style="display: inline; margin: 0;"><?php echo $default['de_admin_company_tel']; ?></dd></div>
                        <div><dt style="display: inline; font-weight: 600; color: var(--m-text-soft);">팩스</dt> <dd style="display: inline; margin: 0;"><?php echo $default['de_admin_company_fax']; ?></dd></div>
                        <div style="grid-column: 1/3;"><dt style="display: inline; font-weight: 600; color: var(--m-text-soft);">개인정보 보호책임자</dt> <dd style="display: inline; margin: 0;"><?php echo $default['de_admin_info_name']; ?></dd></div>
                    </dl>
                </div>
                <section class="m-card" style="padding: 14px;"><?php echo latest('theme/notice', 'notice', 5, 30); ?></section>
                <section class="m-card" style="padding: 14px;"><?php echo visit('theme/shop_basic'); ?></section>
            </div>

            <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--m-border); text-align: center; font-size: var(--m-text-xs); color: var(--m-text-faint);">
                Copyright &copy; 2001-<?php echo date('Y'); ?> <?php echo $default['de_admin_company_name']; ?>. All Rights Reserved.
            </div>
        </div>
    </footer>

    <!-- 우측 fixed quick action — 장바구니/위시/최근본/위로 -->
    <aside class="m-shop-quick" aria-label="빠른 메뉴">
        <a href="<?php echo G5_SHOP_URL; ?>/cart.php" class="m-shop-quick-btn" title="장바구니">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <span class="m-shop-quick-label">장바구니</span>
            <?php if (function_exists('get_boxcart_datas_count')) { $_qcnt = (int) get_boxcart_datas_count(); if ($_qcnt > 0) { ?>
                <span class="m-shop-quick-badge"><?php echo $_qcnt > 99 ? '99+' : $_qcnt; ?></span>
            <?php }} ?>
        </a>
        <a href="<?php echo G5_SHOP_URL; ?>/wishlist.php" class="m-shop-quick-btn" title="위시리스트">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span class="m-shop-quick-label">위시</span>
        </a>
        <button type="button" class="m-shop-quick-btn js-shop-quick-today" title="최근 본 상품" aria-expanded="false" aria-controls="m-shop-today-panel">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span class="m-shop-quick-label">최근본</span>
        </button>
        <button type="button" class="m-shop-quick-btn m-shop-quick-top" title="위로">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
            <span class="m-shop-quick-label">위로</span>
        </button>
    </aside>

    <!-- 최근 본 상품 패널 (quick-today 클릭 시 펼침) -->
    <?php $_today_items = function_exists('get_view_today_items') ? get_view_today_items(true) : []; ?>
    <div id="m-shop-today-panel" class="m-shop-today-panel" hidden>
        <div class="m-shop-today-panel-head">
            <strong>최근 본 상품 <span style="color: var(--m-text-faint); font-weight: 500; font-size: var(--m-text-xs);">(<?php echo count($_today_items); ?>)</span></strong>
            <button type="button" class="m-shop-today-close" aria-label="닫기">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="m-shop-today-panel-body">
            <?php if (!empty($_today_items)) { ?>
                <ul class="m-shop-today-list">
                    <?php foreach ($_today_items as $_tv) {
                        if (empty($_tv['it_id'])) continue;
                        $_tv_url = shop_item_url($_tv['it_id']);
                        $_tv_img = get_it_image($_tv['it_id'], 56, 56, $_tv['it_id'], '', get_text($_tv['it_name']));
                        $_tv_price = get_price($_tv);
                    ?>
                    <li class="m-shop-today-item">
                        <a href="<?php echo $_tv_url; ?>" class="m-shop-today-link">
                            <span class="m-shop-today-thumb"><?php echo $_tv_img; ?></span>
                            <span class="m-shop-today-meta">
                                <span class="m-shop-today-name"><?php echo cut_str(get_text($_tv['it_name']), 30, ''); ?></span>
                                <span class="m-shop-today-price"><?php echo is_int($_tv_price) ? number_format($_tv_price).'원' : $_tv_price; ?></span>
                            </span>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p class="m-shop-today-empty">최근 본 상품이 없습니다.</p>
            <?php } ?>
        </div>
    </div>

</div><!-- } .m-shell 끝 -->

<style>
/* shop 우측 fixed quick action */
.m-shop-quick {
    position: fixed; right: 16px; top: 50%;
    transform: translateY(-50%); z-index: 100;
    display: flex; flex-direction: column; gap: 6px;
}
.m-shop-quick-btn {
    display: inline-flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 2px; width: 52px; height: 52px;
    background: var(--m-surface); border: 1px solid var(--m-border);
    border-radius: var(--m-radius); color: var(--m-text-soft);
    text-decoration: none; cursor: pointer;
    box-shadow: var(--m-shadow);
    position: relative;
    transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.15s;
}
.m-shop-quick-btn:hover {
    background: var(--m-primary); color: #fff; border-color: var(--m-primary);
    transform: translateX(-2px);
}
.m-shop-quick-btn:focus { outline: none; }
.m-shop-quick-btn:focus-visible { outline: 2px solid var(--m-primary); outline-offset: 2px; }
.m-shop-quick-btn[aria-expanded="true"] { background: var(--m-primary); color: #fff; border-color: var(--m-primary); }
.m-shop-quick-label { font-size: 10px; font-weight: 500; letter-spacing: -0.02em; }
/* 라이트: --m-text(짙은) 배경으로 강조 / 다크: --m-bg(더 짙은) 으로 다른 버튼보다 한톤 깊게 */
.m-shop-quick-top { background: var(--m-text); color: var(--m-bg); border-color: var(--m-text); }
[data-theme="dark"] .m-shop-quick-top {
    background: var(--m-bg); color: var(--m-text); border-color: var(--m-border);
}
.m-shop-quick-top:hover { background: var(--m-primary); border-color: var(--m-primary); color: #fff; }
.m-shop-quick-badge {
    position: absolute; top: -4px; right: -4px;
    min-width: 18px; height: 18px; padding: 0 4px;
    display: inline-flex; align-items: center; justify-content: center;
    background: #ef4444; color: #fff;
    border-radius: 9999px;
    font-size: 10px; font-weight: 700; line-height: 1;
}

/* 최근 본 상품 panel */
.m-shop-today-panel {
    position: fixed; right: 80px; top: 50%; transform: translateY(-50%);
    z-index: 99; width: 280px;
    background: var(--m-surface); border: 1px solid var(--m-border);
    border-radius: var(--m-radius); box-shadow: var(--m-shadow-md);
    display: flex; flex-direction: column;
    max-height: 70vh;
}
/* [hidden] HTML attribute 가 display: flex 보다 specificity 가 낮아 무시되는 문제 */
.m-shop-today-panel[hidden] { display: none; }
.m-shop-today-panel-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; border-bottom: 1px solid var(--m-border);
    font-size: var(--m-text-sm);
}
.m-shop-today-close {
    display: inline-flex; width: 24px; height: 24px;
    background: transparent; border: 0; color: var(--m-text-faint); cursor: pointer;
    align-items: center; justify-content: center;
}
.m-shop-today-close:hover { color: var(--m-text); }
.m-shop-today-panel-body { padding: 8px; overflow-y: auto; }

/* 오늘 본 상품 list */
.m-shop-today-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
.m-shop-today-item { margin: 0; }
.m-shop-today-link {
    display: flex; gap: 10px; align-items: center;
    padding: 6px; border-radius: var(--m-radius-sm);
    text-decoration: none; color: inherit;
    transition: background 0.15s;
}
.m-shop-today-link:hover { background: var(--m-surface-2); }
.m-shop-today-thumb {
    flex-shrink: 0; width: 56px; height: 56px;
    border-radius: var(--m-radius-sm); overflow: hidden;
    background: var(--m-surface-2);
}
.m-shop-today-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.m-shop-today-meta { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1; }
.m-shop-today-name {
    font-size: var(--m-text-sm); color: var(--m-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-shop-today-price { font-size: var(--m-text-sm); font-weight: 600; color: var(--m-primary); }
.m-shop-today-empty {
    margin: 0; padding: 16px 12px;
    text-align: center; font-size: var(--m-text-sm);
    color: var(--m-text-muted);
}

@media (max-width: 880px) {
    .m-shop-quick { right: 8px; }
    .m-shop-quick-btn { width: 44px; height: 44px; }
    .m-shop-today-panel { right: 60px; width: 240px; }
}
</style>

<script>
(function(){
    var todayBtn   = document.querySelector('.js-shop-quick-today');
    var todayPanel = document.getElementById('m-shop-today-panel');
    var topBtn     = document.querySelector('.m-shop-quick-top');

    function setOpen(open){
        if (open) {
            todayPanel.removeAttribute('hidden');
            todayBtn.setAttribute('aria-expanded', 'true');
        } else {
            todayPanel.setAttribute('hidden', '');
            todayBtn.setAttribute('aria-expanded', 'false');
        }
    }
    function isOpen(){ return !todayPanel.hasAttribute('hidden'); }

    if (todayBtn && todayPanel) {
        todayBtn.addEventListener('click', function(e){
            e.stopPropagation();
            setOpen(!isOpen());
        });
        todayPanel.addEventListener('click', function(e){
            if (e.target.closest('.m-shop-today-close')) setOpen(false);
        });
        document.addEventListener('click', function(e){
            if (isOpen() && !todayPanel.contains(e.target) && !todayBtn.contains(e.target)) {
                setOpen(false);
            }
        });
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape' && isOpen()) setOpen(false);
        });
    }
    if (topBtn) {
        topBtn.addEventListener('click', function(){
            // 실제 스크롤 컨테이너는 .m-shell (html/body 는 overflow:hidden)
            var scroller = document.querySelector('.m-shell') || window;
            scroller.scrollTo({ top: 0, behavior: 'smooth' });
            // 클릭 후 :focus 잔존 방지
            topBtn.blur();
        });
    }
})();
</script>

<?php
$sec = get_microtime() - $begin_time;
$file = $_SERVER['SCRIPT_NAME'];

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>

<script src="<?php echo G5_JS_URL; ?>/sns.js"></script>

<?php
include_once(G5_THEME_PATH.'/tail.sub.php');
