<?php
// app/router.php — URL → 기존 gnuboard 진입점 경로 해석만 담당.
// 실제 require 는 글로벌 스코프(index.php)에서 수행해야 한다.
// (gnuboard 의 $g5, $member, $is_member 등 수십 개의 전역 변수가
//  require 가 호출된 스코프에 남기 때문에 함수 내부에서 require 하면 망가진다.)

class Router
{
    /**
     * 모던화 완료된 엔드포인트 — `.php` 변형으로 들어오면 클린 URL 로 301 리다이렉트.
     * 키: 클린 URL, 값: gnuboard 진입점 (G5_PATH 기준 상대경로)
     */
    private $cleanRoutes = [
        '/'                       => 'index.php',
        '/login'                  => 'bbs/login.php',
        '/login_check'            => 'bbs/login_check.php',
        '/logout'                 => 'bbs/logout.php',
        '/register'               => 'bbs/register.php',
        '/register_form'          => 'bbs/register_form.php',
        '/register_form_update'   => 'bbs/register_form_update.php',
        '/register_result'        => 'bbs/register_result.php',

        // 회원정보 수정 / 비밀번호 확인 / 비밀번호 찾기 플로우
        '/member_confirm'         => 'bbs/member_confirm.php',
        '/password'               => 'bbs/password.php',
        '/password_check'         => 'bbs/password_check.php',
        '/password_lost'          => 'bbs/password_lost.php',
        '/password_lost_certify'  => 'bbs/password_lost_certify.php',
        '/password_lost2'         => 'bbs/password_lost2.php',
        '/password_reset'         => 'bbs/password_reset.php',
        '/password_reset_update'  => 'bbs/password_reset_update.php',

        // 회원 탈퇴
        '/member_leave'           => 'bbs/member_leave.php',

        // 게시판 부가 페이지
        '/search'                 => 'bbs/search.php',
        '/new'                    => 'bbs/new.php',
        '/faq'                    => 'bbs/faq.php',
        '/content'                => 'bbs/content.php',
        '/group'                  => 'bbs/group.php',

        // 쪽지 (popup)
        '/memo'                   => 'bbs/memo.php',
        '/memo_form'              => 'bbs/memo_form.php',
        '/memo_form_update'       => 'bbs/memo_form_update.php',
        '/memo_view'              => 'bbs/memo_view.php',
        '/memo_delete'            => 'bbs/memo_delete.php',
    ];

    /** 디버그/유틸 라우트 (정규식 기반) */
    private $extraRoutes = [
        '#^/_debug/?$#' => '_debug.php',

        // AJAX 엔드포인트 일괄 매핑 — bbs/ajax.* 파일들이 직접 URL 로 호출됨
        // (jquery.register_form.js 등이 g5_bbs_url+"/ajax.mb_id.php" 형태로 요청)
        '#^/(ajax\.[a-z0-9_.]+\.php)$#i' => 'bbs/{1}',

        // 글쓰기 토큰 발급 (common.js 의 form[name=fwrite] submit 시 호출)
        '#^/write_token\.php$#' => 'bbs/write_token.php',

        // /bbs/*.php 직접 passthrough — gnuboard 내부에서
        // `https_url(G5_BBS_DIR).'/write_update.php'` 형태로 form action 을 만들고
        // POST 가 들어오므로 (GET 이면 legacy redirect 로 클린 URL 변환됨) 우리는
        // 단순히 bbs/{name}.php 로 라우팅한다.
        '#^/bbs/([a-z0-9_]+\.php)$#i' => 'bbs/{1}',

        // 게시판 — /board/{bo_table}[/{wr_id}] + 액션
        // bo_table 은 영문/숫자/_ 만 허용 (gnuboard 표준)
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/?$#'                                     => 'bbs/board.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/(?P<wr_id>\d+)/?$#'                      => 'bbs/board.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/write/?$#'                               => 'bbs/write.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/write/(?P<wr_id>\d+)/?$#'                => 'bbs/write.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/write_update/?$#'                        => 'bbs/write_update.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/delete/(?P<wr_id>\d+)/?$#'               => 'bbs/delete.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/comment/?$#'                             => 'bbs/write_comment_update.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/comment/delete/(?P<comment_id>\d+)/?$#'  => 'bbs/delete_comment.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/good/(?P<wr_id>\d+)/?$#'                 => 'bbs/good.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/download/(?P<wr_id>\d+)/(?P<no>\d+)/?$#' => 'bbs/download.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/view_image/(?P<wr_id>\d+)/(?P<no>\d+)/?$#' => 'bbs/view_image.php',
    ];

    /**
     * @return string|null G5_PATH 기준 상대경로, 매칭 안되면 null
     */
    public function resolve($requestUri)
    {
        $path   = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // 1) 클린 URL 직접 매칭 (trailing slash 정규화)
        $normalized = ($path !== '/') ? rtrim($path, '/') : '/';
        if (isset($this->cleanRoutes[$normalized])) {
            return $this->cleanRoutes[$normalized];
        }

        // 2) 게시판 레거시 URL → 클린 URL 301 리다이렉트
        //    /bbs/board.php?bo_table=X[&wr_id=N]   → /board/X[/N]
        //    /board.php?bo_table=X...               → /board/X[/N]
        //    /(bbs/)?write.php?bo_table=X[&wr_id=N] → /board/X/write[/N]
        //    delete/good/nogood/download/view_image 도 동일한 규칙
        //    (단, GET/HEAD 만 — POST 는 그대로 통과시켜 폼 제출 호환)
        if (($method === 'GET' || $method === 'HEAD')
            && preg_match('#^/(?:bbs/)?(board|write|write_update|delete|good|nogood|download|view_image)\.php$#', $path, $m)) {
            $action = $m[1];
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            if (!empty($params['bo_table']) && preg_match('/^[a-zA-Z0-9_]+$/', $params['bo_table'])) {
                $url = '/board/'.$params['bo_table'];
                if ($action !== 'board') {
                    $url .= '/'.$action;
                }
                if (!empty($params['wr_id']) && preg_match('/^\d+$/', $params['wr_id'])) {
                    $url .= '/'.$params['wr_id'];
                }
                if (!empty($params['no']) && preg_match('/^\d+$/', $params['no'])) {
                    $url .= '/'.$params['no'];
                }
                unset($params['bo_table'], $params['wr_id'], $params['no']);
                if (!empty($params)) {
                    $url .= '?'.http_build_query($params);
                }
                header('Location: '.$url, true, 301);
                exit;
            }
        }

        // 3) `.php` 접미사로 들어왔으면 클린 URL 로 301 리다이렉트
        //    (단, GET/HEAD 만 — POST 폼이 .php 로 날아오면 데이터 유실 방지 위해 그대로 처리)
        //    (a) /name.php  → /name
        //    (b) /bbs/name.php → /name (gnuboard 내부 링크 정리용)
        $cleanCandidate = null;
        if (preg_match('#^(/[a-zA-Z0-9_]+)\.php$#', $path, $m)) {
            $cleanCandidate = $m[1];
        } else if (preg_match('#^/bbs/([a-zA-Z0-9_]+)\.php$#', $path, $m)) {
            $cleanCandidate = '/'.$m[1];
        }
        if ($cleanCandidate !== null && isset($this->cleanRoutes[$cleanCandidate])) {
            if ($method === 'GET' || $method === 'HEAD') {
                $qs = parse_url($requestUri, PHP_URL_QUERY);
                header('Location: '.$cleanCandidate.($qs ? '?'.$qs : ''), true, 301);
                exit;
            }
            // POST 등은 그대로 진행 (폼 제출 호환)
            return $this->cleanRoutes[$cleanCandidate];
        }

        // 4) 정규식 기반 라우트 (디버그/AJAX 등)
        foreach ($this->extraRoutes as $pattern => $target) {
            if (preg_match($pattern, $path, $m)) {
                // 캡처 그룹을 target 의 {N} placeholder 로 치환 ({1}, {2}, ...)
                $resolved = preg_replace_callback('/\{(\d+)\}/', function ($ph) use ($m) {
                    return isset($m[(int)$ph[1]]) ? $m[(int)$ph[1]] : '';
                }, $target);
                // 명명 그룹은 $_GET 에 주입
                foreach ($m as $k => $v) {
                    if (!is_int($k)) {
                        $_GET[$k] = $v;
                        $_REQUEST[$k] = $v;
                    }
                }
                return $resolved;
            }
        }

        return null;
    }
}
