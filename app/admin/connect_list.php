<?php
/*
 * /admin/connect_list — 현재 접속자 (g5_login 테이블).
 *   회원 / 비회원 분리 카운트, 최근 접속 시각 / 마지막 위치(URL) / IP 표시.
 *   gnuboard 의 자동 청소 (login_cleanup) 가 cf_login_minutes 설정에 따라 동작.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

// 일반 admin 도 접근 가능 (level '' = 모두 / 'super' 만은 메뉴에서 super 제한)
$sub_menu = '200900';   // 보통 g5_auth 에 등록 안 되어 있어도 super 면 통과
if ($is_admin === 'super') {
    auth_check_menu($auth, $sub_menu, 'r');
}

$filter = isset($_GET['filter']) ? (string)$_GET['filter'] : 'all';   // all | member | guest

$where = " where 1=1 ";
if ($filter === 'member') $where .= " and a.mb_id <> '' ";
if ($filter === 'guest')  $where .= " and a.mb_id = '' ";

$total_all    = (int)sql_fetch(" select count(*) as cnt from {$g5['login_table']} ")['cnt'];
$total_member = (int)sql_fetch(" select count(*) as cnt from {$g5['login_table']} where mb_id <> '' ")['cnt'];
$total_guest  = $total_all - $total_member;

$sql = " select a.lo_id, a.mb_id, a.lo_ip, a.lo_datetime, a.lo_location, a.lo_url,
                b.mb_nick, b.mb_name, b.mb_level, b.mb_email
            from {$g5['login_table']} a
            left join {$g5['member_table']} b on (a.mb_id = b.mb_id)
            {$where}
            order by a.lo_datetime desc ";
$result = sql_query($sql);

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// 시간 차이를 사람이 읽기 쉽게
$ago = static function (string $datetime): string {
    if (!$datetime || $datetime === '0000-00-00 00:00:00') return '—';
    $ts = strtotime($datetime);
    if (!$ts) return $datetime;
    $diff = time() - $ts;
    if ($diff < 60)    return $diff.'초 전';
    if ($diff < 3600)  return (int)($diff/60).'분 전';
    if ($diff < 86400) return (int)($diff/3600).'시간 전';
    return (int)($diff/86400).'일 전';
};

admin_layout_start('현재 접속자', 'connect');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">

    <header class="flex flex-wrap items-center gap-3 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight">현재 접속자</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                전체 <strong class="text-slate-700 dark:text-slate-300"><?php echo number_format($total_all) ?></strong>명 ·
                회원 <strong class="text-emerald-700 dark:text-emerald-400"><?php echo number_format($total_member) ?></strong>명 ·
                비회원 <strong class="text-slate-500"><?php echo number_format($total_guest) ?></strong>명
                <span class="ml-2 text-slate-400">(cf_login_minutes 기준 자동 만료)</span>
            </p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <a href="/admin/connect_list" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="새로고침" title="새로고침">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            </a>
        </div>
    </header>

    <nav class="flex items-center gap-1 mb-4 text-sm">
        <?php
        $tabs = [
            'all'    => ['전체',  $total_all],
            'member' => ['회원',  $total_member],
            'guest'  => ['비회원',$total_guest],
        ];
        foreach ($tabs as $k => $v):
            $on = ($filter === $k);
            ?>
            <a href="/admin/connect_list<?php echo $k==='all'?'':'?filter='.$k ?>"
               class="<?php echo $on
                    ? 'inline-flex items-center gap-1.5 h-8 px-3 rounded-md bg-admin-primary-600 text-white font-medium'
                    : 'inline-flex items-center gap-1.5 h-8 px-3 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' ?>">
                <?php echo $h($v[0]) ?>
                <span class="text-xs opacity-80"><?php echo number_format($v[1]) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-3 py-2.5 text-center w-12">#</th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap">회원</th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap">IP</th>
                    <th class="px-3 py-2.5 text-left">현재 위치</th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap">접속 시각</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            while ($row = sql_fetch_array($result)):
                $is_member = !empty($row['mb_id']);
                $loc = trim($row['lo_location']) ?: '—';
                $url = trim($row['lo_url']) ?: '—';
                ?>
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                    <td class="px-3 py-2 text-center text-slate-400 text-xs"><?php echo $i + 1 ?></td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <?php if ($is_member): ?>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <a href="/admin/member_form?w=u&amp;mb_id=<?php echo urlencode($row['mb_id']) ?>" class="font-mono text-xs text-admin-primary-700 dark:text-admin-primary-300 hover:underline"><?php echo $h($row['mb_id']) ?></a>
                                <span class="text-slate-700 dark:text-slate-300"><?php echo $h($row['mb_nick']) ?></span>
                                <?php if ((int)$row['mb_level'] >= 9): ?>
                                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium rounded bg-rose-50 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">관리자</span>
                                <?php endif; ?>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 text-slate-500">
                                <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                비회원
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap font-mono text-xs text-slate-600 dark:text-slate-400"><?php echo $h($row['lo_ip']) ?></td>
                    <td class="px-3 py-2">
                        <div class="text-slate-700 dark:text-slate-300 truncate max-w-md"><?php echo $h($loc) ?></div>
                        <?php if ($url !== '—'): ?>
                        <div class="text-xs text-slate-400 font-mono truncate max-w-md"><?php echo $h($url) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs">
                        <span class="text-slate-700 dark:text-slate-300"><?php echo $h($ago($row['lo_datetime'])) ?></span>
                        <span class="text-slate-400 ml-1"><?php echo $h(substr($row['lo_datetime'], 11, 5)) ?></span>
                    </td>
                </tr>
                <?php
                $i++;
            endwhile;
            if ($i === 0): ?>
                <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">접속자가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <p class="mt-4 text-xs text-slate-500">
        ※ 접속자 정보는 페이지 이동 시 갱신되며, 환경설정의 <strong>접속자 표시 시간</strong>이 지나면 자동 만료됩니다.
    </p>

</main>

<script>
// 30초마다 자동 새로고침 (탭 활성 상태에서만)
(function () {
    var timer = setInterval(function () {
        if (!document.hidden) location.reload();
    }, 30000);
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) clearInterval(timer);
    });
})();
</script>

<?php
admin_layout_end();
