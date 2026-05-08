<?php
/*
 * Admin 공통 레이아웃 — admin 페이지에서 진입점 PHP 가 포함시켜 사용한다.
 *
 * 사용 예 (대시보드 / 회원목록 / 게시판폼 등):
 *
 *   require __DIR__.'/_layout.php';
 *   admin_layout_start('회원 관리', 'members');     // 좌측 nav 의 'members' key 활성
 *   ?>
 *   <main class="...">  ... 페이지 내용 ...  </main>
 *   <?php admin_layout_end();
 *
 * 권한: 미인증 → /login?url=현재. super 가드는 각 페이지에서 추가.
 */

if (!defined('_GNUBOARD_')) exit;

/**
 * 페이지의 auth_check_menu() 호출 전에 먼저 부르는 가드.
 * 미로그인 → /login, 일반 회원 → /. admin (super 또는 그룹/게시판 admin) 만 통과.
 * gnuboard 의 auth_check_menu() 는 비-super 에 대해 alert() 로 죽으므로 그 이전에 실행해야 redirect 가 동작.
 */
function admin_require_login(): void
{
    global $is_admin, $is_member;
    if (!$is_member) {
        $back = urlencode($_SERVER['REQUEST_URI'] ?? G5_ADMIN_URL);
        header('Location: '.G5_URL.'/login?url='.$back, true, 302);
        exit;
    }
    if (!$is_admin) {
        header('Location: '.G5_URL.'/', true, 302);
        exit;
    }
    // (이전에 referer 의 /admin/ → /adm/ 치환이 있었으나, G5_ADMIN_DIR='admin' 이후
    //  admin_referer_check 가 직접 /admin/ 를 요구하므로 패치 불필요.)
}

function admin_layout_start(string $title, string $active_key = ''): void
{
    global $is_admin, $member, $config, $sub_menu;

    // 활성 매칭은 gnuboard 의 \$sub_menu 코드 (예: '300100') 로 자동 — 페이지마다
    // 수동으로 \$active_key 를 넘길 필요 없음. \$active_key 는 옛 호환용 폴백.
    $active_code = $sub_menu ?? '';

    // 사이드바 그룹 토글 상태 — 쿠키로 저장해 서버가 첫 paint 부터 정확히 렌더 (FOUC 방지).
    // 쿠키 형식: 'admin-nav-groups' = JSON {"navgrp-100":1,"navgrp-200":0,...}
    $_admin_nav_state = [];
    if (!empty($_COOKIE['admin-nav-groups'])) {
        $_admin_nav_state = json_decode($_COOKIE['admin-nav-groups'], true) ?: [];
    }

    // 가드 한 번 더 — admin_require_login 을 호출 안 했더라도 안전하게.
    admin_require_login();

    require_once __DIR__.'/_menu.php';
    /** @var array $_admin_nav */

    $g5_title = $title.' · 관리자';
    $cf_title = isset($config['cf_title']) && $config['cf_title'] ? $config['cf_title'] : 'gnu5se';

    // 기본 통계 (헤더의 빠른 카운트용 — _common.php 가 이미 로드되어 $g5 사용 가능)
    $admin_nick = isset($member['mb_nick']) ? get_text($member['mb_nick']) : 'admin';

    ?>
<!doctype html>
<html lang="ko" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?php echo get_text($g5_title) ?></title>
    <!-- 다크모드 FOUC 방지 — data-theme 와 .dark 클래스를 동시에 토글 (UnoCSS dark: 변형이 .dark 셀렉터 사용) -->
    <script>(function(){try{var t=localStorage.getItem("m-theme");if(!t)t=matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";document.documentElement.dataset.theme=t;if(t==='dark')document.documentElement.classList.add('dark');}catch(e){}})();</script>
    <!-- admin 전용 정적 CSS — 변수 + 레거시 컴포넌트 레이어 + Tailwind reset 흡수.
         (CDN reset 을 별도 link 로 두면 늦게 도착해서 body margin 8px 이 잠깐 적용됐다
         사라지는 reflow — '컨텐츠가 벽에 붙었다 떨어지는' 느낌 — 이 발생.) -->
    <link rel="stylesheet" href="<?php echo G5_ADMIN_URL ?>/css/admin.css">
    <!-- Pretendard 는 admin 에서 미사용 — 시스템 폰트로 즉시 paint (font-swap 으로 인한
         '창이 새로 뜨는' 느낌 차단). Apple SD Gothic Neo / Malgun Gothic / Noto Sans 등
         OS 가 제공하는 한글 폰트가 가장 자연스럽고 빠름. -->
    <!-- admin shell 레이아웃은 admin.css 에 모두 정적으로 베이킹되어 있어 FOUC 가드 불필요.
         (가드를 두면 body invisible→visible 전환이 "창이 다시 뜨는" 느낌을 줘서 제거함.)
         브라우저는 stylesheet 가 도착하면 자연스럽게 progressive paint 한다. -->

    <!-- UnoCSS runtime — 베이킹 안 된 보조 utility 들을 런타임에 생성. layout 이 이미 정적이라
         실패해도 안전. admin-primary 팔레트 컬러도 정적 CSS 로 커버됨. -->
    <script>window.__unocss = { theme: { colors: { 'admin-primary': { 50:'#f0f7ff', 100:'#dceaff', 200:'#bdd6ff', 300:'#8fb6ff', 400:'#5d8eff', 500:'#3464f5', 600:'#2649d5', 700:'#1f3aac', 800:'#1d3187', 900:'#1c2c6e', 950:'#162050' } } } };</script>
    <script src="<?php echo G5_ADMIN_URL ?>/js/uno.global.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>
    <!-- Alpine.js — 작은 reactive (~15KB). x-data / x-show / x-on / x-text 으로 선언적 인터랙션.
         defer 로 로드 → DOMContentLoaded 시 자동 init. 기존 jQuery 와 충돌 없이 공존. -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- gnuboard 공용 헬퍼 (win_zip, set_cookie 등) + Kakao 우편번호 SDK.
         원래 head.sub.php 의 add_javascript 큐로 들어가지만 modern admin shell 은
         해당 큐를 flush 하지 않아 admin 에서 win_zip 호출 시 ReferenceError 가 발생. -->
    <script>var g5_is_mobile = false, g5_url = "<?php echo G5_URL ?>", g5_bbs_url = "<?php echo G5_BBS_URL ?>";</script>
    <script src="<?php echo G5_JS_URL ?>/common.js?ver=<?php echo defined('G5_JS_VER') ? G5_JS_VER : '1' ?>"></script>
    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <!-- admin 전용 JS — PopupManager, CommonUI 등 (legacy admin.tail.php 가 로드하던 것).
         .htaccess 규칙상 /admin/js/* 만 정적 매핑이라 /admin/admin.js 가 아닌 /admin/js/admin.js. -->
    <script src="<?php echo G5_ADMIN_URL ?>/js/admin.js?ver=<?php echo defined('G5_JS_VER') ? G5_JS_VER : '1' ?>" defer></script>
    <!-- jQuery UI datepicker — admin 의 popular_rank / visit_search / visit.sub 등에서 사용.
         원본은 plugin/jquery-ui/datepicker.php 가 add_stylesheet/javascript 큐로 등록하지만
         modern admin shell 은 큐 flush 안 함. 한 번만 직접 로드. -->
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo G5_PLUGIN_URL ?>/jquery-ui/style.css">
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
    <script>
    jQuery(function($){
        $.datepicker.regional["ko"] = {
            closeText:"닫기", prevText:"이전달", nextText:"다음달", currentText:"오늘",
            monthNames:["1월","2월","3월","4월","5월","6월","7월","8월","9월","10월","11월","12월"],
            monthNamesShort:["1월","2월","3월","4월","5월","6월","7월","8월","9월","10월","11월","12월"],
            dayNames:["일요일","월요일","화요일","수요일","목요일","금요일","토요일"],
            dayNamesShort:["일","월","화","수","목","금","토"],
            dayNamesMin:["일","월","화","수","목","금","토"],
            weekHeader:"주", dateFormat:"yy-mm-dd", firstDay:0, isRTL:false,
            showMonthAfterYear:true, yearSuffix:"년"
        };
        $.datepicker.setDefaults($.datepicker.regional["ko"]);
    });
    </script>
    <style>
        html, body { font-family: -apple-system, BlinkMacSystemFont, "Apple SD Gothic Neo", "Malgun Gothic", "Noto Sans KR", system-ui, sans-serif; }
        /* shop_admin/configform 결제 영역 — JS 가 토글하는 hide 클래스가 CSS 누락이라
           모든 PG 의 카드/팁이 동시에 노출됐던 문제. 표준 gnuboard 룰 추가. */
        .scf_cardtest_hide { display: none !important; }
        .scf_cardtest_tip { display: none; }
        .scf_cardtest_tip_adm_hide { display: none !important; }
        .scf_cardtest_tip.scf_cardtest_tip_show { display: block; }
        .scf_cardtest { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 12px; }
        .scf_cardtest .btn_frmline { margin: 0; }

        /* 테스트결제 팁 카드 — 섹션 시각 분리 + dl/dt/dd grid 정렬 */
        #scf_cardtest_tip {
            margin-top: 12px;
            padding: 18px 20px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #f9fafb;
            line-height: 1.6;
        }
        .dark #scf_cardtest_tip,
        [data-theme="dark"] #scf_cardtest_tip {
            background: rgba(255,255,255,0.04);
            border-color: rgba(255,255,255,0.12);
        }
        #scf_cardtest_tip > strong {
            display: block;
            margin: 18px 0 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 15px;
            font-weight: 700;
            color: #1c70e9;
        }
        .dark #scf_cardtest_tip > strong,
        [data-theme="dark"] #scf_cardtest_tip > strong {
            color: #60a5fa;
            border-bottom-color: rgba(255,255,255,0.08);
        }
        #scf_cardtest_tip > strong:first-child { margin-top: 0; }
        #scf_cardtest_tip > br { display: none; }
        #scf_cardtest_tip dl {
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: 8px 16px;
            margin: 0 0 4px;
        }
        #scf_cardtest_tip dt {
            font-weight: 600;
            color: #4b5563;
        }
        .dark #scf_cardtest_tip dt,
        [data-theme="dark"] #scf_cardtest_tip dt {
            color: #cbd5e1;
        }
        #scf_cardtest_tip dd {
            margin: 0;
            color: #374151;
        }
        .dark #scf_cardtest_tip dd,
        [data-theme="dark"] #scf_cardtest_tip dd {
            color: #e5e7eb;
        }
        #scf_cardtest_tip ul {
            margin: 12px 0 0;
            padding: 12px 16px;
            list-style: none;
            background: rgba(28, 112, 233, 0.06);
            border-left: 3px solid #1c70e9;
            border-radius: 4px;
        }
        .dark #scf_cardtest_tip ul,
        [data-theme="dark"] #scf_cardtest_tip ul {
            background: rgba(96, 165, 250, 0.08);
            border-left-color: #60a5fa;
        }
        #scf_cardtest_tip ul li {
            margin: 4px 0;
            font-size: 13px;
        }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">

<div class="min-h-screen flex">

    <!-- 좌측 사이드바 (모바일: hidden 토글, lg 이상: 항상 노출) -->
    <aside id="adm-sidebar"
           class="flex-col fixed inset-y-0 left-0 w-60 z-40
                  bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
        <div class="h-14 flex items-center justify-between px-5 border-b border-slate-200 dark:border-slate-800">
            <a href="<?php echo G5_ADMIN_URL ?>" class="flex items-center gap-2 font-bold text-slate-900 dark:text-slate-100">
                <span class="inline-flex w-8 h-8 rounded-md bg-admin-primary-600 text-white items-center justify-center font-black">G</span>
                <span><?php echo get_text($cf_title) ?></span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto py-3 px-3 text-sm">
            <?php foreach ($_admin_nav as $group_index => $navGroup) {
                // 주의: 변수명 \$group 을 쓰면 gnuboard 의 전역 \$group (현재 게시판 그룹 row)
                // 을 foreach 가 마지막 값으로 덮어써서 boardgroup_form 등이 빈 칸으로 보임. \$navGroup 사용.
                // super 만 볼 수 있는 항목 필터
                $items = array_values(array_filter($navGroup['items'], function ($it) use ($is_admin) {
                    return ($it['level'] === '') || ($it['level'] === $is_admin) || $is_admin === 'super';
                }));
                if (!$items) continue;

                // 활성 항목이 이 그룹에 있으면 기본 펼침 — code 매칭 우선, key 매칭 폴백
                $group_has_active = false;
                foreach ($items as $it) {
                    if (($active_code !== '' && isset($it['code']) && $it['code'] === $active_code)
                        || ($active_key !== '' && $it['key'] === $active_key)) {
                        $group_has_active = true;
                        break;
                    }
                }
                // 그룹 ID — admin.menu{N}.php 의 숫자 N 을 사용 (_menu.php 가 _id 로 노출).
                $group_id = 'navgrp-'.($navGroup['_id'] ?? $group_index);

                // open 상태: 활성 그룹은 항상 open. 그 외엔 쿠키 우선, 없으면 닫힘.
                if ($group_has_active) {
                    $group_open = true;
                } elseif (array_key_exists($group_id, $_admin_nav_state)) {
                    $group_open = !empty($_admin_nav_state[$group_id]);
                } else {
                    $group_open = false;
                }
            ?>
            <details class="mb-2 nav-group" data-group-id="<?php echo $group_id ?>" data-has-active="<?php echo $group_has_active ? '1' : '0' ?>" <?php echo $group_open ? 'open' : '' ?>>
                <summary class="nav-summary cursor-pointer flex items-center gap-2 px-3 py-2 rounded-md text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/40 select-none list-none">
                    <span><?php echo get_text($navGroup['group']) ?></span>
                </summary>
                <ul class="mt-1">
                    <?php foreach ($items as $item) {
                        $is_active = ($active_code !== '' && isset($item['code']) && $item['code'] === $active_code)
                                  || ($active_key !== '' && $item['key'] === $active_key);
                    ?>
                    <li>
                        <a href="<?php echo $item['url'] ?>"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-md
                                  <?php echo $is_active
                                      ? 'bg-admin-primary-50 text-admin-primary-700 dark:bg-admin-primary-950 dark:text-admin-primary-200 font-semibold'
                                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60' ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><?php echo $item['icon'] ?></svg>
                            <span class="truncate"><?php echo get_text($item['label']) ?></span>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </details>
            <?php } ?>
        </nav>

        <div class="border-t border-slate-200 dark:border-slate-800 p-3">
            <a href="/" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
                사이트로 돌아가기
            </a>
        </div>
    </aside>

    <!-- 메인 영역 -->
    <div class="flex-1 lg:ml-60 flex flex-col min-w-0">

        <!-- 상단 헤더 -->
        <header class="sticky top-0 z-20 h-14 bg-white/85 dark:bg-slate-900/85 backdrop-blur border-b border-slate-200 dark:border-slate-800 flex items-center gap-3 px-4">
            <button type="button" id="adm-mobile-toggle" class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300" aria-label="메뉴 열기">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <h1 class="font-semibold text-slate-900 dark:text-slate-100 truncate"><?php echo get_text($title) ?></h1>
            <div class="ml-auto flex items-center gap-2">
                <button type="button" id="adm-theme-toggle" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300" aria-label="테마 전환">
                    <svg class="block dark:hidden" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    <svg class="hidden dark:block" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                </button>
                <span class="hidden sm:inline text-sm text-slate-600 dark:text-slate-400"><?php echo $admin_nick ?></span>
                <a href="<?php echo G5_BBS_URL ?>/logout.php" class="inline-flex items-center px-3 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60">로그아웃</a>
            </div>
        </header>
<?php
}

function admin_layout_end(): void
{
?>
    </div><!-- /flex-1 -->
</div><!-- /min-h-screen flex -->

<!-- 모바일 사이드바 backdrop (lg 이하에서 토글 시 표시) -->
<div id="adm-sidebar-backdrop" class="hidden fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

<!-- admin 공용 레이어 팝업 컨테이너 — admin.js 의 PopupManager.render() 가 이 마크업 사용
     (legacy admin.tail.php 가 출력하던 것을 modern shell 안으로 이식) -->
<div id="adminPopupContainer">
    <div id="popupOverlay" class="popup-overlay is-hidden" onclick="PopupManager.close('popupOverlay')">
        <div class="popup-content" onclick="event.stopPropagation()">
            <div class="popup-header">
                <strong id="popupTitle" class="popup-title"></strong>
                <button type="button" class="popup-close-btn" onclick="PopupManager.close('popupOverlay')">
                    <i class="fa fa-close"></i><span class="sound_only">팝업 닫기</span>
                </button>
            </div>
            <div class="popup-body" id="popupBody"></div>
            <div class="popup-footer" id="popupFooter"></div>
        </div>
    </div>
</div>

<?php
// admin form 의 hidden token 자동 주입.
// 주의: get_admin_token() 은 호출할 때마다 새 random 토큰을 만들고 세션에 저장.
//       form HTML 에서 이미 get_admin_token() 으로 토큰을 박아놨다면 세션에는 그
//       토큰이 들어있음 — 여기서 다시 get_admin_token() 을 부르면 세션이
//       덮어써져서 form 토큰과 mismatch → 'check_admin_token' 실패.
//       따라서 세션 값을 그대로 읽어 쓰고, 없을 때만 발급.
$_admin_form_token = function_exists('get_session') ? (string)get_session('ss_admin_token') : '';
if ($_admin_form_token === '' && function_exists('get_admin_token')) {
    $_admin_form_token = get_admin_token();
}
?>
<script>
(function () {
    var ADMIN_TOKEN = <?php echo json_encode($_admin_form_token) ?>;
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f || f.tagName !== 'FORM') return;
        // POST form 에 한해 token 자동 보장 — token field 가 없으면 생성, 비어 있으면 채움.
        // (shop_admin/categoryform 등 일부 form 이 hidden token 을 안 박는 케이스 방어막.)
        var method = (f.method || '').toLowerCase();
        if (method && method !== 'post') return;
        var t = f.querySelector('input[name="token"]');
        if (!t) {
            t = document.createElement('input');
            t.type = 'hidden';
            t.name = 'token';
            f.appendChild(t);
        }
        if (!t.value) t.value = ADMIN_TOKEN;
    }, true);
})();

// gnuboard 의 admin.head 안 inline JS 가 정의하던 글로벌 헬퍼들 (전체선택/체크여부/
// 삭제확인/쿠키 유틸). modern admin shell 은 admin.head 를 로드 안 하므로 여기서 정의.
window.is_checked = window.is_checked || function (name) {
    var els = document.getElementsByName(name);
    for (var i = 0; i < els.length; i++) if (els[i].checked) return true;
    return false;
};
window.check_all = window.check_all || function (f) {
    var ck = f.chkall || f.querySelector('input[name="chkall"]');
    if (!ck) return;
    f.querySelectorAll('input[type="checkbox"][name="chk[]"]').forEach(function (i) { i.checked = ck.checked; });
};
window.delete_confirm = window.delete_confirm || function (a) {
    return confirm('정말 삭제하시겠습니까?');
};
window.set_cookie = window.set_cookie || function (n, v, h, d) {
    var e = new Date(); e.setTime(e.getTime() + (h * 1000));
    document.cookie = n + '=' + escape(v) + '; expires=' + e.toUTCString() + '; path=/' + (d ? '; domain=' + d : '');
};
window.delete_cookie = window.delete_cookie || function (n) {
    document.cookie = n + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
};

// SmartEditor2 / cheditor 의 iframe 본문은 별도 문서 — same-origin 이면 style 직접 주입.
// 다크모드 admin shell 에서도 에디터는 항상 라이트 톤 (흰 배경 + 어두운 글자) 으로 강제.
(function () {
    var EDITOR_CSS = 'html,body{background:#fff !important;color:#1e293b !important}'
        + 'body{font-family:"Pretendard Variable","Pretendard",-apple-system,system-ui,sans-serif}'
        + 'a{color:#0369a1}';
    function injectCss(iframe) {
        try {
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            if (!doc || doc.__cssInjected) return;
            var s = doc.createElement('style');
            s.textContent = EDITOR_CSS;
            (doc.head || doc.documentElement).appendChild(s);
            doc.__cssInjected = true;
        } catch (e) { /* cross-origin 등 — 무시 */ }
    }
    function tryAll() {
        document.querySelectorAll('iframe').forEach(function (f) {
            // ckeditor4 iframe (id 가 *_contents) 은 자체 darkmode 핸들러가 있으니 건드리지 않음
            if (f.id && f.id.indexOf('cke_') === 0) return;
            if (f.parentElement && f.parentElement.id && f.parentElement.id.indexOf('cke_') === 0) return;
            injectCss(f);
            f.addEventListener('load', function () { injectCss(f); }, { once: false });
        });
    }
    tryAll();
    // SE2 가 늦게 iframe 을 만드므로 몇 차례 재시도
    var n = 0; var t = setInterval(function () { tryAll(); if (++n > 10) clearInterval(t); }, 500);
})();
</script>

<script>
(function(){
    var sidebar = document.getElementById('adm-sidebar');
    var backdrop = document.getElementById('adm-sidebar-backdrop');
    var toggle = document.getElementById('adm-mobile-toggle');
    var themeBtn = document.getElementById('adm-theme-toggle');
    function openSidebar(){
        sidebar.classList.add('adm-sidebar-open');
        backdrop.classList.remove('hidden');
        document.documentElement.style.overflow = 'hidden';
    }
    function closeSidebar(){
        // lg 이상에서는 항상 노출 (lg:flex 로 자동 복원되지만 명시적으로 hidden 제거 안함)
        if (window.innerWidth < 1024) {
            sidebar.classList.remove('adm-sidebar-open');
        }
        backdrop.classList.add('hidden');
        document.documentElement.style.overflow = '';
    }
    toggle && toggle.addEventListener('click', openSidebar);
    backdrop && backdrop.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && !backdrop.classList.contains('hidden')) closeSidebar();
    });
    // lg 이상으로 리사이즈되면 모바일 sidebar 상태 리셋
    window.addEventListener('resize', function(){
        if (window.innerWidth >= 1024) closeSidebar();
    });

    // 사이드바 그룹 토글 상태 — 쿠키로 영속화 (서버가 다음 페이지에서 정확한 open 으로 렌더 → FOUC 없음).
    // 초기 open 상태는 PHP 가 이미 쿠키 읽고 렌더했으므로 JS 는 토글 이벤트만 처리.
    (function () {
        function readState() {
            var raw = (document.cookie.match(/(?:^|;\s*)admin-nav-groups=([^;]*)/) || [])[1];
            if (!raw) return {};
            try { return JSON.parse(decodeURIComponent(raw)); } catch (e) { return {}; }
        }
        function writeState(s) {
            var v = encodeURIComponent(JSON.stringify(s));
            // 30 일 보관, path=/, SameSite=Lax
            var d = new Date(); d.setTime(d.getTime() + 30 * 24 * 60 * 60 * 1000);
            document.cookie = 'admin-nav-groups=' + v + '; expires=' + d.toUTCString() + '; path=/; SameSite=Lax';
        }
        document.querySelectorAll('.nav-group').forEach(function (g) {
            g.addEventListener('toggle', function () {
                var s = readState();
                s[g.dataset.groupId] = g.open;
                writeState(s);
            });
        });
    })();

    // 다크모드 토글 — main site 와 localStorage key 'm-theme' 공유.
    // data-theme 속성 + .dark 클래스 동시 토글 (UnoCSS dark: variant 가 .dark 사용).
    themeBtn && themeBtn.addEventListener('click', function(){
        var cur = document.documentElement.dataset.theme || 'light';
        var next = cur === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        document.documentElement.classList.toggle('dark', next === 'dark');
        try { localStorage.setItem('m-theme', next); } catch(e) {}
    });
})();
</script>
</body>
</html>
<?php
}
