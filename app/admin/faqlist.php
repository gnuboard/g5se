<?php
/*
 * /admin/faqlist — 특정 FAQ 분류의 Q&A 항목 목록.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

$sub_menu = '300700';
auth_check_menu($auth, $sub_menu, 'r');

$fm_id = isset($_GET['fm_id']) ? (int)$_GET['fm_id'] : 0;
$fm = $fm_id ? sql_fetch(" select * from {$g5['faq_master_table']} where fm_id = '{$fm_id}' ") : null;

if (!$fm || empty($fm['fm_id'])) {
    admin_layout_start('FAQ 상세', 'faq');
    echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">존재하지 않는 FAQ 분류입니다. <a href="/admin/faqmasterlist" class="underline">목록으로</a></div></main>';
    admin_layout_end();
    exit;
}

$result = sql_query(" select * from {$g5['faq_table']} where fm_id = '{$fm_id}' order by fa_order, fa_id ");
$total_count = (int)sql_fetch(" select count(*) as cnt from {$g5['faq_table']} where fm_id = '{$fm_id}' ")['cnt'];

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

admin_layout_start('FAQ 상세 — '.$fm['fm_subject'], 'faq');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex items-center gap-3 mb-5">
        <a href="/admin/faqmasterlist" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="분류 목록">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </a>
        <div class="min-w-0">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="/admin/faqmasterlist" class="hover:underline">FAQ 분류</a>
                <span>/</span>
                <span class="font-mono">#<?php echo (int)$fm['fm_id'] ?></span>
            </div>
            <h2 class="text-xl font-bold tracking-tight"><?php echo $h($fm['fm_subject']) ?></h2>
            <p class="text-xs text-slate-500 mt-0.5">총 <strong class="text-slate-700 dark:text-slate-300"><?php echo number_format($total_count) ?></strong>개 Q&amp;A 항목</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <a href="<?php echo G5_BBS_URL ?>/faq.php?fm_id=<?php echo (int)$fm['fm_id'] ?>" target="_blank" class="inline-flex items-center h-9 px-3 rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">미리보기</a>
            <a href="/admin/faqform?fm_id=<?php echo (int)$fm['fm_id'] ?>" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                항목 추가
            </a>
        </div>
    </header>

    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-2.5 text-center w-16">#</th>
                    <th class="px-4 py-2.5 text-left">질문</th>
                    <th class="px-4 py-2.5 text-center w-20">순서</th>
                    <th class="px-4 py-2.5 text-right whitespace-nowrap">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            while ($row = sql_fetch_array($result)):
                $subj = strip_tags(conv_content($row['fa_subject'], 1));
                ?>
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                    <td class="px-4 py-3 text-center text-slate-500"><?php echo $i + 1 ?></td>
                    <td class="px-4 py-3"><?php echo $h(mb_strimwidth($subj, 0, 100, '…')) ?></td>
                    <td class="px-4 py-3 text-center text-slate-500"><?php echo (int)$row['fa_order'] ?></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                        <a href="/admin/faqform?w=u&amp;fm_id=<?php echo (int)$row['fm_id'] ?>&amp;fa_id=<?php echo (int)$row['fa_id'] ?>" class="inline-flex items-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">수정</a>
                        <a href="/admin/faqformupdate?w=d&amp;fm_id=<?php echo (int)$row['fm_id'] ?>&amp;fa_id=<?php echo (int)$row['fa_id'] ?>&amp;token=<?php echo get_admin_token() ?>" data-confirm="이 Q&amp;A 항목을 삭제할까요?" class="js-confirm inline-flex items-center h-8 px-2.5 rounded-md border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/30">삭제</a>
                    </td>
                </tr>
                <?php
                $i++;
            endwhile;
            if ($i === 0): ?>
                <tr><td colspan="4" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">자료가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

</main>

<script>
document.querySelectorAll('a.js-confirm').forEach(function (a) {
    a.addEventListener('click', function (e) { if (!confirm(a.dataset.confirm)) e.preventDefault(); });
});
</script>

<?php
admin_layout_end();
