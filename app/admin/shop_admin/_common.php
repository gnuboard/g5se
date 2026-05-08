<?php
// 모던 shop_admin (/admin/shop_admin/) 의 공통 부트스트랩.
//   - app/admin/_common.php 와 같은 패턴: G5_IS_ADMIN + ../common.php
//   - 추가로 G5_IS_SHOP_ADMIN_PAGE 정의 + shop 활성화 가드 + admin/shop helper 로드
define('G5_IS_SHOP_ADMIN_PAGE', true);
require_once __DIR__.'/../_common.php';

if (!defined('G5_USE_SHOP') || !G5_USE_SHOP) {
    die('<p>쇼핑몰 설치 후 이용해 주십시오.</p>');
}

require_once __DIR__.'/../admin.lib.php';
require_once __DIR__.'/admin.shop.lib.php';

run_event('admin_common');

if (function_exists('check_order_inicis_tmps')) {
    check_order_inicis_tmps();
}
