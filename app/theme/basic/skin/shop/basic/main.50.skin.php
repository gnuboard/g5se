<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 상품진열 50 시작 { -->
<?php
$i=0;
foreach((array) $list as $row){

    if( empty($row) ) continue;
    $i++;
    
    $item_link_href = shop_item_url($row['it_id']);
    $star_score = $row['it_use_avg'] ? (int) get_star($row['it_use_avg']) : '';

    $sct_last = '';
    if($i>1 && $i%$this->list_mod == 0)
        $sct_last = ' sct_last'; // 줄 마지막
    if ($i == 1) {
        if ($this->css) {
            echo "<ul class=\"{$this->css}\">\n";
        } else {
            echo "<ul id=\"smt_{$this->type}\" class=\"smt_30\">\n";
        }
    }
    echo "<li class=\"sct_li sct_li_{$i}\">\n";
    if ($this->href) {
        echo "<div class=\"sct_img\"><a href=\"{$item_link_href}\">\n";
    }
    if ($this->view_it_img) {
        echo get_it_image($row['it_id'], $this->img_width, $this->img_height, '', '', stripslashes($row['it_name']))."\n";
    }
    if ($this->href) {
        echo "</a></div>\n";
    }
	
	
	echo "<div class=\"sct_cnt\">\n"; 
	
	// 사용후기 평점표시 — 평가가 없어도 빈 별 5개를 출력해 목록 높이를 일정하게 유지한다.
	if ($this->view_star) {
        $star_label = $star_score ? '고객평점 별 '.$star_score.'개' : '고객평점 평가 없음';
        echo "<span class=\"sound_only\">{$star_label}</span>\n";
        echo "<span class=\"sct_star\" aria-hidden=\"true\">\n";
        for ($star = 1; $star <= 5; $star++) {
            echo '<i class="fa fa-star'.(($star_score && $star <= $star_score) ? '' : '-o').'"></i>';
        }
        echo "</span>\n";
    }
       
    if ($this->href) {
        echo "<div class=\"sct_txt\"><a href=\"{$item_link_href}\">\n";
    }
    if ($this->view_it_name) {
        echo stripslashes($row['it_name'])."\n";
    }
    if ($this->href) {
        echo "</a></div>\n";
    }
    if ($this->view_it_cust_price || $this->view_it_price) {
        echo "<div class=\"sct_cost\">\n";
        if ($this->view_it_cust_price && $row['it_cust_price']) {
			echo "<span class=\"sct_dict\">".display_price($row['it_cust_price'])."</span>\n";
        }
        if ($this->view_it_price) {
            echo display_price(get_price($row), $row['it_tel_inq'])."\n";
        }
        echo "</div>\n";
    }
	echo "</div>\n";
    echo "</li>\n";
}
if ($i >= 1) echo "</ul>\n";
if($i == 0) echo "<p class=\"sct_noitem\">등록된 상품이 없습니다.</p>\n";
?>

<script>
$(document).ready(function(){
	$('.smt_30').bxSlider({
	    minSlides: 4,
	    maxSlides: 4,
	    mode: 'vertical',
	    pager:false
	});
});
</script>
<!-- } 상품진열 50 끝 -->
