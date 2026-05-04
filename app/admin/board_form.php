<?php
/*
 * /admin/board_form — 게시판 추가/수정 폼.
 *
 * board_form 자체는 1500+ 라인의 거대한 폼이라 우선 gnuboard adm/board_form.php 를
 * chdir+require 로 그대로 렌더링하고, 출력 버퍼로 form action 만 클린 URL 로 치환한다.
 * UI 모더나이즈는 후속 작업 (이 wrapper 가 그 자리를 잡아둠).
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

ob_start();
chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/board_form.php';
$html = ob_get_clean();

// form action / 사이드 링크 클린 URL 치환
$html = str_replace('action="./board_form_update.php"', 'action="/admin/board_form_update"', $html);
$html = str_replace('href="./board_list.php',          'href="/admin/board_list',                $html);

echo $html;
