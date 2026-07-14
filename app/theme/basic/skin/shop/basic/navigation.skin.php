<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$navi_datas = $ca_ids = array();
$is_item_view = (isset($it_id) && isset($it) && isset($it['it_id']) && $it_id === $it['it_id']) ? true : false;
$current_ca_id = '';

if( !$is_item_view && $ca_id ){
    $current_ca_id = $ca_id;
} else if( $is_item_view && isset($it) && is_array($it) ) {
    $current_ca_id = $it['ca_id'];
}

// 분류 코드는 2자리씩 최대 10자리(5단계). 각 단계에서 현재 분류의 형제 목록을 만든다.
for ($depth = 1; $depth <= 5; $depth++) {
    $length = $depth * 2;
    if (strlen($current_ca_id) < $length) break;

    $parent_id = substr($current_ca_id, 0, $length - 2);
    $sql = " select ca_id, ca_name from {$g5['g5_shop_category_table']}
                where ca_use = '1' and length(ca_id) = {$length} ";
    $params = array();
    if ($parent_id !== '') {
        $sql .= " and ca_id like :parent_id ";
        $params[':parent_id'] = $parent_id.'%';
    }
    $sql .= " order by ca_order, ca_id ";

    $result = sql_pdo_query($sql, $params);
    while ($row = sql_fetch_array($result)) {
        $row['url'] = shop_category_url($row['ca_id']);
        $navi_datas[$depth - 1][] = $row;
    }
    $ca_ids[$depth - 1] = substr($current_ca_id, 0, $length);
}

$location_class = array();
if($is_item_view){
    $location_class[] = 'view_location';    // view_location는 리스트 말고 상품보기에서만 표시
} else {
	$location_class[] = 'is_list is_right';    // view_location는 리스트 말고 상품보기에서만 표시
}

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/shop.category.navigation.js"></script>', 10);
?>

<div id="sct_location" class="<?php echo implode(' ', $location_class);?>"> <!-- class="view_location" --> <!-- view_location는 리스트 말고 상품보기에서만 표시 -->
    <div id="sct-location-path" class="sct-location-path">
        <a href='<?php echo G5_SHOP_URL; ?>/' class="go_home"><span class="sound_only">메인으로</span><i class="fa fa-home" aria-hidden="true"></i></a>
    <?php if ( is_array($navi_datas) && $navi_datas ){ ?>

        <?php foreach ($navi_datas as $depth_index => $depth_categories) {
            if (empty($depth_categories)) continue;
        ?>
        <span class="sct-location-step">
            <i class="dividing-line fa fa-angle-right" aria-hidden="true"></i>
            <select class="shop_hover_selectbox category<?php echo $depth_index + 1; ?>" aria-label="<?php echo $depth_index + 1; ?>단계 상품 분류">
                <?php foreach((array) $depth_categories as $data ){ ?>
                    <option value="<?php echo $data['ca_id']; ?>" data-url="<?php echo $data['url']; ?>" <?php if($ca_ids[$depth_index] === $data['ca_id']) echo 'selected'; ?>><?php echo get_text($data['ca_name']); ?></option>
                <?php } ?>
            </select>
        </span>
        <?php } ?>
    <?php } else { ?>
        <?php echo get_text($g5['title']); ?>
    <?php } ?>
    </div>
</div>
<script>
jQuery(function($){
    $(document).ready(function() {
        $("#sct_location select").on("change", function(e){
            var url = $(this).find(':selected').attr("data-url");
            
            if (typeof itemlist_ca_id != "undefined" && itemlist_ca_id === this.value) {
                return false;
            }

            window.location.href = url;
        });

        // 화면 크기가 바뀌어도 모바일 native select와 데스크톱 드롭다운을 즉시 맞춘다.
        var categoryMedia = window.matchMedia('(max-width: 880px)');
        var syncCategoryNavigation = function() {
            $("select.shop_hover_selectbox").each(function() {
                var $select = $(this);
                var $desktopDropdown = $select.next('.shop_select_to_html');

                if (categoryMedia.matches) {
                    $desktopDropdown.remove();
                    $select.show();
                } else if (!$desktopDropdown.length) {
                    $select.shop_select_to_html();
                }
            });
        };

        syncCategoryNavigation();
        if (categoryMedia.addEventListener) {
            categoryMedia.addEventListener('change', syncCategoryNavigation);
        } else {
            categoryMedia.addListener(syncCategoryNavigation);
        }
    });
});
</script>
