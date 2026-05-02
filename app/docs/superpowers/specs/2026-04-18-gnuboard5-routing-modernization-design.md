# 그누보드5 라우팅 현대화 + 디자인 인프라 설계

- **날짜**: 2026-04-18
- **상태**: Draft (사용자 리뷰 대기)
- **대상 저장소**: `/home/kagla/gnuboard5`
- **그누보드 버전**: 5.6.24

## 1. 개요

### 1-1. 목표

1. 그누보드5의 흩어진 `.php` 진입점을 **AltoRouter 기반 front controller 패턴**으로 통합한다.
2. URL을 사람과 검색엔진 친화적 형태로 정리한다 (`/bbs/board.php?bo_table=free&wr_id=123` → `/boards/free/123`).
3. `theme/basic` 1개 테마에 **TailwindCSS v4 + 다크모드 토글 + 반응형** 인프라를 구축한다.
4. 저가형 호스팅(카페24 수준)에서 동작해야 한다.

### 1-2. 비목표(Non-goals)

- 기능 변경 또는 추가 (기능은 기존 그누보드5 그대로 유지)
- MVC/ORM/DI 등 프레임워크식 아키텍처 도입
- `skin/` 199개 스킨 파일의 Tailwind 전환 (별도 하위 프로젝트)
- REST API 제공 (별도 하위 프로젝트)
- `admin/`, 메모, Q&A, 투표, 폼메일 등의 디자인 개편

### 1-3. 제약사항

| 제약 | 내용 |
|---|---|
| Composer 금지 | 저가 공유호스팅은 CLI/SSH가 제한. 의존성은 파일 직접 배치 |
| MVC 프레임워크 금지 | 라라벨 등은 메모리·CPU 과다. 라우팅 레이어만 추가 |
| Node.js 없음(서버) | Tailwind 빌드는 **개발자 PC에서 standalone 바이너리로** 수행 |
| `.htaccess` 지원 | Apache + mod_rewrite 전제 (카페24 기본) |
| 기존 파일 0줄 수정 원칙 | `bbs/*`, `shop/*`, `skin/*`, `common.php`, `config.php`는 원칙적으로 수정 금지. 엣지케이스 발견 시에만 최소 패치 (`adm/` → `admin/` 리네임은 예외) |

## 2. 스코프

### 2-1. 포함

- **라우팅 레이어 신설**: `index.php` front controller, `core/router-bootstrap.php`, `routes/` 폴더
- **공개 URL 매핑**: 게시판(`/boards/*`), 인증(`/login`, `/register`, `/password/*`, `/email/certify`), 회원(`/members/*`, `/points`)
- **관리자 catch-all 매핑**: `/admin/*` → `admin/*.php`
- **쇼핑몰 catch-all 매핑**: `/shop/*` → `shop/*.php`
- **구 URL 301 리다이렉트**: `routes/90-legacy.php`
- **폴더 리네임**: `adm/` → `admin/` (+ 관련 코드 `adm/` 리터럴 치환)
- **디자인 인프라**: TailwindCSS v4 빌드 파이프라인, 다크모드 토글
- **테마 개편**: `theme/basic/head.php`, `theme/basic/tail.php`, `theme/basic/index.php` 를 Tailwind로 재작성, 반응형 단일 뷰로 통합

### 2-2. 제외 (향후 하위 프로젝트)

- `skin/*` 199개 스킨 파일 Tailwind 전환
- `theme/basic/skin/`, `theme/basic/shop/` 오버라이드 스킨
- `admin/*` 디자인 개편
- `mobile/` 349개 파일 (반응형 통합 후 점진 폐기)
- REST API
- `bbs/ajax.*.php` URL 정리
- 메모(`memo*`), Q&A(`qa*`), 투표(`poll*`), 폼메일(`formmail*`)

## 3. 결정 사항 요약

| 항목 | 결정 |
|---|---|
| 라우팅 라이브러리 | AltoRouter (파일 직접 배치) |
| URL 스타일 | 복수형(`/boards`) + 짧은 단수(`/login`, `/register`) |
| 구 URL 전략 | 새 URL이 정식, 구 URL은 **301 리다이렉트**로 흡수 |
| Front controller | 기존 `index.php` 교체, 기존 메인 내용은 `home.php`로 이동 |
| 라우트 정의 스타일 | A+C: `routes/*.php` 자동 로드 + 인라인 콜백 |
| 기존 파일 실행 방식 | 라우트 콜백에서 `$_GET` + `$_SERVER`(`PHP_SELF`, `SCRIPT_NAME`, `SCRIPT_FILENAME`) 주입 후 `require` |
| 관리자 폴더 | `adm/` → `admin/` 리네임 |
| CSS 프레임워크 | TailwindCSS **v4** |
| Tailwind 빌드 | standalone 바이너리로 **개발자 PC에서 빌드**, `app.css` 커밋·업로드 |
| 다크모드 | class 전략 (`<html class="dark">`) + localStorage |

## 4. 아키텍처

### 4-1. 최종 파일 구조

```
/home/kagla/gnuboard5/
├── index.php                       ← [교체] front controller
├── home.php                        ← [신규] 기존 index.php 내용 이동
├── .htaccess                       ← [교체] mod_rewrite 규칙
├── tailwindcss                     ← Tailwind v4 바이너리 (.gitignore 권장)
│
├── assets/
│   └── css/
│       ├── input.css               ← [신규] Tailwind 소스 (커밋)
│       └── app.css                 ← [신규] 빌드 결과물 (커밋)
│
├── lib/
│   └── AltoRouter/
│       └── AltoRouter.php          ← [신규] composer 없이 파일 직접 배치
│
├── core/
│   ├── router-bootstrap.php        ← [신규] 라우터 생성 + routes 자동 로드 + 매칭
│   └── helpers.php                 ← [신규] g5_dispatch() 등 헬퍼
│
├── routes/                         ← [신규] 라우트 정의 (자동 로드, 파일명 오름차순)
│   ├── 00-root.php                 ← /
│   ├── 01-boards.php               ← 게시판
│   ├── 02-auth.php                 ← 로그인/회원가입/비밀번호
│   ├── 03-members.php              ← 회원 프로필/포인트
│   ├── 04-admin.php                ← /admin/* catch-all
│   ├── 05-shop.php                 ← /shop/* catch-all
│   ├── 90-legacy.php               ← 구 URL 301 리다이렉트
│   └── 99-custom.php               ← 사용자 자유 추가 영역
│
├── admin/                          ← [리네임] adm/ → admin/
├── bbs/                            ← [유지] 0줄 수정 원칙
├── shop/                           ← [유지]
├── skin/, theme/basic/, mobile/    ← 일부 유지, theme/basic 일부만 개편
├── common.php, config.php, lib/    ← [유지] (G5_ADMIN_DIR 상수 값만 'admin'으로)
└── docs/superpowers/specs/         ← 본 설계 문서
```

### 4-2. 요청 처리 흐름

```
사용자 요청
  │
  ▼
.htaccess (mod_rewrite)
  │  • /bbs/*.php, /adm/*.php → index.php (레거시 처리)
  │  • 실제 파일(assets/, /shop/toss/ready.php 등) → 그대로 서빙
  │  • 그 외 모든 요청 → index.php
  ▼
index.php (front controller)
  │  define('_GNUBOARD_', true);
  │  require core/router-bootstrap.php;
  ▼
core/router-bootstrap.php
  │  1. lib/AltoRouter/AltoRouter.php 로드
  │  2. core/helpers.php 로드 (g5_dispatch)
  │  3. routes/*.php 파일명 오름차순 auto-include
  │     - 각 파일은 $router->map(...) 호출
  │     - 90-legacy.php는 include 시점에 구 URL 감지 후 301 exit
  │  4. $router->match() 시도
  │  5. 매치 → 콜백 실행 → 콜백 내부에서 g5_dispatch()
  │  6. 미매치 → 404
  ▼
g5_dispatch($target_file, $query)
  │  $_GET 주입
  │  $_SERVER[PHP_SELF/SCRIPT_NAME/SCRIPT_FILENAME] 조작
  │  require G5_PATH . '/' . $target_file
  ▼
기존 그누보드 파일 (bbs/board.php 등) 정상 실행
  └─ common.php include → DB 연결, 세션, 상수 정의 등
```

## 5. 상세 설계

### 5-1. `.htaccess`

```apache
RewriteEngine On

# 1) 구 URL 패턴 → index.php로 강제 경유 (실제 파일이지만 리다이렉트 처리 위해)
RewriteRule ^(bbs|adm)/[^/]+\.php$ index.php [L,QSA]

# 2) 실제 파일/디렉토리는 그대로 서빙
#    (assets/, /shop/toss/ready.php 등 PG 콜백, 정적 이미지 등)
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# 3) 그 외 모든 요청 → front controller
RewriteRule ^ index.php [L]
```

### 5-2. `index.php` (front controller)

```php
<?php
define('_GNUBOARD_', true);
require __DIR__ . '/core/router-bootstrap.php';
```

### 5-3. `core/router-bootstrap.php`

```php
<?php
require __DIR__ . '/../lib/AltoRouter/AltoRouter.php';
require __DIR__ . '/helpers.php';

$router = new AltoRouter();

// base path 자동 탐지 (서브디렉토리 설치 대응)
$script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($script_name !== '/' && $script_name !== '') {
    $router->setBasePath(rtrim($script_name, '/'));
}

// routes/*.php 자동 로드 (파일명 오름차순)
foreach (glob(__DIR__ . '/../routes/*.php') as $file) {
    require $file;
}

// 매칭
$match = $router->match();
if ($match && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
    exit;
}

// 미매치: 404
http_response_code(404);
echo "404 Not Found";
```

### 5-4. `core/helpers.php`

```php
<?php
if (!defined('G5_PATH')) {
    define('G5_PATH', realpath(__DIR__ . '/..'));
}

function g5_dispatch($target_file, array $query = []) {
    foreach ($query as $k => $v) {
        $_GET[$k] = $v;
        $_REQUEST[$k] = $v;
    }
    $rel = '/' . ltrim($target_file, '/');
    $_SERVER['PHP_SELF']        = $rel;
    $_SERVER['SCRIPT_NAME']     = $rel;
    $_SERVER['SCRIPT_FILENAME'] = G5_PATH . $rel;
    require G5_PATH . $rel;
}
```

### 5-5. `routes/00-root.php`

```php
<?php
$router->map('GET', '/', function() {
    g5_dispatch('home.php');
});
```

### 5-6. `routes/01-boards.php`

| 메서드 | 패턴 | 대상 파일 | 쿼리 주입 |
|---|---|---|---|
| GET | `/boards/[*:bo_table]` | `bbs/board.php` | `bo_table` |
| GET | `/boards/[*:bo_table]/[i:wr_id]` | `bbs/board.php` | `bo_table`, `wr_id` |
| GET | `/boards/[*:bo_table]/write` | `bbs/write.php` | `bo_table` |
| GET | `/boards/[*:bo_table]/write/[i:wr_id]` | `bbs/write.php` | `bo_table`, `wr_id` |
| POST | `/boards/[*:bo_table]/write` | `bbs/write_update.php` | `bo_table` |
| GET | `/boards/[*:bo_table]/delete/[i:wr_id]` | `bbs/delete.php` | `bo_table`, `wr_id` |
| GET | `/boards/[*:bo_table]/search` | `bbs/search.php` | `bo_table` |
| GET | `/boards/[*:bo_table]/rss` | `bbs/rss.php` | `bo_table` |
| GET | `/boards/[*:bo_table]/[i:wr_id]/download/[i:no]` | `bbs/download.php` | `bo_table`, `wr_id`, `no` |
| POST | `/boards/[*:bo_table]/[i:wr_id]/good` | `bbs/good.php` | `bo_table`, `wr_id` |
| POST | `/scrap/[*:bo_table]/[i:wr_id]` | `bbs/scrap.php` | `bo_table`, `wr_id` |
| GET | `/group/[*:gr_id]` | `bbs/group.php` | `gr_id` |
| GET | `/new` | `bbs/new.php` | — |

### 5-7. `routes/02-auth.php`

| 메서드 | 패턴 | 대상 파일 |
|---|---|---|
| GET | `/login` | `bbs/login.php` |
| POST | `/login` | `bbs/login_check.php` |
| GET | `/logout` | `bbs/logout.php` |
| GET | `/register` | `bbs/register.php` |
| GET | `/register/form` | `bbs/register_form.php` |
| POST | `/register/form` | `bbs/register_form_update.php` |
| GET | `/register/result` | `bbs/register_result.php` |
| GET | `/password/lost` | `bbs/password_lost.php` |
| POST | `/password/lost` | `bbs/password_lost_certify.php` |
| GET | `/password/reset` | `bbs/password_reset.php` |
| POST | `/password/reset` | `bbs/password_reset_update.php` |
| GET | `/email/certify` | `bbs/email_certify.php` |

### 5-8. `routes/03-members.php`

| 메서드 | 패턴 | 대상 파일 | 쿼리 주입 |
|---|---|---|---|
| GET | `/members/[*:mb_id]` | `bbs/profile.php` | `mb_id` |
| GET | `/members/me/confirm` | `bbs/member_confirm.php` | — |
| GET | `/members/me/leave` | `bbs/member_leave.php` | — |
| GET | `/points` | `bbs/point.php` | — |

### 5-9. `routes/04-admin.php` (catch-all)

```php
<?php
$router->map('GET|POST', '/admin', function() {
    g5_dispatch('admin/index.php');
});

$router->map('GET|POST', '/admin/[**:path]', function($path) {
    if (strpos($path, '..') !== false) { http_response_code(404); return; }
    if (preg_match('/\.(inc|lib)$|\/_/', $path)) { http_response_code(404); return; }
    $target = 'admin/' . $path . '.php';
    if (!is_file(G5_PATH . '/' . $target)) { http_response_code(404); return; }
    g5_dispatch($target);
});
```

### 5-10. `routes/05-shop.php` (catch-all)

```php
<?php
$router->map('GET|POST', '/shop', function() {
    g5_dispatch('shop/index.php');
});

$router->map('GET|POST', '/shop/[**:path]', function($path) {
    if (strpos($path, '..') !== false) { http_response_code(404); return; }
    if (preg_match('/\.(inc|lib)$|\/_/', $path)) { http_response_code(404); return; }
    $target = 'shop/' . $path . '.php';
    if (!is_file(G5_PATH . '/' . $target)) { http_response_code(404); return; }
    g5_dispatch($target);
});
```

> PG 콜백 URL(`/shop/toss/ready.php` 등 `.php` 확장자 포함)은 `.htaccess` 2번 규칙에서 실제 파일로 직접 서빙되므로 이 라우트를 거치지 않는다.

### 5-11. `routes/90-legacy.php` (구 URL 301 리다이렉트)

```php
<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!preg_match('#^/(bbs|adm)/([^/]+)\.php$#', $uri, $m)) return;

list(, $section, $page) = $m;
$q = $_GET;

$map = [
    // 인증
    'bbs/login'               => '/login',
    'bbs/logout'              => '/logout',
    'bbs/register'            => '/register',
    'bbs/register_form'       => '/register/form',
    'bbs/register_result'     => '/register/result',
    'bbs/password_lost'       => '/password/lost',
    'bbs/password_reset'      => '/password/reset',
    'bbs/email_certify'       => '/email/certify',

    // 회원
    'bbs/profile' => function(&$q) {
        $id = $q['mb_id'] ?? ''; unset($q['mb_id']);
        return "/members/{$id}";
    },
    'bbs/member_confirm'      => '/members/me/confirm',
    'bbs/member_leave'        => '/members/me/leave',
    'bbs/point'               => '/points',

    // 게시판
    'bbs/board' => function(&$q) {
        $t = $q['bo_table'] ?? ''; unset($q['bo_table']);
        $w = $q['wr_id']    ?? ''; unset($q['wr_id']);
        return $w ? "/boards/{$t}/{$w}" : "/boards/{$t}";
    },
    'bbs/write' => function(&$q) {
        $t = $q['bo_table'] ?? ''; unset($q['bo_table']);
        $w = $q['wr_id']    ?? ''; unset($q['wr_id']);
        return $w ? "/boards/{$t}/write/{$w}" : "/boards/{$t}/write";
    },
    'bbs/search' => function(&$q) {
        $t = $q['bo_table'] ?? ''; unset($q['bo_table']);
        return "/boards/{$t}/search";
    },
    'bbs/rss' => function(&$q) {
        $t = $q['bo_table'] ?? ''; unset($q['bo_table']);
        return "/boards/{$t}/rss";
    },
    'bbs/new'                 => '/new',
    'bbs/group' => function(&$q) {
        $g = $q['gr_id'] ?? ''; unset($q['gr_id']);
        return "/group/{$g}";
    },
];

$key = "{$section}/{$page}";
if (isset($map[$key])) {
    $target = is_callable($map[$key]) ? $map[$key]($q) : $map[$key];
    if ($q) $target .= '?' . http_build_query($q);
    header("Location: {$target}", true, 301);
    exit;
}

// adm/* → /admin/* (폴더 리네임 호환)
if ($section === 'adm') {
    $target = "/admin/{$page}";
    if ($q) $target .= '?' . http_build_query($q);
    header("Location: {$target}", true, 301);
    exit;
}

// 매핑 없는 구 URL: 404
http_response_code(404);
echo "Page not found.";
exit;
```

### 5-12. `adm/` → `admin/` 리네임 작업

1. `mv adm admin`
2. `common.php` 내 `G5_ADMIN_DIR` 상수 값을 `'admin'` 으로 변경
3. 코드 내 리터럴 `'adm/'`, `"adm/"`, `/adm/` 등 전수 검색 후 치환 (grep 기반)
4. 설정/DB에 저장된 관리자 경로가 있는지 점검
5. 이후 그누보드 공식 업데이트는 **자체 포크로 수동 머지** 전제 수용

### 5-13. TailwindCSS v4 설정

**`assets/css/input.css`**:

```css
@import "tailwindcss";

@custom-variant dark (&:where(.dark, .dark *));

@source "../../theme/basic/**/*.php";
@source "../../home.php";

@theme {
  --color-bg:      #ffffff;
  --color-surface: #f9fafb;
  --color-text:    #111827;
  --color-muted:   #6b7280;
  --color-border:  #e5e7eb;
  --color-brand:   #3b82f6;
}

.dark {
  --color-bg:      #0b0f17;
  --color-surface: #111827;
  --color-text:    #e5e7eb;
  --color-muted:   #9ca3af;
  --color-border:  #374151;
}
```

**빌드 명령**:

```bash
# 개발 워크플로우 (watch)
./tailwindcss -i assets/css/input.css -o assets/css/app.css --watch

# 배포 전 1회 (minify)
./tailwindcss -i assets/css/input.css -o assets/css/app.css --minify
```

### 5-14. 다크모드 토글

**`theme/basic/head.php` 상단 — FOUC 방지 인라인 스크립트 (CSS 로드 직전)**:

```html
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
```

**토글 버튼**:

```html
<button onclick="toggleTheme()" aria-label="다크모드 전환"
        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">
  <span class="dark:hidden">🌙</span>
  <span class="hidden dark:inline">☀️</span>
</button>

<script>
function toggleTheme(){
  var isDark = document.documentElement.classList.toggle('dark');
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
}
</script>
```

### 5-15. `theme/basic` 재작성 범위

- `theme/basic/head.php`: 전체 레이아웃, 네비게이션, 다크모드 토글, Tailwind 로드
- `theme/basic/tail.php`: 푸터
- `theme/basic/index.php`: 메인 페이지 (홈 화면)
- `theme/basic/head.sub.php`, `tail.sub.php`: 서브 레이아웃 (필요 시)
- 기존 `theme/basic/css/*.css` 로드 제거 (또는 최소만 유지)
- 기존 `theme/basic/js/*.js` 는 기능 JS로 유지
- **`theme/basic/skin/`, `theme/basic/shop/`, `theme/basic/mobile/` 은 본 스코프 제외 — 방치**

### 5-16. 모바일 분기 비활성화

그누보드는 `common.php`의 `g5_is_mobile()` 기반으로 `theme/basic/mobile/` 로 자동 라우팅한다. 반응형 단일 뷰로 통합하려면:

- 그누보드 관리자 설정에서 **모바일 자동 접속 설정 OFF**
- 또는 `config.php` / `common.php`에서 `$g5['is_mobile']` 관련 플래그를 PC로 강제 (최소 1~2줄 패치)
- `theme/basic/mobile/` 폴더는 방치 (미참조로 무해)

## 6. 리스크 및 고려사항

### 6-1. 기술 리스크

| 리스크 | 영향 | 완화 |
|---|---|---|
| `$_SERVER['PHP_SELF']` 기반 분기 코드 존재 | 일부 페이지 오동작 | `g5_dispatch()` 에서 조작. 실사용 중 발견 시 최소 패치 |
| 기존 스킨이 구 URL(`bbs/board.php?...`)을 하드코딩 | URL 공존이므로 정상 동작, 단 SEO 중복 | 구 URL 접근은 301 리다이렉트로 흡수. 스킨 URL 생성은 점진 교체 |
| AltoRouter `[**:path]` catch-all 동작 | admin/shop 매핑 실패 가능 | AltoRouter 버전 확인, 매치 테스트 |
| PG 결제 콜백(`.php` 확장자) 리라이트 간섭 | 결제 실패 | `.htaccess` 2번 규칙으로 실제 파일 우선 서빙 보장. 테스트 필수 |
| `adm/` → `admin/` 리네임 후 DB/설정 잔존 경로 | 관리자 접근 실패 | grep 기반 전수 치환 + 설정 테이블 점검 |

### 6-2. 운영 리스크

- **그누보드 공식 업데이트 포기**: 자체 포크로 전환됨. 보안 패치는 수동 반영 필요
- **Tailwind 바이너리 관리**: 개발자 PC마다 바이너리 다운로드 필요
- **빌드 산출물 커밋**: `assets/css/app.css` 커밋 여부 — 커밋 권장 (배포 단순화)

## 7. 향후 하위 프로젝트

1. **스킨 Tailwind 전환**: `skin/board`, `skin/member`, `skin/shop` 등 199개 파일 점진 마이그레이션
2. **관리자 UI 개편**: `admin/*` Tailwind 적용
3. **모바일 폴더 완전 폐기**: `mobile/` 349개 파일 제거, 반응형 완전 통합
4. **REST API**: `/api/v1/*` 엔드포인트 설계
5. **ajax URL 정리**: `bbs/ajax.*.php` → `/api/` 네임스페이스
6. **메모/Q&A/투표/폼메일 라우팅**: 우선순위에 따라 추가

## 8. 구현 순서 (참고)

실제 구현 플랜은 별도 writing-plans 단계에서 작성한다. 큰 순서만 나열:

1. `adm/` → `admin/` 리네임 + 코드 일괄 치환
2. AltoRouter 배치 + `core/` + `.htaccess` + 새 `index.php` + `home.php`
3. `routes/00-root.php` + 게시판 라우트(`01-boards.php`) — 동작 검증
4. 나머지 라우트(`02`, `03`, `04`, `05`) — 각 단계마다 검증
5. `routes/90-legacy.php` 리다이렉트
6. Tailwind v4 바이너리 + `input.css` + 빌드 파이프라인
7. `theme/basic/head.php`, `tail.php`, `index.php` Tailwind 재작성 + 다크모드 토글
8. 모바일 분기 비활성화
9. 전체 페이지 smoke test
