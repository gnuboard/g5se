<?php
/*
 * /admin/menu_form_search — 메뉴 모달의 게시판그룹 / 게시판 / 내용관리 검색용 JSON.
 *   GET ?type=group|board|content
 *   응답: { items: [{ subject, link, group? }] }
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_ADMIN_PATH.'/admin.lib.php';

if ($is_admin !== 'super') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => '최고관리자만 접근 가능합니다.']);
    exit;
}

$type = isset($_GET['type']) ? preg_replace('/[^0-9a-z_]/i', '', (string)$_GET['type']) : '';

$items = [];
switch ($type) {
    case 'group':
        $r = sql_pdo_query(" select gr_id as id, gr_subject as subject from {$g5['group_table']} order by gr_order, gr_id ");
        while ($row = sql_pdo_fetch_array($r)) {
            $items[] = [
                'subject' => preg_replace('/[\'\"]/', '', $row['subject']),
                'link'    => G5_BBS_URL.'/group.php?gr_id='.$row['id'],
            ];
        }
        break;
    case 'board':
        $r = sql_pdo_query(" select bo_table as id, bo_subject as subject, gr_id from {$g5['board_table']} order by bo_order, bo_table ");
        while ($row = sql_pdo_fetch_array($r)) {
            $g = get_call_func_cache('get_group', [$row['gr_id']]);
            $items[] = [
                'subject' => preg_replace('/[\'\"]/', '', $row['subject']),
                'link'    => get_pretty_url($row['id']),
                'group'   => $g['gr_subject'] ?? $row['gr_id'],
            ];
        }
        break;
    case 'content':
        if (isset($g5['content_table']) && sql_pdo_query(" describe {$g5['content_table']} ", [], false)) {
            $r = sql_pdo_query(" select co_id as id, co_subject as subject from {$g5['content_table']} order by co_id ");
            while ($row = sql_pdo_fetch_array($r)) {
                $items[] = [
                    'subject' => preg_replace('/[\'\"]/', '', $row['subject']),
                    'link'    => get_pretty_url(G5_CONTENT_DIR, $row['id']),
                ];
            }
        }
        break;
    default:
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'invalid type']);
        exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
