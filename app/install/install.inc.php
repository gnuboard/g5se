<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// gnu5se: data 디렉토리는 docroot 루트 (app 밖). __DIR__ = app/install 이므로
// dirname(dirname(__DIR__)) = docroot. G5_DATA_PATH 는 G5_PATH(='..') 기반이라
// CWD 의존 — install 단계에선 CWD 가 install/ 일 수 있어 부정확.
$data_path = dirname(dirname(__DIR__)).'/'.G5_DATA_DIR;

if (! (isset($title) && $title)) $title = "그누보드5SE 설치";

// 진행 단계 — index.php=1, install_config.php=2, install_db.php=3
$_step = 1;
$_self = basename($_SERVER['SCRIPT_NAME'] ?? '');
if ($_self === 'install_config.php') $_step = 2;
else if ($_self === 'install_db.php') $_step = 3;
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#2563eb">
<title><?php echo $title; ?></title>
<link rel="stylesheet" href="install.css">
<script>
// 다크모드 — 메인 사이트와 동일한 localStorage 키 'm-theme' 사용 (상태 공유).
// 저장 없으면 시스템 prefers-color-scheme 따라 명시 → CSS 단일 selector [data-theme="..."] 만 매칭.
(function () {
    try {
        var t = localStorage.getItem('m-theme');
        if (t !== 'dark' && t !== 'light') {
            t = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', t);
    } catch (e) {}
})();
</script>
</head>
<body>

<button type="button" class="ins-theme-toggle" id="ins-theme-toggle" aria-label="다크모드 전환" title="다크모드 전환">
    <svg class="ic-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    <svg class="ic-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
</button>
<script>
document.getElementById('ins-theme-toggle').addEventListener('click', function () {
    var html = document.documentElement;
    var cur = html.getAttribute('data-theme') || 'light';
    var next = cur === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    try { localStorage.setItem('m-theme', next); } catch (e) {}
});
</script>

<div id="ins_bar">
    <span id="bar_img">
        그누보드5SE
        <span class="bar_sub">GNUBOARD5 SECOND EDITION</span>
    </span>
    <span id="bar_txt">설치 마법사</span>
</div>

<div class="ins_steps" aria-label="설치 단계">
    <div class="ins_step <?php echo $_step === 1 ? 'is-active' : ($_step > 1 ? 'is-done' : ''); ?>">
        <span class="step_no">1</span>라이센스
    </div>
    <div class="ins_step <?php echo $_step === 2 ? 'is-active' : ($_step > 2 ? 'is-done' : ''); ?>">
        <span class="step_no">2</span>환경설정
    </div>
    <div class="ins_step <?php echo $_step === 3 ? 'is-active' : ''; ?>">
        <span class="step_no">3</span>설치 완료
    </div>
</div>

<?php
// 파일이 존재한다면 설치할 수 없다.
$dbconfig_file = $data_path.'/'.G5_DBCONFIG_FILE;
if (file_exists($dbconfig_file)) {
?>
<h1><?php echo G5_VERSION; ?> 프로그램이 이미 설치되어 있습니다.</h1>

<div class="ins_inner">
    <p>프로그램이 이미 설치되어 있습니다.<br />새로 설치하시려면 다음 파일을 삭제 하신 후 새로고침 하십시오.</p>
    <ul>
        <li><?php echo $dbconfig_file ?></li>
    </ul>
</div>
<?php
    exit;
}
?>

<?php
$exists_data_dir = true;
// data 디렉토리가 있는가?
if (!is_dir($data_path))
{
?>
<h1><?php echo G5_VERSION; ?> 설치를 위해 아래 내용을 확인해 주십시오.</h1>

<div class="ins_inner">
    <p>
        루트 디렉토리에 아래로 <?php echo G5_DATA_DIR ?> 디렉토리를 생성하여 주십시오.<br />
        (common.php 파일이 있는곳이 루트 디렉토리 입니다.)<br /><br />
        $> mkdir <?php echo G5_DATA_DIR ?><br /><br />
        윈도우의 경우 data 폴더를 하나 생성해 주시기 바랍니다.<br /><br />
        위 명령 실행후 브라우저를 새로고침 하십시오.
    </p>
</div>
<?php
    $exists_data_dir = false;
}
?>

<?php
$write_data_dir = true;
// data 디렉토리에 파일 생성 가능한지 검사.
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    $sapi_type = php_sapi_name();
    if (substr($sapi_type, 0, 3) == 'cgi') {
        if (!(is_readable($data_path) && is_executable($data_path)))
        {
        ?>
        <div class="ins_inner">
            <p>
                <?php echo G5_DATA_DIR ?> 디렉토리의 퍼미션을 705로 변경하여 주십시오.<br /><br />
                $> chmod 705 <?php echo G5_DATA_DIR ?> 또는 chmod uo+rx <?php echo G5_DATA_DIR ?><br /><br />
                위 명령 실행후 브라우저를 새로고침 하십시오.
            </p>
        </div>
        <?php
            $write_data_dir = false;
        }
    } else {
        if (!(is_readable($data_path) && is_writeable($data_path) && is_executable($data_path)))
        {
        ?>
        <div class="ins_inner">
            <p>
                <?php echo G5_DATA_DIR ?> 디렉토리의 퍼미션을 707로 변경하여 주십시오.<br /><br />
                $> chmod 707 <?php echo G5_DATA_DIR ?> 또는 chmod uo+rwx <?php echo G5_DATA_DIR ?><br /><br />
                위 명령 실행후 브라우저를 새로고침 하십시오.
            </p>
        </div>
        <?php
            $write_data_dir = false;
        }
    }
}