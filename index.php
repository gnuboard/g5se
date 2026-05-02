<?php
// Front controller — gnu5se
// 모든 요청을 받아 라우터로 위임한다.
// 주의: gnuboard 의 require 는 반드시 *글로벌 스코프* 에서 호출해야 한다.
//       (메서드/함수 안에서 require 하면 $g5 등 전역 변수가 로컬에 갇혀 DB 연결을 잃는다.)

define('_GNUBOARD_', true);
define('G5_PATH', __DIR__.'/app');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = preg_replace('/[^a-zA-Z0-9\.\-:]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
define('G5_URL', $scheme.'://'.$host);

// gnuboard 의 *_BBS_URL 들이 자동으로 '/bbs' 를 붙이지 못하도록 미리 박는다.
define('G5_BBS_URL',        G5_URL);
define('G5_HTTP_BBS_URL',   G5_URL);
define('G5_HTTPS_BBS_URL',  G5_URL);

// data/ 를 app/ 밖(docroot 직속)으로 분리했으므로 PATH/URL 도 그쪽을 가리키도록 미리 박는다.
define('G5_DATA_PATH',      __DIR__.'/data');
define('G5_DATA_URL',       G5_URL.'/data');

require G5_PATH.'/router.php';

// 출력버퍼 필터: 모던화 완료된 엔드포인트의 `.php` 접미사를 자동 제거 + 게시판 URL 정리.
// gnuboard 내부 코드가 G5_BBS_URL.'/login_check.php' 형태로 URL 을 조립하므로,
// 최종 HTML 송출 직전에 일괄 치환해서 클린 URL 로 노출한다.
ob_start(function ($html) {
    static $clean_endpoints = [
        'login', 'login_check', 'logout',
        'register', 'register_form', 'register_form_update', 'register_result',
        'member_confirm', 'member_leave',
        'password', 'password_check',
        'password_lost', 'password_lost_certify', 'password_lost2',
        'password_reset', 'password_reset_update',
    ];
    // 1) 회원/인증 엔드포인트 .php 제거: (/login).php → /login
    $pattern = '#(/(?:'.implode('|', $clean_endpoints).'))\.php(?![a-zA-Z0-9])#';
    $html = preg_replace($pattern, '$1', $html);

    // 2) 게시판 URL 정리: /board.php?bo_table=X[&wr_id=N] → /board/X[/N]
    //    - bo_table 은 영문/숫자/_ 만, wr_id 는 숫자만 (라우터 패턴과 일치)
    //    - HTML 안에선 `&` 가 `&amp;` 로 인코딩되므로 둘 다 처리
    $html = preg_replace_callback(
        '#/board\.php\?bo_table=([a-zA-Z0-9_]+)(?:(?:&|&amp;)wr_id=(\d+))?#',
        function ($m) {
            return '/board/'.$m[1].(isset($m[2]) && $m[2] !== '' ? '/'.$m[2] : '');
        },
        $html
    );
    return $html;
});

$_route_target = (new Router())->resolve($_SERVER['REQUEST_URI']);

if ($_route_target === null) {
    http_response_code(404);
    echo "404 — no route";
    exit;
}

$_route_full = G5_PATH.'/'.$_route_target;
if (!is_file($_route_full)) {
    http_response_code(500);
    echo "Router target not found: $_route_target";
    exit;
}

// gnuboard 의 './_common.php' 같은 상대 include 가 동작하도록 CWD 를 맞춘다.
chdir(dirname($_route_full));

// 글로벌 스코프 require (반드시!)
require $_route_full;
