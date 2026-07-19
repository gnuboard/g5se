<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 비밀번호 재설정 시작 { -->
<div class="m-shell">

    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-center">
        <div class="m-card m-card-narrow" style="max-width: 420px;">

            <!-- 자물쇠+키 아이콘 -->
            <div style="display: flex; justify-content: center; margin-bottom: 18px;">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: var(--m-primary-soft); display: grid; place-items: center;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--m-primary)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="15" r="4"/>
                        <path d="M10.85 12.15 19 4"/>
                        <path d="M18 5l3 3"/>
                        <path d="M15 8l3 3"/>
                    </svg>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 24px;">
                <h1 style="font-size: 20px; margin-bottom: 8px;">비밀번호 재설정</h1>
                <p style="font-size: 13px; color: var(--m-text-muted); line-height: 1.6;">
                    새로운 비밀번호를 입력해 주세요.
                </p>
            </div>

            <form name="fpasswordreset" action="<?php echo $action_url ?>" onsubmit="return fpasswordreset_submit(this);" method="post" autocomplete="off">

                <!-- 회원 아이디 표시 -->
                <div style="margin-bottom: 14px; padding: 12px 14px; background: var(--m-surface-2); border-radius: var(--m-radius); display: flex; align-items: center; gap: 10px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--m-text-faint)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span style="font-size: 14px; font-weight: 600; color: var(--m-text);"><?php echo get_text($_POST['mb_id']); ?></span>
                    <span style="font-size: 12px; color: var(--m-text-faint); margin-left: auto;">회원 아이디</span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="mb_pw" class="m-label">새 비밀번호</label>
                    <input type="password" name="mb_password" id="mb_pw" required maxlength="20" class="m-input" placeholder="새 비밀번호" autofocus>
                </div>

                <div style="margin-bottom: 18px;">
                    <label for="mb_pw2" class="m-label">새 비밀번호 확인</label>
                    <input type="password" name="mb_password_re" id="mb_pw2" required maxlength="20" class="m-input" placeholder="새 비밀번호 확인">
                </div>

                <button type="submit" class="m-btn m-btn-primary">비밀번호 변경</button>
            </form>

        </div>
    </main>
    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>

<script>
function fpasswordreset_submit(f) {
    if (document.getElementById('mb_pw').value !== document.getElementById('mb_pw2').value) {
        alert("새 비밀번호와 비밀번호 확인이 일치하지 않습니다.");
        document.getElementById('mb_pw2').focus();
        return false;
    }
    return true;
}
</script>
<!-- } 비밀번호 재설정 끝 -->
