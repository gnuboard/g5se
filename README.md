# 그누보드5 SE

**GNUBOARD5 Second Edition** — gnuboard5 기반 second edition.

## 특징

- **파일 하나로 반응형 + 다크모드** — PC/모바일 마크업 분리 없이 단일 파일, `@media` 와 CSS variable + `data-theme` 으로 처리
- PHP 8.x 호환 (PDO named placeholder)
- utf8mb4 + InnoDB (이모지 지원, MySQL strict mode 호환)
- nullable date/datetime (`0000-00-00` 폐기)
- 클린 URL 라우팅 (`/board/{table}/{wr_id}`, `/shop/item/{it_id}` 등)
- **사용자 모듈** — `app/modules/<name>/index.php` 두면 `/<name>` 으로 자동 라우팅 (Next.js app router 풍)
- **스키마 기반 사이트 설정** — `SETTINGS_SCHEMA` 상수에 그룹별 키·필드·타입 선언, admin 은 값만 편집. SMTP 가 첫 등록 (mailer.lib.php 자동 연동)
- 설치 마법사 모더나이즈 (진행 단계, 다크모드)
- DB 마이그레이션 도구 — `/admin/db_migrate` 에서 utf8mb3 → utf8mb4, zero-date → NULL 일괄 변환

## 요구사항

- PHP 8.0+
- MySQL 5.7+ 또는 MariaDB 10.2+
- Apache + mod_rewrite (또는 nginx 동등 설정)

## 설치

```bash
git clone https://github.com/gnuboard/g5se.git
cd g5se
mkdir -p data && chmod 707 data
mysql -u root -e "CREATE DATABASE g5se CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

브라우저로 `/install/` 접근 → 마법사 따라 진행.

## 사용자 모듈 (`app/modules/`)

파일 하나만 두면 자동으로 라우팅되는 폴더 기반 모듈 시스템. Next.js app router 의 핵심 컨벤션을 PHP/gnuboard 맥락으로 단순화했습니다.

### URL ↔ 파일시스템 매핑

| 파일 | URL |
|---|---|
| `app/modules/fortune/index.php` | `/fortune` |
| `app/modules/games/dice/index.php` | `/games/dice` |
| `app/modules/blog/[slug]/index.php` | `/blog/anything` (`$_GET['slug']` 자동 주입) |

### 규칙

- 진입점은 항상 `index.php`. 다른 `.php` 파일은 직접 호출 불가
- 폴더명 segment: `^[a-zA-Z0-9_-]+$` 만 허용 (path traversal · NUL · backslash 차단)
- `[name]` 폴더는 dynamic segment — URL capture 가 `$_GET[name]` + `$_REQUEST[name]` 에 주입
- system route (`/login`, `/board/...`, `/shop/...` 등) 가 항상 우선 — 사용자 모듈은 기존 경로 덮어쓰기 불가
- 페이지 안에선 `include_once('./_common.php')` 그대로 동작 (front controller 가 CWD 를 `app/` 로 고정)

### chrome — modern shell 적용

테마(`theme/basic`) 와 같은 톤으로 가려면 [theme/basic/index.php](app/theme/basic/index.php) 의 패턴 그대로 인클루드:

```php
<?php
include_once('./_common.php');

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
include_once('./head.sub.php');
?>
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>
    <main class="m-container">
        <!-- 페이지 본문 -->
    </main>
    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>
<?php include_once('./tail.sub.php');
```

다크모드 자동 대응을 위해 카드·텍스트는 `--m-*` 토큰 (`var(--m-surface)`, `var(--m-text)`, `var(--m-text-muted)`, `var(--m-shadow)`, `var(--m-radius-lg)`) 사용 권장.

### 모듈 데이터 — `data/modules/`

모듈이 SQLite · 캐시 · 업로드 등 자체 데이터를 둬야 하면 `data/modules/<name>/` 아래에:

```php
$dir = G5_DATA_PATH.'/modules/<name>';
if (!is_dir($dir)) mkdir($dir, 0755, true);
$db = new PDO('sqlite:'.$dir.'/store.sqlite');
```

`.htaccess` 가 `data/modules/` 전체를 직접 URL 접근 차단 (403) 합니다 — 모듈 DB·내부 파일이 노출되지 않음.

### 활성 / 비활성

- **활성** = `app/modules/<name>/index.php` 존재
- **비활성** = 폴더명에 점(`.`) 포함시켜 라우팅 regex 매칭 회피 (예: `app/modules/fortune.off/index.php`). 코드는 보존되고 URL 만 사라짐
- 완전 제거 = 폴더 삭제

별도 관리자 UI · DB · config 없음. 파일시스템이 단일 진실.

## 사이트 설정 (`/admin/setting`)

스키마 기반 설정 관리. 키·필드·타입은 코드에서 고정, admin 은 값만 편집 — 사이트 간 일관성 보장. 새 설정 그룹 추가 = PHP 배열 항목 하나 추가, DDL 불필요.

### 스키마 선언

`app/lib/setting.lib.php` 상단의 `SETTINGS_SCHEMA` 상수에 모든 그룹이 모임:

```php
const SETTINGS_SCHEMA = [
    'smtp' => [
        'title'       => 'SMTP 메일 발송',
        'description' => 'PHP mail() 대신 외부 SMTP 사용...',
        'notes'       => '<h4>Gmail</h4>...',  // (선택) 편집 화면 가이드 HTML
        'fields' => [
            'host'   => ['label' => 'SMTP 호스트', 'type' => 'text',     'default' => ''],
            'port'   => ['label' => '포트',        'type' => 'number',   'default' => 25,
                         'min'   => 1, 'max' => 65535, 'required' => true],
            'secure' => ['label' => '암호화',      'type' => 'select',   'default' => '',
                         'options' => ['' => '없음', 'tls' => 'TLS', 'ssl' => 'SSL/SMTPS']],
            'auth'   => ['label' => '인증 사용',   'type' => 'bool',     'default' => false],
            'user'   => ['label' => '계정',        'type' => 'text',     'default' => ''],
            'pass'   => ['label' => '비밀번호',    'type' => 'password', 'default' => ''],
        ],
    ],
];
```

### 헬퍼 API

```php
$smtp = setting('smtp');                            // schema defaults + 저장값 array_merge
setting_put('smtp', ['host' => 'smtp.gmail.com']);  // JSON UPSERT (부분 갱신 가능)
$all  = setting_schemas();                          // SETTINGS_SCHEMA 전체
```

`setting()` 은 항상 모든 필드를 가진 array 반환 — 호출자는 키 존재 여부 검사 불필요.

### 필드 타입

| 타입 | HTML | 검증 |
|---|---|---|
| `text` | `<input type="text">` | `required` 시 trim 후 비어있지 않음 |
| `number` | `<input type="number">` | int 캐스팅 + `min`/`max` 경계 |
| `select` | `<select>` | `options` 키 화이트리스트 (POST 위조 차단) |
| `bool` | `<input type="checkbox">` | 미체크 = false |
| `password` | `<input type="password">` | 빈 입력 = 기존 값 유지 (특수) |

### Admin UI (`/admin/setting`)

- 목록: 등록된 그룹 테이블 + 저장 상태 뱃지 (✓ 저장됨 / 기본값)
- 편집: `?key=<group>` — 그룹 폼 + [💾 저장] / [↺ 기본값으로 리셋]
- 세션 flash 토스트 — URL 에 액션 흔적 없음 (위조 링크 차단)
- 스키마 `notes` 는 `<details>/<summary>` 토글로 접혀서 표시
- 다크모드 자동 (`--slate-*` 토큰 + `[data-theme="dark"]` 오버라이드)
- 좌측 메뉴: `환경설정 > 설정 관리` (code 100150)

### DB

`g5_setting` 테이블 (`s_key VARCHAR(64) PK`, `s_value LONGTEXT` JSON, `s_updated_at`). 그룹 1개 = row 1개. 처음 설치 후 `/admin/db_migrate` 의 "테이블 생성" 한 번이면 끝.

### SMTP 연동

`mailer.lib.php` 는 우선순위 `setting('smtp')['host']` (비어있지 않으면 그 그룹의 값으로 PHPMailer 구성) → `G5_SMTP_*` 상수 → PHP `mail()`. 기존 상수 기반 설정도 그대로 작동, `/admin/setting` 에 값을 입력하면 자동으로 그쪽이 이김.

Gmail / Naver 두 공급자의 설정 가이드 (2단계 인증 + 앱 비밀번호 흐름, 공식 페이지 직접 링크 포함) 가 SMTP 스키마의 `notes` 에 내장.

## 라이센스

MIT License — `LICENSE` 파일 참조.

## 문서

- [MODERNIZATION.md](MODERNIZATION.md) — 모더나이즈 작업 기록
- [CLAUDE.md](CLAUDE.md) — AI 보조 도구용 가이드라인

---
**기반**: [gnuboard5](https://github.com/gnuboard/gnuboard5)
