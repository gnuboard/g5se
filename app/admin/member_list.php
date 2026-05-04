<?php
// /admin/member_list — 모던 회원 목록
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';

// adm/admin.lib.php 는 require 시점에 alert('로그인 하십시오.', /login.php?url=/adm/) 로
// 자체 가드를 실행하므로, 우리 admin_require_login() 을 그 이전에 호출해야 한다.
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';   // auth_check_menu, $auth 빌드

// gnuboard 의 sub_menu 권한 체크 — 200100 = 회원 관리
$sub_menu = '200100';
auth_check_menu($auth, $sub_menu, 'r');

global $g5, $config, $member, $is_admin;

// ──────────────────────────────────────────────
// 검색 / 필터 / 정렬 파라미터
// ──────────────────────────────────────────────
$sfl = isset($_GET['sfl']) ? trim((string)$_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$sst = isset($_GET['sst']) ? trim((string)$_GET['sst']) : '';
$sod = isset($_GET['sod']) ? trim((string)$_GET['sod']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$status = isset($_GET['status']) ? $_GET['status'] : '';   // '', 'normal', 'leave', 'intercept'

// $sfl 화이트리스트
$allowed_sfl = ['mb_id','mb_nick','mb_name','mb_level','mb_email','mb_hp','mb_point','mb_datetime','mb_ip','mb_recommend'];
if (!in_array($sfl, $allowed_sfl, true)) $sfl = 'mb_id';

// 정렬
$allowed_sst = ['mb_id','mb_nick','mb_level','mb_point','mb_datetime','mb_today_login'];
if (!in_array($sst, $allowed_sst, true)) $sst = 'mb_datetime';
$sod = ($sod === 'asc') ? 'asc' : 'desc';

// ──────────────────────────────────────────────
// SQL 빌드
// ──────────────────────────────────────────────
$sql_common = " from {$g5['member_table']} ";
$sql_search = " where (1) ";
if ($stx !== '') {
    $stx_esc = sql_escape_string($stx);
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " and mb_point >= '$stx_esc' ";
            break;
        case 'mb_level':
            $sql_search .= " and mb_level = '$stx_esc' ";
            break;
        case 'mb_hp':
            $sql_search .= " and mb_hp like '%$stx_esc' ";
            break;
        default:
            $sql_search .= " and $sfl like '$stx_esc%' ";
            break;
    }
}
// super 가 아니면 자기 레벨 이하만
if ($is_admin !== 'super') {
    $lv = (int)$member['mb_level'];
    $sql_search .= " and mb_level <= $lv ";
}
// 상태 필터
if ($status === 'normal')   $sql_search .= " and mb_leave_date = '' and mb_intercept_date = '' ";
if ($status === 'leave')    $sql_search .= " and mb_leave_date <> '' ";
if ($status === 'intercept')$sql_search .= " and mb_intercept_date <> '' ";

// 카운트
$total_count   = (int)sql_fetch(" select count(*) as c $sql_common $sql_search ")['c'];
$normal_count  = (int)sql_fetch(" select count(*) as c $sql_common where mb_leave_date = '' and mb_intercept_date = '' ")['c'];
$leave_count   = (int)sql_fetch(" select count(*) as c $sql_common where mb_leave_date <> '' ")['c'];
$intercept_count = (int)sql_fetch(" select count(*) as c $sql_common where mb_intercept_date <> '' ")['c'];

$rows = (int)$config['cf_page_rows'] ?: 20;
$total_page = max(1, (int)ceil($total_count / $rows));
if ($page > $total_page) $page = $total_page;
$from = ($page - 1) * $rows;

$sql = " select * $sql_common $sql_search order by $sst $sod limit $from, $rows ";
$result = sql_query($sql);
$list = [];
while ($row = sql_fetch_array($result)) $list[] = $row;

// 정렬 토글 헬퍼
$sort_qs = function ($field) use ($sfl, $stx, $status, $sst, $sod) {
    $next = ($sst === $field && $sod === 'desc') ? 'asc' : 'desc';
    return '/admin/member_list?'.http_build_query(array_filter([
        'sfl'=>$sfl, 'stx'=>$stx, 'status'=>$status, 'sst'=>$field, 'sod'=>$next,
    ], static fn($v) => $v !== ''));
};
$sort_arrow = function ($field) use ($sst, $sod) {
    if ($sst !== $field) return '';
    return $sod === 'asc' ? ' ▲' : ' ▼';
};

// 필터 chip URL
$chip_url = function ($s) use ($sfl, $stx, $sst, $sod) {
    return '/admin/member_list?'.http_build_query(array_filter([
        'sfl'=>$sfl, 'stx'=>$stx, 'status'=>$s, 'sst'=>$sst, 'sod'=>$sod,
    ], static fn($v) => $v !== ''));
};

admin_layout_start('회원 관리', 'members');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <!-- 상단 카운트 + 액션 -->
    <header class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-lg font-semibold mr-2">총 <span class="text-admin-primary-600 dark:text-admin-primary-400 tabular-nums"><?php echo number_format($total_count) ?></span>명</h2>
            <a href="<?php echo $chip_url('') ?>"          class="px-3 py-1 rounded-full text-xs font-medium border <?php echo $status==='' ? 'bg-admin-primary-600 text-white border-admin-primary-600' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'?>">전체</a>
            <a href="<?php echo $chip_url('normal') ?>"    class="px-3 py-1 rounded-full text-xs font-medium border <?php echo $status==='normal' ? 'bg-admin-primary-600 text-white border-admin-primary-600' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'?>">정상 <span class="ml-1 tabular-nums opacity-70"><?php echo number_format($normal_count) ?></span></a>
            <a href="<?php echo $chip_url('leave') ?>"     class="px-3 py-1 rounded-full text-xs font-medium border <?php echo $status==='leave' ? 'bg-amber-600 text-white border-amber-600' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'?>">탈퇴 <span class="ml-1 tabular-nums opacity-70"><?php echo number_format($leave_count) ?></span></a>
            <a href="<?php echo $chip_url('intercept') ?>" class="px-3 py-1 rounded-full text-xs font-medium border <?php echo $status==='intercept' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'?>">차단 <span class="ml-1 tabular-nums opacity-70"><?php echo number_format($intercept_count) ?></span></a>
        </div>
        <div class="flex items-center gap-2">
            <a href="/admin/member_form" class="inline-flex items-center gap-1 px-3 h-9 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                회원 추가
            </a>
        </div>
    </header>

    <!-- 검색 -->
    <form method="get" action="/admin/member_list" class="flex flex-wrap items-center gap-2 mb-4 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <?php if ($status) { ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($status) ?>"><?php } ?>
        <select name="sfl" class="h-9 px-2 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
            <?php
            $sfl_labels = ['mb_id'=>'아이디','mb_nick'=>'닉네임','mb_name'=>'이름','mb_email'=>'이메일','mb_hp'=>'휴대폰','mb_level'=>'레벨','mb_point'=>'포인트 ≥','mb_ip'=>'IP','mb_recommend'=>'추천인'];
            foreach ($sfl_labels as $v => $lbl) {
                $sel = $sfl === $v ? ' selected' : '';
                echo '<option value="'.$v.'"'.$sel.'>'.$lbl.'</option>';
            }
            ?>
        </select>
        <input type="text" name="stx" value="<?php echo htmlspecialchars($stx) ?>" placeholder="검색어" class="h-9 px-3 flex-1 min-w-[180px] rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
        <button type="submit" class="h-9 px-4 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium">검색</button>
        <?php if ($stx !== '') { ?>
        <a href="<?php echo $chip_url($status) ?>" class="h-9 inline-flex items-center px-3 rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">초기화</a>
        <?php } ?>
    </form>

    <!-- 테이블 -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-3 py-3 text-left font-semibold"><a href="<?php echo $sort_qs('mb_id') ?>" class="hover:text-slate-900 dark:hover:text-slate-100">아이디<?php echo $sort_arrow('mb_id') ?></a></th>
                        <th class="px-3 py-3 text-left font-semibold"><a href="<?php echo $sort_qs('mb_nick') ?>" class="hover:text-slate-900 dark:hover:text-slate-100">닉네임<?php echo $sort_arrow('mb_nick') ?></a></th>
                        <th class="px-3 py-3 text-center font-semibold"><a href="<?php echo $sort_qs('mb_level') ?>" class="hover:text-slate-900 dark:hover:text-slate-100">레벨<?php echo $sort_arrow('mb_level') ?></a></th>
                        <th class="px-3 py-3 text-right font-semibold"><a href="<?php echo $sort_qs('mb_point') ?>" class="hover:text-slate-900 dark:hover:text-slate-100">포인트<?php echo $sort_arrow('mb_point') ?></a></th>
                        <th class="px-3 py-3 text-left font-semibold hidden md:table-cell">이메일</th>
                        <th class="px-3 py-3 text-left font-semibold hidden lg:table-cell"><a href="<?php echo $sort_qs('mb_datetime') ?>" class="hover:text-slate-900 dark:hover:text-slate-100">가입일<?php echo $sort_arrow('mb_datetime') ?></a></th>
                        <th class="px-3 py-3 text-center font-semibold">상태</th>
                        <th class="px-3 py-3 text-right font-semibold w-24">관리</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php foreach ($list as $row) {
                        $is_leave     = !empty($row['mb_leave_date']);
                        $is_intercept = !empty($row['mb_intercept_date']);
                        $edit_url = '/admin/member_form?w=u&mb_id='.urlencode($row['mb_id']);
                    ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                        <td class="px-3 py-3">
                            <a href="<?php echo $edit_url ?>" class="font-medium text-slate-900 dark:text-slate-100 hover:text-admin-primary-600"><?php echo htmlspecialchars($row['mb_id']) ?></a>
                            <?php if (!empty($row['mb_name'])) { ?><div class="text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars($row['mb_name']) ?></div><?php } ?>
                        </td>
                        <td class="px-3 py-3 text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($row['mb_nick']) ?></td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full text-xs font-bold bg-admin-primary-100 dark:bg-admin-primary-900 text-admin-primary-700 dark:text-admin-primary-200"><?php echo (int)$row['mb_level'] ?></span>
                        </td>
                        <td class="px-3 py-3 text-right tabular-nums"><?php echo number_format((int)$row['mb_point']) ?></td>
                        <td class="px-3 py-3 hidden md:table-cell text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($row['mb_email']) ?></td>
                        <td class="px-3 py-3 hidden lg:table-cell text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap"><?php echo substr($row['mb_datetime'], 0, 10) ?></td>
                        <td class="px-3 py-3 text-center">
                            <?php if ($is_leave) { ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">탈퇴</span>
                            <?php } elseif ($is_intercept) { ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-200">차단</span>
                            <?php } else { ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200">정상</span>
                            <?php } ?>
                        </td>
                        <td class="px-3 py-3 text-right">
                            <a href="<?php echo $edit_url ?>" class="inline-flex items-center px-2.5 h-8 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">수정</a>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (!$list) { ?>
                    <tr>
                        <td colspan="8" class="px-3 py-16 text-center">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-2 text-slate-300 dark:text-slate-600"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <p class="text-sm text-slate-400">조건에 맞는 회원이 없습니다.</p>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($total_page > 1) {
        $base = '/admin/member_list?'.http_build_query(array_filter([
            'sfl'=>$sfl, 'stx'=>$stx, 'status'=>$status, 'sst'=>$sst, 'sod'=>$sod,
        ], static fn($v) => $v !== ''));
        $sep = strpos($base, '?') !== false ? '&' : '?';
        $start = max(1, $page - 4);
        $end = min($total_page, $start + 9);
        $start = max(1, $end - 9);
    ?>
    <nav class="mt-4 flex items-center justify-center gap-1">
        <?php if ($page > 1) { ?>
        <a href="<?php echo $base.$sep.'page='.($page-1) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm">‹</a>
        <?php } ?>
        <?php for ($p = $start; $p <= $end; $p++) { ?>
        <a href="<?php echo $base.$sep.'page='.$p ?>" class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-md border text-sm <?php echo $p === $page ? 'bg-admin-primary-600 text-white border-admin-primary-600' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' ?>"><?php echo $p ?></a>
        <?php } ?>
        <?php if ($page < $total_page) { ?>
        <a href="<?php echo $base.$sep.'page='.($page+1) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm">›</a>
        <?php } ?>
        <span class="ml-3 text-xs text-slate-500">페이지 <?php echo $page ?> / <?php echo $total_page ?></span>
    </nav>
    <?php } ?>

</main>
<?php
admin_layout_end();
