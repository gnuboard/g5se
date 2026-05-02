# gnu5se 모던화 작업 기록

> gnuboard5 위에 모던 디자인 시스템을 점진적으로 얹는 샌드박스. 이 문서는 지금까지 한 일과 새 페이지를 같은 패턴으로 모던화하는 방법을 정리한다.

---

## 1. 전체 구조

```
/home/kagla/gnu5se/                    Apache DocumentRoot (vhost: gnu5se.gnuboard.net)
├── .htaccess                          라우팅 + 보안
├── index.php                          프런트 컨트롤러
├── app/                               gnuboard 본체 (URL 직접접근 차단)
│   ├── bbs/  adm/  lib/  plugin/  extend/  mobile/  install/
│   ├── theme/basic/                   기본 테마
│   │   ├── modern/_head.inc.php       ★ 모던 디자인 시스템 핵심
│   │   ├── skin/member/basic/         로그인/회원가입/완료 스킨들
│   │   └── index.php                  메인 페이지
│   ├── img/  js/  css/  skin/
│   ├── common.php  config.php
│   └── router.php                     URL → 진입점 매핑
├── data/                              런타임 (업로드/세션/캐시) — www-data 소유
│   ├── session/  cache/  file/  member/  ...
│   └── dbconfig.php
├── CLAUDE.md                          일반 코딩 가이드라인
└── MODERNIZATION.md                   이 문서
```

### 요청 흐름

```
브라우저 → /login
    │
    ▼
/home/kagla/gnu5se/.htaccess
    │ 1. 정적자산 폴더 안의 .php 차단
    │ 2. /app/* 직접접근 전면 차단
    │ 3. /data/ 안 PHP 실행 차단
    │ 4. /theme|skin|img|js|css|mobile|plugin → /app/$1 내부 매핑 [END]
    │ 5. 실제 파일/디렉토리는 그대로
    │ 6. 그 외 → index.php (front controller)
    ▼
/index.php                            # 상수 사전 정의 + ob_start 필터 + 라우터 호출
    │
    ▼
Router::resolve('/login')             # 클린 URL 매칭 또는 .php → 301 redirect
    │
    ▼ (글로벌 스코프 require — 메서드 내부에서 require 하면 $g5 등이 잃어짐)
require G5_PATH.'/bbs/login.php'      # gnuboard 표준 진입점
    │
    ▼
include _common.php → common.php → config.php → 스킨 렌더
    │
    ▼
ob_start 필터가 HTML 안의 .php URL → 클린 URL 치환
    │
    ▼
응답 송출
```

---

## 2. 핵심 파일별 역할

### `/home/kagla/gnu5se/.htaccess`

```apache
RewriteEngine On
Options -Indexes +FollowSymLinks

# 1) 정적/라이브러리 폴더의 PHP 실행 차단
#    (plugin/ 은 캡차/결제/소셜로그인 등 직접 호출이 필요해 차단 대상에서 제외)
RewriteRule ^(theme|skin|extend|lib|js|css|img|mobile)/.+\.(php|phtml|phar)$ - [F,L]

# 2) /app/* 직접접근 차단
RewriteRule ^app(/|$) - [F,L]

# 3) data/ 안에서 스크립트 실행 차단
RewriteRule ^data/.+\.(php|phtml|phar|html?|cgi|pl|py|jsp|asp|sh)$ - [F,L]

# 4) 정적 자산 root URL → app/ 내부 위치로 매핑
RewriteRule ^(theme|skin|img|js|css|mobile|plugin)/(.*)$ app/$1/$2 [END]

# 5) 실제 파일/디렉토리 (favicon, robots 등) 그대로
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# 6) 그 외 모든 URL → 프런트 컨트롤러
RewriteRule ^ index.php [QSA,L]

# 7) 민감 파일 차단
<FilesMatch "(^\.|composer\.(json|lock)|\.env|\.git|\.md|\.sql|\.log)$">
    Require all denied
</FilesMatch>
```

**중요 포인트**:
- `[END]` 플래그(룰 4): 정적 자산 매핑 후 mod_rewrite 가 다시 돌면서 `^app(/|$)` 차단 룰에 걸리는 것을 방지.
- `plugin/` 은 차단 대상에서 빠짐. `kcaptcha_image.php`, `kcaptcha_session.php`, 결제 콜백 등이 필요.

### `/index.php` (프런트 컨트롤러)

```php
define('_GNUBOARD_', true);
define('G5_PATH', __DIR__.'/app');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = preg_replace('/[^a-zA-Z0-9\.\-:]/', '', $_SERVER['HTTP_HOST']);
define('G5_URL', $scheme.'://'.$host);

// /bbs 접두어 자동 부착 차단
define('G5_BBS_URL',        G5_URL);
define('G5_HTTP_BBS_URL',   G5_URL);
define('G5_HTTPS_BBS_URL',  G5_URL);

// data/ 가 app/ 밖에 있으므로 명시 (config.php 에서도 보정하지만 둘 다 박음)
define('G5_DATA_PATH', __DIR__.'/data');
define('G5_DATA_URL',  G5_URL.'/data');

// 출력버퍼 필터: 모던화된 엔드포인트의 .php 접미사 자동 제거
ob_start(function ($html) {
    static $clean_endpoints = [
        'login','login_check','logout',
        'register','register_form','register_form_update','register_result',
    ];
    $pattern = '#(/(?:'.implode('|', $clean_endpoints).'))\.php(?![a-zA-Z0-9])#';
    return preg_replace($pattern, '$1', $html);
});

require G5_PATH.'/router.php';
$_route_target = (new Router())->resolve($_SERVER['REQUEST_URI']);
// ... 404/500 가드 ...
chdir(dirname($_route_full));
require $_route_full;   // ★ 글로벌 스코프 require (필수)
```

**주의 — 글로벌 스코프 require**: gnuboard 의 `$g5`, `$member`, `$is_member`, `$config` 같은 전역 변수가 메서드 내부에서 require 하면 로컬에 갇혀 DB 연결을 잃는다 (`sql_query()` 에서 mysqli null 에러). Router 는 경로 해석만 하고, 실제 `require` 는 반드시 index.php 의 top-level 에서.

### `/app/router.php`

```php
class Router {
    private $cleanRoutes = [
        '/'                       => 'index.php',
        '/login'                  => 'bbs/login.php',
        '/login_check'            => 'bbs/login_check.php',
        '/logout'                 => 'bbs/logout.php',
        '/register'               => 'bbs/register.php',
        '/register_form'          => 'bbs/register_form.php',
        '/register_form_update'   => 'bbs/register_form_update.php',
        '/register_result'        => 'bbs/register_result.php',
    ];

    private $extraRoutes = [
        '#^/_debug/?$#'                  => '_debug.php',
        // AJAX 일괄 매핑 (ajax.mb_id, ajax.mb_nick, ajax.autosave 등)
        '#^/(ajax\.[a-z0-9_.]+\.php)$#i' => 'bbs/{1}',
    ];

    public function resolve($requestUri) {
        // 1) clean URL 직접 매칭
        // 2) .php 변형 — GET/HEAD 면 301 redirect, POST 는 그대로 처리
        // 3) extraRoutes 정규식 + {N} placeholder 치환
    }
}
```

### `/app/config.php` (수정 부분)

원본 gnuboard `config.php` 에 두 군데 보정 추가:

**A. G5_URL 강제 설정** (line ~80 위):
```php
// 직접접근 진입점에서도 G5_URL 이 도메인 root 로 잡히도록
if (!defined('G5_URL') && isset($_SERVER['HTTP_HOST'])) {
    $_g5se_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('G5_URL', $_g5se_scheme.'://'.preg_replace('/[^a-zA-Z0-9\.\-:]/', '', $_SERVER['HTTP_HOST']));
}
```

**B. G5_DATA_PATH 보정** (line ~115):
```php
// data/ 가 app/ 밖이므로 dirname 사용
define('G5_DATA_PATH', dirname(G5_PATH).'/'.G5_DATA_DIR);
```

**Why**: 프런트 컨트롤러를 거치지 않는 직접 접근(`plugin/kcaptcha/kcaptcha_image.php` 등) 에서도 동일한 PATH/URL 이 잡혀야 함. 자동탐지(`g5_path()`) 결과는 SCRIPT_NAME 이 `/app/...` 으로 잡혀 깨짐.

---

## 3. 데이터 디렉토리 권한

`cp -a` 로 gnuboard5 → gnu5se 복사 시 owner 가 `kagla:kagla` 가 됨. Apache(`www-data`) 가 쓰기 못해 세션 생성 / 회원가입 / 캡차 모두 실패.

```bash
sudo chown -R www-data:www-data /home/kagla/gnu5se/data
sudo chmod 711 /home/kagla/gnu5se/data
```

원본 gnuboard5 와 동일한 소유권 체계로 맞춤. `data/` 자체는 711 (소유자 only entry), 하위는 755 (www-data 가 owner).

---

## 4. 캡차 처리

### 4.1. .htaccess 가 차단했던 문제

`plugin/kcaptcha/kcaptcha_image.php`, `kcaptcha_session.php`, `kcaptcha_mp3.php` 가 brower 에서 직접 호출되는데 `.htaccess` 룰 1번이 `plugin/*.php` 를 차단했었음 → 403. 차단 대상에서 `plugin` 제거.

### 4.2. mp3 디스크 캐시 제거

기존 `kcaptcha_mp3.php` 는 매 호출마다 `data/cache/kcaptcha-XXX.mp3` 를 생성. 우리는 **메모리 스트리밍**으로 변경:

- POST: URL 만 반환 (`G5_URL/plugin/kcaptcha/kcaptcha_mp3.php`)
- GET: 세션의 캡차 숫자에 해당하는 mp3 들을 메모리에서 합쳐 `audio/mpeg` 헤더로 즉시 스트림

`kcaptcha.js` 와 호환 (POST → URL → audio src 에 박힘 → 브라우저가 GET).

---

## 5. 모던 디자인 시스템 (★ 핵심)

### 5.1. 단일 진입점: `theme/basic/modern/_head.inc.php`

이 파일 하나가 모든 모던 페이지의 공통 head 를 담당. 페이지 스킨은:
```php
require_once(G5_THEME_PATH.'/modern/_head.inc.php');
```
한 줄만 추가하면 됨.

내용:
1. **CDN 의존성**: Pretendard 폰트, UnoCSS reset, UnoCSS runtime
2. **다크모드 FOUC 방지** early init 스크립트 (`add_javascript(..., -100)`)
3. **CSS 토큰** (`:root` + `[data-theme="dark"]`)
4. **컴포넌트 클래스**
5. **다크모드 토글 버튼** 자동 주입 JS

가드:
```php
if (defined('_MODERN_HEAD_LOADED_')) return;
define('_MODERN_HEAD_LOADED_', true);
```
중복 require 안전.

### 5.2. CSS 토큰

| 토큰 | 라이트 | 다크 |
|---|---|---|
| `--m-bg` | `#f8fafc` | `#0a0e1a` |
| `--m-surface` | `#ffffff` | `#131825` |
| `--m-surface-2` | `#f1f5f9` | `#1c2230` |
| `--m-border` | `#e2e8f0` | `#2a3344` |
| `--m-border-hover` | `#cbd5e1` | `#3d4a5e` |
| `--m-text` | `#0f172a` | `#f1f5f9` |
| `--m-text-muted` | `#64748b` | `#94a3b8` |
| `--m-text-soft` | `#475569` | `#cbd5e1` |
| `--m-text-faint` | `#94a3b8` | `#64748b` |
| `--m-primary` | `#2563eb` | `#3b82f6` |
| `--m-primary-hover` | `#1d4ed8` | `#60a5fa` |
| `--m-primary-soft` | `rgba(37,99,235,0.12)` | `rgba(59,130,246,0.20)` |
| `--m-radius-sm` / `--m-radius` / `--m-radius-lg` | `6px` / `8px` / `12px` |
| `color-scheme` | `light` | `dark` |

**폰트 스케일** (라이트/다크 동일):

| 토큰 | 값 | 용도 |
|---|---|---|
| `--m-text-xs` | `11px` | 뱃지·아이콘 pill |
| `--m-text-sm` | `12px` | 힌트·설명·divider·footer 작은 글씨 |
| `--m-text-base` | `13px` | 기본 — label·link·meta·check |
| `--m-text-md` | `14px` | 인터랙티브 — input·btn·card body (body 디폴트) |
| `--m-text-lg` | `16px` | 부제목·hero 부제 |
| `--m-text-xl` | `18px` | brand·section title 강조 |
| `--m-text-2xl` | `22px` | 페이지 타이틀 |
| `--m-text-3xl` | `26px` | 큰 페이지 타이틀 |
| `--m-text-display` | `36px` | hero 제목 |
| `--m-leading-tight` / `--m-leading` / `--m-leading-relaxed` | `1.3` / `1.5` / `1.7` | line-height |

**원칙**: 색상은 절대 하드코딩하지 말고 토큰으로. 하드코딩 = 다크모드 깨짐.

예외적으로 alpha 기반 색상(예: `rgba(16,185,129,0.12)` 의 성공 아이콘 배경)은 양쪽 모드에서 자연스러우니 OK.

**폰트 사이즈도 토큰으로**: `font-size: 14px` 같은 직접 px 사용 금지. 항상 `font-size: var(--m-text-md)` 형태. 새 사이즈가 필요하면 `_head.inc.php` 의 :root 에 추가해서 통제. 기존 스킨 파일 안 인라인 `style="font-size: NNpx"` 는 발견 시 토큰으로 전환 (점진 정리). 컴포넌트 클래스는 모두 토큰 사용으로 전환됨.

### 5.3. 컴포넌트 클래스

| 클래스 | 용도 |
|---|---|
| `.m-shell` | 페이지 최상위 래퍼. **fixed 오버레이**(inset:0, z-index:9999) 로 gnuboard chrome 위에 덮음. 이게 없으면 chrome 에 묻혀 안 보임 |
| `.m-container` | max-width 1100px 가운데 정렬 |
| `.m-center` | 풀스크린 가운데 정렬 (로그인/완료 카드용) |
| `.m-card`, `.m-card-narrow` | 카드 컨테이너 |
| `.m-input` | 텍스트/이메일/비밀번호 인풋 |
| `.m-textarea` | textarea |
| `.m-file` | file 인풋 (`::file-selector-button` 스타일링 포함) |
| `.m-label` | 폼 라벨 |
| `.m-check` / `.m-check-block` | 체크박스 (블록형은 클릭영역 확장 + 체크 시 파란 배경) |
| `.m-pw-wrap`, `.m-pw-toggle` | 비밀번호 표시/숨기기 토글 |
| `.m-btn` 베이스 | `.m-btn-primary`, `.m-btn-secondary`, `.m-btn-ghost` 변형 |
| `.m-link` | 인라인 텍스트 링크 |
| `.m-divider` | "또는" 분리선 |
| `.m-nav`, `.m-nav-inner`, `.m-brand`, `.m-nav-actions` | 상단 네비 |
| `.m-form-section`, `.m-section-title`, `.m-form-row`, `.m-form-grid-2`, `.m-form-msg`, `.m-form-hint`, `.m-form-success`, `.m-input-with-action` | 회원가입 폼 전용 (지금은 register_form.skin.php 안에 인라인. 다른 폼에서도 쓸 일 생기면 `_head.inc.php` 로 승격 권장) |
| `.m-theme-toggle` | 다크모드 토글 버튼 |

### 5.4. 다크모드 동작

1. **첫 방문** → early init 스크립트가 `localStorage('m-theme')` 또는 `prefers-color-scheme` 으로 `<html data-theme="...">` 설정
2. **토글 클릭** → `dataset.theme` 토글 + `localStorage` 저장
3. **재방문** → localStorage 우선 (사용자 명시 선택 존중)
4. **새 페이지 이동** → early init 이 페인트 직전 실행되어 깜빡임 없음

토글 버튼은 모든 `.m-nav-actions` 안에 JS 가 자동으로 첫 자식으로 삽입. 페이지 스킨에 손 안 댐.

---

## 6. 새 페이지 모던화 — Step-by-Step

### A. 라우트 추가

`app/router.php` 의 `$cleanRoutes` 에 한 줄:
```php
'/mypage' => 'bbs/mypage.php',
```

`.php` 변형 자동으로 301 redirect 됨.

`index.php` 의 `$clean_endpoints` 배열에도 엔드포인트명 추가 (출력 HTML 의 .php 자동 제거):
```php
static $clean_endpoints = [..., 'mypage'];
```

### B. 스킨 파일 찾기

`g5_config.cf_member_skin` (또는 cf_skin) 값 확인:
```sql
SELECT cf_member_skin, cf_skin FROM g5_config;
```
값이 `'theme/basic'` 이면 → `app/theme/basic/skin/<area>/basic/` 안의 스킨이 실제 사용됨.

### C. 스킨 파일 작성 (템플릿)

```php
<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>
<div class="m-shell">
    <header class="m-nav">
        <div class="m-nav-inner">
            <a href="<?php echo G5_URL ?>" class="m-brand">gnu5se</a>
            <nav class="m-nav-actions">
                <?php if ($is_member) { ?>
                    <a href="<?php echo G5_BBS_URL ?>/logout.php" class="m-btn m-btn-ghost">로그아웃</a>
                <?php } else { ?>
                    <a href="<?php echo G5_BBS_URL ?>/login.php" class="m-btn m-btn-ghost">로그인</a>
                <?php } ?>
            </nav>
        </div>
    </header>

    <main class="m-center" style="align-items: start;">
        <div class="m-card" style="width: 100%; max-width: 480px;">
            <h1 style="font-size: 22px;">페이지 제목</h1>
            <!-- 콘텐츠 -->
        </div>
    </main>
</div>
```

### D. 폼이 있다면

- gnuboard 의 `<form name=...>`, `action=...`, `onsubmit=...`, 모든 hidden input, 필드 name 그대로 보존
- 입력 클래스만 `m-input`, `m-label` 등으로 교체
- onsubmit JS 검증 함수도 그대로 유지 (`fregister_submit`, `fregisterform_submit`, `flogin_submit` 등)

### E. 다크모드 검증

브라우저에서 토글 클릭 후:
- 흰바탕흰글씨 / 검정바탕검정글씨 발생 안 하는지 확인
- 발견되면 해당 요소의 CSS 가 색상 하드코딩 했는지 검사 → 토큰으로 교체
- gnuboard `default.css` 가 specificity 로 이기는 경우(`.captcha_box`, `.frm_input` 등 클래스에 직접 색상 박혀있음) → `!important` 로 덮어쓰기

---

## 7. 트러블슈팅 — 자주 만난 함정

| 증상 | 원인 | 해결 |
|---|---|---|
| 페이지가 텅 비고 소스만 있음 | head.php 가 출력하는 chrome 안쪽 깊이에 m-shell 이 들어가, chrome-hiding 룰이 m-shell 을 같이 숨김 | `.m-shell` 을 `position: fixed` 오버레이로 (이미 적용됨) |
| 회원가입 시 "올바른 방법으로" + "약관 동의" 알럿 두 번 | AJAX 검증 엔드포인트(`/ajax.mb_id.php` 등) 라우트 누락 → 세션 검증 변수 없음 | router 에 ajax 패턴 추가 (이미 적용됨) |
| mp3 음성 재생 안됨 | mp3 URL 이 `/app/data/...` 로 잘못 박힘 (직접 접근 시 G5_URL 자동탐지 실패) | config.php 에서 G5_URL 강제 설정 |
| 캡차 이미지 안 뜸 | `.htaccess` 가 plugin/*.php 차단 | 차단 룰에서 `plugin` 제외 |
| 캡차 인풋 다크모드 안 보임 | gnuboard `default.css` 가 `.captcha_box` 에 흰배경 박음 | 우리 CSS 에서 `!important` 로 토큰 적용 |
| 약관 표 헤더 안 보임 | `.m-terms-table th { background: white }` 하드코딩 | `var(--m-surface)` 로 교체 |
| `mysqli_query` null 에러 | 라우터 메서드 안에서 require → 전역 변수 갇힘 | 글로벌 스코프 require (이미 적용됨) |
| `/login.php` 와 `/login` 둘 다 200 | 라우터에 둘 다 등록됨 | `.php` 는 GET/HEAD 시 301 redirect (이미 적용됨), POST 는 그대로 처리 (폼 데이터 유실 방지) |
| `.m-btn-primary` 안의 글자가 어둡게 보임 (흰색이어야 하는데) | reset 룰 `.m-shell a { color: inherit }` 의 specificity (0,1,1) 가 `.m-btn-primary` (0,1,0) 보다 높아 부모 색상 상속 | reset 을 `.m-shell a:not(.m-btn):not(.m-link)` 으로 좁힘. **교훈**: 광범위한 element 리셋은 자기 색을 가진 클래스를 `:not()` 으로 제외해야 함 |

---

## 8. 모던화 완료 / 미완 페이지

### 완료 (2026-05-02)

- `/` 메인 — `app/theme/basic/index.php` (로그인 시 "정보수정" 링크 노출)
- `/login` 로그인 — `skin/member/basic/login.skin.php`
- `/register` 약관 동의 — `register.skin.php`
- `/register_form` 회원정보 입력 — `register_form.skin.php` (다음 우편번호 API, 본인확인, 캡차, 마케팅 동의 모두 포함)
- `/register_result` 가입 완료 — `register_result.skin.php`
- `/member_confirm` 정보수정 진입 비번 확인 — `member_confirm.skin.php`
- `/password` 게시글/정보수정 비번 확인 공용 — `password.skin.php`
- `/password_lost` 아이디/비번 찾기 (이메일+본인인증 2-card) — `password_lost.skin.php`
- `/password_reset` 새 비밀번호 입력 — `password_reset.skin.php`
- `/board/{bo_table}` 게시판 목록 — `skin/board/basic/list.skin.php` (모던 테이블 + 검색 드로어 + 페이지네이션)

### 다크모드 토글

- 모든 `.m-nav-actions` 안에 자동 주입 (DOMContentLoaded 시 JS)
- localStorage 'm-theme' 으로 영속화
- 시스템 prefers-color-scheme 디폴트
- early init 스크립트로 FOUC 차단

### 추가 라우트 (2026-05-02 보강)

회원정보 수정 / 비밀번호 찾기 / 회원 탈퇴 플로우용 라우트:
- `/member_confirm` — 정보수정 진입 시 비밀번호 확인. 메인 nav "정보수정" 버튼이 이걸로 들어감 — **스킨 모던화 완료**
- `/password` — 게시글 비번 확인 (글 수정/삭제, 비밀글 열람 공용) — **스킨 모던화 완료**
- `/password_check` — `/password` 의 POST 처리 (스킨 없음 — alert/redirect)
- `/password_lost` — 비번 찾기 (이메일/본인인증 2-card 레이아웃) — **스킨 모던화 완료**
- `/password_lost_certify`, `/password_lost2` — 비번 찾기 단계 (스킨 없음 — alert/redirect)
- `/password_reset` — 새 비번 입력 폼 — **스킨 모던화 완료**
- `/password_reset_update` — 비번 재설정 처리 (스킨 없음)
- `/member_leave` — 회원 탈퇴 처리 (스킨 없음 — `member_confirm` 으로 비번 입력 받고 POST 로 이 엔드포인트가 처리)

**정보 수정 플로우** (gnuboard 표준):
```
메인 nav "정보수정" 클릭
  → /member_confirm?url=<urlencoded /register_form?w=u>
  → 비밀번호 확인 폼 (member_confirm.skin.php)
  → POST 통과 시 url 파라미터의 페이지로 redirect (POST 로 mb_password 전달)
  → /register_form?w=u → register_form.skin.php 가 'u' 모드로 렌더 (값 미리 채워짐)
  → 수정 후 POST /register_form_update → /register_result 또는 G5_URL 로 redirect
```

### 미완 (다음 후보)

게시판 영역 진행 중:
- ✅ 라우트 + URL 필터 + `list.skin.php` (목록) — **완료**
- `view.skin.php` — 게시글 상세 (board.php 가 wr_id 있을 때 사용. 본문/첨부파일/이전·다음글/이미지 갤러리)
- `view_comment.skin.php` — 댓글 (목록 + 작성 폼 + 답글)
- `write.skin.php` — 글쓰기/수정 (에디터, 카테고리, 비밀글, 첨부파일, 추천/비추천 옵션)
- 댓글 AJAX 핸들러는 이미 라우트 등록됨 (`/board/{bo_table}/comment`)
- `delete.php` 등은 라우트만 등록 (스킨 없음, alert/redirect)

기타:
- 검색, 최신글, 인기글, 새글 위젯 (`skin/latest`, `skin/popular`, `skin/new`, `skin/search`)
- `/mypage` — gnuboard 에 mypage 라는 이름의 entry point 는 없음. 우리가 정의해야 함 (예: `app/mypage.php` 신규 작성, 라우트 추가)
- 관리자 페이지(`/adm/...`) — 우선순위 낮음

### 확장 시 주의

- `register_form.skin.php` 의 폼 컴포넌트(`.m-form-section`, `.m-form-row` 등) 는 현재 그 파일 안에 인라인. 게시판 글쓰기처럼 비슷한 폼이 또 등장하면 `_head.inc.php` 로 승격하는 게 좋음
- `/board/...` 는 named group + `{N}` 치환 사용 (router 의 `extraRoutes` 패턴):
  ```php
  '#^/board/(?P<bo_table>[^/]+)/?$#' => 'bbs/board.php',
  '#^/board/(?P<bo_table>[^/]+)/(?P<wr_id>\d+)/?$#' => 'bbs/board.php',
  ```
- 출력 버퍼 필터에 `board.php?bo_table=...` 형태도 클린 URL 로 치환하는 패턴 추가 필요

---

## 9. 파일 변경 요약 (이 프로젝트 고유 수정)

### gnuboard core 수정 (최소화)

- `app/config.php` — G5_URL 강제 설정 + G5_DATA_PATH 보정 (직접 접근 진입점 호환)
- `app/plugin/kcaptcha/kcaptcha_mp3.php` — 디스크 캐시 → 메모리 스트리밍 전환
- `app/lib/mailer.lib.php` — `$mail->SMTPAutoTLS = false` 추가 (PHPMailer 5.2.10+ 가 로컬 postfix 의 self-signed STARTTLS 와 협상 실패하는 문제 해결)
- `app/bbs/password_lost2.php` — 모든 `alert_close()` → `alert(..., redirect_url)` 로 변경 (history.back() 으로 폼 재진입해 무제한 재전송 가능했던 문제)

### 신규 파일

- `/.htaccess` — 라우팅/보안
- `/index.php` — 프런트 컨트롤러
- `/app/router.php` — URL 매핑
- `/app/_debug.php` — 디버그용 상태 출력
- `/app/theme/basic/modern/_head.inc.php` — ★ 디자인 시스템 핵심

### 스킨 교체 (gnu5se 고유 디자인)

- `/app/theme/basic/index.php`
- `/app/theme/basic/skin/member/basic/login.skin.php`
- `/app/theme/basic/skin/member/basic/register.skin.php`
- `/app/theme/basic/skin/member/basic/register_form.skin.php`
- `/app/theme/basic/skin/member/basic/register_result.skin.php`

### 권한 변경

- `sudo chown -R www-data:www-data /home/kagla/gnu5se/data`

---

다음 세션에서 `/mypage` 또는 `/board/...` 작업 이어가면 됨. 이 문서대로 패턴 따라 가면 일관된 디자인 + 다크모드 지원 자동 적용.
