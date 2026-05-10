<?php
// 000 — 대시보드 (가장 위)
// 현황/모니터링 단축 묶음. 접속자·게시판·쇼핑몰 통계는 원래 그룹에도 그대로 노출 (참조).
// 버전 확인은 환경설정에서 이쪽으로 이동 — g5se 차별 기능이라 진입 화면에서 노출.
if (!defined('_GNUBOARD_')) exit;

$items = [
    ['key' => 'home',           'code' => '000000', 'label' => '대시보드',     'url' => '/admin',              'level' => '',
     'icon' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
    ['key' => 'mb_connect',     'code' => '200999', 'label' => '현재접속자',   'url' => '/admin/connect_list', 'level' => 'super',
     'icon' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>'],
    ['key' => 'mb_visit',       'code' => '200800', 'label' => '접속자집계',   'url' => '/admin/visit_list',   'level' => 'super',
     'icon' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>'],
    ['key' => 'scf_writecount', 'code' => '300820', 'label' => '글,댓글 현황', 'url' => '/admin/write_count',  'level' => 'super',
     'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>'],
];

if (defined('G5_USE_SHOP') && G5_USE_SHOP) {
    $items[] = ['key' => 'sst_order_stats', 'code' => '500110', 'label' => '매출현황', 'url' => '/admin/shop_admin/sale1', 'level' => '',
                'icon' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>'];
}

$items[] = ['key' => 'version_check', 'code' => '100415', 'label' => '버전 확인', 'url' => '/admin/version_check', 'level' => 'super',
            'icon' => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/><path d="M20.49 15a9 9 0 0 1-14.85 3.36L1 14"/>'];

return [
    'group' => '대시보드',
    'items' => $items,
];
