<?php
/*
 * /admin/faqform — FAQ Q&A 항목 추가/수정.
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
$fa_id = isset($_GET['fa_id']) ? (int)$_GET['fa_id'] : 0;

$fm = $fm_id ? sql_fetch(" select * from {$g5['faq_master_table']} where fm_id = '{$fm_id}' ") : null;
if (!$fm || empty($fm['fm_id'])) {
    admin_layout_start('FAQ 항목', 'faq');
    echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">존재하지 않는 FAQ 분류입니다.</div></main>';
    admin_layout_end();
    exit;
}

$fa_default = ['fa_id'=>0, 'fm_id'=>$fm_id, 'fa_subject'=>'', 'fa_content'=>'', 'fa_order'=>0];

if ($w === 'u') {
    $row = sql_fetch(" select * from {$g5['faq_table']} where fa_id = '{$fa_id}' ");
    if (empty($row['fa_id'])) {
        admin_layout_start('FAQ 항목 수정', 'faq');
        echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">등록된 자료가 없습니다.</div></main>';
        admin_layout_end();
        exit;
    }
    $fa = array_merge($fa_default, $row);
    $page_title = 'Q&A 수정';
} else {
    $fa = $fa_default;
    $page_title = 'Q&A 추가';
}

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

admin_layout_start($page_title.' — '.$fm['fm_subject'], 'faq');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex items-center gap-3 mb-6">
        <a href="/admin/faqlist?fm_id=<?php echo (int)$fm['fm_id'] ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="목록으로">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </a>
        <div class="min-w-0">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="/admin/faqmasterlist" class="hover:underline">FAQ 분류</a>
                <span>/</span>
                <a href="/admin/faqlist?fm_id=<?php echo (int)$fm['fm_id'] ?>" class="hover:underline truncate"><?php echo $h($fm['fm_subject']) ?></a>
            </div>
            <h2 class="text-xl font-bold tracking-tight"><?php echo $h($page_title) ?></h2>
        </div>
    </header>

    <form id="frmfaqform" action="/admin/faqformupdate" method="post" class="space-y-4">
        <input type="hidden" name="w"     value="<?php echo $h($w) ?>">
        <input type="hidden" name="fm_id" value="<?php echo (int)$fm_id ?>">
        <input type="hidden" name="fa_id" value="<?php echo (int)$fa_id ?>">
        <input type="hidden" name="token" value="<?php echo get_admin_token() ?>">

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="fa_order" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">출력 순서</label>
                    <input type="number" name="fa_order" id="fa_order" value="<?php echo (int)$fa['fa_order'] ?>"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    <p class="mt-1 text-xs text-slate-500">숫자가 작을수록 먼저</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">질문</h3>
            <?php echo editor_html('fa_subject', get_text(html_purifier($fa['fa_subject']), 0)) ?>
        </section>

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">답변</h3>
            <?php echo editor_html('fa_content', get_text(html_purifier($fa['fa_content']), 0)) ?>
        </section>

        <div class="flex items-center justify-end gap-2">
            <a href="/admin/faqlist?fm_id=<?php echo (int)$fm_id ?>" class="h-10 px-5 inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">취소</a>
            <button type="submit" class="h-10 px-6 inline-flex items-center rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-semibold">저장</button>
        </div>
    </form>

</main>

<?php
admin_layout_end();
