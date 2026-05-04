<?php
/*
 * /admin/faqmasterform — FAQ 분류 추가/수정.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';
require_once G5_EDITOR_LIB;

$sub_menu = '300700';
auth_check_menu($auth, $sub_menu, 'w');

$w     = isset($_GET['w']) ? (string)$_GET['w'] : '';
$fm_id = isset($_GET['fm_id']) ? (int)$_GET['fm_id'] : 0;

// 모바일 컬럼 lazy migration — DDL placeholder 불가, sql_pdo_query 로 통일
sql_pdo_query(" ALTER TABLE `{$g5['faq_master_table']}` ADD `fm_mobile_head_html` text NOT NULL AFTER `fm_tail_html`, ADD `fm_mobile_tail_html` text NOT NULL AFTER `fm_mobile_head_html` ", [], false);

$fm_default = ['fm_id'=>0, 'fm_subject'=>'', 'fm_order'=>0, 'fm_head_html'=>'', 'fm_tail_html'=>'', 'fm_mobile_head_html'=>'', 'fm_mobile_tail_html'=>''];

if ($w === 'u') {
    if (!$fm_id) { header('Location: /admin/faqmasterlist', true, 302); exit; }
    $row = sql_pdo_fetch(" select * from {$g5['faq_master_table']} where fm_id = ? ", [$fm_id]);
    if (empty($row['fm_id'])) {
        admin_layout_start('FAQ 분류 수정', 'scf_faq');
        echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">등록된 자료가 없습니다.</div></main>';
        admin_layout_end();
        exit;
    }
    $fm = array_merge($fm_default, $row);
    $page_title = 'FAQ 분류 수정';
} else {
    $fm = $fm_default;
    $page_title = 'FAQ 분류 추가';
}

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$himg_exists = $fm['fm_id'] && file_exists(G5_DATA_PATH.'/faq/'.$fm['fm_id'].'_h');
$timg_exists = $fm['fm_id'] && file_exists(G5_DATA_PATH.'/faq/'.$fm['fm_id'].'_t');

admin_layout_start($page_title, 'scf_faq');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex items-center gap-3 mb-6">
        <a href="/admin/faqmasterlist" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="목록으로">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold tracking-tight"><?php echo $h($page_title) ?></h2>
            <p class="text-xs text-slate-500 mt-0.5"><?php echo $w==='u' ? '#'.(int)$fm['fm_id'].' '.$h($fm['fm_subject']).' 분류 수정' : '새 FAQ 분류 추가' ?></p>
        </div>
    </header>

    <form id="frmfaqmasterform" action="/admin/faqmasterformupdate" method="post" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="w"     value="<?php echo $h($w) ?>">
        <input type="hidden" name="fm_id" value="<?php echo (int)$fm['fm_id'] ?>">
        <input type="hidden" name="token" value="<?php echo get_admin_token() ?>">

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">기본 정보</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="fm_subject" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">제목 <span class="text-admin-primary-600">*</span></label>
                    <input type="text" name="fm_subject" id="fm_subject" value="<?php echo $h($fm['fm_subject']) ?>" required maxlength="255"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
                <div>
                    <label for="fm_order" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">출력 순서</label>
                    <input type="number" name="fm_order" id="fm_order" value="<?php echo (int)$fm['fm_order'] ?>"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    <p class="mt-1 text-xs text-slate-500">숫자가 작을수록 먼저</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">상단 / 하단 이미지</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="fm_himg" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">상단 이미지</label>
                    <input type="file" name="fm_himg" id="fm_himg" accept="image/*"
                           class="block w-full text-sm file:mr-3 file:h-9 file:px-3 file:rounded-md file:border-0 file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300">
                    <?php if ($himg_exists): ?>
                    <div class="mt-3 rounded-md border border-slate-200 dark:border-slate-800 p-2">
                        <img src="<?php echo G5_DATA_URL ?>/faq/<?php echo (int)$fm['fm_id'] ?>_h" class="w-full h-auto rounded" alt="">
                        <label class="mt-2 inline-flex items-center gap-1.5 text-xs text-rose-700 dark:text-rose-300">
                            <input type="checkbox" name="fm_himg_del" value="1" class="rounded border-slate-300"> 삭제
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="fm_timg" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">하단 이미지</label>
                    <input type="file" name="fm_timg" id="fm_timg" accept="image/*"
                           class="block w-full text-sm file:mr-3 file:h-9 file:px-3 file:rounded-md file:border-0 file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300">
                    <?php if ($timg_exists): ?>
                    <div class="mt-3 rounded-md border border-slate-200 dark:border-slate-800 p-2">
                        <img src="<?php echo G5_DATA_URL ?>/faq/<?php echo (int)$fm['fm_id'] ?>_t" class="w-full h-auto rounded" alt="">
                        <label class="mt-2 inline-flex items-center gap-1.5 text-xs text-rose-700 dark:text-rose-300">
                            <input type="checkbox" name="fm_timg_del" value="1" class="rounded border-slate-300"> 삭제
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">PC 상단 내용</h3>
            <?php echo editor_html('fm_head_html', get_text(html_purifier($fm['fm_head_html']), 0)) ?>
        </section>
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">PC 하단 내용</h3>
            <?php echo editor_html('fm_tail_html', get_text(html_purifier($fm['fm_tail_html']), 0)) ?>
        </section>
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">모바일 상단 내용</h3>
            <?php echo editor_html('fm_mobile_head_html', get_text(html_purifier($fm['fm_mobile_head_html']), 0)) ?>
        </section>
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">모바일 하단 내용</h3>
            <?php echo editor_html('fm_mobile_tail_html', get_text(html_purifier($fm['fm_mobile_tail_html']), 0)) ?>
        </section>

        <div class="flex items-center justify-end gap-2">
            <a href="/admin/faqmasterlist" class="h-10 px-5 inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">취소</a>
            <button type="submit" class="h-10 px-6 inline-flex items-center rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-semibold">저장</button>
        </div>
    </form>

</main>

<?php
admin_layout_end();
