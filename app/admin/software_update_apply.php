<?php
/*
 * /admin/software_update_apply - 자동 업데이트 적용은 제공하지 않음.
 */

$sub_menu = '100415';
require_once __DIR__.'/_common.php';
admin_require_login();

if ($is_admin !== 'super') {
    alert('최고관리자만 접근 가능합니다.', G5_ADMIN_URL);
}

check_admin_token();
alert('자동 업데이트 적용은 제공하지 않습니다. 버전 확인 화면에서 릴리스 정보를 확인하십시오.', G5_ADMIN_URL . '/version_check');
