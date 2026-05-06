<?php
$sub_menu = '500210';
require_once __DIR__.'/_common.php';

require_once __DIR__.'/../_layout.php';
admin_require_login();
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '가격비교사이트';
admin_layout_start($g5["title"], "shop");
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5["title"]) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">
<?php
$pg_anchor = '<ul class="anchor">
<li><a href="#anc_pricecompare_info">가격비교사이트 연동 안내</a></li>
<li><a href="#anc_pricecompare_engine">사이트별 엔진페이지 URL</a></li>
</ul>';
?>

<section id="anc_pricecompare_info" class="mb-6">
    <h2 class="text-base font-bold tracking-tight text-slate-700 dark:text-slate-200 mb-3">가격비교사이트 연동 안내</h2>
    <ol class="list-decimal pl-5 space-y-1 text-sm text-slate-600 dark:text-slate-300">
        <li>가격비교사이트는 네이버 지식쇼핑, 다음 쇼핑하우 등이 있습니다.</li>
        <li>앞서 나열한 가격비교사이트 중 희망하시는 사이트에 입점합니다.</li>
        <li><strong class="font-semibold text-slate-800 dark:text-slate-100">사이트별 엔진페이지 URL</strong> 을 참고하여 해당 엔진페이지 URL 을 입점하신 사이트에 알려주시면 됩니다.</li>
    </ol>
</section>

<section id="anc_pricecompare_engine">
    <h2 class="text-base font-bold tracking-tight text-slate-700 dark:text-slate-200 mb-3">사이트별 엔진페이지 URL</h2>

    <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">사이트 명을 클릭하시면 해당 사이트로 이동합니다.</p>

    <div class="space-y-4">
        <!-- 네이버쇼핑 -->
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-3 text-sm font-bold">
                <a href="http://shopping.naver.com/" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">네이버쇼핑 <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></a>
            </h3>
            <ul class="ml-4 space-y-1.5 text-sm">
                <li class="flex flex-wrap items-baseline gap-2"><span class="shrink-0 w-24 text-slate-500 dark:text-slate-400">입점 안내</span><a href="http://join.shopping.naver.com/join/intro.nhn" target="_blank" class="text-admin-primary-600 hover:underline dark:text-admin-primary-400 break-all">http://join.shopping.naver.com/join/intro.nhn</a></li>
                <li class="flex flex-wrap items-baseline gap-2"><span class="shrink-0 w-24 text-slate-500 dark:text-slate-400">전체상품 URL</span><a href="<?php echo G5_SHOP_URL; ?>/price/naver.php" target="_blank" class="text-admin-primary-600 hover:underline dark:text-admin-primary-400 break-all"><?php echo G5_SHOP_URL; ?>/price/naver.php</a></li>
                <li class="flex flex-wrap items-baseline gap-2"><span class="shrink-0 w-24 text-slate-500 dark:text-slate-400">요약상품 URL</span><a href="<?php echo G5_SHOP_URL; ?>/price/naver_summary.php" target="_blank" class="text-admin-primary-600 hover:underline dark:text-admin-primary-400 break-all"><?php echo G5_SHOP_URL; ?>/price/naver_summary.php</a></li>
            </ul>
        </div>

        <!-- 구글 쇼핑 -->
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-3 text-sm font-bold text-blue-600 dark:text-blue-400">구글 쇼핑</h3>
            <ul class="ml-4 space-y-1.5 text-sm">
                <li class="flex flex-wrap items-baseline gap-2"><span class="shrink-0 w-32 text-slate-500 dark:text-slate-400">구글 Merchant Center</span><a href="https://www.google.com/intl/ko_kr/retail/solutions/merchant-center" target="_blank" class="text-admin-primary-600 hover:underline dark:text-admin-primary-400 break-all">https://www.google.com/intl/ko_kr/retail/solutions/merchant-center</a></li>
                <li class="flex flex-wrap items-baseline gap-2"><span class="shrink-0 w-32 text-slate-500 dark:text-slate-400">파일 이름</span><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700 dark:bg-slate-700 dark:text-slate-200">google_feed.php</code></li>
                <li class="flex flex-wrap items-baseline gap-2"><span class="shrink-0 w-32 text-slate-500 dark:text-slate-400">파일 URL</span><a href="<?php echo G5_SHOP_URL; ?>/price/google_feed.php" target="_blank" class="text-admin-primary-600 hover:underline dark:text-admin-primary-400 break-all"><?php echo G5_SHOP_URL; ?>/price/google_feed.php</a></li>
            </ul>
            <div class="mt-4 ml-4 rounded-md border border-dashed border-slate-300 p-3 text-xs text-slate-600 dark:border-slate-600 dark:text-slate-300">
                <div class="mb-1 font-semibold text-slate-700 dark:text-slate-200">Feed 설명</div>
                <ul class="space-y-1">
                    <li>판매국가 <b>대한민국</b>, 언어 <b>한국어</b> 설정 기준</li>
                    <li>기본 피드 이름: <b>쇼핑몰피드</b></li>
                    <li>상품 설명: <code class="rounded bg-slate-100 px-1.5 py-0.5 dark:bg-slate-700">it_basic</code> (상품 기본 설명 필수 입력. HTML 태그는 자동 제거됨)</li>
                </ul>
            </div>
        </div>

        <!-- 다음 쇼핑하우 -->
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-3 text-sm font-bold">
                <a href="http://shopping.daum.net/" target="_blank" class="inline-flex items-center gap-1 text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300">다음 쇼핑하우 <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></a>
            </h3>
            <ul class="ml-4 space-y-1.5 text-sm">
                <li class="flex flex-wrap items-baseline gap-2"><span class="shrink-0 w-24 text-slate-500 dark:text-slate-400">입점 안내</span><a href="https://shopping.biz.daum.net/join/main" target="_blank" class="text-admin-primary-600 hover:underline dark:text-admin-primary-400 break-all">https://shopping.biz.daum.net/join/main</a></li>
                <li class="flex flex-wrap items-baseline gap-2"><span class="shrink-0 w-24 text-slate-500 dark:text-slate-400">전체상품 URL</span><a href="<?php echo G5_SHOP_URL; ?>/price/daum.php" target="_blank" class="text-admin-primary-600 hover:underline dark:text-admin-primary-400 break-all"><?php echo G5_SHOP_URL; ?>/price/daum.php</a></li>
                <li class="flex flex-wrap items-baseline gap-2"><span class="shrink-0 w-24 text-slate-500 dark:text-slate-400">요약상품 URL</span><a href="<?php echo G5_SHOP_URL; ?>/price/daum_summary.php" target="_blank" class="text-admin-primary-600 hover:underline dark:text-admin-primary-400 break-all"><?php echo G5_SHOP_URL; ?>/price/daum_summary.php</a></li>
            </ul>
        </div>
    </div>
</section>

<?php
?>
</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php