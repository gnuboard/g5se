<?php
/*
 * /admin/browscap_convert — 접속로그 → Browscap 정보 변환.
 */
$sub_menu = "100520";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

if ($is_admin !== 'super') {
    header('Location: '.G5_ADMIN_URL, true, 302);
    exit;
}

require_once __DIR__.'/admin.lib.php';

if (!(defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE)) {
    alert('사용할 수 없는 기능입니다.', G5_ADMIN_URL);
}

$rows = isset($_GET['rows']) ? preg_replace('#[^0-9]#', '', $_GET['rows']) : 0;
if (!$rows) {
    $rows = 100;
}

$g5['title'] = '접속로그 변환';
admin_layout_start($g5['title'], 'browscap_convert');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">

<!-- Alpine.js: idle → running → done. done 상태의 result HTML 안에 #run_update
     버튼이 들어 있어 (서버가 chunk 별로 새 버튼을 다시 렌더), 이벤트 위임으로
     run() 재호출. -->
<?php
$_browscap_conv_url = htmlspecialchars(G5_ADMIN_URL.'/browscap_converter', ENT_QUOTES);
?>
<div id="processing"
     x-data="{
        status: 'idle',
        result: '',
        async run() {
            if (this.status === 'running') return;
            this.status = 'running';
            const u = new URL('<?php echo $_browscap_conv_url; ?>', location.href);
            u.searchParams.set('rows', '<?php echo (int)$rows ?>');
            u.searchParams.set('_', Date.now());
            try {
                const r = await fetch(u, { cache: 'no-store' });
                this.result = await r.text();
                this.status = 'done';
            } catch (e) {
                this.result = '<p style=\'color:#b91c1c\'><strong>변환 실패</strong> ' + (e.message || '') + '</p>';
                this.status = 'done';
            }
        }
     }"
     @click="if (event.target.closest('#run_update')) run()">

    <template x-if="status === 'idle'">
        <div>
            <p>접속로그 정보를 Browscap 정보로 변환하시려면 아래 업데이트 버튼을 클릭해 주세요.</p>
            <button type="button" class="btn btn_01" id="run_update">업데이트</button>
        </div>
    </template>

    <template x-if="status === 'running'">
        <div>
            <div class="update_processing"></div>
            <p>Browscap 정보로 변환 중입니다…</p>
        </div>
    </template>

    <template x-if="status === 'done'">
        <div x-html="result"></div>
    </template>
</div>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/browscap_convert — modern shell wrap end
