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
