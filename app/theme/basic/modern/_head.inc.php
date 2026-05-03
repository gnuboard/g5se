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

/* ──────────────────────────────────────────────
   Shell — fixed 오버레이로 gnuboard chrome 위에 덮어씌움
   (m-shell 이 DOM 어느 깊이에 있든 viewport 전체를 차지)
   ────────────────────────────────────────────── */
.m-shell {
    position: fixed; inset: 0;
    background: var(--m-bg);
    z-index: 9999;
    overflow: auto;
    display: flex; flex-direction: column;
    min-height: 100vh;
}
/* sticky footer 패턴: m-shell 직속 main 이 남는 공간을 차지해 footer 가 viewport 하단에 붙도록 */
.m-shell > main { flex: 1 0 auto; }
.m-shell > .m-footer { flex-shrink: 0; }
.m-container { width: 100%; max-width: var(--m-max-7xl); margin: 0 auto; padding: 0 20px; }
.m-center { display: grid; place-items: center; flex: 1; padding: 48px 16px; }

/* 메인+사이드 2-column 레이아웃 — 게시판 list, 메인, 추후 마이페이지 등에서 공통 사용 */
.m-with-sidebar {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 24px;
    align-items: start;
}
.m-main-col { min-width: 0; }   /* grid item 의 자식이 overflow 나지 않도록 */
.m-side-col { position: sticky; top: 80px; }
@media (max-width: 880px) {
    .m-with-sidebar { grid-template-columns: 1fr; }
    .m-side-col { position: static; order: -1; }   /* 모바일에선 사이드(outlogin)가 위로 */
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
   다크모드 토글 버튼 (.m-nav-actions 안에 JS 가 자동 주입)
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
</style>
<?php
// 위 <style> 블록을 ob 에서 꺼내 add_stylesheet 큐로 등록 (default.css 이후에 삽입).
$_modern_main_css = ob_get_clean();
add_stylesheet($_modern_main_css, 50);

// ──────────────────────────────────────────────
// 토글 버튼을 모든 .m-nav-actions 에 자동 주입 + 클릭 핸들러
// (각 페이지 스킨을 수정하지 않아도 됨)
// ──────────────────────────────────────────────
$_modern_toggle_js = <<<'JS'
<script>
document.addEventListener("DOMContentLoaded", function(){
    var html = ''
        + '<button type="button" class="m-theme-toggle" aria-label="테마 전환">'
        +   '<svg class="m-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>'
        +   '<svg class="m-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>'
        + '</button>';
    document.querySelectorAll(".m-nav-actions").forEach(function(nav){
        var tmp = document.createElement("div"); tmp.innerHTML = html;
        var btn = tmp.firstChild;
        btn.addEventListener("click", function(){
            var cur = document.documentElement.dataset.theme || "light";
            var next = cur === "dark" ? "light" : "dark";
            document.documentElement.dataset.theme = next;
            try { localStorage.setItem("m-theme", next); } catch(e) {}
        });
        nav.insertBefore(btn, nav.firstChild);
    });
});
</script>
JS;
add_javascript($_modern_toggle_js, 100);
