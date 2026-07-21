<?php
/*
 * /admin/connect_list_excel_download — 현재 접속자 XLSX 다운로드.
 */
$sub_menu = '200999';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once G5_ADMIN_PATH.'/admin.lib.php';
require_once dirname(__DIR__, 2).'/vendor/autoload.php';

if ($is_admin === 'super') {
    auth_check_menu($auth, $sub_menu, 'r');
}

$filter = isset($_GET['filter']) ? (string)$_GET['filter'] : 'all';
if (!in_array($filter, array('all', 'member', 'guest'), true)) {
    $filter = 'all';
}

$where = '';
if ($filter === 'member') {
    $where = " WHERE a.mb_id <> ''";
} elseif ($filter === 'guest') {
    $where = " WHERE a.mb_id = ''";
}

$sql = "SELECT a.mb_id, a.lo_ip, a.lo_datetime, a.lo_location, a.lo_url,
               b.mb_nick, b.mb_name, b.mb_level
          FROM {$g5['login_table']} a
          LEFT JOIN {$g5['member_table']} b ON a.mb_id = b.mb_id
          {$where}
         ORDER BY a.lo_datetime DESC";
$result = sql_pdo_query($sql);

$excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $excel->getActiveSheet();
$sheet->setTitle('현재 접속자');
$headers = array('번호', '구분', '회원아이디', '닉네임', '이름', '회원권한', 'IP', '현재 위치', 'URL', '접속 시각');

foreach ($headers as $index => $header) {
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1).'1';
    $sheet->setCellValueExplicit($cell, $header, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
}
$sheet->getStyle('A1:J1')->getFont()->setBold(true);
$sheet->getStyle('A1:J1')->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setRGB('D9EAF7');
$sheet->freezePane('A2');
$sheet->setAutoFilter('A1:J1');

$rowNumber = 2;
$sequence = 1;
while ($row = sql_pdo_fetch_array($result)) {
    $isMember = $row['mb_id'] !== '';
    $values = array(
        $sequence,
        $isMember ? '회원' : '비회원',
        $row['mb_id'],
        $row['mb_nick'],
        $row['mb_name'],
        $isMember ? $row['mb_level'] : '',
        $row['lo_ip'],
        trim((string)$row['lo_location']),
        trim((string)$row['lo_url']),
        $row['lo_datetime'],
    );
    foreach ($values as $index => $value) {
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1).$rowNumber;
        $sheet->setCellValueExplicit($cell, (string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    }
    $rowNumber++;
    $sequence++;
}

foreach (array(8, 12, 20, 18, 18, 12, 18, 36, 60, 22) as $index => $width) {
    $sheet->getColumnDimensionByColumn($index + 1)->setWidth($width);
}
$sheet->getStyle('A:J')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

while (ob_get_level() > 0) {
    ob_end_clean();
}
session_write_close();

$filterNames = array('all' => 'all', 'member' => 'members', 'guest' => 'guests');
$filename = 'current_connections_'.$filterNames[$filter].'_'.date('Ymd_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($excel);
$writer->setPreCalculateFormulas(false);
$writer->save('php://output');
$excel->disconnectWorksheets();
exit;
