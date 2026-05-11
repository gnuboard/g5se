# g5se 모던화 작업 기록

> gnuboard5 위에 모던 디자인 시스템을 점진적으로 얹는 샌드박스. 이 문서는 지금까지 한 일과 새 페이지를 같은 패턴으로 모던화하는 방법을 정리한다.

---

## 1. 전체 구조

```
/home/kagla/g5se/                    Apache DocumentRoot (vhost: g5se.gnuboard.net)
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
/home/kagla/g5se/.htaccess
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

### `/home/kagla/g5se/.htaccess`

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

`cp -a` 로 gnuboard5 → g5se 복사 시 owner 가 `kagla:kagla` 가 됨. Apache(`www-data`) 가 쓰기 못해 세션 생성 / 회원가입 / 캡차 모두 실패.

```bash
sudo chown -R www-data:www-data /home/kagla/g5se/data
sudo chmod 711 /home/kagla/g5se/data
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
            <a href="<?php echo G5_URL ?>" class="m-brand">g5se</a>
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

### 스킨 교체 (g5se 고유 디자인)

- `/app/theme/basic/index.php`
- `/app/theme/basic/skin/member/basic/login.skin.php`
- `/app/theme/basic/skin/member/basic/register.skin.php`
- `/app/theme/basic/skin/member/basic/register_form.skin.php`
- `/app/theme/basic/skin/member/basic/register_result.skin.php`

### 권한 변경

- `sudo chown -R www-data:www-data /home/kagla/g5se/data`

---

다음 세션에서 `/mypage` 또는 `/board/...` 작업 이어가면 됨. 이 문서대로 패턴 따라 가면 일관된 디자인 + 다크모드 지원 자동 적용.

---

## 10. 후속 라운드 요약 (1·2·3 순위 + 회귀)

문서 1~9 절 작성 이후 진행한 영역. 모두 같은 m-shell / m-popup / 토큰 기반 패턴.

### 1순위 — 자주 노출
- **사이드뷰 팝업** (`.sv_wrap .sv`) — `_head.inc.php` 에서 토큰 기반 카드로 재스타일, `:has(.sv_on)` 으로 z-index 1000 escape
- **전체검색** `/search` (search.skin) — 카드형 폼 + 결과 요약 + 게시판 필터 pill + 결과 카드 + 빈 상태
- **새글** `/new` (new.skin) — 카드형 폼 + 모던 테이블, 모바일에선 부가 칼럼 숨김

### 2순위 — 회원 자주 사용
- **쪽지** `/memo` · `/memo_form` · `/memo_view` — popup 패턴 (`.m-popup`), 탭 + 카드형 리스트 + 답장 흐름
- **메일보내기** `/formmail` — popup, textarea flex:1 으로 viewport 가득 채움
- **자기소개** `/profile` — popup, avatar 카드 + grid 스펙 + 인사말. mb_id 없으면 본인으로 fallback
- **포인트 내역** `/point` — popup, 보유 포인트 hero 카드(primary 그라디언트) + 적립/사용 색상별 카드 + 만료 pill
- **스크랩** `/scrap` + `/scrap_popin` — popup, 카드형 + 인용 카드(primary border-left)

### 3순위 — 가끔
- **그룹 페이지** `/group` (theme/basic/group.php + latest.skin) — m-shell + 게시판 카드 grid + latest 위젯
- **FAQ** `/faq` (faq/basic/list.skin) — 검색바 + 카테고리 pill + 아코디언 (Q/A 뱃지, chevron 회전)
- **alert / confirm** (`bbs/alert.php`, `bbs/confirm.php`) — script alert 우선, noscript fallback 을 m-card-narrow + 아이콘 + 토큰 기반 버튼으로

### 회귀/폴리시
- **콘텐츠** `/content?co_id=...` (content.skin) — 회사소개·개인정보·이용약관 — 본문 typography 토큰화 + 실제 한글 콘텐츠 시드
- **갤러리 게시판** `bo_skin='theme/basic'` 으로 통일
- **viewport meta** 무조건 출력 (`G5_IS_MOBILE` 분기 제거) — 좁은 viewport 에서 반응형 정상 동작
- **모바일 햄버거 드로어** — 우측 슬라이드 인 패널, 로그인 상태(닉/포인트/쪽지/스크랩 카운트 + 액션) + nav 링크
- **사이드뷰 클립 픽스** — list.skin 의 `<div class="m-card" style="overflow:hidden">` 를 visible 로, 그리고 `.sv_wrap:has(.sv_on) { z-index: 1000 }`
- **케밥 메뉴** (view.skin) — 수정/삭제/복사/이동/검색을 점 세 개 드롭다운으로
- **페이지네이션** — `.pg_*` 토큰 스타일을 `_head.inc.php` 글로벌로 hoist, 어떤 스킨이든 `<?= $write_pages ?>` + `.m-pagination` 만 있으면 적용
- **임시저장 글 목록** popup 정렬 (write.skin)
- **클린 URL 라우트** — `/memo`, `/memo_form`, `/memo_form_update`, `/memo_view`, `/memo_delete`, `/formmail`, `/formmail_send`, `/profile`, `/point`, `/scrap`, `/scrap_delete`, `/scrap_popin`, `/scrap_popin_update`, `/search`, `/new`, `/faq`, `/content`, `/group`, `/write_token.php`. `/bbs/{name}.php` 도 clean URL 로 301
- **출력 필터 강화** (`index.php`) — `/board.php?bo_table=X` 패턴이 추가 쿼리(page/sca/sfl 등) 를 보존하도록 `parse_str` 기반으로 재작성
- **시드** — 50명 회원 + 50건 게시물 (free 게시판) — `/tmp/seed_g5se.php`. FAQ 13건. 콘텐츠 3건

### 직접 수정한 gnuboard 코어 파일 (이 라운드)
- `app/theme/basic/head.sub.php` — viewport meta 무조건 출력
- `app/bbs/alert.php` — modern 토큰 등록 + noscript fallback 카드화
- `app/bbs/confirm.php` — 동일
- `app/bbs/profile.php` — `/profile` 본인 fallback
- `app/theme/basic/group.php` — 그룹 페이지 전면 재작성

### DB 변경 (요약)
- `g5_config`: cf_search_skin / cf_new_skin / cf_faq_skin / cf_member_skin = `theme/basic`
- `g5_board`: 모든 게시판 `bo_skin='theme/basic'`, free 의 `bo_use_search=1`
- `g5_content`: 3건 `co_skin='theme/basic'` + 실 콘텐츠 시드
- `g5_faq_master`/`g5_faq`: 카테고리 3 + Q&A 13 시드
- `data/dbconfig.php`: DB credentials 를 `g5se/g5se/g5se` 로 (이전 `gnuboard5/...` 에서 dump → import)

---

## 11. 새 테마 추가 가이드

`theme/basic/modern/_head.inc.php` 의 토큰 (`--m-bg`, `--m-surface`, `--m-primary`, ...) 만 다른 값으로 바꾸면 모든 스킨이 자동으로 새 팔레트를 따라간다 (스킨은 모두 `var(--m-*)` 만 사용).

새 테마를 추가하려면:
1. `app/theme/<name>/` 디렉토리 생성, 기본은 `theme/basic` 의 구조를 그대로 유지 (skin/, modern/, head.php, tail.php, group.php, index.php, head.sub.php).
2. `theme/<name>/modern/_head.inc.php` 의 토큰 블록만 새 팔레트로 변경.
3. 필요하면 `_nav.inc.php`/`_footer.inc.php` 의 layout 도 변경.
4. 스킨 파일은 **반드시 cp -r 로 복사** (symlink 금지 — 격리 깨짐, git 이슈).
5. `cf_theme` 을 새 이름으로 바꾸면 즉시 전환 — 다른 DB 변경 없음.

`m-*` 클래스/변수 네임 컨벤션을 깨지 않으면 스킨은 100% 재사용 가능.

---

## 12. 추가 라운드 — 멀티테마 / 동적 메뉴 / QA / 폴리시

### 멀티 테마 4종
- `basic` (cool blue, 청회), `forest` (sage green, 자연), `aurora` (lavender violet, 보라), `sunset` (peach amber, 노을)
- 각각 `theme/basic` 을 cp -r 로 통째 복사 (symlink 금지) 한 뒤 `modern/_head.inc.php` 의 토큰 블록만 팔레트로 교체
- 라이트/다크 둘 다 토큰 정의, shadow 톤도 팔레트에 맞게 조정 (e.g. forest 는 (20,40,20) 녹 그림자)
- `UPDATE g5_config SET cf_theme='<name>'` 한 줄로 즉시 전환

### 상단 nav 2-row 구조 (gnuboard 원본 패턴 따라)
- **Row 1** (top utility bar): 브랜드 · 커뮤니티/쇼핑몰 segment · 돋보기 (검색 페이지 직링크) · FAQ · Q&A · 새글 · 접속자 · 다크모드 토글 · 로그인 · 햄버거(모바일)
- **Row 2** (메인 nav, surface bg): 홈 + g5_menu 1차 + 하위 hover 드롭다운
- **`get_menu_db()` 동적 렌더링** — 관리자 → 환경설정 → 메뉴설정 의 g5_menu 항목을 그대로 1차 nav 로 출력. 외부 도메인 링크는 자동 _blank, 같은 호스트는 path/query 만 추출
- `G5_COMMUNITY_USE && G5_USE_SHOP` 둘 다 켜진 경우만 segment 토글 노출 (현재 path 가 /shop 으로 시작하면 쇼핑몰 active)
- 880px 이하: row 2 + utility 모두 햄버거 드로어로 흡수, 사이드바 outlogin 도 hide
- 모바일 드로어에 5개 유틸 링크 + g5_menu + segment 모두 노출

### path-style clean URL
- `/content?co_id=X` → **`/content/X`** (extraRoute + 정규화 redirect)
- `/group?gr_id=X` → **`/group/X`**
- `/qa/{qa_id}` (보기), **`/qa/{qa_id}/edit`** (수정 — resource-first), `/qa/write` (새 글)
- 라우터의 정규화 redirect 를 lookup 테이블로 일반화: `/content` → co_id, `/group` → gr_id (앞으로 같은 패턴 추가 시 한 줄)
- extraRoute target 이 `'bbs/foo.php?key=val'` 형태면 매칭 시 ?key=val 도 $_GET 에 자동 주입 — `/qa/{qa_id}/edit` 가 `w=u` 를 자동으로 가짐
- DB g5_menu 의 외부 데모 링크 (clcode.gnuboard.net) 도 모두 path-style 클린 URL 로 업데이트

### 1:1 문의 (QA) 모듈 모던화
- `g5_qa_config.qa_skin` = `theme/basic`
- 라우터: `/qa` (목록) · `/qa/{N}` (보기) · `/qa/write` (작성) · `/qa/{N}/edit` (수정) · `/qa/write_update` · `/qa/delete` · `/qa/download`
- 레거시 `.php` 5종 (qalist/qaview/qawrite/qadelete/qadownload/qawrite_update) 모두 GET/HEAD 시 클린 URL 로 301 (POST 는 패스스루)
- 5개 스킨 모던화: list (카테고리 pill + 검색 drawer + 답변완료/대기 status pill + 빈 상태) / view (카테고리 태그 + 메타 + status pill + 본문 + 추가질문 + 답변(또는 답변폼) include + prev/next + 연관질문 카드) / view.answer (primary 그라디언트 헤더 + 답변 카드) / view.answerform (관리자=등록 폼, 일반=답변 준비중) / write (분류/연락처/제목/내용/첨부 5단 카드)
- `bbs/qawrite.php` 의 `$action_url` 도 `/qa/write_update` 클린 URL 로
- list/view/write skin 에 `.m-board-head`, `.m-write-section`, `.m-view-*`, `.m-icon-btn` 등 board-common CSS 복제 (board skin 에만 있어 /qa 에선 미적용이던 문제)

### 추가 모던화
- **현재 접속자 `/connect`** (current_connect.skin) — 사람 아이콘 + 카운트 + grid auto-fill 카드 (회원=primary border-left + 프로필 / 비회원=guest svg 칩) + lo_url 클릭 (super admin)
- **outlogin 관리자 톱니** — 프로필 헤더 우측에 32px 톱니 chip, hover 시 45° 회전. 이전의 풀폭 "관리자" 버튼 제거하고 로그아웃이 풀-row 차지

### 폴리시
- 게시판 list 칼럼 정렬 — 텍스트(제목·글쓴이) 좌측 / 숫자·날짜 가운데 (specificity 매칭한 셀렉터로 default thead th 가운데를 덮음)
- 이전/다음 글 nav 를 grid 2-col → flex column 의 2행 가로 layout (제목 ellipsis + 날짜)
- 모바일에서 m-view-actions(목록·답변·글쓰기·케밥) flex-wrap nowrap + padding/font 축소로 한 줄에 들어가도록
- 모바일 글쓰기 관련링크 chip 아이콘 숨김 (40px 가 input 옆에 들어가지 못해 줄바꿈되던 문제)
- 새글 list, search.skin 의 #gr_id select 를 .m-input 스타일로 (id-scoped 셀렉터)
- search 의 게시판 필터 URL 을 `/search?...` 직접 박음 (`$_SERVER['SCRIPT_NAME']` = `/index.php` 였던 것)
- search 의 cnt_cmt 카운트 뱃지를 surface-2 round pill 로
- captcha 스피커/리프레시 — gnuboard sprite 무력화 + inline-SVG (Feather volume-2 / rotate-cw) 를 background-image data-URI 로 그려 양 모드 모두 동일한 흰 알약 + 다크 아이콘
- alert/confirm fallback noscript 마크업도 m-shell + m-card-narrow 카드로 (이전 라운드)
- viewport meta 무조건 출력 (`G5_IS_MOBILE` 분기 제거)
- 페이지네이션 `.pg_*` 토큰 스타일을 `_head.inc.php` 글로벌로 hoist
- 댓글/스크랩/메모/포인트/그룹/콘텐츠 등 모든 popup 스킨도 `.m-popup` 패턴 통일

### 직접 수정한 gnuboard 코어 파일 (이번 라운드)
- `app/bbs/qawrite.php` — `$action_url` 을 `/qa/write_update` 로
- `app/bbs/search.php` — `$str_board_list` 와 `$write_pages` URL 을 `/search` 로

### DB 변경 (이번 라운드)
- `cf_qa_skin` / `cf_mobile_qa_skin` / `cf_connect_skin` / `cf_mobile_connect_skin` = `theme/basic`
- g5_menu: 데모 외부 도메인 → path-style 클린 URL
- g5_qa_config.qa_title 그대로 (1:1문의)

### 메모리 추가
- `feedback_no_symlinks.md` — 새 테마 등 만들 때 symlink 금지, cp -r 로 실제 복사

---

다음 세션에서는 시스템(관리자 영역 `/adm/*`) 모던화나 사용자 플로우 실 사용 검증, 잔여 마이크로 폴리시(다크모드 토글 위치 미세조정 등) 진행 가능.

---

## shop 모더나이즈 트래커 (진행 중)

활성 (`G5_USE_SHOP=true`). DB 스킨 `theme/basic` → 실제 마크업은 `app/theme/basic/skin/shop/basic/*.skin.php`.
~36 skin + `shop.head.php` chrome 270 줄 + 70 PHP entry. 점진적으로 진행.

### 진행 순서
- **B-1** 핵심 페이지 (skin 단위) — list, item, cart
- **A** chrome (`shop.head.php`) 헤더/네비/사이드바
- **B-2** 나머지 — orderform, orderinquiry/view, mypage, coupon/couponzone, itemqa/use*, search, etc.

### 상태 (legacy ✗ / 작업중 ◯ / 완료 ●)

| 영역 | 파일 | 상태 |
|---|---|---|
| chrome | `theme/basic/shop/shop.head.php` | ● modern sticky 헤더 + TNB + 검색 + nav + side drawer |
| chrome | `theme/basic/shop/shop.tail.php` | ● modern footer (회사정보 grid + 최신글/접속자 카드) |
| chrome | `theme/basic/shop/category.php` | ✗ |
| 진입 | `theme/basic/shop/index.php` | ✗ |
| 카테고리 list | `skin/shop/basic/list.10.skin.php` | ● modern grid 카드 |
| 카테고리 list | `skin/shop/basic/list.20/30/40.skin.php` | ✗ (변형 — 사이드텍스트/컴팩트/리스트뷰) |
| 카테고리 list | `skin/shop/basic/list.sort.skin.php` | ✗ |
| 카테고리 list | `skin/shop/basic/list.sub.skin.php` | ✗ |
| 카테고리 list | `skin/shop/basic/listcategory*.skin.php` | ✗ |
| 메인 typed | `skin/shop/basic/main.10.skin.php` | ● modern carousel 카드 (owl-carousel JS 유지) |
| 메인 typed | `skin/shop/basic/main.20/40/50.skin.php` | ✗ |
| 상품 detail | `skin/shop/basic/item.form.skin.php` | ✗ |
| 상품 detail | `skin/shop/basic/item.info.skin.php` | ✗ |
| 상품문의 | `skin/shop/basic/itemqa{,form,list}.skin.php` | ✗ |
| 사용후기 | `skin/shop/basic/itemuse{,form,list}.skin.php` | ✗ |
| 쿠폰존 | `skin/shop/basic/couponzone.10.skin.php` | ✗ |
| box 위젯 | `skin/shop/basic/box{cart,wish,category,banner,event,today,community}.skin.php` | ✗ |
| 큰 이미지 | `skin/shop/basic/largeimage.skin.php` | ✗ |
| cart | `app/shop/cart.php` | ✗ |
| orderform | `app/shop/orderform.php` + `orderform.sub.php` | ✗ |
| orderinquiry | `app/shop/orderinquiry.php` + `orderinquiryview.php` + `orderinquirycancel.php` | ✗ |
| 주문주소 | `app/shop/orderaddress.php` | ✗ |
| mypage | `app/shop/mypage.php` (theme variant 도) | ✗ |
| 검색 | `app/shop/search.php` | ✗ |
| 쿠폰 | `app/shop/coupon.php`, `couponzone.php`, `ordercoupon.php` | ✗ |
| 개인결제 | `app/shop/personalpay.php`, `personalpayform.php`, `personalpayresult.php` | ✗ |
| 영수증 | `app/shop/taxsave.php`, `inicis/lg/nicepay/toss/taxsave_form.php` | ✗ |
| 옵션 popup | `app/shop/cartoption.php`, `itemoption.php` | ✗ |
| 추천 | `app/shop/itemrecommend.php` | ✗ |
| 재고 SMS | `app/shop/itemstocksms.php` | ✗ |

---

## 2026-05-08 — 설치 마법사 + DB 마이그레이션 + zerodate cleanup 통합

### 1) DB 스키마 모더나이즈

**환경**: MariaDB 12.1.2 (utf8mb4 native, ROW_FORMAT=DYNAMIC default)
**문제**: 기존 DB 가 utf8mb3 (이모지 ✗) + zero-date 기본값 (`'0000-00-00 00:00:00'`, MySQL 8.0+ strict mode 비호환)

**작업**:
- 새 admin 페이지 `/admin/db_migrate` (super admin 전용) — 환경설정 메뉴에서 진입
  - **문자셋**: utf8mb3 → utf8mb4_unicode_ci 테이블 단위 ALTER + 일괄 변환
  - **zero-date**: `NOT NULL date/datetime` + `DEFAULT '0000-...'` 컬럼 → `NULL DEFAULT NULL` ALTER + 기존 0000 값 → NULL UPDATE. 일괄 변환. PK 컬럼은 자동 제외 (NULL 불가)
  - sql_mode 안내 표시 (마이그레이션 후 `NO_ZERO_DATE,NO_ZERO_IN_DATE` 추가 권장)
- install SQL (`gnuboard5.sql`, `gnuboard5shop.sql`) — 신규 설치도 처음부터 InnoDB + utf8mb4 + nullable date 로 생성

### 2) PHP 코드 zero-date NULL 호환

마이그레이션 진행 중에도, 완료 후에도 둘 다 작동하도록 backward-compat:
- **읽기 비교** (5개): `WHERE col = '0000-...'` → `WHERE (col IS NULL OR col = '0000-...')`
- **쓰기 할당** (5개): PHP `$var = '0000-...'` → `null`, SQL INSERT VALUES `'0000-...'` → `NULL`
- **DDL** (11개): `NOT NULL DEFAULT '0000-...'` → `NULL DEFAULT NULL` (신규 설치/upgrade 시점)
- **`!isset($row['col'])` 스키마 가드** (54개) → `!array_key_exists('col', $row)` — `isset()` 이 NULL 도 false 처리해서 컬럼이 NULL 값일 때 ALTER 중복 ADD 시도하던 버그 fix

### 3) 설치 마법사 모더나이즈

- 브랜드: **그누보드5 SE / GNUBOARD5 SECOND EDITION**, MIT License (`LICENSE` 파일)
- 진행 단계 표시 (1 라이센스 → 2 환경설정 → 3 설치 완료)
- 모던 디자인 토큰 + 다크모드 토글 (메인 사이트와 `m-theme` localStorage 키 공유)
- 클린 URL: `/install/install_config`, `/install/install_db` 등 .php 없는 형태도 동작
- `data/` 절대경로 (`dirname(dirname(__DIR__))`) 사용 — apache CWD 의존 버그 fix
- `LICENSE` 파일 절대경로 로드, MIT 본문 textarea 표시
- `sql_connect()` `die()` → `throw RuntimeException()` — ajax 가 try/catch 로 정상 처리
- 누락된 `app/admin/sql_write.sql` git history 에서 복원 (legacy `/adm` 폐기 시 유실됨)

### 4) 라우팅 / 클린 URL 보강

- `/admin/...*.php` → `/admin/...*` 일괄 301 + ob_start rewrite
- `/shop/event/{ev_id}` 자원형 추가
- `/shop/orderinquiryview` 검색 (주문번호/주문일자) + 페이지 합계
- `/index.php` → `/` 301
- `.htaccess` install 매핑 — `/install/{name}` → `app/install/{name}.php` (RewriteCond -f 검사)

### 5) UI 정리

- admin 대시보드 카운트 카드 6개 — `bg-gradient-to-br` → 단색 + 좌측 4px accent border
- shop 페이지들 (cart/wishlist/coupon/orderaddress/orderinquiry/mypage 등) modern card list 로 재구성
- 통합 `/mypage` hub (커뮤니티 + 쇼핑 통합)
- footer 통일 (community + shop 동일)
- 다크모드 통합 (`data-theme` 단일 attribute, `m-theme` localStorage)

### 결과

- 모든 작업 브랜치 (`feat/install-modernize`, `feat/db-migration`, `feat/shop-admin`, `feat/shop-ui` 등) `main` 통합 + GitHub push
- README.md 신규 작성 — MIT License 자동 인식 (GitHub 사이드바 뱃지)

