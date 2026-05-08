<?php
// 400 — 쇼핑몰 1/2 (현황/설정/주문/분류/상품/문의/후기/재고/유형/옵션/쿠폰/배송비/미완주문)
if (!defined('_GNUBOARD_')) exit;
if (!defined('G5_USE_SHOP') || !G5_USE_SHOP) return [];

return [
    'group' => '쇼핑몰',
    'items' => [
        ['key' => 'shop_index',     'code' => '400010', 'label' => '쇼핑몰현황',      'url' => '/admin/shop_admin/',                  'level' => '',
         'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>'],
        ['key' => 'scf_config',     'code' => '400100', 'label' => '쇼핑몰설정',      'url' => '/admin/shop_admin/configform',        'level' => 'super',
         'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
        ['key' => 'scf_order',      'code' => '400400', 'label' => '주문내역',        'url' => '/admin/shop_admin/orderlist',         'level' => '',
         'icon' => '<path d="M9 11H7v6h2v-6zm4 0h-2v6h2v-6zm4 0h-2v6h2v-6z"/><path d="M3 7h18l-1.68 12.39A2 2 0 0 1 17.34 21H6.66a2 2 0 0 1-1.98-1.61L3 7z"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>'],
        ['key' => 'scf_personalpay','code' => '400440', 'label' => '개인결제관리',    'url' => '/admin/shop_admin/personalpaylist',   'level' => '',
         'icon' => '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>'],
        ['key' => 'scf_cate',       'code' => '400200', 'label' => '분류관리',        'url' => '/admin/shop_admin/categorylist',      'level' => '',
         'icon' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>'],
        ['key' => 'scf_item',       'code' => '400300', 'label' => '상품관리',        'url' => '/admin/shop_admin/itemlist',          'level' => '',
         'icon' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>'],
        ['key' => 'scf_item_qna',   'code' => '400660', 'label' => '상품문의',        'url' => '/admin/shop_admin/itemqalist',        'level' => '',
         'icon' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>'],
        ['key' => 'scf_ps',         'code' => '400650', 'label' => '사용후기',        'url' => '/admin/shop_admin/itemuselist',       'level' => '',
         'icon' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
        ['key' => 'scf_item_stock', 'code' => '400620', 'label' => '상품재고관리',    'url' => '/admin/shop_admin/itemstocklist',     'level' => '',
         'icon' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
        ['key' => 'scf_item_type',  'code' => '400610', 'label' => '상품유형관리',    'url' => '/admin/shop_admin/itemtypelist',      'level' => '',
         'icon' => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>'],
        ['key' => 'scf_item_option','code' => '400500', 'label' => '상품옵션재고',    'url' => '/admin/shop_admin/optionstocklist',   'level' => '',
         'icon' => '<line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>'],
        ['key' => 'scf_coupon',     'code' => '400800', 'label' => '쿠폰관리',        'url' => '/admin/shop_admin/couponlist',        'level' => '',
         'icon' => '<path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>'],
        ['key' => 'scf_coupon_zone','code' => '400810', 'label' => '쿠폰존관리',      'url' => '/admin/shop_admin/couponzonelist',    'level' => '',
         'icon' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6v6H9z"/>'],
        ['key' => 'scf_sendcost',   'code' => '400750', 'label' => '추가배송비관리',  'url' => '/admin/shop_admin/sendcostlist',      'level' => '',
         'icon' => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
        ['key' => 'scf_inorder',    'code' => '400410', 'label' => '미완료주문',      'url' => '/admin/shop_admin/inorderlist',       'level' => '',
         'icon' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
    ],
];
