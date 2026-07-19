<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 회원 비밀번호 확인 시작 { -->
<div class="m-shell">

    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-center">
        <div class="m-card m-card-narrow" style="max-width: 420px;">

            <!-- 잠금 아이콘 -->
            <div style="display: flex; justify-content: center; margin-bottom: 18px;">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: var(--m-primary-soft); display: grid; place-items: center;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--m-primary)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 24px;">
                <h1 style="font-size: 20px; margin-bottom: 8px;">비밀번호 확인</h1>
                <p style="font-size: 13px; color: var(--m-text-muted); line-height: 1.6;">
                    <?php if ($url == 'member_leave.php' || strpos($url, '/member_leave') !== false) { ?>
                        비밀번호를 입력하시면 회원 탈퇴가 진행됩니다.
                    <?php } else { ?>
                        회원 정보를 안전하게 보호하기 위해<br>
                        비밀번호를 한 번 더 확인합니다.
                    <?php } ?>
                </p>
            </div>

            <form name="fmemberconfirm" action="<?php echo $url ?>" onsubmit="return fmemberconfirm_submit(this);" method="post">
                <input type="hidden" name="mb_id" value="<?php echo $member['mb_id'] ?>">
                <input type="hidden" name="w" value="u">

                <!-- 회원 아이디 표시 (readonly 느낌) -->
                <div style="margin-bottom: 14px; padding: 12px 14px; background: var(--m-surface-2); border-radius: var(--m-radius); display: flex; align-items: center; gap: 10px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--m-text-faint)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span style="font-size: 14px; font-weight: 600; color: var(--m-text);"><?php echo $member['mb_id'] ?></span>
                    <span style="font-size: 12px; color: var(--m-text-faint); margin-left: auto;">로그인 계정</span>
                </div>

                <div style="margin-bottom: 18px;">
                    <label for="confirm_mb_password" class="m-label">비밀번호</label>
                    <input type="password" name="mb_password" id="confirm_mb_password" required maxlength="20" autocomplete="current-password" class="m-input" placeholder="현재 비밀번호" autofocus>
                </div>

                <button type="submit" id="btn_submit" class="m-btn m-btn-primary">확인</button>
            </form>

            <p style="font-size: 12px; color: var(--m-text-faint); text-align: center; margin-top: 18px;">
                비밀번호가 기억나지 않으세요?
                <a href="<?php echo G5_BBS_URL ?>/password_lost.php" class="m-link">비밀번호 찾기</a>
            </p>
        </div>
    </main>
    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>

<script>
function fmemberconfirm_submit(f) {
    document.getElementById("btn_submit").disabled = true;
    return true;
}
</script>
<!-- } 회원 비밀번호 확인 끝 -->
