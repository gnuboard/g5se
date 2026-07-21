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
        '/install'                => 'install/index.php',
        '/install_config'         => 'install/install_config.php',
        '/install_db'             => 'install/install_db.php',
        '/install/install_config' => 'install/install_config.php',
        '/install/install_db'     => 'install/install_db.php',
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
        '/board_list_update'      => 'bbs/board_list_update.php',
        '/move'                   => 'bbs/move.php',
        '/move_update'            => 'bbs/move_update.php',

        // 쪽지 (popup)
        '/memo'                   => 'bbs/memo.php',
        '/memo_form'              => 'bbs/memo_form.php',
        '/memo_form_update'       => 'bbs/memo_form_update.php',
        '/memo_view'              => 'bbs/memo_view.php',
        '/memo_delete'            => 'bbs/memo_delete.php',

        // 메일보내기 (popup)
        '/formmail'               => 'bbs/formmail.php',
        '/formmail_send'          => 'bbs/formmail_send.php',

        // 정보메일 수신거부 (메일 본문의 공개 링크)
        '/email_stop'             => 'bbs/email_stop.php',

        // 프로필 / 자기소개 (popup)
        '/profile'                => 'bbs/profile.php',

        // SNS 공유 redirect (페이스북 / 트위터 / Google+ 등 외부 share URL)
        '/sns_send'               => 'bbs/sns_send.php',

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

        // 설문조사
        '/poll_result'            => 'bbs/poll_result.php',
        '/poll_update'            => 'bbs/poll_update.php',
        '/poll_etc_update'        => 'bbs/poll_etc_update.php',
        '/poll_etc_update_mail'   => 'bbs/poll_etc_update_mail.php',

        // 에디터 업로드 — 글쓰기 페이지와 같은 라우터/세션 컨텍스트에서 처리
        '/api/editor/upload'              => 'plugin/editor/smarteditor2/photo_uploader/popup/php/index.php',
        '/api/editor/smarteditor2/upload' => 'plugin/editor/smarteditor2/photo_uploader/popup/php/index.php',
        '/api/editor/ckeditor4/upload'    => 'plugin/editor/ckeditor4/upload.php',
        '/api/editor/cheditor5/upload'    => 'plugin/editor/cheditor5/imageUpload/upload.php',
        '/api/editor/cheditor5/delete'    => 'plugin/editor/cheditor5/imageUpload/delete.php',
    ];

    /** 디버그/유틸 라우트 (정규식 기반) */
    private $extraRoutes = [
        '#^/_debug/?$#' => '_debug.php',

        // 설치 마법사 — Apache rewrite 없이 PHP 내장 서버로 실행해도 동작하도록 통과
        '#^/install/?$#' => 'install/index.php',
        '#^/(install_config|install_db)(?:\.php)?/?$#i' => 'install/{1}.php',
        '#^/install/(install_config|install_db|ajax\.install\.check)\.php$#i' => 'install/{1}.php',
        '#^/install/(install_config|install_db)/?$#i' => 'install/{1}.php',

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
        '#^/shop/(itemrecommend(?:mail)?)(?:\.php)?/?$#i'                              => 'shop/{1}.php',
        // 자원형 클린 URL — segment catch-all 보다 먼저 매칭되어야 함
        // (catch-all 이 /shop/item/item0008 도 잡아 shop/item/item0008.php 를 시도하면 404)
        '#^/shop/item/(?P<it_id>[a-zA-Z0-9_-]+)/?$#'                                   => 'shop/item.php',
        '#^/shop/category/(?P<ca_id>[a-zA-Z0-9_-]+)/?$#'                               => 'shop/list.php',
        '#^/shop/listtype/(?P<type>\d+)/?$#'                                           => 'shop/listtype.php',
        '#^/shop/event/(?P<ev_id>\d+)/?$#'                                             => 'shop/event.php',
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
        // ?fn= 만 있는 query 형태 (gallery skin 의 view_image 링크 등) 폴백
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/view_image/?$#'                            => 'bbs/view_image.php',
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/download/?$#'                              => 'bbs/download.php',
        // 게시글 첨부 링크 redirect (gnuboard link.php — 본문에 첨부된 외부 URL 클릭 시)
        '#^/board/(?P<bo_table>[a-zA-Z0-9_]+)/link/(?P<wr_id>\d+)/(?P<no>\d+)/?$#'      => 'bbs/link.php',
    ];

    /** 사용자 확장 라우트 — app/routes/*.php 에서 로드 */
    private $userCleanRoutes = [];
    private $userExtraRoutes = [];

    public function __construct()
    {
        $this->loadUserRoutes();
    }

    /**
     * @return string|null G5_PATH 기준 상대경로, 매칭 안되면 null
     */
    public function resolve($requestUri)
    {
        $path   = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $basePath = defined('G5SE_BASE_PATH') ? G5SE_BASE_PATH : '';
        if ($basePath !== '') {
            if ($path === $basePath) {
                $path = '/';
            } else if (str_starts_with($path, $basePath.'/')) {
                $path = substr($path, strlen($basePath));
            }
        }
        $location = static function ($url) use ($basePath) {
            if ($basePath !== '' && str_starts_with($url, '/') && !str_starts_with($url, '//')) {
                if ($url === '/' || !str_starts_with($url, $basePath.'/')) {
                    return $basePath.$url;
                }
            }

            return $url;
        };

        // 0) /index.php → / 301 (클린 URL 정규화 — 설치 완료 후 등 직접 진입 케이스)
        if (($method === 'GET' || $method === 'HEAD') && $path === '/index.php') {
            $qs = parse_url($requestUri, PHP_URL_QUERY);
            header('Location: '.$basePath.'/'.($qs ? '?'.$qs : ''), true, 301);
            exit;
        }

        // 잘못 생성됐던 댓글 삭제 URL 보정:
        // /board/free/comment/delete/6&token=... → /board/free/comment/delete/6?token=...
        if (($method === 'GET' || $method === 'HEAD')
            && preg_match('#^/board/([a-zA-Z0-9_]+)/comment/delete/(\d+)&(.+)$#', $path, $m)) {
            header('Location: '.$basePath.'/board/'.$m[1].'/comment/delete/'.$m[2].'?'.$m[3], true, 302);
            exit;
        }

        // /delete_comment.php?bo_table=free&comment_id=6&token=...
        // → /board/free/comment/delete/6?token=...
        if (($method === 'GET' || $method === 'HEAD') && $path === '/delete_comment.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            if (!empty($params['bo_table']) && preg_match('/^[a-zA-Z0-9_]+$/', $params['bo_table'])
                && !empty($params['comment_id']) && preg_match('/^\d+$/', $params['comment_id'])) {
                $url = $basePath.'/board/'.$params['bo_table'].'/comment/delete/'.$params['comment_id'];
                unset($params['bo_table'], $params['comment_id']);
                if (!empty($params)) $url .= '?'.http_build_query($params);
                header('Location: '.$url, true, 302);
                exit;
            }
        }

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
                        header('Location: '.$location($url), true, 301);
                        exit;
                    }
                }
            }
            return $this->cleanRoutes[$normalized];
        }

        // 1.1) 사용자 정의 클린 URL 직접 매칭
        if (isset($this->userCleanRoutes[$normalized])) {
            return $this->userCleanRoutes[$normalized];
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
            header('Location: '.$location($url), true, 301);
            exit;
        }

        // 2) 게시판 레거시 URL → 클린 URL 301 리다이렉트
        //    /bbs/board.php?bo_table=X[&wr_id=N]   → /board/X[/N]
        //    /board.php?bo_table=X...               → /board/X[/N]
        //    /(bbs/)?write.php?bo_table=X[&wr_id=N] → /board/X/write[/N]
        //    delete/good/nogood/download/view_image 도 동일한 규칙
        //    (단, GET/HEAD 만 — POST 는 그대로 통과시켜 폼 제출 호환)
        if (($method === 'GET' || $method === 'HEAD')
            && preg_match('#^/(?:bbs/)?(board|write|write_update|delete|good|nogood|download|view_image|link)\.php$#', $path, $m)) {
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
                if (isset($params['no']) && preg_match('/^\d+$/', $params['no'])) {
                    $url .= '/'.$params['no'];
                }
                unset($params['bo_table'], $params['wr_id'], $params['no']);
                if (!empty($params)) {
                    $url .= '?'.http_build_query($params);
                }
                header('Location: '.$location($url), true, 301);
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
                header('Location: '.$location($url), true, 301);
                exit;
            }
        }
        // 추천하기 팝업은 레거시 파일을 새 UI로 감싼 엔드포인트다. 노출 URL은
        // .php 없이 사용하며, clean item URL 하위에서 잘못 상대 해석된 경로도 보정한다.
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/item/itemrecommend.php') {
            $qs = parse_url($requestUri, PHP_URL_QUERY);
            header('Location: '.$location('/shop/itemrecommend'.($qs ? '?'.$qs : '')), true, 302);
            exit;
        }
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/list.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            if (!empty($params['ca_id']) && preg_match('/^[a-zA-Z0-9_-]+$/', $params['ca_id'])) {
                $url = '/shop/category/'.$params['ca_id'];
                unset($params['ca_id']);
                if (!empty($params)) $url .= '?'.http_build_query($params);
                header('Location: '.$location($url), true, 301);
                exit;
            }
        }
        // 이벤트 — /shop/event.php?ev_id=N → /shop/event/N (잔여 query 보존)
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/event.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            if (!empty($params['ev_id']) && preg_match('/^\d+$/', $params['ev_id'])) {
                $url = '/shop/event/'.$params['ev_id'];
                unset($params['ev_id']);
                if (!empty($params)) $url .= '?'.http_build_query($params);
                header('Location: '.$location($url), true, 301);
                exit;
            }
        }
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/listtype.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            if (!empty($params['type']) && preg_match('/^\d+$/', $params['type'])) {
                $url = '/shop/listtype/'.$params['type'];
                unset($params['type']);
                if (!empty($params)) $url .= '?'.http_build_query($params);
                header('Location: '.$location($url), true, 301);
                exit;
            }
        }
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/wishlist.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/wishlist';
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$location($url), true, 301);
            exit;
        }
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/cart.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/cart';
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$location($url), true, 301);
            exit;
        }
        // 사용후기·상품문의 목록: 확장자 없는 공개 URL로 통일한다.
        if (($method === 'GET' || $method === 'HEAD')
            && preg_match('#^/shop/(itemuselist|itemqalist)\.php$#', $path, $m)) {
            $qs = parse_url($requestUri, PHP_URL_QUERY);
            header('Location: '.$location('/shop/'.$m[1].($qs ? '?'.$qs : '')), true, 301);
            exit;
        }
        // 주문조회: orderinquiry / orderinquiryview / orderinquirycancel — query 보존 (od_id, uid 등)
        if (($method === 'GET' || $method === 'HEAD')
            && preg_match('#^/shop/(orderinquiry(?:view|cancel)?)\.php$#', $path, $m)) {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/'.$m[1];
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$location($url), true, 301);
            exit;
        }
        // 배송지목록: /shop/orderaddress.php → /shop/orderaddress
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/orderaddress.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/orderaddress';
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$location($url), true, 301);
            exit;
        }
        // 쿠폰: /shop/coupon.php → /shop/coupon
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/coupon.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/coupon';
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$location($url), true, 301);
            exit;
        }
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/orderform.php') {
            parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
            $url = '/shop/orderform';
            if (!empty($params)) $url .= '?'.http_build_query($params);
            header('Location: '.$location($url), true, 301);
            exit;
        }

        // /admin/...something.php → /admin/...something (GET/HEAD 만, query 보존)
        // gnuboard 의 admin 모듈이 hard-code 한 .php URL 들을 클린 URL 로 정규화.
        if (($method === 'GET' || $method === 'HEAD')
            && preg_match('#^(/admin(?:/[a-zA-Z][a-zA-Z0-9_-]*)+)\.php$#', $path, $m)) {
            $qs = parse_url($requestUri, PHP_URL_QUERY);
            $url = $m[1].($qs ? '?'.$qs : '');
            header('Location: '.$location($url), true, 301);
            exit;
        }

        // 사용후기 작성창의 레거시 URL을 확장자 없는 주소로 정규화한다.
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/itemuseform.php') {
            $qs = parse_url($requestUri, PHP_URL_QUERY);
            header('Location: '.$location('/shop/itemuseform'.($qs ? '?'.$qs : '')), true, 301);
            exit;
        }

        // 상품문의 작성창도 확장자 없는 주소로 정규화한다.
        if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/itemqaform.php') {
            $qs = parse_url($requestUri, PHP_URL_QUERY);
            header('Location: '.$location('/shop/itemqaform'.($qs ? '?'.$qs : '')), true, 301);
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
                header('Location: '.$location($cleanCandidate.($qs ? '?'.$qs : '')), true, 301);
                exit;
            }
            // POST 등은 그대로 진행 (폼 제출 호환)
            return $this->cleanRoutes[$cleanCandidate];
        }

        // 4) 정규식 기반 라우트 (디버그/AJAX 등)
        $resolved = $this->matchExtraRoutes($this->extraRoutes, $path);
        if ($resolved !== null) {
            return $resolved;
        }

        // 4.1) 사용자 정의 정규식 라우트
        $resolved = $this->matchExtraRoutes($this->userExtraRoutes, $path);
        if ($resolved !== null) {
            return $resolved;
        }

        // 5) app/modules/ 트리 폴더 기반 자동 라우팅 (Next.js app router 풍)
        $resolved = $this->discoverModulePage($path);
        if ($resolved !== null) {
            return $resolved;
        }

        return null;
    }

    private function loadUserRoutes()
    {
        $dir = G5_PATH.'/routes';
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir.'/*.php') ?: [];
        sort($files, SORT_STRING);

        foreach ($files as $file) {
            $routes = include $file;
            if (!is_array($routes)) {
                continue;
            }

            $cleanRoutes = $routes['cleanRoutes'] ?? $routes['clean'] ?? [];
            $extraRoutes = $routes['extraRoutes'] ?? $routes['regex'] ?? [];

            if (is_array($cleanRoutes)) {
                foreach ($cleanRoutes as $path => $target) {
                    $path = $this->normalizeUserPath($path);
                    $target = $this->normalizeUserTarget($target);
                    if ($path !== null && $target !== null) {
                        $this->userCleanRoutes[$path] = $target;
                    }
                }
            }

            if (is_array($extraRoutes)) {
                foreach ($extraRoutes as $pattern => $target) {
                    $target = $this->normalizeUserTarget($target, true);
                    if (is_string($pattern) && $pattern !== '' && $target !== null) {
                        $this->userExtraRoutes[$pattern] = $target;
                    }
                }
            }
        }
    }

    private function matchExtraRoutes(array $routes, $path)
    {
        foreach ($routes as $pattern => $target) {
            if (preg_match($pattern, $path, $m)) {
                // target 이 'bbs/foo.php?key=val&k2=v2' 형태면 ? 뒤를 $_GET 에 주입
                if (strpos($target, '?') !== false) {
                    list($target, $injectQs) = explode('?', $target, 2);
                    $injectQs = preg_replace_callback('/\{(\w+)\}/', function ($ph) use ($m) {
                        $key = $ph[1];
                        if (ctype_digit($key)) $key = (int)$key;
                        return isset($m[$key]) ? $m[$key] : '';
                    }, $injectQs);
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

    private function normalizeUserPath($path)
    {
        if (!is_string($path) || $path === '') {
            return null;
        }

        $path = '/'.trim($path, '/');
        if (!preg_match('#^/[a-zA-Z0-9_/-]*$#', $path)) {
            return null;
        }

        return $path !== '/' ? rtrim($path, '/') : '/';
    }

    /**
     * app/modules/ 트리에서 path 에 매칭되는 index.php 를 찾는다.
     * - 정적 폴더 우선, [name] dynamic 폴더 fallback (dynamic capture 는 $_GET/$_REQUEST 주입)
     * - segment 패턴: ^[a-zA-Z0-9_-]+$ 만 허용 (path traversal·NUL·\ 차단)
     * - 진입점은 항상 index.php
     * @return string|null G5_PATH 기준 상대경로 (예 "modules/fortune/index.php") 또는 null
     */
    private function discoverModulePage($path)
    {
        if (!is_string($path) || $path === '' || $path === '/') {
            return null;
        }
        $trimmed = trim($path, '/');
        if ($trimmed === '') {
            return null;
        }
        $segments = explode('/', $trimmed);
        foreach ($segments as $seg) {
            if ($seg === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $seg)) {
                return null;
            }
        }

        $modulesRoot = G5_PATH.'/modules';
        if (!is_dir($modulesRoot)) {
            return null;
        }

        $currentDir = $modulesRoot;
        $resolvedSegments = [];
        $dynamicCaptures = [];

        foreach ($segments as $seg) {
            $staticDir = $currentDir.'/'.$seg;
            if (is_dir($staticDir)) {
                $currentDir = $staticDir;
                $resolvedSegments[] = $seg;
                continue;
            }

            $dynamicMatch = null;
            $entries = @scandir($currentDir);
            if ($entries === false) {
                return null;
            }
            sort($entries, SORT_STRING);
            foreach ($entries as $entry) {
                if (preg_match('/^\[([a-zA-Z0-9_-]+)\]$/', $entry, $m)) {
                    $dynDir = $currentDir.'/'.$entry;
                    if (is_dir($dynDir)) {
                        $dynamicMatch = ['name' => $m[1], 'entry' => $entry, 'dir' => $dynDir];
                        break;
                    }
                }
            }
            if ($dynamicMatch === null) {
                return null;
            }

            $dynamicCaptures[$dynamicMatch['name']] = $seg;
            $currentDir = $dynamicMatch['dir'];
            $resolvedSegments[] = $dynamicMatch['entry'];
        }

        $indexFile = $currentDir.'/index.php';
        if (!is_file($indexFile)) {
            return null;
        }

        // Symlink/realpath 안전망: 결과 파일이 반드시 G5_PATH/modules/ 하위인지 검증
        $indexReal = realpath($indexFile);
        $modulesRootReal = realpath($modulesRoot);
        if ($indexReal === false || $modulesRootReal === false) {
            return null;
        }
        if (!str_starts_with($indexReal, $modulesRootReal.DIRECTORY_SEPARATOR)) {
            return null;
        }

        foreach ($dynamicCaptures as $k => $v) {
            $_GET[$k] = $v;
            $_REQUEST[$k] = $v;
        }

        return 'modules/'.implode('/', $resolvedSegments).'/index.php';
    }

    private function normalizeUserTarget($target, $allowQuery = false)
    {
        if (!is_string($target) || $target === '') {
            return null;
        }

        $target = ltrim($target, '/');
        $path = $target;
        $query = '';
        if (strpos($target, '?') !== false) {
            list($path, $query) = explode('?', $target, 2);
            if (!$allowQuery) {
                return null;
            }
        }

        if (strpos($path, '..') !== false || !preg_match('#^[a-zA-Z0-9_./-]+\.php$#', $path)) {
            return null;
        }

        return $query !== '' ? $path.'?'.$query : $path;
    }
}
