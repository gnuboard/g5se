<?php
/*
 * /admin/browscap — Browscap 정보 업데이트.
 */
$sub_menu = "100510";
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

$g5['title'] = 'Browscap 업데이트';
admin_layout_start($g5['title'], 'browscap');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">

<!-- Alpine.js state machine: idle → running → done | error.
     status 에 따라 spinner / 체크 / 에러 텍스트 자동 토글. jQuery 의존 제거. -->
<div id="processing"
     x-data="{
        status: 'idle',
        error: '',
        run() {
            if (this.status === 'running') return;
            this.status = 'running';
            this.error = '';
            fetch(<?php echo json_encode(G5_ADMIN_URL.'/browscap_update?_='.time()) ?>, { cache: 'no-store' })
                .then(r => r.text())
                .then(t => {
                    const trimmed = (t || '').trim();
                    if (trimmed) { this.status = 'error'; this.error = trimmed; return; }
                    this.status = 'done';
                })
                .catch(e => { this.status = 'error'; this.error = e.message || '요청 실패'; });
        }
     }">

    <template x-if="status === 'idle'">
        <div>
            <p>Browscap 정보를 업데이트하시려면 아래 업데이트 버튼을 클릭해 주세요.</p>
            <button type="button" class="btn btn_01" @click="run()">업데이트</button>
        </div>
    </template>

    <template x-if="status === 'running'">
        <div>
            <div class="update_processing"></div>
            <p>Browscap 정보를 업데이트 중입니다…</p>
        </div>
    </template>

    <template x-if="status === 'done'">
        <div>
            <div class="check_processing"></div>
            <p><strong>Browscap 정보를 업데이트 했습니다.</strong></p>
            <button type="button" class="btn btn_03" @click="status = 'idle'">다시 업데이트</button>
        </div>
    </template>

    <template x-if="status === 'error'">
        <div>
            <p style="color:#b91c1c"><strong>업데이트 실패</strong></p>
            <pre style="white-space:pre-wrap;color:#b91c1c" x-text="error"></pre>
            <button type="button" class="btn btn_03" @click="run()">다시 시도</button>
        </div>
    </template>
</div>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/browscap — modern shell wrap end
