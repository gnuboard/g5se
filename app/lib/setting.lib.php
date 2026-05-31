<?php
/**
 * g5_setting — 사이트 설정값 저장/조회 헬퍼
 *
 * 모든 설정 그룹은 SETTINGS_SCHEMA 상수에 선언. admin 은 값만 편집 가능.
 * 키·필드 이름·타입 변경은 코드 수정으로만 가능.
 *
 * 사용:
 *   $smtp = setting('smtp');   // schema defaults + DB 저장값 병합 array
 *   setting_put('smtp', ['host' => 'smtp.gmail.com', 'port' => 587]);
 *
 * 새 그룹 추가 = SETTINGS_SCHEMA 배열에 항목 추가.
 */
if (!defined('_GNUBOARD_')) exit;

const SETTINGS_SCHEMA = [

    'smtp' => [
        'title'       => 'SMTP 메일 발송',
        'description' => 'PHP mail() 대신 외부 SMTP 서버 사용. host 비우면 PHP mail() 폴백.',
        'fields' => [
            'host'   => ['label' => 'SMTP 호스트', 'type' => 'text',     'default' => '',
                         'help'  => '예: smtp.gmail.com (비우면 PHP mail() 사용)'],
            'port'   => ['label' => '포트',        'type' => 'number',   'default' => 25,
                         'min'   => 1, 'max' => 65535, 'required' => true,
                         'help'  => 'SSL=465, TLS=587, plain=25'],
            'secure' => ['label' => '암호화',      'type' => 'select',   'default' => '',
                         'options' => ['' => '없음', 'tls' => 'TLS', 'ssl' => 'SSL/SMTPS']],
            'auth'   => ['label' => '인증 사용',   'type' => 'bool',     'default' => false],
            'user'   => ['label' => '계정',        'type' => 'text',     'default' => ''],
            'pass'   => ['label' => '비밀번호',    'type' => 'password', 'default' => '',
                         'help'  => '저장된 값은 폼에 마스크로만 표시. 빈 값 저장 시 기존 유지.'],
        ],
    ],

    // 새 설정 그룹은 여기에 추가
];

/**
 * 설정 그룹의 값을 반환. schema defaults 와 DB 저장값을 병합 (저장값 우선).
 *
 * @throws \InvalidArgumentException 미등록 키
 * @return array 모든 필드가 채워진 dict
 */
function setting(string $key): array
{
    if (!isset(SETTINGS_SCHEMA[$key])) {
        throw new \InvalidArgumentException("Unknown setting key: $key");
    }
    $schema = SETTINGS_SCHEMA[$key];

    // 1) schema defaults
    $defaults = [];
    foreach ($schema['fields'] as $fkey => $field) {
        $defaults[$fkey] = $field['default'];
    }

    // 2) DB 에서 저장값 lazy SELECT (테이블 미생성 시 defaults 반환)
    $stored = [];
    try {
        $row = sql_pdo_fetch(
            "SELECT s_value FROM `".G5_TABLE_PREFIX."setting` WHERE s_key = ?",
            [$key],
            false
        );
        if ($row && isset($row['s_value'])) {
            $decoded = json_decode($row['s_value'], true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded)) {
                $stored = $decoded;
            }
        }
    } catch (\Throwable $e) {
        // 테이블 없거나 JSON 깨짐 — defaults 만 반환
    }

    return array_merge($defaults, $stored);
}

/**
 * 설정 그룹에 값을 저장 (UPSERT).
 *
 * $values 는 schema 필드 키의 부분집합 가능 — 누락된 필드는 기존 DB 값을 유지.
 * (password 같이 "빈 입력 = 기존 유지" 인 케이스에서 admin 페이지가 키를 제외)
 */
function setting_put(string $key, array $values): void
{
    if (!isset(SETTINGS_SCHEMA[$key])) {
        throw new \InvalidArgumentException("Unknown setting key: $key");
    }

    // 기존 저장값 읽어와 부분 갱신
    $existing = [];
    $row = sql_pdo_fetch(
        "SELECT s_value FROM `".G5_TABLE_PREFIX."setting` WHERE s_key = ?",
        [$key],
        false
    );
    if ($row && isset($row['s_value'])) {
        $decoded = json_decode($row['s_value'], true, 512, JSON_THROW_ON_ERROR);
        if (is_array($decoded)) {
            $existing = $decoded;
        }
    }

    $merged = array_merge($existing, $values);
    $json = json_encode($merged, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

    sql_pdo_query(
        "INSERT INTO `".G5_TABLE_PREFIX."setting` (s_key, s_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE s_value = VALUES(s_value)",
        [$key, $json]
    );
}

/** SETTINGS_SCHEMA 그대로 반환 — admin 페이지가 카드 렌더링에 사용. */
function setting_schemas(): array
{
    return SETTINGS_SCHEMA;
}
