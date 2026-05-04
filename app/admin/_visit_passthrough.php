<?php
/*
 * Visit 통계 페이지 패스스루 헬퍼.
 * 14개 visit_*.php 가 같은 패턴 (admin.head + visit.sub + 컨텐츠 + admin.tail) 이라
 * _legacy_passthrough.php 와 동일하게 추출 후 modern shell 안에 wrap.
 */
if (!defined('_GNUBOARD_')) exit;

require_once G5_PATH.'/adm/admin.lib.php';

// 레거시 admin_referer_check 우회 — _legacy_passthrough 와 동일
if (!empty($_SERVER['HTTP_REFERER'])) {
    $_SERVER['HTTP_REFERER'] = preg_replace('#(/+)admin(/+)#', '$1adm$2', $_SERVER['HTTP_REFERER']);
}

add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?(visit_[a-z_]+)\.php(\?.*)?$#', $u, $m)) {
        header('Location: /admin/'.$m[1].($m[2] ?? ''), true, 302);
        exit;
    }
}, 10);

if (!empty($visit_is_post)) {
    chdir(G5_ADMIN_PATH);
    require G5_ADMIN_PATH.'/'.$visit_target;
    return;
}

ob_start();
chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/'.$visit_target;
$html = ob_get_clean();

$page_title = $g5['title'] ?? '방문자 통계';

$content = '';
if (preg_match('#<div class="container_wr">(.*?)<footer\s+id="ft"#si', $html, $m)) {
    $content = $m[1];
    $content = preg_replace('#(\s*</div>){2,4}\s*$#', '', $content);
} else {
    $content = $html;
}

// visit.sub.php 의 anchor 와 form action 을 클린 URL 로
$content = preg_replace_callback(
    '#(href|action)="\./(visit_[a-z_]+)\.php([^"]*)"#i',
    static fn($m) => $m[1].'="/admin/'.$m[2].$m[3].'"',
    $content
);

admin_layout_start($page_title, 'visit');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
    <header class="flex items-center gap-3 mb-5">
        <h2 class="text-xl font-bold tracking-tight"><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h2>
        <span class="text-xs text-slate-400">방문자 통계</span>
    </header>
    <div class="legacy-admin-content space-y-4">
        <?php echo $content ?>
    </div>
</main>
<script>
window.is_checked = window.is_checked || function (n) { var e = document.getElementsByName(n); for (var i=0;i<e.length;i++) if (e[i].checked) return true; return false; };
</script>
<?php
admin_layout_end();
