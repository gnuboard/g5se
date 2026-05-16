<?php
/*
 * /admin/theme — 테마 설정 (모던 카드 그리드).
 *   - 각 카드: screenshot / 테마명 / 폴더명 (mono) / Maker / Version
 *   - 액션: 테마적용 / 사용안함 / 미리보기 / 상세보기
 *   - AJAX: G5_ADMIN_URL 기반 theme_update (적용/해제), theme_detail (상세)
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_ADMIN_PATH.'/admin.lib.php';

$sub_menu = '100280';
if ($is_admin !== 'super') {
    admin_layout_start('테마 설정', 'theme');
    echo '<main class="flex-1 p-6"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">최고관리자만 접근 가능합니다.</div></main>';
    admin_layout_end();
    exit;
}

// cf_theme 컬럼 lazy migration
if (!isset($config['cf_theme'])) {
    // DDL — placeholder 못 받음, sql_pdo_query 로 통일 (params 빈 배열)
    sql_pdo_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_theme` varchar(255) NOT NULL DEFAULT '' AFTER `cf_title` ", [], true);
}

$theme = get_theme_dir();
if ($config['cf_theme'] && in_array($config['cf_theme'], $theme)) {
    array_unshift($theme, $config['cf_theme']);
}
$theme = array_values(array_unique($theme));
$total_count = count($theme);

if ($config['cf_theme'] && !in_array($config['cf_theme'], $theme)) {
    sql_pdo_query(" update {$g5['config_table']} set cf_theme = '' ");
    $config['cf_theme'] = '';
}

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$admin_theme_preview_url = G5_ADMIN_URL.'/theme_preview';
$admin_theme_update_url  = G5_ADMIN_URL.'/theme_update';
$admin_theme_detail_url  = G5_ADMIN_URL.'/theme_detail';

admin_layout_start('테마 설정', 'theme');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex flex-wrap items-center gap-3 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight">테마 설정</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                설치된 테마 <strong class="text-slate-700 dark:text-slate-300"><?php echo number_format($total_count) ?></strong>개
                <?php if ($config['cf_theme']): ?>
                    · 현재 사용중: <code class="px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-mono text-xs"><?php echo $h($config['cf_theme']) ?></code>
                <?php else: ?>
                    · <span class="text-slate-400">테마 미사용 (기본 스킨 폴더 사용)</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="ml-auto text-xs text-slate-500">
            테마 폴더: <code class="font-mono"><?php echo $h(G5_THEME_DIR) ?>/</code>
        </div>
    </header>

    <?php if ($total_count === 0): ?>
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-12 text-center text-slate-400 dark:text-slate-500">
            설치된 테마가 없습니다. <code class="font-mono"><?php echo $h(G5_THEME_DIR) ?>/</code> 디렉토리에 테마를 추가하세요.
        </div>
    <?php else: ?>
        <ul id="theme_list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($theme as $t):
            $info       = get_theme_info($t);
            $name       = $h($info['theme_name'] ?? $t);
            $maker      = $h($info['maker'] ?? '');
            $version    = $h($info['version'] ?? '');
            $is_active  = ($config['cf_theme'] === $t);
            $screenshot = $info['screenshot'] ?? '';
            $tconfig    = get_theme_config_value($t, 'set_default_skin');
            $set_default_skin = !empty($tconfig['set_default_skin']) ? 'true' : 'false';
            ?>
            <li class="rounded-xl border <?php echo $is_active ? 'border-emerald-300 dark:border-emerald-700 ring-1 ring-emerald-300 dark:ring-emerald-700' : 'border-slate-200 dark:border-slate-800' ?> bg-white dark:bg-slate-900 overflow-hidden flex flex-col">
                <div class="aspect-video bg-slate-100 dark:bg-slate-800 overflow-hidden relative">
                    <?php if ($screenshot): ?>
                        <img src="<?php echo $h($screenshot) ?>" alt="<?php echo $name ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        </div>
                    <?php endif; ?>
                    <?php if ($is_active): ?>
                    <span class="absolute top-2 right-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-600 text-white text-[11px] font-semibold shadow">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        사용중
                    </span>
                    <?php endif; ?>
                </div>
                <div class="flex-1 p-3.5 flex flex-col gap-2">
                    <div class="min-h-0">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 truncate" title="<?php echo $name ?>"><?php echo $name ?></h3>
                        <code class="block mt-0.5 text-xs font-mono text-admin-primary-700 dark:text-admin-primary-300 truncate" title="<?php echo $h($t) ?>"><?php echo $h(G5_THEME_DIR) ?>/<?php echo $h($t) ?></code>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                        <?php if ($maker): ?><span><?php echo $maker ?></span><?php endif; ?>
                        <?php if ($version): ?><span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono">v<?php echo $version ?></span><?php endif; ?>
                    </div>
                    <div class="mt-auto flex items-center gap-1 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <?php if ($is_active): ?>
                            <button type="button" class="theme_deactive flex-1 inline-flex items-center justify-center h-8 px-2.5 rounded-md border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/30"
                                    data-theme="<?php echo $h($t) ?>" data-name="<?php echo $name ?>">사용 안함</button>
                        <?php else: ?>
                            <button type="button" class="theme_active flex-1 inline-flex items-center justify-center h-8 px-2.5 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-xs font-medium"
                                    data-theme="<?php echo $h($t) ?>" data-name="<?php echo $name ?>" data-set_default_skin="<?php echo $set_default_skin ?>">테마 적용</button>
                        <?php endif; ?>
                        <a href="<?php echo $h($admin_theme_preview_url.'?theme='.urlencode($t)) ?>" target="theme_preview" class="inline-flex items-center justify-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" title="미리보기">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <button type="button" class="theme_detail inline-flex items-center justify-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" data-theme="<?php echo $h($t) ?>" title="상세보기">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        </button>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</main>

<!-- 테마 상세 모달 -->
<div id="theme_detail_modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-start justify-center p-4 sm:p-10 overflow-y-auto">
    <div class="w-full max-w-3xl bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800">
        <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-200 dark:border-slate-800">
            <h3 id="theme_detail_title" class="font-semibold">테마 상세</h3>
            <button type="button" id="theme_detail_close" class="ml-auto inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="닫기">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div id="theme_detail_body" class="p-5 max-h-[75vh] overflow-y-auto legacy-admin-content"></div>
    </div>
</div>

<script>
(function () {
    var themeUpdateUrl = <?php echo json_encode($admin_theme_update_url, JSON_UNESCAPED_SLASHES); ?>;
    var themeDetailUrl = <?php echo json_encode($admin_theme_detail_url, JSON_UNESCAPED_SLASHES); ?>;

    document.querySelectorAll('button.theme_active').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var theme = btn.dataset.theme, name = btn.dataset.name;
            if (!confirm(name + ' 테마를 적용하시겠습니까?')) return;
            var set_default_skin = '0';
            if (btn.dataset.set_default_skin === 'true') {
                if (confirm('기본환경설정, 1:1문의, 쇼핑몰 스킨을 테마에서 설정된 스킨으로 변경하시겠습니까?\n\n변경을 선택하시면 테마에서 지정된 스킨으로 회원스킨 등이 변경됩니다.')) {
                    set_default_skin = '1';
                }
            }
            var fd = new FormData();
            fd.append('theme', theme);
            fd.append('set_default_skin', set_default_skin);
            fetch(themeUpdateUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.text(); })
                .then(function (txt) {
                    if (txt && txt.trim()) { alert(txt); return; }
                    location.reload();
                });
        });
    });

    document.querySelectorAll('button.theme_deactive').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var theme = btn.dataset.theme, name = btn.dataset.name;
            if (!confirm(name + ' 테마 사용설정을 해제하시겠습니까?\n\n테마 설정을 해제하셔도 게시판 등의 스킨은 변경되지 않으므로 개별 변경작업이 필요합니다.')) return;
            var fd = new FormData();
            fd.append('theme', theme);
            fd.append('type', 'reset');
            fetch(themeUpdateUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.text(); })
                .then(function (txt) {
                    if (txt && txt.trim()) { alert(txt); return; }
                    location.reload();
                });
        });
    });

    // 상세 모달
    var modal      = document.getElementById('theme_detail_modal');
    var modalTitle = document.getElementById('theme_detail_title');
    var modalBody  = document.getElementById('theme_detail_body');
    var modalClose = document.getElementById('theme_detail_close');
    function openModal() { modal.classList.remove('hidden'); document.documentElement.style.overflow = 'hidden'; }
    function closeModal() { modal.classList.add('hidden'); document.documentElement.style.overflow = ''; modalBody.innerHTML = ''; }
    modalClose && modalClose.addEventListener('click', closeModal);
    modal && modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

    document.querySelectorAll('button.theme_detail').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var theme = btn.dataset.theme;
            modalTitle.textContent = theme + ' — 테마 상세';
            modalBody.innerHTML = '<p class="text-center py-10 text-slate-400 text-sm">불러오는 중…</p>';
            openModal();
            var fd = new FormData();
            fd.append('theme', theme);
            fetch(themeDetailUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    modalBody.innerHTML = html;
                    // gnuboard 의 .close_btn 은 jQuery 로 #theme_detail 만 remove 하므로
                    // 우리는 같은 버튼을 모달 닫기로 재바인딩
                    modalBody.querySelectorAll('.close_btn').forEach(function (b) {
                        b.addEventListener('click', closeModal);
                    });
                });
        });
    });
})();
</script>

<?php
admin_layout_end();
