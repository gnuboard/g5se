<?php
/*
 * /admin/boardgroupmember_form?mb_id=... — 특정 회원의 접근가능 그룹 관리.
 *   - 위쪽 폼: 그룹 select → 회원에게 그룹 추가 (insert)
 *   - 아래쪽 표: 회원이 속한 그룹들 + 선택 삭제
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

$sub_menu = '300200';
auth_check_menu($auth, $sub_menu, 'w');

$mb_id = isset($_GET['mb_id']) ? trim((string)$_GET['mb_id']) : '';
$mb = $mb_id ? get_member($mb_id) : null;

if (!$mb || empty($mb['mb_id'])) {
    admin_layout_start('회원 그룹 관리', 'groups');
    echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">존재하지 않는 회원입니다.</div></main>';
    admin_layout_end();
    exit;
}

// 회원이 속한 접근 그룹 목록
$sql = " select * from {$g5['group_member_table']} a, {$g5['group_table']} b
            where a.mb_id = '".addslashes($mb['mb_id'])."' and a.gr_id = b.gr_id ";
if ($is_admin !== 'super') {
    $sql .= " and b.gr_admin = '".addslashes($member['mb_id'])."' ";
}
$sql .= " order by a.gr_id desc ";
$result = sql_query($sql);

// 추가 가능 그룹 (gr_use_access=1)
$add_sql = " select * from {$g5['group_table']} where gr_use_access = 1 ";
if ($is_admin !== 'super') $add_sql .= " and gr_admin = '".addslashes($member['mb_id'])."' ";
$add_sql .= " order by gr_id ";
$add_result = sql_query($add_sql);

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// 같은 페이지에 form 이 2개 — get_admin_token() 는 호출 때마다 세션에 새 토큰을
// 저장하므로 그냥 두 번 부르면 첫 form 의 토큰이 무효. 한 번 발급해 양쪽 form
// 에서 같은 값을 주입.
$_admin_token = get_admin_token();

admin_layout_start($mb['mb_id'].' 의 접근가능 그룹', 'groups');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex items-center gap-3 mb-5">
        <a href="/admin/member_list" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="회원 목록">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </a>
        <div class="min-w-0">
            <h2 class="text-xl font-bold tracking-tight">접근가능 그룹</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                <span class="font-mono"><?php echo $h($mb['mb_id']) ?></span> ·
                <?php echo $h($mb['mb_name']) ?> · <?php echo $h($mb['mb_nick']) ?>
            </p>
        </div>
    </header>

    <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6 mb-4">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">그룹 추가</h3>
        <form action="/admin/boardgroupmember_update" method="post" class="flex flex-wrap items-center gap-2" id="fboardgroupmember_form">
            <input type="hidden" name="mb_id" value="<?php echo $h($mb['mb_id']) ?>">
            <input type="hidden" name="token" value="<?php echo $_admin_token ?>">
            <select name="gr_id" required class="h-10 pl-3 pr-8 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm flex-1 min-w-64">
                <option value="">접근가능 그룹을 선택하세요</option>
                <?php while ($row = sql_fetch_array($add_result)): ?>
                    <option value="<?php echo $h($row['gr_id']) ?>"><?php echo $h($row['gr_subject']) ?> <span class="font-mono">(<?php echo $h($row['gr_id']) ?>)</span></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="h-10 px-5 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-semibold">추가</button>
        </form>
    </section>

    <form id="fboardgroupmember" action="/admin/boardgroupmember_update" method="post"
          class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
        <input type="hidden" name="w"     value="d">
        <input type="hidden" name="mb_id" value="<?php echo $h($mb['mb_id']) ?>">
        <input type="hidden" name="token" value="<?php echo $_admin_token ?>">

        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="w-10 px-3 py-2.5 text-center"><input type="checkbox" id="chkall" class="rounded border-slate-300"></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap">그룹ID</th>
                    <th class="px-3 py-2.5 text-left">그룹명</th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap">처리일시</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            while ($row = sql_fetch_array($result)):
                ?>
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                    <td class="px-3 py-2 text-center"><input type="checkbox" name="chk[]" value="<?php echo (int)$row['gm_id'] ?>" class="rounded border-slate-300"></td>
                    <td class="px-3 py-2 whitespace-nowrap font-mono text-xs">
                        <a href="<?php echo G5_BBS_URL ?>/group.php?gr_id=<?php echo urlencode($row['gr_id']) ?>" target="_blank" class="text-admin-primary-700 dark:text-admin-primary-300 hover:underline"><?php echo $h($row['gr_id']) ?></a>
                    </td>
                    <td class="px-3 py-2"><?php echo $h($row['gr_subject']) ?></td>
                    <td class="px-3 py-2 whitespace-nowrap text-slate-500 text-xs"><?php echo $h($row['gm_datetime']) ?></td>
                </tr>
                <?php
                $i++;
            endwhile;
            if ($i === 0): ?>
                <tr><td colspan="4" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">접근가능한 그룹이 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>

        <div class="flex items-center gap-2 px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
            <button type="submit" class="h-9 px-3.5 rounded-md bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium">선택 삭제</button>
        </div>
    </form>

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
        if (!confirm('선택한 그룹에서 회원을 제거할까요?')) e.preventDefault();
    });
})();
</script>

<?php
admin_layout_end();
