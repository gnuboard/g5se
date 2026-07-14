<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<script>
(function(){
    try {
        var theme = localStorage.getItem('m-theme');
        if (!theme && window.opener && window.opener.document) theme = window.opener.document.documentElement.dataset.theme;
        if (!theme) theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.dataset.theme = theme;
    } catch (e) {}
})();
</script>

<!-- 사용후기 쓰기 시작 { -->
<div id="sit_use_write" class="new_win">
    <header class="review_write_header">
        <div>
            <h1 id="win_title">사용후기 쓰기</h1>
            <p><?php echo get_text($row['it_name']); ?></p>
        </div>
        <a href="<?php echo get_pretty_url('shop', $it_id); ?>" class="review_write_close" aria-label="작성창 닫기" onclick="if (window.opener) { window.close(); return false; }">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </a>
    </header>

    <form name="fitemuse" method="post" action="<?php echo G5_SHOP_URL;?>/itemuseformupdate" onsubmit="return fitemuse_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="is_id" value="<?php echo $is_id; ?>">

    <div class="new_win_con form_01">
        <ul class="review_write_fields">
            <li class="review_write_field">
                <label for="is_subject" class="sound_only">제목 필수</label>
                <input type="text" name="is_subject" value="<?php echo get_text($use['is_subject']); ?>" id="is_subject" required class="required frm_input full_input"  maxlength="250" placeholder="제목">
            </li>
            <li class="review_write_field review_write_content">
                <strong class="sound_only">내용</strong>
                <?php echo $editor_html; ?>
            </li>
            <li class="review_write_field review_write_score">
                <label for="is_score">평점</label>
                <div class="review_score_select">
                    <select name="is_score" id="is_score" required>
                        <option value="5" <?php echo ($is_score==5)?'selected="selected"':''; ?>>★★★★★ 매우 만족</option>
                        <option value="4" <?php echo ($is_score==4)?'selected="selected"':''; ?>>★★★★☆ 만족</option>
                        <option value="3" <?php echo ($is_score==3)?'selected="selected"':''; ?>>★★★☆☆ 보통</option>
                        <option value="2" <?php echo ($is_score==2)?'selected="selected"':''; ?>>★★☆☆☆ 불만</option>
                        <option value="1" <?php echo ($is_score==1)?'selected="selected"':''; ?>>★☆☆☆☆ 매우 불만</option>
                    </select>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                </div>
            </li>
        </ul>

        <div class="win_btn">
            <button type="submit" class="btn_submit">작성완료</button>
            <a href="<?php echo get_pretty_url('shop', $it_id); ?>" onclick="if (window.opener) { window.close(); return false; }" class="btn_close">취소</a>
        </div>
    </div>
    </form>
</div>

<script type="text/javascript">
function fitemuse_submit(f)
{
    <?php echo $editor_js; ?>

    return true;
}
</script>
<style>
:root {--rw-bg:#f4f6f9;--rw-surface:#fff;--rw-surface-2:#f7f9fc;--rw-text:#111827;--rw-soft:#64748b;--rw-border:#dbe2ea;--rw-primary:#3b82f6;--rw-primary-soft:#eaf2ff;--rw-shadow:0 18px 50px rgba(15,23,42,.1)}
[data-theme="dark"] {color-scheme:dark;--rw-bg:#080d19;--rw-surface:#111827;--rw-surface-2:#182131;--rw-text:#f1f5f9;--rw-soft:#a9b5c7;--rw-border:#2b374a;--rw-primary:#60a5fa;--rw-primary-soft:rgba(59,130,246,.16);--rw-shadow:0 20px 60px rgba(0,0,0,.35)}
html,body {min-height:100%;background:var(--rw-bg) !important;color:var(--rw-text)}
body {margin:0;padding:8px;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
#sit_use_write.new_win {width:min(760px,100%);min-height:0;margin:0 auto;overflow:hidden;border:1px solid var(--rw-border);border-radius:20px;background:var(--rw-surface) !important;box-shadow:var(--rw-shadow)}
#sit_use_write .review_write_header {display:flex;align-items:center;justify-content:space-between;gap:14px;padding:10px 14px;border-bottom:1px solid var(--rw-border);background:var(--rw-surface)}
#sit_use_write #win_title {height:auto;margin:0;padding:0;background:transparent !important;color:var(--rw-text) !important;font-size:22px;line-height:1.3;box-shadow:none}
.review_write_header p {margin:3px 0 0;color:var(--rw-soft);font-size:13px}
.review_write_close {display:grid;flex:0 0 34px;place-items:center;width:34px;height:34px;border:1px solid var(--rw-border);border-radius:10px;color:var(--rw-soft);text-decoration:none}
.review_write_close:hover {background:var(--rw-surface-2);color:var(--rw-text)}
.review_write_close svg {width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round}
#sit_use_write .new_win_con {margin:0;padding:12px 14px;background:var(--rw-surface)}
.review_write_fields {display:grid;gap:6px;margin:0;padding:0;list-style:none}
#sit_use_write .review_write_fields>.review_write_field {display:grid;gap:5px;margin:0 !important;padding:0 !important}
.review_write_field>label,.review_write_field>strong {color:var(--rw-text);font-size:14px;font-weight:700}
.review_write_field>label strong {color:var(--rw-primary);font-size:11px}
#sit_use_write #is_subject,#sit_use_write textarea {width:100%;min-width:0;border:1px solid var(--rw-border);border-radius:12px;background:var(--rw-surface-2);color:var(--rw-text);font-size:15px;box-shadow:none}
#sit_use_write #is_subject {height:40px;padding:0 12px}
#sit_use_write textarea {min-height:150px !important;height:150px}
#sit_use_write #is_subject:focus,#sit_use_write textarea:focus {border-color:var(--rw-primary);outline:3px solid var(--rw-primary-soft)}
#sit_use_write .cke {overflow:hidden;border:1px solid var(--rw-border) !important;border-radius:12px;box-shadow:none !important}
#sit_use_write .cke_top,#sit_use_write .cke_bottom {border-color:var(--rw-border) !important;background:var(--rw-surface-2) !important}
#sit_use_write .cke_contents {height:150px !important}
.review_score_select {position:relative}
.review_score_select select {width:100%;height:42px;padding:0 42px 0 14px;border:1px solid var(--rw-border);border-radius:12px;appearance:none;background:var(--rw-surface-2);color:var(--rw-text);font-size:14px;font-weight:700;box-shadow:none;cursor:pointer}
.review_score_select select:focus {border-color:var(--rw-primary);outline:3px solid var(--rw-primary-soft)}
.review_score_select svg {position:absolute;top:50%;right:14px;width:18px;height:18px;transform:translateY(-50%);fill:none;stroke:var(--rw-soft);stroke-width:2;stroke-linecap:round;stroke-linejoin:round;pointer-events:none}
[data-theme="dark"] .review_score_select option {background:var(--rw-surface);color:var(--rw-text)}
#sit_use_write .win_btn {display:flex;justify-content:flex-end;gap:8px;margin:8px 0 0;padding:8px 0 0;border-top:1px solid var(--rw-border)}
#sit_use_write .win_btn .btn_submit,#sit_use_write .win_btn .btn_close {display:inline-flex;align-items:center;justify-content:center;width:auto;height:42px;margin:0;padding:0 20px;border-radius:11px;font-size:14px;font-weight:700;line-height:42px;text-decoration:none}
#sit_use_write .win_btn .btn_submit {border:1px solid var(--rw-primary);background:var(--rw-primary);color:#fff}
#sit_use_write .win_btn .btn_close {border:1px solid var(--rw-border);background:var(--rw-surface);color:var(--rw-text)}
@media (max-width:600px) {
    body {padding:0}
    #sit_use_write.new_win {width:100%;min-height:100vh;border:0;border-radius:0;box-shadow:none}
    #sit_use_write .review_write_header {padding:9px 12px}
    #sit_use_write #win_title {font-size:20px}
    #sit_use_write .new_win_con {padding:10px 12px}
    #sit_use_write #is_subject {height:40px}
    #sit_use_write textarea {min-height:140px !important;height:140px}
    #sit_use_write .cke_contents {height:140px !important}
    .review_score_select select {height:42px;font-size:13px}
    #sit_use_write .win_btn {position:sticky;bottom:0;display:grid;grid-template-columns:1fr auto;margin:8px -12px -10px;padding:8px 12px calc(8px + env(safe-area-inset-bottom));background:var(--rw-surface)}
    #sit_use_write .win_btn .btn_submit {width:100%}
}
</style>
<!-- } 사용후기 쓰기 끝 -->
