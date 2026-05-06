<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// gnu5se: list.10 — modern Tailwind/UnoCSS grid 카드.
//   기존 sct/sct_10/sct_li 마크업 대신 카드 그리드 + hover lift.
//   JS 훅 (.btn_cart/.btn_wish + data-it_id, theme.shop.list.js 사용) 유지.
add_javascript('<script src="'.G5_THEME_JS_URL.'/theme.shop.list.js"></script>', 10);
?>

<!-- 상품진열 10 (modern) 시작 { -->
<?php
$i = 0;

$this->view_star = (method_exists($this, 'view_star')) ? $this->view_star : true;

$list_mod = max(1, min(6, (int)$this->list_mod));
// Tailwind grid-cols-N — UnoCSS runtime 이 DOM scan 으로 동적 클래스 픽업
$cols_class = "grid-cols-2 sm:grid-cols-2 md:grid-cols-{$list_mod} lg:grid-cols-{$list_mod}";

foreach((array) $list as $row){
    if( empty($row) ) continue;

    $item_link_href = shop_item_url($row['it_id']);
    $star_score = $row['it_use_avg'] ? (int) get_star($row['it_use_avg']) : '';
    $is_soldout = is_soldout($row['it_id'], true);

    if ($i === 0) {
        echo '<ul class="sct grid gap-4 '.$cols_class.' list-none p-0 m-0">'."\n";
    }
    $i++;
?>
<li class="sct_li group relative flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:shadow-md hover:-translate-y-0.5 dark:border-slate-700 dark:bg-slate-800">
    <div class="sct_img relative aspect-square overflow-hidden bg-slate-100 dark:bg-slate-900">
        <?php if ($this->href) { ?>
            <a href="<?php echo $item_link_href; ?>" class="block h-full w-full">
        <?php } ?>

        <?php if ($this->view_it_img) {
            // get_it_image 가 <img> 반환. transition+scale wrap.
            echo '<div class="h-full w-full transition-transform duration-300 group-hover:scale-105">'
                . get_it_image($row['it_id'], $this->img_width, $this->img_height, '', '', stripslashes($row['it_name']))
                . '</div>';
        } ?>

        <?php if ($this->href) { ?></a><?php } ?>

        <?php if ($this->view_it_icon && $is_soldout) { ?>
            <div class="absolute inset-0 flex items-center justify-center bg-black/55">
                <span class="rounded bg-white/90 px-3 py-1 text-xs font-bold tracking-wider text-slate-900">SOLD OUT</span>
            </div>
        <?php } ?>

        <?php if (!$is_soldout) { ?>
            <div class="sct_btn list-10-btn absolute bottom-2 right-2 opacity-0 transition group-hover:opacity-100">
                <button type="button"
                        class="btn_cart sct_cart inline-flex items-center gap-1 rounded-md bg-slate-900/85 px-3 py-1.5 text-xs font-medium text-white backdrop-blur hover:bg-slate-900"
                        data-it_id="<?php echo $row['it_id']; ?>">
                    <i class="fa fa-shopping-cart" aria-hidden="true"></i> 장바구니
                </button>
            </div>
        <?php } ?>

        <div class="cart-layer"></div>
    </div>

    <div class="sct_ct_wrap flex flex-1 flex-col gap-1.5 p-3">
        <?php if ($this->view_star && $star_score) { ?>
            <div class="sct_star flex items-center gap-1 text-xs text-amber-500">
                <span class="sound_only">고객평점</span>
                <?php for ($s=1; $s<=5; $s++) { ?>
                    <i class="fa fa-star<?php echo ($s <= $star_score) ? '' : '-o'; ?>" aria-hidden="true"></i>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($this->view_it_id) { ?>
            <div class="sct_id text-xs text-slate-400">&lt;<?php echo stripslashes($row['it_id']); ?>&gt;</div>
        <?php } ?>

        <div class="sct_txt text-sm font-medium leading-snug text-slate-800 dark:text-slate-100">
            <?php if ($this->href) { ?><a href="<?php echo $item_link_href; ?>" class="line-clamp-2 hover:text-admin-primary-600 dark:hover:text-admin-primary-400"><?php } ?>
            <?php if ($this->view_it_name) echo stripslashes($row['it_name']); ?>
            <?php if ($this->href) { ?></a><?php } ?>
        </div>

        <?php if ($this->view_it_basic && $row['it_basic']) { ?>
            <div class="sct_basic text-xs text-slate-500 line-clamp-2"><?php echo stripslashes($row['it_basic']); ?></div>
        <?php } ?>

        <div class="sct_bottom mt-auto flex items-end justify-between gap-2 pt-2">
            <?php if ($this->view_it_cust_price || $this->view_it_price) { ?>
                <div class="sct_cost flex flex-col">
                    <?php if ($this->view_it_price) { ?>
                        <span class="text-base font-bold text-slate-900 dark:text-slate-50"><?php echo display_price(get_price($row), $row['it_tel_inq']); ?></span>
                    <?php } ?>
                    <?php if ($this->view_it_cust_price && $row['it_cust_price']) { ?>
                        <span class="sct_dict text-xs text-slate-400 line-through"><?php echo display_price($row['it_cust_price']); ?></span>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="sct_op_btn relative flex items-center gap-1">
                <button type="button"
                        class="btn_wish inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-900/30"
                        data-it_id="<?php echo $row['it_id']; ?>">
                    <span class="sound_only">위시리스트</span>
                    <i class="fa fa-heart-o" aria-hidden="true"></i>
                </button>
                <?php if ($this->view_sns) { ?>
                    <button type="button"
                            class="btn_share inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-700 dark:hover:text-slate-200">
                        <span class="sound_only">공유하기</span>
                        <i class="fa fa-share-alt" aria-hidden="true"></i>
                    </button>
                    <div class="sct_sns_wrap absolute bottom-full right-0 z-10 mb-1 hidden">
                        <div class="sct_sns rounded-md border border-slate-200 bg-white p-2 shadow-md dark:border-slate-700 dark:bg-slate-800">
                            <h3 class="mb-1 text-xs font-semibold text-slate-700 dark:text-slate-200">SNS 공유</h3>
                            <?php
                                $sns_url   = $item_link_href;
                                $sns_title = get_text($row['it_name']).' | '.get_text($config['cf_title']);
                                echo get_sns_share_link('facebook', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/facebook.png');
                                echo get_sns_share_link('twitter',  $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/twitter.png');
                            ?>
                            <button type="button" class="sct_sns_cls absolute right-1 top-1 text-slate-400 hover:text-slate-700">
                                <span class="sound_only">닫기</span><i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <?php if ($this->view_it_icon) { ?>
            <div class="sit_icon_li flex flex-wrap gap-1 pt-1"><?php echo item_icon($row); ?></div>
        <?php } ?>
    </div>
</li>
<?php
}   //end foreach

if ($i >= 1) echo "</ul>\n";

if ($i === 0) {
    echo '<p class="sct_noitem rounded-lg border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">등록된 상품이 없습니다.</p>'."\n";
}
?>
<!-- } 상품진열 10 (modern) 끝 -->

<script>
//SNS 공유
$(function (){
    $(".btn_share").on("click", function(e) {
        e.preventDefault();
        $(this).siblings(".sct_sns_wrap").toggleClass("hidden");
    });
    $(document).on("click", ".sct_sns_cls", function(e) {
        e.preventDefault();
        $(this).closest(".sct_sns_wrap").addClass("hidden");
    });
});
</script>
