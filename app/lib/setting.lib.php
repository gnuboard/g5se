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
        'notes' => <<<HTML
<h4>📧 Gmail 설정 가이드</h4>
<ul>
    <li><strong>host</strong>: <code>smtp.gmail.com</code></li>
    <li><strong>port</strong>: <code>587</code> (TLS 권장) 또는 <code>465</code> (SSL)</li>
    <li><strong>암호화</strong>: port 587 이면 <strong>TLS</strong>, port 465 이면 <strong>SSL/SMTPS</strong>. <em>"없음" 으로 두면 발송 실패함</em></li>
    <li><strong>인증 사용</strong>: <strong>반드시 체크</strong> (Gmail 은 인증 필수)</li>
    <li><strong>계정</strong>: 전체 이메일 주소 (예: <code>me@gmail.com</code>)</li>
    <li><strong>비밀번호</strong>: <strong>앱 비밀번호</strong> — 일반 Gmail 비밀번호는 더 이상 안 받음</li>
</ul>
<p>앱 비밀번호 만들기: <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">myaccount.google.com/apppasswords</a> (2단계 인증 활성화 필요) → 앱 비밀번호 생성 → 16자 코드를 그대로 붙여넣기 (공백 포함 가능)</p>
<h4>🇰🇷 Naver 설정 가이드</h4>
<ul>
    <li><strong>host</strong>: <code>smtp.naver.com</code></li>
    <li><strong>port</strong>: <code>587</code> (TLS) 또는 <code>465</code> (SSL)</li>
    <li><strong>암호화</strong>: port 587 이면 <strong>TLS</strong>, port 465 이면 <strong>SSL/SMTPS</strong></li>
    <li><strong>인증 사용</strong>: <strong>반드시 체크</strong></li>
    <li><strong>계정</strong>: 전체 이메일 또는 네이버 ID (예: <code>kagla@naver.com</code> 또는 <code>kagla</code>) — 둘 중 안 되면 다른 쪽 시도</li>
    <li><strong>비밀번호</strong>: <strong>네이버 앱 비밀번호 12자</strong> — 일반 네이버 로그인 비번 안 받음</li>
</ul>
<p>설정 순서:</p>
<ol>
    <li><a href="https://mail.naver.com/v2/settings/smtp/imap" target="_blank" rel="noopener">mail.naver.com 환경설정 → POP3/IMAP 설정</a> → "IMAP/SMTP 설정" 또는 "POP3/SMTP 설정" 탭 → <strong>사용함</strong> 선택</li>
    <li>같은 페이지의 안내 박스 안 <strong>[설정하기]</strong> 버튼 → 네이버 ID 보안설정 페이지로 이동</li>
    <li><strong>2단계 인증 활성화</strong> (이미 켜있으면 건너뜀)</li>
    <li><strong>애플리케이션 비밀번호 생성</strong> — 종류 "직접 입력" 으로 식별 이름 (예: <code>g5se</code>) → [생성하기] → 표시되는 12자 비번 메모 (창 닫으면 다시 못 봄)</li>
    <li>다시 IMAP/SMTP 설정 페이지로 가서 [저장]</li>
    <li>위 12자 비번을 이 폼의 비밀번호 필드에 붙여넣기</li>
</ol>
<p><em>주의:</em> 인증 실패 누적 시 일시 차단됨 (10~30분 대기 후 재시도)</p>
HTML,
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

    // schema 에 선언된 키만 반환 — 과거 schema 의 잔재 키가 응답에 새지 않도록
    return array_intersect_key(array_merge($defaults, $stored), $defaults);
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

    // 현재 schema 의 필드만 저장 — 제거된 필드의 잔재가 영구 누적되지 않도록
    $allowed = array_flip(array_keys(SETTINGS_SCHEMA[$key]['fields']));
    $merged = array_intersect_key(array_merge($existing, $values), $allowed);
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
