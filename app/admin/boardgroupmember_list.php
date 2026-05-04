<?php
/*
 * /admin/boardgroupmember_list — 그룹의 접근가능 회원 목록.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

$sub_menu = '300200';
auth_check_menu($auth, $sub_menu, 'r');

$gr_id = isset($_GET['gr_id']) ? trim((string)$_GET['gr_id']) : '';
$gr = $gr_id ? get_group($gr_id) : null;

if (!$gr || empty($gr['gr_id'])) {
    admin_layout_start('그룹 접근 회원', 'groups');
    echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">존재하지 않는 그룹입니다. <a href="/admin/boardgroup_list" class="underline">그룹 목록으로</a></div></main>';
    admin_layout_end();
    exit;
}

$sfl  = isset($_GET['sfl']) ? (string)$_GET['sfl'] : 'a.mb_id';
$stx  = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$sst  = isset($_GET['sst']) ? (string)$_GET['sst'] : '';
$sod  = isset($_GET['sod']) ? (string)$_GET['sod'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$gr_id_q = addslashes($gr_id);
$sql_common = " from {$g5['group_member_table']} a left outer join {$g5['member_table']} b on (a.mb_id = b.mb_id) ";
$sql_search = " where gr_id = '{$gr_id_q}' ";
if ($stx !== '') {
    $stx_q = addslashes($stx);
    if (!in_array($sfl, ['a.mb_id'], true)) $sfl = 'a.mb_id';
    $sql_search .= " and ({$sfl} like '%{$stx_q}%') ";
}

$allowed = ['gm_datetime','b.mb_id','b.mb_name','b.mb_nick','b.mb_today_login','a.gm_datetime'];
if (!$sst || !in_array($sst, $allowed, true)) { $sst = 'gm_datetime'; $sod = 'desc'; }
if ($sod && !in_array(strtolower($sod), ['asc','desc'], true)) $sod = '';
$sql_order = " order by {$sst} {$sod} ";

$total_count = (int)sql_fetch(" select count(*) as cnt {$sql_common} {$sql_search} ")['cnt'];
$rows = (int)$config['cf_page_rows'];
$total_page = max(1, (int)ceil($total_count / max(1, $rows)));
$from = ($page - 1) * $rows;
$result = sql_query(" select * {$sql_common} {$sql_search} {$sql_order} limit {$from}, {$rows} ");

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$sort_link = function (string $col) use ($sst, $sod, $sfl, $stx, $page, $gr_id, $h): string {
    $next = ($sst === $col && $sod === 'asc') ? 'desc' : 'asc';
    $qs = http_build_query(array_filter([
        'gr_id'=>$gr_id, 'sst'=>$col, 'sod'=>$next, 'sfl'=>$sfl, 'stx'=>$stx, 'page'=>$page,
    ], static fn($v) => $v !== '' && $v !== null));
    $arrow = '';
    if ($sst === $col) {
        $arrow = $sod === 'desc'
            ? '<svg class="w-3 h-3 inline-block opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>'
            : '<svg class="w-3 h-3 inline-block opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>';
    }
    return '<a href="/admin/boardgroupmember_list?'.$h($qs).'" class="inline-flex items-center gap-0.5 hover:text-admin-primary-700 dark:hover:text-admin-primary-300">';
};

admin_layout_start('그룹 접근 회원 — '.$gr['gr_subject'], 'groups');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex flex-wrap items-center gap-3 mb-5">
        <a href="/admin/boardgroup_list" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="그룹 목록">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </a>
        <div class="min-w-0">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="/admin/boardgroup_list" class="hover:underline">그룹</a>
                <span>/</span>
                <span class="font-mono"><?php echo $h($gr['gr_id']) ?></span>
            </div>
            <h2 class="text-xl font-bold tracking-tight"><?php echo $h($gr['gr_subject']) ?> <span class="text-sm font-normal text-slate-500">접근 회원</span></h2>
            <p class="text-xs text-slate-500 mt-0.5">총 <strong class="text-slate-700 dark:text-slate-300"><?php echo number_format($total_count) ?></strong>명</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <form method="get" action="/admin/boardgroupmember_list" class="flex items-center gap-2">
                <input type="hidden" name="gr_id" value="<?php echo $h($gr_id) ?>">
                <select name="sfl" class="h-9 pl-3 pr-8 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    <option value="a.mb_id">회원아이디</option>
                </select>
                <input type="text" name="stx" value="<?php echo $h($stx) ?>" placeholder="검색어"
                       class="h-9 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm w-44">
                <button type="submit" class="h-9 px-3 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium">검색</button>
                <?php if ($stx !== ''): ?>
                <a href="/admin/boardgroupmember_list?gr_id=<?php echo urlencode($gr_id) ?>" class="h-9 px-3 inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300">전체</a>
                <?php endif; ?>
            </form>
        </div>
    </header>

    <form id="fboardgroupmember" action="/admin/boardgroupmember_update" method="post"
          class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
        <input type="hidden" name="gr_id" value="<?php echo $h($gr_id) ?>">
        <input type="hidden" name="w"     value="ld">
        <input type="hidden" name="token" value="<?php echo get_admin_token() ?>">
        <input type="hidden" name="sst"  value="<?php echo $h($sst) ?>">
        <input type="hidden" name="sod"  value="<?php echo $h($sod) ?>">
        <input type="hidden" name="sfl"  value="<?php echo $h($sfl) ?>">
        <input type="hidden" name="stx"  value="<?php echo $h($stx) ?>">
        <input type="hidden" name="page" value="<?php echo (int)$page ?>">

        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="w-10 px-3 py-2.5 text-center"><input type="checkbox" id="chkall" class="rounded border-slate-300"></th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap">접근그룹</th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('b.mb_id') ?>회원아이디</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('b.mb_name') ?>이름</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('b.mb_nick') ?>별명</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('b.mb_today_login') ?>최종접속</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('a.gm_datetime') ?>처리일시</a></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            while ($row = sql_fetch_array($result)):
                $row2 = sql_fetch(" select count(*) as cnt from {$g5['group_member_table']} where mb_id = '".addslashes($row['mb_id'])."' ");
                $cnt = (int)$row2['cnt'];
                ?>
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                    <td class="px-3 py-2 text-center"><input type="checkbox" name="chk[]" value="<?php echo (int)$row['gm_id'] ?>" class="rounded border-slate-300"></td>
                    <td class="px-3 py-2 text-center">
                        <?php if ($cnt): ?>
                        <a href="/admin/boardgroupmember_form?mb_id=<?php echo urlencode($row['mb_id']) ?>" class="inline-flex items-center justify-center min-w-7 h-6 px-2 rounded text-xs bg-admin-primary-50 dark:bg-admin-primary-950 text-admin-primary-700 dark:text-admin-primary-300 hover:bg-admin-primary-100 dark:hover:bg-admin-primary-900"><?php echo $cnt ?></a>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap font-mono text-xs"><?php echo $h($row['mb_id']) ?></td>
                    <td class="px-3 py-2 whitespace-nowrap"><?php echo $h($row['mb_name']) ?></td>
                    <td class="px-3 py-2 whitespace-nowrap"><?php echo $h($row['mb_nick']) ?></td>
                    <td class="px-3 py-2 whitespace-nowrap text-slate-500 text-xs"><?php echo $h(substr($row['mb_today_login'] ?? '', 2, 8)) ?></td>
                    <td class="px-3 py-2 whitespace-nowrap text-slate-500 text-xs"><?php echo $h($row['gm_datetime']) ?></td>
                </tr>
                <?php
                $i++;
            endwhile;
            if ($i === 0): ?>
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">접근가능 회원이 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>

        <div class="flex flex-wrap items-center gap-2 px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
            <button type="submit" class="h-9 px-3.5 rounded-md bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium">선택 삭제</button>
            <span class="ml-auto text-xs text-slate-500"><?php echo number_format($total_count) ?>명 중 <?php echo (int)$page ?>/<?php echo (int)$total_page ?>페이지</span>
        </div>
    </form>

    <?php if ($total_page > 1): ?>
    <nav class="mt-4 flex items-center gap-1 justify-center text-sm">
        <?php
        $pgCls = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
        $pgActive = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md bg-admin-primary-600 text-white font-semibold';
        $pgUrl = function ($p) use ($gr_id, $sfl, $stx, $sst, $sod) {
            $params = array_filter(['gr_id'=>$gr_id,'sfl'=>$sfl,'stx'=>$stx,'sst'=>$sst,'sod'=>$sod,'page'=>$p], static fn($v) => $v !== '' && $v !== null);
            return '/admin/boardgroupmember_list?'.http_build_query($params);
        };
        for ($p = 1; $p <= $total_page; $p++):
            if ($p === $page): ?>
                <span class="<?php echo $pgActive ?>"><?php echo $p ?></span>
            <?php else: ?>
                <a class="<?php echo $pgCls ?>" href="<?php echo $h($pgUrl($p)) ?>"><?php echo $p ?></a>
            <?php endif;
        endfor; ?>
    </nav>
    <?php endif; ?>

</main>

<script>
(function () {
    var f = document.getElementById('fboardgroupmember');
    var ck = document.getElementById('chkall');
    if (ck && f) ck.addEventListener('change', function () {
        f.querySelectorAll('input[name="chk[]"]').forEach(function (i) { i.checked = ck.checked; });
    });
    if (f) f.addEventListener('submit', function (e) {
        if (!f.querySelector('input[name="chk[]"]:checked')) { e.preventDefault(); alert('선택삭제 하실 항목을 하나 이상 선택하세요.'); return; }
        if (!confirm('선택한 회원을 그룹에서 제거할까요?')) e.preventDefault();
    });
})();
</script>

<?php
admin_layout_end();
