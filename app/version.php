<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 시스템 버전 — 릴리즈 식별용. CSS/JS 캐시 버스터(G5_CSS_VER, G5_JS_VER) 는
// 별도로 app/extend/version.extend.php 에 정의됨.

define('G5_VERSION', '그누보드5');
define('G5SE_VERSION', '0.1.27');
define('G5_GNUBOARD_VER', '5.6.26');
// 그누보드5.4.5.5 버전과 영카트5.4.5.5.1 버전을 합쳐서 그누보드5.4.6 버전에서 시작함 (kagla-210617)
// G5_YOUNGCART_VER 이 상수를 사용하는 곳이 있으므로 주석 처리 해제함
// 그누보드5.4.6 이상 버전 부터는 영카트를 그누보드에 포함하여 배포하므로 영카트5의 버전은 의미가 없습니다.
define('G5_YOUNGCART_VER', '5.4.5.5.1');
