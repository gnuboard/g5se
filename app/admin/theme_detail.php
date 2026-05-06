<?php
/*
 * /admin/theme_detail — 테마 상세정보 popup HTML 응답 (admin shell wrap 없음).
 */
$sub_menu = "100280";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if ($is_admin !== 'super') {
    die('최고관리자만 접근 가능합니다.');
}

$theme = trim($_POST['theme']);
$theme_dir = get_theme_dir();

if(!in_array($theme, $theme_dir))
    die('선택하신 테마가 설치되어 있지 않습니다.');

$info = get_theme_info($theme);
$name = get_text($info['theme_name']);

if($info['screenshot'])
    $screenshot = '<img src="'.$info['screenshot'].'" alt="'.$name.'">';
else
    $screenshot = '<img src="'.G5_ADMIN_URL.'/img/theme_img.jpg" alt="">';

if($info['theme_uri']) {
    $name = '<a href="'.set_http($info['theme_uri']).'" target="_blank" class="thdt_home">'.$name.'</a>';
}

$maker = get_text($info['maker']);
if($info['maker_uri']) {
    $maker = '<a href="'.set_http($info['maker_uri']).'" target="_blank" class="thdt_home">'.$maker.'</a>';
}

$license = get_text($info['license']);
if($info['license_uri']) {
    $license = '<a href="'.set_http($info['license_uri']).'" target="_blank" class="thdt_home">'.$license.'</a>';
}
?>

<div id="theme_detail">
    <h2><?php echo $name; ?></h2>
    <div class="theme_dt_img"><?php echo $screenshot; ?></div>
    <div class="theme_dt_if">
        <p><?php echo get_text($info['detail']); ?></p>
        <table>
            <tr>
                <th scope="row">Version</th>
                <td><?php echo get_text($info['version']); ?></td>
            </tr>
            <tr>
                <th scope="row">Maker</th>
                <td><?php echo $maker; ?></td>
            </tr>
            <tr>
                <th scope="row">License</th>
                <td><?php echo $license; ?></td>
            </tr>
        </table>
        <div class="theme_dt_btn">
        <a href="<?php echo G5_ADMIN_URL ?>/theme_preview?theme=<?php echo $theme; ?>" class="theme_pr btn_03" target="theme_preview">미리보기</a>
        <button type="button" class="close_btn">닫기</button>
        </div>
    </div>
</div>

<script>
$(".close_btn").on("click", function() {
    $("#theme_detail").remove();
});
</script>