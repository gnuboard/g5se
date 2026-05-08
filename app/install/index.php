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

    // LICENSE — docroot 루트 (GitHub 표준 확장자 없는 파일명).
    // install/ CWD 기준 ../ 는 app/ 라 잘못 잡힘 → 절대경로 사용.
    $_license_path = dirname(dirname(__DIR__)).'/LICENSE';
    $_license_text = '';
    if (is_file($_license_path)) {
        $_license_text = file_get_contents($_license_path);
    } else {
        $_license_text = "LICENSE 파일을 찾을 수 없습니다.\n\n예상 경로: $_license_path\n\n설치를 계속하기 전 docroot 루트에 LICENSE 파일이 있어야 합니다.";
    }
?>
<form action="./install_config" method="post" onsubmit="return frm_submit(this);">

<div class="ins_inner">
    <p style="font-size: 1.1em;">
        그누보드5SE 는 <strong style="color: var(--ins-primary);">MIT License</strong> 로 배포됩니다.
    </p>
    <p>
        gnuboard5 (GPL v2) 를 기반으로 한 second edition 으로, 본 second edition 자체는 MIT 로 자유롭게 사용·수정·배포 가능합니다.
        아래 라이센스 본문을 확인하시고 동의 후 설치를 진행하세요.
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
