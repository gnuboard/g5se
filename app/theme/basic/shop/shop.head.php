<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$q = isset($_GET['q']) ? clean_xss_tags($_GET['q'], 1, 1) : '';

if(G5_IS_MOBILE) {
    include_once(G5_THEME_MSHOP_PATH.'/shop.head.php');
    return;
}

include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
include_once(G5_LIB_PATH.'/latest.lib.php');

add_javascript('<script src="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/owlcarousel/owl.carousel.css">', 0);
?>

<!-- 상단 (modern) 시작 { -->
<header id="hd" class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-700 dark:bg-slate-900/90">
    <h1 id="hd_h1" class="sr-only"><?php echo $g5['title'] ?></h1>
    <div id="skip_to_container"><a href="#container" class="sr-only focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-50 focus:rounded focus:bg-admin-primary-600 focus:px-3 focus:py-1 focus:text-white">본문 바로가기</a></div>

    <?php if(defined('_INDEX_')) { include G5_BBS_PATH.'/newwin.inc.php'; } ?>

    <!-- TNB (커뮤니티/쇼핑 토글 + 빠른링크) -->
    <div id="tnb" class="border-b border-slate-100 bg-slate-50 text-xs dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto flex max-w-screen-xl items-center justify-between px-4 py-1.5">
            <?php if(defined('G5_COMMUNITY_USE') && G5_COMMUNITY_USE) { ?>
            <ul id="hd_define" class="flex items-center gap-1">
                <li><a href="<?php echo G5_URL ?>/" class="rounded px-2 py-1 text-slate-500 hover:bg-slate-200/70 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100">커뮤니티</a></li>
                <li class="active"><a href="<?php echo G5_SHOP_URL ?>/" class="rounded bg-slate-900 px-2 py-1 font-medium text-white dark:bg-slate-200 dark:text-slate-900">쇼핑몰</a></li>
            </ul>
            <?php } else { ?><span></span><?php } ?>
            <ul id="hd_qnb" class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                <li><a href="<?php echo G5_BBS_URL ?>/faq.php" class="hover:text-slate-900 dark:hover:text-slate-100">FAQ</a></li>
                <li><a href="<?php echo G5_BBS_URL ?>/qalist.php" class="hover:text-slate-900 dark:hover:text-slate-100">1:1문의</a></li>
                <li><a href="<?php echo G5_SHOP_URL ?>/personalpay.php" class="hover:text-slate-900 dark:hover:text-slate-100">개인결제</a></li>
                <li><a href="<?php echo G5_SHOP_URL ?>/itemuselist.php" class="hover:text-slate-900 dark:hover:text-slate-100">사용후기</a></li>
                <li><a href="<?php echo G5_SHOP_URL ?>/itemqalist.php" class="hover:text-slate-900 dark:hover:text-slate-100">상품문의</a></li>
                <li class="bd"><a href="<?php echo G5_SHOP_URL; ?>/couponzone.php" class="rounded bg-rose-50 px-2 py-1 font-medium text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-300">쿠폰존</a></li>
            </ul>
        </div>
    </div>

    <!-- 로고 + 검색 + 액션 -->
    <div id="hd_wrapper" class="mx-auto flex max-w-screen-xl items-center gap-6 px-4 py-3">
        <div id="logo" class="shrink-0">
            <a href="<?php echo G5_SHOP_URL; ?>/" class="block">
                <img src="<?php echo G5_DATA_URL; ?>/common/logo_img" alt="<?php echo $config['cf_title']; ?>" class="h-10 w-auto">
            </a>
        </div>

        <div class="hd_sch_wr flex-1">
            <fieldset id="hd_sch" class="m-0 border-0 p-0">
                <legend class="sr-only">쇼핑몰 전체검색</legend>
                <form name="frmsearch1" action="<?php echo G5_SHOP_URL; ?>/search.php" onsubmit="return search_submit(this);" class="relative mx-auto max-w-xl">
                    <label for="sch_str" class="sr-only">검색어<strong class="sr-only"> 필수</strong></label>
                    <input type="text" name="q" value="<?php echo stripslashes(get_text(get_search_string($q))); ?>" id="sch_str" required
                           placeholder="검색어를 입력해주세요"
                           class="h-11 w-full rounded-full border border-slate-300 bg-white pl-5 pr-12 text-sm text-slate-800 placeholder:text-slate-400 focus:border-admin-primary-500 focus:outline-none focus:ring-2 focus:ring-admin-primary-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-admin-primary-900/40">
                    <button type="submit" id="sch_submit" value="검색"
                            class="absolute right-1 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-admin-primary-600 text-white transition hover:bg-admin-primary-700">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        <span class="sr-only">검색</span>
                    </button>
                </form>
                <script>
                function search_submit(f) {
                    if (f.q.value.length < 2) {
                        alert("검색어는 두글자 이상 입력하십시오.");
                        f.q.select();
                        f.q.focus();
                        return false;
                    }
                    return true;
                }
                </script>
            </fieldset>
        </div>

        <ul class="hd_login flex shrink-0 items-center gap-2">
            <?php if ($is_member) {  ?>
                <li class="shop_login">
                    <?php echo outlogin('theme/shop_basic'); // 아웃로그인 ?>
                </li>
                <li class="shop_cart">
                    <a href="<?php echo G5_SHOP_URL; ?>/cart.php"
                       class="relative inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        <span class="sr-only">장바구니</span>
                        <span class="count absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white"><?php echo get_boxcart_datas_count(); ?></span>
                    </a>
                </li>
            <?php } else { ?>
                <li class="login">
                    <a href="<?php echo G5_BBS_URL ?>/login.php?url=<?php echo $urlencode; ?>"
                       class="inline-flex h-10 items-center rounded-full bg-admin-primary-600 px-4 text-sm font-medium text-white hover:bg-admin-primary-700">
                        로그인
                    </a>
                </li>
            <?php }  ?>
        </ul>
    </div>

    <!-- hd_menu (카테고리 + type 링크) -->
    <div id="hd_menu" class="border-t border-slate-100 dark:border-slate-800">
        <div class="mx-auto flex max-w-screen-xl items-center gap-2 px-4 py-2">
            <button type="button" id="menu_open"
                    class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                <i class="fa fa-bars" aria-hidden="true"></i> 카테고리
            </button>
            <?php include_once(G5_THEME_SHOP_PATH.'/category.php'); // 분류 ?>
            <ul class="hd_menu ml-auto flex items-center gap-1 text-sm">
                <li><a href="<?php echo shop_type_url(1); ?>" class="rounded px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100">히트상품</a></li>
                <li><a href="<?php echo shop_type_url(2); ?>" class="rounded px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100">추천상품</a></li>
                <li><a href="<?php echo shop_type_url(3); ?>" class="rounded px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100">최신상품</a></li>
                <li><a href="<?php echo shop_type_url(4); ?>" class="rounded px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100">인기상품</a></li>
                <li><a href="<?php echo shop_type_url(5); ?>" class="rounded bg-rose-50 px-3 py-1.5 font-medium text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-300">할인상품</a></li>
            </ul>
        </div>
    </div>
</header>
<!-- } 상단 끝 -->

<!-- side menu (마이메뉴/오늘본/장바구니/위시 quick drawer) -->
<div id="side_menu" class="fixed right-4 top-1/3 z-30 flex flex-col items-end">
    <ul id="quick" class="flex flex-col gap-2">
        <li><button class="btn_sm_cl1 btn_sm flex h-12 w-12 flex-col items-center justify-center rounded-full bg-white text-[10px] text-slate-700 shadow-md transition hover:bg-admin-primary-50 hover:text-admin-primary-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><i class="fa fa-user-o text-base" aria-hidden="true"></i><span class="qk_tit mt-0.5">메뉴</span></button></li>
        <li><button class="btn_sm_cl2 btn_sm flex h-12 w-12 flex-col items-center justify-center rounded-full bg-white text-[10px] text-slate-700 shadow-md transition hover:bg-admin-primary-50 hover:text-admin-primary-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><i class="fa fa-archive text-base" aria-hidden="true"></i><span class="qk_tit mt-0.5">최근본</span></button></li>
        <li><button class="btn_sm_cl3 btn_sm flex h-12 w-12 flex-col items-center justify-center rounded-full bg-white text-[10px] text-slate-700 shadow-md transition hover:bg-admin-primary-50 hover:text-admin-primary-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><i class="fa fa-shopping-cart text-base" aria-hidden="true"></i><span class="qk_tit mt-0.5">장바구니</span></button></li>
        <li><button class="btn_sm_cl4 btn_sm flex h-12 w-12 flex-col items-center justify-center rounded-full bg-white text-[10px] text-slate-700 shadow-md transition hover:bg-rose-50 hover:text-rose-500 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-rose-900/30"><i class="fa fa-heart-o text-base" aria-hidden="true"></i><span class="qk_tit mt-0.5">위시</span></button></li>
    </ul>
    <button type="button" id="top_btn" class="mt-2 flex h-10 w-10 items-center justify-center rounded-full bg-slate-900/80 text-white shadow-md backdrop-blur transition hover:bg-slate-900 dark:bg-slate-200/80 dark:text-slate-900 dark:hover:bg-white">
        <i class="fa fa-arrow-up" aria-hidden="true"></i>
        <span class="sr-only">상단으로</span>
    </button>

    <div id="tabs_con" class="absolute right-14 top-0 hidden">
        <div class="side_mn_wr1 qk_con hidden">
            <div class="qk_con_wr w-72 rounded-lg border border-slate-200 bg-white p-4 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <?php echo outlogin('theme/shop_side'); // 아웃로그인 ?>
                <ul class="side_tnb mt-3 space-y-1 text-sm">
                    <?php if ($is_member) { ?>
                    <li><a href="<?php echo G5_SHOP_URL; ?>/mypage.php" class="block rounded px-2 py-1 text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">마이페이지</a></li>
                    <?php } ?>
                    <li><a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php" class="block rounded px-2 py-1 text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">주문내역</a></li>
                    <li><a href="<?php echo G5_BBS_URL ?>/faq.php" class="block rounded px-2 py-1 text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">FAQ</a></li>
                    <li><a href="<?php echo G5_BBS_URL ?>/qalist.php" class="block rounded px-2 py-1 text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">1:1문의</a></li>
                    <li><a href="<?php echo G5_SHOP_URL ?>/personalpay.php" class="block rounded px-2 py-1 text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">개인결제</a></li>
                    <li><a href="<?php echo G5_SHOP_URL ?>/itemuselist.php" class="block rounded px-2 py-1 text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">사용후기</a></li>
                    <li><a href="<?php echo G5_SHOP_URL ?>/itemqalist.php" class="block rounded px-2 py-1 text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">상품문의</a></li>
                    <li><a href="<?php echo G5_SHOP_URL; ?>/couponzone.php" class="block rounded px-2 py-1 text-rose-600 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-900/30">쿠폰존</a></li>
                </ul>
                <button type="button" class="con_close mt-3 inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                    <i class="fa fa-times-circle" aria-hidden="true"></i> 닫기
                </button>
            </div>
        </div>
        <div class="side_mn_wr2 qk_con hidden">
            <div class="qk_con_wr w-72 rounded-lg border border-slate-200 bg-white p-4 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <?php include(G5_SHOP_SKIN_PATH.'/boxtodayview.skin.php'); ?>
                <button type="button" class="con_close mt-3 inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                    <i class="fa fa-times-circle" aria-hidden="true"></i> 닫기
                </button>
            </div>
        </div>
        <div class="side_mn_wr3 qk_con hidden">
            <div class="qk_con_wr w-72 rounded-lg border border-slate-200 bg-white p-4 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <?php include_once(G5_SHOP_SKIN_PATH.'/boxcart.skin.php'); ?>
                <button type="button" class="con_close mt-3 inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                    <i class="fa fa-times-circle" aria-hidden="true"></i> 닫기
                </button>
            </div>
        </div>
        <div class="side_mn_wr4 qk_con hidden">
            <div class="qk_con_wr w-72 rounded-lg border border-slate-200 bg-white p-4 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                <?php include_once(G5_SHOP_SKIN_PATH.'/boxwish.skin.php'); ?>
                <button type="button" class="con_close mt-3 inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                    <i class="fa fa-times-circle" aria-hidden="true"></i> 닫기
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(function ($){
    $(".btn_member_mn").on("click", function() {
        $(".member_mn").toggle();
        $(".btn_member_mn").toggleClass("btn_member_mn_on");
    });

    var active_class = "btn_sm_on",
        side_btn_el = "#quick .btn_sm",
        quick_container = ".qk_con";

    $(document).on("click", side_btn_el, function(e){
        e.preventDefault();
        var $this = $(this);
        if (!$this.hasClass(active_class)) {
            $(side_btn_el).removeClass(active_class);
            $this.addClass(active_class);
        }

        $("#tabs_con").removeClass("hidden");
        $(quick_container).addClass("hidden");
        if      ( $this.hasClass("btn_sm_cl1") ) $(".side_mn_wr1").removeClass("hidden");
        else if ( $this.hasClass("btn_sm_cl2") ) $(".side_mn_wr2").removeClass("hidden");
        else if ( $this.hasClass("btn_sm_cl3") ) $(".side_mn_wr3").removeClass("hidden");
        else if ( $this.hasClass("btn_sm_cl4") ) $(".side_mn_wr4").removeClass("hidden");
    }).on("click", ".con_close", function(e){
        $(quick_container).addClass("hidden");
        $("#tabs_con").addClass("hidden");
        $(side_btn_el).removeClass(active_class);
    });

    $(document).mouseup(function (e){
        var container = $(quick_container),
            mn_container = $(".shop_login");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.addClass("hidden");
            $("#tabs_con").addClass("hidden");
            $(side_btn_el).removeClass(active_class);
        }
        if( mn_container.has(e.target).length === 0){
            $(".member_mn").hide();
            $(".btn_member_mn").removeClass("btn_member_mn_on");
        }
    });

    $("#top_btn").on("click", function() {
        $("html, body").animate({scrollTop:0}, '500');
        return false;
    });
});
</script>

<?php
    $wrapper_class = array();
    if( defined('G5_IS_COMMUNITY_PAGE') && G5_IS_COMMUNITY_PAGE ){
        $wrapper_class[] = 'is_community';
    }
?>
<!-- 전체 콘텐츠 시작 { -->
<div id="wrapper" class="<?php echo implode(' ', $wrapper_class); ?> mx-auto max-w-screen-xl px-4 py-6">
    <div id="container" class="<?php if(defined('_INDEX_')) echo 'lg:grid lg:grid-cols-[260px_1fr] lg:gap-6'; ?>">

        <?php if(defined('_INDEX_')) { ?>
        <aside id="aside" class="space-y-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <?php include_once(G5_SHOP_SKIN_PATH.'/boxcategory.skin.php'); // 상품분류 ?>
            </div>
            <?php if($default['de_type4_list_use']) { ?>
            <section id="side_pd" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <h2 class="mb-3 text-sm font-bold tracking-tight text-slate-700 dark:text-slate-200"><a href="<?php echo shop_type_url('4'); ?>" class="hover:text-admin-primary-600 dark:hover:text-admin-primary-400">인기상품</a></h2>
                <?php
                $list = new item_list();
                $list->set_type(4);
                $list->set_view('it_id', false);
                $list->set_view('it_name', true);
                $list->set_view('it_basic', false);
                $list->set_view('it_cust_price', false);
                $list->set_view('it_price', true);
                $list->set_view('it_icon', false);
                $list->set_view('sns', false);
                $list->set_view('star', true);
                echo $list->run();
                ?>
            </section>
            <?php } ?>

            <div><?php echo display_banner('왼쪽', 'boxbanner.skin.php'); ?></div>
            <div><?php echo poll('theme/shop_basic'); // 설문조사 ?></div>
        </aside>
        <?php } // end if ?>

        <?php
            $content_class = array('shop-content');
            if( isset($it_id) && isset($it) && isset($it['it_id']) && $it_id === $it['it_id']){
                $content_class[] = 'is_item';
            }
            if( defined('IS_SHOP_SEARCH') && IS_SHOP_SEARCH ){
                $content_class[] = 'is_search';
            }
            if( defined('_INDEX_') && _INDEX_ ){
                $content_class[] = 'is_index';
            }
        ?>
        <!-- .shop-content 시작 { -->
        <main class="<?php echo implode(' ', $content_class); ?> min-w-0">
            <?php if ((!$bo_table || $w == 's' ) && !defined('_INDEX_')) { ?>
                <div id="wrapper_title" class="mb-4 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50"><?php echo $g5['title'] ?></div>
            <?php } ?>
            <!-- 글자크기 조정 (display:none — 호환용 hook) -->
            <div id="text_size" class="hidden">
                <button class="no_text_resize" onclick="font_resize('container', 'decrease');">작게</button>
                <button class="no_text_resize" onclick="font_default('container');">기본</button>
                <button class="no_text_resize" onclick="font_resize('container', 'increase');">크게</button>
            </div>
