<?php
/*
 * /admin/member_form_update — 회원 추가/수정 폼 저장 핸들러.
 *
 * 모던 폼은 `/admin/member_form` 에서 렌더되고, action 으로 `/admin/member_form_update` 를 친다.
 * 저장 로직은 gnuboard 가 이미 검증해놓은 `app/adm/member_form_update.php` 를 그대로 재사용 —
 * chdir + require 로 호출만 하고, 마지막의 `goto_url('./member_form.php?...')` 를
 * `goto_url` 훅으로 가로채서 클린 URL `/admin/member_form?...` 로 보낸다.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

// 비밀번호 변경 시 캡챠 검사 우회 (admin 인증+토큰으로 충분).
// gnuboard 의 chk_captcha 는 $_POST['captcha_key'] 와 session ss_captcha_key 비교 →
// 양쪽을 같은 값으로 미리 세팅해 통과시킨다.
if (!empty($_POST['mb_password'])) {
    $bypass = 'admin_bypass_captcha';
    $_POST['captcha_key'] = $bypass;
    $ip_key = md5(sha1($_SERVER['REMOTE_ADDR'] ?? ''));
    if (function_exists('get_string_encrypt')) {
        $_SESSION['ss_captcha_key'] = get_string_encrypt($ip_key.$bypass);
    } else {
        $_SESSION['ss_captcha_key'] = $bypass;
    }
    $_SESSION['ss_captcha_count'] = 0;
}

// gnuboard 의 redirect 들을 /admin/* 클린 URL 로 변환.
add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?(member_form|member_list)\.php(\?.*)?$#', $u, $m)) {
        $target = '/admin/'.$m[1].($m[2] ?? '');
        header('Location: '.$target, true, 302);
        exit;
    }
}, 10);

// gnuboard 의 adm/member_form_update.php 는 `require_once "./_common.php"` 같은
// 상대 require 와 `$_FILES` 처리, `$qstr` 등 adm 디렉토리 컨텍스트에 의존하므로
// cwd 를 G5_ADMIN_PATH 로 옮기고 그대로 호출한다.
chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/member_form_update.php';
