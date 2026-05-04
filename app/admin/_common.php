<?php
// 모던 admin (/admin) 의 공통 부트스트랩.
// gnuboard 의 /adm 패턴(define G5_IS_ADMIN + require ../common.php) 을 따라
// $config / $member / $is_admin / $g5 등 전역을 그대로 사용 가능하게 한다.
define('G5_IS_ADMIN', true);
require_once __DIR__.'/../common.php';
