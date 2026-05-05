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

<div id="processing">
    <p>접속로그 정보를 Browscap 정보로 변환하시려면 아래 업데이트 버튼을 클릭해 주세요.</p>
    <button type="button" id="run_update">업데이트</button>
</div>

<script>
    $(function() {
        $(document).on("click", "#run_update", function() {
            $("#processing").html('<div class="update_processing"></div><p>Browscap 정보로 변환 중입니다.</p>');

            $.ajax({
                method: "GET",
                url: <?php echo json_encode(G5_ADMIN_URL.'/browscap_converter') ?>,
                data: {
                    rows: "<?php echo strval($rows); ?>"
                },
                async: true,
                cache: false,
                dataType: "html",
                success: function(data) {
                    $("#processing").html(data);
                }
            });
        });
    });
</script>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/browscap_convert — modern shell wrap end
