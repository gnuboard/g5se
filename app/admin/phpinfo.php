<?php
/*
 * /admin/phpinfo — phpinfo() 단독 출력 (admin shell wrap 없음).
 */
$sub_menu = "100500";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if (function_exists('check_demo')) {
    check_demo();
}

auth_check_menu($auth, $sub_menu, 'r');

phpinfo();
