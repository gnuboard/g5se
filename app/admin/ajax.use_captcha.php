<?php
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if (isset($_POST['admin_use_captcha'])) {
    set_session('ss_admin_use_captcha', true);
}
