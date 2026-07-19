<?php
include_once('./_common.php');

if ($is_guest)
    alert('로그인 한 회원만 접근하실 수 있습니다.', G5_BBS_URL.'/login.php');

while (1) {
    $tmp = preg_replace('/&#[^;]+;/', '', $url);
    if ($tmp == $url) break;
    $url = $tmp;
}

//소셜 로그인 한 경우
if( function_exists('social_member_comfirm_redirect') && (! $url || $url === 'register_form.php' || (function_exists('social_is_edit_page') && social_is_edit_page($url) ) ) ){    
    social_member_comfirm_redirect();
}

$url = run_replace('member_confirm_next_url', $url);

// 다음 경로 없이 직접 접근한 경우에도 회원정보 수정 확인 흐름으로 이어간다.
// 빈 action 으로 현재 페이지에 다시 POST되면 비밀번호 검증이 실행되지 않는다.
if (!$url) {
    $url = G5_BBS_URL.'/register_form.php';
}

$g5['title'] = '회원 비밀번호 확인';
include_once('./_head.sub.php');

// url 체크
check_url_host($url, '', G5_URL, true);

if($url){
    $url = preg_replace('#^/\\\{1,}#', '/', $url);

    if( preg_match('#^/{3,}#', $url) ){
        $url = preg_replace('#^/{3,}#', '/', $url);
    }

    if (function_exists('safe_filter_url_host')) {
        $url = safe_filter_url_host($url);
    }
}

$url = get_text($url);

include_once($member_skin_path.'/member_confirm.skin.php');

include_once('./_tail.sub.php');
