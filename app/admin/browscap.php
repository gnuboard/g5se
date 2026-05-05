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

<div id="processing">
    <p>Browscap 정보를 업데이트하시려면 아래 업데이트 버튼을 클릭해 주세요.</p>
    <button type="button" id="run_update">업데이트</button>
</div>

<script>
    $(function() {
        $("#run_update").on("click", function() {
            $("#processing").html('<div class="update_processing"></div><p>Browscap 정보를 업데이트 중입니다.</p>');

            $.ajax({
                url: <?php echo json_encode(G5_ADMIN_URL.'/browscap_update') ?>,
                async: true,
                cache: false,
                dataType: "html",
                success: function(data) {
                    if (data != "") {
                        alert(data);
                        return false;
                    }

                    $("#processing").html("<div class='check_processing'></div><p>Browscap 정보를 업데이트 했습니다.</p>");
                }
            });
        });
    });
</script>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/browscap — modern shell wrap end
