<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$sct_sort_href = $_SERVER['SCRIPT_NAME'].'?';

if($ca_id) {
	$shop_category_url = shop_category_url($ca_id);
    $sct_sort_href = (strpos($shop_category_url, '?') === false) ? $shop_category_url.'?1=1' : $shop_category_url;
} else if($ev_id) {
    $sct_sort_href .= 'ev_id='.$ev_id;
}

if($skin)
    $sct_sort_href .= '&amp;skin='.$skin;
$sct_sort_href .= '&amp;sort=';

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);

$mobile_sort_options = array(
    array('it_sum_qty', 'desc', '판매많은순'),
    array('it_price', 'asc', '낮은가격순'),
    array('it_price', 'desc', '높은가격순'),
    array('it_use_avg', 'desc', '평점높은순'),
    array('it_use_cnt', 'desc', '후기많은순'),
    array('it_update_time', 'desc', '최근등록순'),
);
$current_sort = isset($sort) ? $sort : '';
$current_sortodr = isset($sortodr) ? strtolower($sortodr) : '';
?>

<!-- 상품 정렬 선택 시작 { -->
<section id="sct_sort">
    <h2>상품 정렬</h2>

    <!-- 기타 정렬 옵션 
    <ul>
        <li><a href="<?php echo $sct_sort_href; ?>it_name&amp;sortodr=asc">상품명순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type1&amp;sortodr=desc">히트상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type2&amp;sortodr=desc">추천상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type3&amp;sortodr=desc">최신상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type4&amp;sortodr=desc">인기상품</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_type5&amp;sortodr=desc">할인상품</a></li>
    </ul>
	-->

    <ul id="ssch_sort">
        <li><a href="<?php echo $sct_sort_href; ?>it_sum_qty&amp;sortodr=desc">판매많은순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_price&amp;sortodr=asc">낮은가격순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_price&amp;sortodr=desc">높은가격순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_use_avg&amp;sortodr=desc">평점높은순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_use_cnt&amp;sortodr=desc">후기많은순</a></li>
        <li><a href="<?php echo $sct_sort_href; ?>it_update_time&amp;sortodr=desc">최근등록순</a></li>
    </ul>

    <label for="sct-sort-mobile" class="sound_only">상품 정렬 선택</label>
    <select id="sct-sort-mobile" onchange="if (this.value) window.location.href = this.value;">
        <option value="">상품 정렬 선택</option>
        <?php foreach ($mobile_sort_options as $sort_option) {
            $is_selected = $current_sort === $sort_option[0] && $current_sortodr === $sort_option[1];
        ?>
        <option value="<?php echo $sct_sort_href.$sort_option[0].'&amp;sortodr='.$sort_option[1]; ?>"<?php echo $is_selected ? ' selected' : ''; ?>><?php echo $sort_option[2]; ?></option>
        <?php } ?>
    </select>
</section>
<!-- } 상품 정렬 선택 끝 -->
