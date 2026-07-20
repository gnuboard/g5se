<?php

/********************
    상수 선언
********************/

// 이 상수가 정의되지 않으면 각각의 개별 페이지는 별도로 실행될 수 없음
if (!defined('_GNUBOARD_')) define('_GNUBOARD_', true);

/*
 * 사용자 환경 설정 (G5_DOMAIN, G5_TIMEZONE, G5_SMTP, G5_DEBUG, G5_DB_CHARSET 등) 은 data/user_config.php 로 분리.
 * 여기는 user_config.php 가 정의 안 한 경우의 framework default 만. defined() 가드로 보호.
 *
 *   사용자 변경 → data/user_config.php 에서 수정 (자동 업데이트 시 보존됨)
 *   여기 (app/config.php) 는 절대 수정 X — 자동 업데이트 시 덮어써짐
 */
$_user_config = dirname($g5_path['path']).'/data/user_config.php';
if (is_file($_user_config)) include_once($_user_config);
unset($_user_config);

include_once($g5_path['path'].'/version.php');   // 설정 파일

// 기본 시간대 설정
if (!defined('G5_TIMEZONE'))       define('G5_TIMEZONE',      'Asia/Seoul');
date_default_timezone_set(G5_TIMEZONE);

/********************
    경로 상수
********************/

// 보안서버 도메인 (https 시작, 회원가입/글쓰기 등에서 사용). '' 면 일반 G5_URL 사용.
//   예: 'https://www.domain.com:443/gnuboard5' (뒤 / 없음)
if (!defined('G5_DOMAIN'))         define('G5_DOMAIN',        '');
if (!defined('G5_HTTPS_DOMAIN'))   define('G5_HTTPS_DOMAIN',  '');

// 디버그바 / 쿼리 수집 — 운영 시 false
if (!defined('G5_DEBUG'))          define('G5_DEBUG',         false);
if (!defined('G5_COLLECT_QUERY'))  define('G5_COLLECT_QUERY', false);

// DB 기본 storage engine — '' (DB 기본값) / 'InnoDB' / 'MyISAM'
if (!defined('G5_DB_ENGINE'))      define('G5_DB_ENGINE',     '');

// DB 기본 charset — 'utf8' / 'utf8mb4' (이모지 지원, MySQL/MariaDB 5.5+)
if (!defined('G5_DB_CHARSET'))     define('G5_DB_CHARSET',    'utf8mb4');

// 쿠키 도메인 — 서브도메인 간 로그인 공유. '.example.com' 식. '' 면 정확한 도메인만.
if (!defined('G5_COOKIE_DOMAIN'))  define('G5_COOKIE_DOMAIN', '');

// 사용기기 설정 — pc / mobile / both. G5_USE_MOBILE=false 이면 both 여도 레거시 모바일 화면은 사용하지 않음.
if (!defined('G5_SET_DEVICE'))     define('G5_SET_DEVICE',    'both');
if (!defined('G5_USE_MOBILE'))     define('G5_USE_MOBILE',    false);
if (!defined('G5_USE_CACHE'))      define('G5_USE_CACHE',     true);

// SMTP — lib/mailer.lib.php 에서 사용. G5_SMTP 가 빈 문자열이면 PHP mail() 기본 전송 사용.
if (!defined('G5_SMTP'))           define('G5_SMTP',          '127.0.0.1');
if (!defined('G5_SMTP_PORT'))      define('G5_SMTP_PORT',     '25');
if (!defined('G5_SMTP_SECURE'))    define('G5_SMTP_SECURE',   '');
if (!defined('G5_SMTP_AUTH'))      define('G5_SMTP_AUTH',     false);
if (!defined('G5_SMTP_USER'))      define('G5_SMTP_USER',     '');
if (!defined('G5_SMTP_PASS'))      define('G5_SMTP_PASS',     '');
if (!defined('G5_SMTP_AUTO_TLS'))  define('G5_SMTP_AUTO_TLS', false);

define('G5_DBCONFIG_FILE',  'dbconfig.php');

define('G5_ADMIN_DIR',      'admin');   // legacy 'adm' → modern 'admin' 로 canonical 이동
define('G5_BBS_DIR',        'bbs');
define('G5_CSS_DIR',        'css');
define('G5_DATA_DIR',       'data');
define('G5_EXTEND_DIR',     'extend');
define('G5_IMG_DIR',        'img');
define('G5_JS_DIR',         'js');
define('G5_LIB_DIR',        'lib');
define('G5_PLUGIN_DIR',     'plugin');
define('G5_SKIN_DIR',       'skin');
define('G5_EDITOR_DIR',     'editor');
define('G5_MOBILE_DIR',     'mobile');
define('G5_OKNAME_DIR',     'okname');

define('G5_KCPCERT_DIR',    'kcpcert');
define('G5_INICERT_DIR',     'inicert');
define('G5_LGXPAY_DIR',     'lgxpay');

define('G5_SNS_DIR',        'sns');
define('G5_SYNDI_DIR',      'syndi');
define('G5_SESSION_DIR',    'session');
define('G5_THEME_DIR',      'theme');

define('G5_GROUP_DIR',      'group');
define('G5_CONTENT_DIR',    'content');

// g5se 구조 보정: 프런트 컨트롤러를 거치지 않는 직접접근 진입점
// (예: plugin/editor 업로드, kcaptcha 등) 에서도 하위 설치 경로(/g5se 등)를 보존한다.
// 자동탐지(g5_path)는 내부 rewrite 경로(app/plugin/...)를 볼 수 있어 REQUEST_URI 기준으로 계산한다.
if (!defined('G5_URL') && isset($_SERVER['HTTP_HOST'])) {
    $_g5se_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $_g5se_host = preg_replace('/[^a-zA-Z0-9\.\-:]/', '', $_SERVER['HTTP_HOST']);
    $_g5se_request_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $_g5se_base_path = '';
    if (preg_match('#^(.+?)/(?:plugin|theme|skin|img|js|css|mobile|shop|admin|install|data)(?:/|$)#', $_g5se_request_path, $_g5se_match)) {
        $_g5se_base_path = rtrim($_g5se_match[1], '/');
    }
    if ($_g5se_base_path === '/') $_g5se_base_path = '';
    if (!defined('G5SE_BASE_PATH')) define('G5SE_BASE_PATH', $_g5se_base_path);
    define('G5_URL', $_g5se_scheme.'://'.$_g5se_host.$_g5se_base_path);
    unset($_g5se_scheme, $_g5se_host, $_g5se_request_path, $_g5se_base_path, $_g5se_match);
}

// URL 은 브라우저상에서의 경로 (도메인으로 부터)
if (!defined('G5_URL')) {
    if (G5_DOMAIN) {
        define('G5_URL', G5_DOMAIN);
    } else {
        if (isset($g5_path['url']))
            define('G5_URL', $g5_path['url']);
        else
            define('G5_URL', '');
    }
}

if (!defined('G5_PATH') && isset($g5_path['path'])) {
    define('G5_PATH', $g5_path['path']);
} else if (!defined('G5_PATH')) {
    define('G5_PATH', '');
}

define('G5_ADMIN_URL',      G5_URL.'/'.G5_ADMIN_DIR);
if (!defined('G5_BBS_URL')) define('G5_BBS_URL', G5_URL.'/'.G5_BBS_DIR);
define('G5_CSS_URL',        G5_URL.'/'.G5_CSS_DIR);
if (!defined('G5_DATA_URL')) define('G5_DATA_URL', G5_URL.'/'.G5_DATA_DIR);
define('G5_IMG_URL',        G5_URL.'/'.G5_IMG_DIR);
define('G5_JS_URL',         G5_URL.'/'.G5_JS_DIR);
define('G5_SKIN_URL',       G5_URL.'/'.G5_SKIN_DIR);
define('G5_PLUGIN_URL',     G5_URL.'/'.G5_PLUGIN_DIR);
define('G5_EDITOR_URL',     G5_PLUGIN_URL.'/'.G5_EDITOR_DIR);
define('G5_OKNAME_URL',     G5_PLUGIN_URL.'/'.G5_OKNAME_DIR);
define('G5_KCPCERT_URL',    G5_PLUGIN_URL.'/'.G5_KCPCERT_DIR);
define('G5_INICERT_URL',     G5_PLUGIN_URL.'/'.G5_INICERT_DIR);
define('G5_LGXPAY_URL',     G5_PLUGIN_URL.'/'.G5_LGXPAY_DIR);
define('G5_SNS_URL',        G5_PLUGIN_URL.'/'.G5_SNS_DIR);
define('G5_SYNDI_URL',      G5_PLUGIN_URL.'/'.G5_SYNDI_DIR);
define('G5_MOBILE_URL',     G5_URL.'/'.G5_MOBILE_DIR);

// PATH 는 서버상에서의 절대경로
define('G5_ADMIN_PATH',     G5_PATH.'/'.G5_ADMIN_DIR);
define('G5_BBS_PATH',       G5_PATH.'/'.G5_BBS_DIR);
// g5se 구조: data/ 가 app/ 밖(docroot 직속)에 있음. 프런트 컨트롤러를 거치지 않는 진입점
// (예: plugin/kcaptcha/kcaptcha_image.php) 에서도 올바른 경로가 잡히도록 dirname(G5_PATH) 기준.
if (!defined('G5_DATA_PATH')) define('G5_DATA_PATH', dirname(G5_PATH).'/'.G5_DATA_DIR);
define('G5_EXTEND_PATH',    G5_PATH.'/'.G5_EXTEND_DIR);
define('G5_LIB_PATH',       G5_PATH.'/'.G5_LIB_DIR);
define('G5_PLUGIN_PATH',    G5_PATH.'/'.G5_PLUGIN_DIR);
define('G5_SKIN_PATH',      G5_PATH.'/'.G5_SKIN_DIR);
define('G5_MOBILE_PATH',    G5_PATH.'/'.G5_MOBILE_DIR);
define('G5_SESSION_PATH',   G5_DATA_PATH.'/'.G5_SESSION_DIR);
define('G5_EDITOR_PATH',    G5_PLUGIN_PATH.'/'.G5_EDITOR_DIR);
define('G5_OKNAME_PATH',    G5_PLUGIN_PATH.'/'.G5_OKNAME_DIR);

define('G5_KCPCERT_PATH',   G5_PLUGIN_PATH.'/'.G5_KCPCERT_DIR);
define('G5_INICERT_PATH',   G5_PLUGIN_PATH.'/'.G5_INICERT_DIR);
define('G5_LGXPAY_PATH',    G5_PLUGIN_PATH.'/'.G5_LGXPAY_DIR);

define('G5_SNS_PATH',       G5_PLUGIN_PATH.'/'.G5_SNS_DIR);
define('G5_SYNDI_PATH',     G5_PLUGIN_PATH.'/'.G5_SYNDI_DIR);
//==============================================================================


/********************
    시간 상수
********************/
// 서버의 시간과 실제 사용하는 시간이 틀린 경우 수정하세요.
// 하루는 86400 초입니다. 1시간은 3600초
// 6시간이 빠른 경우 time() + (3600 * 6);
// 6시간이 느린 경우 time() - (3600 * 6);
if (!defined('G5_SERVER_TIME_OFFSET')) define('G5_SERVER_TIME_OFFSET', 0);
if (!defined('G5_SERVER_TIME')) define('G5_SERVER_TIME', time() + G5_SERVER_TIME_OFFSET);
define('G5_TIME_YMDHIS',    date('Y-m-d H:i:s', G5_SERVER_TIME));
define('G5_TIME_YMD',       substr(G5_TIME_YMDHIS, 0, 10));
define('G5_TIME_HIS',       substr(G5_TIME_YMDHIS, 11, 8));

// 입력값 검사 상수 (숫자를 변경하시면 안됩니다.)
define('G5_ALPHAUPPER',      1); // 영대문자
define('G5_ALPHALOWER',      2); // 영소문자
define('G5_ALPHABETIC',      4); // 영대,소문자
define('G5_NUMERIC',         8); // 숫자
define('G5_HANGUL',         16); // 한글
define('G5_SPACE',          32); // 공백
define('G5_SPECIAL',        64); // 특수문자

// SEO TITLE 문단 길이
define('G5_SEO_TITLE_WORD_CUT', 8);        // SEO TITLE 문단 길이

// 퍼미션
define('G5_DIR_PERMISSION',  0755); // 디렉토리 생성시 퍼미션
define('G5_FILE_PERMISSION', 0644); // 파일 생성시 퍼미션

// 모바일 인지 결정 $_SERVER['HTTP_USER_AGENT']
define('G5_MOBILE_AGENT',   'phone|samsung.*mobile|lgtel|mobile|[^A]skt|nokia|blackberry|BB10|android|sony');

/********************
    기타 상수
********************/

// 암호화 함수 지정
// 사이트 운영 중 설정을 변경하면 로그인이 안되는 등의 문제가 발생합니다.
// 5.4 버전 이전에는 sql_password 이 사용됨, 5.4 버전부터 기본이 create_hash 로 변경
//define('G5_STRING_ENCRYPT_FUNCTION', 'sql_password');
define('G5_STRING_ENCRYPT_FUNCTION', 'create_hash');
define('G5_MYSQL_PASSWORD_LENGTH', 41);         // mysql password length 41, old_password 의 경우에는 16

// SQL 에러를 표시할 것인지 지정
// 에러를 표시하려면 true 로 변경
define('G5_DISPLAY_SQL_ERROR', false);

// escape string 처리 함수 지정
// addslashes 로 변경 가능
define('G5_ESCAPE_FUNCTION', 'sql_escape_string');

// sql_escape_string 함수에서 사용될 패턴
//define('G5_ESCAPE_PATTERN',  '/(and|or).*(union|select|insert|update|delete|from|where|limit|create|drop).*/i');
//define('G5_ESCAPE_REPLACE',  '');

// 게시판에서 링크의 기본개수를 말합니다.
// 필드를 추가하면 이 숫자를 필드수에 맞게 늘려주십시오.
define('G5_LINK_COUNT', 2);

// 썸네일 jpg Quality 설정
define('G5_THUMB_JPG_QUALITY', 90);

// 썸네일 png Compress 설정
define('G5_THUMB_PNG_COMPRESS', 5);

// 모바일 기기에서 DHTML 에디터 사용여부를 설정합니다.
define('G5_IS_MOBILE_DHTML_USE', false);

// MySQLi 사용여부를 설정합니다.
define('G5_MYSQLI_USE', true);

// Browscap 사용여부를 설정합니다.
define('G5_BROWSCAP_USE', true);

// 접속자 기록 때 Browscap 사용여부를 설정합니다.
define('G5_VISIT_BROWSCAP_USE', false);

// ip 숨김방법 설정
/* 123.456.789.012 ip의 숨김 방법을 변경하는 방법은
\\1 은 123, \\2는 456, \\3은 789, \\4는 012에 각각 대응되므로
표시되는 부분은 \\1 과 같이 사용하시면 되고 숨길 부분은 ♡등의
다른 문자를 적어주시면 됩니다.
*/
define('G5_IP_DISPLAY', '\\1.♡.\\3.\\4');

// KAKAO 우편번호 서비스 CDN
define('G5_POSTCODE_JS', '<script src="//t1.kakaocdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js" async></script>');
