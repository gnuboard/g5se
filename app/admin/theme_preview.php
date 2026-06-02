<?php
/*
 * /admin/theme_preview — 테마 launcher.
 * 설치된 테마 목록 + 각 테마에 [미리보기 시작] / [지금 적용 중] 표시.
 * inline preview 는 제거됨 — 시작 클릭 시 세션 + 사이트 navigation 으로.
 */
$sub_menu = "100280";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if ($member['mb_id'] !== $config['cf_admin']) {
    alert('최고 관리자만 접근 가능합니다.');
}

$g5['title'] = '테마 관리';

$theme_dirs = get_theme_dir();
$current_theme = (string)($config['cf_theme'] ?? '');
$preview_theme = isset($_SESSION['ss_theme_preview']) ? (string)$_SESSION['ss_theme_preview'] : '';

admin_layout_start($g5['title'], 'core');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h1 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']); ?></h1>
</header>

<?php if ($preview_theme !== '') { ?>
<div class="tp-notice">
    🎨 현재 <strong><?php echo htmlspecialchars($preview_theme); ?></strong> 테마를 미리보기 중. 사이트 상단의 [종료] 로 해제하거나, 아래에서 다른 테마를 선택해 갈아탈 수 있습니다.
</div>
<?php } ?>

<?php if (!$theme_dirs) { ?>
<div class="tp-empty">설치된 테마가 없습니다. <code>app/theme/</code> 아래에 테마 폴더를 두면 여기 나타납니다.</div>
<?php } else { ?>
<div class="tp-grid">
<?php foreach ($theme_dirs as $theme) {
    $info = get_theme_info($theme);
    $is_current = ($theme === $current_theme);
    $is_previewing = ($theme === $preview_theme);
    $thumb_path = G5_PATH.'/'.G5_THEME_DIR.'/'.$theme.'/screenshot.png';
    $thumb_url = is_file($thumb_path)
        ? G5_URL.'/'.G5_THEME_DIR.'/'.$theme.'/screenshot.png'
        : '';
?>
    <div class="tp-card<?php echo $is_current ? ' tp-current' : ''; ?><?php echo $is_previewing ? ' tp-previewing' : ''; ?>">
        <div class="tp-thumb">
            <?php if ($thumb_url) { ?>
                <img src="<?php echo htmlspecialchars($thumb_url); ?>" alt="">
            <?php } else { ?>
                <span class="tp-thumb-empty">미리보기 이미지 없음</span>
            <?php } ?>
        </div>
        <div class="tp-body">
            <h3 class="tp-name"><?php echo htmlspecialchars((string)($info['theme_name'] ?? $theme)); ?>
                <?php if ($is_current) { ?><span class="tp-tag tp-tag-current">적용 중</span><?php } ?>
                <?php if ($is_previewing) { ?><span class="tp-tag tp-tag-preview">미리보기 중</span><?php } ?>
            </h3>
            <p class="tp-meta"><code><?php echo htmlspecialchars($theme); ?></code></p>
            <?php if (!empty($info['theme_description'])) { ?>
            <p class="tp-desc"><?php echo htmlspecialchars((string)$info['theme_description']); ?></p>
            <?php } ?>
            <div class="tp-actions">
                <form method="post" action="<?php echo G5_ADMIN_URL; ?>/theme_preview_start" style="display:inline">
                    <input type="hidden" name="theme" value="<?php echo htmlspecialchars($theme); ?>">
                    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
                    <button type="submit" class="tp-btn tp-btn-preview">미리보기 시작</button>
                </form>
                <?php if (!$is_current) { ?>
                <form method="post" action="<?php echo G5_ADMIN_URL; ?>/theme_preview_apply" style="display:inline"
                      onsubmit="return confirm('테마 <?php echo htmlspecialchars($theme, ENT_QUOTES); ?> 를 사이트 전체에 적용합니다. 계속하시겠습니까?');">
                    <input type="hidden" name="theme" value="<?php echo htmlspecialchars($theme); ?>">
                    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
                    <button type="submit" class="tp-btn tp-btn-apply">이 테마로 적용</button>
                </form>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>
</div>
<?php } ?>

</main>

<style>
.tp-notice {
    background: rgba(96,165,250,0.1); border: 1px solid rgba(96,165,250,0.3);
    color: #1d4ed8; padding: 12px 16px; border-radius: 8px;
    margin-bottom: 20px; font-size: 14px;
}
[data-theme="dark"] .tp-notice {
    background: rgba(96,165,250,0.15); border-color: rgba(96,165,250,0.4); color: #93c5fd;
}
.tp-empty {
    padding: 32px; text-align: center; color: var(--slate-500);
    background: var(--slate-50); border: 1px dashed var(--slate-300); border-radius: 8px;
}
[data-theme="dark"] .tp-empty { color: var(--slate-400); background: var(--slate-800); border-color: var(--slate-700); }
.tp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.tp-card {
    background: #fff; border: 1px solid var(--slate-200); border-radius: 10px;
    overflow: hidden; display: flex; flex-direction: column;
}
[data-theme="dark"] .tp-card { background: var(--slate-800); border-color: var(--slate-700); }
.tp-card.tp-current { border-color: #10b981; }
.tp-card.tp-previewing { border-color: #60a5fa; box-shadow: 0 0 0 2px rgba(96,165,250,0.2); }
.tp-thumb {
    aspect-ratio: 16/10; background: var(--slate-100);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
[data-theme="dark"] .tp-thumb { background: var(--slate-900); }
.tp-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.tp-thumb-empty { color: var(--slate-400); font-size: 13px; }
.tp-body { padding: 14px 16px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
.tp-name { font-size: 15px; font-weight: 700; margin: 0; color: var(--slate-900); }
[data-theme="dark"] .tp-name { color: var(--slate-100); }
.tp-tag { font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 600; margin-left: 6px; vertical-align: middle; }
.tp-tag-current { background: rgba(16,185,129,0.12); color: #047857; }
[data-theme="dark"] .tp-tag-current { background: rgba(16,185,129,0.2); color: #34d399; }
.tp-tag-preview { background: rgba(96,165,250,0.12); color: #1d4ed8; }
[data-theme="dark"] .tp-tag-preview { background: rgba(96,165,250,0.2); color: #93c5fd; }
.tp-meta { font-size: 12px; color: var(--slate-500); margin: 0; }
.tp-desc { font-size: 13px; color: var(--slate-600); margin: 4px 0 0; line-height: 1.4; }
[data-theme="dark"] .tp-desc { color: var(--slate-400); }
.tp-actions { display: flex; gap: 6px; margin-top: 10px; }
.tp-btn {
    padding: 7px 12px; border-radius: 6px; font-size: 13px; font-weight: 600;
    border: 1px solid transparent; cursor: pointer;
}
.tp-btn-preview { background: #2563eb; color: #fff; border-color: #2563eb; }
.tp-btn-preview:hover { background: #1d4ed8; border-color: #1d4ed8; }
.tp-btn-apply { background: var(--slate-100); color: var(--slate-900); border-color: var(--slate-200); }
.tp-btn-apply:hover { background: var(--slate-200); }
[data-theme="dark"] .tp-btn-apply { background: var(--slate-700); color: var(--slate-100); border-color: var(--slate-600); }
[data-theme="dark"] .tp-btn-apply:hover { background: var(--slate-600); }
</style>

<?php admin_layout_end(); ?>
