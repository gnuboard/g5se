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
$page = min($page, $total_page);
$from = ($page - 1) * $rows;

$result = sql_pdo_query(" select po.*, mb.mb_name, mb.mb_nick, mb.mb_email, mb.mb_homepage, mb.mb_point
    {$sql_common} LEFT JOIN {$g5['member_table']} mb ON po.mb_id = mb.mb_id
    {$sql_search} {$sql_order} limit {$from}, {$rows} ", $params);

$mb = ($sfl === 'mb_id' && $stx !== '') ? get_member($stx) : [];
$total_sum = sql_pdo_fetch(" select sum(po_point) as s from {$g5['point_table']} ")['s'] ?? 0;

// 엑셀 다운로드 구간 계산: 목록 검색 조건 + 별도 기간 조건을 적용한다.
$export_prepare = isset($_GET['export_prepare']) && $_GET['export_prepare'] === '1';
$export_fr_date = isset($_GET['export_fr_date']) ? (string)$_GET['export_fr_date'] : date('Y-m-d', strtotime('-1 month'));
$export_to_date = isset($_GET['export_to_date']) ? (string)$_GET['export_to_date'] : G5_TIME_YMD;
$export_period = isset($_GET['export_period']) ? (string)$_GET['export_period'] : '';
$export_date_pattern = '/^\d{4}-(0[1-9]|1[0-2])-([0-2]\d|3[01])$/';
if (!preg_match($export_date_pattern, $export_fr_date)) $export_fr_date = date('Y-m-d', strtotime('-1 month'));
if (!preg_match($export_date_pattern, $export_to_date)) $export_to_date = G5_TIME_YMD;
if ($export_fr_date > $export_to_date) {
    $export_temp_date = $export_fr_date;
    $export_fr_date = $export_to_date;
    $export_to_date = $export_temp_date;
}

$export_count = 0;
$export_snapshot = 0;
$export_chunk_size = 50000;
if ($export_prepare) {
    $export_sql_search = $sql_search." and po_datetime between ? and ? ";
    $export_params = array_merge($params, array($export_fr_date.' 00:00:00', $export_to_date.' 23:59:59'));
    $export_row = sql_pdo_fetch(
        " select count(*) as cnt, coalesce(max(po_id), 0) as max_po_id {$sql_common} {$export_sql_search} ",
        $export_params
    );
    $export_count = (int)($export_row['cnt'] ?? 0);
    $export_snapshot = (int)($export_row['max_po_id'] ?? 0);
}

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

<main class="point-list-page flex-1 p-4 sm:p-6 lg:p-8 w-full">

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
            <form method="get" action="/admin/point_list" class="point-search-form flex items-center gap-2">
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

    <form id="fpointlist" action="/admin/point_list_delete" method="post" data-floating-actions="off"
          class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden mb-6">
        <input type="hidden" name="token" value="<?php echo get_admin_token() ?>">
        <input type="hidden" name="sst"  value="<?php echo $h($sst) ?>">
        <input type="hidden" name="sod"  value="<?php echo $h($sod) ?>">
        <input type="hidden" name="sfl"  value="<?php echo $h($sfl) ?>">
        <input type="hidden" name="stx"  value="<?php echo $h($stx) ?>">
        <input type="hidden" name="page" value="<?php echo (int)$page ?>">

        <div class="point-list-scroll overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="point-col-check w-10 px-3 py-2.5 text-center"><input type="checkbox" id="chkall" class="rounded border-slate-300"></th>
                    <th class="point-col-member px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('mb_id') ?>회원아이디</a></th>
                    <th class="point-col-name px-3 py-2.5 text-left whitespace-nowrap">이름</th>
                    <th class="point-col-nick px-3 py-2.5 text-left whitespace-nowrap">닉네임</th>
                    <th class="point-col-content px-3 py-2.5 text-left"><?php echo $sort_link('po_content') ?>내용</a></th>
                    <th class="point-col-amount px-3 py-2.5 text-right whitespace-nowrap"><?php echo $sort_link('po_point') ?>포인트</a></th>
                    <th class="point-col-date px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('po_datetime') ?>일시</a></th>
                    <th class="point-col-expire px-3 py-2.5 text-left whitespace-nowrap">만료일</th>
                    <th class="point-col-total px-3 py-2.5 text-right whitespace-nowrap">합계</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            while ($row = sql_pdo_fetch_array($result)):
                $linkable = !preg_match('/^@/', $row['po_rel_table']) && $row['po_rel_table'];
                $is_expired = (int)$row['po_expired'] === 1;
                ?>
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                    <td class="point-col-check px-3 py-2 text-center">
                        <input type="hidden" name="mb_id[<?php echo $i ?>]" value="<?php echo $h($row['mb_id']) ?>">
                        <input type="hidden" name="po_id[<?php echo $i ?>]" value="<?php echo (int)$row['po_id'] ?>">
                        <input type="checkbox" name="chk[]" value="<?php echo $i ?>" class="rounded border-slate-300">
                    </td>
                    <td class="point-col-member px-3 py-2 whitespace-nowrap font-mono text-xs">
                        <a href="/admin/point_list?sfl=mb_id&amp;stx=<?php echo urlencode($row['mb_id']) ?>" class="text-admin-primary-700 dark:text-admin-primary-300 hover:underline"><?php echo $h($row['mb_id']) ?></a>
                    </td>
                    <td class="point-col-name px-3 py-2 whitespace-nowrap"><?php echo $h($row['mb_name']) ?></td>
                    <td class="point-col-nick px-3 py-2 whitespace-nowrap"><?php echo $h($row['mb_nick']) ?></td>
                    <td class="point-col-content px-3 py-2">
                        <?php if ($linkable): ?>
                            <a href="<?php echo $h(get_pretty_url($row['po_rel_table'], $row['po_rel_id'])) ?>" target="_blank" class="hover:underline"><?php echo $h($row['po_content']) ?></a>
                        <?php else: ?>
                            <?php echo $h($row['po_content']) ?>
                        <?php endif; ?>
                    </td>
                    <td class="point-col-amount px-3 py-2 text-right whitespace-nowrap font-mono <?php echo (int)$row['po_point'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' ?>"><?php echo number_format((int)$row['po_point']) ?></td>
                    <td class="point-col-date px-3 py-2 whitespace-nowrap text-slate-500 text-xs"><?php echo $h($row['po_datetime']) ?></td>
                    <td class="point-col-expire px-3 py-2 whitespace-nowrap text-xs <?php echo $is_expired ? 'text-rose-500 line-through' : 'text-slate-500' ?>">
                        <?php if ($is_expired): ?>
                            만료 <?php echo $h(substr(str_replace('-', '', $row['po_expire_date']), 2)) ?>
                        <?php else: ?>
                            <?php echo $row['po_expire_date'] === '9999-12-31' ? '—' : $h($row['po_expire_date']) ?>
                        <?php endif; ?>
                    </td>
                    <td class="point-col-total px-3 py-2 text-right whitespace-nowrap font-mono text-slate-700 dark:text-slate-300"><?php echo number_format((int)$row['po_mb_point']) ?></td>
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

        <div class="point-list-footer flex flex-wrap items-center gap-2 px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
            <button type="submit" name="act_button" value="선택삭제" class="h-9 px-3.5 rounded-md bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium">선택 삭제</button>
            <span class="ml-auto text-xs text-slate-500"><?php echo number_format($total_count) ?>건 중 <?php echo (int)$page ?>/<?php echo (int)$total_page ?>페이지</span>
        </div>
    </form>

    <?php if ($total_page > 1):
        $pgCls = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
        $pgActive = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md bg-admin-primary-600 text-white font-semibold';
        $pgUrl = function ($p) use ($sfl, $stx, $sst, $sod) {
            $params = array_filter(['sfl'=>$sfl,'stx'=>$stx,'sst'=>$sst,'sod'=>$sod,'page'=>$p], static fn($v) => $v !== '' && $v !== null);
            return '/admin/point_list?'.http_build_query($params);
        };
        $pgInputParams = array_filter(['sfl'=>$sfl, 'stx'=>$stx, 'sst'=>$sst, 'sod'=>$sod], static fn($v) => $v !== '' && $v !== null);
        $pgInputQuery = http_build_query($pgInputParams);
        $pgInputUrl = '/admin/point_list?'.($pgInputQuery !== '' ? $pgInputQuery.'&' : '').'page=';
    ?>
    <nav class="point-pagination point-desktop-pagination mb-6 flex items-center gap-1 justify-center text-sm">
        <?php
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
    <nav class="point-mobile-pagination" aria-label="포인트 내역 페이지 이동">
        <?php if ($page > 1): ?>
            <a href="<?php echo $h($pgUrl(1)) ?>">처음</a>
            <a href="<?php echo $h($pgUrl($page - 1)) ?>">이전</a>
        <?php else: ?>
            <span class="is-disabled">처음</span>
            <span class="is-disabled">이전</span>
        <?php endif; ?>
        <label class="current-page">
            <input type="number"
                   class="current-page-input rounded"
                   value="<?php echo (int)$page ?>"
                   min="1"
                   max="<?php echo (int)$total_page ?>"
                   inputmode="numeric"
                   data-current-page="<?php echo (int)$page ?>"
                   data-page-url="<?php echo $h($pgInputUrl) ?>"
                   aria-label="이동할 페이지">
        </label>
        <?php if ($page < $total_page): ?>
            <a href="<?php echo $h($pgUrl($page + 1)) ?>">다음</a>
            <a href="<?php echo $h($pgUrl($total_page)) ?>">맨끝</a>
        <?php else: ?>
            <span class="is-disabled">다음</span>
            <span class="is-disabled">맨끝</span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <section class="point-export-section rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5 mb-6">
        <div class="flex flex-wrap items-end gap-3">
            <form method="get" action="/admin/point_list" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="export_prepare" value="1">
                <input type="hidden" name="sfl" value="<?php echo $h($sfl) ?>">
                <input type="hidden" name="stx" value="<?php echo $h($stx) ?>">
                <input type="hidden" name="sst" value="<?php echo $h($sst) ?>">
                <input type="hidden" name="sod" value="<?php echo $h($sod) ?>">
                <div>
                    <label for="export_fr_date" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">엑셀 시작일</label>
                    <input type="text" name="export_fr_date" id="export_fr_date" required value="<?php echo $h($export_fr_date) ?>" size="11" maxlength="10"
                           class="frm_input h-9 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label for="export_to_date" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">엑셀 종료일</label>
                    <input type="text" name="export_to_date" id="export_to_date" required value="<?php echo $h($export_to_date) ?>" size="11" maxlength="10"
                           class="frm_input h-9 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label for="export_period" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">빠른 기간 선택</label>
                    <select name="export_period" id="export_period"
                            class="h-9 pl-3 pr-8 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="">직접 입력</option>
                        <?php
                        $export_period_options = array(
                            'today' => '오늘', 'yesterday' => '어제',
                            'this_week' => '이번주', 'last_week' => '지난주', 'last_7_days' => '지난 7일',
                            'this_month' => '이번달', 'last_month' => '지난달', 'last_30_days' => '지난 30일',
                            'this_year' => '올해', 'last_year' => '작년',
                            'q1' => '1분기', 'q2' => '2분기', 'q3' => '3분기', 'q4' => '4분기',
                            'first_half' => '상반기', 'second_half' => '하반기',
                        );
                        foreach ($export_period_options as $export_period_value => $export_period_label) {
                            echo '<option value="'.$h($export_period_value).'"'.($export_period === $export_period_value ? ' selected' : '').'>'.$h($export_period_label).'</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="h-9 px-3.5 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium">다운로드 구간 조회</button>
            </form>
            <p class="text-xs text-slate-500 pb-2">파일당 최대 50,000건 · 현재 검색 조건 적용</p>
        </div>

        <?php if ($export_prepare): ?>
        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800">
            <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">
                조회 결과 <strong><?php echo number_format($export_count) ?></strong>건
                (<?php echo $h($export_fr_date) ?> ~ <?php echo $h($export_to_date) ?>)
            </p>
            <?php if ($export_count > 0): ?>
            <div class="flex flex-wrap gap-2">
                <?php
                $export_chunks = (int)ceil($export_count / $export_chunk_size);
                for ($export_chunk = 1; $export_chunk <= $export_chunks; $export_chunk++):
                    $export_start = (($export_chunk - 1) * $export_chunk_size) + 1;
                    $export_end = min($export_chunk * $export_chunk_size, $export_count);
                    $export_query = http_build_query(array(
                        'chunk' => $export_chunk,
                        'snapshot' => $export_snapshot,
                        'fr_date' => $export_fr_date,
                        'to_date' => $export_to_date,
                        'sfl' => $sfl,
                        'stx' => $stx,
                        'sst' => $sst,
                        'sod' => $sod,
                    ));
                ?>
                <a href="/admin/point_list_excel_download?<?php echo $h($export_query) ?>"
                   class="inline-flex items-center h-9 px-3.5 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">
                    <?php echo number_format($export_start) ?>~<?php echo number_format($export_end) ?>건 엑셀
                </a>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <p class="text-sm text-slate-500">해당 기간에 다운로드할 포인트 내역이 없습니다.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>

    <section class="point-adjust-section rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
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
$(function () {
    $('#export_fr_date, #export_to_date').datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        showButtonPanel: true,
        yearRange: 'c-99:c+99',
        maxDate: '+0d'
    });

    $('#export_period').on('change', function () {
        var period = this.value;
        if (!period) return;

        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var year = today.getFullYear();
        var month = today.getMonth();
        var start = new Date(today);
        var end = new Date(today);

        function addDays(date, days) {
            var result = new Date(date);
            result.setDate(result.getDate() + days);
            return result;
        }
        function mondayOf(date) {
            var result = new Date(date);
            var day = result.getDay();
            result.setDate(result.getDate() - (day === 0 ? 6 : day - 1));
            return result;
        }
        function formatDate(date) {
            var y = date.getFullYear();
            var m = String(date.getMonth() + 1).padStart(2, '0');
            var d = String(date.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + d;
        }

        switch (period) {
            case 'yesterday':
                start = end = addDays(today, -1);
                break;
            case 'this_week':
                start = mondayOf(today);
                end = today;
                break;
            case 'last_week':
                end = addDays(mondayOf(today), -1);
                start = addDays(end, -6);
                break;
            case 'last_7_days':
                start = addDays(today, -6);
                break;
            case 'this_month':
                start = new Date(year, month, 1);
                break;
            case 'last_month':
                start = new Date(year, month - 1, 1);
                end = new Date(year, month, 0);
                break;
            case 'last_30_days':
                start = addDays(today, -29);
                break;
            case 'this_year':
                start = new Date(year, 0, 1);
                break;
            case 'last_year':
                start = new Date(year - 1, 0, 1);
                end = new Date(year - 1, 11, 31);
                break;
            case 'q1':
                start = new Date(year, 0, 1); end = new Date(year, 2, 31);
                break;
            case 'q2':
                start = new Date(year, 3, 1); end = new Date(year, 5, 30);
                break;
            case 'q3':
                start = new Date(year, 6, 1); end = new Date(year, 8, 30);
                break;
            case 'q4':
                start = new Date(year, 9, 1); end = new Date(year, 11, 31);
                break;
            case 'first_half':
                start = new Date(year, 0, 1); end = new Date(year, 5, 30);
                break;
            case 'second_half':
                start = new Date(year, 6, 1); end = new Date(year, 11, 31);
                break;
            case 'today':
            default:
                break;
        }

        $('#export_fr_date').val(formatDate(start));
        $('#export_to_date').val(formatDate(end));
    });
});

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

document.querySelectorAll('.point-mobile-pagination .current-page input').forEach(function (input) {
    function moveToPage() {
        var current = Number(input.dataset.currentPage);
        var target = Number(input.value);
        var max = Number(input.max);

        if (!Number.isInteger(target) || target < 1 || target > max) {
            input.value = current;
            input.classList.add('is-invalid');
            window.setTimeout(function () { input.classList.remove('is-invalid'); }, 700);
            return;
        }

        if (target !== current) {
            window.location.href = input.dataset.pageUrl + target;
        }
    }

    input.addEventListener('change', moveToPage);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            moveToPage();
        }
    });
});
</script>

<?php
admin_layout_end();
