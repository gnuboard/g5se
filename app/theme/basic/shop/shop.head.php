<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$q = isset($_GET['q']) ? clean_xss_tags($_GET['q'], 1, 1) : '';

// g5se: 반응형 단일 마크업 정책 — G5_IS_MOBILE 분기 제거. 데스크탑 chrome + @media query 만 사용.
// (G5_THEME_MSHOP_PATH 의 mobile 전용 chrome 은 미사용)

include_once(G5_THEME_PATH.'/head.sub.php');
// modern 디자인 시스템 — UnoCSS runtime + Pretendard 폰트 + 토큰
require_once(G5_THEME_PATH.'/modern/_head.inc.php');

include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
include_once(G5_LIB_PATH.'/latest.lib.php');

add_javascript('<script src="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/owlcarousel/owl.carousel.css">', 0);
?>

<style>
/* item_list::run() 가 결과를 .m-shop-grid 로 감싸고 --m-list-cols 를 주입함.
   레거시 skin 의 fixed-width float 를 CSS Grid 로 강제 → 쇼핑몰설정의
   "1줄당 이미지 수" 가 실제 컬럼수에 반영되도록 함.
   - .owl-carousel  : 히트상품 (main.10) - JS slider 라 제외
   - .smt_30        : 사이드바용 thumb+텍스트 가로 리스트 (main.50) - 제외
   - .sct_ul        : 추천상품 (main.20) 은 <div class=smt_20><ul class=sct_ul> 처럼
                      한 단계 더 감싸므로 별도 selector 로 같은 grid 적용 */
/* 컬럼 전략:
   - cell 의 *최소* 폭은 설정된 --m-img-width (이미지 크기 보장)
   - 컨테이너에 cell 들이 들어가는 만큼 자동 컬럼 (auto-fit)
   - max-width 로 list_mod 컬럼 캡 적용 (4컬럼 설정인데 화면이 너무 넓어서
     5컬럼 잡히는 일을 방지) */
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl):not(.m-shop-sidebar-products),
.m-shop-grid > .smt_20 > ul.sct_ul {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(min(var(--m-img-width, 200px), 100%), 1fr));
    max-width: calc(var(--m-list-cols, 4) * var(--m-img-width, 200px) + (var(--m-list-cols, 4) - 1) * 16px);
    gap: 16px;
    margin: 0 auto !important;
    padding: 0;
    list-style: none;
}

/* 모바일은 별도의 "1줄당 이미지 수" 설정을 요구하지 않는다.
   뷰포트 기준 2열을 사용하고 300px 이하에서만 1열이 된다.
   관리자 ca_list_mod는 데스크톱 최대 열 수와 페이지당 상품 수에만 사용한다. */
@media (max-width: 880px) {
    .m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl):not(.m-shop-sidebar-products),
    .m-shop-grid > .smt_20 > ul.sct_ul {
        grid-template-columns: repeat(2, minmax(0, calc((100vw - 52px) / 2))) !important;
        width: calc(100vw - 40px) !important;
        max-width: calc(100vw - 40px) !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        gap: 12px;
    }
}
@media (max-width: 300px) {
    .m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl):not(.m-shop-sidebar-products),
    .m-shop-grid > .smt_20 > ul.sct_ul {
        grid-template-columns: minmax(0, 1fr);
    }
}
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl):not(.m-shop-sidebar-products) > li.sct_li,
.m-shop-grid > .smt_20 > ul.sct_ul > li.sct_li {
    float: none !important;
    width: auto !important;
    margin: 0 !important;
    border-bottom: 0 !important;
}
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl):not(.m-shop-sidebar-products) > li.sct_li .sct_img img,
.m-shop-grid > .smt_20 > ul.sct_ul > li.sct_li .sct_img img {
    width: 100%;
    height: auto;
    display: block;
}
/* 긴 상품명 (영문/숫자 연속) 이 셀 폭을 침범하는 문제.
   grid item 의 기본 min-width:auto 가 unbreakable string 에 막혀 cell 이 늘어남
   → min-width:0 + overflow-wrap:anywhere 로 강제 wrap. */
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl):not(.m-shop-sidebar-products) > li.sct_li,
.m-shop-grid > .smt_20 > ul.sct_ul > li.sct_li {
    min-width: 0;
}
.m-shop-grid .sct_txt,
.m-shop-grid .sct_txt a,
.m-shop-grid .sct_id,
.m-shop-grid .sct_id a {
    overflow-wrap: anywhere;
    word-break: break-word;
    min-width: 0;
}
/* 상품 카드 타이포그래피 — 모바일 2열과 데스크톱 다열 모두 읽기 쉬운 크기로 통일. */
.m-shop-grid .sct_star {
    font-size: 14px !important;
}
.m-shop-grid .sct_txt,
.m-shop-grid .sct_txt a {
    font-size: 16px !important;
    line-height: 1.4 !important;
}
.m-shop-grid .sct_basic {
    font-size: 14px !important;
    line-height: 1.45 !important;
}
.m-shop-grid .sct_cost > span:first-child {
    font-size: 18px !important;
    line-height: 1.35 !important;
}
.m-shop-grid .sct_cost .sct_dict {
    font-size: 13px !important;
}
/* modern product card */
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl):not(.m-shop-sidebar-products) > li.sct_li {
    position: relative;
    border: 1px solid var(--m-border) !important;
    border-radius: 14px !important;
    background: var(--m-surface) !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease !important;
}
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl):not(.m-shop-sidebar-products) > li.sct_li:hover {
    transform: translateY(-3px);
    border-color: var(--m-border-hover) !important;
    box-shadow: 0 12px 28px -16px rgba(15, 23, 42, 0.32) !important;
}
.m-shop-grid .sct_img {
    border-radius: 13px 13px 0 0;
}
.m-shop-grid .sct_img img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
}
.m-shop-grid .sct_ct_wrap {
    position: static;
    gap: 8px !important;
    padding: 16px !important;
}
.m-shop-grid .sct_star {
    line-height: 1;
    letter-spacing: 1px;
}
.m-shop-grid .sct_txt,
.m-shop-grid .sct_txt a {
    color: var(--m-text) !important;
    font-weight: 700 !important;
    letter-spacing: -0.02em;
}
.m-shop-grid .sct_basic {
    color: var(--m-text-muted) !important;
}
.m-shop-grid .sct_bottom {
    width: 100%;
    margin-top: auto !important;
    padding-top: 14px !important;
    border-top: 1px solid var(--m-border);
}
.m-shop-grid .sct_cost {
    gap: 1px;
}
.m-shop-grid .sct_cost > span:first-child {
    font-weight: 800 !important;
    letter-spacing: -0.025em;
}
.m-shop-grid .sct_cost .sct_dict {
    color: var(--m-text-faint) !important;
}
.m-shop-grid .sct_op_btn .btn_wish {
    width: 34px !important;
    height: 34px !important;
    border: 1px solid var(--m-border);
    background: var(--m-surface-2);
    color: var(--m-text-muted) !important;
}
.m-shop-grid .sct_op_btn .btn_wish:hover,
.m-shop-grid .sct_op_btn .btn_wish.is_active {
    border-color: rgba(244, 63, 94, .28);
    background: rgba(244, 63, 94, .1);
    color: #f43f5e !important;
}
/* 추천/할인/인기 배지는 내용 높이에 영향을 주지 않고 이미지 위에 표시한다. */
.m-shop-grid .sit_icon_li {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
    padding: 0 !important;
}
@media (max-width: 600px) {
    .m-shop-grid .sct_ct_wrap {padding: 12px !important}
    .m-shop-grid .sit_icon_li {top: 8px;left: 8px}
    .m-shop-grid .sct_bottom {padding-top: 12px !important}
}
/* 옛날 UX 정리:
   - .sctrl: main.20 의 ▶◾ (수직 롤링 효과재생/정지) — list_row>=2 + stacked-ul 일 때만
     의미 있는데 grid 레이아웃에선 작동 안함
   - .sct_sns / .sct_sns_wrap / .btn_share: 페이스북/트위터 공유 버튼 — UI 노이즈만 됨 */
.m-shop-grid .sctrl,
.m-shop-grid .sct_sns,
.m-shop-grid .sct_sns_wrap,
.m-shop-grid .btn_share {
    display: none !important;
}

/* ============================================================
   list 변형 overlay
   - sct_20         : 기본 grid overlay 자동. 추가 처리 없음.
   - sct_30         : 사이드텍스트 horizontal 카드. PHP 가 li 에 inline padding/width/height 를 박음.
   - sct_40         : 관련상품 또는 list.10 의 *리스트뷰* 토글 시 (shop.list.js 가 ul 클래스를 sct_40
                       로 바꾸고 li 에 inline padding-left:img_width+20 박음) 도 같은 사이드텍스트형.
   sct_30 + sct_40 둘 다 inline padding 무력화 후 grid 2-col 로 재배치 (이미지 좌 + 텍스트 우).
   ============================================================ */
/* sct_30: 다열 사이드텍스트 카드 (380px 단위로 auto-fit). list.30.skin.php 가 default.
   주의: base overlay (`.m-shop-grid > ul:not(...)`) 의 specificity 가 더 높아서 !important 필요. */
.m-shop-grid > ul.sct_30 {
    grid-template-columns: repeat(auto-fit, minmax(min(380px, 100%), 1fr)) !important;
}
/* sct_40: "한줄에 하나" — list.40.skin.php (관련상품) 의 default 동작이고,
   list.10 의 *리스트뷰* 토글 (shop.list.js 가 ul 클래스를 sct_40 로 갈아끼움) 도 동일한 의도. */
.m-shop-grid > ul.sct_40 {
    grid-template-columns: 1fr !important;
}
.m-shop-grid > ul.sct_30 > li.sct_li,
.m-shop-grid > ul.sct_40 > li.sct_li {
    padding: 12px !important;
    width: auto !important;
    height: auto !important;
    display: grid;
    grid-template-columns: var(--m-img-width, 200px) 1fr;
    gap: 16px;
    align-items: start;
}
.m-shop-grid > ul.sct_30 > li.sct_li > .sct_img,
.m-shop-grid > ul.sct_40 > li.sct_li > .sct_img {
    grid-column: 1;
    grid-row: 1 / span 6;
}
.m-shop-grid > ul.sct_30 > li.sct_li > .sct_id,
.m-shop-grid > ul.sct_30 > li.sct_li > .sct_txt,
.m-shop-grid > ul.sct_30 > li.sct_li > .sct_basic,
.m-shop-grid > ul.sct_30 > li.sct_li > .sct_cost,
.m-shop-grid > ul.sct_30 > li.sct_li > .sct_icon,
.m-shop-grid > ul.sct_40 > li.sct_li > .sct_ct_wrap {
    grid-column: 2;
}
.m-shop-grid > ul.sct_30 .sct_arw_toleft {
    display: none !important;
}

/* list 카드 다크 토큰화 — sct_10/20/30/40 공통 sct_li.
   light 모드는 legacy style.css 가 처리. 다크에서만 surface/text 토큰 덮어씀. */
[data-theme="dark"] .m-shop-grid > ul > li.sct_li {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
/* 홈 히트상품은 owl-carousel이 중간 wrapper를 추가하므로 직계 자식 규칙이 닿지 않는다. */
[data-theme="dark"] .m-shell .m-shop-grid .owl-carousel .sct_li {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] .m-shell .m-shop-grid .owl-carousel .sct_txt,
[data-theme="dark"] .m-shell .m-shop-grid .owl-carousel .sct_txt a,
[data-theme="dark"] .m-shell .m-shop-grid .owl-carousel .sct_cost,
[data-theme="dark"] .m-shell .m-shop-grid .owl-carousel .sct_cost > span {
    color: var(--m-text) !important;
}
[data-theme="dark"] .m-shell .m-shop-grid .owl-carousel .sct_img,
[data-theme="dark"] .m-shell .m-shop-grid .owl-carousel .sct_img a {
    background: var(--m-surface-2) !important;
}

/* 쇼핑 홈 이벤트 — legacy float 목록을 반응형 event/product card로 재구성 */
.m-shell #sev {
    margin: 48px 0 20px;
    text-align: left;
}
.m-shell #sev header {
    margin: 0 0 18px;
}
.m-shell #sev h2 {
    float: none;
    margin: 0;
    color: var(--m-text);
    font-size: var(--m-text-2xl);
    font-weight: 800;
    line-height: 1.3;
    letter-spacing: -0.025em;
}
.m-shell #sev > ul {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(520px, 100%), 1fr));
    gap: 20px;
    margin: 0;
    padding: 0;
    list-style: none;
}
.m-shell #sev .ev_li {
    float: none;
    width: auto;
    padding: 0;
}
.m-shell #sev .ev_li_wr {
    overflow: hidden;
    padding: 20px;
    border: 1px solid var(--m-border);
    border-radius: 16px;
    background: var(--m-surface);
    box-shadow: var(--m-shadow);
}
.m-shell #sev .sev_text {
    display: inline-flex;
    align-items: center;
    margin: 0 0 16px;
    color: var(--m-text);
    font-size: 18px;
    font-weight: 750;
    line-height: 1.4;
    text-decoration: none;
}
.m-shell #sev .sev_text:hover {
    color: var(--m-primary);
}
.m-shell #sev .ev_prd {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin: 0;
    padding: 0;
    border: 0;
    background: transparent;
    list-style: none;
    text-align: left;
}
.m-shell #sev .ev_prd > li {
    display: flex;
    min-width: 0;
    padding: 0 0 14px;
    overflow: hidden;
    flex-direction: column;
    border: 1px solid var(--m-border);
    border-radius: 12px;
    background: var(--m-surface-2);
}
.m-shell #sev .ev_prd_img {
    display: block;
    float: none;
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background: var(--m-bg);
}
.m-shell #sev .ev_prd_img img {
    display: block;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
}
.m-shell #sev .ev_txt_wr {
    float: none;
    max-width: none;
    margin: 0;
    padding: 12px 12px 0;
}
.m-shell #sev .ev_prd .ev_prd_tit {
    min-height: 2.8em;
    margin: 0 0 8px;
    color: var(--m-text);
    font-size: 14px;
    font-weight: 700;
    line-height: 1.4;
    overflow-wrap: anywhere;
    text-decoration: none;
}
.m-shell #sev .ev_prd .ev_prd_price {
    margin: 0;
    color: var(--m-text);
    font-size: 17px;
    font-weight: 800;
    line-height: 1.35;
}
.m-shell #sev .ev_prd > li:has(.sev_more) {
    grid-column: 1 / -1;
    display: block;
    padding: 0;
    border: 0;
    background: transparent;
}
.m-shell #sev .ev_prd .sev_more {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 42px;
    border: 1px solid var(--m-border);
    border-radius: 10px;
    background: var(--m-surface-2);
    color: var(--m-text-soft);
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
}
.m-shell #sev .ev_prd .sev_more:hover {
    border-color: var(--m-primary);
    background: var(--m-primary-soft);
    color: var(--m-primary);
}
@media (max-width: 640px) {
    .m-shell #sev {margin-top: 36px}
    .m-shell #sev h2 {font-size: 20px}
    .m-shell #sev .ev_li_wr {padding: 14px;border-radius: 14px}
    .m-shell #sev .sev_text {margin-bottom: 12px;font-size: 16px}
    .m-shell #sev .ev_prd {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
    }
    .m-shell #sev .ev_prd::-webkit-scrollbar {display:none}
    .m-shell #sev .ev_prd > li {
        flex: 0 0 150px;
        scroll-snap-align: start;
    }
    .m-shell #sev .ev_prd > li:has(.sev_more) {
        flex-basis: 84px;
        display: flex;
        align-items: stretch;
    }
    .m-shell #sev .ev_prd .sev_more {padding: 0 14px}
}
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_img,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_img a,
[data-theme="dark"] .m-shop-grid > .smt_20 > ul.sct_ul > li.sct_li .sct_img,
[data-theme="dark"] .m-shop-grid > .smt_20 > ul.sct_ul > li.sct_li .sct_img a {
    background: var(--m-surface-2) !important;
}
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_txt,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_txt a,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_basic,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_cost {
    color: var(--m-text) !important;
}
[data-theme="dark"] .m-shop-grid .sct_cost > span:first-child {
    color: var(--m-text) !important;
}
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_id,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_txt,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_basic,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_cost {
    background: transparent !important;
}
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_id,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_dict {
    color: var(--m-text-soft) !important;
}
/* sct_txt 의 가로선 (legacy #d9dde2) 다크에선 토큰. 위시/공유 아이콘 (#949494) 도. */
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_txt {
    border-bottom-color: var(--m-border) !important;
}
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_op_btn > button {
    color: var(--m-text-soft) !important;
}
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_op_btn > button:hover {
    color: var(--m-text) !important;
}

/* 빈 상품 영역 — 히트상품도 최신/추천상품처럼 배경 박스 없이 토큰 색상만 적용 */
.m-shell .m-shop-empty {
    display: block !important;
    min-height: 0;
    margin: 0;
    padding: 100px 0;
    border: 0;
    border-radius: 0;
    background: transparent;
    color: var(--m-text);
    font-size: inherit;
    text-align: center;
}
[data-theme="dark"] .m-shell .m-shop-empty {
    background: transparent !important;
    color: var(--m-text) !important;
}

/* 정렬바 (#sct_sortlst — 판매많은순/낮은가격순/...) + view toggle (#sct_lst — 리스트뷰/갤러리뷰).
   legacy style.css 가 #fff 배경 + #adadad 회색 글자 hardcode. 다크에선 토큰화. */
[data-theme="dark"] #sct_sortlst {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
}
[data-theme="dark"] #sct_sort li a {
    color: var(--m-text) !important;
    border-left-color: var(--m-border) !important;
}
[data-theme="dark"] #sct_sort li a:hover {
    color: var(--m-primary) !important;
}
[data-theme="dark"] #sct_lst button {
    background: var(--m-surface) !important;
    color: var(--m-text-soft) !important;
}
[data-theme="dark"] #sct_lst button:hover {
    color: var(--m-text) !important;
}

/* 관리자 빠른편집 톱니바퀴 (poll/visit skin 의 .btn_admin) — 사용자 화면 노이즈라 숨김.
   단, shop 의 .sct_admin (분류 관리 — list.php) / .sit_admin (상품 관리 — item.php) 안의
   .btn_admin 은 의미 있어서 예외 노출. */
.m-shell .btn_admin { display: none !important; }
.m-shell main.m-container { position: relative; }
/* sct_location (홈/네비/categorydropdown) + sct_admin/sit_admin (admin 톱니) 한 줄 정렬.
   - sct_location 은 style.css 가 absolute right:0 top:12px 로 띄움 (item view 에선 view_location → relative)
   - inline-flex + align-items:center 로 legacy vertical-align:top 무시하고 baseline 통일. */
.m-shell #sct_location {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}
.m-shell #sct_location .sct-location-path {
    display: contents;
}
/* admin 톱니가 h1 안 인라인으로 들어왔을 때 — 제목 뒤에 약간 떨어져 작은 카드로 */
.m-shell main.m-container > h1 > .sct_admin,
.m-shell main.m-container > h1 > .sit_admin {
    display: inline-block !important;
    margin: 0 0 0 12px !important;
    position: static !important;
    vertical-align: middle;
}
.m-shell .sct_admin .btn_admin,
.m-shell .sit_admin .btn_admin {
    display: inline-flex !important;
    align-items: center;
    padding: 0;
    background: transparent;
    color: var(--m-text-soft);
    border: 0;
    font-size: 0.6em;  /* h1 의 큰 font-size 를 상속하지 않게 축소 */
    font-weight: normal;
    line-height: 1.2;
    text-decoration: none;
}
.m-shell .sct_admin .btn_admin:hover,
.m-shell .sit_admin .btn_admin:hover {
    color: var(--m-primary);
    background: transparent;
}
/* 톱니 아이콘 회전 비활성화 — list.php / item.php 가 fa-spin hardcode (`<i class="fa fa-cog fa-spin">`) */
.m-shell .sct_admin .btn_admin .fa-spin,
.m-shell .sit_admin .btn_admin .fa-spin {
    animation: none !important;
    -webkit-animation: none !important;
}

/* 정렬바 (#sct_sortlst) 와 그 아래 상품 카드 사이 간격 — legacy style.css 가 margin 없이
   바로 붙임. 모던 시각적으로 분리. */
.m-shell #sct_sortlst {
    margin-bottom: 20px;
}

/* 위시리스트 버튼 (.btn_wish) — 클릭 시 shop.list.action.js 가 is_active 클래스 추가.
   채워진 하트 + primary 색으로 표시해 "담겼다" 시각 피드백.
   다크모드 conflict: 위쪽 [data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_op_btn > button
   룰이 specificity 0,5,3 으로 var(--m-text-soft) 강제 → 같은 chain depth 로 selector 박아 무력화. */
.m-shell .m-shop-grid > ul > li.sct_li .sct_op_btn > .btn_wish.is_active,
.m-shell .m-shop-grid > ul > li.sct_li .sct_op_btn > .btn_wish.is_active i.fa-heart {
    color: var(--m-primary) !important;
}
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_op_btn > .btn_wish.is_active,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_op_btn > .btn_wish.is_active i.fa-heart {
    color: var(--m-primary) !important;
}

/* 레거시 shop skin (style.css) 의 흰 배경 / 검정 텍스트 hardcode 를 다크모드에서 토큰으로 덮어씀 */
[data-theme="dark"] .smt_40 {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
}
[data-theme="dark"] .smt_30 li {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
}
[data-theme="dark"] .smt_30 .sct_txt a,
[data-theme="dark"] .smt_30 .sct_cost {
    color: var(--m-text) !important;
}

/* 홈 우측 인기상품 — main.50의 레거시 float/세로 슬라이더 대신 간결한 정적 목록 */
.m-shop-sidebar-products {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin: 0;
    padding: 0;
    list-style: none;
}
/* 상단 공통 상품 grid 규칙보다 구체적으로 지정해 72px max-width 상속을 차단한다. */
.m-shop-grid > ul.m-shop-sidebar-products {
    display: flex !important;
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
}
.m-shop-sidebar-products > li {
    display: grid;
    grid-template-columns: 72px minmax(0, 1fr);
    align-items: center;
    gap: 12px;
    min-height: 88px;
    padding: 8px;
    border-radius: 12px;
    background: transparent;
    transition: background-color .15s ease;
    width: 100%;
}
.m-shop-sidebar-products > li:hover {
    background: var(--m-surface-2);
}
.m-shop-sidebar-products .sct_img {
    float: none;
    width: 72px;
    height: 72px;
    margin: 0;
    overflow: hidden;
    border-radius: 10px;
    background: var(--m-bg);
}
.m-shop-sidebar-products .sct_img a,
.m-shop-sidebar-products .sct_img img {
    display: block;
    width: 100% !important;
    height: 100% !important;
}
.m-shop-sidebar-products .sct_img img {
    object-fit: cover;
}
.m-shop-sidebar-products .sct_cnt {
    display: flex;
    float: none;
    min-width: 0;
    max-width: none;
    flex-direction: column;
    gap: 4px;
    line-height: 1.35;
}
.m-shop-sidebar-products .sit_star {
    width: 72px;
    height: auto;
}
.m-shop-sidebar-products .sct_txt,
.m-shop-sidebar-products .sct_txt a {
    min-width: 0;
    color: var(--m-text) !important;
    font-size: 14px !important;
    font-weight: 700;
    line-height: 1.4 !important;
}
.m-shop-sidebar-products .sct_txt a {
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    text-decoration: none;
}
.m-shop-sidebar-products .sct_cost {
    color: var(--m-text) !important;
    font-size: 15px !important;
    font-weight: 800;
    line-height: 1.3;
    white-space: nowrap;
}
/* 카테고리 박스: 외곽은 .m-card 가 그리므로 안쪽 #gnb 의 좌/우/하단 border 제거
   (원본 style.css 가 border-top:0 만 빼서 위만 비어 보이는 어색한 상태였음).
   light/dark 양쪽 다 적용. */
#gnb {
    border: 0 !important;
    margin-bottom: 0 !important;
    background: transparent !important;
}
#gnb .gnb_1da {
    color: var(--m-text) !important;
}
#gnb .gnb_2da {
    color: var(--m-text-soft) !important;
    /* default.css 의 background:#fff 박힌 거 무력화 — 부모 .gnb_2dul 의 surface 색이 비치도록 */
    background: transparent !important;
}
#gnb .gnb_1dli_on .gnb_1da {
    background-color: var(--m-surface-2) !important;
    color: var(--m-primary) !important;
}
#gnb .gnb_1dli_on .gnb_2da {
    color: var(--m-text) !important;
}
#gnb .gnb_2da:focus,
#gnb .gnb_2da:hover {
    color: var(--m-primary) !important;
    /* default.css 의 hover background:#f7f7f8 도 무력화 — 테마 surface-2 사용 */
    background: var(--m-surface-2) !important;
}
[data-theme="dark"] #gnb {
    background: transparent !important;
}
[data-theme="dark"] .gnb_1da {
    color: var(--m-text) !important;
}
[data-theme="dark"] .gnb_1dli_on .gnb_1da {
    background-color: var(--m-surface-2) !important;
    color: var(--m-primary) !important;
}
[data-theme="dark"] .gnb_1dli_over .gnb_2dul,
[data-theme="dark"] .gnb_1dli_over2 .gnb_2dul {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
}

/* sct_location 의 카테고리 dropdown (item view 의 브레드크럼). JS 가 native <select>
   를 .shop_select_to_html > .menulist > .option 구조로 변환. style.css 가 .menulist 의
   background:#fff, hover #f6f7f9, 홈/> 아이콘 color:#a2a2a2 하드코딩 — 다크모드에서
   글자 안 보임. 다크 토큰으로 오버라이드. */
[data-theme="dark"] #sct_location .go_home,
[data-theme="dark"] #sct_location i.dividing-line {
    color: var(--m-text-soft) !important;
}
/* 닫힌 상태의 트리거 (.category_title) + 그 안의 ▼ 아이콘 — JS 가 .category_title.current
   안에 텍스트 + <i class="fa fa-chevron-circle-down"> 를 박음. 기본 색이 inherit 라
   다크모드 부모 색에 따라 흐려짐. 명시 토큰 적용. */
[data-theme="dark"] .shop_select_to_html .category_title,
[data-theme="dark"] .shop_select_to_html .category_title i {
    color: var(--m-text) !important;
}
[data-theme="dark"] .shop_select_to_html .menulist {
    background-color: var(--m-surface) !important;
    box-shadow: 0 0 0 1px var(--m-border) !important;
}
[data-theme="dark"] .shop_select_to_html .menulist ul.left-border {
    border-left-color: var(--m-border) !important;
}
[data-theme="dark"] .shop_select_to_html .option,
[data-theme="dark"] .shop_select_to_html .option a {
    color: var(--m-text) !important;
}
[data-theme="dark"] .shop_select_to_html .option:hover,
[data-theme="dark"] .shop_select_to_html .option.focus,
[data-theme="dark"] .shop_select_to_html .option.selected.focus {
    background-color: var(--m-surface-2) !important;
}

/* 카테고리 list 페이지 상단의 하위 카테 네비게이션 (.sct_ct = aside, #sct_ct_1 li a).
   style.css 가 background:#fff, border-top:2px solid #000, li 구분자 #f6f6f6 하드코딩
   → 다크에서 흰 박스 + 흐린 글자. 다크 토큰 적용. */
[data-theme="dark"] .sct_ct {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    border-top-color: var(--m-text-soft) !important;
}
[data-theme="dark"] .sct_ct a {
    color: var(--m-text) !important;
}
[data-theme="dark"] .sct_ct a:hover {
    color: var(--m-primary) !important;
}
[data-theme="dark"] #sct_ct_1 li {
    border-right-color: var(--m-border) !important;
}
[data-theme="dark"] .sct_ct_here {
    color: var(--m-primary) !important;  /* 원본 #ff3600 도 다크에서 보이지만 테마와 일관 */
}

/* 상품 분류 목록 모바일 정리 — legacy absolute breadcrumb와 20% float 열을 해제한다. */
@media (max-width: 880px) {
    /* 현재 분류는 상단 통합 카테고리 바가 표시한다. h1은 접근성용으로만 유지한다. */
    .m-shell main.m-container > h1.m-shop-category-page-title {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        margin: -1px !important;
        padding: 0 !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
    .m-shell main.m-container > h1 {
        font-size: var(--m-text-xl) !important;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }
    /* 1단계 분류는 상단 가로 메뉴에서 이미 제공하므로 중복 경로 UI를 숨긴다. */
    .m-shell #sct_location {
        display: none !important;
    }
    .m-shell #sct_ct_1 {
        display: none;
    }

    .m-shell #sct_sortlst {
        display: flex;
        align-items: stretch;
        margin: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        overflow: visible;
    }
    /* 이벤트 페이지는 목록 툴바 wrapper가 없으므로 정렬 셀렉트 자체에 간격을 둔다. */
    .m-shell #sct_sortlst.m-event-sort {
        margin-bottom: 16px;
    }
    .m-shell #sct_sort {
        float: none;
        width: auto;
        min-width: 0;
        flex: 1;
    }
    .m-shell #sct_sort ul {
        display: none;
    }
    .m-shell #sct_sort #sct-sort-mobile {
        display: block;
        width: 100%;
        height: 36px;
        margin: 0;
        padding: 0 28px 0 10px;
        border: 1px solid var(--m-border);
        border-radius: var(--m-radius);
        background-color: var(--m-surface-2);
        color: var(--m-text);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        color-scheme: light dark;
    }
    .m-shell #sct_sort #sct-sort-mobile:focus {
        border-color: var(--m-primary);
        outline: 3px solid var(--m-primary-soft);
    }
    .m-shell #sct_lst {
        display: none;
    }
    .m-shell .m-shop-list-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 6px 0 12px;
    }
    .m-shell .m-shop-list-count {
        flex: 0 0 auto;
        color: var(--m-text-muted);
        font-size: var(--m-text-sm);
        white-space: nowrap;
    }
    .m-shell .m-shop-list-count strong {
        color: var(--m-text);
        font-size: var(--m-text-md);
    }
    .m-shell .m-shop-list-toolbar #sct_sortlst {
        width: min(156px, 46vw);
    }
}

.m-shell .m-shop-list-count {
    display: none;
}
@media (max-width: 880px) {
    .m-shell .m-shop-list-count {
        display: inline;
    }
}

/* 상품 정렬 셀렉트는 모바일 전용이다. */
@media (min-width: 881px) {
    .m-shell #sct_sort #sct-sort-mobile {
        display: none !important;
    }
}

/* 설문조사 (poll) — shop_basic skin */
[data-theme="dark"] #poll {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
}
[data-theme="dark"] #poll header h2,
[data-theme="dark"] #poll .poll_con p {
    color: var(--m-text) !important;
}
[data-theme="dark"] #poll header .btn_result {
    background: var(--m-surface-2) !important;
    border-color: var(--m-border) !important;
}
[data-theme="dark"] .chk_box input[type="radio"] + label {
    color: var(--m-text-soft) !important;
}
[data-theme="dark"] .chk_box input[type="radio"] + label span {
    background: var(--m-surface-2) !important;
    border-color: var(--m-border) !important;
}

/* item.php 다크모드 — sit_ov_from / sit_pvi / sit_ov / sit_opt_added / sit_btn_*
   / sit_siblings 등 메인 영역은 legacy style.css 가 var(--m-*) 토큰 사용 (모드 자동).
   여기 남은 부분은 #sit_tab (하단 탭 영역, 사용후기 / 상품문의) + 일부 hardcode 박스. */
[data-theme="dark"] #sit_ov_from,
[data-theme="dark"] #sit_ov_wrap,
[data-theme="dark"] #sit_ov,
[data-theme="dark"] #sit_siblings,
[data-theme="dark"] #sit_buy,
[data-theme="dark"] .sit_buy_inner {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] #sit_title,
[data-theme="dark"] #sit_tot_price,
[data-theme="dark"] #sit_tot_price strong,
[data-theme="dark"] #sit_tot_price span,
[data-theme="dark"] .sit_tot_price,
[data-theme="dark"] .sit_tot_price strong,
[data-theme="dark"] .sit_tot_price span {
    color: var(--m-text) !important;
}
[data-theme="dark"] .sit_info,
[data-theme="dark"] .sit_info .tr_price,
[data-theme="dark"] .sit_ov_tbl,
[data-theme="dark"] .sit_ov_tbl th,
[data-theme="dark"] .sit_ov_tbl td,
[data-theme="dark"] .sit_ov_tbl td strong {
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] #sit_star_sns span,
[data-theme="dark"] #sit_desc,
[data-theme="dark"] .sit_option label,
[data-theme="dark"] .sit_side_option label {
    color: var(--m-text-soft) !important;
}
[data-theme="dark"] #sit_opt_added li,
[data-theme="dark"] .sit_sel_option li {
    background: var(--m-surface-2) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] #sit_opt_added .opt_name,
[data-theme="dark"] #sit_opt_added .sit_opt_prc,
[data-theme="dark"] .sit_sel_option .opt_name,
[data-theme="dark"] .sit_sel_option .sit_opt_prc {
    color: var(--m-text) !important;
    overflow-wrap: anywhere;
    word-break: break-word;
}
[data-theme="dark"] #sit_opt_added button,
[data-theme="dark"] .sit_sel_option button {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] #sit_opt_added .num_input,
[data-theme="dark"] .sit_sel_option .num_input {
    background: var(--m-surface-2) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] .sit_btn_cart {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] .sit_btn_cart:hover {
    background: var(--m-surface-2) !important;
}
[data-theme="dark"] #sit_info,
[data-theme="dark"] #sit_tab .tab_tit,
[data-theme="dark"] #sit_tab .tab_tit li button,
[data-theme="dark"] #sit_tab .tab_con,
[data-theme="dark"] #sit_rel,
[data-theme="dark"] #sit_inf_open td {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text);
}
[data-theme="dark"] .sit_use_top,
[data-theme="dark"] #sit_use_wbtn,
[data-theme="dark"] #sit_qa_wbtn {
    background: var(--m-surface-2) !important;
    border-color: var(--m-border) !important;
}
[data-theme="dark"] #sit_use_wbtn a.itemuse_list,
[data-theme="dark"] #sit_qa_wbtn a#itemqa_list {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] #sit_inf_open th,
[data-theme="dark"] #sit_sms_new .prd_name,
[data-theme="dark"] #sit_tab .item_use_count,
[data-theme="dark"] #sit_tab .item_qa_count {
    background: var(--m-surface-2) !important;
    color: var(--m-text-soft) !important;
    border-color: var(--m-border) !important;
}
[data-theme="dark"] #sit_tab .tab_tit li .selected {
    background: var(--m-surface) !important;
    color: var(--m-primary) !important;
    border-bottom-color: var(--m-surface) !important;
}
[data-theme="dark"] #sit_tab .tab_tit li button {
    color: var(--m-text-soft) !important;
}
[data-theme="dark"] #sit_ov_soldout {
    background: rgba(239, 68, 68, 0.1) !important;
}
[data-theme="dark"] #sit_star_sns .sns_area {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
}
[data-theme="dark"] #sit_star_sns .sns_area #sit_btn_rec {
    background: var(--m-surface-2) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] #sit_star_sns .btn_sns_share,
[data-theme="dark"] #btn_wish {
    color: var(--m-text-soft) !important;
}
/* 상품 옵션 select (사이즈 등) — native <select>. 다크에선 select 자체 + 브라우저 dropdown
   둘 다 토큰. color-scheme:dark 힌트로 브라우저가 dropdown 옵션 리스트도 다크 팔레트로 그림. */
[data-theme="dark"] .sit_option select {
    background: var(--m-surface) !important;
    color: var(--m-text) !important;
    border-color: var(--m-border) !important;
    color-scheme: dark;
}
[data-theme="dark"] .sit_option select option {
    background: var(--m-surface);
    color: var(--m-text);
}

/* ============================================================
   주문조회 상세 (#sod_fin — orderinquiryview.php)
   - light/dark 공통 폴리시: 카드형 + 반응형 sod_left/sod_right 1열
   - dark: tbl_head01/03 + sod_sts_explan + sod_fin_legend 토큰화
   ============================================================ */
.m-shell #sod_fin {
    margin: 8px 0 32px;
}
.m-shell #sod_fin_no {
    font-size: 1.1em;
    margin-bottom: 16px;
}
.m-shell #sod_fin_no strong {
    color: var(--m-primary);
}
/* sod_left/sod_right — legacy fixed 840px float. 반응형 grid 로 재배치 */
.m-shell #sod_fin .sod_left,
.m-shell #sod_fin .sod_right {
    float: none !important;
    width: auto !important;
    margin: 0 !important;
    display: block !important;
}
.m-shell #sod_fin section,
.m-shell #sod_fin > .sod_left > section {
    margin-bottom: 24px;
}
@media (min-width: 980px) {
    .m-shell #sod_fin {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 24px;
        align-items: start;
    }
    .m-shell #sod_fin > #sod_fin_no,
    .m-shell #sod_fin > #sod_fin_list {
        grid-column: 1 / -1;
    }
}
.m-shell #sod_fin .tbl_wrap {
    overflow-x: auto;
}

/* 상품목록 (#sod_fin_list .tbl_head03) — 주문서 (#forderform #sod_list) 와 같은 카드형 톤. light/dark 공통 */
.m-shell #sod_fin_list .tbl_head03 {
    overflow-x: auto;
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius, 8px);
    background: var(--m-surface);
    box-shadow: var(--m-shadow);
    margin-bottom: 20px;
}
.m-shell #sod_fin_list .tbl_head03 table {
    width: 100%;
    border: 0 !important;
    border-collapse: collapse;
    margin: 0 !important;
}
.m-shell #sod_fin_list .tbl_head03 thead th {
    padding: 12px 14px !important;
    background: var(--m-surface-2) !important;
    border-top: 0 !important;
    border-bottom: 1px solid var(--m-border) !important;
    color: var(--m-text) !important;
    font-weight: 700;
    text-align: center;
    letter-spacing: 0;
}
.m-shell #sod_fin_list .tbl_head03 td {
    padding: 14px 12px !important;
    background: var(--m-surface) !important;
    border-top: 1px solid var(--m-border) !important;
    border-left: 0 !important;
    border-bottom: 0 !important;
    color: var(--m-text) !important;
    vertical-align: middle;
}
.m-shell #sod_fin_list .tbl_head03 a {
    color: var(--m-text);
    text-decoration: none;
}
.m-shell #sod_fin_list .tbl_head03 a:hover {
    color: var(--m-primary);
}
.m-shell #sod_fin_list .tbl_head03 .td_imgsmall {
    width: 100px !important;
    min-width: 100px;
    max-width: 100px;
    padding: 14px 8px !important;
    box-sizing: border-box;
    text-align: center !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_imgsmall img {
    width: 80px !important;
    max-width: 80px !important;
    height: 80px !important;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid var(--m-border);
    background: var(--m-surface-2);
    display: block !important;
    margin: 0 auto !important;
    position: static !important;
    top: auto !important;
    left: auto !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_bdno {
    border-bottom: 0 !important;
    font-weight: 700;
}
/* (legacy 마크업용 옵션 indent 룰 — theme 마크업 (.td_prd) 과는 무관) */
.m-shell #sod_fin_list .tbl_head03 td[headers="th_itopt"]:not(.td_prd) {
    padding-left: 32px !important;
    color: var(--m-text-soft) !important;
    font-weight: normal !important;
    text-align: left !important;
}
/* theme 마크업의 sod_opt 들여쓰기 (cart 의 m-cart-options 톤) */
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_opt {
    padding-left: 14px !important;
    color: var(--m-text-soft) !important;
    font-size: 0.92em !important;
    margin-top: 4px !important;
}
/* legacy "옵션" pill (default_shop.css:1154) 제거 */
.m-shell #sod_fin_list .sod_name .sod_opt:before {
    content: none !important;
    display: none !important;
    background: transparent !important;
    padding: 0 !important;
    margin: 0 !important;
}

/* 주문번호 박스 (#sod_fin_no) — 카드형 surface */
.m-shell #sod_fin #sod_fin_no {
    padding: 16px 20px !important;
    background: var(--m-surface) !important;
    border: 1px solid var(--m-border) !important;
    border-radius: var(--m-radius, 8px);
    color: var(--m-text);
    font-size: 1em;
    margin-bottom: 16px;
}
.m-shell #sod_fin #sod_fin_no strong {
    color: var(--m-primary);
    font-size: 1.05em;
    margin-left: 4px;
}

/* 결제정보 박스 (#sod_fin_pay) — light/dark 공통 토큰 (legacy #fff hardcode 무력화) */
.m-shell #sod_fin_pay {
    background: var(--m-surface) !important;
    border: 1px solid var(--m-border) !important;
    border-radius: var(--m-radius, 8px);
    color: var(--m-text);
    margin-bottom: 20px !important;
    overflow: hidden;
}
.m-shell #sod_fin_pay h3 {
    background: var(--m-surface-2) !important;
    border-bottom: 1px solid var(--m-border) !important;
    color: var(--m-text);
}
.m-shell #sod_fin_pay ul {
    margin: 0 !important;
    padding: 12px 20px !important;
}
.m-shell #sod_fin_pay li {
    display: block !important;
    color: var(--m-text);
    padding: 10px 0;
    border-bottom: 1px dashed var(--m-border);
    list-style: none;
}
.m-shell #sod_fin_pay li:last-child {
    border-bottom: 0;
}
.m-shell #sod_fin_pay li > strong {
    display: block !important;
    float: none !important;
    width: auto !important;
    margin: 0 0 4px !important;
    color: var(--m-text-soft);
    font-weight: 600;
    font-size: 0.92em;
}
.m-shell #sod_fin_pay li > span {
    display: block !important;
    float: none !important;
    width: auto !important;
    padding-left: 14px !important;
    color: var(--m-text);
    font-weight: 500;
}

/* 우측 총계 박스 (#sod_bsk_tot.order_view_infos) — 카드형 + 토큰 */
.m-shell #sod_fin #sod_bsk_tot.order_view_infos {
    background: var(--m-surface) !important;
    border: 1px solid var(--m-border) !important;
    border-radius: var(--m-radius, 8px);
    color: var(--m-text);
    overflow: hidden;
    margin-bottom: 20px !important;
}
.m-shell #sod_fin #sod_bsk_tot.order_view_infos li {
    background: transparent !important;
    border-bottom: 1px solid var(--m-border) !important;
    border-left: 0 !important;
    color: var(--m-text) !important;
    width: auto !important;
    float: none !important;
    display: flex;
    justify-content: space-between;
    padding: 12px 16px !important;
}
.m-shell #sod_fin #sod_bsk_tot.order_view_infos li:last-child {
    border-bottom: 0 !important;
}

/* 주문 취소하기 (.btn_cancel) — 다크 친화 토큰 */
.m-shell #sod_fin .btn_cancel,
.m-shell .btn_cancel {
    background: var(--m-surface-2) !important;
    color: var(--m-text) !important;
    border: 1px solid var(--m-border) !important;
}
.m-shell #sod_fin .btn_cancel:hover,
.m-shell .btn_cancel:hover {
    border-color: var(--m-primary) !important;
    color: var(--m-primary) !important;
}

/* 주문상세 주문취소 레이어 */
.m-shell #sod_cancel_pop {
    position: fixed !important;
    inset: 0 !important;
    z-index: 10000 !important;
}
.m-shell #sod_cancel_pop .sod_fin_bg {
    position: fixed !important;
    inset: 0 !important;
    background: rgba(15, 23, 42, .58) !important;
    backdrop-filter: blur(2px);
}
.m-shell #sod_fin_cancelfrm {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    z-index: 1 !important;
    transform: translate(-50%, -50%) !important;
    width: min(360px, calc(100vw - 32px)) !important;
    max-height: calc(100vh - 48px) !important;
    margin: 0 !important;
    overflow: auto !important;
    border: 1px solid var(--m-border) !important;
    border-radius: var(--m-radius-lg) !important;
    background: var(--m-surface) !important;
    color: var(--m-text) !important;
    box-shadow: 0 24px 70px rgba(15,23,42,.32) !important;
}
.m-shell #sod_fin_cancelfrm h2 {
    position: static !important;
    display: block !important;
    margin: 0 !important;
    padding: 16px 52px 16px 20px !important;
    border-bottom: 1px solid var(--m-border) !important;
    background: color-mix(in srgb, var(--m-surface) 92%, var(--m-primary) 8%) !important;
    color: var(--m-text) !important;
    font-size: var(--m-text-lg) !important;
    font-weight: 750 !important;
    line-height: 1.35 !important;
    text-align: left !important;
}
.m-shell #sod_fin_cancelfrm form {
    padding: 24px 30px 30px !important;
}
.m-shell #sod_fin_cancelfrm .frm_input {
    width: 100% !important;
    height: 48px !important;
    margin: 0 0 8px !important;
    border: 1px solid var(--m-border) !important;
    border-radius: var(--m-radius) !important;
    background: var(--m-surface-2) !important;
    color: var(--m-text) !important;
}
.m-shell #sod_fin_cancelfrm .frm_input::placeholder {
    color: var(--m-text-faint) !important;
}
.m-shell #sod_fin_cancelfrm .frm_input:focus {
    border-color: var(--m-primary) !important;
    box-shadow: 0 0 0 3px var(--m-primary-soft) !important;
    outline: none !important;
}
.m-shell #sod_fin_cancelfrm .btn_frmline {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    height: 48px !important;
    padding: 0 16px !important;
    border: 1px solid var(--m-primary) !important;
    border-radius: var(--m-radius) !important;
    background: var(--m-primary) !important;
    color: #fff !important;
    font-weight: 700 !important;
    cursor: pointer !important;
}
.m-shell #sod_fin_cancelfrm .sod_cls_btn {
    position: absolute !important;
    top: 10px !important;
    right: 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 32px !important;
    height: 32px !important;
    padding: 0 !important;
    border: 1px solid var(--m-border) !important;
    border-radius: var(--m-radius) !important;
    background: transparent !important;
    color: var(--m-text-soft) !important;
    font-size: 16px !important;
    cursor: pointer !important;
}
.m-shell #sod_fin_cancelfrm .sod_cls_btn:hover {
    background: var(--m-surface-2) !important;
    color: var(--m-text) !important;
}

/* 다크 — tbl_head01 (orderer/receiver/payment/dvr) — 상세 정보 테이블 */
[data-theme="dark"] #sod_fin .tbl_head01 thead th {
    background: var(--m-surface-2) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] #sod_fin .tbl_head01 td,
[data-theme="dark"] #sod_fin .tbl_head01 th {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] #sod_fin .tbl_head01 a {
    color: var(--m-text) !important;
}

/* 다크 — 상태설명 */
[data-theme="dark"] #sod_fin #sod_sts_explan,
[data-theme="dark"] #sod_fin #sod_fin_legend {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] #sod_fin .btn_frmline {
    background: var(--m-surface-2) !important;
    color: var(--m-text) !important;
    border-color: var(--m-border) !important;
}

/* 다크 — 우측 총계 (#sod_bsk_tot.order_view_infos) */
[data-theme="dark"] #sod_fin #sod_bsk_tot,
[data-theme="dark"] #sod_fin #sod_bsk_tot li {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
    color: var(--m-text) !important;
}

/* 주문조회 상세 - 상품목록/요약/정보 UI 보정 */
.m-shell #sod_fin_list .tbl_head03 {
    overflow: hidden;
}
.m-shell #sod_fin_list .tbl_head03 table {
    min-width: 0;
    width: 100%;
    table-layout: fixed;
}
.m-shell #sod_fin_list .tbl_head03 #th_itname { width: auto; }
.m-shell #sod_fin_list .tbl_head03 #th_itqty { width: 74px; }
.m-shell #sod_fin_list .tbl_head03 #th_itprice,
.m-shell #sod_fin_list .tbl_head03 #th_itpt,
.m-shell #sod_fin_list .tbl_head03 #th_itsd,
.m-shell #sod_fin_list .tbl_head03 #th_itst { width: 88px; }
.m-shell #sod_fin_list .tbl_head03 #th_itsum { width: 96px; }
.m-shell #sod_fin_list .tbl_head03 thead th {
    height: 58px;
    padding: 0 14px !important;
    font-size: 17px;
    font-weight: 800;
}
.m-shell #sod_fin_list .tbl_head03 tbody td {
    height: 118px;
    padding: 22px 14px !important;
    border-top: 1px solid var(--m-border) !important;
    font-size: 17px;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
}
.m-shell #sod_fin_list .tbl_head03 tbody tr:first-child td {
    border-top: 0 !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd {
    position: relative;
    min-height: 118px;
    padding-left: 136px !important;
    text-align: left;
    white-space: normal;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_img {
    position: absolute;
    top: 22px;
    left: 22px;
    width: 96px;
    height: 96px;
    overflow: hidden;
    border-radius: var(--m-radius-sm, 6px);
    background: var(--m-surface-2);
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_name {
    min-height: 96px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    color: var(--m-text);
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_name a {
    color: var(--m-text);
    font-size: 17px;
    font-weight: 800;
    line-height: 1.35;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_opt {
    margin: 0;
    color: var(--m-text);
    font-size: 16px;
    line-height: 1.5;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_opt:before {
    display: none !important;
    content: none !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_numbig,
.m-shell #sod_fin_list .tbl_head03 #th_itsum ~ td,
.m-shell #sod_fin_list .tbl_head03 td[headers="th_itsum"] {
    font-weight: 400;
}
.m-shell #sod_fin_list .tbl_head03 td[headers="th_itsum"] {
    font-weight: 900;
}
.m-shell #sod_sts_wrap {
    display: flex;
    justify-content: flex-end;
    margin: 18px 0 28px;
}
.m-shell #sod_sts_wrap .btn_frmline {
    min-width: 150px;
    height: 44px;
    border-radius: var(--m-radius-sm, 6px);
    font-weight: 700;
}
.m-shell #sod_sts_explan {
    position: absolute;
    right: 0;
    z-index: 20;
    max-width: min(520px, calc(100vw - 32px));
    padding: 16px;
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius, 8px);
    background: var(--m-surface);
    box-shadow: var(--m-shadow-md);
}
.m-shell #sod_fin #sod_bsk_tot2 {
    margin: 0 0 20px;
    padding: 0;
    overflow: hidden;
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius, 8px);
    background: var(--m-surface);
    color: var(--m-text);
    list-style: none;
}
.m-shell #sod_fin #sod_bsk_tot2 li {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 13px 16px;
    border-bottom: 1px solid var(--m-border);
    background: var(--m-surface);
    color: var(--m-text);
}
.m-shell #sod_fin #sod_bsk_tot2 li:last-child {
    border-bottom: 0;
}
.m-shell #sod_fin #sod_bsk_tot2 span {
    color: var(--m-text-soft);
    font-weight: 700;
}
.m-shell #sod_fin #sod_bsk_tot2 strong {
    color: var(--m-text);
    font-weight: 900;
    text-align: right;
}
.m-shell #sod_fin #sod_bsk_tot2 .sod_fin_tot {
    background: var(--m-surface-2);
}
.m-shell #sod_fin #sod_bsk_tot2 .sod_bsk_cnt strong,
.m-shell #sod_fin #sod_bsk_tot2 .sod_fin_tot strong {
    color: var(--m-primary);
}
.m-shell #sod_fin_orderer,
.m-shell #sod_fin_receiver,
.m-shell #sod_fin_dvr {
    overflow: hidden;
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius, 8px);
    background: var(--m-surface);
}
.m-shell #sod_fin_orderer h3,
.m-shell #sod_fin_receiver h3,
.m-shell #sod_fin_dvr h3 {
    margin: 0;
    background: var(--m-surface-2) !important;
    border: 0 !important;
    border-bottom: 1px solid var(--m-border) !important;
    color: var(--m-text);
}
.m-shell #sod_fin_orderer .tbl_head01,
.m-shell #sod_fin_receiver .tbl_head01,
.m-shell #sod_fin_dvr .tbl_head01 {
    border: 0 !important;
    background: var(--m-surface) !important;
}
.m-shell #sod_fin_orderer .tbl_wrap,
.m-shell #sod_fin_receiver .tbl_wrap,
.m-shell #sod_fin_dvr .tbl_wrap {
    padding: 0 !important;
}
.m-shell #sod_fin_orderer table,
.m-shell #sod_fin_receiver table,
.m-shell #sod_fin_dvr table {
    width: 100%;
}
.m-shell #sod_fin_orderer th,
.m-shell #sod_fin_receiver th,
.m-shell #sod_fin_dvr th,
.m-shell #sod_fin_orderer td,
.m-shell #sod_fin_receiver td,
.m-shell #sod_fin_dvr td {
    padding: 12px 16px !important;
    border-top: 1px solid var(--m-border) !important;
    background: var(--m-surface) !important;
    color: var(--m-text) !important;
    word-break: keep-all;
}
.m-shell #sod_fin_orderer tr:first-child th,
.m-shell #sod_fin_receiver tr:first-child th,
.m-shell #sod_fin_dvr tr:first-child th,
.m-shell #sod_fin_orderer tr:first-child td,
.m-shell #sod_fin_receiver tr:first-child td,
.m-shell #sod_fin_dvr tr:first-child td {
    border-top: 0 !important;
}

/* g5se: orderform 섹션 — 무거운 카드 박스 제거, 단순한 heading + 인라인 폼 */
.m-shell #sod_frm_orderer,
.m-shell #sod_frm_taker,
.m-shell #sod_frm_pay,
.m-shell #sod_frm_dvr {
    margin: 0 0 24px !important;
    border: 0 !important;
    background: transparent !important;
    padding: 0 !important;
    box-shadow: none !important;
}
.m-shell #sod_frm section > h2,
.m-shell #sod_frm_orderer > h2,
.m-shell #sod_frm_taker > h2,
.m-shell #sod_frm_pay > h2,
.m-shell #sod_frm_dvr > h2 {
    border: 0 !important;
    border-bottom: 1px solid var(--m-border) !important;
    background: transparent !important;
    padding: 0 0 8px !important;
    margin: 0 0 14px !important;
    font-size: 1.05em !important;
    font-weight: 700 !important;
    color: var(--m-text) !important;
    line-height: 1.4 !important;
}
.m-shell #sod_frm .tbl_frm01 {
    margin: 0 !important;
    padding: 0 !important;
}
.m-shell #sod_frm .tbl_frm01 th {
    background: transparent !important;
    color: var(--m-text-soft) !important;
    font-weight: 500 !important;
    line-height: 1.4 !important;
    white-space: nowrap !important;
    width: auto !important;
    min-width: 100px;
}

/* g5se: orderform 입력 박스 데스크탑 다듬기 — legacy default_shop.css 의 width:100% + height:45px 무력화 */
.m-shell #sod_frm .tbl_frm01 .frm_input {
    height: 40px !important;
    padding: 8px 12px !important;
    background: var(--m-surface) !important;
    border: 1px solid var(--m-border) !important;
    color: var(--m-text) !important;
    border-radius: var(--m-radius-sm) !important;
    box-sizing: border-box;
}
.m-shell #sod_frm .tbl_frm01 .frm_input:focus {
    outline: 2px solid var(--m-primary);
    outline-offset: 1px;
    border-color: var(--m-primary) !important;
}
/* 데스크탑: 일반 input — td 풀 너비 (max-width 캡 제거) */
.m-shell #sod_frm .tbl_frm01 td input[type="text"]:not(.frm_address):not(#od_zip):not(#od_b_zip),
.m-shell #sod_frm .tbl_frm01 td input[type="password"],
.m-shell #sod_frm .tbl_frm01 td input[type="email"],
.m-shell #sod_frm .tbl_frm01 td input[type="tel"] {
    width: 100% !important;
    max-width: 100% !important;
}
/* 우편번호 — 좁게 + 검색 버튼과 한 줄 */
.m-shell #sod_frm .tbl_frm01 td #od_zip,
.m-shell #sod_frm .tbl_frm01 td #od_b_zip {
    width: 140px !important;
    max-width: 140px !important;
    margin-right: 6px;
}
.m-shell #sod_frm .tbl_frm01 td .btn_address {
    height: 40px !important;
    line-height: 38px !important;
    padding: 0 14px !important;
    vertical-align: middle;
}
/* 주소 (기본/상세) — 풀 너비 */
.m-shell #sod_frm .tbl_frm01 td .frm_address {
    width: 100% !important;
    max-width: 100% !important;
}
/* 전하실말씀 — 길게 한 줄 입력. legacy textarea 규칙 (min-height:100px) 무력화 */
.m-shell #sod_frm .tbl_frm01 td #od_memo {
    width: 100% !important;
    max-width: 100% !important;
    min-height: 0 !important;
    height: 40px !important;
}
@media (max-width: 768px) {
    .m-shell #sod_fin {
        display: block;
    }
    .m-shell #sod_fin_list .tbl_head03 {
        overflow: visible;
        border: 0;
        background: transparent;
        box-shadow: none;
    }
    .m-shell #sod_fin_list .tbl_head03 table,
    .m-shell #sod_fin_list .tbl_head03 thead,
    .m-shell #sod_fin_list .tbl_head03 tbody,
    .m-shell #sod_fin_list .tbl_head03 tr,
    .m-shell #sod_fin_list .tbl_head03 th,
    .m-shell #sod_fin_list .tbl_head03 td {
        display: block;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }
    .m-shell #sod_fin_list .tbl_head03 thead {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
    }
    .m-shell #sod_fin_list .tbl_head03 tbody {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .m-shell #sod_fin_list .tbl_head03 tr {
        overflow: hidden;
        border: 1px solid var(--m-border);
        border-radius: var(--m-radius, 8px);
        background: var(--m-surface);
        box-shadow: var(--m-shadow);
    }
    .m-shell #sod_fin_list .tbl_head03 tbody td {
        height: auto;
        min-height: 42px;
        padding: 11px 14px !important;
        border-top: 1px solid var(--m-border) !important;
        text-align: right;
    }
    .m-shell #sod_fin_list .tbl_head03 tbody td:first-child {
        border-top: 0 !important;
    }
    .m-shell #sod_fin_list .tbl_head03 .td_prd {
        min-height: 112px;
        padding: 14px 14px 14px 112px !important;
        text-align: left;
    }
    .m-shell #sod_fin_list .tbl_head03 .td_prd .sod_img {
        top: 14px;
        left: 14px;
        width: 80px;
        height: 80px;
    }
    .m-shell #sod_fin_list .tbl_head03 .td_prd .sod_name {
        min-height: 80px;
    }
    .m-shell #sod_fin_list .tbl_head03 td:not(.td_prd):before {
        float: left;
        color: var(--m-text-soft);
        font-weight: 700;
    }
    .m-shell #sod_fin_list .tbl_head03 td[headers="th_itqty"]:before { content: "총수량"; }
    .m-shell #sod_fin_list .tbl_head03 td[headers="th_itprice"]:before { content: "판매가"; }
    .m-shell #sod_fin_list .tbl_head03 td[headers="th_itpt"]:before { content: "포인트"; }
    .m-shell #sod_fin_list .tbl_head03 td[headers="th_itsd"]:before { content: "배송비"; }
    .m-shell #sod_fin_list .tbl_head03 td[headers="th_itsum"]:before { content: "소계"; }
    .m-shell #sod_fin_list .tbl_head03 td[headers="th_itst"]:before { content: "상태"; }
    .m-shell #sod_sts_wrap {
        justify-content: stretch;
    }
    .m-shell #sod_sts_wrap .btn_frmline {
        width: 100%;
    }
    .m-shell #sod_sts_explan {
        left: 16px;
        right: 16px;
        max-width: none;
    }
    .m-shell #sod_fin_orderer table,
    .m-shell #sod_fin_receiver table,
    .m-shell #sod_fin_dvr table,
    .m-shell #sod_fin_orderer tbody,
    .m-shell #sod_fin_receiver tbody,
    .m-shell #sod_fin_dvr tbody,
    .m-shell #sod_fin_orderer tr,
    .m-shell #sod_fin_receiver tr,
    .m-shell #sod_fin_dvr tr,
    .m-shell #sod_fin_orderer th,
    .m-shell #sod_fin_receiver th,
    .m-shell #sod_fin_dvr th,
    .m-shell #sod_fin_orderer td,
    .m-shell #sod_fin_receiver td,
    .m-shell #sod_fin_dvr td {
        display: block;
        width: 100%;
        box-sizing: border-box;
    }
    .m-shell #sod_fin_orderer th,
    .m-shell #sod_fin_receiver th,
    .m-shell #sod_fin_dvr th {
        padding-bottom: 4px !important;
        border-bottom: 0 !important;
        color: var(--m-text-soft) !important;
    }
    .m-shell #sod_fin_orderer td,
    .m-shell #sod_fin_receiver td,
    .m-shell #sod_fin_dvr td {
        padding-top: 4px !important;
    }
}

/* ============================================================
   주문서 (#forderform) 의 상품 목록 테이블 — cart 의 m-cart-table 톤과 동일하게 정렬.
   legacy 마크업 (.tbl_head03 .od_prd_list > #sod_list) 위에 카드형 overlay.
   ============================================================ */
.m-shell #forderform .od_prd_list {
    overflow-x: auto;
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius, 8px);
    background: var(--m-surface);
    box-shadow: var(--m-shadow);
    margin-bottom: 20px;
}
.m-shell #forderform #sod_list {
    width: 100%;
    min-width: 800px;
    border: 0 !important;
    border-collapse: collapse;
    margin: 0 !important;
}
.m-shell #forderform #sod_list thead th {
    height: 58px;
    padding: 0 14px !important;
    background: var(--m-surface-2) !important;
    border-top: 0 !important;
    border-bottom: 1px solid var(--m-border) !important;
    color: var(--m-text) !important;
    font-size: 1.05em;
    font-weight: 700;
    text-align: center;
    letter-spacing: 0;
}
.m-shell #forderform #sod_list td {
    padding: 18px 14px !important;
    background: var(--m-surface) !important;
    border-top: 0 !important;
    border-left: 0 !important;
    border-bottom: 1px solid var(--m-border) !important;
    color: var(--m-text) !important;
    text-align: center;
    vertical-align: middle;
}
.m-shell #forderform #sod_list tbody tr:last-child td {
    border-bottom: 0 !important;
}
.m-shell #forderform #sod_list td.td_prd {
    text-align: left;
    display: grid;
    grid-template-columns: 80px minmax(0, 1fr);
    gap: 14px;
    align-items: center;
    position: static !important;  /* legacy .od_prd_list .td_prd 의 position:relative 무력화 */
    padding-left: 14px !important;  /* legacy padding-left:120px (이미지 absolute 자리) 무력화 */
}
.m-shell #forderform #sod_list td.td_prd .sod_img {
    /* legacy 의 position:absolute; top:25px; left:20px 무력화 — grid 자식으로 흐름 */
    position: static !important;
    top: auto !important;
    left: auto !important;
    grid-column: 1;
    width: 80px;
    height: 80px;
    overflow: hidden;
    border-radius: 6px;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    margin: 0 !important;
}
.m-shell #forderform #sod_list td.td_prd .sod_img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.m-shell #forderform #sod_list td.td_prd .sod_name {
    grid-column: 2;
    min-width: 0;
    min-height: 0 !important;  /* legacy min-height:80px 가 grid cell 강제로 키움, 무력화 */
}
.m-shell #forderform #sod_list td.td_prd .sod_name b {
    color: var(--m-text);
    font-weight: 700;
}
.m-shell #forderform #sod_list .sod_opt {
    color: var(--m-text-soft) !important;
    font-size: 0.9em;
    margin: 6px 0 0 !important;
    line-height: 1.6 !important;
}
/* cart 의 m-cart-options 스타일 mirror — ul block + padding-left:18px, bullet 없음, "옵션" pill 제거 */
.m-shell #forderform #sod_list .sod_opt ul {
    display: block !important;
    margin: 0 !important;
    padding: 0 0 0 18px !important;
    list-style: none !important;
}
.m-shell #forderform #sod_list .sod_opt li {
    color: var(--m-text-soft) !important;
    margin: 3px 0 !important;
    padding: 0 !important;
    line-height: 1.5 !important;
}
.m-shell #forderform #sod_list .sod_opt li:before,
.m-shell #forderform #sod_list .sod_opt .opt_name:before {
    content: none !important;
    display: none !important;
    background: transparent !important;
    padding: 0 !important;
    margin: 0 !important;
}
.m-shell #forderform #sod_list .total_price {
    font-weight: 800;
    color: var(--m-primary);
}
.m-shell #forderform #sod_list .cp_btn {
    margin-top: 6px;
    padding: 4px 10px;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    border-radius: 4px;
    color: var(--m-text);
    font-size: 0.9em;
    cursor: pointer;
}
.m-shell #forderform #sod_list .cp_btn:hover {
    border-color: var(--m-primary);
    color: var(--m-primary);
}

/* ── 모바일 (≤768px): orderform 의 #sod_list 를 카드형으로 stack ────────── */
@media (max-width: 768px) {
    .m-shell #forderform .od_prd_list {
        overflow-x: visible !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .m-shell #forderform #sod_list {
        min-width: 0 !important;
        display: block !important;
    }
    .m-shell #forderform #sod_list thead {
        display: none !important;
    }
    .m-shell #forderform #sod_list tbody,
    .m-shell #forderform #sod_list tr {
        display: block !important;
        width: 100% !important;
    }
    .m-shell #forderform #sod_list tr {
        margin-bottom: 12px;
        border: 1px solid var(--m-border);
        border-radius: var(--m-radius, 8px);
        background: var(--m-surface);
        overflow: hidden;
    }
    .m-shell #forderform #sod_list td {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px !important;
        border-top: 0 !important;
        border-bottom: 1px solid var(--m-border) !important;
        text-align: right !important;
        white-space: normal !important;
    }
    .m-shell #forderform #sod_list tr td:last-child {
        border-bottom: 0 !important;
    }
    /* 상품 cell — 이미지 + 이름 (라벨 없음, 헤더 영역 역할) */
    .m-shell #forderform #sod_list td.td_prd {
        display: grid !important;
        grid-template-columns: 64px 1fr;
        gap: 12px;
        background: var(--m-surface-2);
        text-align: left !important;
    }
    .m-shell #forderform #sod_list td.td_prd .sod_img {
        width: 64px !important;
        height: 64px !important;
    }
    /* 데이터 cell ::before 로 라벨 (thead 자리 대체) */
    .m-shell #forderform #sod_list td.td_num::before { content: "총수량"; }
    .m-shell #forderform #sod_list td.td_dvr::before { content: "배송비"; }
    .m-shell #forderform #sod_list td.td_numbig:nth-of-type(3)::before { content: "판매가"; }
    .m-shell #forderform #sod_list td.td_numbig:nth-of-type(4)::before { content: "포인트"; }
    .m-shell #forderform #sod_list td.td_numbig:last-child::before { content: "소계"; }
    .m-shell #forderform #sod_list td::before {
        color: var(--m-text-soft);
        font-weight: 600;
        font-size: 0.92em;
    }

    /* 주문하시는 분 / 받으시는 분 form (#sod_frm_orderer / #sod_frm_taker / 등) — th/td block stack + 100% 입력 */
    .m-shell #sod_frm .tbl_frm01 table,
    .m-shell #sod_frm .tbl_frm01 tbody,
    .m-shell #sod_frm .tbl_frm01 tr,
    .m-shell #sod_frm .tbl_frm01 th,
    .m-shell #sod_frm .tbl_frm01 td {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
        background: transparent !important;  /* codex 다크룰의 surface-2 박스 무력화 */
        border: 0 !important;
    }
    .m-shell #sod_frm .tbl_frm01 th {
        padding: 12px 4px 4px !important;
        text-align: left !important;
        font-size: 0.9em;
        font-weight: 600;
        color: var(--m-text-soft) !important;
        line-height: 1.3 !important;
    }
    .m-shell #sod_frm .tbl_frm01 td {
        padding: 0 4px 8px !important;
        line-height: 1.4 !important;
        color: var(--m-text) !important;
    }
    /* tr 사이 dashed 분리 */
    .m-shell #sod_frm .tbl_frm01 tr {
        padding: 0 0 4px !important;
        border-bottom: 1px dashed var(--m-border) !important;
        margin-bottom: 8px;
    }
    .m-shell #sod_frm .tbl_frm01 tr:last-child {
        border-bottom: 0 !important;
    }
    .m-shell #sod_frm .tbl_frm01 td input[type="text"],
    .m-shell #sod_frm .tbl_frm01 td input[type="password"],
    .m-shell #sod_frm .tbl_frm01 td input[type="email"],
    .m-shell #sod_frm .tbl_frm01 td input[type="tel"],
    .m-shell #sod_frm .tbl_frm01 td .frm_input,
    .m-shell #sod_frm .tbl_frm01 td textarea,
    .m-shell #sod_frm .tbl_frm01 td select {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
        margin-bottom: 6px;
    }
    /* 주소 cluster — zip(좁게) + 검색 버튼 가로, 기본/상세/참고 stacked 100% */
    .m-shell #sod_frm .tbl_frm01 td #od_zip,
    .m-shell #sod_frm .tbl_frm01 td #od_b_zip {
        width: calc(100% - 130px) !important;
        display: inline-block !important;
        vertical-align: middle;
    }
    .m-shell #sod_frm .tbl_frm01 td .btn_address {
        width: 120px !important;
        display: inline-block !important;
        vertical-align: middle;
        height: auto;
    }

    /* 받으시는 분 / 배송지선택 (.order_choice_place) — radio + label 한 줄 쌍, 각 쌍은 line-break 으로 다음 줄 */
    .m-shell #sod_frm_taker .order_choice_place {
        display: block !important;
        padding: 0 !important;
        background: transparent !important;
        border: 0 !important;
    }
    .m-shell #sod_frm_taker .order_choice_place input[type="radio"] {
        margin: 0 6px 0 0 !important;
        vertical-align: middle;
    }
    .m-shell #sod_frm_taker .order_choice_place label {
        display: inline-block !important;
        vertical-align: middle;
        margin: 0 !important;
        padding: 6px 0;
    }
    /* 각 label 뒤에 줄바꿈 — input/label/input/label/... 마크업에서 label 뒤에 line break */
    .m-shell #sod_frm_taker .order_choice_place label::after {
        content: "";
        display: block;
    }
    .m-shell #sod_frm_taker .order_choice_place br {
        display: none !important;
    }
    /* 배송지목록 버튼 — float 무력화, 풀폭 + 텍스트 가운데 */
    .m-shell #sod_frm_taker .order_choice_place #order_address {
        float: none !important;
        position: static !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        width: 100% !important;
        margin: 8px 0 0 !important;
        padding: 10px 12px !important;
        text-align: center !important;
        text-decoration: none;
    }
    /* 배송지명 입력 + 기본배송지 체크박스 — 같은 td 안 인라인 분리 */
    .m-shell #sod_frm_taker .tbl_frm01 td input[type="checkbox"] + label {
        display: inline-block !important;
        margin: 4px 0 0;
    }
}

/* KCP 결제 modal (blockUI) — body 직속에 inject 되는데 .m-shell 의 z-index:9999
   에 가려져 보이지 않던 문제. 모달 / 오버레이 / NAX_BLOCK / 결제 iframe 모두
   m-shell 위로 올림. */
body > .blockUI,
body > #blockOverlayID,
body > #NAX_BLOCK,
body > #NAX_BLOCK iframe,
#naxIfr {
    z-index: 2147483000 !important;
}

/* ============================================================
   FINAL OVERRIDES — orderinquiryview 의 .td_prd 를 grid 로 재구성
   (absolute 방식이 어떤 이유로 작동 안함 → flex/grid 로 강제)
   ============================================================ */
.m-shell #sod_fin_list .tbl_head03 .td_prd {
    position: static !important;
    padding: 22px 14px !important;
    min-height: 0 !important;
    display: grid !important;
    grid-template-columns: 96px minmax(0, 1fr);
    gap: 18px;
    align-items: center;
    text-align: left !important;
    white-space: normal !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_img {
    position: static !important;
    top: auto !important;
    left: auto !important;
    grid-column: 1;
    grid-row: 1;
    width: 96px !important;
    height: 96px !important;
    max-width: 96px !important;
    min-width: 96px !important;
    overflow: hidden !important;
    border-radius: 6px;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_img img {
    width: 100% !important;
    height: 100% !important;
    max-width: 100% !important;
    max-height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_name {
    grid-column: 2;
    grid-row: 1;
    min-height: 0 !important;
    padding-left: 0 !important;
    min-width: 0;
    display: flex !important;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    color: var(--m-text) !important;
    font-size: 16px !important;
    line-height: 1.6 !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_name > a {
    display: block;
    margin: 0 !important;
    color: var(--m-text) !important;
    font-size: 17px !important;
    font-weight: 800 !important;
    line-height: 1.35 !important;
    text-decoration: none;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_name br {
    display: none !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_opt {
    margin: 0 !important;
    padding: 0 0 0 18px !important;
    color: var(--m-text) !important;
    font-size: 16px !important;
    line-height: 1.6 !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_opt ul {
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    list-style: none !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_opt li {
    margin: 0 !important;
    padding: 0 !important;
    color: var(--m-text) !important;
    line-height: 1.6 !important;
}
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_opt:before,
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_opt li:before,
.m-shell #sod_fin_list .tbl_head03 .td_prd .sod_opt .opt_name:before {
    display: none !important;
    content: none !important;
}

@media (max-width: 768px) {
    .m-shell #sod_fin_list .tbl_head03 .td_prd {
        grid-template-columns: 80px minmax(0, 1fr);
        gap: 14px;
        padding: 14px !important;
    }
    .m-shell #sod_fin_list .tbl_head03 .td_prd .sod_img {
        width: 80px !important;
        height: 80px !important;
        max-width: 80px !important;
        min-width: 80px !important;
    }
}

/* 모바일 쇼핑 카테고리 — 데스크톱 레이아웃은 유지하고, 숨겨진 메뉴 대신
   1차 상품분류를 상단에서 바로 탐색할 수 있게 한다. */
.m-shop-mobile-categories {
    display: none;
}
@media (max-width: 880px) {
    .m-shop-mobile-categories {
        display: block;
        flex: 0 0 auto;
        overflow-x: auto;
        padding: 9px 14px;
        border-bottom: 1px solid var(--m-border);
        background: var(--m-surface);
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
        cursor: grab;
        user-select: none;
        touch-action: pan-x;
    }
    .m-shop-mobile-categories.is-dragging {
        cursor: grabbing;
    }
    .m-shop-mobile-categories::-webkit-scrollbar {
        display: none;
    }
    .m-shop-mobile-categories-list {
        display: flex;
        align-items: center;
        gap: 8px;
        width: max-content;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .m-shop-mobile-category-link {
        display: block;
        padding: 8px 12px;
        border: 1px solid var(--m-border);
        border-radius: 999px;
        background: var(--m-bg);
        color: var(--m-text-soft);
        font-size: var(--m-text-sm);
        font-weight: 600;
        line-height: 1.2;
        text-decoration: none;
        white-space: nowrap;
    }
    .m-shop-mobile-category-home {
        border-color: transparent;
        background: transparent;
        color: var(--m-text-muted);
    }
    .m-shop-mobile-category-separator {
        flex: 0 0 auto;
        color: var(--m-text-muted);
        font-size: 18px;
        line-height: 1;
    }
    .m-shop-mobile-category-select {
        flex: 0 0 auto;
        width: auto;
        max-width: 180px;
        height: 36px;
        margin: 0;
        padding: 0 32px 0 11px;
        border: 1px solid var(--m-border);
        border-radius: 999px;
        background-color: var(--m-bg);
        color: var(--m-text);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        color-scheme: light dark;
    }
    .m-shop-mobile-category-select:focus {
        border-color: var(--m-primary);
        outline: 3px solid var(--m-primary-soft);
    }
    .m-shop-mobile-category-link:hover,
    .m-shop-mobile-category-link:focus-visible,
    .m-shop-mobile-category-link.is-active {
        border-color: var(--m-primary);
        background: var(--m-primary-soft);
        color: var(--m-primary);
    }
}

</style>

<script>
// shop 의 admin 톱니 (.sct_admin / .sit_admin) 을 페이지 타이틀 (h1) 끝으로 옮김.
// 카테고리/상품명 바로 뒤에 톱니가 붙어 한 라인으로 보임.
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var adm = document.querySelector('main.m-container .sct_admin, main.m-container .sit_admin');
        var h1  = document.querySelector('main.m-container > h1');
        if (adm && h1) h1.appendChild(adm);
    });
})();

// 위시리스트 클릭 시 시각 active sync — 정적 .js 가 브라우저 캐시되는 케이스 방어용.
// $.ajaxSuccess 로 ajax.action.php?action=wish_update 응답을 가로채 it_id 매칭 button 상태를 맞춤.
(function () {
    if (typeof jQuery === 'undefined') return;
    jQuery(document).ajaxSuccess(function (event, xhr, settings) {
        var url = (settings && settings.url) || '';
        var data = (settings && settings.data) || '';
        if (url.indexOf('ajax.action.php') < 0) return;
        if (typeof data === 'string' && data.indexOf('action=wish_update') < 0) return;
        var m = /it_id=([a-zA-Z0-9_-]+)/.exec(typeof data === 'string' ? data : '');
        if (!m) return;
        var $btn = jQuery('.btn_wish[data-it_id="' + m[1] + '"]');
        if (!$btn.length) return;
        var response = {};
        try {
            response = JSON.parse(xhr.responseText || '{}');
        } catch (e) {
            response = {};
        }
        if (response.status === 'deleted') {
            $btn.removeClass('is_active text-rose-500');
            $btn.find('i.fa-heart').removeClass('fa-heart').addClass('fa-heart-o');
        } else {
            $btn.addClass('is_active');
            $btn.find('i.fa-heart-o').removeClass('fa-heart-o').addClass('fa-heart');
        }
    });
})();
</script>


<div class="m-shell">

    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <?php
    // 모바일 카테고리 바: 홈에서는 1단계 전체, 분류 화면에서는 현재 경로와 다음 단계를 한 줄에 노출한다.
    $_shop_categories = get_shop_category_array(true);
    if (!empty($_shop_categories)) {
        $_mobile_ca_id = !empty($ca_id) ? preg_replace('/[^0-9a-z]/i', '', $ca_id) : '';
        if (!$_mobile_ca_id && isset($it['ca_id'])) {
            $_mobile_ca_id = preg_replace('/[^0-9a-z]/i', '', $it['ca_id']);
        }
    ?>
    <nav class="m-shop-mobile-categories<?php echo $_mobile_ca_id ? ' has-current-category' : ''; ?>" aria-label="쇼핑몰 카테고리">
        <div class="m-shop-mobile-categories-list">
            <a href="<?php echo G5_SHOP_URL; ?>/" class="m-shop-mobile-category-link m-shop-mobile-category-home">홈</a>

            <?php if (!$_mobile_ca_id) { ?>
                <?php foreach ($_shop_categories as $_shop_category) {
                    if (empty($_shop_category['text'])) continue;
                    $_shop_category_data = $_shop_category['text'];
                ?>
                <a href="<?php echo $_shop_category_data['url']; ?>" class="m-shop-mobile-category-link"><?php echo get_text($_shop_category_data['ca_name']); ?></a>
                <?php } ?>
            <?php } else {
                $_root_id = substr($_mobile_ca_id, 0, 2);
                $_root = isset($_shop_categories[$_root_id]['text']) ? $_shop_categories[$_root_id]['text'] : null;
                if ($_root) {
            ?>
                <span class="m-shop-mobile-category-separator" aria-hidden="true">›</span>
                <a href="<?php echo $_root['url']; ?>" class="m-shop-mobile-category-link is-active"><?php echo get_text($_root['ca_name']); ?></a>
            <?php
                }

                $_current_depth = min(5, intdiv(strlen($_mobile_ca_id), 2));
                for ($_depth = 2; $_depth <= min(5, $_current_depth + 1); $_depth++) {
                    $_length = $_depth * 2;
                    $_parent_id = substr($_mobile_ca_id, 0, $_length - 2);
                    $_selected_id = strlen($_mobile_ca_id) >= $_length ? substr($_mobile_ca_id, 0, $_length) : '';
                    $_category_options = array();
                    $_category_result = sql_pdo_query(
                        " select ca_id, ca_name from {$g5['g5_shop_category_table']}
                            where ca_use = '1' and length(ca_id) = {$_length} and ca_id like :parent_id
                            order by ca_order, ca_id ",
                        array(':parent_id' => $_parent_id.'%')
                    );
                    while ($_category_row = sql_fetch_array($_category_result)) {
                        $_category_options[] = $_category_row;
                    }
                    if (!$_category_options) continue;
                    $_selected_name = '';
                    if ($_selected_id) {
                        foreach ($_category_options as $_category_option) {
                            if ($_selected_id === $_category_option['ca_id']) {
                                $_selected_name = $_category_option['ca_name'];
                                break;
                            }
                        }
                    }
            ?>
                <span class="m-shop-mobile-category-separator" aria-hidden="true">›</span>
                <select class="m-shop-mobile-category-select" aria-label="<?php echo $_depth; ?>단계 상품 분류" onchange="if (this.value) window.location.href = this.value;">
                    <?php if (!$_selected_id) { ?><option value="">하위 분류</option><?php } ?>
                    <?php if ($_selected_name !== '') { ?><option value="" selected hidden><?php echo get_text($_selected_name); ?></option><?php } ?>
                    <?php foreach ($_category_options as $_category_option) { ?>
                    <option value="<?php echo shop_category_url($_category_option['ca_id']); ?>"><?php echo get_text($_category_option['ca_name']); ?></option>
                    <?php } ?>
                </select>
            <?php
                }
            } ?>
        </div>
    </nav>
    <script>
    (function () {
        var scroller = document.querySelector('.m-shop-mobile-categories');
        if (!scroller) return;
        var startX = 0, startScroll = 0, dragging = false, moved = false;
        scroller.addEventListener('pointerdown', function (event) {
            if (event.pointerType !== 'mouse' || event.button !== 0 || event.target.closest('select')) return;
            dragging = true;
            moved = false;
            startX = event.clientX;
            startScroll = scroller.scrollLeft;
            scroller.setPointerCapture(event.pointerId);
            scroller.classList.add('is-dragging');
        });
        scroller.addEventListener('pointermove', function (event) {
            if (!dragging) return;
            var distance = event.clientX - startX;
            if (Math.abs(distance) > 4) moved = true;
            scroller.scrollLeft = startScroll - distance;
        });
        function stopDragging(event) {
            if (!dragging) return;
            dragging = false;
            scroller.classList.remove('is-dragging');
            if (scroller.hasPointerCapture(event.pointerId)) scroller.releasePointerCapture(event.pointerId);
        }
        scroller.addEventListener('pointerup', stopDragging);
        scroller.addEventListener('pointercancel', stopDragging);
        scroller.addEventListener('click', function (event) {
            if (moved) {
                event.preventDefault();
                event.stopPropagation();
                moved = false;
            }
        }, true);
        if (scroller.classList.contains('has-current-category')) {
            requestAnimationFrame(function () {
                scroller.scrollLeft = scroller.scrollWidth - scroller.clientWidth;
            });
        }
    })();
    </script>
    <?php } ?>

    <?php if(defined('_INDEX_')) { include G5_BBS_PATH.'/newwin.inc.php'; } ?>

    <?php
        // 콘텐츠 분류 — 홈/검색/상품 페이지에 따라 다른 레이아웃
        $is_index = defined('_INDEX_') && _INDEX_;
        $is_shop_category_page = !empty($ca_id) && !(isset($it_id) && isset($it) && isset($it['it_id']) && $it_id === $it['it_id']);
        // 우측 사이드바 사용 페이지 — 홈일 때만 (검색/리스트/상품 본문은 풀폭이 깔끔)
        $use_sidebar = $is_index;
    ?>

    <main class="m-container <?php echo $use_sidebar ? 'm-with-sidebar' : ''; ?>" style="padding: 24px 20px 48px;">
        <?php if (!$use_sidebar && !empty($g5['title']) && (!isset($bo_table) || !$bo_table || (isset($w) && $w == 's'))) { ?>
            <h1<?php echo $is_shop_category_page ? ' class="m-shop-category-page-title"' : ''; ?> style="font-size: var(--m-text-2xl); margin: 0 0 18px; letter-spacing: -0.01em;"><?php echo $g5['title'] ?></h1>
        <?php } ?>

        <div class="m-main-col">
            <!-- shop-content open : 개별 페이지가 콘텐츠를 채움. shop.tail.php 에서 닫힘. -->
            <?php
                $content_class = array('shop-content');
                if( isset($it_id) && isset($it) && isset($it['it_id']) && $it_id === $it['it_id']) $content_class[] = 'is_item';
                if( defined('IS_SHOP_SEARCH') && IS_SHOP_SEARCH ) $content_class[] = 'is_search';
                if( $is_index ) $content_class[] = 'is_index';
            ?>
            <div class="<?php echo implode(' ', $content_class); ?>">

<?php /* shop.tail.php 가 m-main-col / aside / main / m-shell 을 닫음 */ ?>
