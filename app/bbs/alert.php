<?php
global $lo_location;
global $lo_url;

include_once('./_common.php');

if($error) {
    $g5['title'] = "오류안내 페이지";
} else {
    $g5['title'] = "결과안내 페이지";
}
// 모던 디자인 토큰 큐에 등록 (head.sub.php 가 큐를 출력하므로 이전에 require)
if (defined('G5_THEME_PATH') && is_file(G5_THEME_PATH.'/modern/_head.inc.php')) {
    require_once(G5_THEME_PATH.'/modern/_head.inc.php');
}
include_once(G5_PATH.'/head.sub.php');
// 필수 입력입니다.
// 양쪽 공백 없애기
// 필수 (선택 혹은 입력)입니다.
// 전화번호 형식이 올바르지 않습니다. 하이픈(-)을 포함하여 입력하세요.
// 이메일주소 형식이 아닙니다.
// 한글이 아닙니다. (자음, 모음만 있는 한글은 처리하지 않습니다.)
// 한글이 아닙니다.
// 한글, 영문, 숫자가 아닙니다.
// 한글, 영문이 아닙니다.
// 숫자가 아닙니다.
// 영문이 아닙니다.
// 영문 또는 숫자가 아닙니다.
// 영문, 숫자, _ 가 아닙니다.
// 최소 글자 이상 입력하세요.
// 이미지 파일이 아닙니다..gif .jpg .png 파일만 가능합니다.
// 파일만 가능합니다.
// 공백이 없어야 합니다.

$msg = isset($msg) ? strip_tags($msg) : '';
$msg2 = str_replace("\\n", "<br>", $msg);

$url = isset($url) ? clean_xss_tags($url, 1) : '';
if (!$url) $url = isset($_SERVER['HTTP_REFERER']) ? clean_xss_tags($_SERVER['HTTP_REFERER'], 1) : '';

$url = preg_replace("/[\<\>\'\"\\\'\\\"\(\)]/", "", $url);
$url = preg_replace('/\r\n|\r|\n|[^\x20-\x7e]/','', $url);

// url 체크
check_url_host($url, $msg);

if($error) {
    $header2 = "다음 항목에 오류가 있습니다.";
} else {
    $header2 = "다음 내용을 확인해 주세요.";
}
?>

<script>
alert(<?php echo function_exists('get_js_safe_string') ? get_js_safe_string($msg) : '""'; ?>);
<?php if ($url) { ?>
document.location.replace("<?php echo str_replace('&amp;', '&', $url); ?>");
<?php } else { ?>
history.back();
<?php } ?>
</script>

<noscript>
<div class="m-shell">
    <main class="m-center">
        <div class="m-card m-card-narrow m-alert m-alert-<?php echo $error ? 'error' : 'info' ?>">
            <div class="m-alert-icon">
                <?php if ($error) { ?>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php } else { ?>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
                <?php } ?>
            </div>
            <h1 class="m-alert-title"><?php echo $header2 ?></h1>
            <p class="m-alert-msg"><?php echo $msg2 ?></p>

            <?php if ($post) { ?>
            <form method="post" action="<?php echo $url ?>" class="m-alert-actions">
                <?php foreach ($_POST as $key => $value) {
                    $key = clean_xss_tags($key);
                    $value = clean_xss_tags($value);
                    if (strlen($value) < 1) continue;
                    if (preg_match("/pass|pwd|capt|url/", $key)) continue;
                ?>
                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                <?php } ?>
                <button type="submit" class="m-btn">돌아가기</button>
            </form>
            <?php } else { ?>
            <div class="m-alert-actions">
                <a href="<?php echo $url ?>" class="m-btn">돌아가기</a>
            </div>
            <?php } ?>
        </div>
    </main>
</div>

<style>
.m-alert { text-align: center; padding: 36px 32px; }
.m-alert-icon { display: flex; justify-content: center; margin-bottom: 12px; }
.m-alert-error .m-alert-icon { color: #ef4444; }
.m-alert-info  .m-alert-icon { color: var(--m-primary); }
.m-alert-title {
    margin: 0 0 8px;
    font-size: var(--m-text-lg); font-weight: 700; color: var(--m-text);
}
.m-alert-msg {
    margin: 0 0 18px;
    font-size: var(--m-text-md); line-height: var(--m-leading-relaxed);
    color: var(--m-text-soft); word-break: break-word;
}
.m-alert-actions { display: flex; justify-content: center; gap: 8px; }
.m-alert-actions .m-btn { width: auto; padding: 10px 22px; }
</style>
</noscript>

<?php
include_once(G5_PATH.'/tail.sub.php');