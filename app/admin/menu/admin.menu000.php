<?php
// 000 — 대시보드 (가장 위)
// 대시보드 진입과 버전 확인만 노출한다.
if (!defined('_GNUBOARD_')) exit;

$items = [
    ['key' => 'home',           'code' => '000000', 'label' => '대시보드',     'url' => '/admin',              'level' => '',
     'icon' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
    ['key' => 'version_check', 'code' => '100415', 'label' => '버전 확인', 'url' => '/admin/version_check', 'level' => 'super',
     'icon' => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/><path d="M20.49 15a9 9 0 0 1-14.85 3.36L1 14"/>'],
];

return [
    'group' => '대시보드',
    'items' => $items,
];
