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
        body { background-color: var(--slate-50); color: var(--slate-900); padding: 1.5rem; }
        html.dark body { background-color: var(--slate-950); color: var(--slate-100); }
        .popup-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
        .popup-head h1 { font-size: 1.125rem; font-weight: 700; }
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
