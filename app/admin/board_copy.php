<?php
/*
 * /admin/board_copy — 게시판 복사 (popup 창).
 *
 * board_list 의 '복사' 링크에서 새 창으로 열림 — admin shell (사이드바/헤더) 없이
 * 깨끗한 폼만 노출. gnuboard adm/board_copy.php 의 .new_win 구조를 그대로 사용.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

// gnuboard adm/board_copy.php 가 admin.head/admin.tail 로 자체 head/tail 을 그리지만
// admin shell 없는 popup 으로 띄우려면 본문만 직접 추출. ob 로 캡처 후 본문만 출력.
ob_start();
chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/board_copy.php';
$html = ob_get_clean();

$content = '';
if (preg_match('#<div class="container_wr">(.*?)<footer\s+id="ft"#si', $html, $m)) {
    $content = preg_replace('#(\s*</div>){2,4}\s*$#', '', $m[1]);
} else {
    $content = $html;
}

// form action 의 ./xxx.php → /admin/xxx 로 변환 (clean URL)
$content = preg_replace_callback(
    '#(href|action)="\./([a-z][a-z0-9_]*)\.php([^"]*)"#i',
    static fn($m) => $m[1].'="/admin/'.$m[2].$m[3].'"',
    $content
);

// gnuboard 의 admin.js 로더 + g5_admin_csrf_token_key 변수 정의 제거.
// admin.js 는 form submit 시 /adm/ajax.token.php 로 AJAX 호출해 token 을 갱신하지만,
// 우리 환경에선 그 endpoint 가 없어 빈 토큰으로 덮어쓰는 부작용 → check_admin_token 실패.
// 우리 inline JS 가 페이지 로드 시점의 get_admin_token() 결과를 form 에 채워주므로 admin.js 불필요.
$content = preg_replace('#<script[^>]*>\s*var\s+g5_admin_csrf_token_key\s*=.*?</script>#si', '', $content);
$content = preg_replace('#<script[^>]*src="[^"]*admin\.js[^"]*"[^>]*></script>#i', '', $content);

$page_title = $g5['title'] ?? '게시판 복사';
$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="ko" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?php echo $h($page_title) ?> · 관리자</title>
    <script>(function(){try{var t=localStorage.getItem("m-theme");if(!t)t=matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";document.documentElement.dataset.theme=t;if(t==='dark')document.documentElement.classList.add('dark');}catch(e){}})();</script>
    <link rel="stylesheet" href="/admin/css/admin.css">
    <style>
        html, body { font-family: -apple-system, BlinkMacSystemFont, "Apple SD Gothic Neo", "Malgun Gothic", "Noto Sans KR", system-ui, sans-serif; }
        html, body { background-color: var(--slate-50); }
        body { color: var(--slate-900); padding: 1.5rem; margin: 0; min-height: 100vh; }
        html.dark, html.dark body, html[data-theme="dark"], html[data-theme="dark"] body {
            background-color: var(--slate-950) !important;
            color: var(--slate-100);
        }
        .popup-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
        .popup-head h1 { font-size: 1.125rem; font-weight: 700; }

        /* 다크모드 폴백 — popup 안의 .new_win / .legacy-admin-content / form 영역에서
           라이트 톤이 새지 않도록 모든 컨테이너에 다크 bg 강제. */
        html.dark .legacy-admin-content,
        html.dark .legacy-admin-content .new_win,
        html.dark .legacy-admin-content form,
        html.dark .legacy-admin-content .new_win_con {
            background-color: transparent !important;
            color: var(--slate-100);
        }
        html.dark .legacy-admin-content .frm_input,
        html.dark .legacy-admin-content input[type="text"]:not([class*="rounded"]),
        html.dark .legacy-admin-content input[type="password"]:not([class*="rounded"]),
        html.dark .legacy-admin-content input[type="number"]:not([class*="rounded"]),
        html.dark .legacy-admin-content input[type="email"]:not([class*="rounded"]),
        html.dark .legacy-admin-content textarea:not([class*="rounded"]),
        html.dark .legacy-admin-content select:not([class*="rounded"]) {
            border-color: var(--slate-700) !important;
            background-color: var(--slate-800) !important;
            color: var(--slate-100) !important;
        }
        html.dark .legacy-admin-content .tbl_frm01,
        html.dark .legacy-admin-content .tbl_frm01.tbl_wrap {
            border-color: var(--slate-800) !important;
            background-color: var(--slate-900) !important;
        }
        html.dark .legacy-admin-content .tbl_frm01 tbody th {
            background-color: #15202b !important;
            color: var(--slate-300) !important;
            border-bottom-color: var(--slate-800) !important;
        }
        html.dark .legacy-admin-content .tbl_frm01 tbody td {
            background-color: var(--slate-900) !important;
            border-bottom-color: var(--slate-800) !important;
            color: var(--slate-300) !important;
        }
        html.dark .legacy-admin-content .win_btn {
            background-color: transparent !important;
            border-top-color: var(--slate-800) !important;
        }
        html.dark .legacy-admin-content .btn,
        html.dark .legacy-admin-content input[type="submit"],
        html.dark .legacy-admin-content input[type="button"],
        html.dark .legacy-admin-content button:not([class*="rounded"]) {
            border-color: var(--slate-700) !important;
            background-color: var(--slate-800) !important;
            color: var(--slate-300) !important;
        }
        /* Primary 버튼 (.btn_submit / .btn_01) 은 admin-primary 톤 강제 */
        html.dark .legacy-admin-content .btn_submit,
        html.dark .legacy-admin-content .btn_01,
        html.dark .legacy-admin-content input[type="submit"].btn_submit,
        html.dark .legacy-admin-content input[type="submit"].btn_01 {
            background-color: var(--admin-primary-600) !important;
            border-color: var(--admin-primary-600) !important;
            color: #fff !important;
        }
        html.dark .legacy-admin-content .btn_submit:hover,
        html.dark .legacy-admin-content .btn_01:hover {
            background-color: var(--admin-primary-700) !important;
        }
    </style>
</head>
<body>
    <div class="popup-head">
        <h1><?php echo $h($page_title) ?></h1>
    </div>
    <div class="legacy-admin-content">
        <?php echo $content ?>
    </div>
<script>
// .btn_close 클릭 시 진짜 popup 이면 window.close, 아니면 history.back()
document.addEventListener('click', function (e) {
    var b = e.target.closest('.btn_close');
    if (!b) return;
    if (window.opener && !window.opener.closed) return;  // 진짜 팝업 (그대로 close)
    e.preventDefault();
    if (history.length > 1) history.back();
    else location.href = '/admin/board_list';
});
// 폼 submit 시 hidden token 자동 채움
(function () {
    var ADMIN_TOKEN = <?php echo json_encode(get_admin_token()) ?>;
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f || f.tagName !== 'FORM') return;
        var t = f.querySelector('input[name="token"]');
        if (t && !t.value) t.value = ADMIN_TOKEN;
    }, true);
})();
</script>
</body>
</html>
