# 그누보드5 라우팅 현대화 + 디자인 인프라 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 그누보드5에 AltoRouter 기반 front controller, 깔끔한 URL(`/boards/free/123`), 관리자 폴더 리네임(`adm/`→`admin/`), TailwindCSS v4 + 다크모드를 도입해 저가형 호스팅에서 동작하도록 현대화한다.

**Architecture:** `.htaccess`가 모든 요청을 `index.php`로 리라이트 → front controller가 `routes/*.php`를 자동 로드 → AltoRouter 매칭 → 콜백이 `$_GET`/`$_SERVER` 주입 후 기존 그누보드 파일을 `require`. 구 URL은 `routes/90-legacy.php`가 301 리다이렉트. Tailwind는 standalone 바이너리로 로컬 빌드 후 `assets/css/app.css`를 서버에 업로드.

**Tech Stack:** PHP 5.2+ (그누보드5 호환), Apache mod_rewrite, AltoRouter(PHP 단일 파일), TailwindCSS v4 standalone binary, 바닐라 JS (다크모드 토글).

**Spec:** [2026-04-18-gnuboard5-routing-modernization-design.md](../specs/2026-04-18-gnuboard5-routing-modernization-design.md)

**제약 재확인:**
- Composer 사용 금지 — AltoRouter는 GitHub에서 파일 직접 다운로드
- MVC 프레임워크 사용 금지
- `bbs/*`, `shop/*`, `skin/*`, `common.php`, `config.php` 는 **0줄 수정 원칙** (단, `config.php` 의 `G5_ADMIN_DIR` 상수 값 1곳만 예외)
- 저가형 호스팅 (카페24 등) 에서 동작 — Node.js 불필요, SSH 불필요

**테스트 전략 (레거시 PHP 환경):**
- PHPUnit 미사용 (composer 금지)
- `scripts/smoke.sh` 로 curl 기반 HTTP 상태 코드 검증
- 각 Task 종료 시 `scripts/smoke.sh` 실행하여 회귀 없음 확인
- DB 로직이 필요한 페이지는 수동 브라우저 확인으로 보완

**파일 구조 맵 (최종):**

| 경로 | 동작 |
|---|---|
| `index.php` | 새로 작성 (front controller) |
| `home.php` | 신규 (기존 `index.php` 내용 이동) |
| `.htaccess` | 신규 |
| `admin/` | `adm/` 리네임 |
| `config.php:52` | `G5_ADMIN_DIR` 값만 `'admin'` 으로 변경 |
| `lib/AltoRouter/AltoRouter.php` | 신규 (GitHub에서 다운로드) |
| `core/helpers.php` | 신규 (`g5_dispatch`) |
| `core/router-bootstrap.php` | 신규 |
| `routes/00-root.php` 등 8개 파일 | 신규 |
| `tailwindcss` | 신규 (바이너리, `.gitignore`) |
| `assets/css/input.css` | 신규 |
| `assets/css/app.css` | 신규 (빌드 산출물, 커밋) |
| `theme/basic/head.php` | 재작성 |
| `theme/basic/tail.php` | 재작성 |
| `theme/basic/index.php` | 재작성 |
| `scripts/smoke.sh` | 신규 (테스트 스크립트) |
| `.gitignore` | 수정 (tailwindcss 바이너리 제외) |

---

## Phase A: 준비 및 베이스라인

### Task 1: 작업 브랜치 생성 + 베이스라인 확인

**Files:**
- (검증만) `/home/kagla/gnuboard5`

- [ ] **Step 1: git 상태 확인 및 브랜치 생성**

```bash
cd /home/kagla/gnuboard5
git status
git checkout -b feat/routing-modernization
```

Expected: 새 브랜치 생성됨, `git status` 가 clean 이거나 알려진 파일만 변경.

- [ ] **Step 2: 로컬 서버에서 베이스라인 URL 접근 확인**

그누보드가 설치되어 있고 로컬 서버(Apache 또는 PHP 내장 서버)가 돌고 있는지 확인. 테스트 호스트를 환경변수로 둔다.

```bash
export BASE_URL="http://localhost/gnuboard5"   # 실제 접근 URL로 조정
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/"
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/bbs/login.php"
```

Expected: 둘 다 `200`. 만약 서버가 없으면, 아래로 PHP 내장 서버 기동:

```bash
cd /home/kagla/gnuboard5
php -S 127.0.0.1:8000
export BASE_URL="http://127.0.0.1:8000"
```

- [ ] **Step 3: 베이스라인 상태를 메모**

이후 모든 Task 검증은 이 BASE_URL 을 기준으로 수행한다. 터미널에서 `echo $BASE_URL` 로 확인.

### Task 2: 스모크 테스트 스크립트 작성

**Files:**
- Create: `scripts/smoke.sh`

- [ ] **Step 1: scripts 디렉토리 생성**

```bash
mkdir -p /home/kagla/gnuboard5/scripts
```

- [ ] **Step 2: `scripts/smoke.sh` 작성**

```bash
#!/usr/bin/env bash
# 간단한 HTTP 상태 코드 검증 스모크 테스트
# 사용: BASE_URL=http://127.0.0.1:8000 ./scripts/smoke.sh

set -e
BASE="${BASE_URL:-http://127.0.0.1:8000}"
FAIL=0

check() {
  local method="$1"
  local url="$2"
  local expected="$3"
  local status
  status=$(curl -s -o /dev/null -w "%{http_code}" -X "$method" "${BASE}${url}" --max-redirs 0 || echo "000")
  if [ "$status" = "$expected" ]; then
    printf "  ✓ %-4s %-50s → %s\n" "$method" "$url" "$status"
  else
    printf "  ✗ %-4s %-50s → %s (expected %s)\n" "$method" "$url" "$status" "$expected"
    FAIL=1
  fi
}

echo "=== Baseline URLs (existing gnuboard5 endpoints) ==="
check GET "/" 200
check GET "/bbs/login.php" 200

echo "=== Post-refactor URLs (neu) ==="
# New URLs (초기엔 404, 구현 진행 시 200/301 으로 바뀜)
# check GET "/login" 200
# check GET "/boards/free" 200
# check GET "/admin" 200
# check GET "/bbs/login.php" 301

if [ "$FAIL" = "1" ]; then
  echo "FAILED"
  exit 1
fi
echo "OK"
```

- [ ] **Step 3: 실행 권한 부여 및 베이스라인 실행**

```bash
cd /home/kagla/gnuboard5
chmod +x scripts/smoke.sh
./scripts/smoke.sh
```

Expected: 두 개의 ✓ 출력 ("Baseline URLs" 섹션), `OK` 로 종료.

- [ ] **Step 4: 커밋**

```bash
git add scripts/smoke.sh
git commit -m "test: add smoke test script for routing verification"
```

---

## Phase B: `adm/` → `admin/` 리네임

### Task 3: `adm/` 폴더를 `admin/` 으로 리네임 + 상수 변경

**Files:**
- Rename: `adm/` → `admin/`
- Modify: `config.php:52`

- [ ] **Step 1: 폴더 리네임**

```bash
cd /home/kagla/gnuboard5
git mv adm admin
ls admin | head -5
```

Expected: `admin/` 폴더 존재, 내용은 기존 adm 과 동일.

- [ ] **Step 2: `config.php:52` 상수 값 변경**

파일에서 `define('G5_ADMIN_DIR',      'adm');` 라인을 찾아 `'adm'` → `'admin'` 으로 수정.

변경 후 내용:
```php
define('G5_ADMIN_DIR',      'admin');
```

- [ ] **Step 3: 변경 확인**

```bash
grep -n "G5_ADMIN_DIR" config.php
```

Expected 출력:
```
52:define('G5_ADMIN_DIR',      'admin');
```

- [ ] **Step 4: 기존 관리자 URL 접근 테스트 (PHP 내장 서버 기동 중 전제)**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/admin/"
```

Expected: `200` 또는 관리자 권한 없음으로 리다이렉트(`302`). `404` 가 아니어야 한다.

- [ ] **Step 5: 커밋**

```bash
git add admin config.php
git commit -m "refactor: rename adm/ to admin/ and update G5_ADMIN_DIR constant"
```

### Task 4: 코드 내 하드코딩된 `'adm/'` 리터럴 전수 검사

**Files:**
- Audit only (이 단계에서는 보고만 수행)

- [ ] **Step 1: 리터럴 `adm/` 검색**

```bash
cd /home/kagla/gnuboard5
grep -rn --include="*.php" -E "['\"]adm/" . 2>/dev/null | grep -v "^\./admin/" | grep -v "^\./docs/" | tee /tmp/adm-literals.txt
wc -l /tmp/adm-literals.txt
```

Expected: 출력 파일에 나열된 모든 매치를 검토. 대부분 `G5_ADMIN_URL`/`G5_ADMIN_PATH` 를 이미 사용하므로 매치 개수는 적을 것.

- [ ] **Step 2: 매치된 각 라인 수동 점검**

각 파일을 열어 `'adm/'` 또는 `"adm/"` 리터럴이 진짜 관리자 경로인지 확인. 대부분은 **주석/URL 예시**이거나 `adm/skin/` 같은 스킨 경로. 실제 경로 참조면 `admin/` 으로 변경.

**주의**: `lib/iteminfo.lib.php` 같은 변수명(`$admin_xxx`)이나 주석은 건드리지 않는다. 리터럴 경로만 대상.

- [ ] **Step 3: 치환 수행 (있다면)**

예시: 만약 `some_file.php` 에 `"adm/board_list.php"` 가 있다면 `"admin/board_list.php"` 로 변경.

- [ ] **Step 4: 변경 후 baseline smoke 재확인**

```bash
./scripts/smoke.sh
```

Expected: 기존 URL 들이 여전히 `200`.

- [ ] **Step 5: 커밋 (변경이 있었다면)**

```bash
git add -u
git commit -m "refactor: update hardcoded 'adm/' literals to 'admin/'"
```

변경 없으면 커밋 생략.

---

## Phase C: 라우터 인프라

### Task 5: `.gitignore` 업데이트 + AltoRouter 배치

**Files:**
- Create: `lib/AltoRouter/AltoRouter.php`
- Create: `lib/AltoRouter/LICENSE`
- Modify: `.gitignore`

- [ ] **Step 1: AltoRouter 다운로드**

```bash
cd /home/kagla/gnuboard5
mkdir -p lib/AltoRouter
curl -L -o lib/AltoRouter/AltoRouter.php \
  https://raw.githubusercontent.com/dannyvankooten/AltoRouter/master/AltoRouter.php
curl -L -o lib/AltoRouter/LICENSE \
  https://raw.githubusercontent.com/dannyvankooten/AltoRouter/master/LICENSE
```

Expected: 두 파일이 정상 다운로드. `AltoRouter.php` 는 약 8KB, `LICENSE` 는 MIT.

- [ ] **Step 2: 파일 유효성 확인**

```bash
php -l lib/AltoRouter/AltoRouter.php
head -5 lib/AltoRouter/AltoRouter.php
```

Expected: `No syntax errors detected`. 파일 상단에 `class AltoRouter` 또는 `<?php` 가 보임.

- [ ] **Step 3: `.gitignore` 업데이트**

루트의 `.gitignore` 에 다음 줄 추가 (기존 파일이 있으면 append, 없으면 생성):

```
# Tailwind v4 standalone binary (downloaded locally per developer)
/tailwindcss
/tailwindcss.exe

# Temp files
/tmp/
```

```bash
# 기존 .gitignore 가 있는지 확인
ls -la .gitignore 2>/dev/null || touch .gitignore
cat >> .gitignore <<'EOF'

# Tailwind v4 standalone binary (downloaded locally per developer)
/tailwindcss
/tailwindcss.exe

# Temp files
/tmp/
EOF
```

- [ ] **Step 4: 커밋**

```bash
git add lib/AltoRouter .gitignore
git commit -m "feat: add AltoRouter library (vendored, no composer)"
```

### Task 6: `core/helpers.php` 작성

**Files:**
- Create: `core/helpers.php`

- [ ] **Step 1: 디렉토리 생성**

```bash
mkdir -p /home/kagla/gnuboard5/core
```

- [ ] **Step 2: `core/helpers.php` 작성**

```php
<?php
/**
 * Routing helpers.
 *
 * g5_dispatch($target_file, $query) 는
 *  - $_GET / $_REQUEST 에 파라미터 주입
 *  - $_SERVER[PHP_SELF/SCRIPT_NAME/SCRIPT_FILENAME] 를 실제 호출된 파일처럼 조작
 *  - 기존 그누보드 파일을 require
 * 한다. 기존 그누보드 파일은 자기가 원래 URL 로 호출된 것처럼 동작한다.
 */

if (!defined('G5_PATH')) {
    define('G5_PATH', realpath(__DIR__ . '/..'));
}

function g5_dispatch($target_file, array $query = array()) {
    foreach ($query as $k => $v) {
        $_GET[$k]     = $v;
        $_REQUEST[$k] = $v;
    }
    $rel = '/' . ltrim($target_file, '/');
    $_SERVER['PHP_SELF']        = $rel;
    $_SERVER['SCRIPT_NAME']     = $rel;
    $_SERVER['SCRIPT_FILENAME'] = G5_PATH . $rel;

    require G5_PATH . $rel;
}
```

- [ ] **Step 3: syntax check**

```bash
php -l core/helpers.php
```

Expected: `No syntax errors detected in core/helpers.php`.

- [ ] **Step 4: 커밋**

```bash
git add core/helpers.php
git commit -m "feat: add g5_dispatch helper for routing callbacks"
```

### Task 7: `core/router-bootstrap.php` 작성

**Files:**
- Create: `core/router-bootstrap.php`

- [ ] **Step 1: 파일 작성**

```php
<?php
/**
 * Router bootstrap.
 *
 * Called from the new index.php (front controller). Responsibilities:
 *   1) Load AltoRouter library.
 *   2) Load helpers (g5_dispatch).
 *   3) Auto-load all routes/*.php (ascending filename order).
 *   4) Match request against registered routes.
 *   5) Execute the matched callback, or emit 404.
 */

require __DIR__ . '/../lib/AltoRouter/AltoRouter.php';
require __DIR__ . '/helpers.php';

$router = new AltoRouter();

// Auto-detect base path (installation under subdirectory, e.g. /gnuboard5/).
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($script_dir !== '/' && $script_dir !== '' && $script_dir !== '.') {
    $router->setBasePath(rtrim($script_dir, '/'));
}

// Auto-load routes/*.php in ascending filename order.
$route_files = glob(__DIR__ . '/../routes/*.php');
if ($route_files) {
    sort($route_files);
    foreach ($route_files as $file) {
        require $file;
    }
}

$match = $router->match();
if ($match && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
    exit;
}

// No match → 404.
http_response_code(404);
echo '404 Not Found';
exit;
```

- [ ] **Step 2: syntax check**

```bash
php -l core/router-bootstrap.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: 커밋**

```bash
git add core/router-bootstrap.php
git commit -m "feat: add router bootstrap with auto-loading routes"
```

### Task 8: `.htaccess` 작성

**Files:**
- Create: `.htaccess`

- [ ] **Step 1: 파일 작성**

```apache
# gnuboard5 routing modernization - front controller rewrite
RewriteEngine On

# 1) 구 URL 패턴(/bbs/*.php, /adm/*.php) → index.php 경유 (legacy 301 처리용)
RewriteRule ^(bbs|adm)/[^/]+\.php$ index.php [L,QSA]

# 2) 실제 파일/디렉토리(assets, shop 결제 콜백 등)는 그대로 서빙
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# 3) 나머지 모든 요청 → front controller
RewriteRule ^ index.php [L]
```

- [ ] **Step 2: Apache 환경에서 동작 확인 (PHP 내장 서버는 .htaccess 무시)**

PHP 내장 서버에서 개발 중이라면, 내장 서버용 라우터 스크립트로 같은 효과를 낸다. `scripts/serve.php` 를 만들지 말고 한 줄 명령으로:

```bash
# 개발 중 PHP 내장 서버 사용 시
php -S 127.0.0.1:8000 -t . index.php
```

이렇게 하면 모든 요청이 `index.php` 로 라우팅된다.

- [ ] **Step 3: 커밋**

```bash
git add .htaccess
git commit -m "feat: add .htaccess with mod_rewrite rules for front controller"
```

### Task 9: `home.php` 생성 (기존 `index.php` 내용 이동)

**Files:**
- Create: `home.php` (기존 `index.php` 내용 복사)
- Modify: `index.php` (front controller 로 교체는 Task 10에서)

- [ ] **Step 1: 기존 index.php 를 home.php 로 복사**

```bash
cd /home/kagla/gnuboard5
cp index.php home.php
```

- [ ] **Step 2: `home.php` 내용 확인**

```bash
head -20 home.php
```

Expected: 기존 `index.php` 의 내용이 그대로 보임 (메인 페이지 뷰 로직).

- [ ] **Step 3: syntax check**

```bash
php -l home.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 4: 커밋**

```bash
git add home.php
git commit -m "feat: copy current index.php to home.php (becomes main page view)"
```

### Task 10: 새 `index.php` front controller 로 교체

**Files:**
- Modify: `index.php` (전체 교체)

- [ ] **Step 1: `index.php` 전체를 새 front controller 로 교체**

```php
<?php
// Front controller — entrypoint for all HTTP requests.
// All request logic lives in core/router-bootstrap.php and routes/*.php.
define('_GNUBOARD_', true);
require __DIR__ . '/core/router-bootstrap.php';
```

- [ ] **Step 2: syntax check**

```bash
php -l index.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: 아직 routes 가 없으므로 / 접근 시 404 예상**

```bash
# 내장 서버 띄운 상태에서
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/"
```

Expected: `404` (routes/00-root.php 가 아직 없음). 이것이 정상이다.

- [ ] **Step 4: 커밋**

```bash
git add index.php
git commit -m "feat: replace index.php with front controller"
```

---

## Phase D: 라우트 정의

### Task 11: `routes/00-root.php` — 루트 `/` 라우트

**Files:**
- Create: `routes/00-root.php`

- [ ] **Step 1: 디렉토리 생성**

```bash
mkdir -p /home/kagla/gnuboard5/routes
```

- [ ] **Step 2: `routes/00-root.php` 작성**

```php
<?php
// Root route — "/" serves home.php (former index.php content).

$router->map('GET', '/', function() {
    g5_dispatch('home.php');
});
```

- [ ] **Step 3: syntax check**

```bash
php -l routes/00-root.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 4: / 접근 테스트**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/"
```

Expected: `200`. (기존 index.php 와 동일한 메인 페이지 내용)

- [ ] **Step 5: 커밋**

```bash
git add routes/00-root.php
git commit -m "feat(routes): add root route mapping / to home.php"
```

### Task 12: `routes/01-boards.php` — 게시판 라우트

**Files:**
- Create: `routes/01-boards.php`

- [ ] **Step 1: 파일 작성**

```php
<?php
// Board routes.

// 게시판 목록: /boards/free → bbs/board.php?bo_table=free
$router->map('GET', '/boards/[*:bo_table]', function($bo_table) {
    g5_dispatch('bbs/board.php', array('bo_table' => $bo_table));
});

// 게시물 상세: /boards/free/123 → bbs/board.php?bo_table=free&wr_id=123
$router->map('GET', '/boards/[*:bo_table]/[i:wr_id]', function($bo_table, $wr_id) {
    g5_dispatch('bbs/board.php', array('bo_table' => $bo_table, 'wr_id' => $wr_id));
});

// 글쓰기 폼: /boards/free/write
$router->map('GET', '/boards/[*:bo_table]/write', function($bo_table) {
    g5_dispatch('bbs/write.php', array('bo_table' => $bo_table));
});

// 글 수정 폼: /boards/free/write/123
$router->map('GET', '/boards/[*:bo_table]/write/[i:wr_id]', function($bo_table, $wr_id) {
    g5_dispatch('bbs/write.php', array('bo_table' => $bo_table, 'wr_id' => $wr_id));
});

// 글쓰기 처리 (POST)
$router->map('POST', '/boards/[*:bo_table]/write', function($bo_table) {
    g5_dispatch('bbs/write_update.php', array('bo_table' => $bo_table));
});

// 삭제
$router->map('GET', '/boards/[*:bo_table]/delete/[i:wr_id]', function($bo_table, $wr_id) {
    g5_dispatch('bbs/delete.php', array('bo_table' => $bo_table, 'wr_id' => $wr_id));
});

// 검색
$router->map('GET', '/boards/[*:bo_table]/search', function($bo_table) {
    g5_dispatch('bbs/search.php', array('bo_table' => $bo_table));
});

// RSS
$router->map('GET', '/boards/[*:bo_table]/rss', function($bo_table) {
    g5_dispatch('bbs/rss.php', array('bo_table' => $bo_table));
});

// 첨부 다운로드: /boards/free/123/download/0
$router->map('GET', '/boards/[*:bo_table]/[i:wr_id]/download/[i:no]',
    function($bo_table, $wr_id, $no) {
        g5_dispatch('bbs/download.php',
            array('bo_table' => $bo_table, 'wr_id' => $wr_id, 'no' => $no));
    }
);

// 추천
$router->map('POST', '/boards/[*:bo_table]/[i:wr_id]/good', function($bo_table, $wr_id) {
    g5_dispatch('bbs/good.php', array('bo_table' => $bo_table, 'wr_id' => $wr_id));
});

// 스크랩
$router->map('POST', '/scrap/[*:bo_table]/[i:wr_id]', function($bo_table, $wr_id) {
    g5_dispatch('bbs/scrap.php', array('bo_table' => $bo_table, 'wr_id' => $wr_id));
});

// 게시판 그룹
$router->map('GET', '/group/[*:gr_id]', function($gr_id) {
    g5_dispatch('bbs/group.php', array('gr_id' => $gr_id));
});

// 전체 새글
$router->map('GET', '/new', function() {
    g5_dispatch('bbs/new.php');
});
```

- [ ] **Step 2: syntax check**

```bash
php -l routes/01-boards.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: 실제 게시판이 있다면 접근 테스트**

그누보드가 설치되어 `free` 또는 유사한 게시판이 있다고 가정:

```bash
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/boards/free"
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/new"
```

Expected: `/boards/free` 는 존재하면 `200`, 없으면 그누보드의 "없는 게시판" 응답 (`200` 또는 alert). `/new` 는 `200`.

- [ ] **Step 4: `scripts/smoke.sh` 에 새 URL 추가**

아래 라인들의 주석을 해제 (또는 추가):

```bash
echo "=== Post-refactor URLs (boards) ==="
check GET "/new" 200
# 실제 존재하는 게시판이 있다면:
# check GET "/boards/YOUR_BOARD" 200
```

- [ ] **Step 5: 커밋**

```bash
git add routes/01-boards.php scripts/smoke.sh
git commit -m "feat(routes): add board routes (/boards, /group, /new, download, good, scrap)"
```

### Task 13: `routes/02-auth.php` — 인증 라우트

**Files:**
- Create: `routes/02-auth.php`

- [ ] **Step 1: 파일 작성**

```php
<?php
// Authentication routes — login, logout, registration, password reset, email certify.

$router->map('GET',  '/login',  function() { g5_dispatch('bbs/login.php'); });
$router->map('POST', '/login',  function() { g5_dispatch('bbs/login_check.php'); });
$router->map('GET',  '/logout', function() { g5_dispatch('bbs/logout.php'); });

$router->map('GET',  '/register',            function() { g5_dispatch('bbs/register.php'); });
$router->map('GET',  '/register/form',       function() { g5_dispatch('bbs/register_form.php'); });
$router->map('POST', '/register/form',       function() { g5_dispatch('bbs/register_form_update.php'); });
$router->map('GET',  '/register/result',     function() { g5_dispatch('bbs/register_result.php'); });

$router->map('GET',  '/password/lost',       function() { g5_dispatch('bbs/password_lost.php'); });
$router->map('POST', '/password/lost',       function() { g5_dispatch('bbs/password_lost_certify.php'); });
$router->map('GET',  '/password/reset',      function() { g5_dispatch('bbs/password_reset.php'); });
$router->map('POST', '/password/reset',      function() { g5_dispatch('bbs/password_reset_update.php'); });

$router->map('GET',  '/email/certify',       function() { g5_dispatch('bbs/email_certify.php'); });
```

- [ ] **Step 2: syntax check**

```bash
php -l routes/02-auth.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: smoke 확인**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/login"
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/register"
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/password/lost"
```

Expected: 세 URL 모두 `200`.

- [ ] **Step 4: `scripts/smoke.sh` 에 라인 추가**

```bash
echo "=== Post-refactor URLs (auth) ==="
check GET "/login" 200
check GET "/register" 200
check GET "/password/lost" 200
```

- [ ] **Step 5: 커밋**

```bash
git add routes/02-auth.php scripts/smoke.sh
git commit -m "feat(routes): add auth routes (login, register, password, email certify)"
```

### Task 14: `routes/03-members.php` — 회원 라우트

**Files:**
- Create: `routes/03-members.php`

- [ ] **Step 1: 파일 작성**

```php
<?php
// Member routes.

// 특정 회원 프로필: /members/admin → bbs/profile.php?mb_id=admin
$router->map('GET', '/members/me/confirm', function() { g5_dispatch('bbs/member_confirm.php'); });
$router->map('GET', '/members/me/leave',   function() { g5_dispatch('bbs/member_leave.php'); });

$router->map('GET', '/members/[*:mb_id]', function($mb_id) {
    g5_dispatch('bbs/profile.php', array('mb_id' => $mb_id));
});

// 포인트 내역
$router->map('GET', '/points', function() { g5_dispatch('bbs/point.php'); });
```

**주의**: `/members/me/confirm` 처럼 `me` 가 먼저 오는 구체적 라우트를 더 일반적인 `/members/[*:mb_id]` **앞에** 등록해야 매칭 우선순위가 맞는다.

- [ ] **Step 2: syntax check**

```bash
php -l routes/03-members.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: smoke 확인 (그누보드에 `admin` 계정이 있다고 가정)**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/members/admin"
```

Expected: `200`. 로그인 요구 페이지일 수 있으니 정확한 응답은 설치 상태에 따라 다름. `404` 만 아니면 OK.

- [ ] **Step 4: 커밋**

```bash
git add routes/03-members.php
git commit -m "feat(routes): add member routes (/members, /points)"
```

### Task 15: `routes/04-admin.php` — 관리자 catch-all

**Files:**
- Create: `routes/04-admin.php`

- [ ] **Step 1: 파일 작성**

```php
<?php
// Admin catch-all route: /admin/* → admin/*.php

$router->map('GET|POST', '/admin', function() {
    g5_dispatch('admin/index.php');
});

$router->map('GET|POST', '/admin/[**:path]', function($path) {
    // path traversal 방지
    if (strpos($path, '..') !== false) {
        http_response_code(404);
        echo 'Not Found';
        return;
    }
    // include-only / private 파일 접근 차단
    if (preg_match('/\.(inc|lib)$|\/_/', $path)) {
        http_response_code(404);
        echo 'Not Found';
        return;
    }
    $target = 'admin/' . $path . '.php';
    if (!is_file(G5_PATH . '/' . $target)) {
        http_response_code(404);
        echo 'Not Found';
        return;
    }
    g5_dispatch($target);
});
```

- [ ] **Step 2: syntax check**

```bash
php -l routes/04-admin.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: smoke 확인**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/admin"
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/admin/board_list"
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/admin/__nope__"
```

Expected: 
- `/admin` → `200` 또는 로그인 페이지로 `302`
- `/admin/board_list` → `200` 또는 `302`
- `/admin/__nope__` → `404`

- [ ] **Step 4: 커밋**

```bash
git add routes/04-admin.php
git commit -m "feat(routes): add admin catch-all route mapping /admin/* to admin/*.php"
```

### Task 16: `routes/05-shop.php` — 쇼핑몰 catch-all

**Files:**
- Create: `routes/05-shop.php`

- [ ] **Step 1: 파일 작성**

```php
<?php
// Shop catch-all route: /shop/* → shop/*.php
// 주의: /shop/toss/ready.php 같은 PG 콜백은 .htaccess 2번 규칙(실제 파일 우선 서빙)에
// 의해 라우터를 거치지 않고 직접 실행된다.

$router->map('GET|POST', '/shop', function() {
    g5_dispatch('shop/index.php');
});

$router->map('GET|POST', '/shop/[**:path]', function($path) {
    if (strpos($path, '..') !== false) {
        http_response_code(404);
        echo 'Not Found';
        return;
    }
    if (preg_match('/\.(inc|lib)$|\/_/', $path)) {
        http_response_code(404);
        echo 'Not Found';
        return;
    }
    $target = 'shop/' . $path . '.php';
    if (!is_file(G5_PATH . '/' . $target)) {
        http_response_code(404);
        echo 'Not Found';
        return;
    }
    g5_dispatch($target);
});
```

- [ ] **Step 2: syntax check**

```bash
php -l routes/05-shop.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: smoke 확인**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/shop"
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/shop/cart"
```

Expected: 각각 `200` (또는 세션 없음으로 적절한 응답).

- [ ] **Step 4: 커밋**

```bash
git add routes/05-shop.php
git commit -m "feat(routes): add shop catch-all route mapping /shop/* to shop/*.php"
```

### Task 17: `routes/90-legacy.php` — 구 URL 301 리다이렉트

**Files:**
- Create: `routes/90-legacy.php`

- [ ] **Step 1: 파일 작성**

```php
<?php
// Legacy URL handler.
// .htaccess 1번 규칙이 /bbs/*.php, /adm/*.php 를 index.php 로 리라이트하므로
// 이 시점에 $_SERVER['REQUEST_URI'] 는 원래 구 URL 을 보존한다.
// 매핑되는 URL 은 301 리다이렉트, 매핑 없는 legacy URL 은 404.

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 설치 서브디렉토리(예: /gnuboard5/bbs/login.php) 지원 위해 basePath 제거
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($script_dir !== '/' && $script_dir !== '' && $script_dir !== '.') {
    $uri = preg_replace('#^' . preg_quote(rtrim($script_dir, '/'), '#') . '#', '', $uri, 1);
}

if (!preg_match('#^/(bbs|adm)/([^/]+)\.php$#', $uri, $m)) {
    return;  // legacy URL 이 아니면 통과 (이후 라우트들이 처리)
}

list(, $section, $page) = $m;
$q = $_GET;

$map = array(
    // 인증
    'bbs/login'           => '/login',
    'bbs/logout'          => '/logout',
    'bbs/register'        => '/register',
    'bbs/register_form'   => '/register/form',
    'bbs/register_result' => '/register/result',
    'bbs/password_lost'   => '/password/lost',
    'bbs/password_reset'  => '/password/reset',
    'bbs/email_certify'   => '/email/certify',

    // 회원
    'bbs/profile' => function(&$q) {
        $id = isset($q['mb_id']) ? $q['mb_id'] : '';
        unset($q['mb_id']);
        return '/members/' . rawurlencode($id);
    },
    'bbs/member_confirm' => '/members/me/confirm',
    'bbs/member_leave'   => '/members/me/leave',
    'bbs/point'          => '/points',

    // 게시판
    'bbs/board' => function(&$q) {
        $t = isset($q['bo_table']) ? $q['bo_table'] : '';
        $w = isset($q['wr_id'])    ? $q['wr_id']    : '';
        unset($q['bo_table'], $q['wr_id']);
        return $w ? "/boards/{$t}/{$w}" : "/boards/{$t}";
    },
    'bbs/write' => function(&$q) {
        $t = isset($q['bo_table']) ? $q['bo_table'] : '';
        $w = isset($q['wr_id'])    ? $q['wr_id']    : '';
        unset($q['bo_table'], $q['wr_id']);
        return $w ? "/boards/{$t}/write/{$w}" : "/boards/{$t}/write";
    },
    'bbs/search' => function(&$q) {
        $t = isset($q['bo_table']) ? $q['bo_table'] : '';
        unset($q['bo_table']);
        return "/boards/{$t}/search";
    },
    'bbs/rss' => function(&$q) {
        $t = isset($q['bo_table']) ? $q['bo_table'] : '';
        unset($q['bo_table']);
        return "/boards/{$t}/rss";
    },
    'bbs/new'   => '/new',
    'bbs/group' => function(&$q) {
        $g = isset($q['gr_id']) ? $q['gr_id'] : '';
        unset($q['gr_id']);
        return '/group/' . rawurlencode($g);
    },
);

$key = "{$section}/{$page}";
if (isset($map[$key])) {
    $target = is_callable($map[$key]) ? $map[$key]($q) : $map[$key];
    if (!empty($q)) {
        $target .= '?' . http_build_query($q);
    }
    header('Location: ' . $target, true, 301);
    exit;
}

// adm/* → /admin/* 폴더 리네임 호환
if ($section === 'adm') {
    $target = '/admin/' . $page;
    if (!empty($q)) {
        $target .= '?' . http_build_query($q);
    }
    header('Location: ' . $target, true, 301);
    exit;
}

// 매핑 없는 legacy URL
http_response_code(404);
echo 'Page not found.';
exit;
```

- [ ] **Step 2: syntax check**

```bash
php -l routes/90-legacy.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: 301 리다이렉트 확인**

```bash
# 구 URL → 신 URL 301
curl -s -o /dev/null -w "%{http_code} Location=%{redirect_url}\n" "${BASE_URL}/bbs/login.php"
curl -s -o /dev/null -w "%{http_code} Location=%{redirect_url}\n" "${BASE_URL}/adm/board_list.php"
curl -s -o /dev/null -w "%{http_code} Location=%{redirect_url}\n" "${BASE_URL}/bbs/board.php?bo_table=free&wr_id=1"
```

Expected:
- `/bbs/login.php` → `301`, `Location=/login`
- `/adm/board_list.php` → `301`, `Location=/admin/board_list`
- `/bbs/board.php?bo_table=free&wr_id=1` → `301`, `Location=/boards/free/1`

- [ ] **Step 4: `scripts/smoke.sh` 에 레거시 라인 추가**

```bash
echo "=== Legacy URL redirects ==="
check GET "/bbs/login.php" 301
check GET "/adm/board_list.php" 301
```

- [ ] **Step 5: 커밋**

```bash
git add routes/90-legacy.php scripts/smoke.sh
git commit -m "feat(routes): add legacy URL 301 redirects for backward compatibility"
```

### Task 18: `routes/99-custom.php` — 사용자 확장 자리

**Files:**
- Create: `routes/99-custom.php`

- [ ] **Step 1: 파일 작성**

```php
<?php
/**
 * User-defined custom routes.
 *
 * 이 파일에 자유롭게 라우트를 추가하세요. 예:
 *
 *   $router->map('GET', '/about', function() {
 *       g5_dispatch('pages/about.php');
 *   });
 */
```

- [ ] **Step 2: syntax check**

```bash
php -l routes/99-custom.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: 커밋**

```bash
git add routes/99-custom.php
git commit -m "feat(routes): add placeholder file for user-defined custom routes"
```

### Task 19: Phase D 통합 smoke test

- [ ] **Step 1: 전체 smoke 실행**

```bash
cd /home/kagla/gnuboard5
./scripts/smoke.sh
```

Expected: 모든 라인 ✓, 종료 `OK`.

- [ ] **Step 2: 실패 라인이 있으면 해당 Task 로 돌아가 수정**

증상별 점검:
- `/login` 404 → `routes/02-auth.php` 가 auto-load 됐는지 (`php -l` 및 `glob()`)
- `/bbs/login.php` 가 200(리다이렉트 안 됨) → `.htaccess` 1번 규칙 동작 확인 (Apache 에서만 동작, 내장 서버는 `-t . index.php` 옵션 필수)
- `/boards/free` 200 인데 빈 페이지 → 그누보드 DB 설치 및 `free` 게시판 존재 여부 확인

---

## Phase E: Tailwind 인프라

### Task 20: Tailwind v4 바이너리 다운로드

**Files:**
- Create: `tailwindcss` (`.gitignore` 처리됨)

- [ ] **Step 1: 바이너리 다운로드 (Linux x64 기준)**

```bash
cd /home/kagla/gnuboard5
curl -L -o tailwindcss \
  https://github.com/tailwindlabs/tailwindcss/releases/download/v4.2.2/tailwindcss-linux-x64
chmod +x tailwindcss
```

macOS: `tailwindcss-macos-arm64` 또는 `tailwindcss-macos-x64`. Windows: `tailwindcss-windows-x64.exe`.

- [ ] **Step 2: `/tmp` noexec 대응 (필요 시)**

```bash
mkdir -p $HOME/tmp
export TMPDIR=$HOME/tmp
./tailwindcss --help | head -5
```

Expected: Tailwind 도움말 출력. `ERR_DLOPEN_FAILED` 가 나오면 `TMPDIR` 환경변수를 `~/.bashrc` 에 영구 추가.

- [ ] **Step 3: 바이너리가 `.gitignore` 로 제외되는지 확인**

```bash
git status
```

Expected: `tailwindcss` 파일이 "untracked" 에 나타나지 않아야 한다 (Task 5 에서 gitignore 에 추가됨).

### Task 21: `assets/css/input.css` 작성

**Files:**
- Create: `assets/css/input.css`

- [ ] **Step 1: 디렉토리 생성**

```bash
mkdir -p /home/kagla/gnuboard5/assets/css
```

- [ ] **Step 2: `assets/css/input.css` 작성**

```css
@import "tailwindcss";

/* 다크모드: class 전략 (<html class="dark">) */
@custom-variant dark (&:where(.dark, .dark *));

/* Tailwind 가 스캔할 소스 범위 — theme/basic 과 home.php 만 */
@source "../../theme/basic/**/*.php";
@source "../../home.php";

/* 라이트 모드 토큰 */
@theme {
  --color-bg:      #ffffff;
  --color-surface: #f9fafb;
  --color-text:    #111827;
  --color-muted:   #6b7280;
  --color-border:  #e5e7eb;
  --color-brand:   #3b82f6;
}

/* 다크 모드 토큰 오버라이드 */
.dark {
  --color-bg:      #0b0f17;
  --color-surface: #111827;
  --color-text:    #e5e7eb;
  --color-muted:   #9ca3af;
  --color-border:  #374151;
}
```

- [ ] **Step 3: 커밋**

```bash
git add assets/css/input.css
git commit -m "feat(css): add Tailwind v4 input.css with dark mode tokens"
```

### Task 22: 첫 빌드 + `assets/css/app.css` 생성 + 커밋

**Files:**
- Create: `assets/css/app.css` (빌드 산출물, 커밋 대상)

- [ ] **Step 1: 빌드 실행**

```bash
cd /home/kagla/gnuboard5
TMPDIR=$HOME/tmp ./tailwindcss -i assets/css/input.css -o assets/css/app.css --minify
```

Expected: `Done` 또는 유사 메시지. `assets/css/app.css` 파일 생성됨. 이 시점엔 theme/basic 에 Tailwind 클래스가 없으므로 매우 작은 CSS 파일.

- [ ] **Step 2: 생성 확인**

```bash
ls -l assets/css/app.css
head -5 assets/css/app.css
```

Expected: 파일 존재, 상단에 Tailwind preflight 스타일.

- [ ] **Step 3: 서버에서 접근 확인**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/assets/css/app.css"
```

Expected: `200` (실제 파일이므로 `.htaccess` 2번 규칙으로 직접 서빙).

- [ ] **Step 4: 커밋**

```bash
git add assets/css/app.css
git commit -m "build(css): initial Tailwind build output"
```

---

## Phase F: 테마 재작성

### Task 23: 기존 `theme/basic/head.php` 백업 + 새 버전 작성

**Files:**
- Rename: `theme/basic/head.php` → `theme/basic/head.legacy.php`
- Create: `theme/basic/head.php` (Tailwind 기반)

- [ ] **Step 1: 기존 파일 백업**

```bash
cd /home/kagla/gnuboard5
git mv theme/basic/head.php theme/basic/head.legacy.php
```

백업 이유: 그누보드의 `head.sub.php` 등에서 호출하는 공통 변수/상수 로직이 들어있을 수 있으므로, 새 `head.php` 에서 **`head.legacy.php` 의 PHP 로직 부분만 선별적으로 include** 하거나 참조한다.

- [ ] **Step 2: 기존 `head.legacy.php` 에서 PHP 로직 부분 파악**

```bash
head -50 theme/basic/head.legacy.php
```

그누보드 관례상 상단에 `include_once G5_PATH.'/head.sub.php';` 류의 부트스트랩 코드가 있다. 이건 DB, 세션, `$g5` 변수 초기화에 필요하므로 **새 `head.php` 상단에도 동일하게 포함**해야 한다.

- [ ] **Step 3: 새 `theme/basic/head.php` 작성**

```php
<?php
if (!defined('_GNUBOARD_')) exit;

// 그누보드 head 초기화 로직 (기존 head.legacy.php 상단과 동일)
include_once(G5_PATH.'/head.sub.php');
?><!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="<?php echo G5_CHARSET; ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $g5['title']; ?></title>

<!-- FOUC 방지: 다크모드 class 적용을 CSS 로드 전에 -->
<script>
(function(){
  try {
    var t = localStorage.getItem('theme');
    if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    }
  } catch(e) {}
})();
</script>

<link rel="stylesheet" href="<?php echo G5_URL; ?>/assets/css/app.css">

<?php
// 기존 그누보드 head 렌더링이 필요로 하는 추가 include/변수들을
// head.legacy.php 에서 복사 (케이스별로 다르므로 Step 4 에서 병합).
?>
</head>
<body class="min-h-screen bg-[var(--color-bg)] text-[var(--color-text)] antialiased">

<header class="border-b border-[var(--color-border)]">
  <div class="max-w-6xl mx-auto flex items-center justify-between px-4 py-3">
    <a href="<?php echo G5_URL; ?>/" class="text-lg font-semibold">
      <?php echo $config['cf_title']; ?>
    </a>
    <nav class="flex items-center gap-4">
      <?php if (!isset($member['mb_id']) || $member['mb_id'] === '') { ?>
        <a href="<?php echo G5_URL; ?>/login" class="hover:underline">로그인</a>
        <a href="<?php echo G5_URL; ?>/register" class="hover:underline">회원가입</a>
      <?php } else { ?>
        <span class="text-sm text-[var(--color-muted)]">
          <?php echo htmlspecialchars($member['mb_nick']); ?>
        </span>
        <a href="<?php echo G5_URL; ?>/logout" class="hover:underline">로그아웃</a>
      <?php } ?>
      <button onclick="toggleTheme()" aria-label="다크모드 전환"
              class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">
        <span class="dark:hidden">🌙</span>
        <span class="hidden dark:inline">☀️</span>
      </button>
    </nav>
  </div>
</header>

<script>
function toggleTheme(){
  var isDark = document.documentElement.classList.toggle('dark');
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
}
</script>

<main class="max-w-6xl mx-auto px-4 py-6">
```

- [ ] **Step 4: `head.legacy.php` 와 비교해 누락된 필수 include 병합**

```bash
diff theme/basic/head.legacy.php theme/basic/head.php | less
```

새 `head.php` 에 누락된 **PHP 로직** (예: `include_once G5_THEME_PATH.'/_common.php'`, `$g5['title']` 설정 등) 이 있으면 `<head>` 섹션 상단에 추가. **HTML/CSS 부분은 새 버전을 우선**하되, 누락된 메타태그/JS가 있으면 포함.

- [ ] **Step 5: syntax check**

```bash
php -l theme/basic/head.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 6: Tailwind 재빌드 (새 클래스가 스캔되어 app.css 에 포함되어야 함)**

```bash
TMPDIR=$HOME/tmp ./tailwindcss -i assets/css/input.css -o assets/css/app.css --minify
```

- [ ] **Step 7: 메인 페이지 수동 확인**

브라우저에서 `${BASE_URL}/` 열기. 다음 확인:
- 새 헤더가 보임 (로그인/회원가입 링크 + 달 이모지 토글 버튼)
- 달 버튼 클릭 시 다크모드 토글 동작
- 새로고침 후 다크모드 유지 (localStorage)
- 운영체제 다크모드 감지 동작

- [ ] **Step 8: 커밋**

```bash
git add theme/basic/head.php theme/basic/head.legacy.php assets/css/app.css
git commit -m "feat(theme): rewrite theme/basic/head.php with Tailwind + dark mode toggle"
```

### Task 24: `theme/basic/tail.php` 재작성

**Files:**
- Rename: `theme/basic/tail.php` → `theme/basic/tail.legacy.php`
- Create: `theme/basic/tail.php`

- [ ] **Step 1: 백업**

```bash
cd /home/kagla/gnuboard5
git mv theme/basic/tail.php theme/basic/tail.legacy.php
```

- [ ] **Step 2: 새 `theme/basic/tail.php` 작성**

```php
<?php
if (!defined('_GNUBOARD_')) exit;
?>
</main>

<footer class="mt-12 border-t border-[var(--color-border)]">
  <div class="max-w-6xl mx-auto px-4 py-6 text-sm text-[var(--color-muted)]">
    <p>&copy; <?php echo date('Y'); ?> <?php echo $config['cf_title']; ?></p>
  </div>
</footer>

<?php
// 기존 tail 하단 로직 (tail.sub.php 등) 이 있으면 여기 include
if (is_file(G5_PATH . '/tail.sub.php')) {
    include_once(G5_PATH . '/tail.sub.php');
}
?>
</body>
</html>
```

- [ ] **Step 3: `tail.legacy.php` 와 비교해 누락 로직 점검**

```bash
diff theme/basic/tail.legacy.php theme/basic/tail.php | less
```

필요한 include/변수 해제가 있으면 병합.

- [ ] **Step 4: syntax check + 재빌드**

```bash
php -l theme/basic/tail.php
TMPDIR=$HOME/tmp ./tailwindcss -i assets/css/input.css -o assets/css/app.css --minify
```

- [ ] **Step 5: 브라우저 확인**

`${BASE_URL}/` 에서 페이지 하단 푸터가 보이고 다크모드에 반응하는지 확인.

- [ ] **Step 6: 커밋**

```bash
git add theme/basic/tail.php theme/basic/tail.legacy.php assets/css/app.css
git commit -m "feat(theme): rewrite theme/basic/tail.php with Tailwind"
```

### Task 25: `theme/basic/index.php` 재작성

**Files:**
- Rename: `theme/basic/index.php` → `theme/basic/index.legacy.php`
- Create: `theme/basic/index.php`

- [ ] **Step 1: 백업**

```bash
cd /home/kagla/gnuboard5
git mv theme/basic/index.php theme/basic/index.legacy.php
```

- [ ] **Step 2: 새 `theme/basic/index.php` 작성**

기존 `index.legacy.php` 의 핵심 위젯/블록 구성을 유지하되 Tailwind 반응형 레이아웃으로 감싼다. **최소한의 레이아웃만** 제공하고, 세부 위젯은 기존 그누보드 `latest`/`popular`/`outlogin` 함수 호출을 그대로 사용한다.

```php
<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/latest.lib.php');
?>

<section class="grid grid-cols-1 md:grid-cols-3 gap-6">
  <div class="md:col-span-2 space-y-6">
    <h2 class="text-xl font-bold">최근 게시글</h2>

    <?php
    // 그누보드의 latest() 함수가 HTML 반환. 기본 스킨으로 렌더.
    echo latest('theme/basic', 'notice', 5, 25);
    echo latest('theme/basic', 'free',   5, 25);
    ?>
  </div>

  <aside class="space-y-4">
    <div class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
      <h3 class="font-semibold mb-2">바로가기</h3>
      <ul class="space-y-1 text-sm">
        <li><a class="hover:underline" href="<?php echo G5_URL; ?>/new">전체 새글</a></li>
        <li><a class="hover:underline" href="<?php echo G5_URL; ?>/members/me/confirm">내 프로필</a></li>
        <li><a class="hover:underline" href="<?php echo G5_URL; ?>/points">포인트</a></li>
      </ul>
    </div>
  </aside>
</section>
```

- [ ] **Step 3: syntax check + 재빌드**

```bash
php -l theme/basic/index.php
TMPDIR=$HOME/tmp ./tailwindcss -i assets/css/input.css -o assets/css/app.css --minify
```

- [ ] **Step 4: 브라우저 확인**

`${BASE_URL}/` 이 새 레이아웃으로 렌더되는지 확인. 데스크톱(3단 그리드) / 모바일(1단) 반응형 확인.

**주의**: `latest()` 함수 호출에 사용한 게시판 테이블명(`notice`, `free`)은 실제 설치된 게시판에 맞게 교체. 게시판이 없으면 해당 라인 삭제.

- [ ] **Step 5: 커밋**

```bash
git add theme/basic/index.php theme/basic/index.legacy.php assets/css/app.css
git commit -m "feat(theme): rewrite theme/basic/index.php as responsive Tailwind grid"
```

### Task 26: 모바일 자동 분기 비활성화

**Files:**
- Modify: `common.php` 또는 관리자 설정

그누보드는 `common.php` 하단에서 모바일 디바이스 감지 후 `G5_IS_MOBILE` 상수와 `$g5['device']` 를 설정한다. 이 값에 따라 `theme/basic/mobile/` 로 자동 라우팅된다. 반응형 단일 뷰로 통합하려면 **그누보드 관리자 설정에서 "모바일 자동 접속" 을 OFF** 로 설정하는 것이 가장 안전하다.

- [ ] **Step 1: 관리자에서 설정 변경**

브라우저에서 `${BASE_URL}/admin` → 기본 환경설정 → "모바일 자동 접속 설정" 을 **사용 안함** 으로 변경 후 저장.

- [ ] **Step 2: 모바일 UA 로 접근 테스트**

```bash
curl -s -A "Mozilla/5.0 (iPhone; CPU iPhone OS 14_0) Mobile/15E148" \
  "${BASE_URL}/" | grep -c "theme/basic/mobile"
```

Expected: `0` (모바일 테마로 리다이렉트되지 않음).

- [ ] **Step 3: 반응형 동작 수동 확인**

브라우저 개발자 도구 → 모바일 뷰포트로 전환 → `${BASE_URL}/` 가 단일 반응형 레이아웃으로 렌더되는지 확인.

- [ ] **Step 4: 커밋 (설정 변경이 DB 에 저장되므로 파일 커밋 불필요)**

**주의**: 관리자 설정은 DB에 저장되므로 이 Task 에서는 코드 커밋이 없다. README 또는 docs 에 "설치 후 관리자에서 모바일 자동 접속 OFF 하기" 를 문서화.

```bash
# docs 에 배포 체크리스트 추가
cat >> docs/superpowers/plans/2026-04-18-gnuboard5-routing-modernization.md <<'EOF'

## 배포 체크리스트 (DB 설정)

- [ ] 관리자 → 기본 환경설정 → "모바일 자동 접속 설정" = 사용 안함
EOF
```

- [ ] **Step 5: 체크리스트 추가 커밋**

```bash
git add docs/superpowers/plans/2026-04-18-gnuboard5-routing-modernization.md
git commit -m "docs(plan): add deployment checklist for mobile auto-redirect setting"
```

---

## Phase G: 통합 검증

### Task 27: 전체 스모크 테스트 + 회귀 확인

- [ ] **Step 1: 모든 URL 카테고리 smoke 실행**

```bash
cd /home/kagla/gnuboard5
./scripts/smoke.sh
```

Expected: 모든 라인 ✓, `OK` 종료.

- [ ] **Step 2: 핵심 사용자 플로우 수동 테스트**

브라우저에서 다음 순서로 확인:

1. `/` → 메인 페이지 렌더, 다크모드 토글 동작
2. `/register` → 회원가입 이용약관
3. `/register/form` → 회원가입 폼
4. `/login` → 로그인 폼, 로그인 성공 후 `/` 로 돌아옴
5. `/boards/free` (실 게시판명) → 게시판 목록
6. `/boards/free/1` → 글 상세
7. `/boards/free/write` → 글쓰기 폼 (로그인 필요)
8. 새 글 작성 → POST → 리다이렉트
9. `/admin` → 관리자 로그인 (관리자 계정으로)
10. `/admin/board_list` → 게시판 관리
11. `/shop` → 쇼핑몰 메인 (그누보드 기본엔 상품 없음, 500 만 아니면 OK)

각 단계에서 FOUC/깨진 레이아웃/404 없는지 확인.

- [ ] **Step 3: 레거시 URL 리다이렉트 수동 확인**

브라우저 주소창에:
- `/bbs/login.php` → `/login` 으로 이동
- `/bbs/board.php?bo_table=free&wr_id=1` → `/boards/free/1`
- `/adm/board_list.php` → `/admin/board_list`

- [ ] **Step 4: PG 결제 콜백 경로 간섭 없음 확인**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "${BASE_URL}/shop/toss/ready.php"
```

Expected: `200` 또는 결제 파라미터 부재로 인한 PG 에러 응답. `404` 가 아니어야 한다 (PG 콜백 URL 유지 중요).

### Task 28: README / 개발 가이드 추가

**Files:**
- Create: `docs/superpowers/dev-guide.md`

- [ ] **Step 1: 개발 가이드 문서 작성**

```markdown
# 개발 가이드 (라우팅 현대화 이후)

## 로컬 개발 환경

### PHP 서버 기동

Apache + mod_rewrite 사용 시:

```bash
# DocumentRoot 를 /home/kagla/gnuboard5 로 설정한 가상 호스트
# http://localhost.gnu5/ 로 접근
```

PHP 내장 서버 사용 시:

```bash
cd /home/kagla/gnuboard5
php -S 127.0.0.1:8000 -t . index.php
# http://127.0.0.1:8000/ 로 접근
```

`-t . index.php` 옵션이 핵심: 모든 요청을 `index.php` 로 라우팅.

## Tailwind CSS 빌드

개발 중엔 watch 모드로 켜두기:

```bash
cd /home/kagla/gnuboard5
TMPDIR=$HOME/tmp ./tailwindcss -i assets/css/input.css -o assets/css/app.css --watch
```

파일 저장 시 자동 재빌드됨. 배포 전 1회 `--minify` 로 재빌드.

## 라우트 추가

`routes/99-custom.php` 에 자유롭게 추가:

```php
$router->map('GET', '/about', function() {
    g5_dispatch('pages/about.php');
});
```

## 스모크 테스트

```bash
BASE_URL=http://127.0.0.1:8000 ./scripts/smoke.sh
```
```

- [ ] **Step 2: 커밋**

```bash
git add docs/superpowers/dev-guide.md
git commit -m "docs: add developer guide for post-refactor workflow"
```

### Task 29: 최종 정리 및 PR 준비

- [ ] **Step 1: 전체 git log 확인**

```bash
git log --oneline main..HEAD
```

Expected: Task 별로 분리된 커밋 이력.

- [ ] **Step 2: 누락된 파일 / 임시 파일 없는지 확인**

```bash
git status
```

Expected: `tailwindcss` 바이너리만 ignored, 그 외 clean.

- [ ] **Step 3: `theme/basic/*.legacy.php` 파일 처리 결정**

세 가지 옵션:
1. **유지**: 참조용으로 남겨둠 (권장). `theme/basic/head.legacy.php` 등.
2. **삭제**: git 이력에 남아있으므로 필요 시 복구 가능.
3. **별도 디렉토리로 이동**: `theme/basic/_legacy/`.

옵션 1을 기본으로 하되, 사용자가 원하면 `rm theme/basic/*.legacy.php && git add -u && git commit -m "chore: remove legacy theme files (available in git history)"`.

- [ ] **Step 4: 최종 smoke 한 번 더**

```bash
./scripts/smoke.sh
```

Expected: `OK`.

- [ ] **Step 5: 브랜치 push 및 PR 생성 준비**

사용자에게 PR 생성 여부 확인 후 진행 (자동 push 금지).

---

## 셀프 리뷰

### Spec 커버리지

| Spec 섹션 | 구현 Task |
|---|---|
| 4-1 파일 구조 | Task 5–10, 21–22 (디렉토리/파일 생성) |
| 4-2 요청 처리 흐름 | Task 8–10 (.htaccess, index.php, router-bootstrap) |
| 5-1 `.htaccess` | Task 8 |
| 5-2 `index.php` | Task 10 |
| 5-3 router-bootstrap | Task 7 |
| 5-4 helpers | Task 6 |
| 5-5 `routes/00-root.php` | Task 11 |
| 5-6 boards | Task 12 |
| 5-7 auth | Task 13 |
| 5-8 members | Task 14 |
| 5-9 admin catch-all | Task 15 |
| 5-10 shop catch-all | Task 16 |
| 5-11 legacy 리다이렉트 | Task 17 |
| 5-12 `adm/` → `admin/` | Task 3–4 |
| 5-13 Tailwind 설정 | Task 20–22 |
| 5-14 다크모드 토글 | Task 23 |
| 5-15 `theme/basic` 재작성 | Task 23–25 |
| 5-16 모바일 분기 비활성화 | Task 26 |

모든 스펙 섹션이 1개 이상의 Task 로 매핑됨.

### Placeholder 스캔

- "TBD" / "TODO" 부재 ✓
- 모든 코드 블록에 실제 코드 ✓
- 모든 검증 단계에 실제 명령 + expected ✓

### Type/Signature 일관성

- `g5_dispatch($target_file, array $query = array())` — Task 6 에서 정의, 12–18, 23 에서 동일 시그니처로 호출 ✓
- `$router->map('METHOD', '/pattern', callback)` — AltoRouter 표준, 모든 라우트에서 동일 ✓
- `G5_PATH` 상수 — Task 6 에서 guard 포함 정의, 이후 그대로 사용 ✓

---

## 배포 체크리스트

- [ ] 개발 환경에서 `./scripts/smoke.sh` 전체 통과
- [ ] 핵심 사용자 플로우 수동 테스트 완료
- [ ] 레거시 301 리다이렉트 수동 확인
- [ ] PG 결제 콜백 URL 접근 정상 (`/shop/*/ready.php` 등)
- [ ] 프로덕션 배포 전 `./tailwindcss -i assets/css/input.css -o assets/css/app.css --minify` 실행
- [ ] 관리자 → 기본 환경설정 → "모바일 자동 접속 설정" = 사용 안함 (프로덕션 DB)
- [ ] 서버에 `.htaccess`, `index.php`, `home.php`, `core/`, `routes/`, `lib/AltoRouter/`, `assets/css/app.css`, `admin/` (리네임), `theme/basic/*.php` 업로드
