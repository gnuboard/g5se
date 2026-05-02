<?php
// 모던 디자인 시스템 — 공통 head 주입
// 사용: 페이지 최상단에서 require_once(G5_THEME_PATH.'/modern/_head.inc.php');
// 그 다음 콘텐츠를 <div class="m-shell"> ... </div> 로 감싸면 됨.
// (m-shell 이 아닌 body 직속 형제는 모두 시각적으로 숨겨져 gnuboard chrome 이 가려짐)

if (!defined('_GNUBOARD_')) exit;
if (defined('_MODERN_HEAD_LOADED_')) return;
define('_MODERN_HEAD_LOADED_', true);

// CDN: 폰트 + UnoCSS reset + UnoCSS runtime
add_stylesheet('<link rel="preconnect" href="https://fonts.googleapis.com">', -10);
add_stylesheet('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>', -9);
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css">', -8);
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@unocss/reset/tailwind.min.css">', -7);
add_javascript('<script src="https://cdn.jsdelivr.net/npm/@unocss/runtime/uno.global.js"></script>', -1);

// 다크모드 FOUC 방지: localStorage / 시스템 설정으로 페인트 전에 data-theme 적용
add_javascript('<script>(function(){try{var t=localStorage.getItem("m-theme");if(!t)t=matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";document.documentElement.dataset.theme=t;}catch(e){}})();</script>', -100);
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
    font-family: 'Pretendard',-apple-system,BlinkMacSystemFont,system-ui,sans-serif;
    font-size: var(--m-text-md);  /* 기본 본문 사이즈 — 자식이 별도 지정 안 하면 14px */
    line-height: var(--m-leading);
    overflow: hidden;  /* m-shell 이 자체 스크롤 — body 스크롤바 중복 방지 */
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
.m-container { width: 100%; max-width: 1100px; margin: 0 auto; padding: 0 20px; }
.m-center { display: grid; place-items: center; flex: 1; padding: 48px 16px; }

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
    display: inline-block; box-sizing: border-box;
    padding: 11px 16px; border: 0; border-radius: var(--m-radius);
    font-size: var(--m-text-md); font-weight: 600;
    cursor: pointer; text-decoration: none; text-align: center;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
    font-family: inherit;
}
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
    padding: 14px 20px; max-width: 1100px; margin: 0 auto;
}
.m-brand {
    font-size: var(--m-text-xl); font-weight: 700; color: var(--m-text);
    text-decoration: none; letter-spacing: -0.01em;
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
</style>
<?php
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
