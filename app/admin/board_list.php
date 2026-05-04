<?php
/*
 * /admin/board_list — 게시판 목록 (벌크 편집).
 *
 * gnuboard adm/board_list.php 의 inline-edit 테이블 (skin/제목/포인트/사용여부 등) 을
 * 그대로 유지하되 Tailwind 카드/그리드 UI 로 재포장.
 * 폼 action 은 /admin/board_list_update — chdir+require 로 gnuboard 의 update 호출.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

$sub_menu = '300100';   // 게시판 관리 (g5_auth 의 권한 코드)
auth_check_menu($auth, $sub_menu, 'r');

// gnuboard 의 정렬/검색/페이징 입력
$sfl  = isset($_GET['sfl']) ? (string)$_GET['sfl'] : '';
$stx  = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$sst  = isset($_GET['sst']) ? (string)$_GET['sst'] : '';
$sod  = isset($_GET['sod']) ? (string)$_GET['sod'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$sql_common = " from {$g5['board_table']} a ";
$sql_search = " where (1) ";

if ($is_admin !== 'super') {
    $sql_common .= " , {$g5['group_table']} b ";
    $sql_search .= " and (a.gr_id = b.gr_id and b.gr_admin = '".addslashes($member['mb_id'])."') ";
}

if ($stx !== '') {
    $stx_q = addslashes($stx);
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'bo_table':  $sql_search .= " (bo_table like '{$stx_q}%') "; break;
        case 'a.gr_id':   $sql_search .= " (a.gr_id = '{$stx_q}') "; break;
        case 'bo_subject':default: $sql_search .= " (bo_subject like '%{$stx_q}%') "; $sfl = 'bo_subject'; break;
    }
    $sql_search .= " ) ";
}

$allowed_sst = ['a.gr_id','bo_table','bo_skin','bo_mobile_skin','bo_subject','bo_use_sns','bo_use_search','bo_order','a.gr_id, a.bo_table'];
if (!$sst || !in_array($sst, $allowed_sst, true)) {
    $sst = 'a.gr_id, a.bo_table';
    $sod = 'asc';
}
if ($sod && !in_array(strtolower($sod), ['asc','desc'], true)) $sod = '';
$sql_order = " order by {$sst} {$sod} ";

$row = sql_fetch(" select count(*) as cnt {$sql_common} {$sql_search} ");
$total_count = (int)$row['cnt'];

$rows        = (int)$config['cf_page_rows'];
$total_page  = max(1, (int)ceil($total_count / max(1, $rows)));
$from_record = ($page - 1) * $rows;

$result = sql_query(" select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ");

// 헬퍼
$h    = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$qstr = http_build_query(array_filter([
    'sfl'=>$sfl, 'stx'=>$stx, 'sst'=>$sst, 'sod'=>$sod, 'page'=>$page,
], static fn($v) => $v !== '' && $v !== null));
$base_qs = $qstr !== '' ? '?'.$qstr : '';

// 정렬 링크 (gnuboard subject_sort_link 와 동일한 로직, 클린 URL 출력)
$sort_link = function (string $col, string $default = 'asc') use ($sst, $sod, $sfl, $stx, $page, $h): string {
    $next_sod = $default;
    if ($sst === $col) $next_sod = ($sod === 'asc') ? 'desc' : 'asc';
    $qs = http_build_query(array_filter([
        'sst'=>$col, 'sod'=>$next_sod, 'sfl'=>$sfl, 'stx'=>$stx, 'page'=>$page,
    ], static fn($v) => $v !== '' && $v !== null));
    $arrow = '';
    if ($sst === $col) {
        $arrow = $sod === 'desc'
            ? '<svg class="w-3 h-3 inline-block opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>'
            : '<svg class="w-3 h-3 inline-block opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>';
    }
    return '<a href="/admin/board_list?'.$h($qs).'" class="inline-flex items-center gap-0.5 hover:text-admin-primary-700 dark:hover:text-admin-primary-300">';
};

admin_layout_start('게시판 관리', 'bbs_board');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex flex-wrap items-center gap-3 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight">게시판 관리</h2>
            <p class="text-xs text-slate-500 mt-0.5">총 <strong class="text-slate-700 dark:text-slate-300"><?php echo number_format($total_count) ?></strong>개 게시판 · 정렬·스킨·포인트 등을 일괄 수정</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <form method="get" action="/admin/board_list" class="flex items-center gap-2">
                <select name="sfl" class="h-9 pl-3 pr-8 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    <option value="bo_subject" <?php echo $sfl==='bo_subject'?'selected':'' ?>>제목</option>
                    <option value="bo_table"   <?php echo $sfl==='bo_table'?'selected':'' ?>>TABLE</option>
                    <option value="a.gr_id"    <?php echo $sfl==='a.gr_id'?'selected':'' ?>>그룹ID</option>
                </select>
                <input type="text" name="stx" value="<?php echo $h($stx) ?>" placeholder="검색어"
                       class="h-9 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm w-44">
                <button type="submit" class="h-9 px-3 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium">검색</button>
                <?php if ($stx !== ''): ?>
                <a href="/admin/board_list" class="h-9 px-3 inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-600 dark:text-slate-300">전체</a>
                <?php endif; ?>
            </form>
            <?php if ($is_admin === 'super'): ?>
            <a href="/admin/board_form" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                게시판 추가
            </a>
            <?php endif; ?>
        </div>
    </header>

    <form name="fboardlist" id="fboardlist" action="/admin/board_list_update" method="post"
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
                    <th class="w-10 px-3 py-2.5 text-center">
                        <input type="checkbox" id="chkall" class="rounded border-slate-300">
                    </th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('a.gr_id') ?>그룹</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('bo_table') ?>TABLE</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('bo_skin','desc') ?>스킨</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap"><?php echo $sort_link('bo_mobile_skin','desc') ?>모바일</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap min-w-[14rem]"><?php echo $sort_link('bo_subject') ?>제목</a></th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap" title="읽기 포인트">읽</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap" title="쓰기 포인트">쓰</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap" title="댓글 포인트">댓</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap" title="다운 포인트">다</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap"><?php echo $sort_link('bo_use_sns') ?>SNS</a></th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap"><?php echo $sort_link('bo_use_search') ?>검색</a></th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap"><?php echo $sort_link('bo_order') ?>순서</a></th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap">접속</th>
                    <th class="px-3 py-2.5 text-right whitespace-nowrap">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            $select_cls = 'h-9 pl-2.5 pr-8 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';
            $input_cls  = 'w-full h-9 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500';
            $num_cls    = 'w-14 h-9 px-2 text-center rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';
            while ($row = sql_fetch_array($result)) {
                ?>
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                    <td class="px-3 py-2 text-center">
                        <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>" class="rounded border-slate-300">
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <?php if ($is_admin === 'super'): ?>
                            <?php echo str_replace('<select ', '<select class="'.$select_cls.'" ', get_group_select("gr_id[$i]", $row['gr_id'])) ?>
                        <?php else: ?>
                            <input type="hidden" name="gr_id[<?php echo $i ?>]" value="<?php echo $h($row['gr_id']) ?>">
                            <span class="text-slate-700 dark:text-slate-300"><?php echo $h($row['gr_subject'] ?? $row['gr_id']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap font-mono text-xs">
                        <input type="hidden" name="board_table[<?php echo $i ?>]" value="<?php echo $h($row['bo_table']) ?>">
                        <a href="<?php echo $h(get_pretty_url($row['bo_table'])) ?>" class="text-admin-primary-700 dark:text-admin-primary-300 hover:underline" target="_blank"><?php echo $h($row['bo_table']) ?></a>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <?php echo str_replace('<select ', '<select class="'.$select_cls.'" ', get_skin_select('board', 'bo_skin_'.$i, "bo_skin[$i]", $row['bo_skin'])) ?>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <?php echo str_replace('<select ', '<select class="'.$select_cls.'" ', get_mobile_skin_select('board', 'bo_mobile_skin_'.$i, "bo_mobile_skin[$i]", $row['bo_mobile_skin'])) ?>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="bo_subject[<?php echo $i ?>]" value="<?php echo $h($row['bo_subject']) ?>" required class="<?php echo $input_cls ?>" maxlength="255">
                    </td>
                    <td class="px-3 py-2"><input type="text" name="bo_read_point[<?php echo $i ?>]"     value="<?php echo (int)$row['bo_read_point'] ?>"     class="<?php echo $num_cls ?>"></td>
                    <td class="px-3 py-2"><input type="text" name="bo_write_point[<?php echo $i ?>]"    value="<?php echo (int)$row['bo_write_point'] ?>"    class="<?php echo $num_cls ?>"></td>
                    <td class="px-3 py-2"><input type="text" name="bo_comment_point[<?php echo $i ?>]"  value="<?php echo (int)$row['bo_comment_point'] ?>"  class="<?php echo $num_cls ?>"></td>
                    <td class="px-3 py-2"><input type="text" name="bo_download_point[<?php echo $i ?>]" value="<?php echo (int)$row['bo_download_point'] ?>" class="<?php echo $num_cls ?>"></td>
                    <td class="px-3 py-2 text-center"><input type="checkbox" name="bo_use_sns[<?php echo $i ?>]"    value="1" <?php echo $row['bo_use_sns']?'checked':'' ?> class="rounded border-slate-300"></td>
                    <td class="px-3 py-2 text-center"><input type="checkbox" name="bo_use_search[<?php echo $i ?>]" value="1" <?php echo $row['bo_use_search']?'checked':'' ?> class="rounded border-slate-300"></td>
                    <td class="px-3 py-2"><input type="text" name="bo_order[<?php echo $i ?>]" value="<?php echo (int)$row['bo_order'] ?>" class="<?php echo $num_cls ?>"></td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <select name="bo_device[<?php echo $i ?>]" class="<?php echo $select_cls ?>">
                            <option value="both"   <?php echo $row['bo_device']==='both'  ?'selected':'' ?>>모두</option>
                            <option value="pc"     <?php echo $row['bo_device']==='pc'    ?'selected':'' ?>>PC</option>
                            <option value="mobile" <?php echo $row['bo_device']==='mobile'?'selected':'' ?>>모바일</option>
                        </select>
                    </td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">
                        <a href="/admin/board_form?w=u&amp;bo_table=<?php echo urlencode($row['bo_table']) ?>" class="inline-flex items-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">수정</a>
                        <a href="/admin/board_copy?bo_table=<?php echo urlencode($row['bo_table']) ?>" class="inline-flex items-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 board-copy">복사</a>
                    </td>
                </tr>
                <?php
                $i++;
            }
            if ($i === 0): ?>
                <tr><td colspan="15" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">자료가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>

        <div class="flex flex-wrap items-center gap-2 px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
            <button type="submit" name="act_button" value="선택수정" class="h-9 px-3.5 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium" onclick="window.__pressed=this.value">선택 수정</button>
            <?php if ($is_admin === 'super'): ?>
            <button type="submit" name="act_button" value="선택삭제" class="h-9 px-3.5 rounded-md bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium" onclick="window.__pressed=this.value">선택 삭제</button>
            <?php endif; ?>
            <span class="ml-auto text-xs text-slate-500"><?php echo number_format($total_count) ?>개 중 <?php echo (int)$page ?>/<?php echo (int)$total_page ?>페이지</span>
        </div>
    </form>

    <?php if ($total_page > 1): ?>
    <nav class="mt-4 flex flex-wrap items-center gap-1 justify-center text-sm">
        <?php
        $page_size = G5_IS_MOBILE ? (int)$config['cf_mobile_pages'] : (int)$config['cf_write_pages'];
        if ($page_size < 1) $page_size = 10;
        $half = (int)floor($page_size/2);
        $start = max(1, $page - $half);
        $end   = min($total_page, $start + $page_size - 1);
        $start = max(1, $end - $page_size + 1);
        $pgUrl = function($p) use ($qstr) {
            $params = [];
            if ($qstr) parse_str($qstr, $params);
            $params['page'] = $p;
            return '/admin/board_list?'.http_build_query($params);
        };
        $pgCls = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
        $pgActive = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md bg-admin-primary-600 text-white border border-admin-primary-600 font-semibold';
        ?>
        <?php if ($page > 1): ?><a class="<?php echo $pgCls ?>" href="<?php echo $h($pgUrl(1)) ?>">처음</a><?php endif; ?>
        <?php for ($p = $start; $p <= $end; $p++): ?>
            <?php if ($p === $page): ?>
                <span class="<?php echo $pgActive ?>"><?php echo $p ?></span>
            <?php else: ?>
                <a class="<?php echo $pgCls ?>" href="<?php echo $h($pgUrl($p)) ?>"><?php echo $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $total_page): ?><a class="<?php echo $pgCls ?>" href="<?php echo $h($pgUrl($total_page)) ?>">끝</a><?php endif; ?>
    </nav>
    <?php endif; ?>

</main>

<script>
(function () {
    var f  = document.getElementById('fboardlist');
    var ck = document.getElementById('chkall');
    if (ck && f) {
        ck.addEventListener('change', function () {
            f.querySelectorAll('input[name="chk[]"]').forEach(function (i) { i.checked = ck.checked; });
        });
    }
    if (f) {
        f.addEventListener('submit', function (e) {
            var anyChecked = !!f.querySelector('input[name="chk[]"]:checked');
            if (!anyChecked) { e.preventDefault(); alert((window.__pressed || '실행') + ' 하실 항목을 하나 이상 선택하세요.'); return; }
            if (window.__pressed === '선택삭제' && !confirm('선택한 자료를 정말 삭제하시겠습니까?')) { e.preventDefault(); }
        });
    }
    document.querySelectorAll('a.board-copy').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            window.open(a.href, 'win_board_copy', 'left=100,top=100,width=550,height=450');
        });
    });
})();
</script>

<?php
admin_layout_end();
