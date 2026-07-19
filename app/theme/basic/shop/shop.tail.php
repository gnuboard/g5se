<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// g5se: 반응형 단일 마크업 정책 — G5_IS_MOBILE 분기 제거.

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
            <section class="m-card m-shop-popular-card" style="padding: 16px;">
                <header class="m-shop-popular-header">
                    <h2>
                        <a href="<?php echo shop_type_url('4'); ?>">인기상품 <span aria-hidden="true">→</span></a>
                    </h2>
                    <div class="m-shop-popular-controls" aria-label="인기상품 이동">
                        <button type="button" class="m-shop-popular-prev" aria-label="이전 인기상품">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <button type="button" class="m-shop-popular-next" aria-label="다음 인기상품">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>
                </header>
                <?php
                // 우측 사이드바는 작은 영역이므로 레거시 세로 슬라이더 대신
                // 전용 컨트롤로 이동할 수 있는 간결한 상품 목록으로 출력한다.
                $list = new item_list(G5_SHOP_SKIN_PATH.'/main.50.skin.php', 1, 8, 72, 72);
                $list->set_type(4);
                $list->set_css('m-shop-sidebar-products');
                $list->set_view('it_id', false);
                $list->set_view('it_name', true);
                $list->set_view('it_basic', false);
                $list->set_view('it_cust_price', false);
                $list->set_view('it_price', true);
                $list->set_view('it_icon', false);
                $list->set_view('sns', false);
                // 평가가 없어도 빈 별 5개를 표시해 모든 행의 높이를 일정하게 유지한다.
                $list->set_view('star', true);
                echo $list->run();
                ?>
                <script>
                (function () {
                    var script = document.currentScript;
                    var card = script ? script.closest('.m-shop-popular-card') : null;
                    if (!card) return;
                    var items = Array.prototype.slice.call(card.querySelectorAll('.m-shop-sidebar-products > li'));
                    var controls = card.querySelector('.m-shop-popular-controls');
                    var prev = card.querySelector('.m-shop-popular-prev');
                    var next = card.querySelector('.m-shop-popular-next');
                    var visibleCount = 4;
                    var start = 0;

                    if (items.length <= visibleCount) {
                        controls.hidden = true;
                        return;
                    }

                    function render() {
                        items.forEach(function (item, index) {
                            var relative = (index - start + items.length) % items.length;
                            item.hidden = relative >= visibleCount;
                        });
                    }

                    prev.addEventListener('click', function () {
                        start = (start - 1 + items.length) % items.length;
                        render();
                    });
                    next.addEventListener('click', function () {
                        start = (start + 1) % items.length;
                        render();
                    });
                    render();
                })();
                </script>
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

    <!-- g5se: 통합 footer — community 와 shop 동일 (_tail.inc.php). 사이트 정보 + 공지 + 접속자 (현재/오늘/어제/최대/전체) -->
    <?php require_once(G5_THEME_PATH.'/modern/_tail.inc.php'); ?>

    <?php /* g5se: shop 전용 floating quick action (장바구니/위시/최근본/위로) 제거 — top nav 의 카트 + 마이페이지 아이콘 + 통합 /mypage hub 로 대체 */ ?>

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

    /* footer 의 inline grid-template-columns: 2fr 1fr 1fr 무력화 → 1열 stack */
    .m-footer [style*="grid-template-columns: 2fr 1fr 1fr"],
    .m-footer [style*="grid-template-columns:2fr 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
@media (max-width: 600px) {
    /* 사이트 정보 dl 의 inline 1fr 1fr — 600px 이하에선 dt/dd 도 1열로 */
    .m-footer dl[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
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
