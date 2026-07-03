<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

run_event('pre_head');

// 테마는 항상 정의됨(cf_theme 폴백, common.php) — 테마 head 로 위임.
require_once(G5_THEME_PATH.'/head.php');
