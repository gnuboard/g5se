<?php
/*
 * /admin/config_form — 사이트 기본 환경설정.
 *
 * 1854 라인 거대 폼이라 우선 gnuboard adm/config_form.php 를 chdir+require 로
 * 그대로 렌더하고 form action 만 클린 URL 로 치환. UI 모더나이즈는 후속 작업.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

ob_start();
chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/config_form.php';
$html = ob_get_clean();

// form 태그에 action 이 없는 채로 출력되므로 (gnuboard 가 self-post 가정) 클린 URL 로 채워준다.
$html = str_replace(
    '<form name="fconfigform" id="fconfigform" method="post"',
    '<form name="fconfigform" id="fconfigform" method="post" action="/admin/config_form_update"',
    $html
);

echo $html;
