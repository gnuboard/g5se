<?php
/*
 * /admin/phpinfo — phpinfo() 단독 출력 (admin shell wrap 없음).
 */
$sub_menu = "100500";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if (function_exists('check_demo')) {
    check_demo();
}

auth_check_menu($auth, $sub_menu, 'r');

// phpinfo() 는 자체 완성 HTML 문서를 출력하며 viewport 메타가 없다.
// 모바일 브라우저가 980px 데스크톱 페이지로 축소하지 않도록 결과를 버퍼링한 뒤
// 반응형 메타와 표 전용 스타일을 head 에 주입한다.
ob_start();
phpinfo();
$phpinfo_html = (string) ob_get_clean();

$responsive_head = <<<'HTML'
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
html {
    width: 100%;
    min-width: 0;
    -webkit-text-size-adjust: 100%;
    color-scheme: light;
}
body {
    box-sizing: border-box;
    width: 100%;
    min-width: 0 !important;
    margin: 0;
    padding: 1rem;
    overflow-x: hidden;
    background: #f1f5f9 !important;
    color: #1e293b;
    font-family: -apple-system, BlinkMacSystemFont, "Apple SD Gothic Neo",
        "Malgun Gothic", "Noto Sans KR", system-ui, sans-serif !important;
}
.center {
    box-sizing: border-box;
    width: 100% !important;
    max-width: 72rem;
    margin: 0 auto;
}
.center table {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    margin: 0 0 1rem !important;
    border-collapse: separate !important;
    border-spacing: 0;
    table-layout: fixed;
    overflow: hidden;
    border: 1px solid #cbd5e1;
    border-radius: 0.625rem;
    background: #fff;
}
td, th {
    box-sizing: border-box;
    padding: 0.55rem 0.7rem !important;
    border-color: #cbd5e1 !important;
    color: #1e293b !important;
    font-family: inherit !important;
    font-size: 0.8125rem !important;
    line-height: 1.45;
    overflow-wrap: anywhere;
    word-break: break-word;
}
th {
    background: #dbeafe !important;
    color: #1e3a8a !important;
    font-weight: 700;
}
td.e {
    width: 32% !important;
    background: #f8fafc !important;
    color: #334155 !important;
    font-weight: 600;
}
td.v {
    width: auto !important;
    max-width: none !important;
    background: #fff !important;
}
td.h {
    background: #eff6ff !important;
}
h1 {
    margin: 0.75rem 0 1rem !important;
    font-family: inherit !important;
    font-size: 1.5rem !important;
    line-height: 1.3;
}
h2 {
    font-family: inherit !important;
    font-size: 1rem !important;
}
img {
    max-width: 100%;
    height: auto;
}
hr {
    box-sizing: border-box;
    width: 100% !important;
    max-width: 100% !important;
}
pre {
    margin: 0;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}
a {
    color: #1d4ed8 !important;
}

@media (max-width: 640px) {
    body {
        padding: 0.75rem;
    }
    .center {
        width: 100%;
    }
    .center table {
        margin-bottom: 0.75rem !important;
        border-radius: 0.5rem;
    }
    td, th {
        padding: 0.5rem !important;
        font-size: 0.875rem !important;
        line-height: 1.5;
    }
    td.e {
        width: 36% !important;
    }
    h1 {
        font-size: 1.125rem !important;
    }
}
</style>
HTML;

if (stripos($phpinfo_html, '</head>') !== false) {
    // phpinfo 기본 CSS 뒤에 배치해 반응형 규칙이 안정적으로 우선하도록 한다.
    $phpinfo_html = preg_replace('/<\/head>/i', $responsive_head.'</head>', $phpinfo_html, 1);
} else {
    $phpinfo_html = $responsive_head.$phpinfo_html;
}

echo $phpinfo_html;
