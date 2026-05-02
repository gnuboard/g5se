<?php
include_once("_common.php");

/**
 * 현재 세션에 저장된 캡차 숫자에 해당하는 mp3 파일들을 메모리에서 합쳐 반환.
 * (디스크에 캐시 파일을 쓰지 않음)
 *
 * @return string 합쳐진 mp3 바이트. 세션이 없거나 mp3 파일을 못 찾으면 빈 문자열.
 */
function build_captcha_mp3_bytes()
{
    global $config;

    $number = get_session("ss_captcha_key");
    if ($number == "") return '';

    $ip = md5(sha1($_SERVER['REMOTE_ADDR']));
    if ($number && function_exists('get_string_decrypt')) {
        $number = str_replace($ip, '', get_string_decrypt($number));
    }

    $contents = '';
    for ($i = 0; $i < strlen($number); $i++) {
        $file = G5_CAPTCHA_PATH.'/mp3/'.$config['cf_captcha_mp3'].'/'.$number[$i].'.mp3';
        if (is_file($file)) {
            $contents .= file_get_contents($file);
        }
    }
    return $contents;
}

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

if ($method === 'POST') {
    // 클라이언트 JS 가 POST 로 호출 → URL 만 반환. 받은 URL 은 <audio src> 에 박힘.
    // (기존 동작 호환: kcaptcha.js 가 응답을 그대로 audio src 로 사용. JS 가 ?t=timestamp 를
    //  덧붙이므로 우리는 query 없이 베이스 URL 만 반환)
    echo G5_URL.'/plugin/kcaptcha/kcaptcha_mp3.php';

    // 옛 버전이 남긴 캐시 파일 정리 (1% 확률, 24시간 지난 것들)
    if (function_exists('glob') && rand(0, 99) == 0) {
        foreach (glob(G5_DATA_PATH.'/cache/kcaptcha-*.mp3') as $file) {
            if (filemtime($file) + 86400 < G5_SERVER_TIME) {
                @unlink($file);
            }
        }
    }
    exit;
}

// GET: 메모리에서 mp3 바이트 만들어 audio/mpeg 으로 즉시 스트리밍
$bytes = build_captcha_mp3_bytes();

if ($bytes === '') {
    http_response_code(404);
    exit;
}

header('Content-Type: audio/mpeg');
header('Content-Length: '.strlen($bytes));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo $bytes;
exit;
