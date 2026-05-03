<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 쪽지 보내기 시작 { -->
<div class="m-popup">
    <header class="m-popup-head">
        <h1 class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            쪽지 보내기
        </h1>
    </header>

    <nav class="m-memo-tabs">
        <a href="/memo?kind=recv" class="m-memo-tab">받은쪽지</a>
        <a href="/memo?kind=send" class="m-memo-tab">보낸쪽지</a>
        <a href="/memo_form" class="m-memo-tab m-memo-tab-write is-active">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            쪽지쓰기
        </a>
    </nav>

    <form name="fmemoform" action="<?php echo $memo_action_url; ?>" onsubmit="return fmemoform_submit(this);" method="post" autocomplete="off" class="m-memo-form">
        <div class="m-memo-field">
            <label for="me_recv_mb_id" class="m-label">받는 회원아이디 <span class="m-label-req">필수</span></label>
            <input type="text" name="me_recv_mb_id" value="<?php echo $me_recv_mb_id; ?>" id="me_recv_mb_id" required class="m-input" placeholder="회원아이디 (여러 명은 ',' 로 구분)">
            <p class="m-memo-hint">
                여러 명은 컴마(,)로 구분<?php if ($config['cf_memo_send_point']) { ?> · 회원당 <strong><?php echo number_format($config['cf_memo_send_point']); ?></strong>점 차감<?php } ?>
            </p>
        </div>

        <div class="m-memo-field">
            <label for="me_memo" class="m-label">내용</label>
            <textarea name="me_memo" id="me_memo" required class="m-input m-memo-textarea"><?php echo $content ?></textarea>
        </div>

        <div class="m-memo-captcha"><?php echo captcha_html(); ?></div>

        <div class="m-popup-actions">
            <button type="submit" id="btn_submit" class="m-btn" style="width:auto; padding:8px 20px;">보내기</button>
            <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:7px 18px;">창닫기</button>
        </div>
    </form>
</div>

<script>
function fmemoform_submit(f)
{
    <?php echo chk_captcha_js();  ?>
    return true;
}
</script>

<style>
/* memo_form 전용 — popup 공통은 _head.inc.php */
.m-memo-form { display: flex; flex-direction: column; gap: 10px; }
.m-memo-field { display: flex; flex-direction: column; gap: 4px; }
.m-label-req {
    font-size: var(--m-text-xs); font-weight: 500;
    color: var(--m-primary); margin-left: 4px;
}
.m-memo-hint {
    margin: 2px 0 0; font-size: var(--m-text-xs); color: var(--m-text-muted);
}
.m-memo-hint strong { color: var(--m-primary); font-weight: 600; }
.m-memo-textarea {
    min-height: 110px; max-height: 30vh;
    padding: 10px; font-family: inherit; resize: vertical;
    line-height: var(--m-leading-relaxed);
}

/* 캡챠 공통 스타일은 _head.inc.php (#captcha) — 여기서는 spacing 만 */
.m-memo-captcha { margin-top: 2px; }
</style>
<!-- } 쪽지 보내기 끝 -->
