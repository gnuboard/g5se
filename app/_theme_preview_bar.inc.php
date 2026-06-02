<?php
/*
 * 세션 기반 테마 미리보기 상단 표시 바.
 * head.sub.php 가 세션 활성 시 include — 그 외에는 출력 안 됨.
 * 보안: super-admin 검사를 한 번 더 (호출 측에서 이미 확인하지만 방어).
 */
if (!defined('_GNUBOARD_')) return;

global $member, $config;

// 세션 active + super-admin 검사
$_tp_theme = isset($_SESSION['ss_theme_preview']) ? (string)$_SESSION['ss_theme_preview'] : '';
if ($_tp_theme === ''
    || !isset($member['mb_id'])
    || ($config['cf_admin'] ?? '') === ''
    || $member['mb_id'] !== $config['cf_admin']) {
    return;  // 출력 안 함
}

require_once G5_ADMIN_PATH.'/admin.lib.php';  // get_admin_token

$_tp_token = get_admin_token();
?>
<div id="m-theme-preview-bar" role="region" aria-label="테마 미리보기">
    <div class="tpb-inner">
        <span class="tpb-lbl">🎨 테마 미리보기</span>
        <strong class="tpb-theme"><?php echo htmlspecialchars($_tp_theme, ENT_QUOTES, 'UTF-8'); ?></strong>
        <span class="tpb-spacer"></span>
        <form method="post" action="<?php echo G5_ADMIN_URL; ?>/theme_preview_apply" class="tpb-form-apply">
            <input type="hidden" name="theme" value="<?php echo htmlspecialchars($_tp_theme, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="token" value="<?php echo $_tp_token; ?>">
            <button type="submit" class="tpb-btn tpb-apply">이 테마로 적용</button>
        </form>
        <a href="<?php echo G5_ADMIN_URL; ?>/theme_preview_exit?token=<?php echo $_tp_token; ?>"
           class="tpb-btn tpb-exit">종료</a>
    </div>
</div>
<style>
#m-theme-preview-bar {
    position: fixed; top: 0; left: 0; right: 0;
    height: 40px; z-index: 2147483600;
    background: #0f172a; color: #f1f5f9;
    border-bottom: 1px solid #1e293b;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    font-size: 13px; line-height: 1;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Pretendard", Roboto, sans-serif;
}
#m-theme-preview-bar .tpb-inner {
    height: 100%;
    display: flex; align-items: center; gap: 10px;
    padding: 0 16px;
    max-width: none;
}
#m-theme-preview-bar .tpb-lbl { font-weight: 500; opacity: 0.85; }
#m-theme-preview-bar .tpb-theme {
    background: #1e293b;
    padding: 4px 10px; border-radius: 12px;
    font-weight: 700; color: #60a5fa;
}
#m-theme-preview-bar .tpb-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 6px 12px; border-radius: 6px;
    color: #cbd5e1; text-decoration: none;
    background: transparent; border: 1px solid transparent;
    font-size: 12px; cursor: pointer;
    transition: background 0.12s, color 0.12s, border-color 0.12s;
}
#m-theme-preview-bar .tpb-btn:hover {
    background: #1e293b; color: #fff;
}
#m-theme-preview-bar .tpb-form-apply { margin: 0; display: inline-flex; }
#m-theme-preview-bar .tpb-apply {
    background: #2563eb; color: #fff;
    border: 1px solid #2563eb;
}
#m-theme-preview-bar .tpb-apply:hover {
    background: #1d4ed8; border-color: #1d4ed8;
}
#m-theme-preview-bar .tpb-exit {
    color: #fca5a5;
    border: 1px solid #7f1d1d;
}
#m-theme-preview-bar .tpb-exit:hover {
    background: #7f1d1d; color: #fff;
}
#m-theme-preview-bar .tpb-spacer { flex: 1; }
body { padding-top: 40px !important; }
@media (max-width: 640px) {
    #m-theme-preview-bar { height: 36px; font-size: 12px; }
    #m-theme-preview-bar .tpb-inner { padding: 0 8px; gap: 6px; }
    #m-theme-preview-bar .tpb-lbl { display: none; }
    #m-theme-preview-bar .tpb-btn { padding: 4px 8px; }
    body { padding-top: 36px !important; }
}
</style>
