<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 로그인 시작 { -->
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>
    <div class="m-center">
        <div class="m-card m-card-narrow">

            <div style="text-align: center; margin-bottom: 28px;">
                <h1 style="font-size: 22px; margin-bottom: 6px;">로그인</h1>
                <p style="font-size: 13px; color: var(--m-text-muted);">계정에 로그인하여 서비스를 이용하세요.</p>
            </div>

            <form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
                <input type="hidden" name="url" value="<?php echo $login_url ?>">

                <div style="margin-bottom: 14px;">
                    <label for="login_id" class="m-label">아이디</label>
                    <input type="text" name="mb_id" id="login_id" required maxlength="20" autocomplete="username" class="m-input" placeholder="아이디를 입력하세요">
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="login_pw" class="m-label">비밀번호</label>
                    <div class="m-pw-wrap">
                        <input type="password" name="mb_password" id="login_pw" required maxlength="20" autocomplete="current-password" class="m-input" placeholder="비밀번호" style="padding-right: 40px;">
                        <button type="button" class="m-pw-toggle" id="pw_toggle" aria-label="비밀번호 표시/숨기기">
                            <svg id="pw_eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                    <label class="m-check">
                        <input type="checkbox" name="auto_login" id="login_auto_login">
                        <span>자동로그인</span>
                    </label>
                    <a href="<?php echo G5_BBS_URL ?>/password_lost.php" class="m-link">비밀번호 찾기</a>
                </div>

                <button type="submit" class="m-btn m-btn-primary">로그인</button>
            </form>

            <div class="m-divider">또는</div>

            <a href="<?php echo G5_BBS_URL ?>/register.php" class="m-btn m-btn-secondary">회원가입</a>

            <?php @include_once(get_social_skin_path().'/social_login.skin.php'); ?>
        </div>
    </div>
    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>

<script>
document.getElementById('pw_toggle').addEventListener('click', function() {
    var pw = document.getElementById('login_pw');
    var eye = document.getElementById('pw_eye');
    if (pw.type === 'password') {
        pw.type = 'text';
        eye.innerHTML = '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-10-7-10-7a18.45 18.45 0 0 1 4.16-5.39"/><path d="M9.9 4.24A10.18 10.18 0 0 1 12 4c7 0 10 7 10 7a18.4 18.4 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="2" y1="2" x2="22" y2="22"/>';
    } else {
        pw.type = 'password';
        eye.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
    }
});

document.getElementById('login_auto_login').addEventListener('click', function() {
    if (this.checked) {
        this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
    }
});

function flogin_submit(f) {
    if (typeof jQuery !== 'undefined' && jQuery(document.body).triggerHandler('login_sumit', [f, 'flogin']) !== false) {
        return true;
    }
    return true;
}
</script>
<!-- } 로그인 끝 -->
