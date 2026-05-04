<?php
/*
 * /admin/boardgroup_list — 게시판 그룹 관리 (벌크 편집).
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

$sub_menu = '300200';
auth_check_menu($auth, $sub_menu, 'r');

// gr_device 컬럼 보장 (gnuboard 가 lazy migration 함)
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_device` ENUM('both','pc','mobile') NOT NULL DEFAULT 'both' AFTER `gr_subject` ", false);

$sfl  = isset($_GET['sfl']) ? (string)$_GET['sfl'] : '';
$stx  = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$sst  = isset($_GET['sst']) ? (string)$_GET['sst'] : '';
$sod  = isset($_GET['sod']) ? (string)$_GET['sod'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$sql_common = " from {$g5['group_table']} ";
$sql_search = " where (1) ";
if ($is_admin !== 'super') {
    $sql_search .= " and (gr_admin = '".addslashes($member['mb_id'])."') ";
}
if ($stx !== '') {
    $stx_q = addslashes($stx);
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'gr_id':
        case 'gr_admin':    $sql_search .= " ({$sfl} = '{$stx_q}') "; break;
        case 'gr_subject':default: $sql_search .= " (gr_subject like '%{$stx_q}%') "; $sfl = 'gr_subject'; break;
    }
    $sql_search .= " ) ";
}

$allowed_sst = ['gr_id','gr_subject','gr_admin','gr_order'];
if ($sst && !in_array($sst, $allowed_sst, true)) $sst = '';
if ($sod && !in_array(strtolower($sod), ['asc','desc'], true)) $sod = '';
$sql_order = $sst ? " order by {$sst} {$sod} " : " order by gr_order asc, gr_id asc ";

$row = sql_fetch(" select count(*) as cnt {$sql_common} {$sql_search} ");
$total_count = (int)$row['cnt'];
$rows        = (int)$config['cf_page_rows'];
$total_page  = max(1, (int)ceil($total_count / max(1, $rows)));
$from_record = ($page - 1) * $rows;
$result = sql_query(" select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ");

$h    = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$qstr = http_build_query(array_filter([
    'sfl'=>$sfl,'stx'=>$stx,'sst'=>$sst,'sod'=>$sod,'page'=>$page,
], static fn($v) => $v !== '' && $v !== null));

$sort_link = function (string $col) use ($sst, $sod, $sfl, $stx, $page, $h): string {
    $next = ($sst === $col && $sod === 'asc') ? 'desc' : 'asc';
    $qs = http_build_query(array_filter([
        'sst'=>$col,'sod'=>$next,'sfl'=>$sfl,'stx'=>$stx,'page'=>$page,
    ], static fn($v) => $v !== '' && $v !== null));
    $arrow = '';
    if ($sst === $col) {
        $arrow = $sod === 'desc'
            ? '<svg class="w-3 h-3 inline-block opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>'
            : '<svg class="w-3 h-3 inline-block opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>';
    }
    return '<a href="/admin/boardgroup_list?'.$h($qs).'" class="inline-flex items-center gap-0.5 hover:text-admin-primary-700 dark:hover:text-admin-primary-300">';
};

admin_layout_start('게시판 그룹', 'groups');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex flex-wrap items-center gap-3 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight">게시판 그룹</h2>
            <p class="text-xs text-slate-500 mt-0.5">총 <strong class="text-slate-700 dark:text-slate-300"><?php echo number_format($total_count) ?></strong>개 그룹 · 게시판은 그룹에 소속됩니다</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <form method="get" action="/admin/boardgroup_list" class="flex items-center gap-2">
                <select name="sfl" class="h-9 pl-3 pr-8 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    <option value="gr_subject" <?php echo $sfl==='gr_subject'?'selected':'' ?>>제목</option>
                    <option value="gr_id"      <?php echo $sfl==='gr_id'?'selected':'' ?>>그룹ID</option>
                    <option value="gr_admin"   <?php echo $sfl==='gr_admin'?'selected':'' ?>>그룹관리자</option>
                </select>
                <input type="text" name="stx" value="<?php echo $h($stx) ?>" placeholder="검색어"
                       class="h-9 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm w-44">
                <button type="submit" class="h-9 px-3 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium">검색</button>
                <?php if ($stx !== ''): ?>
                <a href="/admin/boardgroup_list" class="h-9 px-3 inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300">전체</a>
                <?php endif; ?>
            </form>
            <a href="/admin/boardgroup_form" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                그룹 추가
            </a>
        </div>
    </header>

    <form id="fboardgrouplist" action="/admin/boardgroup_list_update" method="post"
          class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
        <input type="hidden" name="sst"   value="<?php echo $h($sst) ?>">
        <input type="hidden" name="sod"   value="<?php echo $h($sod) ?>">
        <input type="hidden" name="sfl"   value="<?php echo $h($sfl) ?>">
        <input type="hidden" name="stx"   value="<?php echo $h($stx) ?>">
        <input type="hidden" name="page"  value="<?php echo (int)$page ?>">
        <input type="hidden" name="token" value="<?php echo get_admin_token() ?>">

        <div class="overflow-x-auto">
        <table class="min-w-full text-sm border-collapse">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="w-10 px-3 py-2.5 text-center"><input type="checkbox" id="chkall" class="rounded border-slate-300"></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('gr_id') ?>그룹ID</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap min-w-[18rem]"><?php echo $sort_link('gr_subject') ?>제목</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('gr_admin') ?>그룹관리자</a></th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap">게시판</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap">접근<br>사용</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap">접근<br>회원수</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap"><?php echo $sort_link('gr_order') ?>순서</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap">접속</th>
                    <th class="px-3 py-2.5 text-right whitespace-nowrap">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            $select_cls = 'h-9 pl-2.5 pr-8 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';
            $input_cls  = 'w-full h-9 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';
            $num_cls    = 'w-14 h-9 px-2 text-center rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';
            while ($row = sql_fetch_array($result)) {
                $bm_cnt = sql_fetch(" select count(*) as cnt from {$g5['group_member_table']} where gr_id = '".addslashes($row['gr_id'])."' ");
                $bd_cnt = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where gr_id = '".addslashes($row['gr_id'])."' ");
                ?>
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                    <td class="px-3 py-2 text-center">
                        <input type="hidden" name="group_id[<?php echo $i ?>]" value="<?php echo $h($row['gr_id']) ?>">
                        <input type="checkbox" name="chk[]" value="<?php echo $i ?>" class="rounded border-slate-300">
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap font-mono text-xs">
                        <a href="<?php echo G5_BBS_URL ?>/group.php?gr_id=<?php echo urlencode($row['gr_id']) ?>" class="text-admin-primary-700 dark:text-admin-primary-300 hover:underline" target="_blank"><?php echo $h($row['gr_id']) ?></a>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="gr_subject[<?php echo $i ?>]" value="<?php echo $h($row['gr_subject']) ?>" class="<?php echo $input_cls ?>" maxlength="255">
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <?php if ($is_admin === 'super'): ?>
                            <input type="text" name="gr_admin[<?php echo $i ?>]" value="<?php echo $h($row['gr_admin']) ?>" class="<?php echo $input_cls ?> w-32" maxlength="20">
                        <?php else: ?>
                            <input type="hidden" name="gr_admin[<?php echo $i ?>]" value="<?php echo $h($row['gr_admin']) ?>">
                            <span class="text-slate-700 dark:text-slate-300"><?php echo $h($row['gr_admin']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <a href="/admin/board_list?sfl=a.gr_id&amp;stx=<?php echo urlencode($row['gr_id']) ?>" class="text-admin-primary-700 dark:text-admin-primary-300 hover:underline"><?php echo number_format((int)$bd_cnt['cnt']) ?></a>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <input type="checkbox" name="gr_use_access[<?php echo $i ?>]" value="1" <?php echo $row['gr_use_access']?'checked':'' ?> class="rounded border-slate-300">
                    </td>
                    <td class="px-3 py-2 text-center">
                        <a href="/admin/boardgroupmember_list?gr_id=<?php echo urlencode($row['gr_id']) ?>" class="text-admin-primary-700 dark:text-admin-primary-300 hover:underline"><?php echo number_format((int)$bm_cnt['cnt']) ?></a>
                    </td>
                    <td class="px-3 py-2"><input type="text" name="gr_order[<?php echo $i ?>]" value="<?php echo (int)$row['gr_order'] ?>" class="<?php echo $num_cls ?>"></td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <select name="gr_device[<?php echo $i ?>]" class="<?php echo $select_cls ?>">
                            <option value="both"   <?php echo $row['gr_device']==='both'  ?'selected':'' ?>>모두</option>
                            <option value="pc"     <?php echo $row['gr_device']==='pc'    ?'selected':'' ?>>PC</option>
                            <option value="mobile" <?php echo $row['gr_device']==='mobile'?'selected':'' ?>>모바일</option>
                        </select>
                    </td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">
                        <a href="/admin/boardgroup_form?w=u&amp;gr_id=<?php echo urlencode($row['gr_id']) ?>" class="inline-flex items-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">수정</a>
                    </td>
                </tr>
                <?php
                $i++;
            }
            if ($i === 0): ?>
                <tr><td colspan="10" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">자료가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>

        <div class="flex flex-wrap items-center gap-2 px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
            <button type="submit" name="act_button" value="선택수정" class="h-9 px-3.5 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium" onclick="window.__pressed=this.value">선택 수정</button>
            <button type="submit" name="act_button" value="선택삭제" class="h-9 px-3.5 rounded-md bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium" onclick="window.__pressed=this.value">선택 삭제</button>
            <span class="ml-auto text-xs text-slate-500"><?php echo number_format($total_count) ?>개 중 <?php echo (int)$page ?>/<?php echo (int)$total_page ?>페이지</span>
        </div>
    </form>

    <p class="mt-4 text-xs text-slate-500">접근사용을 켜면 그룹 내 모든 게시판은 지정된 회원만 접근할 수 있습니다.</p>

</main>

<script>
(function () {
    var f = document.getElementById('fboardgrouplist');
    var ck = document.getElementById('chkall');
    if (ck && f) ck.addEventListener('change', function () {
        f.querySelectorAll('input[name="chk[]"]').forEach(function (i) { i.checked = ck.checked; });
    });
    if (f) f.addEventListener('submit', function (e) {
        if (!f.querySelector('input[name="chk[]"]:checked')) { e.preventDefault(); alert((window.__pressed || '실행') + ' 하실 항목을 하나 이상 선택하세요.'); return; }
        if (window.__pressed === '선택삭제' && !confirm('선택한 자료를 정말 삭제하시겠습니까?')) e.preventDefault();
    });
})();
</script>

<?php
admin_layout_end();
