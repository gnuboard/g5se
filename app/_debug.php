<?php
include_once('./_common.php');

header('Content-Type: text/plain; charset=utf-8');

echo "=== gnu5se debug ===\n";
echo "G5_PATH       : ".G5_PATH."\n";
echo "G5_URL        : ".G5_URL."\n";
echo "G5_BBS_URL    : ".G5_BBS_URL."\n";
echo "G5_HTTPS_BBS_URL: ".G5_HTTPS_BBS_URL."\n";
echo "G5_SKIN_URL   : ".G5_SKIN_URL."\n";
echo "G5_DATA_URL   : ".G5_DATA_URL."\n";
echo "G5_BBS_PATH   : ".G5_BBS_PATH."\n";
echo "G5_DATA_PATH  : ".G5_DATA_PATH."\n";
echo "G5_SKIN_PATH  : ".G5_SKIN_PATH."\n";
echo "is_member     : ".(isset($is_member) ? var_export($is_member, true) : 'undefined')."\n";
echo "member id     : ".(isset($member['mb_id']) ? $member['mb_id'] : '')."\n";
echo "session id    : ".session_id()."\n";
