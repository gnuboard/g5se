<?php
/*
 * 레거시 admin 페이지 패스스루 (visit 외).
 *
 * gnuboard adm/*.php 가 admin.head.php / admin.tail.php 로 자체 HTML 문서를
 * 그리는데, 이 헬퍼는:
 *   1) ob_start 로 출력 캡처
 *   2) admin.head 가 그린 <header>...<wrapper>...<container_wr> 안의 form 본문만 추출
 *   3) modern admin shell (admin_layout_start/end) 안에 .legacy-admin-content 로 wrap
 *      → input.css 의 컴포넌트 레이어가 .frm_input/.tbl_frm01/.btn 등을 모던 스타일로 매핑
 *
 * 호출 예 (admin/auth_list.php 에서):
 *   $legacy_target    = 'auth_list.php';
 *   $legacy_menu_key  = 'auth';        // (선택) 사이드바 active key
 *   $legacy_is_post   = true;          // (선택) POST 핸들러는 ob 없이 require
 */
if (!defined('_GNUBOARD_')) exit;

require_once G5_PATH.'/adm/admin.lib.php';

// goto_url 의 ./foo.php 또는 ./bar.php?... 를 /admin/foo, /admin/bar?... 로 변환
add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?([a-z][a-z0-9_]*)\.php(\?.*)?$#i', $u, $m)) {
        header('Location: /admin/'.$m[1].($m[2] ?? ''), true, 302);
        exit;
    }
}, 10);

if (!empty($legacy_is_post)) {
    chdir(G5_ADMIN_PATH);
    require G5_ADMIN_PATH.'/'.$legacy_target;
    return;
}

ob_start();
chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/'.$legacy_target;
$html = ob_get_clean();

$page_title = $g5['title'] ?? '관리자';

// 1) 본문 추출 — admin.head.php 가 `<div class="container_wr">` 으로 컨텐츠 영역을 열고
//    admin.tail.php 가 `<footer id="ft">` 직전에 닫는다.
//    추출 후 닫는 div 들도 제거.
$content = '';
if (preg_match('#<div class="container_wr">(.*?)<footer\s+id="ft"#si', $html, $m)) {
    $content = $m[1];
    // 끝의 닫는 </div> 들 제거 (container_wr / container / wrapper)
    $content = preg_replace('#(\s*</div>){2,4}\s*$#', '', $content);
} else {
    // 추출 실패 시 본문 폴백
    $content = $html;
}

// 2) form action / 링크의 ./foo.php → /admin/foo 일괄 변환
$content = preg_replace_callback(
    '#(href|action)="\./([a-z][a-z0-9_]*)\.php([^"]*)"#i',
    static fn($m) => $m[1].'="/admin/'.$m[2].$m[3].'"',
    $content
);

// 3) form 의 self-action 보완 — action 이 없는 form 의 경우 클린 URL 주입
if (!empty($legacy_form_replace) && is_array($legacy_form_replace)) {
    foreach ($legacy_form_replace as $needle => $action) {
        $content = str_replace($needle, $needle.' action="'.$action.'"', $content);
    }
}

// 4) modern admin shell 안에 wrap
admin_layout_start($page_title, $legacy_menu_key ?? '');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
    <header class="flex items-center gap-3 mb-5">
        <h2 class="text-xl font-bold tracking-tight"><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h2>
        <span class="text-xs text-slate-400">레거시 페이지</span>
    </header>
    <div class="legacy-admin-content space-y-4">
        <?php echo $content ?>
    </div>
</main>
<script>
// 레거시 admin 이 사용하는 헬퍼 (gnuboard 의 admin.head 안 inline JS 정의분)
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
</script>
<?php
admin_layout_end();
