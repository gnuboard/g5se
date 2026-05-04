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
        $back = urlencode($_SERVER['REQUEST_URI'] ?? '/admin');
        header('Location: /login?url='.$back, true, 302);
        exit;
    }
    if (!$is_admin) {
        header('Location: /', true, 302);
        exit;
    }

    // 레거시 admin.lib.php 의 admin_referer_check / admin_check_xss_params 가
    // referer path 에 /adm/ 를 요구한다. 모던 URL 은 /admin/... 이라 매번 실패해서
    // POST 시 'XSS 공격' alert 가 떴음 (특히 contentform 의 editor HTML 본문).
    // admin.lib.php 가 require 되기 전에 referer 를 패치해 검증을 통과시킨다.
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $_SERVER['HTTP_REFERER'] = preg_replace('#(/+)admin(/+)#', '$1adm$2', $_SERVER['HTTP_REFERER']);
    }
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
    <!-- UnoCSS reset (Tailwind 호환) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@unocss/reset/tailwind.min.css">
    <!-- admin 전용 정적 CSS — 변수 + 레거시 컴포넌트 레이어 (.legacy-admin-content) -->
    <link rel="stylesheet" href="/admin/css/admin.css">
    <!-- Pretendard 는 admin 에서 미사용 — 시스템 폰트로 즉시 paint (font-swap 으로 인한
         '창이 새로 뜨는' 느낌 차단). Apple SD Gothic Neo / Malgun Gothic / Noto Sans 등
         OS 가 제공하는 한글 폰트가 가장 자연스럽고 빠름. -->
    <!-- admin shell 레이아웃은 admin.css 에 모두 정적으로 베이킹되어 있어 FOUC 가드 불필요.
         (가드를 두면 body invisible→visible 전환이 "창이 다시 뜨는" 느낌을 줘서 제거함.)
         브라우저는 stylesheet 가 도착하면 자연스럽게 progressive paint 한다. -->

    <!-- UnoCSS runtime — 베이킹 안 된 보조 utility 들을 런타임에 생성. layout 이 이미 정적이라
         실패해도 안전. admin-primary 팔레트 컬러도 정적 CSS 로 커버됨. -->
    <script>window.__unocss = { theme: { colors: { 'admin-primary': { 50:'#f0f7ff', 100:'#dceaff', 200:'#bdd6ff', 300:'#8fb6ff', 400:'#5d8eff', 500:'#3464f5', 600:'#2649d5', 700:'#1f3aac', 800:'#1d3187', 900:'#1c2c6e', 950:'#162050' } } } };</script>
    <script src="/admin/js/uno.global.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>
    <style>
        html, body { font-family: -apple-system, BlinkMacSystemFont, "Apple SD Gothic Neo", "Malgun Gothic", "Noto Sans KR", system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">

<div class="min-h-screen flex">

    <!-- 좌측 사이드바 (모바일: hidden 토글, lg 이상: 항상 노출) -->
    <aside id="adm-sidebar"
           class="flex-col fixed inset-y-0 left-0 w-60 z-40
                  bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
        <div class="h-14 flex items-center justify-between px-5 border-b border-slate-200 dark:border-slate-800">
            <a href="/admin" class="flex items-center gap-2 font-bold text-slate-900 dark:text-slate-100">
                <span class="inline-flex w-8 h-8 rounded-md bg-admin-primary-600 text-white items-center justify-center font-black">G</span>
                <span><?php echo get_text($cf_title) ?></span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto py-3 px-3 text-sm">
            <?php foreach ($_admin_nav as $group_index => $group) {
                // super 만 볼 수 있는 항목 필터
                $items = array_values(array_filter($group['items'], function ($it) use ($is_admin) {
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
                $group_id = 'navgrp-'.($group['_id'] ?? $group_index);

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
                    <span><?php echo get_text($group['group']) ?></span>
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
                            <span class="nav-icon shrink-0" aria-hidden="true"><?php echo $item['icon'] ?></span>
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
                <span class="nav-icon shrink-0" aria-hidden="true">←</span>
                사이트로 돌아가기
            </a>
        </div>
    </aside>

    <!-- 메인 영역 -->
    <div class="flex-1 lg:ml-60 flex flex-col min-w-0">

        <!-- 상단 헤더 -->
        <header class="sticky top-0 z-20 h-14 bg-white/85 dark:bg-slate-900/85 backdrop-blur border-b border-slate-200 dark:border-slate-800 flex items-center gap-3 px-4">
            <button type="button" id="adm-mobile-toggle" class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300" aria-label="메뉴 열기">
                <span class="nav-icon" aria-hidden="true">☰</span>
            </button>
            <h1 class="font-semibold text-slate-900 dark:text-slate-100 truncate"><?php echo get_text($title) ?></h1>
            <div class="ml-auto flex items-center gap-2">
                <button type="button" id="adm-theme-toggle" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300" aria-label="테마 전환">
                    <span class="nav-icon block dark:hidden" aria-hidden="true">🌙</span>
                    <span class="nav-icon hidden dark:block" aria-hidden="true">☀️</span>
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
