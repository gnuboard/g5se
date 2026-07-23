<?php
/*
 * /admin/member_list_file_delete — 회원관리파일 일괄삭제 (스트리밍 출력).
 */
$sub_menu = '100930';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

if ($is_admin !== 'super') {
    header('Location: '.G5_URL, true, 302);
    exit;
}

require_once __DIR__.'/admin.lib.php';

$g5['title'] = '회원관리파일 일괄삭제';
admin_layout_start($g5['title'], 'member_list_file_delete');
?>
<main class="maintenance-result-page member-file-delete-page flex-1 p-4 sm:p-6 lg:p-8 w-full">
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

if (!$dir = @opendir(G5_DATA_PATH . '/member_list')) {
    echo '<p>회원관리파일를 열지못했습니다.</p>';
}

$cnt = 0;
echo '<ul class="session_del">' . PHP_EOL;

$files = glob(G5_DATA_PATH . '/member_list/*');
$cnt = 0;

// 폴더 및 하위 파일 재귀 삭제 함수
function deleteFolder($folderPath) {
    $items = glob($folderPath . '/*');
    foreach ($items as $item) {
        if (is_dir($item)) {
            deleteFolder($item);
        } else {
            unlink($item);
        }
    }
    rmdir($folderPath); // 폴더 자체 삭제
}

if (is_array($files)) {
    foreach ($files as $member_list_file) {
        // log 확장자가 아닌 파일/디렉토리 처리
        $ext = strtolower(pathinfo($member_list_file, PATHINFO_EXTENSION));
        $basename = basename($member_list_file);

        if (is_file($member_list_file) && $ext !== 'log') {
            unlink($member_list_file);
            $cnt++;
            echo '<li class="session-del-item">';
            echo '<span class="session-del-index">'.$cnt.'</span>';
            echo '<span class="session-del-file"><span class="session-del-kind">파일</span>'.htmlspecialchars($basename, ENT_QUOTES, 'UTF-8').'</span>';
            echo '<code class="session-del-path">'.htmlspecialchars($member_list_file, ENT_QUOTES, 'UTF-8').'</code>';
            echo '</li>'.PHP_EOL;
        } elseif (is_dir($member_list_file) && $basename !== 'log') {
            deleteFolder($member_list_file);
            $cnt++;
            echo '<li class="session-del-item">';
            echo '<span class="session-del-index">'.$cnt.'</span>';
            echo '<span class="session-del-file"><span class="session-del-kind session-del-kind-folder">폴더</span>'.htmlspecialchars($basename, ENT_QUOTES, 'UTF-8').'</span>';
            echo '<code class="session-del-path">'.htmlspecialchars($member_list_file, ENT_QUOTES, 'UTF-8').'</code>';
            echo '</li>'.PHP_EOL;
        }

        flush();

        if ($cnt % 10 == 0) {
            echo PHP_EOL;
        }
    }
}
echo '<li class="session-del-complete"><span aria-hidden="true">✓</span> 완료됨</li></ul>' . PHP_EOL;
echo '<div class="local_desc01 local_desc"><p><strong>회원관리파일 ' . $cnt . '건 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>' . PHP_EOL;
?>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/member_list_file_delete — modern shell wrap end
