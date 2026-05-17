<?php
include_once('./_common.php');

$it_id = isset($_REQUEST['it_id']) ? safe_replace_regex($_REQUEST['it_id'], 'it_id') : '';

if (!$is_member)
    alert_close('회원만 메일을 발송할 수 있습니다.');

// 스팸을 발송할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = get_random_token_string(16);
set_session("ss_token", $token);

$it = sql_pdo_fetch(" select it_name from {$g5['g5_shop_item_table']} where it_id = :it_id ", [':it_id' => $it_id]);
if (!$it['it_name'])
    alert_close("등록된 상품이 아닙니다.");

$g5['title'] =  $it['it_name'].' - 추천하기';
include_once(G5_PATH.'/head.sub.php');
?>

<!-- 상품 추천하기 시작 { -->
<script>
(function() {
    try {
        var theme = localStorage.getItem('m-theme');
        if (!theme) {
            theme = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.dataset.theme = theme;
        if (theme === 'dark') document.documentElement.classList.add('dark');
    } catch (e) {}
})();
</script>
<div id="sit_rec_new" class="new_win m-recommend-window">
    <h1 id="win_title"><?php echo $g5['title']; ?></h1>

    <form name="fitemrecommend" method="post" action="<?php echo G5_SHOP_URL; ?>/itemrecommendmail" autocomplete="off" onsubmit="return fitemrecommend_check(this);">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">

    <div class="m-recommend-form">
        <div class="m-recommend-field">
            <label for="to_email">추천받는 분 E-mail <span aria-hidden="true">*</span></label>
            <input type="email" name="to_email" id="to_email" required class="frm_input full_input required" size="51">
        </div>
        <div class="m-recommend-field">
            <label for="subject">제목 <span aria-hidden="true">*</span></label>
            <input type="text" name="subject" id="subject" required class="frm_input full_input required" size="51">
        </div>
        <div class="m-recommend-field">
            <label for="content">내용 <span aria-hidden="true">*</span></label>
            <textarea name="content" id="content" required class="frm_input required"></textarea>
        </div>
    </div>

    <div class="win_btn">
        <button type="submit" id="btn_submit" class="btn_submit">보내기</button>
        <button onclick="javascript:window.close();" class="btn_close">닫기</button>
    </div>
    
    </form>
</div>

<script>
function fitemrecommend_check(f)
{
    return true;
}
</script>
<style>
html, body {
    min-height: 100%;
    background: #f8fafc;
    color: #0f172a;
}
body {
    margin: 0;
    overflow-x: hidden;
}
.m-recommend-window {
    width: min(100vw, 760px);
    min-height: 100vh;
    margin: 0 auto;
    padding: 0;
    background: transparent;
    box-sizing: border-box;
}
.m-recommend-window #win_title {
    margin: 0;
    padding: clamp(16px, 3.8vw, 22px) clamp(18px, 4vw, 26px);
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
    color: #0f172a;
    font-size: clamp(18px, 3.2vw, 22px);
    font-weight: 800;
    line-height: 1.35;
    overflow-wrap: anywhere;
    word-break: break-word;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}
.m-recommend-window form {
    padding: clamp(18px, 4vw, 24px) clamp(18px, 4vw, 26px) clamp(20px, 4vw, 28px);
    box-sizing: border-box;
}
.m-recommend-form {
    display: grid;
    gap: clamp(14px, 2.8vw, 20px);
}
.m-recommend-field label {
    display: block;
    margin: 0 0 8px;
    color: #111827;
    font-size: 14px;
    font-weight: 700;
}
.m-recommend-field label span {
    color: #ef4444;
}
.m-recommend-field input,
.m-recommend-field textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #0f172a;
    font-size: 15px;
    outline: none;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.m-recommend-field input {
    height: clamp(42px, 8vh, 46px);
    padding: 0 13px;
}
.m-recommend-field textarea {
    min-height: clamp(112px, 28vh, 150px);
    padding: 12px 13px;
    resize: vertical;
}
.m-recommend-field input:focus,
.m-recommend-field textarea:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
}
.m-recommend-window .win_btn {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 8px;
    margin: clamp(18px, 3.2vw, 24px) 0 0;
}
.m-recommend-window .btn_submit,
.m-recommend-window .btn_close {
    min-width: 96px;
    height: 42px;
    padding: 0 18px;
    border-radius: 8px;
    font-weight: 800;
    cursor: pointer;
}
.m-recommend-window .btn_submit {
    border: 1px solid #2563eb;
    background: #3b82f6;
    color: #fff;
}
.m-recommend-window .btn_close {
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #334155;
}
@media (max-width: 480px) {
    .m-recommend-window .btn_submit,
    .m-recommend-window .btn_close {
        flex: 1 1 calc(50% - 4px);
    }
}
[data-theme="dark"],
[data-theme="dark"] body {
    color-scheme: dark;
}
[data-theme="dark"] body {
    background: #0b1120;
    color: #e5e7eb;
}
[data-theme="dark"] .m-recommend-window #win_title,
[data-theme="dark"] .m-recommend-field input,
[data-theme="dark"] .m-recommend-field textarea,
[data-theme="dark"] .m-recommend-window .btn_close {
    background: #111827;
    border-color: #334155;
    color: #f8fafc;
}
[data-theme="dark"] .m-recommend-window #win_title {
    box-shadow: none;
}
[data-theme="dark"] .m-recommend-field label {
    color: #e5e7eb;
}
@media (prefers-color-scheme: dark) {
    html, body {
        background: #0b1120;
        color: #e5e7eb;
    }
    .m-recommend-window #win_title,
    .m-recommend-field input,
    .m-recommend-field textarea,
    .m-recommend-window .btn_close {
        background: #111827;
        border-color: #334155;
        color: #f8fafc;
    }
    .m-recommend-window #win_title {
        box-shadow: none;
    }
    .m-recommend-field label {
        color: #e5e7eb;
    }
}
</style>
<!-- } 상품 추천하기 끝 -->

<?php
include_once(G5_PATH.'/tail.sub.php');
