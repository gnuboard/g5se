<?php
/*
 * /admin/board_form — 게시판 추가/수정 폼.
 *
 * 1500+ 라인 거대 폼이라 gnuboard adm/board_form.php 를 _legacy_passthrough 로
 * modern shell 안에 추출. UI 는 modern shell + 레거시 폼 클래스가 컴포넌트 레이어를 통해
 * 모던 톤으로 렌더링.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

$legacy_target   = 'board_form.php';
$legacy_menu_key = 'bbs_board';
require __DIR__.'/_legacy_passthrough.php';
