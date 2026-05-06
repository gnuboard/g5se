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

/* 관리자 빠른편집 톱니바퀴 (poll/visit skin 의 .btn_admin) — 사용자 화면 노이즈라 숨김 */
.m-shell .btn_admin { display: none !important; }

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

/* item.php (상품상세) 다크모드 — legacy style.css 의 sit_* 흰배경 / 검정텍스트 hardcode 오버라이드 */
[data-theme="dark"] #sit_info,
[data-theme="dark"] #sit_tab .tab_tit,
[data-theme="dark"] #sit_tab .tab_tit li button,
[data-theme="dark"] #sit_tab .tab_con,
[data-theme="dark"] #sit_rel,
[data-theme="dark"] #sit_ov_from,
[data-theme="dark"] #sit_siblings,
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
[data-theme="dark"] #sit_desc,
[data-theme="dark"] #sit_star_sns span,
[data-theme="dark"] #sit_siblings a {
    color: var(--m-text-soft) !important;
}
[data-theme="dark"] #sit_ov_soldout {
    background: rgba(239, 68, 68, 0.1) !important;
}
[data-theme="dark"] #sit_star_sns .sns_area {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
}

/* item.php 레이아웃 폭 정리 — legacy 가 width:1200px + padding:45px + float 로 잡혀 있어
   모던 컨테이너 안에서 폭이 어색함. grid 로 이미지/구매패널 깔끔하게 분배. */
#sit_ov_wrap {
    width: 100% !important;
    padding: 0 !important;
    border-top: 0 !important;
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 32px;
    align-items: start;
}
#sit_pvi,
#sit_ov {
    float: none !important;
    width: auto !important;
    min-height: 0 !important;
}
#sit_pvi_big img { width: 100% !important; height: auto !important; max-width: 500px; }
@media (max-width: 880px) {
    #sit_ov_wrap { grid-template-columns: 1fr; }
}
</style>


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
