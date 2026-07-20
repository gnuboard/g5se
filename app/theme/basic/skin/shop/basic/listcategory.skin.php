<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$categories = array();

$ca_id_len = strlen($ca_id);
$len2 = $ca_id_len + 2;
$len4 = $ca_id_len + 4;

$sql = " select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id like :ca_pattern and length(ca_id) = :ca_length and ca_use = '1' order by ca_order, ca_id ";
$result = sql_pdo_query($sql, [':ca_pattern' => $ca_id.'%', ':ca_length' => $len2]);
while ($row=sql_pdo_fetch_array($result)) {

    $category_pattern = $row['ca_id'].'%';
    $row2 = sql_pdo_fetch(
        " select count(*) as cnt from {$g5['g5_shop_item_table']} where (ca_id like :ca_id1 or ca_id2 like :ca_id2 or ca_id3 like :ca_id3) and it_use = '1' ",
        [':ca_id1' => $category_pattern, ':ca_id2' => $category_pattern, ':ca_id3' => $category_pattern]
    );

    $categories[] = array(
        'url' => shop_category_url($row['ca_id']),
        'name' => $row['ca_name'],
        'count' => (int)$row2['cnt'],
    );
}

if ($categories) {

    // add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
    add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 상품분류 1 시작 { -->
<aside id="sct_ct_1" class="sct_ct">
    <h2>현재 상품 분류와 관련된 분류</h2>
    <ul class="sct-children-desktop">
        <?php foreach ($categories as $category) { ?>
        <li><a href="<?php echo $category['url']; ?>"><?php echo get_text($category['name']); ?> (<?php echo $category['count']; ?>)</a></li>
        <?php } ?>
    </ul>

</aside>
<!-- } 상품분류 1 끝 -->

<?php }
