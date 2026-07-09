<?php
include_once('./_common.php');
// 모던 디자인 토큰 큐에 등록 (head.sub.php 가 큐를 출력하므로 이전에 require)
if (defined('G5_THEME_PATH') && is_file(G5_THEME_PATH.'/modern/_head.inc.php')) {
    require_once(G5_THEME_PATH.'/modern/_head.inc.php');
}
include_once(G5_PATH.'/head.sub.php');

$pattern1 = "/[\<\>\'\"\\\'\\\"\(\)]/";
$pattern2 = "/\r\n|\r|\n|[^\x20-\x7e]/";

$url1 = isset($url1) ? preg_replace($pattern1, "", clean_xss_tags($url1, 1)) : '';
$url1 = preg_replace($pattern2, "", $url1);
$url2 = isset($url2) ? preg_replace($pattern1, "", clean_xss_tags($url2, 1)) : '';
$url2 = preg_replace($pattern2, "", $url2);
$url3 = isset($url3) ? preg_replace($pattern1, "", clean_xss_tags($url3, 1)) : '';
$url3 = preg_replace($pattern2, "", $url3);

$msg = isset($msg) ? $msg : '';
$header = isset($header) ? $msg : '';

// url 체크
check_url_host($url1);
check_url_host($url2);
check_url_host($url3);
?>

<script>
var conf = <?php echo function_exists('get_js_safe_string') ? get_js_safe_string(strip_tags($msg)) : '""'; ?>;
if (confirm(conf)) {
    document.location.replace("<?php echo $url1; ?>");
} else {
    document.location.replace("<?php echo $url2; ?>");
}
</script>

<noscript>
<div class="m-shell">
    <main class="m-center">
        <div class="m-card m-card-narrow m-confirm">
            <div class="m-confirm-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <?php $hdr = get_text(strip_tags($header)); if ($hdr) { ?>
            <h1 class="m-confirm-title"><?php echo $hdr ?></h1>
            <?php } else { ?>
            <h1 class="m-confirm-title">확인이 필요합니다</h1>
            <?php } ?>
            <p class="m-confirm-msg"><?php echo get_text(strip_tags($msg)); ?></p>

            <div class="m-confirm-actions">
                <a href="<?php echo $url1; ?>" class="m-btn">확인</a>
                <a href="<?php echo $url2; ?>" class="m-btn m-btn-ghost">취소</a>
                <?php if ($url3) { ?>
                <a href="<?php echo $url3; ?>" class="m-btn m-btn-ghost">돌아가기</a>
                <?php } ?>
            </div>
        </div>
    </main>
</div>

<style>
.m-confirm { text-align: center; padding: 36px 32px; }
.m-confirm-icon { display: flex; justify-content: center; margin-bottom: 12px; color: var(--m-primary); }
.m-confirm-title {
    margin: 0 0 8px;
    font-size: var(--m-text-lg); font-weight: 700; color: var(--m-text);
}
.m-confirm-msg {
    margin: 0 0 18px;
    font-size: var(--m-text-md); line-height: var(--m-leading-relaxed);
    color: var(--m-text-soft); word-break: break-word;
}
.m-confirm-actions { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; }
.m-confirm-actions .m-btn { width: auto; padding: 10px 22px; }
</style>
</noscript>

<?php
include_once(G5_PATH.'/tail.sub.php');