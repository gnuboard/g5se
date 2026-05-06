<?php
/*
 * /admin/write_count — 글/댓글 현황 (Chart.js 그래프).
 * gnu5se: 원본 jqplot → Chart.js 4.x (admin shell 에 이미 로드돼 있음).
 */
$sub_menu = '300820';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if (function_exists('check_demo')) {
    check_demo();
}

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '글,댓글 현황';
admin_layout_start($g5['title'], 'write_count');

$graph = isset($_GET['graph']) ? clean_xss_tags($_GET['graph'], 1, 1) : '';
$period = isset($_GET['period']) ? clean_xss_tags($_GET['period'], 1, 1) : '';

if (!($graph == 'line' || $graph == 'bar'))
    $graph = 'line';

$period_array = array(
    '오늘'=>array('시간', 0),
    '어제'=>array('시간', 0),
    '7일전'=>array('일', 7),
    '14일전'=>array('일', 14),
    '30일전'=>array('일', 30),
    '3개월전'=>array('주', 90),
    '6개월전'=>array('주', 180),
    '1년전'=>array('월', 365),
    '2년전'=>array('월', 365*2),
    '3년전'=>array('월', 365*3),
    '5년전'=>array('년', 365*5),
    '10년전'=>array('년', 365*10),
);

$is_period = false;
foreach($period_array as $key=>$value) {
    if ($key == $period) {
        $is_period = true;
        break;
    }
}
if (!$is_period)
    $period = '오늘';

$day = $period_array[$period][0];

$today = date('Y-m-d', G5_SERVER_TIME);
$yesterday = date('Y-m-d', G5_SERVER_TIME - 86400);

if ($period == '오늘') {
    $from = $today;
    $to = $from;
} else if ($period == '어제') {
    $from = $yesterday;
    $to = $from;
} else if ($period == '내일') {
    $from = date('Y-m-d', G5_SERVER_TIME + (86400 * 2));
    $to = $from;
} else {
    $from = date('Y-m-d', G5_SERVER_TIME - (86400 * $period_array[$period][1]));
    $to = $yesterday;
}

$params = [':from' => $from, ':to' => $to];
$sql_bo_table = '';
if ($bo_table) {
    $sql_bo_table = "and bo_table = :bo_table";
    $params[':bo_table'] = $bo_table;
}

$labels = $wcounts = $ccounts = array();

switch ($day) {
    case '시간' :
        $sql = " select substr(bn_datetime,6,8) as hours, sum(if(wr_id=wr_parent,1,0)) as wcount, sum(if(wr_id=wr_parent,0,1)) as ccount from {$g5['board_new_table']} where substr(bn_datetime,1,10) between :from and :to {$sql_bo_table} group by hours order by bn_datetime ";
        $result = sql_pdo_query($sql, $params);
        while ($row = sql_fetch_array($result)) {
            $labels[]  = substr($row['hours'], 0, 8);
            $wcounts[] = (int)$row['wcount'];
            $ccounts[] = (int)$row['ccount'];
        }
        break;
    case '일' :
        $sql  = " select substr(bn_datetime,1,10) as days, sum(if(wr_id=wr_parent,1,0)) as wcount, sum(if(wr_id=wr_parent,0,1)) as ccount from {$g5['board_new_table']} where substr(bn_datetime,1,10) between :from and :to {$sql_bo_table} group by days order by bn_datetime ";
        $result = sql_pdo_query($sql, $params);
        while ($row = sql_fetch_array($result)) {
            $labels[]  = substr($row['days'], 5, 5);
            $wcounts[] = (int)$row['wcount'];
            $ccounts[] = (int)$row['ccount'];
        }
        break;
    case '주' :
        $sql  = " select concat(substr(bn_datetime,1,4), '-', weekofyear(bn_datetime)) as weeks, sum(if(wr_id=wr_parent,1,0)) as wcount, sum(if(wr_id=wr_parent,0,1)) as ccount from {$g5['board_new_table']} where substr(bn_datetime,1,10) between :from and :to {$sql_bo_table} group by weeks order by bn_datetime ";
        $result = sql_pdo_query($sql, $params);
        while ($row = sql_fetch_array($result)) {
            list($lyear, $lweek) = explode('-', $row['weeks']);
            $labels[]  = date('y-m-d', strtotime($lyear.'W'.str_pad($lweek, 2, '0', STR_PAD_LEFT)));
            $wcounts[] = (int)$row['wcount'];
            $ccounts[] = (int)$row['ccount'];
        }
        break;
    case '월' :
        $sql  = " select substr(bn_datetime,1,7) as months, sum(if(wr_id=wr_parent,1,0)) as wcount, sum(if(wr_id=wr_parent,0,1)) as ccount from {$g5['board_new_table']} where substr(bn_datetime,1,10) between :from and :to {$sql_bo_table} group by months order by bn_datetime ";
        $result = sql_pdo_query($sql, $params);
        while ($row = sql_fetch_array($result)) {
            $labels[]  = substr($row['months'], 2, 5);
            $wcounts[] = (int)$row['wcount'];
            $ccounts[] = (int)$row['ccount'];
        }
        break;
    case '년' :
        $sql  = " select substr(bn_datetime,1,4) as years, sum(if(wr_id=wr_parent,1,0)) as wcount, sum(if(wr_id=wr_parent,0,1)) as ccount from {$g5['board_new_table']} where substr(bn_datetime,1,10) between :from and :to {$sql_bo_table} group by years order by bn_datetime ";
        $result = sql_pdo_query($sql, $params);
        while ($row = sql_fetch_array($result)) {
            $labels[]  = substr($row['years'], 0, 4);
            $wcounts[] = (int)$row['wcount'];
            $ccounts[] = (int)$row['ccount'];
        }
        break;
}
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>

<div class="space-y-4">
    <form method="get" class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <select name="bo_table" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-admin-primary-500 focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
            <option value="">전체게시판</option>
            <?php
            $sql = " select bo_table, bo_subject from {$g5['board_table']} order by bo_count_write desc ";
            $result = sql_query($sql);
            while ($row = sql_fetch_array($result)) {
                $sel = ($bo_table == $row['bo_table']) ? ' selected' : '';
                echo '<option value="'.htmlspecialchars($row['bo_table']).'"'.$sel.'>'.htmlspecialchars($row['bo_subject']).'</option>'."\n";
            }
            ?>
        </select>

        <select name="period" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-admin-primary-500 focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
            <?php
            foreach ($period_array as $key => $value) {
                $sel = ($key == $period) ? ' selected' : '';
                echo '<option value="'.htmlspecialchars($key).'"'.$sel.'>'.htmlspecialchars($key).'</option>'."\n";
            }
            ?>
        </select>

        <select name="graph" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-admin-primary-500 focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
            <option value="line" <?php echo ($graph == 'line' ? 'selected' : ''); ?>>선그래프</option>
            <option value="bar"  <?php echo ($graph == 'bar'  ? 'selected' : ''); ?>>막대그래프</option>
        </select>

        <button type="submit" class="rounded-md bg-admin-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-admin-primary-700">확인</button>
    </form>

    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <?php if (empty($labels)) { ?>
            <div class="flex h-72 items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                그래프를 만들 데이터가 없습니다.
            </div>
        <?php } else { ?>
            <div style="position:relative; height:480px; width:100%;">
                <canvas id="wc_chart"></canvas>
            </div>
        <?php } ?>
    </div>
</div>

<?php if (!empty($labels)) { ?>
<script>
(function(){
    function init(){
        if (typeof Chart === 'undefined') { setTimeout(init, 50); return; }

        var labels   = <?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
        var wcounts  = <?php echo json_encode($wcounts); ?>;
        var ccounts  = <?php echo json_encode($ccounts); ?>;
        var graphType = <?php echo json_encode($graph); ?>;

        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        var textColor = isDark ? '#cbd5e1' : '#475569';
        var gridColor = isDark ? 'rgba(148,163,184,0.15)' : 'rgba(100,116,139,0.15)';

        var primary  = getComputedStyle(document.documentElement).getPropertyValue('--admin-primary-500').trim() || '#3464f5';
        var primary600 = getComputedStyle(document.documentElement).getPropertyValue('--admin-primary-600').trim() || '#2952d6';
        var accent   = '#f59e0b';
        var accent600 = '#d97706';

        function rgba(hex, a){
            var h = hex.replace('#','');
            if (h.length === 3) h = h.split('').map(function(c){return c+c;}).join('');
            var r = parseInt(h.substr(0,2),16), g = parseInt(h.substr(2,2),16), b = parseInt(h.substr(4,2),16);
            return 'rgba('+r+','+g+','+b+','+a+')';
        }

        var common = {
            tension: 0.35,
            borderWidth: 2,
            pointRadius: graphType === 'line' ? 3 : 0,
            pointHoverRadius: 5,
            fill: graphType === 'line',
            borderRadius: graphType === 'bar' ? 6 : 0,
            maxBarThickness: 36
        };

        var datasets = [
            Object.assign({
                label: '글 수',
                data: wcounts,
                borderColor: primary,
                backgroundColor: graphType === 'line' ? rgba(primary, 0.18) : primary,
                hoverBackgroundColor: primary600,
                pointBackgroundColor: primary
            }, common),
            Object.assign({
                label: '댓글 수',
                data: ccounts,
                borderColor: accent,
                backgroundColor: graphType === 'line' ? rgba(accent, 0.18) : accent,
                hoverBackgroundColor: accent600,
                pointBackgroundColor: accent
            }, common)
        ];

        var ctx = document.getElementById('wc_chart').getContext('2d');
        new Chart(ctx, {
            type: graphType === 'bar' ? 'bar' : 'line',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { color: textColor, usePointStyle: true, boxWidth: 8, padding: 16 }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#e2e8f0',
                        padding: 10,
                        cornerRadius: 6,
                        displayColors: true,
                        boxPadding: 4
                    }
                },
                scales: {
                    x: {
                        ticks: { color: textColor, maxRotation: 0, autoSkip: true },
                        grid: { color: gridColor, drawTicks: false },
                        border: { color: gridColor }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: textColor, precision: 0 },
                        grid: { color: gridColor, drawTicks: false },
                        border: { display: false }
                    }
                }
            }
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
</script>
<?php } ?>
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/write_count — modern shell wrap end
