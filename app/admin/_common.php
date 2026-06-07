<?php
// 모던 admin (/admin) 의 공통 부트스트랩.
// gnuboard 의 /adm 패턴(define G5_IS_ADMIN + require ../common.php) 을 따라
// $config / $member / $is_admin / $g5 등 전역을 그대로 사용 가능하게 한다.
define('G5_IS_ADMIN', true);
require_once __DIR__.'/../common.php';

// admin 페이지는 절대 브라우저 캐시/bfcache 되지 않도록 한다.
// (옛 admin HTML/JS 가 캐시·뒤로가기 복원으로 남아 — 모달이 안 뜨고 옛 링크 동작이
//  되살아나는 문제를 근본 차단. admin 은 동적·사용자별이라 캐시 이득도 없음.)
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}
