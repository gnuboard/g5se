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
        '/mypage'                 => 'mypage.php',
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
        '/connect'                => 'bbs/current_connect.php',

        // 쪽지 (popup)
        '/memo'                   => 'bbs/memo.php',
        '/memo_form'              => 'bbs/memo_form.php',
        '/memo_form_update'       => 'bbs/memo_form_update.php',
        '/memo_view'              => 'bbs/memo_view.php',
        '/memo_delete'            => 'bbs/memo_delete.php',

        // 메일보내기 (popup)
        '/formmail'               => 'bbs/formmail.php',
        '/formmail_send'          => 'bbs/formmail_send.php',

        // 프로필 / 자기소개 (popup)
        '/profile'                => 'bbs/profile.php',

        // 포인트 내역
        '/point'                  => 'bbs/point.php',

        // 스크랩
        '/scrap'                  => 'bbs/scrap.php',
        '/scrap_delete'           => 'bbs/scrap_delete.php',
        '/scrap_popin'            => 'bbs/scrap_popin.php',
        '/scrap_popin_update'     => 'bbs/scrap_popin_update.php',

        // 1:1 문의 (QA)
        '/qa'                     => 'bbs/qalist.php',
        '/qa/write'               => 'bbs/qawrite.php',
        '/qa/write_update'        => 'bbs/qawrite_update.php',
        '/qa/delete'              => 'bbs/qadelete.php',
        '/qa/download'            => 'bbs/qadownload.php',
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

        // 내용관리 — /content/{co_id}
        '#^/content/(?P<co_id>[a-zA-Z0-9_-]+)/?$#' => 'bbs/content.php',

        // 그룹 — /group/{gr_id}
        '#^/group/(?P<gr_id>[a-zA-Z0-9_-]+)/?$#' => 'bbs/group.php',

        // 모던 admin (/adm 와 별개로 점진적 마이그레이션)
        // .php 만 제거한 path-rewrite — /admin/foo → app/admin/foo.php
        //   - 각 segment 는 글자로 시작 ([a-zA-Z]) + 영문/숫자/_/- (점 금지)
        //     → /admin/admin.menu100 / /admin/menu.d/foo / /admin/_layout 등
        //       internal 파일이 web 으로 노출되지 않도록 차단
        //   - 서브디렉토리 가능 (segment/segment) — 단 모든 segment 가 같은 룰
        '#^/admin/?$#'                                                                       => 'admin/index.php',
        // 디렉토리 directory-index — /admin/{dir}/ → admin/{dir}/index.php (예: /admin/shop_admin/)
        '#^/admin/(?P<_admindir>[a-zA-Z][a-zA-Z0-9_-]*)/$#'                                  => 'admin/{_admindir}/index.php',
        // ajax.* 엔드포인트 (점 허용) — /admin/ajax.token, /admin/ajax.use_captcha 등
        // 캡처 이름 _adminpage: 'page' 를 쓰면 페이지네이션 ?page=N 와 충돌해서
        // $_GET['page'] 가 페이지명으로 덮어써짐 → list 페이지 페이징 깨짐.
        // 주의: [a-z0-9_.]+ 가 greedy 라 trailing .php 까지 잡아먹음 (ajax.foo.php → 캡처에 .php 포함
        //  → target {_x}.php → ajax.foo.php.php 이중확장자). .php 있는 것/없는 것 두 패턴으로 분리.
        '#^/admin/(?P<_adminpage>ajax\.[a-z0-9_.]+?)\.php/?$#i' => 'admin/{_adminpage}.php',
        '#^/admin/(?P<_adminpage>ajax\.[a-z0-9_.]+)/?$#i'       => 'admin/{_adminpage}.php',
        // 서브디렉토리 ajax.* 도 허용 — /admin/shop_admin/ajax.ca_id 등
        '#^/admin/(?P<_adminpage>[a-zA-Z][a-zA-Z0-9_-]*/ajax\.[a-z0-9_.]+?)\.php/?$#i' => 'admin/{_adminpage}.php',
        '#^/admin/(?P<_adminpage>[a-zA-Z][a-zA-Z0-9_-]*/ajax\.[a-z0-9_.]+)/?$#i'       => 'admin/{_adminpage}.php',
        '#^/admin/(?P<_adminpage>[a-zA-Z][a-zA-Z0-9_-]*(?:/[a-zA-Z][a-zA-Z0-9_-]*)*)(?:\.php)?/?$#' => 'admin/{_adminpage}.php',

        // shop — admin 동일 패턴. 정적 자산 (img/css/js) 은 .htaccess 가 먼저 매핑.
        // 서브디렉토리 (inicis/lg/nicepay/toss/kakaopay/naverpay/kcp) 도 segment-by-segment 룰로 통과.
        '#^/shop/?$#'                                                                  => 'shop/index.php',
        '#^/shop/(?P<_shoppage>ajax\.[a-z0-9_.]+?)\.php/?$#i'                         => 'shop/{_shoppage}.php',
        '#^/shop/(?P<_shoppage>ajax\.[a-z0-9_.]+)/?$#i'                               => 'shop/{_shoppage}.php',
        // 자원형 클린 URL — segment catch-all 보다 먼저 매칭되어야 함
        // (catch-all 이 /shop/item/item0008 도 잡아 shop/item/item0008.php 를 시도하면 404)
        '#^/shop/item/(?P<it_id>[a-zA-Z0-9_-]+)/?$#'                                   => 'shop/item.php',
        '#^/shop/category/(?P<ca_id>[a-zA-Z0-9_-]+)/?$#'                               => 'shop/list.php',
        '#^/shop/listtype/(?P<type>\d+)/?$#'                                           => 'shop/listtype.php',
        '#^/shop/(?P<_shoppage>[a-zA-Z][a-zA-Z0-9_-]*(?:/[a-zA-Z][a-zA-Z0-9_-]*)*)(?:\.php)?/?$#' => 'shop/{_shoppage}.php',

        // 1:1 문의 단일 보기 — /qa/{qa_id}
        '#^/qa/(?P<qa_id>\d+)/?$#'        => 'bbs/qaview.php',
        // 1:1 문의 수정 — /qa/{qa_id}/edit (resource-first; w=u 자동 주입)
        '#^/qa/(?P<qa_id>\d+)/edit/?$#'   => 'bbs/qawrite.php?w=u',

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
            // /content?co_id=X → /content/X, /group?gr_id=X → /group/X 강한 정규화
            if (($method === 'GET' || $method === 'HEAD')) {
                $pathParams = [
                    '/content' => 'co_id',
                    '/group'   => 'gr_id',
                ];
                if (isset($pathParams[$normalized])) {
                    $key = $pathParams[$normalized];
                    parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
                    if (!empty($params[$key]) && preg_match('/^[a-zA-Z0-9_-]+$/', $params[$key])) {
                        $url = $normalized.'/'.$params[$key];
                        unset($params[$key]);
                        if (!empty($params)) $url .= '?'.http_build_query($params);
                        header('Location: '.$url, true, 301);
                        exit;
                    }
                }
            }
            return $this->cleanRoutes[$normalized];
        }

        // 1.5) 1:1 문의 (qa) 레거시 URL → 클린 URL 301
        //      /qalist.php?... → /qa[?...]
        //      /qaview.php?qa_id=N → /qa/N
        //      /qawrite.php[?w=u&qa_id=N] → /qa/write[/N]
        //      /qadelete.php / /qadownload.php / /qawrite_update.php → /qa/delete /download /write_update
        if (($method === 'GET' || $method === 'HEAD')
            && preg_match('#^/(qalist|qaview|qawrite|qadelete|qadownload|qawrite_update)\.php$#', $path, $m)) {
            $action = $m[1];
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/qa';
            if ($action === 'qaview') {
                if (!empty($params['qa_id']) && preg_match('/^\d+$/', $params['qa_id'])) {
                    $url = '/qa/'.$params['qa_id'];
                    unset($params['qa_id']);
                }
            } else if ($action === 'qawrite') {
                $url = '/qa/write';
                if (!empty($params['w']) && $params['w'] === 'u' && !empty($params['qa_id']) && preg_match('/^\d+$/', $params['qa_id'])) {
                    $url = '/qa/'.$params['qa_id'].'/edit';
                    unset($params['qa_id'], $params['w']);
                }
            } else if ($action === 'qadelete') {
                $url = '/qa/delete';
            } else if ($action === 'qadownload') {
                $url = '/qa/download';
            } else if ($action === 'qawrite_update') {
                $url = '/qa/write_update';
            }
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$url, true, 301);
            exit;
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

        // 2.5) shop 자원형 레거시 URL → 클린 URL 301 (GET/HEAD)
        //      /shop/item.php?it_id=X → /shop/item/X
        //      /shop/list.php?ca_id=X → /shop/category/X
        //      id 형식 이상하면 통과시켜 legacy 동작 유지 (404 등)
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/item.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            if (!empty($params['it_id']) && preg_match('/^[a-zA-Z0-9_-]+$/', $params['it_id'])) {
                $url = '/shop/item/'.$params['it_id'];
                unset($params['it_id']);
                if (!empty($params)) $url .= '?'.http_build_query($params);
                header('Location: '.$url, true, 301);
                exit;
            }
        }
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/list.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            if (!empty($params['ca_id']) && preg_match('/^[a-zA-Z0-9_-]+$/', $params['ca_id'])) {
                $url = '/shop/category/'.$params['ca_id'];
                unset($params['ca_id']);
                if (!empty($params)) $url .= '?'.http_build_query($params);
                header('Location: '.$url, true, 301);
                exit;
            }
        }
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/listtype.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            if (!empty($params['type']) && preg_match('/^\d+$/', $params['type'])) {
                $url = '/shop/listtype/'.$params['type'];
                unset($params['type']);
                if (!empty($params)) $url .= '?'.http_build_query($params);
                header('Location: '.$url, true, 301);
                exit;
            }
        }
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/wishlist.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/wishlist';
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$url, true, 301);
            exit;
        }
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/cart.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/cart';
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$url, true, 301);
            exit;
        }
        // 주문조회: orderinquiry / orderinquiryview / orderinquirycancel — query 보존 (od_id, uid 등)
        if (($method === 'GET' || $method === 'HEAD')
            && preg_match('#^/shop/(orderinquiry(?:view|cancel)?)\.php$#', $path, $m)) {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/'.$m[1];
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$url, true, 301);
            exit;
        }
        // 배송지목록: /shop/orderaddress.php → /shop/orderaddress
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/orderaddress.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/orderaddress';
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$url, true, 301);
            exit;
        }
        // 쿠폰: /shop/coupon.php → /shop/coupon
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/coupon.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/coupon';
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$url, true, 301);
            exit;
        }
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/orderform.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/orderform';
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$url, true, 301);
            exit;
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
                // target 이 'bbs/foo.php?key=val&k2=v2' 형태면 ? 뒤를 $_GET 에 주입
                if (strpos($target, '?') !== false) {
                    list($target, $injectQs) = explode('?', $target, 2);
                    parse_str($injectQs, $injectParams);
                    foreach ($injectParams as $k => $v) {
                        $_GET[$k] = $v;
                        $_REQUEST[$k] = $v;
                    }
                }
                // 캡처 그룹을 target 의 placeholder 로 치환:
                //   {1}, {2} ...  → 숫자 인덱스 캡처
                //   {name}        → 명명 캡처 (?P<name>...)
                $resolved = preg_replace_callback('/\{(\w+)\}/', function ($ph) use ($m) {
                    $key = $ph[1];
                    if (ctype_digit($key)) $key = (int)$key;
                    return isset($m[$key]) ? $m[$key] : '';
                }, $target);
                // 명명 그룹은 $_GET 에 주입 (단, '_' 로 시작하는 이름은 routing-only 이므로 skip
                //  — 예: admin pattern 의 (?P<_adminpage>...) 는 실제 query 의 ?page=N 와 충돌 회피용)
                foreach ($m as $k => $v) {
                    if (!is_int($k) && $k[0] !== '_') {
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
