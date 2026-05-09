<?php
/*
 * /admin/software_update - 이전 주소 호환용 리다이렉트.
 */
$sub_menu = '100415';
require_once __DIR__.'/_common.php';

goto_url(G5_ADMIN_URL . '/version_check');
