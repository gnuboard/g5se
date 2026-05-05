<?php
/*
 * 레거시 admin 페이지 패스스루 (visit 외).
 *
 * gnuboard adm/*.php 가 admin.head.php / admin.tail.php 로 자체 HTML 문서를
 * 그리는데, 이 헬퍼는:
 *   1) ob_start 로 출력 캡처
 *   2) admin.head 가 그린 <header>...<wrapper>...<container_wr> 안의 form 본문만 추출
 *   3) modern admin shell (admin_layout_start/end) 안에 .legacy-admin-content 로 wrap
 *      → input.css 의 컴포넌트 레이어가 .frm_input/.tbl_frm01/.btn 등을 모던 스타일로 매핑
 *
 * 호출 예 (admin/auth_list.php 에서):
 *   $legacy_target    = 'auth_list.php';
 *   $legacy_menu_key  = 'auth';        // (선택) 사이드바 active key
 *   $legacy_is_post   = true;          // (선택) POST 핸들러는 ob 없이 require
 */
if (!defined('_GNUBOARD_')) exit;

// referer 패치는 admin_require_login() 안에서 이미 처리됨 (_layout.php).
require_once G5_PATH.'/adm/admin.lib.php';

// goto_url 의 ./foo.php 또는 ./bar.php?... 를 G5_ADMIN_URL/foo, G5_ADMIN_URL/bar?... 로 변환
add_event('goto_url', function ($url) {
    $u = str_replace('&amp;', '&', (string)$url);
    if (preg_match('#^\.?/?([a-z][a-z0-9_]*)\.php(\?.*)?$#i', $u, $m)) {
        header('Location: '.G5_ADMIN_URL.'/'.$m[1].($m[2] ?? ''), true, 302);
        exit;
    }
}, 10, 1);

if (!empty($legacy_is_post)) {
    chdir(G5_PATH.'/adm');
    require G5_PATH.'/adm'.'/'.$legacy_target;
    return;
}

ob_start();
chdir(G5_PATH.'/adm');
require G5_PATH.'/adm'.'/'.$legacy_target;
$html = ob_get_clean();

$page_title = $g5['title'] ?? '관리자';

// 1) 본문 추출 — admin.head.php 가 `<div class="container_wr">` 으로 컨텐츠 영역을 열고
//    admin.tail.php 가 `<footer id="ft">` 직전에 닫는다.
//    추출 후 닫는 div 들도 제거.
$content = '';
if (preg_match('#<div class="container_wr">(.*?)<footer\s+id="ft"#si', $html, $m)) {
    $content = $m[1];
    // 끝의 닫는 </div> 들 제거 (container_wr / container / wrapper)
    $content = preg_replace('#(\s*</div>){2,4}\s*$#', '', $content);
} else {
    // 추출 실패 시 본문 폴백
    $content = $html;
}

// 2) form action / 링크의 ./foo.php → G5_ADMIN_URL/foo 일괄 변환
$content = preg_replace_callback(
    '#(href|action)="\./([a-z][a-z0-9_]*)\.php([^"]*)"#i',
    static fn($m) => $m[1].'="'.G5_ADMIN_URL.'/'.$m[2].$m[3].'"',
    $content
);

// 2.5) 레거시 페이지가 인라인 <style> 안에서 body/html/a 등 전역 셀렉터를 사용하면
//     admin shell 의 폰트/컬러를 글로벌로 덮어써서 'phpinfo 페이지에 들어가면
//     글자 크기/간격이 바뀌는' 점프가 발생. 전역 셀렉터를 wrapper 스코프로 재작성.
$content = preg_replace_callback(
    '#<style\b[^>]*>(.*?)</style>#si',
    static function ($m) {
        $css = $m[1];
        // 'html, body { ... }', 'body { ... }', 'a { ... }', 'a:hover { ... }' 같은
        // 단독 전역 셀렉터를 .legacy-admin-content 안으로 스코프.
        $scoped = preg_replace_callback(
            '#(^|\})\s*(html\s*,\s*body|body|html|a(?::[a-z-]+)?|table|pre|tr|td|th|h\d)\s*\{#i',
            static fn ($mm) => $mm[1].' .legacy-admin-content '.$mm[2].' {',
            $css
        );
        return '<style>'.$scoped.'</style>';
    },
    $content
);

// 3) form 의 self-action 보완 — action 이 없는 form 의 경우 클린 URL 주입
if (!empty($legacy_form_replace) && is_array($legacy_form_replace)) {
    foreach ($legacy_form_replace as $needle => $action) {
        $content = str_replace($needle, $needle.' action="'.$action.'"', $content);
    }
}

// 4) modern admin shell 안에 wrap
admin_layout_start($page_title, $legacy_menu_key ?? '');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
    <header class="flex items-center gap-3 mb-5">
        <h2 class="text-xl font-bold tracking-tight"><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h2>
        <span class="text-xs text-slate-400">레거시 페이지</span>
    </header>
    <div class="legacy-admin-content space-y-4 <?php echo !empty($legacy_force_light) ? 'force-light' : '' ?>">
        <?php echo $content ?>
    </div>
</main>
<script>
// 폼 submit 시 hidden token 자동 채움.
// (gnuboard admin.js 는 ajax 로 /adm/ajax.token.php 를 호출하지만 referer 가 /admin/ 이라
//  admin_referer_check 에서 막힘 → 그냥 페이지 렌더 시점에 생성된 admin_token 을 직접 주입)
(function () {
    var ADMIN_TOKEN = <?php echo json_encode(get_admin_token()) ?>;
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f || f.tagName !== 'FORM') return;
        var t = f.querySelector('input[name="token"]');
        if (t && !t.value) t.value = ADMIN_TOKEN;
    }, true);
})();
</script>
<script>
// 레거시 admin 이 사용하는 헬퍼 (gnuboard 의 admin.head 안 inline JS 정의분)
window.is_checked = window.is_checked || function (name) {
    var els = document.getElementsByName(name);
    for (var i = 0; i < els.length; i++) if (els[i].checked) return true;
    return false;
};
window.check_all = window.check_all || function (f) {
    var ck = f.chkall || f.querySelector('input[name="chkall"]');
    if (!ck) return;
    f.querySelectorAll('input[type="checkbox"][name="chk[]"]').forEach(function (i) { i.checked = ck.checked; });
};
// 팝업용 .btn_close (onclick=window.close()) 가 admin shell 안에서 직접 진입했을 때
// 동작하도록 history.back() 으로 대체.
document.addEventListener('click', function (e) {
    var b = e.target.closest('.legacy-admin-content .btn_close');
    if (!b) return;
    if (window.opener && !window.opener.closed) return; // 진짜 팝업이면 그대로 close
    e.preventDefault();
    if (history.length > 1) history.back();
    else location.href = <?php echo json_encode(G5_ADMIN_URL) ?>;
});

window.delete_confirm = window.delete_confirm || function (a) {
    return confirm('정말 삭제하시겠습니까?');
};
window.set_cookie = window.set_cookie || function (n, v, h, d) {
    var e = new Date(); e.setTime(e.getTime() + (h * 1000));
    document.cookie = n + '=' + escape(v) + '; expires=' + e.toUTCString() + '; path=/' + (d ? '; domain=' + d : '');
};
window.delete_cookie = window.delete_cookie || function (n) {
    document.cookie = n + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
};

// 섹션 anchor 네비게이션 — 클릭 / 스크롤 시 active 상태 표시
(function () {
    var anchors = document.querySelectorAll('.legacy-admin-content ul.anchor a[href^="#"]');
    if (!anchors.length) return;
    var byHash = {};
    anchors.forEach(function (a) {
        var hash = a.getAttribute('href');
        if (hash && hash.length > 1) {
            (byHash[hash] = byHash[hash] || []).push(a);
        }
    });
    function setActive(hash) {
        document.querySelectorAll('.legacy-admin-content ul.anchor a.active').forEach(function (a) { a.classList.remove('active'); });
        if (hash && byHash[hash]) byHash[hash].forEach(function (a) { a.classList.add('active'); });
    }
    // 1) 초기 hash 또는 첫 섹션 active
    var initialHash = location.hash && byHash[location.hash] ? location.hash : Object.keys(byHash)[0];
    setActive(initialHash);

    // 2) 클릭 시 active 갱신
    anchors.forEach(function (a) {
        a.addEventListener('click', function () { setActive(a.getAttribute('href')); });
    });

    // 3) 스크롤 시 viewport 안의 첫 section 을 active 로 (가벼운 scroll-spy)
    var sections = Array.from(document.querySelectorAll('.legacy-admin-content section[id]'));
    if (sections.length) {
        var observer = new IntersectionObserver(function (entries) {
            // 가장 위쪽에 보이는 섹션 선택
            var visible = entries.filter(function (e) { return e.isIntersecting; })
                                 .sort(function (a, b) { return a.boundingClientRect.top - b.boundingClientRect.top; });
            if (visible.length) setActive('#' + visible[0].target.id);
        }, { rootMargin: '-30% 0px -60% 0px', threshold: 0 });
        sections.forEach(function (s) { observer.observe(s); });
    }
})();

// SmartEditor2 / cheditor 의 iframe 내부는 별도 문서 — same-origin 이므로 JS 로
// style 태그를 주입해 라이트 톤 강제 (다크모드에서도 읽기 좋게).
(function () {
    var EDITOR_CSS = 'html,body{background:#fff !important;color:#1e293b !important}'
        + 'body{font-family:"Pretendard Variable","Pretendard",-apple-system,system-ui,sans-serif}'
        + 'a{color:#0369a1}';
    function injectCss(iframe) {
        try {
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            if (!doc || doc.__cssInjected) return;
            var s = doc.createElement('style');
            s.textContent = EDITOR_CSS;
            (doc.head || doc.documentElement).appendChild(s);
            doc.__cssInjected = true;
        } catch (e) { /* cross-origin 등 — 무시 */ }
    }
    function tryAll() {
        document.querySelectorAll('.legacy-admin-content iframe').forEach(function (f) {
            injectCss(f);
            f.addEventListener('load', function () { injectCss(f); }, { once: false });
        });
    }
    tryAll();
    // SE2 가 늦게 iframe 을 만드므로 몇 차례 재시도
    var n = 0; var t = setInterval(function () { tryAll(); if (++n > 10) clearInterval(t); }, 500);
})();

// 사이드뷰 (.sv_member / .sv_guest 클릭 시 같은 .sv_wrap 안의 .sv 토글)
document.addEventListener('click', function (e) {
    var trigger = e.target.closest('.legacy-admin-content .sv_member, .legacy-admin-content .sv_guest');
    if (trigger) {
        e.preventDefault();
        var wrap = trigger.closest('.sv_wrap');
        if (!wrap) return;
        var sv = wrap.querySelector('.sv');
        if (!sv) return;
        var wasOn = sv.classList.contains('sv_on');
        // 다른 열린 sv 닫기
        document.querySelectorAll('.legacy-admin-content .sv.sv_on').forEach(function (s) { s.classList.remove('sv_on'); });
        if (!wasOn) sv.classList.add('sv_on');
        return;
    }
    // 외부 클릭 시 닫기
    if (!e.target.closest('.legacy-admin-content .sv')) {
        document.querySelectorAll('.legacy-admin-content .sv.sv_on').forEach(function (s) { s.classList.remove('sv_on'); });
    }
});
</script>
<?php
admin_layout_end();
