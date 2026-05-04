<?php
include_once('./_common.php');

if (!$is_member) die('');

$as_id = isset($_REQUEST['as_id']) ? (int) $_REQUEST['as_id'] : 0;

$row = sql_pdo_fetch(" select as_subject, as_content from {$g5['autosave_table']} where mb_id = :mb_id and as_id = :as_id ",
                     [':mb_id' => $member['mb_id'], ':as_id' => $as_id]);
$subject = $row['as_subject'];
$content = $row['as_content'];

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<item>\n";
echo "<subject><![CDATA[{$subject}]]></subject>\n";
echo "<content><![CDATA[{$content}]]></content>\n";
echo "</item>\n";