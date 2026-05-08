<?php
@header('Content-Type: text/html; charset=utf-8');
@header('X-Robots-Tag: noindex');
$g5_path['path'] = '..';
include_once('install_common.php');
include_once ('../config.php');
$title = "그누보드5SE 설치 — 라이센스 확인";
include_once ('./install.inc.php');
?>

<h1>라이센스 (License)</h1>

<?php
if ($exists_data_dir && $write_data_dir) {
    // 필수 모듈 체크
    require_once('./library.check.php');

    $_license_text = '';
    if (is_file('../LICENSE.txt')) {
        $_license_text = file_get_contents('../LICENSE.txt');
    } else {
        $_license_text = "LICENSE.txt 파일을 찾을 수 없습니다. 설치를 계속하기 전 루트에 LICENSE.txt 가 있어야 합니다.";
    }
?>
<form action="./install_config.php" method="post" onsubmit="return frm_submit(this);">

<div class="ins_inner">
    <p>
        <strong class="st_strong">라이센스 내용을 확인하시고 동의 후 설치를 진행하세요.</strong>
    </p>
    <p>
        그누보드5SE 는 <strong>MIT License</strong> 로 배포되며, gnuboard5 (GPL v2) 를 기반으로 합니다.
    </p>

    <div class="ins_ta ins_license">
        <textarea name="textarea" id="ins_license" readonly><?php echo htmlspecialchars($_license_text, ENT_QUOTES); ?></textarea>
    </div>

    <div id="ins_agree">
        <input type="checkbox" name="agree" value="동의함" id="agree">
        <label for="agree">위 라이센스 내용에 동의합니다.</label>
    </div>

    <div class="inner_btn">
        <input type="submit" value="다음 단계 →">
    </div>
</div>

</form>

<script>
function frm_submit(f)
{
    if (!f.agree.checked) {
        alert("라이센스 내용에 동의하셔야 설치가 가능합니다.");
        return false;
    }
    return true;
}
</script>
<?php
} // if
?>

<?php
include_once ('./install.inc2.php');
