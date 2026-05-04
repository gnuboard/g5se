<?php
include_once('./_common.php');

if (!$is_member) die('0');

$uid     = isset($_REQUEST['uid']) ? preg_replace('/[^0-9]/', '', $_REQUEST['uid']) : 0;
$subject = isset($_REQUEST['subject']) ? preg_replace("#[\\\]+$#", "", substr(trim($_POST['subject']),0,255)) : '';
$content = isset($_REQUEST['content']) ? preg_replace("#[\\\]+$#", "", substr(trim($_POST['content']),0,65536)) : '';

if ($subject && $content) {
    $row = sql_pdo_fetch(" select count(*) as cnt from {$g5['autosave_table']} where mb_id = :mb_id and as_subject = :subject and as_content = :content ",
                         [':mb_id' => $member['mb_id'], ':subject' => stripslashes($subject), ':content' => stripslashes($content)]);
    if (!$row['cnt']) {
        sql_pdo_query(" insert into {$g5['autosave_table']} set
                            mb_id       = :mb_id,
                            as_uid      = :as_uid,
                            as_subject  = :subject,
                            as_content  = :content,
                            as_datetime = :now
                        on duplicate key update
                            as_subject  = :subject,
                            as_content  = :content,
                            as_datetime = :now ",
                      [':mb_id' => $member['mb_id'], ':as_uid' => $uid,
                       ':subject' => stripslashes($subject), ':content' => stripslashes($content),
                       ':now' => G5_TIME_YMDHIS], false);

        echo autosave_count($member['mb_id']);
    }
}