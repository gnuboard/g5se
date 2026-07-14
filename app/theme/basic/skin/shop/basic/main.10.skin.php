<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// g5se: main.10 — 메인 페이지 carousel (owl-carousel 사용).
//   <li> 카드는 list.10 modern 패턴과 동일 (rounded card, hover lift).
?>

<!-- 상품진열 main.10 (modern) 시작 { -->
<?php
$i = 0;
foreach((array) $list as $row){
    if( empty($row) ) continue;
    $i++;

    $item_link_href = shop_item_url($row['it_id']);
    $star_score = $row['it_use_avg'] ? (int) get_star($row['it_use_avg']) : '';
    $is_soldout = is_soldout($row['it_id'], true);

    if ($i === 1) {
        if ($this->css) {
            echo '<ul class="'.$this->css.' list-none p-0 m-0">'."\n";
        } else {
            // owl-carousel 은 그대로 유지 (외부 JS 의존). 내부 카드만 modern.
            echo '<ul class="sct owl-carousel list-none p-0 m-0" data-value="'.(int)$this->list_mod.'">'."\n";
        }
    }
?>
<li class="sct_li group relative flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:shadow-md hover:-translate-y-0.5 dark:border-slate-700 dark:bg-slate-800">
    <div class="sct_img relative aspect-square overflow-hidden bg-slate-100 dark:bg-slate-900">
        <?php if ($this->href) { ?><a href="<?php echo $item_link_href; ?>" class="block h-full w-full"><?php } ?>
            <?php if ($this->view_it_img) {
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
    </div>

    <div class="flex flex-1 flex-col gap-1.5 p-3">
        <?php if ($this->view_it_id) { ?>
            <div class="sct_id text-xs text-slate-400">&lt;<?php echo stripslashes($row['it_id']); ?>&gt;</div>
        <?php } ?>

        <?php if ($this->view_star) { ?>
            <div class="sct_star<?php echo $star_score ? '' : ' is-empty'; ?> flex min-h-3.5 items-center gap-0.5 text-xs<?php echo $star_score ? ' text-amber-500' : ' text-slate-400 dark:text-slate-500'; ?>">
                <?php if ($star_score) { ?>
                    <span class="sound_only">고객평점 별 <?php echo $star_score; ?>개</span>
                    <?php for ($s=1; $s<=5; $s++) { ?>
                        <i class="fa fa-star<?php echo ($s <= $star_score) ? '' : '-o'; ?>" aria-hidden="true"></i>
                    <?php } ?>
                <?php } else { ?>
                    <i class="fa fa-minus-circle" aria-hidden="true"></i>
                    <span>평가 없음</span>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="sct_txt text-sm font-medium leading-snug text-slate-800 dark:text-slate-100">
            <?php if ($this->href) { ?><a href="<?php echo $item_link_href; ?>" class="line-clamp-2 hover:text-admin-primary-600 dark:hover:text-admin-primary-400"><?php } ?>
            <?php if ($this->view_it_name) echo stripslashes($row['it_name']); ?>
            <?php if ($this->href) { ?></a><?php } ?>
        </div>

        <?php if ($this->view_it_cust_price || $this->view_it_price) { ?>
            <div class="sct_cost mt-auto pt-2">
                <?php if ($this->view_it_price) { ?>
                    <span class="text-base font-bold text-slate-900 dark:text-slate-50"><?php echo display_price(get_price($row), $row['it_tel_inq']); ?></span>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</li>
<?php
}

if ($i >= 1) echo "</ul>\n";

if($i === 0) {
    echo '<p class="sct_noitem m-shop-empty">등록된 상품이 없습니다.</p>'."\n";
}
?>
<!-- } 상품진열 main.10 (modern) 끝 -->
