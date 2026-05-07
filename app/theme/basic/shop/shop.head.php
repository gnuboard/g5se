<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$q = isset($_GET['q']) ? clean_xss_tags($_GET['q'], 1, 1) : '';

if(G5_IS_MOBILE) {
    include_once(G5_THEME_MSHOP_PATH.'/shop.head.php');
    return;
}

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
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl),
.m-shop-grid > .smt_20 > ul.sct_ul {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(min(var(--m-img-width, 200px), 100%), 1fr));
    max-width: calc(var(--m-list-cols, 4) * var(--m-img-width, 200px) + (var(--m-list-cols, 4) - 1) * 16px);
    gap: 16px;
    margin: 0 auto !important;
    padding: 0;
    list-style: none;
}
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl) > li.sct_li,
.m-shop-grid > .smt_20 > ul.sct_ul > li.sct_li {
    float: none !important;
    width: auto !important;
    margin: 0 !important;
    border-bottom: 0 !important;
}
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30):not(.sctrl) > li.sct_li .sct_img img,
.m-shop-grid > .smt_20 > ul.sct_ul > li.sct_li .sct_img img {
    width: 100%;
    height: auto;
    display: block;
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
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_txt,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_txt a,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_basic,
[data-theme="dark"] .m-shop-grid > ul > li.sct_li .sct_cost {
    color: var(--m-text) !important;
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
/* 카테고리 박스: 외곽은 .m-card 가 그리므로 안쪽 #gnb 의 좌/우/하단 border 제거
   (원본 style.css 가 border-top:0 만 빼서 위만 비어 보이는 어색한 상태였음).
   light/dark 양쪽 다 적용. */
#gnb {
    border: 0 !important;
    margin-bottom: 0 !important;
    background: transparent !important;
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

    <?php if(defined('_INDEX_')) { include G5_BBS_PATH.'/newwin.inc.php'; } ?>

    <?php
        // 콘텐츠 분류 — 홈/검색/상품 페이지에 따라 다른 레이아웃
        $is_index = defined('_INDEX_') && _INDEX_;
        // 우측 사이드바 사용 페이지 — 홈일 때만 (검색/리스트/상품 본문은 풀폭이 깔끔)
        $use_sidebar = $is_index;
    ?>

    <main class="m-container <?php echo $use_sidebar ? 'm-with-sidebar' : ''; ?>" style="padding: 24px 20px 48px;">
        <?php if (!$use_sidebar && !empty($g5['title']) && (!isset($bo_table) || !$bo_table || (isset($w) && $w == 's'))) { ?>
            <h1 style="font-size: var(--m-text-2xl); margin: 0 0 18px; letter-spacing: -0.01em;"><?php echo $g5['title'] ?></h1>
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
