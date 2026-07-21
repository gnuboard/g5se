<?php
/*
 * /admin/visit_excel_download — 접속자 목록 XLSX 다운로드.
 */
$mode = isset($_GET['mode']) ? (string)$_GET['mode'] : '';
$sub_menu = $mode === 'search' ? '200810' : '200800';

require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
require_once G5_LIB_PATH.'/visit.lib.php';
require_once dirname(__DIR__, 2).'/vendor/autoload.php';

auth_check_menu($auth, $sub_menu, 'r');

const VISIT_EXCEL_CHUNK_SIZE = 10000;
const VISIT_EXCEL_MAX_SIZE = 300000;

if (!in_array($mode, array('list', 'search'), true)) {
    alert('올바르지 않은 다운로드 요청입니다.');
}

$where = array();
$params = array();
$suffix = '';

if ($mode === 'list') {
    $frDate = isset($_GET['fr_date']) ? (string)$_GET['fr_date'] : G5_TIME_YMD;
    $toDate = isset($_GET['to_date']) ? (string)$_GET['to_date'] : G5_TIME_YMD;
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-([0-2]\d|3[01])$/', $frDate)) {
        $frDate = G5_TIME_YMD;
    }
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-([0-2]\d|3[01])$/', $toDate)) {
        $toDate = G5_TIME_YMD;
    }
    if ($frDate > $toDate) {
        $temp = $frDate;
        $frDate = $toDate;
        $toDate = $temp;
    }
    $where[] = 'vi_date BETWEEN :fr_date AND :to_date';
    $params[':fr_date'] = $frDate;
    $params[':to_date'] = $toDate;
    $domain = isset($_GET['domain']) ? trim((string)$_GET['domain']) : '';
    if ($domain !== '') {
        $where[] = 'vi_referer LIKE :domain';
        $params[':domain'] = '%'.$domain.'%';
    }
    $suffix = str_replace('-', '', $frDate).'_'.str_replace('-', '', $toDate);
} else {
    $allowedFields = array('vi_ip', 'vi_date', 'vi_time', 'vi_referer', 'vi_agent', 'vi_browser', 'vi_os', 'vi_device');
    $field = isset($_GET['sfl']) && in_array($_GET['sfl'], $allowedFields, true) ? $_GET['sfl'] : '';
    $keyword = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
    if ($field !== '' && $keyword !== '') {
        $where[] = "{$field} LIKE :stx";
        $params[':stx'] = ($field === 'vi_ip' || $field === 'vi_date') ? $keyword.'%' : '%'.$keyword.'%';
    }
    $suffix = 'search';
}

$whereSql = $where ? ' WHERE '.implode(' AND ', $where) : '';
$count = sql_pdo_fetch("SELECT COUNT(*) AS cnt FROM {$g5['visit_table']}{$whereSql}", $params);
$total = isset($count['cnt']) ? (int)$count['cnt'] : 0;
if ($total < 1) {
    alert('다운로드할 접속자 자료가 없습니다.');
}
if ($total > VISIT_EXCEL_MAX_SIZE) {
    alert('엑셀 다운로드는 최대 '.number_format(VISIT_EXCEL_MAX_SIZE).'건까지 가능합니다. 기간이나 검색 조건을 좁혀 주세요.');
}

/** @return string 생성된 임시 XLSX 파일 경로 */
function visit_excel_create_file($sql, $params, $isSuper, $sheetTitle)
{
    $result = sql_pdo_query($sql, $params);
    $excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $excel->getActiveSheet();
    $sheet->setTitle($sheetTitle);
    $headers = array('IP', '접속 경로', '브라우저', 'OS', '접속기기', '일시');

    foreach ($headers as $index => $header) {
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1).'1';
        $sheet->setCellValueExplicit($cell, $header, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    }
    $sheet->getStyle('A1:F1')->getFont()->setBold(true);
    $sheet->getStyle('A1:F1')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('D9EAF7');
    $sheet->freezePane('A2');
    $sheet->setAutoFilter('A1:F1');

    $rowNumber = 2;
    while ($row = sql_pdo_fetch_array($result)) {
        $browser = $row['vi_browser'] ?: get_brow($row['vi_agent']);
        $os = $row['vi_os'] ?: get_os($row['vi_agent']);
        $referer = urldecode((string)$row['vi_referer']);
        if ($referer !== '' && !is_utf8($referer)) {
            $converted = @iconv('euc-kr', 'utf-8//IGNORE', $referer);
            if ($converted !== false) {
                $referer = $converted;
            }
        }
        $ip = (string)$row['vi_ip'];
        if (!$isSuper) {
            $ip = preg_replace('/([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)/', G5_IP_DISPLAY, $ip);
        }
        $values = array($ip, $referer, $browser, $os, $row['vi_device'], $row['vi_date'].' '.$row['vi_time']);
        foreach ($values as $index => $value) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1).$rowNumber;
            $sheet->setCellValueExplicit($cell, (string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        $rowNumber++;
    }

    foreach (array(18, 60, 18, 18, 16, 22) as $index => $width) {
        $sheet->getColumnDimensionByColumn($index + 1)->setWidth($width);
    }
    $sheet->getStyle('A:F')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    $basePath = tempnam(sys_get_temp_dir(), 'g5se_visit_');
    if ($basePath === false) {
        throw new RuntimeException('임시 파일을 생성하지 못했습니다.');
    }
    $filePath = $basePath.'.xlsx';
    @unlink($basePath);
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($excel);
    $writer->setPreCalculateFormulas(false);
    $writer->save($filePath);
    $excel->disconnectWorksheets();
    unset($writer, $excel);
    return $filePath;
}

$files = array();
$zipPath = '';
try {
    $chunks = (int)ceil($total / VISIT_EXCEL_CHUNK_SIZE);
    for ($chunk = 0; $chunk < $chunks; $chunk++) {
        $offset = $chunk * VISIT_EXCEL_CHUNK_SIZE;
        $limit = min(VISIT_EXCEL_CHUNK_SIZE, $total - $offset);
        $sql = "SELECT vi_ip, vi_referer, vi_browser, vi_os, vi_device, vi_agent, vi_date, vi_time
                  FROM {$g5['visit_table']}{$whereSql}
                 ORDER BY vi_id DESC
                 LIMIT {$offset}, {$limit}";
        $files[] = visit_excel_create_file($sql, $params, $is_admin === 'super', '접속자 '.($chunk + 1));
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    session_write_close();
    $baseName = 'visits_'.$suffix.'_'.date('Ymd_His');

    if (count($files) === 1) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$baseName.'.xlsx"');
        header('Content-Length: '.filesize($files[0]));
        header('X-Content-Type-Options: nosniff');
        readfile($files[0]);
        @unlink($files[0]);
        exit;
    }

    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('대용량 다운로드에 필요한 ZIP 기능을 사용할 수 없습니다.');
    }
    $zipPath = tempnam(sys_get_temp_dir(), 'g5se_visit_zip_');
    if ($zipPath === false) {
        throw new RuntimeException('임시 ZIP 파일을 생성하지 못했습니다.');
    }
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('ZIP 파일을 생성하지 못했습니다.');
    }
    foreach ($files as $index => $file) {
        $zip->addFile($file, $baseName.'_'.sprintf('%02d', $index + 1).'.xlsx');
    }
    $zip->close();
    foreach ($files as $file) {
        @unlink($file);
    }
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.$baseName.'.zip"');
    header('Content-Length: '.filesize($zipPath));
    header('X-Content-Type-Options: nosniff');
    readfile($zipPath);
    @unlink($zipPath);
    exit;
} catch (Throwable $e) {
    foreach ($files as $file) {
        if (is_string($file) && is_file($file)) {
            @unlink($file);
        }
    }
    if ($zipPath !== '' && is_file($zipPath)) {
        @unlink($zipPath);
    }
    error_log('[visit_excel_download] '.$e->getMessage());
    alert('엑셀 파일을 생성하지 못했습니다. 잠시 후 다시 시도해 주세요.');
}
