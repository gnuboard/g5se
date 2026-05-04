<?php
/*
 * /admin/content_list — 내용(콘텐츠) 관리 목록.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

$sub_menu = '300600';
auth_check_menu($auth, $sub_menu, 'r');

if (!isset($g5['content_table'])) {
    die('<meta charset="utf-8">/data/dbconfig.php 파일에 <strong>$g5[\'content_table\'] = G5_TABLE_PREFIX.\'content\';</strong> 를 추가해 주세요.');
}

// 내용 테이블 보장 (gnuboard 가 lazy 생성)
if (!sql_query(" DESCRIBE {$g5['content_table']} ", false)) {
    sql_query(
        " CREATE TABLE IF NOT EXISTS `{$g5['content_table']}` (
            `co_id` varchar(20) NOT NULL DEFAULT '',
            `co_html` tinyint(4) NOT NULL DEFAULT '0',
            `co_subject` varchar(255) NOT NULL DEFAULT '',
            `co_content` longtext NOT NULL,
            `co_hit` int(11) NOT NULL DEFAULT '0',
            `co_include_head` varchar(255) NOT NULL,
            `co_include_tail` varchar(255) NOT NULL,
            PRIMARY KEY (`co_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$rows = (int)$config['cf_page_rows'];
$row  = sql_fetch(" select count(*) as cnt from {$g5['content_table']} ");
$total_count = (int)$row['cnt'];
$total_page  = max(1, (int)ceil($total_count / max(1, $rows)));
$from        = ($page - 1) * $rows;
$result = sql_query(" select * from {$g5['content_table']} order by co_id limit {$from}, {$rows} ");

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

admin_layout_start('내용 관리', 'contents');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full max-w-6xl mx-auto">

    <header class="flex flex-wrap items-center gap-3 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight">내용 관리</h2>
            <p class="text-xs text-slate-500 mt-0.5">총 <strong class="text-slate-700 dark:text-slate-300"><?php echo number_format($total_count) ?></strong>개 내용 페이지 · /content/{ID} 로 노출</p>
        </div>
        <div class="ml-auto">
            <a href="/admin/content_form" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                내용 추가
            </a>
        </div>
    </header>

    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-2.5 text-left whitespace-nowrap">ID</th>
                    <th class="px-4 py-2.5 text-left">제목</th>
                    <th class="px-4 py-2.5 text-right whitespace-nowrap">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            while ($row = sql_fetch_array($result)):
                ?>
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                    <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-admin-primary-700 dark:text-admin-primary-300"><?php echo $h($row['co_id']) ?></td>
                    <td class="px-4 py-3"><?php echo $h($row['co_subject']) ?></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                        <a href="/admin/content_form?w=u&amp;co_id=<?php echo urlencode($row['co_id']) ?>" class="inline-flex items-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">수정</a>
                        <a href="<?php echo $h(get_pretty_url('content', $row['co_id'])) ?>" target="_blank" class="inline-flex items-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">보기</a>
                        <a href="/admin/content_form_update?w=d&amp;co_id=<?php echo urlencode($row['co_id']) ?>&amp;token=<?php echo get_admin_token() ?>" data-confirm="정말 삭제하시겠습니까?" class="js-confirm inline-flex items-center h-8 px-2.5 rounded-md border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/30">삭제</a>
                    </td>
                </tr>
                <?php
                $i++;
            endwhile;
            if ($i === 0): ?>
                <tr><td colspan="3" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">자료가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <?php if ($total_page > 1): ?>
    <nav class="mt-4 flex items-center gap-1 justify-center text-sm">
        <?php
        $size = G5_IS_MOBILE ? (int)$config['cf_mobile_pages'] : (int)$config['cf_write_pages'];
        if ($size < 1) $size = 10;
        $start = max(1, $page - (int)floor($size/2));
        $end   = min($total_page, $start + $size - 1);
        $start = max(1, $end - $size + 1);
        $pgCls = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
        $pgActive = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md bg-admin-primary-600 text-white border border-admin-primary-600 font-semibold';
        for ($p = $start; $p <= $end; $p++):
            if ($p === $page): ?>
                <span class="<?php echo $pgActive ?>"><?php echo $p ?></span>
            <?php else: ?>
                <a class="<?php echo $pgCls ?>" href="/admin/content_list?page=<?php echo $p ?>"><?php echo $p ?></a>
            <?php endif;
        endfor; ?>
    </nav>
    <?php endif; ?>

</main>

<script>
document.querySelectorAll('a.js-confirm').forEach(function (a) {
    a.addEventListener('click', function (e) {
        if (!confirm(a.dataset.confirm || '정말 진행하시겠습니까?')) e.preventDefault();
    });
});
</script>

<?php
admin_layout_end();
