<?php
/*
 * /admin/point_list_excel_download — 포인트 내역 구간별 XLSX 다운로드.
 */
$sub_menu = '200200';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once G5_ADMIN_PATH.'/admin.lib.php';
require_once dirname(__DIR__, 2).'/vendor/autoload.php';

auth_check_menu($auth, $sub_menu, 'r');

const POINT_EXCEL_CHUNK_SIZE = 50000;

// 5만 건 XLSX 생성에 필요한 메모리를 확보하되 처리 시간 제한은 해제한다.
@ini_set('memory_limit', '1024M');
@set_time_limit(0);

$chunk = max(1, isset($_GET['chunk']) ? (int)$_GET['chunk'] : 1);
$snapshot = max(0, isset($_GET['snapshot']) ? (int)$_GET['snapshot'] : 0);
$frDate = isset($_GET['fr_date']) ? (string)$_GET['fr_date'] : '';
$toDate = isset($_GET['to_date']) ? (string)$_GET['to_date'] : '';
$datePattern = '/^\d{4}-(0[1-9]|1[0-2])-([0-2]\d|3[01])$/';
if (!preg_match($datePattern, $frDate) || !preg_match($datePattern, $toDate) || $snapshot < 1) {
    alert('다운로드 조건이 올바르지 않습니다. 구간을 다시 조회해 주세요.');
}
if ($frDate > $toDate) {
    $tempDate = $frDate;
    $frDate = $toDate;
    $toDate = $tempDate;
}

$sfl = isset($_GET['sfl']) ? (string)$_GET['sfl'] : '';
$stx = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$sst = isset($_GET['sst']) ? (string)$_GET['sst'] : 'po_id';
$sod = isset($_GET['sod']) ? strtolower((string)$_GET['sod']) : 'desc';
$allowedSorts = array('po_id', 'mb_id', 'po_content', 'po_point', 'po_datetime');
if (!in_array($sst, $allowedSorts, true)) $sst = 'po_id';
if (!in_array($sod, array('asc', 'desc'), true)) $sod = 'desc';

$where = array('po.po_id <= ?', 'po.po_datetime BETWEEN ? AND ?');
$params = array($snapshot, $frDate.' 00:00:00', $toDate.' 23:59:59');
if ($stx !== '') {
    if ($sfl === 'mb_id') {
        $where[] = 'po.mb_id = ?';
        $params[] = $stx;
    } else {
        $where[] = 'po.po_content LIKE ?';
        $params[] = '%'.$stx.'%';
    }
}

$whereSql = ' WHERE '.implode(' AND ', $where);
$countRow = sql_pdo_fetch("SELECT COUNT(*) AS cnt FROM {$g5['point_table']} po {$whereSql}", $params);
$total = (int)($countRow['cnt'] ?? 0);
$offset = ($chunk - 1) * POINT_EXCEL_CHUNK_SIZE;
if ($offset >= $total) {
    alert('다운로드할 구간이 없습니다. 구간을 다시 조회해 주세요.');
}

$limit = min(POINT_EXCEL_CHUNK_SIZE, $total - $offset);
$sql = "SELECT po.mb_id, mb.mb_name, mb.mb_nick, po.po_content, po.po_point,
               po.po_datetime, po.po_expire_date, po.po_expired, po.po_mb_point
          FROM {$g5['point_table']} po
          LEFT JOIN {$g5['member_table']} mb ON po.mb_id = mb.mb_id
          {$whereSql}
         ORDER BY po.{$sst} {$sod}
         LIMIT {$offset}, {$limit}";
$result = sql_pdo_query($sql, $params);

$excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $excel->getActiveSheet();
$sheet->setTitle('포인트 내역');
$headers = array('회원아이디', '이름', '닉네임', '내용', '포인트', '일시', '만료일', '만료 여부', '처리 후 포인트');
foreach ($headers as $index => $header) {
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1).'1';
    $sheet->setCellValueExplicit($cell, $header, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
}
$sheet->getStyle('A1:I1')->getFont()->setBold(true);
$sheet->getStyle('A1:I1')->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setRGB('D9EAF7');
$sheet->freezePane('A2');
$sheet->setAutoFilter('A1:I1');

$rowNumber = 2;
while ($row = sql_pdo_fetch_array($result)) {
    $values = array(
        $row['mb_id'],
        $row['mb_name'],
        $row['mb_nick'],
        $row['po_content'],
        $row['po_point'],
        $row['po_datetime'],
        $row['po_expire_date'] === '9999-12-31' ? '' : $row['po_expire_date'],
        (int)$row['po_expired'] === 1 ? '만료' : '유효',
        $row['po_mb_point'],
    );
    foreach ($values as $index => $value) {
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1).$rowNumber;
        $sheet->setCellValueExplicit($cell, (string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    }
    $rowNumber++;
}

foreach (array(20, 16, 16, 50, 14, 22, 14, 12, 18) as $index => $width) {
    $sheet->getColumnDimensionByColumn($index + 1)->setWidth($width);
}
$sheet->getStyle('A:I')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

while (ob_get_level() > 0) {
    ob_end_clean();
}
session_write_close();

$startNumber = $offset + 1;
$endNumber = $offset + $limit;
$filename = 'points_'.str_replace('-', '', $frDate).'_'.str_replace('-', '', $toDate).'_'.$startNumber.'-'.$endNumber.'_'.date('Ymd_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($excel);
$writer->setPreCalculateFormulas(false);
$writer->save('php://output');
$excel->disconnectWorksheets();
exit;
