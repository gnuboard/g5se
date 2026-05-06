<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MSHOP_PATH.'/shop.tail.php');
    return;
}

$admin = get_admin("super");
?>
        </main>  <!-- } .shop-content 끝 -->
    </div>      <!-- } #container 끝 -->
</div>          <!-- } #wrapper 끝 -->

<!-- 하단 (modern) 시작 { -->
<footer id="ft" class="mt-16 border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950">
    <div id="ft_wr" class="mx-auto max-w-screen-xl px-4 py-10">
        <ul id="ft_link" class="ft_cnt mb-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-medium text-slate-700 dark:text-slate-200">
            <li><a href="<?php echo get_pretty_url('content', 'company'); ?>" class="hover:text-admin-primary-600 dark:hover:text-admin-primary-400">회사소개</a></li>
            <li class="text-slate-300 dark:text-slate-700">·</li>
            <li><a href="<?php echo get_pretty_url('content', 'provision'); ?>" class="hover:text-admin-primary-600 dark:hover:text-admin-primary-400">서비스이용약관</a></li>
            <li class="text-slate-300 dark:text-slate-700">·</li>
            <li><a href="<?php echo get_pretty_url('content', 'privacy'); ?>" class="font-semibold text-slate-900 hover:text-admin-primary-600 dark:text-slate-100 dark:hover:text-admin-primary-400">개인정보처리방침</a></li>
            <li class="text-slate-300 dark:text-slate-700">·</li>
            <li><a href="<?php echo get_device_change_url(); ?>" class="hover:text-admin-primary-600 dark:hover:text-admin-primary-400">모바일버전</a></li>
        </ul>

        <div class="grid gap-8 lg:grid-cols-[2fr_1fr_1fr]">
            <div id="ft_company" class="ft_cnt">
                <h2 class="mb-3 text-sm font-bold tracking-tight text-slate-700 dark:text-slate-200">사이트 정보</h2>
                <dl class="ft_info grid grid-cols-1 gap-x-6 gap-y-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400 sm:grid-cols-2">
                    <div><dt class="inline font-semibold text-slate-700 dark:text-slate-200">회사명</dt> <dd class="inline"><?php echo $default['de_admin_company_name']; ?></dd></div>
                    <div><dt class="inline font-semibold text-slate-700 dark:text-slate-200">대표</dt> <dd class="inline"><?php echo $default['de_admin_company_owner']; ?></dd></div>
                    <div class="sm:col-span-2"><dt class="inline font-semibold text-slate-700 dark:text-slate-200">주소</dt> <dd class="inline"><?php echo $default['de_admin_company_addr']; ?></dd></div>
                    <div><dt class="inline font-semibold text-slate-700 dark:text-slate-200">사업자 등록번호</dt> <dd class="inline"><?php echo $default['de_admin_company_saupja_no']; ?></dd></div>
                    <div><dt class="inline font-semibold text-slate-700 dark:text-slate-200">통신판매업신고번호</dt> <dd class="inline"><?php echo $default['de_admin_tongsin_no']; ?></dd></div>
                    <div><dt class="inline font-semibold text-slate-700 dark:text-slate-200">전화</dt> <dd class="inline"><?php echo $default['de_admin_company_tel']; ?></dd></div>
                    <div><dt class="inline font-semibold text-slate-700 dark:text-slate-200">팩스</dt> <dd class="inline"><?php echo $default['de_admin_company_fax']; ?></dd></div>
                    <div class="sm:col-span-2"><dt class="inline font-semibold text-slate-700 dark:text-slate-200">개인정보 보호책임자</dt> <dd class="inline"><?php echo $default['de_admin_info_name']; ?></dd></div>
                    <?php if ($default['de_admin_buga_no']) { ?>
                        <div class="sm:col-span-2"><dt class="inline font-semibold text-slate-700 dark:text-slate-200">부가통신사업신고번호</dt> <dd class="inline"><?php echo $default['de_admin_buga_no']; ?></dd></div>
                    <?php } ?>
                </dl>
            </div>

            <section id="sidx_lat" class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <?php echo latest('theme/notice', 'notice', 5, 30); ?>
            </section>

            <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <?php echo visit('theme/shop_basic'); // 접속자 ?>
            </div>
        </div>
    </div>

    <div id="ft_copy" class="border-t border-slate-200 py-4 text-center text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
        Copyright &copy; 2001-<?php echo date('Y'); ?> <?php echo $default['de_admin_company_name']; ?>. All Rights Reserved.
    </div>
</footer>
<!-- } 하단 끝 -->

<?php
$sec = get_microtime() - $begin_time;
$file = $_SERVER['SCRIPT_NAME'];

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>

<script src="<?php echo G5_JS_URL; ?>/sns.js"></script>

<?php
include_once(G5_THEME_PATH.'/tail.sub.php');
