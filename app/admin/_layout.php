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
}

function admin_layout_start(string $title, string $active_key = ''): void
{
    global $is_admin, $member, $config;

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
    <script>(function(){try{var t=localStorage.getItem("m-theme");if(!t)t=matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";document.documentElement.dataset.theme=t;}catch(e){}})();</script>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable.min.css">
    <link rel="stylesheet" href="/admin/css/tailwind.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>
    <style>
        html, body { font-family: 'Pretendard Variable','Pretendard',-apple-system,system-ui,sans-serif; }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">

<div class="min-h-screen flex">

    <!-- 좌측 사이드바 -->
    <aside id="adm-sidebar"
           class="hidden lg:flex lg:flex-col lg:fixed lg:inset-y-0 lg:left-0 lg:w-60 z-30
                  bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
        <div class="h-14 flex items-center justify-between px-5 border-b border-slate-200 dark:border-slate-800">
            <a href="/admin" class="flex items-center gap-2 font-bold text-slate-900 dark:text-slate-100">
                <span class="inline-flex w-8 h-8 rounded-md bg-admin-primary-600 text-white items-center justify-center font-black">G</span>
                <span><?php echo get_text($cf_title) ?></span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 text-sm">
            <?php foreach ($_admin_nav as $group) {
                // super 만 볼 수 있는 항목 필터
                $items = array_values(array_filter($group['items'], function ($it) use ($is_admin) {
                    return ($it['level'] === '') || ($it['level'] === $is_admin) || $is_admin === 'super';
                }));
                if (!$items) continue;
            ?>
            <div class="mb-5">
                <div class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"><?php echo get_text($group['group']) ?></div>
                <ul>
                    <?php foreach ($items as $item) {
                        $is_active = ($item['key'] === $active_key);
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
            </div>
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

<script>
(function(){
    var sidebar = document.getElementById('adm-sidebar');
    var backdrop = document.getElementById('adm-sidebar-backdrop');
    var toggle = document.getElementById('adm-mobile-toggle');
    var themeBtn = document.getElementById('adm-theme-toggle');
    function openSidebar(){
        sidebar.classList.remove('hidden');
        sidebar.classList.add('flex','fixed');
        backdrop.classList.remove('hidden');
        document.documentElement.style.overflow = 'hidden';
    }
    function closeSidebar(){
        sidebar.classList.remove('flex','fixed');
        sidebar.classList.add('hidden');
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

    // 다크모드 토글 — main site 와 localStorage key 'm-theme' 공유
    themeBtn && themeBtn.addEventListener('click', function(){
        var cur = document.documentElement.dataset.theme || 'light';
        var next = cur === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        try { localStorage.setItem('m-theme', next); } catch(e) {}
    });
})();
</script>
</body>
</html>
<?php
}
