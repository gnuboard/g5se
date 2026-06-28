<?php
// 모던 디자인 시스템 — 공통 head 주입
// 사용: 페이지 최상단에서 require_once(G5_THEME_PATH.'/modern/_head.inc.php');
// 그 다음 콘텐츠를 <div class="m-shell"> ... </div> 로 감싸면 됨.
// (m-shell 이 아닌 body 직속 형제는 모두 시각적으로 숨겨져 gnuboard chrome 이 가려짐)

if (!defined('_GNUBOARD_')) exit;
if (defined('_MODERN_HEAD_LOADED_')) return;
define('_MODERN_HEAD_LOADED_', true);

// CDN: 폰트 + UnoCSS reset + UnoCSS runtime
// FOUT(Flash Of Unstyled Text) 방지를 위해 preconnect + preload 로 폰트 다운로드를 paint 전에 끝냄.
// Pretendard 는 variable 폰트(단일 파일) 로 가져오면 한 번의 다운로드로 모든 weight 사용 가능.
add_stylesheet('<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>', -11);
add_stylesheet('<link rel="preload" as="style" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable.min.css">', -10);
add_stylesheet('<link rel="preload" as="font" type="font/woff2" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/packages/pretendard/dist/web/variable/woff2/PretendardVariable.woff2">', -9);
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable.min.css">', -8);
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@unocss/reset/tailwind.min.css">', -7);
add_javascript('<script src="https://cdn.jsdelivr.net/npm/@unocss/runtime/uno.global.js"></script>', -1);

// 카카오 SDK 자동 로드 — modern admin 의 '소셜 공유' (social_share) 그룹에 카카오 JS 키가 등록됐을 때만.
// share modal 의 [카카오톡] 버튼이 Kakao.Share.sendDefault 로 직접 공유 (SDK 없으면 navigator.share / clipboard fallback).
$_kakao_js_key = '';
if (function_exists('setting')) {
    try {
        $_social = setting('social_share');
        $_kakao_js_key = isset($_social['kakao_js_key']) ? trim((string)$_social['kakao_js_key']) : '';
    } catch (\Throwable $e) { /* schema 미등록 시 silent — fallback 동작 */ }
}
if ($_kakao_js_key !== '') {
    add_javascript('<script src="https://t1.kakaocdn.net/kakao_js_sdk/2.7.4/kakao.min.js" integrity="sha384-DKYJZ8NLiK8MN4/C5P2dtSmLQ4KwPaoqAfyA/DfmEc1VDxu4yyC7wy6K1Hs90nka" crossorigin="anonymous"></script>', -2);
    add_javascript('<script>window.kakao_javascript_apikey = '.json_encode($_kakao_js_key).';</script>', -1);
}
unset($_kakao_js_key);

// 다크모드 FOUC 방지: localStorage / 시스템 설정으로 페인트 전에 data-theme 적용
add_javascript('<script>(function(){try{var t=localStorage.getItem("m-theme");if(!t)t=matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";document.documentElement.dataset.theme=t;}catch(e){}})();</script>', -100);

// 메인 디자인 토큰 + 컴포넌트 스타일을 add_stylesheet 큐로 등록 → gnuboard default.css 이후에 삽입되도록.
// (그렇지 않으면 default.css 의 `body { font-family: 'Malgun Gothic' ... }` 가 우리 Pretendard 설정을 덮어써 자간/렌더 차이 발생)
ob_start();
?>
<style>
/* ──────────────────────────────────────────────
   디자인 토큰 (라이트)
   ────────────────────────────────────────────── */
:root {
    --m-bg:           #f8fafc;
    --m-surface:      #ffffff;
    --m-surface-2:    #f1f5f9;
    --m-border:       #e2e8f0;
    --m-border-hover: #cbd5e1;
    --m-text:         #0f172a;
    --m-text-muted:   #64748b;
    --m-text-soft:    #475569;
    --m-text-faint:   #94a3b8;
    --m-primary:      #2563eb;
    --m-primary-hover:#1d4ed8;
    --m-primary-soft: rgba(37,99,235,0.12);
    --m-radius-sm:    6px;
    --m-radius:       8px;
    --m-radius-lg:    12px;
    --m-shadow:       0 1px 3px rgba(15,23,42,0.04);
    --m-shadow-md:    0 4px 16px -4px rgba(15,23,42,0.08);

    /* 폰트 스케일 — 일관된 단계로 사이즈 통제. 추가 단계 필요시 여기에 정의. */
    --m-text-xs:      11px;   /* 뱃지·아이콘 pill */
    --m-text-sm:      12px;   /* 힌트·설명·divider */
    --m-text-base:    13px;   /* 기본 — label·link·meta·check */
    --m-text-md:      14px;   /* 인터랙티브 — input·btn·card body */
    --m-text-lg:      16px;   /* 부제목·hero 부제 */
    --m-text-xl:      18px;   /* brand·section title 강조 */
    --m-text-2xl:     22px;   /* 페이지 타이틀 */
    --m-text-3xl:     26px;   /* 큰 페이지 타이틀 */
    --m-text-display: 36px;   /* hero 제목 */

    /* line-height 도 단계화 */
    --m-leading-tight: 1.3;
    --m-leading:       1.5;
    --m-leading-relaxed: 1.7;

    /* 컨테이너 max-width 스케일 — Tailwind / UnoCSS 표준 (rem 기반) */
    --m-max-xs:   20rem;   /* 320px  */
    --m-max-sm:   24rem;   /* 384px  */
    --m-max-md:   28rem;   /* 448px  */
    --m-max-lg:   32rem;   /* 512px  */
    --m-max-xl:   36rem;   /* 576px  */
    --m-max-2xl:  42rem;   /* 672px  */
    --m-max-3xl:  48rem;   /* 768px  */
    --m-max-4xl:  56rem;   /* 896px  */
    --m-max-5xl:  64rem;   /* 1024px */
    --m-max-6xl:  72rem;   /* 1152px */
    --m-max-7xl:  80rem;   /* 1280px */

    color-scheme:     light;
}

/* 디자인 토큰 (다크) — html[data-theme="dark"] 일 때 덮어씀 */
[data-theme="dark"] {
    --m-bg:           #0a0e1a;
    --m-surface:      #131825;
    --m-surface-2:    #1c2230;
    --m-border:       #2a3344;
    --m-border-hover: #3d4a5e;
    --m-text:         #f1f5f9;
    --m-text-muted:   #94a3b8;
    --m-text-soft:    #cbd5e1;
    --m-text-faint:   #64748b;
    --m-primary:      #3b82f6;
    --m-primary-hover:#60a5fa;
    --m-primary-soft: rgba(59,130,246,0.20);
    --m-shadow:       0 1px 3px rgba(0,0,0,0.5);
    --m-shadow-md:    0 4px 16px -4px rgba(0,0,0,0.6);
    color-scheme:     dark;
}

/* ──────────────────────────────────────────────
   기본 body
   ────────────────────────────────────────────── */
html, body {
    margin: 0;
    background: var(--m-bg);
    color: var(--m-text);
    /* Pretendard Variable 우선, 미지원 브라우저는 정적 Pretendard 또는 시스템 폰트로 fallback */
    font-family: 'Pretendard Variable','Pretendard',-apple-system,BlinkMacSystemFont,system-ui,'Malgun Gothic',sans-serif;
    font-size: var(--m-text-md);  /* 기본 본문 사이즈 — 자식이 별도 지정 안 하면 14px */
    line-height: var(--m-leading);
    overflow: hidden;  /* m-shell 이 자체 스크롤 — body 스크롤바 중복 방지 */
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
}

.m-shell,
.m-shell *,
.m-shell *::before,
.m-shell *::after {
    box-sizing: border-box;
}

/* ──────────────────────────────────────────────
   Shell — fixed 오버레이로 gnuboard chrome 위에 덮어씌움
   (m-shell 이 DOM 어느 깊이에 있든 viewport 전체를 차지)
   ────────────────────────────────────────────── */
.m-shell {
    position: fixed; inset: 0;
    background: var(--m-bg);
    z-index: 9999;
    overflow-y: auto;
    overflow-x: clip;
    display: flex; flex-direction: column;
    min-height: 100vh;
}
/* sticky footer 패턴: m-shell 직속 main 이 남는 공간을 차지해 footer 가 viewport 하단에 붙도록 */
.m-shell > main { flex: 1 0 auto; }
.m-shell > .m-footer { flex-shrink: 0; }
.m-container { width: 100%; max-width: var(--m-max-7xl); min-width: 0; margin: 0 auto; padding: 0 20px; }
.m-center { display: grid; place-items: center; flex: 1; padding: 48px 16px; }

/* 메인+사이드 2-column 레이아웃 — 게시판 list, 메인, 추후 마이페이지 등에서 공통 사용 */
.m-with-sidebar {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 24px;
    align-items: start;
}
.m-main-col { min-width: 0; }   /* grid item 의 자식이 overflow 나지 않도록 */
.m-side-col { position: sticky; top: 80px; display: flex; flex-direction: column; gap: 16px; }
.m-side-card { display: block; }
@media (max-width: 880px) {
    .m-with-sidebar { grid-template-columns: 1fr; }
    .m-side-col { position: static; order: -1; }   /* 모바일에선 사이드(outlogin)가 위로 */
}

/* 메인 페이지 최신글 위젯 그리드 — 2열 기본, 좁으면 1열 */
.m-latest-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}
@media (max-width: 720px) {
    .m-latest-grid { grid-template-columns: 1fr; }
}

/* ──────────────────────────────────────────────
   Card
   ────────────────────────────────────────────── */
.m-card {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-lg);
    padding: 32px;
    box-shadow: var(--m-shadow);
}
.m-card-narrow { max-width: 400px; padding: 36px 32px; width: 100%; }
@media (max-width: 560px) {
    .m-container { padding-left: 14px; padding-right: 14px; }
    .m-card { padding: 20px; }
}

/* ──────────────────────────────────────────────
   Input / Label
   ────────────────────────────────────────────── */
.m-input {
    width: 100%; padding: 10px 12px; box-sizing: border-box;
    background: var(--m-surface);
    border: 1px solid var(--m-border-hover);
    border-radius: var(--m-radius);
    font-size: var(--m-text-md); color: var(--m-text);
    transition: border-color 0.15s, box-shadow 0.15s;
    outline: none;
    font-family: inherit;
}
.m-input:focus {
    border-color: var(--m-primary);
    box-shadow: 0 0 0 3px var(--m-primary-soft);
}
.m-input::placeholder { color: var(--m-text-faint); }

.m-label {
    display: block; font-size: var(--m-text-base); font-weight: 500;
    color: var(--m-text-soft); margin-bottom: 6px;
}

.m-pw-wrap { position: relative; }
.m-pw-toggle {
    position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
    background: transparent; border: 0; cursor: pointer;
    color: var(--m-text-faint); padding: 6px; border-radius: var(--m-radius-sm);
}
.m-pw-toggle:hover { color: var(--m-text-soft); }

.m-check { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; }
.m-check input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--m-primary); cursor: pointer; }
.m-check span { font-size: var(--m-text-base); color: var(--m-text-soft); }

/* ──────────────────────────────────────────────
   Buttons
   ────────────────────────────────────────────── */
.m-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 4px;
    box-sizing: border-box;
    padding: 11px 16px; border: 0; border-radius: var(--m-radius);
    font-size: var(--m-text-md); font-weight: 600;
    cursor: pointer; text-decoration: none; text-align: center;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
    font-family: inherit;
}
/* UnoCSS reset 이 SVG 를 block 으로 만들어 m-btn 안에서 줄바꿈됨 — 인라인 정렬 강제 */
.m-btn svg { display: inline-block; flex-shrink: 0; }
.m-btn-primary { background: var(--m-primary); color: white; width: 100%; }
.m-btn-primary:hover { background: var(--m-primary-hover); }

.m-btn-secondary {
    background: var(--m-surface);
    border: 1px solid var(--m-border-hover);
    color: var(--m-text-soft); width: 100%;
}
.m-btn-secondary:hover { background: var(--m-surface-2); border-color: var(--m-text-faint); }

.m-btn-ghost {
    background: transparent; color: var(--m-text-soft);
    padding: 8px 12px; font-weight: 500;
}
.m-btn-ghost:hover { background: var(--m-surface-2); }

/* ──────────────────────────────────────────────
   Misc
   ────────────────────────────────────────────── */
.m-link { color: var(--m-primary); font-size: var(--m-text-base); text-decoration: none; }
.m-link:hover { text-decoration: underline; }

.m-divider {
    display: flex; align-items: center; gap: 12px;
    color: var(--m-text-faint); font-size: var(--m-text-sm); margin: 18px 0;
}
.m-divider::before, .m-divider::after {
    content: ''; flex: 1; height: 1px; background: var(--m-border);
}

/* ──────────────────────────────────────────────
   Nav / Header
   ────────────────────────────────────────────── */
.m-nav {
    background: var(--m-surface);
    border-bottom: 1px solid var(--m-border);
    position: sticky; top: 0; z-index: 10;
}
.m-nav-inner {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; max-width: var(--m-max-7xl); margin: 0 auto;
}
.m-brand {
    font-size: var(--m-text-xl); font-weight: 700; color: var(--m-text);
    text-decoration: none;
}
.m-nav-actions { display: flex; align-items: center; gap: 8px; }

/* ──────────────────────────────────────────────
   Reset for elements that gnuboard's default.css might touch
   (높은 specificity 로 우리 스타일 보호)
   ────────────────────────────────────────────── */
.m-shell h1, .m-shell h2, .m-shell h3 { margin: 0; color: var(--m-text); font-weight: 700; }
.m-shell p { margin: 0; }
.m-shell a:not(.m-btn):not(.m-link) { color: inherit; }

/* ──────────────────────────────────────────────
   gnuboard kcaptcha 마크업 모던 스타일링 (모든 페이지 공통)
   ────────────────────────────────────────────── */
.m-captcha-wrap #captcha {
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
    border: 0; padding: 0; margin: 0;
}
.m-captcha-wrap #captcha legend { position: absolute; left: -9999px; }
.m-captcha-wrap #captcha_img {
    display: block; height: 44px; width: auto;
    border: 1px solid var(--m-border-hover);
    border-radius: var(--m-radius); background: white;
}
.m-captcha-wrap #captcha_key {
    width: 9ch;   /* 약 8자리 숫자 + 여유 1자 */
    padding: 10px 12px; box-sizing: content-box;
    background: var(--m-surface) !important; color: var(--m-text) !important;
    border: 1px solid var(--m-border-hover);
    border-radius: var(--m-radius);
    font-size: var(--m-text-md); font-family: inherit; outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    text-align: center; letter-spacing: 0.05em;
}
.m-captcha-wrap #captcha_key::placeholder { color: var(--m-text-faint); }
.m-captcha-wrap #captcha_key:focus {
    border-color: var(--m-primary);
    box-shadow: 0 0 0 3px var(--m-primary-soft);
}
.m-captcha-wrap #captcha_reload, .m-captcha-wrap #captcha_mp3 {
    width: 38px; height: 38px; padding: 0;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    cursor: pointer; color: var(--m-text-soft);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0;
}
.m-captcha-wrap #captcha_reload::before, .m-captcha-wrap #captcha_mp3::before {
    content: ''; display: block; width: 16px; height: 16px;
    background: currentColor;
}
.m-captcha-wrap #captcha_reload::before {
    -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'><path d='M3 12a9 9 0 0 1 15-6.7L21 8'/><path d='M21 3v5h-5'/><path d='M21 12a9 9 0 0 1-15 6.7L3 16'/><path d='M3 21v-5h5'/></svg>") no-repeat center / contain;
            mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'><path d='M3 12a9 9 0 0 1 15-6.7L21 8'/><path d='M21 3v5h-5'/><path d='M21 12a9 9 0 0 1-15 6.7L3 16'/><path d='M3 21v-5h5'/></svg>") no-repeat center / contain;
}
.m-captcha-wrap #captcha_mp3::before {
    -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'><polygon points='11 5 6 9 2 9 2 15 6 15 11 19 11 5'/><path d='M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07'/></svg>") no-repeat center / contain;
            mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'><polygon points='11 5 6 9 2 9 2 15 6 15 11 19 11 5'/><path d='M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07'/></svg>") no-repeat center / contain;
}
.m-captcha-wrap #captcha_reload:hover, .m-captcha-wrap #captcha_mp3:hover {
    background: var(--m-border); color: var(--m-text);
}
.m-captcha-wrap #captcha_info {
    display: block; flex-basis: 100%;
    font-size: var(--m-text-xs); color: var(--m-text-faint); margin-top: 4px;
}

/* ──────────────────────────────────────────────
   다크모드 토글 버튼 (_nav.inc.php 헤더에 정적 배치, 클릭 핸들러는 JS)
   ────────────────────────────────────────────── */
.m-theme-toggle {
    width: 36px; height: 36px; padding: 0;
    background: transparent;
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    color: var(--m-text-soft);
    cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.m-theme-toggle:hover {
    background: var(--m-surface-2);
    color: var(--m-text);
    border-color: var(--m-border-hover);
}
.m-theme-toggle svg { width: 16px; height: 16px; }
.m-theme-toggle .m-icon-sun  { display: none; }
.m-theme-toggle .m-icon-moon { display: block; }
[data-theme="dark"] .m-theme-toggle .m-icon-sun  { display: block; }
[data-theme="dark"] .m-theme-toggle .m-icon-moon { display: none; }

/* 관리자 빠른편집 톱니 (.btn_admin) — outlogin 의 .m-ol-admin-shortcut 와 동일한 톤 */
.m-shell .btn_admin {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    width: 32px; height: 32px;
    padding: 0 !important;
    background: var(--m-surface-2) !important;
    border: 1px solid var(--m-border) !important;
    border-radius: var(--m-radius) !important;
    color: var(--m-text-muted) !important;
    text-decoration: none !important;
    vertical-align: middle;
    transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.4s;
}
.m-shell .btn_admin:hover {
    background: var(--m-primary-soft) !important;
    color: var(--m-primary) !important;
    border-color: var(--m-primary) !important;
    transform: rotate(45deg);
}
.m-shell .btn_admin .fa-spin,
.m-shell .btn_admin .fa-cog {
    animation: none !important;
    -webkit-animation: none !important;
    margin: 0;
}

/* SmartEditor2 단축키 일람 — 다크모드에서 짙은 바탕 + 밝은 글자로. */
[data-theme="dark"] .cke_sc_def {
    background: var(--m-surface);
    border-color: var(--m-border);
    color: var(--m-text);
}
[data-theme="dark"] .cke_sc_def dt,
[data-theme="dark"] .cke_sc_def dd {
    border-bottom-color: var(--m-border);
    color: var(--m-text);
}
[data-theme="dark"] .btn_cke_sc {
    background: var(--m-surface);
    border-color: var(--m-border);
    color: var(--m-text);
}

/* 글쓴이 닉네임/게스트명 — default.css 의 .sv_member { color:#333 } 가
   다크모드에서 어두운 배경에 어두운 글자가 되어 안보이는 문제. 토큰으로 덮음. */
[data-theme="dark"] .sv_member,
[data-theme="dark"] .sv_member:link,
[data-theme="dark"] .sv_member:visited,
[data-theme="dark"] .sv_guest,
[data-theme="dark"] .sv_guest:link,
[data-theme="dark"] .sv_guest:visited {
    color: var(--m-text-soft);
}

/* sideview — 회원 아이콘 + 닉네임이 같은 줄에 오도록.
   UnoCSS reset 이 img 를 display:block 으로 만들어 아이콘이 따로 줄을 차지하던 문제. */
.sv_wrap { display: inline-flex; align-items: center; gap: 4px; }
.sv_member, .sv_guest { display: inline-flex; align-items: center; gap: 4px; }
.profile_img { display: inline-flex; align-items: center; line-height: 0; }
.profile_img img { display: inline-block; vertical-align: middle; }

/* gnuboard 페이지네이션 (.pg_*) — get_paging() 출력을 토큰 기반으로 재스타일.
   default.css 가 .pg_start/.pg_prev/.pg_next/.pg_end 에 sprite GIF 배경 + text-indent:-999px 를
   걸어놔서 화살표 라벨이 보이지 않으므로 같이 리셋. 모든 스킨에서 write_pages 만 출력하면 적용됨. */
.m-pagination { margin-top: 24px; display: flex; justify-content: center; }
.pg_wrap { display: inline-flex; gap: 4px; flex-wrap: wrap; }
.pg_wrap .pg { display: inline-flex; gap: 4px; flex-wrap: wrap; }
.pg_page, .pg_current,
.pg_start, .pg_prev, .pg_next, .pg_end {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 36px; height: 36px; padding: 0 10px;
    background: var(--m-surface); border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    color: var(--m-text-soft); text-decoration: none; font-size: var(--m-text-base);
    transition: background 0.15s, border-color 0.15s, color 0.15s;
    text-indent: 0; overflow: visible; background-image: none;
}
a.pg_page:hover, a.pg_start:hover, a.pg_prev:hover, a.pg_next:hover, a.pg_end:hover {
    background: var(--m-surface-2); border-color: var(--m-border-hover); color: var(--m-text);
}
.pg_current {
    background: var(--m-primary); border-color: var(--m-primary); color: #fff; font-weight: 600;
}

/* sideview 팝업 메뉴 (.sv_wrap .sv) — gnuboard 기본 #333 검정 박스 → 모던 카드 */
/* sv_wrap 을 inline-flex 로 바꾸면서 자식 .sv 가 같이 노출되던 문제: 명시적으로 숨김 처리 */
.sv_wrap .sv {
    display: none !important;
    position: absolute;
    margin-top: 8px !important;
    min-width: 140px;
    padding: 6px;
    background: var(--m-surface) !important;
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    box-shadow: var(--m-shadow-md) !important;
    font-size: var(--m-text-sm);
    overflow: hidden;
    z-index: 100;
}
.sv_wrap .sv.sv_on { display: block !important; }
.sv_wrap { position: relative; }
/* sideview 가 열리면 부모 sv_wrap 자체를 stacking context 의 위로 올려서
   테이블의 다른 행들 (hover bg 등) 에 가려지지 않도록 보장 */
.sv_wrap:has(.sv_on) { z-index: 1000; }
.sv_wrap .sv:before {
    border-bottom-color: var(--m-border) !important;
    top: -7px !important;
}
.sv_wrap .sv:after {
    content: ""; position: absolute;
    top: -5px; left: 16px; width: 0; height: 0;
    border-style: solid; border-width: 0 5px 5px 5px;
    border-color: transparent transparent var(--m-surface) transparent;
}
.sv_wrap .sv a {
    width: auto !important; min-width: 120px;
    line-height: 1 !important;
    padding: 9px 12px !important;
    border-radius: var(--m-radius-sm);
    color: var(--m-text-soft) !important;
    font-size: var(--m-text-sm) !important;
    transition: background 0.15s, color 0.15s;
}
.sv_wrap .sv a:hover {
    background: var(--m-primary-soft) !important;
    color: var(--m-primary) !important;
}
.sv_on {
    top: calc(100% + 2px) !important;
    left: 0 !important;
}

/* ──────────────────────────────────────────────
   Popup window 공통 (쪽지 등 window.open 으로 뜨는 작은 창)
   m-shell 처럼 fixed 오버레이로 gnuboard chrome 을 가린다.
   ────────────────────────────────────────────── */
.m-popup {
    position: fixed; inset: 0;
    background: var(--m-bg);
    color: var(--m-text);
    z-index: 9999;
    overflow: auto;
    padding: 14px 16px;
    box-sizing: border-box;
    min-height: 100vh;
}
.m-popup-head { margin-bottom: 8px; }
.m-popup-title {
    display: flex; align-items: center; gap: 8px;
    font-size: var(--m-text-lg); font-weight: 700;
    color: var(--m-text); margin: 0 0 2px;
}
.m-popup-title svg { color: var(--m-primary); }
.m-popup-sub { margin: 0; font-size: var(--m-text-sm); color: var(--m-text-muted); }
.m-popup-sub strong { color: var(--m-text); }
.m-popup-hint {
    display: flex; align-items: center; gap: 6px;
    margin: 14px 0 0; padding: 10px 14px;
    background: var(--m-surface-2); border-radius: var(--m-radius);
    font-size: var(--m-text-xs); color: var(--m-text-muted);
}
.m-popup-hint svg { color: var(--m-text-faint); flex-shrink: 0; }
.m-popup-hint strong { color: var(--m-text); font-weight: 600; }
.m-popup-actions {
    display: flex; justify-content: flex-end; gap: 6px;
    margin-top: 10px;
}

/* 쪽지 탭 */
.m-memo-tabs {
    display: flex; gap: 4px;
    border-bottom: 1px solid var(--m-border);
    margin-bottom: 10px;
}
.m-memo-tab {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 7px 12px;
    color: var(--m-text-muted); text-decoration: none;
    font-size: var(--m-text-sm); font-weight: 500;
    border-bottom: 2px solid transparent;
    transition: color 0.15s, border-color 0.15s;
    margin-bottom: -1px;
}
.m-memo-tab:hover { color: var(--m-text); }
.m-memo-tab.is-active {
    color: var(--m-primary); border-bottom-color: var(--m-primary);
}
.m-memo-tab-write { margin-left: auto; color: var(--m-primary); }

/* 쪽지 공통 — avatar */
.m-memo-avatar { display: inline-flex; align-items: center; flex-shrink: 0; }
.m-memo-avatar img {
    width: 36px; height: 36px; border-radius: 50%;
    border: 1px solid var(--m-border); object-fit: cover;
}

/* ──────────────────────────────────────────────
   kcaptcha — 모든 모던 폼 (register_form, memo_form, board write, password_lost, formmail 등)
   captcha_html() 이 #captcha 컨테이너로 일관된 마크업을 출력하므로 글로벌 셀렉터로 통일.
   이미지/입력/스피커/리프레시 모두 같은 height(40px) 한 줄 정렬, 다크모드에서도 입력값이 읽힘.
   ────────────────────────────────────────────── */
#captcha {
    --cap-h: 40px;
    display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
}
#captcha_img,
#captcha_mp3,
#captcha_reload {
    display: inline-flex; align-items: center; justify-content: center;
    height: var(--cap-h); box-sizing: border-box;
    border: 1px solid var(--m-border); border-radius: var(--m-radius-sm);
    background: var(--m-surface); overflow: hidden;
}
#captcha_img img,
#captcha_img canvas { height: 100%; width: auto; display: block; }
#captcha #captcha_key,
#captcha_key {
    height: var(--cap-h, 40px) !important;
    width: 9ch !important;
    min-width: 9ch !important;
    box-sizing: content-box !important;
    padding: 0 12px !important;
    background: var(--m-surface) !important;
    color: var(--m-text) !important;
    border: 1px solid var(--m-border) !important;
    border-radius: var(--m-radius-sm) !important;
    font-size: var(--m-text-md) !important;
    font-weight: 600 !important;
    letter-spacing: 0.08em !important;
    text-align: center !important;
    -webkit-text-fill-color: var(--m-text);
}
#captcha_key::placeholder {
    color: var(--m-text-faint); font-weight: 400; letter-spacing: 0;
}
#captcha_key:focus {
    outline: none; border-color: var(--m-primary) !important;
    box-shadow: 0 0 0 3px var(--m-primary-soft);
}
/* 캡챠 스피커/리프레시 — gnuboard sprite 대신 inline-SVG (Feather volume-2 / rotate-cw) 를
   background-image 로 그려 양 모드 모두 흰 알약 + 다크 아이콘. SVG color 는 url-encoded %23.
   shorthand + !important + 별도 background-size 로 다른 규칙이 부분 속성으로 덮지 못하게 함. */
#captcha #captcha_mp3,
#captcha #captcha_reload {
    width: var(--cap-h) !important; height: var(--cap-h) !important;
    background-color: #ffffff !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
    background-size: 18px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: var(--m-radius-sm) !important;
    text-indent: 0 !important;
    color: transparent !important;
    cursor: pointer;
}
#captcha #captcha_mp3 {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231e293b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polygon points='11 5 6 9 2 9 2 15 6 15 11 19 11 5'/><path d='M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07'/></svg>") !important;
}
#captcha #captcha_reload {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231e293b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='23 4 23 10 17 10'/><path d='M20.49 15a9 9 0 1 1-2.12-9.36L23 10'/></svg>") !important;
}
#captcha_mp3:hover, #captcha_reload:hover { background-color: #f1f5f9 !important; border-color: #94a3b8 !important; }
#captcha_mp3 > *, #captcha_reload > * { display: none !important; }
#captcha_info {
    width: 100%; margin: 4px 0 0;
    font-size: var(--m-text-xs); color: var(--m-text-muted);
}

/* newwin (팝업레이어) — 다크모드 톤 보정 (default.css 의 .hd_pops {background:#fff} 가 하드코딩이라 가독성 깨짐) */
.hd_pops {
    background: var(--m-surface) !important;
    color: var(--m-text);
    border-color: var(--m-border) !important;
}
.hd_pops_con,
.hd_pops_con * { color: var(--m-text); }
[data-theme="dark"] .hd_pops_footer { background: #1c2230 !important; color: var(--m-text); }
[data-theme="dark"] .hd_pops_footer button,
[data-theme="dark"] .hd_pops_footer .hd_pops_reject,
[data-theme="dark"] .hd_pops_footer .hd_pops_close { background: #2a3344 !important; color: var(--m-text-soft) !important; }

/* 게시판 view 의 기본 SNS 영역은 숨김 — modern UI 는 .m-view-scrap 의 [공유] 버튼 + 모달 사용.
   JS 가 #bo_v_sns 의 link 들을 파싱해 모달 옵션으로 옮긴다. */
.m-shell #bo_v_sns { display: none !important; }

/* .m-view-scrap (본문 하단의 스크랩 + 공유 칩 라인) — .m-view-actions 와 동일한
   m-icon-btn 칩 layout. JS 가 [공유] 버튼을 스크랩 anchor 우측에 inject. */
.m-view-scrap {
    display: flex; flex-wrap: wrap; gap: 6px;
    margin-top: 14px;
    justify-content: flex-end;
}
.m-view-scrap .m-icon-btn {
    width: auto; padding: 6px 12px;
    display: inline-flex; align-items: center; gap: 4px;
    font-size: var(--m-text-sm); color: var(--m-text-soft);
}
.m-view-scrap .m-icon-btn span { font-size: var(--m-text-sm); }
@media (max-width: 540px) {
    .m-view-scrap { flex-wrap: nowrap; gap: 2px; }
    .m-view-scrap .m-icon-btn { padding: 6px 8px; gap: 3px; }
    .m-view-scrap .m-icon-btn span { font-size: var(--m-text-xs); }
}

/* 공유 모달 */
.m-share-modal[hidden] { display: none !important; }
.m-share-modal {
    position: fixed; inset: 0; z-index: 10000;
    display: flex; align-items: center; justify-content: center;
    background: rgba(0,0,0,0.5);
    padding: 16px;
    opacity: 0;
    transition: opacity .15s ease;
}
.m-share-modal.is-open { opacity: 1; }
.m-share-card {
    position: relative;
    background: var(--m-surface);
    border-radius: var(--m-radius-lg);
    padding: 20px;
    max-width: 360px; width: 100%;
    box-shadow: var(--m-shadow-md);
    transform: translateY(10px);
    transition: transform .18s cubic-bezier(0.4,0,0.2,1);
}
.m-share-modal.is-open .m-share-card { transform: translateY(0); }
.m-share-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.m-share-title { font-size: var(--m-text-lg); font-weight: 700; color: var(--m-text); margin: 0; }
.m-share-close {
    background: transparent; border: 0; cursor: pointer;
    padding: 6px 10px; border-radius: var(--m-radius-sm);
    color: var(--m-text-muted); font-size: 16px;
}
.m-share-close:hover { background: var(--m-surface-2); color: var(--m-text); }
.m-share-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.m-share-btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 12px 14px;
    border-radius: var(--m-radius);
    background: var(--m-surface-2);
    color: var(--m-text);
    border: 1px solid var(--m-border);
    text-decoration: none;
    font-size: var(--m-text-base); font-weight: 600;
    cursor: pointer; text-align: left;
    transition: background .15s, border-color .15s, color .15s, transform .15s;
}
.m-share-btn:hover { transform: translateY(-1px); }
.m-share-btn img, .m-share-btn svg { width: 18px; height: 18px; flex-shrink: 0; }
.m-share-btn.sns_f:hover  { background: rgba(24,119,242,0.10); border-color: #1877f2; color: #1877f2; }
.m-share-btn.sns_x:hover  { background: var(--m-text); border-color: var(--m-text); color: var(--m-bg); }
.m-share-btn.sns_k:hover  { background: rgba(254,229,0,0.20); border-color: #fee500; color: #3d2900; }
[data-theme="dark"] .m-share-btn.sns_k:hover { color: #fee500; }
.m-share-btn.sns_threads:hover { background: var(--m-text); border-color: var(--m-text); color: var(--m-bg); }
.m-share-btn.sns_copy:hover { background: var(--m-primary-soft); border-color: var(--m-primary); color: var(--m-primary); }
.m-share-toast {
    position: absolute; bottom: -36px; left: 50%; transform: translateX(-50%);
    background: var(--m-text); color: var(--m-bg);
    padding: 6px 12px; border-radius: var(--m-radius-sm);
    font-size: var(--m-text-sm); white-space: nowrap;
    opacity: 0; pointer-events: none;
    transition: opacity .2s, transform .2s;
}
.m-share-toast.is-show { opacity: 1; transform: translateX(-50%) translateY(-6px); }
@media (max-width: 480px) {
    .m-share-grid { grid-template-columns: 1fr; }
}
</style>
<?php
// 위 <style> 블록을 ob 에서 꺼내 add_stylesheet 큐로 등록 (default.css 이후에 삽입).
$_modern_main_css = ob_get_clean();
add_stylesheet($_modern_main_css, 50);

// ──────────────────────────────────────────────
// 화면 우하단 floating "위로 가기" 버튼.
// (다크모드 토글은 _nav.inc.php 헤더로 이동 — 로그인/햄버거 좌측에 정적 배치)
// ──────────────────────────────────────────────
$_modern_float_css = <<<'CSS'
<style>
.m-float-actions {
    position: fixed;
    right: 16px;
    bottom: 16px;
    z-index: 10001;  /* .m-shell (9999) 위로 — 덮이지 않도록 */
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.m-float-actions button {
    width: 44px; height: 44px;
    padding: 0; margin: 0;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: 999px;
    color: var(--m-text-soft);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--m-shadow);
    transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.15s, opacity 0.2s;
}
.m-float-actions button:hover {
    background: var(--m-surface-2);
    color: var(--m-text);
    border-color: var(--m-border-hover);
}
.m-float-actions button svg { width: 18px; height: 18px; }
/* 모바일 — 살짝 작게 */
@media (max-width: 768px) {
    .m-float-actions { right: 12px; bottom: 12px; gap: 6px; }
    .m-float-actions button { width: 40px; height: 40px; }
}
</style>
CSS;
add_stylesheet($_modern_float_css, 51);

$_modern_toggle_js = <<<'JS'
<script>
document.addEventListener("DOMContentLoaded", function(){
    // 헤더(_nav) 의 테마 토글 — popup/iframe 여부와 무관하게 항상 바인딩
    var themeBtn = document.querySelector(".m-theme-toggle");
    if (themeBtn) {
        themeBtn.addEventListener("click", function(){
            var cur = document.documentElement.dataset.theme || "light";
            var next = cur === "dark" ? "light" : "dark";
            document.documentElement.dataset.theme = next;
            try { localStorage.setItem("m-theme", next); } catch(e) {}
        });
    }

    // popup 윈도우 및 iframe 레이어에서는 floating "위로 가기" 숨김
    // — point/memo/scrap/coupon/orderaddress 등 popup 컨텍스트에서는 본문 조작 버튼을 노출하지 않는다.
    try {
        if (window.opener && window.opener !== window) return;
        if (window.parent && window.parent !== window && window.parent.G5PopupLayer) return;
        if (new URLSearchParams(window.location.search).get("g5_layer") === "1") return;
    } catch (e) { /* cross-origin opener 접근 차단되면 무시 */ }
    // 우하단 floating "위로 가기" 버튼 생성
    var wrap = document.createElement("div");
    wrap.className = "m-float-actions";
    wrap.innerHTML = ''
        + '<button type="button" class="m-float-top" aria-label="위로 가기" title="위로 가기">'
        +   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>'
        + '</button>';
    document.body.appendChild(wrap);

    var topBtn = wrap.querySelector(".m-float-top");

    // 위로 가기 — m-shell 이 실제 scroll container 면 그걸, 아니면 window
    var shell = document.querySelector(".m-shell");
    var scroller = (shell && shell.scrollHeight > shell.clientHeight + 10) ? shell : window;
    topBtn.addEventListener("click", function(){
        if (scroller === window) {
            window.scrollTo({ top: 0, behavior: "smooth" });
        } else {
            scroller.scrollTo({ top: 0, behavior: "smooth" });
        }
    });

    function onScroll() {
        var y = (scroller === window) ? window.scrollY : scroller.scrollTop;
        wrap.classList.toggle("is-scrolled", y > 200);
    }
    if (scroller === window) {
        window.addEventListener("scroll", onScroll, { passive: true });
    } else {
        scroller.addEventListener("scroll", onScroll, { passive: true });
    }
    onScroll();
});
</script>
JS;
add_javascript($_modern_toggle_js, 100);

// ──────────────────────────────────────────────
// 게시판 view 의 공유 버튼 + 모달 — 기존 #bo_v_sns 의 link 들을 파싱해서
// .m-view-actions 안에 [공유] m-icon-btn inject 하고 모달 열기.
// + 링크 복사 (clipboard API) 옵션 추가.
// ──────────────────────────────────────────────
$_modern_share_js = <<<'JS'
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 게시판 view 페이지에서만 활성화 — #bo_v_sns 존재가 indicator
    var sns = document.getElementById("bo_v_sns");
    if (!sns) return;
    // [공유] 위치: .m-view-scrap (하단 영역) 의 스크랩 우측. 스크랩 없으면 새 .m-view-scrap 생성해서
    // .m-view-react (추천/비추천 영역) 직전에 둠. .m-view-react 도 없으면 #bo_v_atc 다음.
    var scrap = document.querySelector(".m-view-scrap");
    if (!scrap) {
        scrap = document.createElement("div");
        scrap.className = "m-view-scrap";
        var react = document.querySelector(".m-view-react");
        if (react && react.parentNode) {
            react.parentNode.insertBefore(scrap, react);
        } else {
            var atc = document.getElementById("bo_v_atc");
            if (atc && atc.parentNode) atc.parentNode.insertBefore(scrap, atc.nextSibling);
            else return;
        }
    }

    // 페이지 URL / title 자체 추출 (sns_send.php 의존 안 함 — 직접 외부 share URL 생성)
    var pageUrl   = window.location.href;
    var pageTitle = document.title || (document.querySelector("h1") ? document.querySelector("h1").textContent.trim() : "");

    // SNS 옵션 정의
    var SNS = [
        {
            key: "sns_f", label: "페이스북",
            href: "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(pageUrl),
            icon: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg>'
        },
        {
            key: "sns_x", label: "X",
            href: "https://x.com/intent/post?url=" + encodeURIComponent(pageUrl) + "&text=" + encodeURIComponent(pageTitle),
            icon: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>'
        },
        {
            key: "sns_threads", label: "Threads",
            href: "https://www.threads.net/intent/post?text=" + encodeURIComponent(pageTitle + "\n" + pageUrl),
            icon: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.255 11.083a8.4 8.4 0 0 0-.32-.144c-.188-3.47-2.084-5.456-5.262-5.476h-.043c-1.9 0-3.482.812-4.455 2.287l1.747 1.198c.728-1.103 1.87-1.337 2.71-1.337h.029c1.044.007 1.832.31 2.343.902.371.43.62 1.026.745 1.776a13.5 13.5 0 0 0-2.998-.144c-3.015.175-4.954 1.933-4.823 4.378.066 1.24.685 2.307 1.74 3.003.893.589 2.043.876 3.238.812 1.578-.087 2.817-.687 3.681-1.785.658-.836 1.075-1.92 1.258-3.282.752.453 1.31 1.05 1.618 1.767.523 1.22.554 3.224-1.082 4.86-1.435 1.432-3.16 2.052-5.764 2.071-2.888-.021-5.07-.948-6.485-2.755C7.798 17.532 7.092 15.43 7.064 12c.028-3.43.734-5.531 2.084-7.244C10.564 2.948 12.746 2.022 15.634 2c2.91.022 5.13.952 6.6 2.766.72.889 1.263 2.007 1.62 3.314l-2.151.575c-.286-1.05-.696-1.937-1.224-2.65-.928-1.255-2.367-1.926-4.279-1.946-3.03.021-5.16.93-6.331 2.696-1.103 1.66-1.676 4.044-1.706 7.288.03 3.244.603 5.627 1.706 7.288 1.171 1.766 3.302 2.675 6.331 2.696 2.27-.012 3.79-.564 5.067-1.847.92-.922 1.467-2.2 1.456-3.392-.011-.804-.236-1.535-.661-2.17z"/></svg>'
        },
        {
            // 카카오: Web Share API (모바일 native sheet 에서 카카오톡 선택 가능) + 데스크탑 fallback
            // = 클립보드 복사 후 toast "링크 복사됨 — 카카오톡에 붙여넣어 공유"
            key: "sns_k", label: "카카오톡",
            href: null,  // 클릭 시 JS 핸들러 — share API 또는 copy
            icon: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C6.477 3 2 6.477 2 10.8c0 2.8 1.857 5.265 4.638 6.66l-1.18 4.31c-.103.376.296.69.628.49l5.158-3.413c.247.025.496.043.756.043 5.523 0 10-3.477 10-7.8S17.523 3 12 3z"/></svg>',
            customClick: function (showToast, setToast) {
                // 1) Kakao SDK (cf_kakao_js_apikey 설정 시 자동 로드됨) — 직접 카카오톡 공유
                if (window.Kakao && window.kakao_javascript_apikey) {
                    try {
                        if (!Kakao.isInitialized()) Kakao.init(window.kakao_javascript_apikey);
                        var KakaoShare = Kakao.Share || Kakao.Link;
                        if (KakaoShare && KakaoShare.sendDefault) {
                            KakaoShare.sendDefault({
                                objectType: "feed",
                                content: {
                                    title: pageTitle,
                                    description: pageUrl,
                                    link: { mobileWebUrl: pageUrl, webUrl: pageUrl }
                                }
                            });
                            return;
                        }
                    } catch (e) {}
                }
                // 2) Web Share API (모바일 native sheet 에서 카카오톡 선택 가능)
                if (navigator.share) {
                    navigator.share({ title: pageTitle, url: pageUrl }).catch(function () {});
                    return;
                }
                // 3) 클립보드 fallback
                var fallbackCopy = function (text) {
                    var ta = document.createElement("textarea");
                    ta.value = text; ta.style.position = "fixed"; ta.style.opacity = "0";
                    document.body.appendChild(ta); ta.select();
                    try { document.execCommand("copy"); setToast("링크가 복사되었습니다 — 카카오톡에 붙여넣어 공유"); showToast(); } catch (e) {}
                    document.body.removeChild(ta);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(pageTitle + "\n" + pageUrl).then(function () {
                        setToast("링크가 복사되었습니다 — 카카오톡에 붙여넣어 공유");
                        showToast();
                    }).catch(function () { fallbackCopy(pageTitle + "\n" + pageUrl); });
                } else {
                    fallbackCopy(pageTitle + "\n" + pageUrl);
                }
            }
        }
    ];

    // [공유] m-icon-btn inject
    var shareBtn = document.createElement("button");
    shareBtn.type = "button";
    shareBtn.className = "m-icon-btn";
    shareBtn.title = "공유";
    shareBtn.innerHTML =
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>' +
        '<line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>' +
        '</svg><span>공유</span>';
    // 사용자 요구: 스크랩 우측 (스크랩 anchor 의 nextSibling 위치).
    var scrapAnchor = scrap.querySelector("a.m-icon-btn");
    if (scrapAnchor) {
        scrap.insertBefore(shareBtn, scrapAnchor.nextSibling);
    } else {
        scrap.appendChild(shareBtn);
    }

    // 모달
    var modal = document.createElement("div");
    modal.className = "m-share-modal";
    modal.hidden = true;
    modal.innerHTML =
        '<div class="m-share-card" role="dialog" aria-modal="true" aria-label="공유">' +
            '<header class="m-share-head">' +
                '<h3 class="m-share-title">공유</h3>' +
                '<button type="button" class="m-share-close" aria-label="닫기">✕</button>' +
            '</header>' +
            '<div class="m-share-grid"></div>' +
            '<div class="m-share-toast">링크가 복사되었습니다</div>' +
        '</div>';
    var grid = modal.querySelector(".m-share-grid");
    var toast = modal.querySelector(".m-share-toast");

    function setToast(text) { toast.textContent = text; }
    function showToast() {
        toast.classList.add("is-show");
        clearTimeout(toast._timer);
        toast._timer = setTimeout(function () { toast.classList.remove("is-show"); }, 2000);
    }

    SNS.forEach(function (s) {
        var el;
        if (s.customClick) {
            el = document.createElement("button");
            el.type = "button";
            el.addEventListener("click", function () { s.customClick(showToast, setToast); });
        } else {
            el = document.createElement("a");
            el.href = s.href;
            el.target = "_blank";
            el.rel = "noopener";
        }
        el.className = "m-share-btn " + s.key;
        el.innerHTML = s.icon + '<span>' + s.label + '</span>';
        grid.appendChild(el);
    });

    // 링크 복사
    var copyBtn = document.createElement("button");
    copyBtn.type = "button";
    copyBtn.className = "m-share-btn sns_copy";
    copyBtn.innerHTML =
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<rect x="9" y="9" width="13" height="13" rx="2"/>' +
        '<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>' +
        '</svg><span>링크 복사</span>';
    copyBtn.addEventListener("click", function () {
        function done() { setToast("링크가 복사되었습니다"); showToast(); }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(pageUrl).then(done).catch(function () {
                var ta = document.createElement("textarea");
                ta.value = pageUrl; ta.style.position = "fixed"; ta.style.opacity = "0";
                document.body.appendChild(ta); ta.select();
                try { document.execCommand("copy"); done(); } catch (e) {}
                document.body.removeChild(ta);
            });
        } else {
            var ta = document.createElement("textarea");
            ta.value = pageUrl; ta.style.position = "fixed"; ta.style.opacity = "0";
            document.body.appendChild(ta); ta.select();
            try { document.execCommand("copy"); done(); } catch (e) {}
            document.body.removeChild(ta);
        }
    });
    grid.appendChild(copyBtn);
    document.body.appendChild(modal);

    function open() {
        modal.hidden = false;
        requestAnimationFrame(function () { modal.classList.add("is-open"); });
        document.body.style.overflow = "hidden";
    }
    function close() {
        modal.classList.remove("is-open");
        document.body.style.overflow = "";
        setTimeout(function () { if (!modal.classList.contains("is-open")) modal.hidden = true; }, 200);
    }
    shareBtn.addEventListener("click", function (e) { e.preventDefault(); open(); });
    modal.addEventListener("click", function (e) { if (e.target === modal) close(); });
    modal.querySelector(".m-share-close").addEventListener("click", close);
    document.addEventListener("keydown", function (e) { if (e.key === "Escape" && !modal.hidden) close(); });
});
</script>
JS;
add_javascript($_modern_share_js, 110);
