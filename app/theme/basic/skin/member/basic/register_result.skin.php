<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 회원가입 완료 시작 { -->
<div class="m-shell">

    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-center">
        <div class="m-card m-card-narrow" style="max-width: 480px;">

            <!-- 성공 아이콘 -->
            <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(16,185,129,0.12); display: grid; place-items: center;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 24px;">
                <h1 style="font-size: 22px; margin-bottom: 8px;">가입이 완료되었습니다</h1>
                <p style="font-size: 14px; color: var(--m-text-muted);">
                    <strong style="color: var(--m-text);"><?php echo get_text($mb['mb_name']); ?></strong> 님, 환영합니다.
                </p>
            </div>

            <?php if (is_use_email_certify()) { ?>
            <!-- 이메일 인증 안내 -->
            <div style="background: var(--m-surface-2); border: 1px solid var(--m-border); border-radius: var(--m-radius); padding: 16px 18px; margin-bottom: 16px;">
                <div style="display: flex; gap: 10px; align-items: flex-start; margin-bottom: 12px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--m-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <div style="font-size: 13px; color: var(--m-text-soft); line-height: 1.6;">
                        가입 시 입력하신 이메일로 <strong style="color: var(--m-text);">인증메일</strong>이 발송되었습니다.
                        메일을 확인하고 인증을 완료해 주세요.
                    </div>
                </div>
                <dl style="margin: 0; display: grid; grid-template-columns: 80px 1fr; gap: 6px 12px; font-size: 13px; padding-top: 12px; border-top: 1px dashed var(--m-border);">
                    <dt style="color: var(--m-text-faint);">아이디</dt>
                    <dd style="margin: 0; font-weight: 500; color: var(--m-text);"><?php echo $mb['mb_id'] ?></dd>
                    <dt style="color: var(--m-text-faint);">이메일</dt>
                    <dd style="margin: 0; font-weight: 500; color: var(--m-text);"><?php echo $mb['mb_email'] ?></dd>
                </dl>
                <p style="font-size: 12px; color: var(--m-text-faint); margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--m-border);">
                    이메일을 잘못 입력하셨다면 사이트 관리자에게 문의해 주세요.
                </p>
            </div>
            <?php } ?>

            <!-- 안내 사항 -->
            <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; display: flex; flex-direction: column; gap: 10px;">
                <li style="display: flex; gap: 10px; align-items: flex-start; font-size: 13px; color: var(--m-text-soft); line-height: 1.6;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--m-text-faint)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 4px;">
                        <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    비밀번호는 암호화되어 저장되며, 분실 시 가입한 이메일로 재설정할 수 있습니다.
                </li>
                <li style="display: flex; gap: 10px; align-items: flex-start; font-size: 13px; color: var(--m-text-soft); line-height: 1.6;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--m-text-faint)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 4px;">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    회원 탈퇴는 언제든 가능하며, 일정 기간 후 정보가 안전하게 삭제됩니다.
                </li>
            </ul>

            <a href="<?php echo G5_URL ?>/" class="m-btn m-btn-primary">메인으로</a>
        </div>
    </main>
    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>
<!-- } 회원가입 완료 끝 -->
