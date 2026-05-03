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

    // 2) 게시판 URL 정리: /board.php?bo_table=X[&wr_id=N][&page=Y&...] → /board/X[/N][?page=Y&...]
    //    매개변수 순서 무관하게 bo_table, wr_id 추출 후 나머지 query string 은 보존.
    //    HTML 안에선 `&` 가 `&amp;` 로 인코딩되므로 parse_str 호출 전 디코드.
    $html = preg_replace_callback(
        '#/board\.php\?([^"\'\s<>]+)#',
        function ($m) {
            $qs = str_replace('&amp;', '&', $m[1]);
            parse_str($qs, $params);
            if (empty($params['bo_table']) || !preg_match('/^[a-zA-Z0-9_]+$/', $params['bo_table'])) {
                return $m[0];
            }
            $url = '/board/'.$params['bo_table'];
            if (!empty($params['wr_id']) && preg_match('/^\d+$/', $params['wr_id'])) {
                $url .= '/'.$params['wr_id'];
                unset($params['wr_id']);
            }
            unset($params['bo_table']);
            if (!empty($params)) {
                $url .= '?' . http_build_query($params, '', '&amp;');
            }
            return $url;
        },
        $html
    );

    // 3) 게시판 액션 URL 정리: /write.php / delete.php / good.php / download.php / view_image.php
    //    매개변수 순서 무관하게 bo_table, wr_id, no, w 추출 후 /board/{bo_table}/{action}[/{wr_id}[/{no}]][?w=X&...] 로 재조립
    $html = preg_replace_callback(
        '#/(write|write_update|delete|good|nogood|download|view_image)\.php\?([^"\'\s<>]+)#',
        function ($m) {
            $action = $m[1];
            $qs = str_replace('&amp;', '&', $m[2]);
            parse_str($qs, $params);
            if (empty($params['bo_table']) || !preg_match('/^[a-zA-Z0-9_]+$/', $params['bo_table'])) {
                return $m[0];   // bo_table 없거나 이상하면 그대로 둠
            }
            $url = '/board/'.$params['bo_table'].'/'.$action;
            if (!empty($params['wr_id']) && preg_match('/^\d+$/', $params['wr_id'])) {
                $url .= '/'.$params['wr_id'];
                unset($params['wr_id']);
            }
            if (!empty($params['no']) && preg_match('/^\d+$/', $params['no'])) {
                $url .= '/'.$params['no'];
                unset($params['no']);
            }
            unset($params['bo_table']);
            // 남은 query 파라미터 (w, sca, sfl, stx, page 등) 보존
            if (!empty($params)) {
                $url .= '?' . http_build_query($params, '', '&amp;');
            }
            return $url;
        },
        $html
    );

    // 4) 댓글 작성/삭제도 마찬가지: /write_comment_update.php, /delete_comment.php
    $html = preg_replace_callback(
        '#/write_comment_update\.php\?bo_table=([a-zA-Z0-9_]+)#',
        fn($m) => '/board/'.$m[1].'/comment',
        $html
    );
    $html = preg_replace_callback(
        '#/delete_comment\.php\?(?:[^"\']*?(?:&|&amp;)?bo_table=([a-zA-Z0-9_]+))(?:[^"\']*?(?:&|&amp;)comment_id=(\d+))?#',
        function ($m) {
            return '/board/'.$m[1].'/comment/delete'.(isset($m[2]) && $m[2] !== '' ? '/'.$m[2] : '');
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
