<?php
/*
 * /admin/point_list — 포인트 내역 (조회 + 일괄삭제 + 회원 포인트 증감).
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_ADMIN_PATH.'/admin.lib.php';

$sub_menu = '200200';
auth_check_menu($auth, $sub_menu, 'r');

$sfl  = isset($_GET['sfl']) ? (string)$_GET['sfl'] : '';
$stx  = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$sst  = isset($_GET['sst']) ? (string)$_GET['sst'] : '';
$sod  = isset($_GET['sod']) ? (string)$_GET['sod'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$sql_common = " from {$g5['point_table']} po ";
$sql_search = " where (1) ";
$params     = [];
if ($stx !== '') {
    if ($sfl === 'mb_id') {
        $sql_search .= " and (po.mb_id = ?) ";
        $params[] = $stx;
    } else {
        if (!in_array($sfl, ['po_content'], true)) $sfl = 'po_content';
        // 컬럼명은 화이트리스트 매칭 — placeholder 못 쓰는 식별자라 보간
        $sql_search .= " and ({$sfl} like ?) ";
        $params[] = '%'.$stx.'%';
    }
}

$allowed = ['po_id','mb_id','po_content','po_point','po_datetime'];
if (!$sst || !in_array($sst, $allowed, true)) { $sst = 'po_id'; $sod = 'desc'; }
if ($sod && !in_array(strtolower($sod), ['asc','desc'], true)) $sod = '';
$sql_order = " order by {$sst} {$sod} ";

$total_count = (int)sql_pdo_fetch(" select count(*) as cnt {$sql_common} {$sql_search} ", $params)['cnt'];
$rows = (int)$config['cf_page_rows'];
$total_page = max(1, (int)ceil($total_count / max(1, $rows)));
$from = ($page - 1) * $rows;

$result = sql_pdo_query(" select po.*, mb.mb_name, mb.mb_nick, mb.mb_email, mb.mb_homepage, mb.mb_point
    {$sql_common} LEFT JOIN {$g5['member_table']} mb ON po.mb_id = mb.mb_id
    {$sql_search} {$sql_order} limit {$from}, {$rows} ", $params);

$mb = ($sfl === 'mb_id' && $stx !== '') ? get_member($stx) : [];
$total_sum = sql_pdo_fetch(" select sum(po_point) as s from {$g5['point_table']} ")['s'] ?? 0;

$po_expire_term = ((int)$config['cf_point_term'] > 0) ? (int)$config['cf_point_term'] : '';

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$sort_link = function (string $col) use ($sst, $sod, $sfl, $stx, $page, $h): string {
    $next = ($sst === $col && $sod === 'asc') ? 'desc' : 'asc';
    $qs = http_build_query(array_filter([
        'sst'=>$col,'sod'=>$next,'sfl'=>$sfl,'stx'=>$stx,'page'=>$page,
    ], static fn($v) => $v !== '' && $v !== null));
    $arrow = '';
    if ($sst === $col) {
        $arrow = $sod === 'desc'
            ? '<span class="sort-arrow">▾</span>'
            : '<span class="sort-arrow">▴</span>';
    }
    return '<a href="/admin/point_list?'.$h($qs).'" class="inline-flex items-center gap-0.5 hover:text-admin-primary-700 dark:hover:text-admin-primary-300">';
};

admin_layout_start('포인트 내역', 'mb_point');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex flex-wrap items-center gap-3 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight">포인트 내역</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                총 <strong class="text-slate-700 dark:text-slate-300"><?php echo number_format($total_count) ?></strong>건 ·
                <?php if (!empty($mb['mb_id'])): ?>
                    <strong class="text-slate-700 dark:text-slate-300"><?php echo $h($mb['mb_id']) ?></strong> 의 합계 <strong class="text-admin-primary-700 dark:text-admin-primary-300"><?php echo number_format((int)$mb['mb_point']) ?></strong>점
                <?php else: ?>
                    전체 합계 <strong class="text-admin-primary-700 dark:text-admin-primary-300"><?php echo number_format((int)$total_sum) ?></strong>점
                <?php endif; ?>
            </p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <form method="get" action="/admin/point_list" class="flex items-center gap-2">
                <select name="sfl" class="h-9 pl-3 pr-8 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    <option value="mb_id"      <?php echo $sfl==='mb_id'?'selected':'' ?>>회원아이디</option>
                    <option value="po_content" <?php echo $sfl==='po_content'?'selected':'' ?>>내용</option>
                </select>
                <input type="text" name="stx" value="<?php echo $h($stx) ?>" placeholder="검색어"
                       class="h-9 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm w-44">
                <button type="submit" class="h-9 px-3 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium">검색</button>
                <?php if ($stx !== ''): ?>
                <a href="/admin/point_list" class="h-9 px-3 inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300">전체</a>
                <?php endif; ?>
            </form>
        </div>
    </header>

    <form id="fpointlist" action="/admin/point_list_delete" method="post"
          class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden mb-6">
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
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('mb_id') ?>회원아이디</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap">이름</th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap">닉네임</th>
                    <th class="px-3 py-2.5 text-left"><?php echo $sort_link('po_content') ?>내용</a></th>
                    <th class="px-3 py-2.5 text-right whitespace-nowrap"><?php echo $sort_link('po_point') ?>포인트</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('po_datetime') ?>일시</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap">만료일</th>
                    <th class="px-3 py-2.5 text-right whitespace-nowrap">합계</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            while ($row = sql_fetch_array($result)):
                $linkable = !preg_match('/^@/', $row['po_rel_table']) && $row['po_rel_table'];
                $is_expired = (int)$row['po_expired'] === 1;
                ?>
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                    <td class="px-3 py-2 text-center">
                        <input type="hidden" name="mb_id[<?php echo $i ?>]" value="<?php echo $h($row['mb_id']) ?>">
                        <input type="hidden" name="po_id[<?php echo $i ?>]" value="<?php echo (int)$row['po_id'] ?>">
                        <input type="checkbox" name="chk[]" value="<?php echo $i ?>" class="rounded border-slate-300">
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap font-mono text-xs">
                        <a href="/admin/point_list?sfl=mb_id&amp;stx=<?php echo urlencode($row['mb_id']) ?>" class="text-admin-primary-700 dark:text-admin-primary-300 hover:underline"><?php echo $h($row['mb_id']) ?></a>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap"><?php echo $h($row['mb_name']) ?></td>
                    <td class="px-3 py-2 whitespace-nowrap"><?php echo $h($row['mb_nick']) ?></td>
                    <td class="px-3 py-2">
                        <?php if ($linkable): ?>
                            <a href="<?php echo $h(get_pretty_url($row['po_rel_table'], $row['po_rel_id'])) ?>" target="_blank" class="hover:underline"><?php echo $h($row['po_content']) ?></a>
                        <?php else: ?>
                            <?php echo $h($row['po_content']) ?>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 text-right whitespace-nowrap font-mono <?php echo (int)$row['po_point'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' ?>"><?php echo number_format((int)$row['po_point']) ?></td>
                    <td class="px-3 py-2 whitespace-nowrap text-slate-500 text-xs"><?php echo $h($row['po_datetime']) ?></td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs <?php echo $is_expired ? 'text-rose-500 line-through' : 'text-slate-500' ?>">
                        <?php if ($is_expired): ?>
                            만료 <?php echo $h(substr(str_replace('-', '', $row['po_expire_date']), 2)) ?>
                        <?php else: ?>
                            <?php echo $row['po_expire_date'] === '9999-12-31' ? '—' : $h($row['po_expire_date']) ?>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 text-right whitespace-nowrap font-mono text-slate-700 dark:text-slate-300"><?php echo number_format((int)$row['po_mb_point']) ?></td>
                </tr>
                <?php
                $i++;
            endwhile;
            if ($i === 0): ?>
                <tr><td colspan="9" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">자료가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>

        <div class="flex flex-wrap items-center gap-2 px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
            <button type="submit" name="act_button" value="선택삭제" class="h-9 px-3.5 rounded-md bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium">선택 삭제</button>
            <span class="ml-auto text-xs text-slate-500"><?php echo number_format($total_count) ?>건 중 <?php echo (int)$page ?>/<?php echo (int)$total_page ?>페이지</span>
        </div>
    </form>

    <?php if ($total_page > 1): ?>
    <nav class="mb-6 flex items-center gap-1 justify-center text-sm">
        <?php
        $pgCls = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
        $pgActive = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md bg-admin-primary-600 text-white font-semibold';
        $pgUrl = function ($p) use ($sfl, $stx, $sst, $sod) {
            $params = array_filter(['sfl'=>$sfl,'stx'=>$stx,'sst'=>$sst,'sod'=>$sod,'page'=>$p], static fn($v) => $v !== '' && $v !== null);
            return '/admin/point_list?'.http_build_query($params);
        };
        $size = G5_IS_MOBILE ? (int)$config['cf_mobile_pages'] : (int)$config['cf_write_pages'];
        if ($size < 1) $size = 10;
        $start = max(1, $page - (int)floor($size/2));
        $end   = min($total_page, $start + $size - 1);
        $start = max(1, $end - $size + 1);
        for ($p = $start; $p <= $end; $p++):
            if ($p === $page): ?>
                <span class="<?php echo $pgActive ?>"><?php echo $p ?></span>
            <?php else: ?>
                <a class="<?php echo $pgCls ?>" href="<?php echo $h($pgUrl($p)) ?>"><?php echo $p ?></a>
            <?php endif;
        endfor; ?>
    </nav>
    <?php endif; ?>

    <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">개별 회원 포인트 증감</h3>
        <form id="fpointlist2" action="/admin/point_update" method="post" autocomplete="off" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="hidden" name="sfl"  value="<?php echo $h($sfl) ?>">
            <input type="hidden" name="stx"  value="<?php echo $h($stx) ?>">
            <input type="hidden" name="sst"  value="<?php echo $h($sst) ?>">
            <input type="hidden" name="sod"  value="<?php echo $h($sod) ?>">
            <input type="hidden" name="page" value="<?php echo (int)$page ?>">
            <input type="hidden" name="token" value="<?php echo get_admin_token() ?>">

            <div>
                <label for="mb_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">회원 아이디 <span class="text-admin-primary-600">*</span></label>
                <input type="text" name="mb_id" id="mb_id" required value="<?php echo $sfl==='mb_id' ? $h($stx) : '' ?>"
                       class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono">
            </div>
            <div>
                <label for="po_point" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">포인트 <span class="text-admin-primary-600">*</span></label>
                <input type="text" name="po_point" id="po_point" required placeholder="음수면 차감"
                       class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
            </div>
            <div class="sm:col-span-2">
                <label for="po_content" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">포인트 내용 <span class="text-admin-primary-600">*</span></label>
                <input type="text" name="po_content" id="po_content" required maxlength="255"
                       class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
            </div>
            <?php if ((int)$config['cf_point_term'] > 0): ?>
            <div>
                <label for="po_expire_term" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">유효 기간 (일)</label>
                <input type="number" name="po_expire_term" id="po_expire_term" value="<?php echo $h($po_expire_term) ?>"
                       class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
            </div>
            <?php endif; ?>
            <div class="sm:col-span-2 flex justify-end">
                <button type="submit" class="h-10 px-6 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-semibold">포인트 적용</button>
            </div>
        </form>
    </section>

</main>

<script>
(function () {
    var f = document.getElementById('fpointlist');
    var ck = document.getElementById('chkall');
    if (ck && f) ck.addEventListener('change', function () {
        f.querySelectorAll('input[name="chk[]"]').forEach(function (i) { i.checked = ck.checked; });
    });
    if (f) f.addEventListener('submit', function (e) {
        if (!f.querySelector('input[name="chk[]"]:checked')) { e.preventDefault(); alert('선택삭제 하실 항목을 하나 이상 선택하세요.'); return; }
        if (!confirm('선택한 자료를 정말 삭제하시겠습니까?')) e.preventDefault();
    });
})();
</script>

<?php
admin_layout_end();
