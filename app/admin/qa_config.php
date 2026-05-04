<?php
/*
 * /admin/qa_config — 1:1 문의 설정 폼.
 * gnuboard adm/qa_config.php (412 라인) 를 chdir+require 로 그대로 렌더,
 * <form> 에 action 이 없으므로 ob_start 로 클린 URL 주입.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

ob_start();
chdir(G5_ADMIN_PATH);
require G5_ADMIN_PATH.'/qa_config.php';
$html = ob_get_clean();

$html = str_replace(
    '<form name="fqaconfigform" id="fqaconfigform" method="post"',
    '<form name="fqaconfigform" id="fqaconfigform" method="post" action="/admin/qa_config_update"',
    $html
);

echo $html;
