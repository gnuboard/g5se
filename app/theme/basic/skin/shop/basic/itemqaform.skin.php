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

<!-- 상품문의 쓰기 시작 { -->
<div id="sit_qa_write" class="new_win">
    <div class="qa_write_header">
        <div>
            <h1 id="win_title">상품문의 쓰기</h1>
            <p><?php echo get_text($row['it_name']); ?></p>
        </div>
        <a href="<?php echo get_pretty_url('shop', $it_id); ?>" onclick="if (window.opener) { window.close(); return false; }" class="qa_write_close" aria-label="닫기"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></a>
    </div>

    <form name="fitemqa" method="post" action="<?php echo G5_SHOP_URL;?>/itemqaformupdate" onsubmit="return fitemqa_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="iq_id" value="<?php echo $iq_id; ?>">

    <div class="form_01 new_win_con">
        <ul class="qa_write_fields">
            <li class="qa_write_field qa_write_secret chk_box">
                <strong class="sound_only">옵션</strong>
                <input type="checkbox" name="iq_secret" id="iq_secret" value="1" <?php echo $chk_secret; ?> class="selec_chk">
                <label for="iq_secret"><span></span>비밀글</label> 
            </li>
            <li class="qa_write_field qa_write_contacts">
                <div class="form_left">
                    <label for="iq_email" class="sound_only">이메일</label>
                    <input type="text" name="iq_email" id="iq_email" value="<?php echo get_text($qa['iq_email']); ?>" class="frm_input full_input" size="30" placeholder="이메일"><br>
                    <span class="frm_info">이메일을 입력하시면 답변 등록 시 답변이 이메일로 전송됩니다.</span>
                </div>
                <div class="form_right">
                    <label for="iq_hp" class="sound_only">휴대폰</label>
                    <input type="text" name="iq_hp" id="iq_hp" value="<?php echo get_text($qa['iq_hp']); ?>" class="frm_input full_input" size="20" placeholder="휴대폰"><br>
                    <span class="frm_info">휴대폰번호를 입력하시면 답변 등록 시 답변등록 알림이 SMS로 전송됩니다.</span>
                </div>
            </li>
            <li class="qa_write_field">
                <label for="iq_subject" class="sound_only">제목<strong> 필수</strong></label>
                <input type="text" name="iq_subject" value="<?php echo get_text($qa['iq_subject']); ?>" id="iq_subject" required class="required frm_input" maxlength="250" placeholder="제목">
            </li>
            <li class="qa_write_field qa_write_content">
                <label for="iq_question" class="sound_only">질문</label>
                <?php echo $editor_html; ?>
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
function fitemqa_submit(f)
{
    <?php echo $editor_js; ?>

    return true;
}
</script>
<style>
:root {--qw-bg:#f4f6f9;--qw-surface:#fff;--qw-surface-2:#f7f9fc;--qw-text:#111827;--qw-soft:#64748b;--qw-border:#dbe2ea;--qw-primary:#3b82f6;--qw-primary-soft:#eaf2ff;--qw-shadow:0 18px 50px rgba(15,23,42,.1)}
[data-theme="dark"] {color-scheme:dark;--qw-bg:#080d19;--qw-surface:#111827;--qw-surface-2:#182131;--qw-text:#f1f5f9;--qw-soft:#a9b5c7;--qw-border:#2b374a;--qw-primary:#60a5fa;--qw-primary-soft:rgba(59,130,246,.16);--qw-shadow:0 20px 60px rgba(0,0,0,.35)}
html,body {min-height:100%;background:var(--qw-bg) !important;color:var(--qw-text)}
body {margin:0;padding:8px;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
#sit_qa_write.new_win {width:min(760px,100%);min-height:0;margin:0 auto;overflow:hidden;border:1px solid var(--qw-border);border-radius:20px;background:var(--qw-surface) !important;box-shadow:var(--qw-shadow)}
#sit_qa_write .qa_write_header {display:flex;align-items:center;justify-content:space-between;gap:14px;padding:10px 14px;border-bottom:1px solid var(--qw-border);background:var(--qw-surface)}
#sit_qa_write #win_title {height:auto;margin:0;padding:0;background:transparent !important;color:var(--qw-text) !important;font-size:22px;line-height:1.3;box-shadow:none}
.qa_write_header p {margin:3px 0 0;color:var(--qw-soft);font-size:13px}
.qa_write_close {display:grid;flex:0 0 34px;place-items:center;width:34px;height:34px;border:1px solid var(--qw-border);border-radius:10px;color:var(--qw-soft);text-decoration:none}
.qa_write_close:hover {background:var(--qw-surface-2);color:var(--qw-text)}
.qa_write_close svg {width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round}
#sit_qa_write .new_win_con {margin:0;padding:12px 14px;background:var(--qw-surface)}
.qa_write_fields {display:grid;gap:8px;margin:0;padding:0;list-style:none}
#sit_qa_write .qa_write_field {display:grid;gap:5px;margin:0;padding:0}
.qa_write_contacts {grid-template-columns:repeat(2,minmax(0,1fr));gap:10px !important}
.qa_write_contacts>div {min-width:0}
#sit_qa_write input[type="text"],#sit_qa_write textarea {width:100%;min-width:0;border:1px solid var(--qw-border);border-radius:12px;background:var(--qw-surface-2);color:var(--qw-text);font-size:14px;box-shadow:none}
#sit_qa_write input[type="text"] {height:40px;padding:0 12px}
#sit_qa_write textarea {min-height:140px !important;height:140px}
#sit_qa_write input[type="text"]:focus,#sit_qa_write textarea:focus {border-color:var(--qw-primary);outline:3px solid var(--qw-primary-soft)}
#sit_qa_write .frm_info {display:block;margin-top:4px;color:var(--qw-soft);font-size:11px;line-height:1.4}
#sit_qa_write .qa_write_secret {display:flex;align-items:center;justify-content:flex-end}
#sit_qa_write .qa_write_secret label {color:var(--qw-text);font-size:13px;font-weight:700}
#sit_qa_write .cke {overflow:hidden;border:1px solid var(--qw-border) !important;border-radius:12px;box-shadow:none !important}
#sit_qa_write .cke_top,#sit_qa_write .cke_bottom {border-color:var(--qw-border) !important;background:var(--qw-surface-2) !important}
#sit_qa_write .cke_contents {height:140px !important}
#sit_qa_write .win_btn {display:flex;justify-content:flex-end;gap:8px;margin:8px 0 0;padding:8px 0 0;border-top:1px solid var(--qw-border)}
#sit_qa_write .win_btn .btn_submit,#sit_qa_write .win_btn .btn_close {display:inline-flex;align-items:center;justify-content:center;width:auto;height:42px;margin:0;padding:0 20px;border-radius:11px;font-size:14px;font-weight:700;line-height:42px;text-decoration:none}
#sit_qa_write .win_btn .btn_submit {border:1px solid var(--qw-primary);background:var(--qw-primary);color:#fff}
#sit_qa_write .win_btn .btn_close {border:1px solid var(--qw-border);background:var(--qw-surface);color:var(--qw-text)}
@media (max-width:600px) {
    body {padding:0}
    #sit_qa_write.new_win {width:100%;min-height:100vh;border:0;border-radius:0;box-shadow:none}
    #sit_qa_write .qa_write_header {padding:9px 12px}
    #sit_qa_write #win_title {font-size:20px}
    #sit_qa_write .new_win_con {padding:10px 12px}
    .qa_write_contacts {grid-template-columns:1fr}
    #sit_qa_write textarea,#sit_qa_write .cke_contents {min-height:130px !important;height:130px !important}
    #sit_qa_write .win_btn {position:sticky;bottom:0;display:grid;grid-template-columns:1fr auto;margin:8px -12px -10px;padding:8px 12px calc(8px + env(safe-area-inset-bottom));background:var(--qw-surface)}
    #sit_qa_write .win_btn .btn_submit {width:100%}
}
</style>
<!-- } 상품문의 쓰기 끝 -->
