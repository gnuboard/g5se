<?php
/*
 * /admin/poll_list_excel_download — 투표 목록 XLSX 다운로드.
 */
$sub_menu = '200900';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
require_once dirname(__DIR__, 2).'/vendor/autoload.php';

auth_check_menu($auth, $sub_menu, 'r');

$stx = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$allowedSorts = array('po_id', 'po_subject', 'po_level', 'po_use', 'po_etc');
$sst = isset($_GET['sst']) && in_array($_GET['sst'], $allowedSorts, true) ? $_GET['sst'] : 'po_id';
$sod = isset($_GET['sod']) && in_array(strtolower((string)$_GET['sod']), array('asc', 'desc'), true)
    ? strtolower((string)$_GET['sod'])
    : '';

$where = ' WHERE 1 = 1';
$params = array();
if ($stx !== '') {
    $where .= ' AND po_subject LIKE :stx';
    $params[':stx'] = '%'.$stx.'%';
}

$sql = "SELECT po_id, po_subject, po_level, po_etc, po_use, po_date,
               (po_cnt1+po_cnt2+po_cnt3+po_cnt4+po_cnt5+po_cnt6+po_cnt7+po_cnt8+po_cnt9) AS total_votes
          FROM {$g5['poll_table']}
          {$where}
         ORDER BY {$sst} {$sod}";
$result = sql_pdo_query($sql, $params);

$excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $excel->getActiveSheet();
$sheet->setTitle('투표 목록');
$headers = array('번호', '제목', '투표권한', '투표수', '기타의견', '사용', '등록일');
foreach ($headers as $index => $header) {
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1).'1';
    $sheet->setCellValueExplicit($cell, $header, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
}
$sheet->getStyle('A1:G1')->getFont()->setBold(true);
$sheet->getStyle('A1:G1')->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setRGB('D9EAF7');
$sheet->freezePane('A2');
$sheet->setAutoFilter('A1:G1');

$rowNumber = 2;
while ($row = sql_pdo_fetch_array($result)) {
    $values = array(
        $row['po_id'],
        $row['po_subject'],
        $row['po_level'],
        $row['total_votes'],
        $row['po_etc'] ? '사용' : '미사용',
        $row['po_use'] ? '사용' : '미사용',
        $row['po_date'],
    );
    foreach ($values as $index => $value) {
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1).$rowNumber;
        $sheet->setCellValueExplicit($cell, (string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    }
    $rowNumber++;
}

foreach (array(10, 60, 14, 14, 14, 12, 16) as $index => $width) {
    $sheet->getColumnDimensionByColumn($index + 1)->setWidth($width);
}

while (ob_get_level() > 0) {
    ob_end_clean();
}
session_write_close();

$filename = 'polls_'.date('Ymd_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($excel);
$writer->setPreCalculateFormulas(false);
$writer->save('php://output');
$excel->disconnectWorksheets();
exit;
