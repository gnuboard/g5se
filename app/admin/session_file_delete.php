<?php
/*
 * /admin/session_file_delete — 6시간 이상 안 쓴 세션파일 일괄삭제.
 */
$sub_menu = "100800";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

if ($is_admin !== 'super') {
    header('Location: '.G5_URL, true, 302);
    exit;
}

require_once __DIR__.'/admin.lib.php';

$g5['title'] = '세션파일 일괄삭제';
admin_layout_start($g5['title'], 'session_file_delete');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">

<div class="local_desc02 local_desc">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    </p>
</div>

    <?php
    flush();

    $list_tag_st = "";
    $list_tag_end = "";
    if (!$dir=@opendir(G5_DATA_PATH.'/session')) {
      echo "<p>세션 디렉토리를 열지못했습니다.</p>";
    } else {
        $list_tag_st = "<ul class=\"session_del\">\n<li>완료됨</li>\n";
        $list_tag_end = "</ul>\n";
    }

    $cnt=0;
    echo $list_tag_st;
    while($file=readdir($dir)) {

        if (strpos($file,'sess_') === false) continue;
        if (strpos($file,'sess_')!=0) continue;

        $session_file = G5_DATA_PATH.'/session/'.$file;

        if (!$atime=@fileatime($session_file)) {
            continue;
        }
        if (time() > $atime + (3600 * 6)) {  // 지난시간을 초로 계산해서 적어주시면 됩니다. default : 6시간전
            $cnt++;
            $return = unlink($session_file);
            //echo "<script>document.getElementById('ct').innerHTML += '{$session_file}<br/>';</script>\n";
            echo "<li>{$session_file}</li>\n";

            flush();

            if ($cnt%10==0)
                //echo "<script>document.getElementById('ct').innerHTML = '';</script>\n";
                echo "\n";
        }
    }
    echo $list_tag_end;
    echo '<div class="local_desc01 local_desc"><p><strong>세션데이터 '.$cnt.'건 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>'.PHP_EOL;
?>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/session_file_delete — modern shell wrap end