<?php
// 500 — 쇼핑몰 통계/기타 (매출/판매순위/주문출력/SMS/이벤트/배너/보관함/가격비교)
if (!defined('_GNUBOARD_')) exit;
if (!defined('G5_USE_SHOP') || !G5_USE_SHOP) return [];

return [
    'group' => '쇼핑몰 통계/기타',
    'items' => [
        ['key' => 'sst_order_stats',  'code' => '500110', 'label' => '매출현황',        'url' => '/admin/shop_admin/sale1',          'level' => '',
         'icon' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>'],
        ['key' => 'sst_rank',         'code' => '500100', 'label' => '상품판매순위',    'url' => '/admin/shop_admin/itemsellrank',   'level' => '',
         'icon' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
        ['key' => 'sst_print_order',  'code' => '500120', 'label' => '주문내역출력',    'url' => '/admin/shop_admin/orderprint',     'level' => '',
         'icon' => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>'],
        ['key' => 'sst_stock_sms',    'code' => '500400', 'label' => '재입고SMS알림',   'url' => '/admin/shop_admin/itemstocksms',   'level' => '',
         'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
        ['key' => 'scf_event',        'code' => '500300', 'label' => '이벤트관리',      'url' => '/admin/shop_admin/itemevent',      'level' => '',
         'icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
        ['key' => 'scf_event_mng',    'code' => '500310', 'label' => '이벤트일괄처리',  'url' => '/admin/shop_admin/itemeventlist',  'level' => '',
         'icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/>'],
        ['key' => 'scf_banner',       'code' => '500500', 'label' => '배너관리',        'url' => '/admin/shop_admin/bannerlist',     'level' => '',
         'icon' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>'],
        ['key' => 'sst_wish',         'code' => '500140', 'label' => '보관함현황',      'url' => '/admin/shop_admin/wishlist',       'level' => '',
         'icon' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>'],
        ['key' => 'sst_compare',      'code' => '500210', 'label' => '가격비교사이트',  'url' => '/admin/shop_admin/price',          'level' => '',
         'icon' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'],
    ],
];
