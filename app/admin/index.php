<?php
// admin 대시보드 — /admin
$sub_menu = '000000';   // 사이드바 active 매칭용 (대시보드)
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';

// ──────────────────────────────────────────────
// 카운트 위젯용 통계
// ──────────────────────────────────────────────
global $g5;
$today = G5_TIME_YMD;

$stat_member_total = (int) sql_pdo_fetch("SELECT COUNT(*) AS c FROM {$g5['member_table']} WHERE mb_leave_date = '' AND mb_intercept_date = ''")['c'];
$stat_member_today = (int) sql_pdo_fetch("SELECT COUNT(*) AS c FROM {$g5['member_table']} WHERE LEFT(mb_datetime, 10) = :today", [':today' => $today])['c'];
$stat_post_total   = (int) sql_pdo_fetch("SELECT COUNT(*) AS c FROM {$g5['board_new_table']}")['c'];
$stat_post_today   = (int) sql_pdo_fetch("SELECT COUNT(*) AS c FROM {$g5['board_new_table']} WHERE LEFT(bn_datetime, 10) = :today", [':today' => $today])['c'];
$stat_qa_unanswered = (int) sql_pdo_fetch("SELECT COUNT(*) AS c FROM {$g5['qa_content_table']} WHERE qa_type = 0 AND qa_status = 0")['c'];
$stat_connect      = (int) sql_pdo_fetch("SELECT COUNT(*) AS c FROM {$g5['login_table']} WHERE mb_id <> :cf_admin", [':cf_admin' => $GLOBALS['config']['cf_admin']])['c'];

// 7일 추이 차트용 — 가입 / 게시물
$days = [];
for ($i = 6; $i >= 0; $i--) $days[] = date('Y-m-d', strtotime("-$i days"));

$chart_join = []; $chart_post = [];
foreach ($days as $d) {
    $chart_join[] = (int) sql_pdo_fetch("SELECT COUNT(*) AS c FROM {$g5['member_table']} WHERE LEFT(mb_datetime, 10) = :d", [':d' => $d])['c'];
    $chart_post[] = (int) sql_pdo_fetch("SELECT COUNT(*) AS c FROM {$g5['board_new_table']} WHERE LEFT(bn_datetime, 10) = :d", [':d' => $d])['c'];
}

// 최근 가입 회원 5명
$recent_members = [];
$res = sql_pdo_query("SELECT mb_id, mb_nick, mb_datetime FROM {$g5['member_table']} WHERE mb_leave_date = '' ORDER BY mb_datetime DESC LIMIT 5");
while ($row = sql_fetch_array($res)) $recent_members[] = $row;

// 최근 게시물 5개
$recent_posts = [];
$res = sql_pdo_query("SELECT bo_table, wr_id, wr_parent, bn_datetime, mb_id FROM {$g5['board_new_table']} ORDER BY bn_datetime DESC LIMIT 5");
while ($row = sql_fetch_array($res)) {
    $w = sql_pdo_fetch("SELECT wr_subject, wr_name FROM {$g5['write_prefix']}{$row['bo_table']} WHERE wr_id = :wr_id", [':wr_id' => (int)$row['wr_id']]);
    if ($w) {
        $row['wr_subject'] = $w['wr_subject'];
        $row['wr_name']    = $w['wr_name'];
        $recent_posts[] = $row;
    }
}

admin_layout_start('대시보드', 'home');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <!-- 카운트 카드 6개 -->
    <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 mb-6">
        <?php
        $cards = [
            ['label' => '총 회원',       'value' => number_format($stat_member_total),  'sub' => '오늘 +'.number_format($stat_member_today),   'tone' => 'primary'],
            ['label' => '총 게시물',     'value' => number_format($stat_post_total),    'sub' => '오늘 +'.number_format($stat_post_today),     'tone' => 'green'],
            ['label' => '미답변 1:1',    'value' => number_format($stat_qa_unanswered), 'sub' => '대기 중',                                     'tone' => 'amber'],
            ['label' => '현재 접속',     'value' => number_format($stat_connect),       'sub' => '실시간',                                      'tone' => 'sky'],
            ['label' => '오늘 가입',     'value' => number_format($stat_member_today),  'sub' => date('Y-m-d'),                                'tone' => 'violet'],
            ['label' => '오늘 게시물',   'value' => number_format($stat_post_today),    'sub' => date('Y-m-d'),                                'tone' => 'rose'],
        ];
        // 그라디언트 폐기 — 좌측 accent border + 단색 배경
        $tone_classes = [
            'primary' => 'border-l-admin-primary-500 text-admin-primary-700 dark:text-admin-primary-300',
            'green'   => 'border-l-emerald-500 text-emerald-700 dark:text-emerald-300',
            'amber'   => 'border-l-amber-500 text-amber-700 dark:text-amber-300',
            'sky'     => 'border-l-sky-500 text-sky-700 dark:text-sky-300',
            'violet'  => 'border-l-violet-500 text-violet-700 dark:text-violet-300',
            'rose'    => 'border-l-rose-500 text-rose-700 dark:text-rose-300',
        ];
        foreach ($cards as $c) { ?>
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 border-l-4 bg-white dark:bg-slate-900 <?php echo $tone_classes[$c['tone']] ?> p-4 shadow-sm">
            <div class="text-xs font-medium opacity-70"><?php echo $c['label'] ?></div>
            <div class="mt-1 text-2xl font-bold tabular-nums"><?php echo $c['value'] ?></div>
            <div class="mt-1 text-[11px] opacity-70"><?php echo $c['sub'] ?></div>
        </div>
        <?php } ?>
    </section>

    <!-- 7일 추이 차트 -->
    <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-6 mb-6">
        <header class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">최근 7일 추이</h2>
            <span class="text-xs text-slate-500"><?php echo $days[0] ?> ~ <?php echo end($days) ?></span>
        </header>
        <div class="h-64 sm:h-72"><canvas id="chart-7days"></canvas></div>
    </section>

    <!-- 최근 가입 + 최근 게시물 -->
    <section class="grid lg:grid-cols-2 gap-4">
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-6">
            <header class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">최근 가입 회원</h2>
                <a href="/admin/member_list" class="text-xs text-admin-primary-600 hover:underline">전체 →</a>
            </header>
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php foreach ($recent_members as $m) { ?>
                <li class="flex items-center gap-3 py-2.5">
                    <span class="w-8 h-8 rounded-full bg-admin-primary-100 dark:bg-admin-primary-900 text-admin-primary-700 dark:text-admin-primary-200 flex items-center justify-center text-sm font-semibold">
                        <?php echo mb_substr(get_text($m['mb_nick']), 0, 1) ?>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium truncate"><?php echo get_text($m['mb_nick']) ?></div>
                        <div class="text-xs text-slate-500 truncate"><?php echo get_text($m['mb_id']) ?></div>
                    </div>
                    <span class="text-xs text-slate-400"><?php echo substr($m['mb_datetime'], 5, 11) ?></span>
                </li>
                <?php } ?>
                <?php if (!$recent_members) { ?>
                <li class="py-6 text-center text-sm text-slate-400">최근 가입 회원이 없습니다.</li>
                <?php } ?>
            </ul>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-6">
            <header class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">최근 게시물</h2>
                <a href="/new" class="text-xs text-admin-primary-600 hover:underline">전체 →</a>
            </header>
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php foreach ($recent_posts as $p) { ?>
                <li class="py-2.5">
                    <a href="/board/<?php echo $p['bo_table'] ?>/<?php echo $p['wr_id'] ?>" class="block group">
                        <div class="text-sm font-medium truncate group-hover:text-admin-primary-600"><?php echo get_text($p['wr_subject']) ?></div>
                        <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px]"><?php echo $p['bo_table'] ?></span>
                            <span><?php echo get_text($p['wr_name']) ?></span>
                            <span class="ml-auto"><?php echo substr($p['bn_datetime'], 5, 11) ?></span>
                        </div>
                    </a>
                </li>
                <?php } ?>
                <?php if (!$recent_posts) { ?>
                <li class="py-6 text-center text-sm text-slate-400">최근 게시물이 없습니다.</li>
                <?php } ?>
            </ul>
        </div>
    </section>

</main>

<script>
window.addEventListener('load', function () {
    if (typeof Chart === 'undefined') return;
    var ctx = document.getElementById('chart-7days');
    if (!ctx) return;
    var dark = document.documentElement.dataset.theme === 'dark';
    var grid = dark ? 'rgba(148,163,184,0.15)' : 'rgba(148,163,184,0.25)';
    var tickColor = dark ? '#cbd5e1' : '#475569';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_map(function ($d) { return substr($d, 5); }, $days)) ?>,
            datasets: [
                { label: '가입',   data: <?php echo json_encode($chart_join) ?>,
                  borderColor: 'rgb(14,165,233)', backgroundColor: 'rgba(14,165,233,.15)', tension: .35, fill: true, borderWidth: 2, pointRadius: 3 },
                { label: '게시물', data: <?php echo json_encode($chart_post) ?>,
                  borderColor: 'rgb(99,102,241)', backgroundColor: 'rgba(99,102,241,.15)', tension: .35, fill: true, borderWidth: 2, pointRadius: 3 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: tickColor, boxWidth: 12, boxHeight: 12, padding: 14 } }
            },
            scales: {
                x: { grid: { color: grid }, ticks: { color: tickColor } },
                y: { grid: { color: grid }, ticks: { color: tickColor, precision: 0 }, beginAtZero: true }
            }
        }
    });
});
</script>

<?php
admin_layout_end();
