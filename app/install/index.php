<?php
@header('Content-Type: text/html; charset=utf-8');
@header('X-Robots-Tag: noindex');
$g5_path['path'] = '..';
include_once('install_common.php');
include_once ('../config.php');
$title = "그누보드5 SE 설치 — 라이센스 확인";
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

    $_license_ko_text = <<<'EOT'
MIT 라이센스

저작권 (c) 2026 (주)에스아이알소프트

본 소프트웨어 및 관련 문서 파일(이하 "소프트웨어")의 사본을 취득하는 모든 사람에게
소프트웨어를 제한 없이 사용할 수 있는 권한을 무상으로 허가합니다.
여기에는 소프트웨어의 사용, 복사, 수정, 병합, 게시, 배포, 재실시권 허여 및 판매 권리와
소프트웨어를 제공받은 사람이 동일하게 사용할 수 있도록 허가하는 권리가 포함됩니다.
다만 다음 조건을 따라야 합니다.

위 저작권 표시와 본 허가 표시는 소프트웨어의 모든 사본 또는 주요 부분에 포함되어야 합니다.

본 소프트웨어는 상품성, 특정 목적 적합성 및 권리 비침해에 대한 보증을 포함하되 이에 한정되지 않는
어떠한 명시적 또는 묵시적 보증 없이 "있는 그대로" 제공됩니다.
계약, 불법행위 또는 기타 사유와 관계없이, 소프트웨어 또는 소프트웨어의 사용이나 기타 거래로 인해
또는 그와 관련하여 발생하는 모든 청구, 손해 또는 기타 책임에 대해 저작자 또는 저작권자는 책임을 지지 않습니다.
EOT;
?>
<form action="./install_config" method="post" onsubmit="return frm_submit(this);">

<div class="ins_inner">
    <p style="font-size: 1.1em;">
        그누보드5 SE 는 <strong style="color: var(--ins-primary);">MIT License</strong> 로 배포됩니다.
    </p>
    <p>
        아래 라이센스 원문과 한글 번역본을 확인하시고 동의 후 설치를 진행하세요.
    </p>

    <div class="ins_ta ins_license">
        <strong class="ins_license_title">MIT License 원문</strong>
        <textarea name="textarea" id="ins_license" readonly><?php echo htmlspecialchars($_license_text, ENT_QUOTES); ?></textarea>
    </div>
    <div class="ins_ta ins_license">
        <strong class="ins_license_title">MIT License 한글 번역본</strong>
        <textarea readonly><?php echo htmlspecialchars($_license_ko_text, ENT_QUOTES); ?></textarea>
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
