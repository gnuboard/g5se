<?php
include_once("./_common.php");

if (!$is_member) die("0");

$as_id = isset($_REQUEST['as_id']) ? (int)$_REQUEST['as_id'] : 0;

$result = sql_pdo_query(" delete from {$g5['autosave_table']} where mb_id = :mb_id and as_id = :as_id ",
                        [':mb_id' => $member['mb_id'], ':as_id' => $as_id]);
if (!$result) {
    echo "-1";
}

echo autosave_count($member['mb_id']);