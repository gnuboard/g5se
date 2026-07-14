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

    <form name="fitemuse" method="post" action="<?php echo G5_SHOP_URL;?>/itemuseformupdate.php" onsubmit="return fitemuse_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="is_id" value="<?php echo $is_id; ?>">

    <div class="new_win_con form_01">
        <ul class="review_write_fields">
            <li class="review_write_field">
                <label for="is_subject">제목 <strong>필수</strong></label>
                <input type="text" name="is_subject" value="<?php echo get_text($use['is_subject']); ?>" id="is_subject" required class="required frm_input full_input"  maxlength="250" placeholder="제목">
            </li>
            <li class="review_write_field review_write_content">
                <strong>내용</strong>
                <?php echo $editor_html; ?>
            </li>
            <li class="review_write_field review_write_score">
                <strong>평점</strong>
                <ul id="sit_use_write_star" class="chk_box">
                    <li>
                        <input type="radio" name="is_score" value="5" id="is_score5" <?php echo ($is_score==5)?'checked="checked"':''; ?>>
                        <label for="is_score5"><span>매우만족</span><img src="<?php echo G5_URL; ?>/shop/img/s_star5.png" alt="별 5개"></label>
                    </li>
                    <li>
                        <input type="radio" name="is_score" value="4" id="is_score4" <?php echo ($is_score==4)?'checked="checked"':''; ?>>
                        <label for="is_score4"><span>만족</span><img src="<?php echo G5_URL; ?>/shop/img/s_star4.png" alt="별 4개"></label>
                    </li>
                    <li>
                        <input type="radio" name="is_score" value="3" id="is_score3" <?php echo ($is_score==3)?'checked="checked"':''; ?>>
                        <label for="is_score3"><span>보통</span><img src="<?php echo G5_URL; ?>/shop/img/s_star3.png" alt="별 3개"></label>
                    </li>
                    <li>
                        <input type="radio" name="is_score" value="2" id="is_score2" <?php echo ($is_score==2)?'checked="checked"':''; ?>>
                        <label for="is_score2"><span>불만</span><img src="<?php echo G5_URL; ?>/shop/img/s_star2.png" alt="별 2개"></label>
                    </li>
                    <li>
                        <input type="radio" name="is_score" value="1" id="is_score1" <?php echo ($is_score==1)?'checked="checked"':''; ?>>
                        <label for="is_score1"><span>매우불만</span><img src="<?php echo G5_URL; ?>/shop/img/s_star1.png" alt="별 1개"></label>
                    </li>
                </ul>
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
body {margin:0;padding:24px;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
#sit_use_write.new_win {width:min(760px,100%);min-height:0;margin:0 auto;overflow:hidden;border:1px solid var(--rw-border);border-radius:20px;background:var(--rw-surface) !important;box-shadow:var(--rw-shadow)}
#sit_use_write .review_write_header {display:flex;align-items:center;justify-content:space-between;gap:20px;padding:22px 24px;border-bottom:1px solid var(--rw-border);background:var(--rw-surface)}
#sit_use_write #win_title {height:auto;margin:0;padding:0;background:transparent !important;color:var(--rw-text) !important;font-size:22px;line-height:1.3;box-shadow:none}
.review_write_header p {margin:5px 0 0;color:var(--rw-soft);font-size:13px}
.review_write_close {display:grid;flex:0 0 40px;place-items:center;width:40px;height:40px;border:1px solid var(--rw-border);border-radius:12px;color:var(--rw-soft);text-decoration:none}
.review_write_close:hover {background:var(--rw-surface-2);color:var(--rw-text)}
.review_write_close svg {width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round}
#sit_use_write .new_win_con {margin:0;padding:24px;background:var(--rw-surface)}
.review_write_fields {display:grid;gap:22px;margin:0;padding:0;list-style:none}
.review_write_field {display:grid;gap:9px;margin:0}
.review_write_field>label,.review_write_field>strong {color:var(--rw-text);font-size:14px;font-weight:700}
.review_write_field>label strong {color:var(--rw-primary);font-size:11px}
#sit_use_write #is_subject,#sit_use_write textarea {width:100%;min-width:0;border:1px solid var(--rw-border);border-radius:12px;background:var(--rw-surface-2);color:var(--rw-text);font-size:15px;box-shadow:none}
#sit_use_write #is_subject {height:48px;padding:0 14px}
#sit_use_write #is_subject:focus,#sit_use_write textarea:focus {border-color:var(--rw-primary);outline:3px solid var(--rw-primary-soft)}
#sit_use_write .cke {overflow:hidden;border:1px solid var(--rw-border) !important;border-radius:12px;box-shadow:none !important}
#sit_use_write .cke_top,#sit_use_write .cke_bottom {border-color:var(--rw-border) !important;background:var(--rw-surface-2) !important}
#sit_use_write_star {display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px;margin:0;padding:0;list-style:none}
#sit_use_write_star:after {display:none}
#sit_use_write_star li {float:none;width:auto;margin:0;padding:0}
#sit_use_write_star input {position:absolute;z-index:0;width:1px;height:1px;opacity:0;clip-path:inset(50%)}
#sit_use_write_star label {display:flex;min-height:68px;flex-direction:column;align-items:center;justify-content:center;gap:7px;padding:10px 6px;border:1px solid var(--rw-border);border-radius:12px;background:var(--rw-surface-2);color:var(--rw-soft);font-size:12px;font-weight:700;cursor:pointer;transition:border-color .15s ease,background .15s ease,color .15s ease}
#sit_use_write_star label span {position:static;width:auto;height:auto;border:0;border-radius:0;background:transparent;color:inherit}
#sit_use_write_star label span:before {display:none !important}
#sit_use_write_star label img {width:78px;max-width:100%;height:auto}
#sit_use_write_star input:checked+label {border-color:var(--rw-primary);background:var(--rw-primary-soft);color:var(--rw-primary);box-shadow:inset 0 0 0 1px var(--rw-primary)}
#sit_use_write_star input:focus-visible+label {outline:3px solid var(--rw-primary-soft);outline-offset:2px}
#sit_use_write .win_btn {display:flex;justify-content:flex-end;gap:8px;margin:24px 0 0;padding:20px 0 0;border-top:1px solid var(--rw-border)}
#sit_use_write .win_btn .btn_submit,#sit_use_write .win_btn .btn_close {display:inline-flex;align-items:center;justify-content:center;width:auto;height:46px;margin:0;padding:0 22px;border-radius:11px;font-size:14px;font-weight:700;line-height:46px;text-decoration:none}
#sit_use_write .win_btn .btn_submit {border:1px solid var(--rw-primary);background:var(--rw-primary);color:#fff}
#sit_use_write .win_btn .btn_close {border:1px solid var(--rw-border);background:var(--rw-surface);color:var(--rw-text)}
@media (max-width:600px) {
    body {padding:0}
    #sit_use_write.new_win {width:100%;min-height:100vh;border:0;border-radius:0;box-shadow:none}
    #sit_use_write .review_write_header {padding:18px 16px}
    #sit_use_write #win_title {font-size:20px}
    #sit_use_write .new_win_con {padding:20px 16px}
    #sit_use_write_star {grid-template-columns:repeat(2,minmax(0,1fr))}
    #sit_use_write_star li:last-child {grid-column:1/-1}
    #sit_use_write .win_btn {position:sticky;bottom:0;display:grid;grid-template-columns:1fr auto;margin:22px -16px -20px;padding:12px 16px calc(12px + env(safe-area-inset-bottom));background:var(--rw-surface)}
    #sit_use_write .win_btn .btn_submit {width:100%}
}
</style>
<!-- } 사용후기 쓰기 끝 -->
