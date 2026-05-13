<?php
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

$admin_csrf_token_key = isset($_POST['admin_csrf_token_key']) ? $_POST['admin_csrf_token_key'] : '';

if(function_exists('admin_csrf_token_key') && $admin_csrf_token_key !== admin_csrf_token_key(1)){
    die(json_encode(array('error' => '토큰키 에러!', 'url' => G5_URL)));
}

$error = admin_referer_check(true);
if ($error) {
    die(json_encode(array('error' => $error, 'url' => G5_URL)));
}

$token = get_admin_token();

die(json_encode(array('error' => '', 'token' => $token, 'url' => '')));
