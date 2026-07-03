<?php
if (!defined('_GNUBOARD_')) exit;

// 설문조사
function poll($skin_dir='basic', $po_id=false)
{
    global $config, $member, $g5, $is_admin;

    // 투표번호가 넘어오지 않았다면 가장 큰(최근에 등록한) 투표번호를 얻는다
    if (!$po_id) {
        $row = sql_pdo_fetch(" select MAX(po_id) as max_po_id from {$g5['poll_table']} where po_use = 1 ", [], false);
        $po_id = isset($row['max_po_id']) ? $row['max_po_id'] : 0;
    }

    if(!$po_id)
        return;

    // $skin_dir 은 스킨 안에서 hidden 필드로 그대로 재사용되므로 정규화하지 않는다
    $poll_skin_path = get_skin_path('poll', $skin_dir);
    $poll_skin_url  = get_skin_url('poll', $skin_dir);

    $po = sql_pdo_fetch(" select * from {$g5['poll_table']} where po_id = :po_id and po_use = 1 ",
                        [':po_id' => $po_id]);

    ob_start();
    include_once ($poll_skin_path.'/poll.skin.php');
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}