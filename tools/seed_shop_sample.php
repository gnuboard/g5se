<?php
declare(strict_types=1);

define('_GNUBOARD_', true);
require_once __DIR__.'/../data/dbconfig.php';

$host = G5_MYSQL_HOST === 'localhost' ? '127.0.0.1' : G5_MYSQL_HOST;
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, G5_MYSQL_DB);
$pdo = new PDO($dsn, G5_MYSQL_USER, G5_MYSQL_PASSWORD, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => true,
]);
$pdo->exec("SET SESSION sql_mode = ''");
$pdo->exec("SET time_zone = '+09:00'");

$categoryTable = G5_SHOP_TABLE_PREFIX.'category';
$itemTable = G5_SHOP_TABLE_PREFIX.'item';
$orderTable = G5_SHOP_TABLE_PREFIX.'order';
$cartTable = G5_SHOP_TABLE_PREFIX.'cart';

$imageDir = realpath(__DIR__.'/../data/item');
if ($imageDir === false) {
    $imageDir = __DIR__.'/../data/item';
    mkdir($imageDir, 0755, true);
}

$font = first_existing_file([
    '/usr/share/fonts/truetype/noto/NotoSansMono-Bold.ttf',
    '/usr/share/fonts/truetype/noto/NotoSansMono-Regular.ttf',
    '/usr/share/fonts/truetype/droid/DroidSansFallbackFull.ttf',
    '/usr/share/fonts/opentype/unifont/unifont.otf',
    '/usr/share/fonts/opentype/ipafont-gothic/ipagp.ttf',
]);

$mainCategories = [
    '디지털', '생활가전', '주방용품', '패션의류', '뷰티케어',
    '식품', '홈인테리어', '스포츠', '문구오피스', '반려용품',
];
$subCategories = [
    ['스마트폰', '태블릿', '노트북', '이어폰', '스마트워치', '카메라', '게임기', '모니터', '저장장치'],
    ['공기청정기', '청소기', '세탁기', '건조기', '선풍기', '히터', '가습기', '제습기', '전기면도기'],
    ['냄비팬', '칼도마', '식기세트', '커피용품', '보관용기', '조리도구', '주방수납', '정수필터', '베이킹'],
    ['티셔츠', '셔츠', '팬츠', '아우터', '니트', '원피스', '운동복', '신발', '가방'],
    ['스킨케어', '클렌징', '선케어', '메이크업', '헤어케어', '바디케어', '향수', '마스크팩', '뷰티툴'],
    ['쌀잡곡', '과일', '채소', '정육', '수산', '간편식', '간식', '음료', '건강식품'],
    ['조명', '침구', '커튼', '러그', '수납장', '의자', '테이블', '디퓨저', '벽장식'],
    ['러닝', '요가', '등산', '자전거', '수영', '골프', '헬스', '캠핑', '구기용품'],
    ['노트', '펜', '파일', '데스크용품', '프린터용품', '스티커', '캘린더', '계산기', '포장재'],
    ['강아지사료', '고양이사료', '간식', '장난감', '배변용품', '미용용품', '하우스', '산책용품', '식기'],
];
$productBases = [
    '디지털' => ['울트라 스마트폰', '라이트 태블릿', '프로 노트북', '노이즈캔슬 이어폰', '헬스 스마트워치', '브이로그 카메라', '포터블 게임기', '와이드 모니터', '고속 SSD', '무선 충전패드'],
    '생활가전' => ['프리미엄 공기청정기', '무선 청소기', '미니 세탁기', '저소음 건조기', '서큘레이터 선풍기', '세라믹 히터', '초음파 가습기', '스마트 제습기', '전기면도기', '핸디 스팀다리미'],
    '주방용품' => ['스테인리스 냄비', '셰프 식도', '도자기 식기세트', '드립 커피포트', '밀폐 보관용기', '실리콘 조리도구', '슬림 주방수납랙', '정수 필터', '오븐 베이킹몰드', '인덕션 프라이팬'],
    '패션의류' => ['코튼 티셔츠', '옥스퍼드 셔츠', '스트레치 팬츠', '라이트 아우터', '캐시미어 니트', '플레어 원피스', '트레이닝 세트', '러닝 스니커즈', '데일리 백팩', '레더 벨트'],
    '뷰티케어' => ['수분 세럼', '마일드 클렌저', '톤업 선크림', '벨벳 립틴트', '단백질 샴푸', '바디 로션', '우디 향수', '진정 마스크팩', '메이크업 브러시', '헤어 에센스'],
    '식품' => ['햅쌀 세트', '프리미엄 사과', '유기농 샐러드', '한우 불고기', '손질 고등어', '즉석 파스타', '그래놀라 바', '콜드브루 커피', '멀티 비타민', '견과 믹스'],
    '홈인테리어' => ['무드 조명', '호텔 침구세트', '암막 커튼', '워셔블 러그', '모듈 수납장', '라운지 의자', '원목 테이블', '플라워 디퓨저', '패브릭 포스터', '스탠드 거울'],
    '스포츠' => ['러닝화', '요가 매트', '등산 배낭', '로드 자전거 헬멧', '수경 세트', '골프 장갑', '덤벨 세트', '캠핑 랜턴', '축구공', '스포츠 물병'],
    '문구오피스' => ['하드커버 노트', '젤 잉크펜', '클리어 파일', '데스크 오거나이저', '프린터 토너', '다이어리 스티커', '탁상 캘린더', '공학 계산기', '크라프트 포장지', '메모 패드'],
    '반려용품' => ['강아지 사료', '고양이 사료', '동결건조 간식', '터그 장난감', '흡수 배변패드', '저자극 샴푸', '쿠션 하우스', '리드줄 세트', '스테인리스 식기', '브러싱 빗'],
];
$palettes = [
    ['#1F6FEB', '#BFD7FF'], ['#0E9F6E', '#B7F0D2'], ['#E11D48', '#FFD0DA'],
    ['#7C3AED', '#DDD0FF'], ['#F59E0B', '#FFE2A8'], ['#0891B2', '#BDEFFF'],
    ['#475569', '#D7DEE8'], ['#DB2777', '#FFD1E8'], ['#16A34A', '#CBF5D6'], ['#EA580C', '#FFD6B8'],
];

$categories = build_categories($mainCategories, $subCategories);
$products = build_products($mainCategories, $productBases, $categories, $palettes);
$orders = build_orders($products);

$pdo->beginTransaction();
try {
    cleanup_seed_data($pdo, $categoryTable, $itemTable, $orderTable, $cartTable);
    insert_categories($pdo, $categoryTable, $categories);
    insert_products($pdo, $itemTable, $products);
    insert_orders($pdo, $orderTable, $cartTable, $orders);
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}

foreach ($products as $product) {
    for ($imageNo = 1; $imageNo <= 3; $imageNo++) {
        create_product_image($imageDir.'/'.$product['it_img'.$imageNo], $product + ['image_variant' => $imageNo], $font);
    }
}

printf(
    "Seed complete: %d categories, %d products, %d orders, %d cart rows, %d images\n",
    count($categories),
    count($products),
    count($orders),
    count($orders),
    count($products) * 3
);

function first_existing_file(array $paths): string
{
    foreach ($paths as $path) {
        if (is_file($path)) {
            return $path;
        }
    }
    return '';
}

function build_categories(array $mainCategories, array $subCategories): array
{
    $rows = [];
    foreach ($mainCategories as $mainIndex => $mainName) {
        $mainId = sprintf('%02d', $mainIndex + 1);
        $rows[] = [
            'ca_id' => $mainId,
            'ca_name' => $mainName,
            'ca_order' => ($mainIndex + 1) * 10,
        ];

        foreach ($subCategories[$mainIndex] as $subIndex => $subName) {
            $rows[] = [
                'ca_id' => $mainId.sprintf('%02d', $subIndex + 1),
                'ca_name' => $subName,
                'ca_order' => ($subIndex + 1) * 10,
            ];
        }
    }
    return $rows;
}

function build_products(array $mainCategories, array $productBases, array $categories, array $palettes): array
{
    $subCategoryIds = array_values(array_filter(array_column($categories, 'ca_id'), static fn($id) => strlen($id) === 4));
    $rows = [];
    $n = 1;
    foreach ($mainCategories as $mainIndex => $mainName) {
        foreach ($productBases[$mainName] as $variantIndex => $baseName) {
            $price = 100 + ((($n * 37) % 90) * 10);
            $name = sprintf('%s %02d', $baseName, $variantIndex + 1);
            $rows[] = [
                'it_id' => sprintf('SEED%06d', $n),
                'ca_id' => $subCategoryIds[($n - 1) % count($subCategoryIds)],
                'it_name' => $name,
                'it_seo_title' => 'seed-'.sprintf('%06d', $n),
                'it_brand' => 'G5SE Sample',
                'it_model' => sprintf('SMP-%03d', $n),
                'it_basic' => $mainName.' 추천 샘플 상품입니다.',
                'it_explan' => '<p>'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').' 상세 설명용 시드 상품입니다.</p>',
                'it_cust_price' => $price + 50,
                'it_price' => $price,
                'it_point' => (int)floor($price * 0.01),
                'it_stock_qty' => 20 + ($n % 80),
                'it_order' => $n,
                'it_img1' => sprintf('seed_item_%03d.png', $n),
                'it_img2' => sprintf('seed_item_%03d_detail.png', $n),
                'it_img3' => sprintf('seed_item_%03d_package.png', $n),
                'main_name' => $mainName,
                'palette' => $palettes[$mainIndex % count($palettes)],
            ];
            $n++;
        }
    }
    return $rows;
}

function build_orders(array $products): array
{
    $names = ['김민준', '이서연', '박지후', '최하윤', '정도윤', '강서준', '윤지아', '장하린', '임도현', '한유진'];
    $statuses = ['주문', '입금', '준비', '배송', '완료'];
    $settles = ['무통장', '신용카드', '계좌이체', '간편결제'];
    $rows = [];
    foreach (range(1, 100) as $i) {
        $product = $products[$i - 1];
        $qty = ($i % 3) + 1;
        $sendCost = $product['it_price'] >= 50000 ? 0 : 3000;
        $cartPrice = $product['it_price'] * $qty;
        $total = $cartPrice + $sendCost;
        $status = $statuses[$i % count($statuses)];
        $time = date('Y-m-d H:i:s', strtotime(sprintf('2026-05-13 12:00:00 -%d hours', $i * 5)));
        $rows[] = [
            'od_id' => 202605130000 + $i,
            'product' => $product,
            'name' => $names[$i % count($names)],
            'email' => sprintf('seed%03d@example.com', $i),
            'tel' => sprintf('010-%04d-%04d', 1000 + $i, 2000 + $i),
            'zip1' => sprintf('%03d', 10 + ($i % 80)),
            'zip2' => sprintf('%03d', 100 + ($i % 700)),
            'addr1' => sprintf('서울특별시 샘플구 시드로 %d', $i),
            'addr2' => sprintf('%d층 %d호', ($i % 20) + 1, 100 + $i),
            'qty' => $qty,
            'send_cost' => $sendCost,
            'cart_price' => $cartPrice,
            'total' => $total,
            'status' => $status,
            'settle' => $settles[$i % count($settles)],
            'time' => $time,
        ];
    }
    return $rows;
}

function cleanup_seed_data(PDO $pdo, string $categoryTable, string $itemTable, string $orderTable, string $cartTable): void
{
    $pdo->exec("DELETE FROM `{$cartTable}` WHERE `od_id` BETWEEN 202605130001 AND 202605130100");
    $pdo->exec("DELETE FROM `{$orderTable}` WHERE `od_id` BETWEEN 202605130001 AND 202605130100");
    $pdo->exec("DELETE FROM `{$itemTable}` WHERE `it_id` LIKE 'SEED%'");
    $pdo->exec("DELETE FROM `{$categoryTable}` WHERE `ca_id` IN ('01','02','03','04','05','06','07','08','09','10') OR `ca_id` BETWEEN '0101' AND '1009'");
}

function insert_categories(PDO $pdo, string $table, array $categories): void
{
    $sql = "INSERT INTO `{$table}` (`ca_id`, `ca_name`, `ca_order`, `ca_skin_dir`, `ca_mobile_skin_dir`, `ca_skin`, `ca_mobile_skin`, `ca_img_width`, `ca_img_height`, `ca_mobile_img_width`, `ca_mobile_img_height`, `ca_use`, `ca_stock_qty`, `ca_list_mod`, `ca_list_row`, `ca_mobile_list_mod`, `ca_mobile_list_row`) VALUES (:ca_id, :ca_name, :ca_order, 'basic', 'basic', 'list.10.skin.php', 'list.10.skin.php', 230, 230, 230, 230, 1, 99999, 4, 5, 2, 5)";
    $stmt = $pdo->prepare($sql);
    foreach ($categories as $row) {
        $stmt->execute($row);
    }
}

function insert_products(PDO $pdo, string $table, array $products): void
{
    $sql = "INSERT INTO `{$table}` (`it_id`, `ca_id`, `it_name`, `it_seo_title`, `it_brand`, `it_model`, `it_type1`, `it_type2`, `it_basic`, `it_explan`, `it_cust_price`, `it_price`, `it_point`, `it_use`, `it_stock_qty`, `it_noti_qty`, `it_sc_type`, `it_sc_method`, `it_sc_price`, `it_buy_min_qty`, `it_buy_max_qty`, `it_time`, `it_update_time`, `it_ip`, `it_order`, `it_info_gubun`, `it_info_value`, `it_use_avg`, `it_img1`, `it_img2`, `it_img3`) VALUES (:it_id, :ca_id, :it_name, :it_seo_title, :it_brand, :it_model, :it_type1, :it_type2, :it_basic, :it_explan, :it_cust_price, :it_price, :it_point, 1, :it_stock_qty, 5, 0, 0, 0, 1, 20, NOW(), NOW(), '127.0.0.1', :it_order, 'etc', '', 0.0, :it_img1, :it_img2, :it_img3)";
    $stmt = $pdo->prepare($sql);
    foreach ($products as $row) {
        $stmt->execute([
            ':it_id' => $row['it_id'],
            ':ca_id' => $row['ca_id'],
            ':it_name' => $row['it_name'],
            ':it_seo_title' => $row['it_seo_title'],
            ':it_brand' => $row['it_brand'],
            ':it_model' => $row['it_model'],
            ':it_type1' => ($row['it_order'] % 2) ? 1 : 0,
            ':it_type2' => ($row['it_order'] % 3) ? 0 : 1,
            ':it_basic' => $row['it_basic'],
            ':it_explan' => $row['it_explan'],
            ':it_cust_price' => $row['it_cust_price'],
            ':it_price' => $row['it_price'],
            ':it_point' => $row['it_point'],
            ':it_stock_qty' => $row['it_stock_qty'],
            ':it_order' => $row['it_order'],
            ':it_img1' => $row['it_img1'],
            ':it_img2' => $row['it_img2'],
            ':it_img3' => $row['it_img3'],
        ]);
    }
}

function insert_orders(PDO $pdo, string $orderTable, string $cartTable, array $orders): void
{
    $orderSql = "INSERT INTO `{$orderTable}` (`od_id`, `mb_id`, `od_name`, `od_email`, `od_tel`, `od_hp`, `od_zip1`, `od_zip2`, `od_addr1`, `od_addr2`, `od_deposit_name`, `od_b_name`, `od_b_tel`, `od_b_hp`, `od_b_zip1`, `od_b_zip2`, `od_b_addr1`, `od_b_addr2`, `od_cart_count`, `od_cart_price`, `od_send_cost`, `od_receipt_price`, `od_misu`, `od_status`, `od_settle_case`, `od_tax_mny`, `od_vat_mny`, `od_delivery_company`, `od_time`, `od_pwd`, `od_ip`) VALUES (:od_id, '', :name, :email, :tel, :tel, :zip1, :zip2, :addr1, :addr2, :name, :name, :tel, :tel, :zip1, :zip2, :addr1, :addr2, 1, :cart_price, :send_cost, :receipt_price, 0, :status, :settle, :tax_mny, :vat_mny, '', :time, 'seed', '127.0.0.1')";
    $cartSql = "INSERT INTO `{$cartTable}` (`od_id`, `mb_id`, `it_id`, `it_name`, `it_sc_type`, `it_sc_method`, `it_sc_price`, `ct_status`, `ct_price`, `ct_point`, `ct_option`, `ct_qty`, `ct_time`, `ct_ip`, `ct_select`, `ct_select_time`) VALUES (:od_id, '', :it_id, :it_name, 0, 0, 0, :status, :price, :point, '기본', :qty, :time, '127.0.0.1', 1, :time)";
    $orderStmt = $pdo->prepare($orderSql);
    $cartStmt = $pdo->prepare($cartSql);

    foreach ($orders as $row) {
        $taxMny = (int)floor($row['cart_price'] / 1.1);
        $vatMny = $row['cart_price'] - $taxMny;
        $orderStmt->execute([
            ':od_id' => $row['od_id'],
            ':name' => $row['name'],
            ':email' => $row['email'],
            ':tel' => $row['tel'],
            ':zip1' => $row['zip1'],
            ':zip2' => $row['zip2'],
            ':addr1' => $row['addr1'],
            ':addr2' => $row['addr2'],
            ':cart_price' => $row['cart_price'],
            ':send_cost' => $row['send_cost'],
            ':receipt_price' => $row['total'],
            ':status' => $row['status'],
            ':settle' => $row['settle'],
            ':tax_mny' => $taxMny,
            ':vat_mny' => $vatMny,
            ':time' => $row['time'],
        ]);
        $cartStmt->execute([
            ':od_id' => $row['od_id'],
            ':it_id' => $row['product']['it_id'],
            ':it_name' => $row['product']['it_name'],
            ':status' => $row['status'],
            ':price' => $row['product']['it_price'],
            ':point' => $row['product']['it_point'],
            ':qty' => $row['qty'],
            ':time' => $row['time'],
        ]);
    }
}

function create_product_image(string $path, array $product, string $font): void
{
    $size = 800;
    $image = imagecreatetruecolor($size, $size);
    [$primary, $soft] = $product['palette'];
    $variant = (int)($product['image_variant'] ?? 1);
    if ($variant === 2) {
        [$primary, $soft] = ['#0F766E', '#CCFBF1'];
    } elseif ($variant === 3) {
        [$primary, $soft] = ['#A21CAF', '#F5D0FE'];
    }

    fill_rect($image, 0, 0, $size, $size, '#F8FAFC');
    fill_rect($image, 0, 0, $size, 280, $soft);
    fill_rect($image, 0, 640, $size, 160, '#FFFFFF');
    draw_shadow_card($image, 70, 70, 660, 580);
    fill_rect($image, 94, 94, 612, 532, '#FFFFFF');
    fill_rect($image, 94, 94, 612, 88, $primary);
    draw_centered_text($image, category_label($product['main_name']).' VIEW '.$variant, $font, 24, 150, '#FFFFFF', 560);
    draw_product_illustration($image, $product, $primary, $soft);
    if ($variant === 2) {
        draw_detail_marks($image, $primary);
    } elseif ($variant === 3) {
        draw_package_frame($image, $primary);
    }
    draw_centered_text($image, $product['it_model'], $font, 30, 610, '#111827', 620);
    draw_centered_text($image, number_format((int)$product['it_price']).' KRW', $font, 30, 686, $primary, 560);
    if (!imagepng($image, $path, 9)) {
        imagedestroy($image);
        throw new RuntimeException('Failed to write product image: '.$path);
    }
    imagedestroy($image);
}

function draw_detail_marks(GdImage $image, string $hex): void
{
    $line = color($image, $hex);
    imagesetthickness($image, 4);
    imageline($image, 190, 250, 290, 310, $line);
    imageline($image, 610, 250, 510, 310, $line);
    imageline($image, 190, 475, 300, 420, $line);
    imageline($image, 610, 475, 500, 420, $line);
    imagesetthickness($image, 1);
}

function draw_package_frame(GdImage $image, string $hex): void
{
    $line = color($image, $hex);
    imagesetthickness($image, 5);
    imagerectangle($image, 150, 220, 650, 520, $line);
    imageline($image, 150, 220, 250, 170, $line);
    imageline($image, 650, 220, 550, 170, $line);
    imageline($image, 250, 170, 550, 170, $line);
    imagesetthickness($image, 1);
}

function category_label(string $category): string
{
    return [
        '디지털' => 'DIGITAL',
        '생활가전' => 'APPLIANCE',
        '주방용품' => 'KITCHEN',
        '패션의류' => 'FASHION',
        '뷰티케어' => 'BEAUTY',
        '식품' => 'FOOD',
        '홈인테리어' => 'HOME',
        '스포츠' => 'SPORTS',
        '문구오피스' => 'OFFICE',
        '반려용품' => 'PET',
    ][$category] ?? 'PRODUCT';
}

function draw_shadow_card(GdImage $image, int $x, int $y, int $width, int $height): void
{
    fill_rect($image, $x + 10, $y + 14, $width, $height, '#D7DEE8');
    fill_rect($image, $x, $y, $width, $height, '#FFFFFF');
}

function draw_product_illustration(GdImage $image, array $product, string $color, string $soft): void
{
    $name = $product['it_name'];
    $category = $product['main_name'];
    $black = color($image, '#0F172A');
    $primary = color($image, $color);
    $light = color($image, $soft);
    imagefilledellipse($image, 400, 360, 360, 270, color($image, '#F1F5F9'));
    imagefilledellipse($image, 400, 360, 300, 220, $light);
    imagesetthickness($image, 10);

    if (str_contains($name, '스마트폰')) {
        draw_device($image, 330, 235, 140, 250, $primary, $black, true);
    } elseif (str_contains($name, '태블릿')) {
        draw_device($image, 290, 260, 220, 170, $primary, $black, true);
    } elseif (str_contains($name, '노트북')) {
        draw_laptop($image, $primary, $black);
    } elseif (str_contains($name, '이어폰')) {
        draw_earbuds($image, $primary, $black);
    } elseif (str_contains($name, '스마트워치')) {
        draw_watch($image, $primary, $black);
    } elseif (str_contains($name, '카메라')) {
        draw_camera($image, $primary, $black);
    } elseif (str_contains($name, '모니터')) {
        draw_monitor($image, $primary, $black);
    } elseif (str_contains($name, '신발') || str_contains($name, '러닝화') || str_contains($name, '스니커즈')) {
        draw_shoe($image, $primary, $black);
    } elseif (str_contains($name, '가방') || str_contains($name, '백팩') || str_contains($name, '배낭')) {
        draw_bag($image, $primary, $black);
    } elseif (str_contains($name, '사료') || str_contains($name, '간식')) {
        draw_package($image, $primary, $black, 'PET');
    } elseif ($category === '식품') {
        draw_food($image, $primary, $black);
    } elseif ($category === '뷰티케어') {
        draw_bottle($image, $primary, $black);
    } elseif ($category === '주방용품') {
        draw_pan($image, $primary, $black);
    } elseif ($category === '생활가전') {
        draw_appliance($image, $primary, $black);
    } elseif ($category === '패션의류') {
        draw_clothes($image, $primary, $black);
    } elseif ($category === '홈인테리어') {
        draw_home($image, $primary, $black);
    } elseif ($category === '스포츠') {
        draw_sports($image, $primary, $black);
    } elseif ($category === '문구오피스') {
        draw_stationery($image, $primary, $black);
    } else {
        draw_package($image, $primary, $black, 'NEW');
    }
    imagesetthickness($image, 1);
}

function draw_device(GdImage $image, int $x, int $y, int $width, int $height, int $primary, int $black, bool $button): void
{
    imagefilledrectangle($image, $x, $y, $x + $width, $y + $height, $black);
    imagefilledrectangle($image, $x + 14, $y + 18, $x + $width - 14, $y + $height - 24, $primary);
    imagefilledrectangle($image, $x + 26, $y + 36, $x + $width - 26, $y + $height - 76, color($image, '#DBEAFE'));
    if ($button) imagefilledellipse($image, $x + (int)($width / 2), $y + $height - 42, 18, 18, color($image, '#FFFFFF'));
}

function draw_laptop(GdImage $image, int $primary, int $black): void
{
    imagefilledrectangle($image, 265, 250, 535, 420, $black);
    imagefilledrectangle($image, 285, 270, 515, 400, $primary);
    imagefilledrectangle($image, 230, 430, 570, 465, $black);
    imagefilledrectangle($image, 350, 440, 450, 452, color($image, '#CBD5E1'));
}

function draw_earbuds(GdImage $image, int $primary, int $black): void
{
    imagefilledellipse($image, 330, 310, 88, 108, $black);
    imagefilledellipse($image, 470, 310, 88, 108, $black);
    imagefilledellipse($image, 330, 310, 52, 68, $primary);
    imagefilledellipse($image, 470, 310, 52, 68, $primary);
    imagefilledrectangle($image, 314, 360, 346, 465, $black);
    imagefilledrectangle($image, 454, 360, 486, 465, $black);
}

function draw_watch(GdImage $image, int $primary, int $black): void
{
    imagefilledrectangle($image, 360, 220, 440, 280, $black);
    imagefilledrectangle($image, 360, 440, 440, 500, $black);
    imagefilledrectangle($image, 310, 270, 490, 455, $black);
    imagefilledrectangle($image, 335, 295, 465, 430, $primary);
}

function draw_camera(GdImage $image, int $primary, int $black): void
{
    imagefilledrectangle($image, 275, 285, 525, 445, $black);
    imagefilledrectangle($image, 310, 250, 405, 295, $black);
    imagefilledrectangle($image, 295, 305, 505, 425, $primary);
    imagefilledellipse($image, 400, 365, 110, 110, $black);
    imagefilledellipse($image, 400, 365, 66, 66, color($image, '#E0F2FE'));
}

function draw_monitor(GdImage $image, int $primary, int $black): void
{
    imagefilledrectangle($image, 250, 245, 550, 415, $black);
    imagefilledrectangle($image, 272, 268, 528, 390, $primary);
    imagefilledrectangle($image, 380, 420, 420, 465, $black);
    imagefilledrectangle($image, 320, 465, 480, 485, $black);
}

function draw_shoe(GdImage $image, int $primary, int $black): void
{
    imagefilledpolygon($image, [245,420, 320,335, 470,385, 555,430, 540,465, 275,465], $primary);
    imageline($image, 310, 390, 470, 430, $black);
    imageline($image, 285, 468, 545, 468, $black);
}

function draw_bag(GdImage $image, int $primary, int $black): void
{
    imagearc($image, 400, 300, 150, 120, 180, 360, $black);
    imagefilledrectangle($image, 290, 315, 510, 475, $primary);
    imagerectangle($image, 290, 315, 510, 475, $black);
    imagefilledrectangle($image, 370, 345, 430, 395, color($image, '#FFFFFF'));
}

function draw_package(GdImage $image, int $primary, int $black, string $label): void
{
    imagefilledrectangle($image, 300, 250, 500, 475, $primary);
    imagerectangle($image, 300, 250, 500, 475, $black);
    imagefilledrectangle($image, 330, 305, 470, 390, color($image, '#FFFFFF'));
    imagestring($image, 5, 372, 338, $label, $black);
}

function draw_food(GdImage $image, int $primary, int $black): void
{
    imagefilledellipse($image, 400, 385, 250, 110, color($image, '#FFFFFF'));
    imagearc($image, 400, 385, 250, 110, 0, 360, $black);
    imagefilledellipse($image, 350, 350, 70, 70, $primary);
    imagefilledellipse($image, 430, 345, 60, 60, color($image, '#FDE68A'));
    imageline($image, 290, 460, 510, 460, $black);
}

function draw_bottle(GdImage $image, int $primary, int $black): void
{
    imagefilledrectangle($image, 360, 235, 440, 285, $black);
    imagefilledrectangle($image, 330, 285, 470, 480, $primary);
    imagefilledrectangle($image, 350, 330, 450, 405, color($image, '#FFFFFF'));
    imagerectangle($image, 330, 285, 470, 480, $black);
}

function draw_pan(GdImage $image, int $primary, int $black): void
{
    imagefilledellipse($image, 360, 365, 190, 115, $primary);
    imagearc($image, 360, 365, 190, 115, 0, 360, $black);
    imageline($image, 445, 365, 555, 315, $black);
    imageline($image, 455, 385, 565, 335, $black);
}

function draw_appliance(GdImage $image, int $primary, int $black): void
{
    imagefilledrectangle($image, 315, 245, 485, 485, $primary);
    imagerectangle($image, 315, 245, 485, 485, $black);
    imagefilledellipse($image, 400, 365, 105, 105, color($image, '#FFFFFF'));
    imagearc($image, 400, 365, 105, 105, 0, 360, $black);
}

function draw_clothes(GdImage $image, int $primary, int $black): void
{
    imagefilledpolygon($image, [330,260, 380,225, 420,225, 470,260, 505,330, 455,350, 455,480, 345,480, 345,350, 295,330], $primary);
    imageline($image, 380, 225, 400, 275, $black);
    imageline($image, 420, 225, 400, 275, $black);
}

function draw_home(GdImage $image, int $primary, int $black): void
{
    imagefilledrectangle($image, 310, 330, 490, 475, $primary);
    imagefilledpolygon($image, [285,330, 400,240, 515,330], $black);
    imagefilledrectangle($image, 375, 385, 425, 475, color($image, '#FFFFFF'));
}

function draw_sports(GdImage $image, int $primary, int $black): void
{
    imagefilledellipse($image, 400, 365, 210, 210, $primary);
    imagearc($image, 400, 365, 210, 210, 0, 360, $black);
    imagearc($image, 400, 365, 130, 210, 80, 280, $black);
    imageline($image, 300, 365, 500, 365, $black);
}

function draw_stationery(GdImage $image, int $primary, int $black): void
{
    imagefilledrectangle($image, 305, 260, 455, 480, color($image, '#FFFFFF'));
    imagerectangle($image, 305, 260, 455, 480, $black);
    imagefilledrectangle($image, 305, 260, 455, 315, $primary);
    imagefilledpolygon($image, [485,270, 535,300, 405,480, 355,450], $primary);
    imageline($image, 485, 270, 355, 450, $black);
}

function draw_centered_text(GdImage $image, string $text, string $font, int $fontSize, int $y, string $hex, int $maxWidth = 680): void
{
    $lines = wrap_text($text, $font, $fontSize, $maxWidth);
    $lineHeight = $fontSize + 12;
    $startY = $y - (int)((count($lines) - 1) * $lineHeight / 2);
    foreach ($lines as $index => $line) {
        $box = imagettfbbox($fontSize, 0, $font, $line);
        $width = $box[2] - $box[0];
        imagettftext($image, $fontSize, 0, (int)((800 - $width) / 2), $startY + ($index * $lineHeight), color($image, $hex), $font, $line);
    }
}

function wrap_text(string $text, string $font, int $fontSize, int $maxWidth): array
{
    if ($font === '') {
        return [$text];
    }
    $words = preg_split('/\s+/u', $text) ?: [$text];
    $lines = [];
    $line = '';
    foreach ($words as $word) {
        $candidate = $line === '' ? $word : $line.' '.$word;
        $box = imagettfbbox($fontSize, 0, $font, $candidate);
        if (($box[2] - $box[0]) <= $maxWidth || $line === '') {
            $line = $candidate;
            continue;
        }
        $lines[] = $line;
        $line = $word;
    }
    if ($line !== '') {
        $lines[] = $line;
    }
    return $lines;
}

function fill_rect(GdImage $image, int $x, int $y, int $width, int $height, string $hex): void
{
    imagefilledrectangle($image, $x, $y, $x + $width, $y + $height, color($image, $hex));
}

function color(GdImage $image, string $hex): int
{
    $hex = ltrim($hex, '#');
    return imagecolorallocate(
        $image,
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    );
}
