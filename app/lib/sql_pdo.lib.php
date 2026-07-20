<?php
/*
 * /lib/sql_pdo.lib.php — PDO prepared-statement 헬퍼.
 *
 * 애플리케이션 SQL 실행을 단일 prepared-statement 경로로 통합한다.
 * 값은 placeholder 로 바인딩하고, 테이블/컬럼 등 식별자는 호출자가
 * 신뢰 가능한 설정값 또는 명시적 허용 목록으로 제한해야 한다.
 *
 * 사용 예:
 *   $stmt = sql_pdo_query("select * from {$g5['member_table']} where mb_id = ?", [$mb_id]);
 *   while ($row = sql_fetch_array($stmt)) { ... }
 *
 *   $row = sql_pdo_fetch("select * from {$g5['faq_table']} where fa_id = ?", [$fa_id]);
 *
 *   // named placeholder 도 동작:
 *   sql_pdo_query("update t set a = :a where id = :id", [':a' => $a, ':id' => $id]);
 *
 * 테이블명/컬럼명은 PDO 가 placeholder 로 받지 못하므로 기존 {$g5['x_table']} 보간 그대로.
 * 위험한 부분은 사용자 입력 → WHERE/VALUES 의 값 부분이므로 그곳만 placeholder.
 */

if (!defined('_GNUBOARD_')) exit;

/**
 * Prepared statement 실행.
 *
 * @param string     $sql     SQL with `?` 또는 `:name` placeholder
 * @param array|bool $params  positional/named 배열. 이전 중인 레거시 호출은 error bool 허용
 * @param bool       $error   에러 발생 시 die 여부 (sql_query 의 두 번째 인자와 동일 의미)
 * @param PDO|null   $link    DB 핸들 (생략 시 $g5['connect_db'])
 * @return PDOStatement|false
 */
function sql_pdo_query($sql, $params = [], $error = G5_DISPLAY_SQL_ERROR, $link = null)
{
    global $g5, $g5_debug;

    // Legacy sql_query($sql, $error, $link) 호출을 단계적으로 이전할 수
    // 있도록 두 번째 인자가 bool인 형태도 수용한다. 모든 호출처가 명시적
    // params 배열로 전환되면 이 호환 분기는 제거할 수 있다.
    if (is_bool($params)) {
        $link = $error instanceof PDO ? $error : $link;
        $error = $params;
        $params = [];
    }

    if (!is_array($params)) {
        throw new InvalidArgumentException('SQL parameters must be an array.');
    }

    if (!$link) $link = $g5['connect_db'];

    $sql = trim($sql);
    // raw query 와 동일한 보안 패턴 적용
    $sql = preg_replace("#^select.*from.*([\s\(]+union[\s\)]+|/\*.*union.*\*/).*#i", "select 1", $sql);
    $sql = preg_replace("#^select.*from.*where.*`?information_schema`?.*#i", "select 1", $sql);

    $is_debug   = function_exists('get_permission_debug_show') ? get_permission_debug_show() : false;
    $start_time = ($is_debug || G5_COLLECT_QUERY) ? get_microtime() : 0;

    $stmt = false;
    if ($link instanceof PDO) {
        try {
            $stmt = @$link->prepare($sql);
            if ($stmt) {
                $ok = @$stmt->execute($params);
                if (!$ok) $stmt = false;
            }
        } catch (Exception $e) {
            $stmt = false;
        }

        if ($stmt instanceof PDOStatement) {
            // get_sql_affected_rows() 가 마지막 statement 의 rowCount 사용
            $g5['last_stmt'] = $stmt;
        }

        if (!$stmt && $error) {
            $info     = $link->errorInfo();
            $err_no   = isset($info[1]) ? (int)$info[1] : 0;
            $err_msg  = isset($info[2]) ? (string)$info[2] : '';
            $err_file = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';

            @error_log("[g5 sql_pdo_query] {$err_no}: {$err_msg} | SQL: {$sql} | params: ".json_encode($params, JSON_UNESCAPED_UNICODE)." | file: {$err_file}");

            if ($is_debug) {
                die("<p>" . htmlspecialchars($sql, ENT_QUOTES, 'UTF-8')
                    . "<p>" . (int)$err_no . " : " . htmlspecialchars($err_msg, ENT_QUOTES, 'UTF-8')
                    . "<p>error file : " . htmlspecialchars($err_file, ENT_QUOTES, 'UTF-8'));
            }
            die('데이터베이스 처리 중 오류가 발생했습니다.');
        }
    }

    $end_time = ($is_debug || G5_COLLECT_QUERY) ? get_microtime() : 0;
    if ($is_debug || G5_COLLECT_QUERY) {
        $info = ($link instanceof PDO) ? $link->errorInfo() : [null, 0, ''];
        $g5_debug['sql'][] = array(
            'sql'           => $sql . '  -- params: ' . json_encode($params, JSON_UNESCAPED_UNICODE),
            'result'        => $stmt,
            'success'       => !!$stmt,
            'source'        => array(),
            'error_code'    => isset($info[1]) ? (int)$info[1] : 0,
            'error_message' => isset($info[2]) ? (string)$info[2] : '',
            'start_time'    => $start_time,
            'end_time'      => $end_time,
        );
    }

    return $stmt;
}


/**
 * Prepared statement 한 행 fetch — sql_fetch 의 PDO 버전.
 *
 * @param string     $sql     SQL with placeholders
 * @param array      $params  바인딩 값
 * @param bool       $error   에러 die 여부
 * @param PDO|null   $link    DB 핸들
 * @return array              연관배열 (행 없으면 빈 배열)
 */
function sql_pdo_fetch($sql, array $params = [], $error = G5_DISPLAY_SQL_ERROR, $link = null)
{
    $stmt = sql_pdo_query($sql, $params, $error, $link);
    if (!$stmt) return array();
    $row = sql_fetch_array($stmt);
    return is_array($row) ? $row : array();
}
