<?php
/*
 * /admin/faqmasterlist — FAQ 마스터(분류) 목록.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

$sub_menu = '300700';
auth_check_menu($auth, $sub_menu, 'r');

if (!isset($g5['faq_table']) || !isset($g5['faq_master_table'])) {
    die('<meta charset="utf-8">/data/dbconfig.php 파일에 <strong>$g5[\'faq_table\']</strong> 와 <strong>$g5[\'faq_master_table\']</strong> 를 추가해 주세요.');
}

// FAQ master 테이블 보장 — DDL 은 placeholder 못 받지만 sql_pdo_query 로 통일 (params 빈 배열)
if (!sql_pdo_query(" DESCRIBE {$g5['faq_master_table']} ", [], false)) {
    sql_pdo_query(
        " CREATE TABLE IF NOT EXISTS `{$g5['faq_master_table']}` (
            `fm_id` int(11) NOT NULL AUTO_INCREMENT,
            `fm_subject` varchar(255) NOT NULL DEFAULT '',
            `fm_head_html` text NOT NULL,
            `fm_tail_html` text NOT NULL,
            `fm_order` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`fm_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", [], true);
    sql_pdo_query(" insert into `{$g5['faq_master_table']}` set fm_id = :fm_id, fm_subject = :fm_subject ",
                  [':fm_id' => 1, ':fm_subject' => '자주하시는 질문'], false);
}
// FAQ 항목 테이블 보장
if (!sql_pdo_query(" DESCRIBE {$g5['faq_table']} ", [], false)) {
    sql_pdo_query(
        " CREATE TABLE IF NOT EXISTS `{$g5['faq_table']}` (
            `fa_id` int(11) NOT NULL AUTO_INCREMENT,
            `fm_id` int(11) NOT NULL DEFAULT '0',
            `fa_subject` text NOT NULL,
            `fa_content` text NOT NULL,
            `fa_order` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`fa_id`),
            KEY `fm_id` (`fm_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", [], true);
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$rows = (int)$config['cf_page_rows'];
$total_count = (int)sql_pdo_fetch(" select count(*) as cnt from {$g5['faq_master_table']} ")['cnt'];
$total_page  = max(1, (int)ceil($total_count / max(1, $rows)));
$from        = ($page - 1) * $rows;
// LIMIT 의 from/rows 는 (int) 캐스트 정수라 보간 안전
$result = sql_pdo_query(" select * from {$g5['faq_master_table']} order by fm_order, fm_id limit {$from}, {$rows} ");

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

admin_layout_start('FAQ 관리', 'scf_faq');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex flex-wrap items-center gap-3 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight">FAQ 관리</h2>
            <p class="text-xs text-slate-500 mt-0.5">총 <strong class="text-slate-700 dark:text-slate-300"><?php echo number_format($total_count) ?></strong>개 분류 · 분류(Master) 안에 Q&amp;A 항목을 등록</p>
        </div>
        <div class="ml-auto">
            <a href="/admin/faqmasterform" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                FAQ 분류 추가
            </a>
        </div>
    </header>

    <p class="mb-4 text-xs text-slate-500">
        먼저 <strong>FAQ 분류</strong>(자주하시는 질문, 이용안내 등)를 만들고, 분류의 <strong>제목</strong>을 클릭해 Q&amp;A 세부 항목을 관리합니다.
    </p>

    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-2.5 text-center w-16">ID</th>
                    <th class="px-4 py-2.5 text-left">제목</th>
                    <th class="px-4 py-2.5 text-center w-24">FAQ 수</th>
                    <th class="px-4 py-2.5 text-center w-20">순서</th>
                    <th class="px-4 py-2.5 text-right whitespace-nowrap">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            while ($row = sql_fetch_array($result)):
                $cnt = (int)sql_pdo_fetch(" select count(*) as cnt from {$g5['faq_table']} where fm_id = :fm_id ", [':fm_id' => (int)$row['fm_id']])['cnt'];
                ?>
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                    <td class="px-4 py-3 text-center font-mono text-xs text-slate-500"><?php echo (int)$row['fm_id'] ?></td>
                    <td class="px-4 py-3">
                        <a href="/admin/faqlist?fm_id=<?php echo (int)$row['fm_id'] ?>&amp;fm_subject=<?php echo urlencode($row['fm_subject']) ?>" class="text-admin-primary-700 dark:text-admin-primary-300 hover:underline font-medium"><?php echo $h($row['fm_subject']) ?></a>
                    </td>
                    <td class="px-4 py-3 text-center"><?php echo number_format($cnt) ?></td>
                    <td class="px-4 py-3 text-center text-slate-500"><?php echo (int)$row['fm_order'] ?></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                        <a href="/admin/faqmasterform?w=u&amp;fm_id=<?php echo (int)$row['fm_id'] ?>" class="inline-flex items-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">수정</a>
                        <a href="<?php echo G5_BBS_URL ?>/faq.php?fm_id=<?php echo (int)$row['fm_id'] ?>" target="_blank" class="inline-flex items-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">보기</a>
                        <a href="/admin/faqmasterformupdate?w=d&amp;fm_id=<?php echo (int)$row['fm_id'] ?>&amp;token=<?php echo get_admin_token() ?>" data-confirm="이 분류와 안의 모든 Q&amp;A 가 삭제됩니다. 계속할까요?" class="js-confirm inline-flex items-center h-8 px-2.5 rounded-md border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/30">삭제</a>
                    </td>
                </tr>
                <?php
                $i++;
            endwhile;
            if ($i === 0): ?>
                <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">자료가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <?php if ($total_page > 1): ?>
    <nav class="mt-4 flex items-center gap-1 justify-center text-sm">
        <?php
        $pgCls = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
        $pgActive = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md bg-admin-primary-600 text-white font-semibold';
        for ($p = 1; $p <= $total_page; $p++):
            if ($p === $page): ?>
                <span class="<?php echo $pgActive ?>"><?php echo $p ?></span>
            <?php else: ?>
                <a class="<?php echo $pgCls ?>" href="/admin/faqmasterlist?page=<?php echo $p ?>"><?php echo $p ?></a>
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
