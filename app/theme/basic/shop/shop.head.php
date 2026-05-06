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
   "1줄당 이미지 수" 가 실제 컬럼수에 반영되도록 함. owl-carousel 은 제외. */
/* .smt_30 은 사이드바용 (thumb 80px 좌측 + 텍스트 우측 리스트) → grid 미적용 */
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30) {
    display: grid !important;
    grid-template-columns: repeat(var(--m-list-cols, 4), minmax(0, 1fr));
    gap: 16px;
    margin: 0;
    padding: 0;
    list-style: none;
}
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30) > li.sct_li {
    float: none !important;
    width: auto !important;
    margin: 0 !important;
    border-bottom: 0 !important;
}
.m-shop-grid > ul:not(.owl-carousel):not(.smt_30) > li.sct_li .sct_img img {
    width: 100%;
    height: auto;
    display: block;
}
@media (max-width: 880px) {
    .m-shop-grid > ul:not(.owl-carousel):not(.smt_30) {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
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
[data-theme="dark"] #gnb {
    background: var(--m-surface) !important;
    border-color: var(--m-border) !important;
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
