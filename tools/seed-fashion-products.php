<?php

/**
 * 패션 테스트몰 상품/무료 이미지 시드.
 *
 * 사용:
 *   php tools/seed-fashion-products.php
 *   php tools/seed-fashion-products.php --clean
 *
 * 먼저 tools/seed-clothing-categories.php를 실행해야 한다.
 * 이미지는 Unsplash License가 적용되는 공개 사진을 내려받아 data/item에 저장한다.
 * 원본과 라이선스: tools/fashion-demo-image-sources.md
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI에서만 실행할 수 있습니다.\n");
    exit(1);
}

$project_root = dirname(__DIR__);
$original_cwd = getcwd();
$_SERVER += [
    'HTTP_HOST' => 'localhost',
    'SERVER_NAME' => 'localhost',
    'SERVER_PORT' => '80',
    'REMOTE_ADDR' => '127.0.0.1',
    'REQUEST_URI' => '/',
    'SCRIPT_NAME' => '/tools/seed-fashion-products.php',
];
chdir($project_root.'/app');
require_once './_common.php';
chdir($original_cwd);

$item_table = $g5['g5_shop_item_table'];
$category_table = $g5['g5_shop_category_table'];
$id_prefix = 'FASH-DEMO-';
$image_dir_name = 'fashion-demo';
$image_dir = G5_DATA_PATH.'/item/'.$image_dir_name;

if (in_array('--clean', $argv, true)) {
    sql_pdo_query(
        " delete from {$item_table} where it_id like :prefix ",
        [':prefix' => $id_prefix.'%']
    );

    if (is_dir($image_dir)) {
        foreach (glob($image_dir.'/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        @rmdir($image_dir);
    }

    echo "패션 테스트 상품과 내려받은 이미지를 삭제했습니다.\n";
    exit(0);
}

$images = [
    'trench'    => 'photo-1529139574466-a303027c1d8b',
    'jacket'    => 'photo-1551028719-00167b16eac5',
    'blouse'    => 'photo-1596755094514-f87e34085b2c',
    'knit'      => 'photo-1620799140408-edc6dcb6d633',
    'sneaker1'  => 'photo-1542291026-7eec264c27ff',
    'slipon'    => 'photo-1560769629-975ec94e6a86',
    'pumps'     => 'photo-1543163521-1bf539c55dd2',
    'loafer'    => 'photo-1614252369475-531eba835eb1',
    'coat'      => 'photo-1544022613-e87ca75a784a',
    'blouson'   => 'photo-1591047139829-d91aecb6caea',
    'shirt'     => 'photo-1603252109303-2751441dd157',
    'sweatshirt'=> 'photo-1576566588028-4147f3842f27',
    'sneaker2'  => 'photo-1549298916-b41d501d3772',
    'boatshoe'  => 'photo-1533867617858-e7b97e060509',
    'oxford'    => 'photo-1613987876445-fcb353cd8e27',
    'derby'     => 'photo-1449505278894-297fdb3edbc1',
];

$catalog = [
    ['z910101010', 'trench',     ['클래식 벨티드 트렌치코트', '라이트 오버핏 트렌치코트'],  [129000, 149000]],
    ['z910101020', 'jacket',     ['미니멀 싱글 재킷', '데일리 크롭 재킷'],             [89000, 99000]],
    ['z910102010', 'blouse',     ['소프트 코튼 셔츠', '실키 타이 블라우스'],           [49000, 59000]],
    ['z910102020', 'knit',       ['캐시미어 터치 니트', '케이블 버튼 가디건'],         [55000, 69000]],
    ['z910201010', 'sneaker1',   ['컬러 포인트 스니커즈', '라이트 러닝 스니커즈'],     [79000, 89000]],
    ['z910201020', 'slipon',     ['캔버스 데일리 슬립온', '쿠션 플랫폼 슬립온'],       [59000, 69000]],
    ['z910202010', 'pumps',      ['클래식 미들 펌프스', '슬림 라인 펌프스'],           [89000, 99000]],
    ['z910202020', 'loafer',     ['소프트 레더 로퍼', '메탈 장식 페니 로퍼'],          [99000, 119000]],
    ['z920101010', 'coat',       ['울 블렌드 싱글 코트', '프리미엄 발마칸 코트'],      [159000, 189000]],
    ['z920101020', 'blouson',    ['미니멀 집업 블루종', '라이트 유틸리티 블루종'],     [109000, 129000]],
    ['z920102010', 'shirt',      ['이지케어 드레스 셔츠', '옥스퍼드 버튼다운 셔츠'],   [49000, 59000]],
    ['z920102020', 'sweatshirt', ['헤비웨이트 맨투맨', '릴랙스 그래픽 맨투맨'],        [59000, 69000]],
    ['z920201010', 'sneaker2',   ['레트로 코트 스니커즈', '베이직 레더 스니커즈'],     [89000, 109000]],
    ['z920201020', 'boatshoe',   ['클래식 보트화', '스웨이드 데크 슈즈'],              [109000, 129000]],
    ['z920202010', 'oxford',     ['캡토 옥스퍼드 슈즈', '플레인토 옥스퍼드'],          [139000, 159000]],
    ['z920202020', 'derby',      ['볼륨 솔 더비 슈즈', '클래식 레더 더비'],            [149000, 169000]],
];

/**
 * 결제 테스트에 부담 없는 데모 금액으로 축소한다.
 * 예: 17,800 → 1,780 / 129,000 → 1,290
 */
function normalize_demo_price(int $price): int
{
    while ($price > 2000) {
        $price = (int) floor($price / 10);
    }

    return max(1, $price);
}

foreach ($catalog as [$ca_id]) {
    $category = sql_fetch(" select ca_id from {$category_table} where ca_id = '{$ca_id}' and ca_use = 1 ");
    if (empty($category['ca_id'])) {
        fwrite(STDERR, "분류 {$ca_id}가 없습니다. 먼저 php tools/seed-clothing-categories.php를 실행하세요.\n");
        exit(1);
    }
}

if (!is_dir($image_dir) && !mkdir($image_dir, G5_DIR_PERMISSION, true) && !is_dir($image_dir)) {
    throw new RuntimeException("이미지 디렉터리를 만들 수 없습니다: {$image_dir}");
}
// CLI 사용자와 웹서버 사용자가 다르더라도 목록 썸네일을 같은 폴더에 만들 수 있게 한다.
chmod($image_dir, 0777);

function download_demo_image(string $photo_id, string $destination): void
{
    if (is_file($destination) && filesize($destination) > 10000 && @getimagesize($destination)) {
        return;
    }

    $url = 'https://images.unsplash.com/'.$photo_id.'?auto=format&fit=crop&w=900&h=1100&q=82';
    $temporary = $destination.'.download';
    $handle = fopen($temporary, 'wb');
    if (!$handle) {
        throw new RuntimeException("임시 이미지 파일을 열 수 없습니다: {$temporary}");
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_FILE => $handle,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_USERAGENT => 'G5SE fashion demo seeder',
        CURLOPT_FAILONERROR => true,
    ]);
    $success = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    fclose($handle);

    if (!$success || !@getimagesize($temporary)) {
        @unlink($temporary);
        throw new RuntimeException("이미지 다운로드 실패 ({$photo_id}): {$error}");
    }

    rename($temporary, $destination);
    chmod($destination, G5_FILE_PERMISSION);
}

foreach ($images as $key => $photo_id) {
    download_demo_image($photo_id, $image_dir.'/'.$key.'.jpg');
    echo "이미지 준비: {$key}.jpg\n";
}

$sql = " insert into {$item_table}
            (it_id, ca_id, it_name, it_seo_title, it_maker, it_origin, it_brand,
             it_basic, it_explan, it_explan2, it_mobile_explan,
             it_cust_price, it_price, it_point, it_use, it_stock_qty,
             it_sc_type, it_buy_min_qty, it_buy_max_qty,
             it_type1, it_type2, it_type3, it_type4, it_type5,
             it_hit, it_time, it_update_time, it_ip, it_order,
             it_sum_qty, it_use_cnt, it_use_avg, it_img1,
             it_head_html, it_tail_html, it_mobile_head_html, it_mobile_tail_html,
             it_info_value, it_shop_memo)
         values
            (:it_id, :ca_id, :it_name, :it_name, 'G5SE Demo', '대한민국', 'G5SE Fashion',
             :it_basic, :it_explan, :it_explan, :it_explan,
             :it_cust_price, :it_price, :it_point, 1, :it_stock_qty,
             1, 1, 5,
             :it_type1, :it_type2, :it_type3, :it_type4, :it_type5,
             :it_hit, :it_time, :it_time, '127.0.0.1', :it_order,
             :it_sum_qty, :it_use_cnt, :it_use_avg, :it_img1,
             '', '', '', '', '', :it_shop_memo)
         on duplicate key update
            ca_id = values(ca_id), it_name = values(it_name), it_seo_title = values(it_seo_title),
            it_basic = values(it_basic), it_explan = values(it_explan), it_explan2 = values(it_explan2),
            it_mobile_explan = values(it_mobile_explan), it_cust_price = values(it_cust_price),
            it_price = values(it_price), it_point = values(it_point), it_use = 1,
            it_stock_qty = values(it_stock_qty), it_type1 = values(it_type1),
            it_type2 = values(it_type2), it_type3 = values(it_type3),
            it_type4 = values(it_type4), it_type5 = values(it_type5),
            it_hit = values(it_hit), it_update_time = values(it_update_time),
            it_order = values(it_order), it_sum_qty = values(it_sum_qty),
            it_use_cnt = values(it_use_cnt), it_use_avg = values(it_use_avg),
            it_img1 = values(it_img1), it_shop_memo = values(it_shop_memo) ";

$item_number = 0;
$now = new DateTimeImmutable('now');

foreach ($catalog as [$ca_id, $image_key, $names, $prices]) {
    foreach ($names as $variant => $name) {
        $item_number++;
        $price = normalize_demo_price($prices[$variant]);
        $photo_id = $images[$image_key];
        $registered = $now->modify('-'.($item_number * 2).' hours')->format('Y-m-d H:i:s');

        sql_pdo_query($sql, [
            ':it_id' => $id_prefix.str_pad((string) $item_number, 3, '0', STR_PAD_LEFT),
            ':ca_id' => $ca_id,
            ':it_name' => $name,
            ':it_basic' => '패션 테스트몰 UI 확인용 데모 상품',
            ':it_explan' => '<p>색상과 사이즈 선택을 가정한 패션 테스트몰 데모 상품입니다.</p>',
            ':it_cust_price' => min(2000, (int) round($price * 1.18 / 100) * 100),
            ':it_price' => $price,
            ':it_point' => (int) floor($price * 0.01),
            ':it_stock_qty' => 20 + ($item_number % 30),
            ':it_type1' => $item_number % 7 === 0 ? 1 : 0,
            ':it_type2' => $item_number % 5 === 0 ? 1 : 0,
            ':it_type3' => $item_number > 24 ? 1 : 0,
            ':it_type4' => $item_number % 4 === 0 ? 1 : 0,
            ':it_type5' => $item_number % 6 === 0 ? 1 : 0,
            ':it_hit' => 80 + ($item_number * 37),
            ':it_time' => $registered,
            ':it_order' => $item_number * 10,
            ':it_sum_qty' => ($item_number * 13) % 180,
            ':it_use_cnt' => $item_number % 11,
            ':it_use_avg' => number_format(3.8 + (($item_number % 7) * 0.2), 1),
            ':it_img1' => $image_dir_name.'/'.$image_key.'.jpg',
            ':it_shop_memo' => 'Unsplash image: https://images.unsplash.com/'.$photo_id,
        ]);
    }
}

echo "패션 테스트 상품 {$item_number}개를 생성·갱신했습니다.\n";
echo "말단 분류 16개 × 상품 2개, 무료 이미지 ".count($images)."장을 사용했습니다.\n";
