<?php
/*
 * 모던 nav 헤더 — 모든 페이지 상단에 일관된 디자인 제공.
 * 사용: require G5_THEME_PATH.'/modern/_nav.inc.php';
 *
 * 구성:
 *   - 좌측: 브랜드 + 1차 nav 링크 (게시판/새글/검색 등)
 *   - 중앙: 검색 인풋
 *   - 우측: 로그인 상태별 액션 + 다크모드 토글(자동주입)
 */

if (!defined('_GNUBOARD_')) return;

global $is_member, $is_admin, $member, $config;

// gnuboard 의 g5_menu (관리자 → 환경설정 → 메뉴설정) 에서 등록된 메뉴를 동적으로 출력.
// me_link 가 외부 도메인 (예: 데모 install 의 clcode.gnuboard.net) 이면 path/query 만 추출해
// 우리 호스트 안의 클린 URL 로 동작하게 만든다 (target 은 self/blank 그대로 유지).
$_nav_menu = function_exists('get_menu_db') ? get_menu_db(0, true) : [];
$_nav_host = $_SERVER['HTTP_HOST'] ?? '';
$_nav_normalize = function ($url) use ($_nav_host) {
    if ($url === '' || $url === '#') return '#';
    $u = parse_url($url);
    // host 가 비어있으면 이미 path 형태 — 그대로
    if (empty($u['host'])) return $url;
    // 같은 호스트면 path[?query][#fragment] 만
    if ($u['host'] === $_nav_host) {
        $rebuilt = $u['path'] ?? '/';
        if (!empty($u['query']))    $rebuilt .= '?' . $u['query'];
        if (!empty($u['fragment'])) $rebuilt .= '#' . $u['fragment'];
        return $rebuilt;
    }
    // 외부 호스트는 그대로 (target 으로 새창 처리)
    return $url;
};
$_nav_is_external = function ($url) use ($_nav_host) {
    $u = parse_url($url);
    return !empty($u['host']) && $u['host'] !== $_nav_host;
};

$_cur_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>

<header class="m-nav">
    <!-- Row 1: 브랜드 + 검색 + (커뮤니티/쇼핑몰 segment) + 로그인 액션 + 햄버거 -->
    <div class="m-nav-row m-nav-row-top">
        <div class="m-nav-row-inner">
            <a href="<?php echo G5_URL ?>" class="m-brand"><?php echo isset($config['cf_title']) && $config['cf_title'] ? get_text($config['cf_title']) : 'g5se' ?></a>

            <?php if (defined('G5_COMMUNITY_USE') && G5_COMMUNITY_USE && defined('G5_USE_SHOP') && G5_USE_SHOP) { ?>
            <nav class="m-nav-segment" aria-label="섹션 전환">
                <a href="<?php echo G5_URL ?>/" class="m-nav-segment-item<?php echo (strpos($_cur_path, '/shop') !== 0) ? ' is-active' : '' ?>">커뮤니티</a>
                <a href="<?php echo G5_SHOP_URL ?>/" class="m-nav-segment-item<?php echo (strpos($_cur_path, '/shop') === 0) ? ' is-active' : '' ?>">쇼핑몰</a>
            </nav>
            <?php } ?>

            <div class="m-nav-spacer"></div>

            <a href="/search" class="m-nav-search-icon-btn" title="검색" aria-label="검색">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </a>

            <button type="button" class="m-theme-toggle" aria-label="테마 전환" title="테마 전환">
                <svg class="m-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg class="m-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            </button>

            <nav class="m-nav-utility" aria-label="유틸 메뉴">
                <a href="/faq" class="m-nav-utility-link<?php echo strpos($_cur_path, '/faq') === 0 ? ' is-active' : '' ?>">FAQ</a>
                <a href="/qa"  class="m-nav-utility-link<?php echo strpos($_cur_path, '/qa') === 0 ? ' is-active' : '' ?>">Q&amp;A</a>
                <?php /* 새글 / 접속자 는 footer 로 이동 (m-footer 의 사이트/접속자 통계 컬럼) */ ?>
            </nav>

            <?php /* 장바구니 아이콘 — shop 활성 시 항상 노출, 아이템 수 badge */
            if (defined('G5_USE_SHOP') && G5_USE_SHOP) { ?>
            <a href="<?php echo G5_SHOP_URL ?>/cart" class="m-nav-cart-link m-nav-shop-cart" aria-label="장바구니" title="장바구니">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <?php
                // 카트 아이템 수 (세션에 저장된 ss_cart_id 의 ct 카운트)
                $_nav_cart_count = 0;
                $_nav_cart_id = function_exists('get_session') ? get_session('ss_cart_id') : '';
                if ($_nav_cart_id && function_exists('sql_pdo_fetch')) {
                    $_r = @sql_pdo_fetch(" select count(*) as cnt from {$g5['g5_shop_cart_table']} where od_id = :od_id ", [':od_id' => $_nav_cart_id]);
                    $_nav_cart_count = (int)($_r['cnt'] ?? 0);
                }
                if ($_nav_cart_count > 0) { ?>
                <span class="m-nav-cart-badge"><?php echo $_nav_cart_count > 99 ? '99+' : $_nav_cart_count ?></span>
                <?php } ?>
            </a>
            <?php } ?>

            <?php /* 마이페이지 사람 아이콘 — 로그인 상태일 때만 노출. 통합 mypage hub */
            if (!empty($is_member)) { ?>
            <a href="/mypage" class="m-nav-cart-link m-nav-mypage-link" aria-label="마이페이지" title="마이페이지">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>
            <?php } ?>

            <div class="m-nav-actions">
                <?php if ($is_member) { ?>
                    <a href="<?php echo G5_BBS_URL ?>/logout.php" class="m-btn m-btn-ghost">로그아웃</a>
                <?php } else { ?>
                    <a href="<?php echo G5_BBS_URL ?>/login.php" class="m-btn m-btn-ghost">로그인</a>
                    <a href="<?php echo G5_BBS_URL ?>/register.php" class="m-btn m-btn-secondary" style="width: auto; padding: 8px 14px;">회원가입</a>
                <?php } ?>
            </div>

            <button type="button" class="m-nav-mobile-toggle" aria-label="메뉴 열기" aria-expanded="false" aria-controls="m-nav-drawer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
    </div>

    <!-- Row 2: 메인 nav 링크 (홈 + g5_menu + 새글 + FAQ) -->
    <div class="m-nav-row m-nav-row-links">
        <nav class="m-nav-row-inner m-nav-primary" aria-label="메인 메뉴">
            <a href="/" class="m-nav-link<?php echo $_cur_path === '/' ? ' is-active' : '' ?>">홈</a>
            <?php foreach ($_nav_menu as $_row) {
                if (empty($_row)) continue;
                $href = $_nav_normalize($_row['me_link']);
                $href_path = parse_url($href, PHP_URL_PATH) ?? '';
                $active = ($href_path !== '' && $href_path !== '/' && strpos($_cur_path, $href_path) === 0);
                $external = $_nav_is_external($_row['me_link']);
                $target = ($_row['me_target'] === 'blank' || $external) ? '_blank' : '_self';
                $has_sub = !empty($_row['sub']);
            ?>
            <div class="m-nav-item<?php echo $has_sub ? ' has-sub' : '' ?>">
                <a href="<?php echo $href ?>" target="<?php echo $target ?>"
                   class="m-nav-link<?php echo $active ? ' is-active' : '' ?>"><?php echo get_text($_row['me_name']) ?><?php if ($has_sub) { ?>
                    <svg class="m-nav-chev" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                <?php } ?></a>
                <?php if ($has_sub) { ?>
                <div class="m-nav-sub" role="menu">
                    <?php foreach ((array)$_row['sub'] as $_sub) {
                        if (empty($_sub)) continue;
                        $sub_href = $_nav_normalize($_sub['me_link']);
                        $sub_external = $_nav_is_external($_sub['me_link']);
                        $sub_target = ($_sub['me_target'] === 'blank' || $sub_external) ? '_blank' : '_self';
                    ?>
                    <a href="<?php echo $sub_href ?>" target="<?php echo $sub_target ?>" class="m-nav-sub-link" role="menuitem"><?php echo get_text($_sub['me_name']) ?></a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </nav>
    </div>

    <!-- 모바일 드로어: 햄버거 클릭 시 열림. 데스크톱에선 hidden. -->
    <div id="m-nav-drawer" class="m-nav-drawer" hidden>
        <div class="m-nav-drawer-backdrop" data-nav-close></div>
        <aside class="m-nav-drawer-panel" role="dialog" aria-label="메뉴">
            <header class="m-nav-drawer-head">
                <span class="m-nav-drawer-title">메뉴</span>
                <button type="button" class="m-nav-drawer-close" aria-label="메뉴 닫기" data-nav-close>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </header>

            <?php if ($is_member) { ?>
            <section class="m-nav-drawer-me">
                <div class="m-nav-drawer-me-row">
                    <span class="m-nav-drawer-avatar">
                        <?php $_drawer_nick = get_text($member['mb_nick']); echo mb_substr($_drawer_nick, 0, 1); ?>
                    </span>
                    <div class="m-nav-drawer-me-text">
                        <div class="m-nav-drawer-nick"><?php echo $_drawer_nick ?> 님</div>
                        <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo urlencode(G5_BBS_URL.'/register_form.php'); ?>" class="m-nav-drawer-edit">정보 수정 →</a>
                    </div>
                </div>
                <ul class="m-nav-drawer-stats">
                    <li><a href="/point" onclick="win_point(this.href); return false;">
                        <span class="m-nav-drawer-stat-label">포인트</span>
                        <span class="m-nav-drawer-stat-value"><?php echo number_format((int)$member['mb_point']) ?></span>
                    </a></li>
                    <li><a href="/memo" onclick="win_memo(this.href); return false;">
                        <span class="m-nav-drawer-stat-label">쪽지</span>
                        <span class="m-nav-drawer-stat-value"><?php echo isset($member['mb_memo_cnt']) ? (int)$member['mb_memo_cnt'] : 0 ?></span>
                    </a></li>
                    <li><a href="/scrap" onclick="win_scrap(this.href); return false;">
                        <span class="m-nav-drawer-stat-label">스크랩</span>
                        <span class="m-nav-drawer-stat-value"><?php echo isset($member['mb_scrap_cnt']) ? (int)$member['mb_scrap_cnt'] : 0 ?></span>
                    </a></li>
                </ul>
            </section>
            <?php } ?>

            <?php if (defined('G5_COMMUNITY_USE') && G5_COMMUNITY_USE && defined('G5_USE_SHOP') && G5_USE_SHOP) { ?>
            <nav class="m-nav-drawer-segment" aria-label="섹션 전환">
                <a href="<?php echo G5_URL ?>/" class="m-nav-drawer-segment-item<?php echo (strpos($_cur_path, '/shop') !== 0) ? ' is-active' : '' ?>">커뮤니티</a>
                <a href="<?php echo G5_SHOP_URL ?>/" class="m-nav-drawer-segment-item<?php echo (strpos($_cur_path, '/shop') === 0) ? ' is-active' : '' ?>">쇼핑몰</a>
            </nav>
            <?php } ?>

            <?php if (defined('G5_USE_SHOP') && G5_USE_SHOP) {
                $_nav_cart_count = isset($_nav_cart_count) ? (int)$_nav_cart_count : 0;
            ?>
            <nav class="m-nav-drawer-shop" aria-label="쇼핑몰 메뉴">
                <a href="<?php echo G5_SHOP_URL ?>/cart" class="m-nav-drawer-shop-link<?php echo strpos($_cur_path, '/shop/cart') === 0 ? ' is-active' : '' ?>">
                    <span>장바구니</span>
                    <?php if ($_nav_cart_count > 0) { ?>
                    <b class="m-nav-drawer-shop-badge"><?php echo $_nav_cart_count > 99 ? '99+' : $_nav_cart_count ?></b>
                    <?php } ?>
                </a>
                <a href="<?php echo G5_SHOP_URL ?>/wishlist" class="m-nav-drawer-shop-link<?php echo strpos($_cur_path, '/shop/wishlist') === 0 ? ' is-active' : '' ?>">위시리스트</a>
                <a href="<?php echo G5_SHOP_URL ?>/orderinquiry" class="m-nav-drawer-shop-link<?php echo strpos($_cur_path, '/shop/orderinquiry') === 0 ? ' is-active' : '' ?>">주문내역</a>
                <a href="/mypage" class="m-nav-drawer-shop-link<?php echo $_cur_path === '/mypage' ? ' is-active' : '' ?>">마이페이지</a>
            </nav>
            <?php } ?>

            <nav class="m-nav-drawer-links">
                <a href="/" class="m-nav-drawer-link<?php echo $_cur_path === '/' ? ' is-active' : '' ?>">홈</a>
                <?php foreach ($_nav_menu as $_row) {
                    if (empty($_row)) continue;
                    $href = $_nav_normalize($_row['me_link']);
                    $href_path = parse_url($href, PHP_URL_PATH) ?? '';
                    $active = ($href_path !== '' && $href_path !== '/' && strpos($_cur_path, $href_path) === 0);
                    $external = $_nav_is_external($_row['me_link']);
                    $target = ($_row['me_target'] === 'blank' || $external) ? '_blank' : '_self';
                ?>
                <a href="<?php echo $href ?>" target="<?php echo $target ?>" class="m-nav-drawer-link<?php echo $active ? ' is-active' : '' ?>"><?php echo get_text($_row['me_name']) ?></a>
                <?php foreach ((array)$_row['sub'] as $_sub) {
                    if (empty($_sub)) continue;
                    $sub_href = $_nav_normalize($_sub['me_link']);
                    $sub_external = $_nav_is_external($_sub['me_link']);
                    $sub_target = ($_sub['me_target'] === 'blank' || $sub_external) ? '_blank' : '_self';
                    $sub_path = parse_url($sub_href, PHP_URL_PATH) ?? '';
                    $sub_active = ($sub_path !== '' && $sub_path !== '/' && strpos($_cur_path, $sub_path) === 0);
                ?>
                <a href="<?php echo $sub_href ?>" target="<?php echo $sub_target ?>" class="m-nav-drawer-link m-nav-drawer-sublink<?php echo $sub_active ? ' is-active' : '' ?>"><?php echo get_text($_sub['me_name']) ?></a>
                <?php } ?>
                <?php } ?>
                <a href="/faq"     class="m-nav-drawer-link<?php echo strpos($_cur_path, '/faq') === 0 ? ' is-active' : '' ?>">FAQ</a>
                <a href="/qa"      class="m-nav-drawer-link<?php echo strpos($_cur_path, '/qa') === 0 ? ' is-active' : '' ?>">Q&amp;A</a>
                <a href="/new"     class="m-nav-drawer-link<?php echo strpos($_cur_path, '/new') === 0 ? ' is-active' : '' ?>">새글</a>
                <a href="/connect" class="m-nav-drawer-link<?php echo strpos($_cur_path, '/connect') === 0 ? ' is-active' : '' ?>">접속자</a>
                <a href="/search"  class="m-nav-drawer-link<?php echo strpos($_cur_path, '/search') === 0 ? ' is-active' : '' ?>">검색</a>
            </nav>

            <div class="m-nav-drawer-actions">
                <?php if ($is_member) { ?>
                    <?php if ($is_admin) { ?>
                    <a href="/admin" class="m-btn m-btn-ghost">관리자</a>
                    <?php } ?>
                    <a href="<?php echo G5_BBS_URL ?>/logout.php" class="m-btn m-btn-ghost">로그아웃</a>
                <?php } else { ?>
                    <a href="<?php echo G5_BBS_URL ?>/login.php" class="m-btn m-btn-ghost">로그인</a>
                    <a href="<?php echo G5_BBS_URL ?>/register.php" class="m-btn">회원가입</a>
                <?php } ?>
            </div>
        </aside>
    </div>
</header>

<script>
(function(){
    var nav = document.querySelector('.m-nav');
    var shell = nav ? nav.closest('.m-shell') : null;
    function syncNavHeight(){
        if (!nav || !shell) return;
        shell.style.setProperty('--m-nav-height', Math.ceil(nav.getBoundingClientRect().height) + 'px');
    }
    syncNavHeight();
    if (nav && window.ResizeObserver) {
        new ResizeObserver(syncNavHeight).observe(nav);
    } else {
        window.addEventListener('resize', syncNavHeight);
    }

    var drawer = document.getElementById('m-nav-drawer');
    var toggle = document.querySelector('.m-nav-mobile-toggle');
    if (!drawer || !toggle) return;
    function open(){
        drawer.removeAttribute('hidden');
        toggle.setAttribute('aria-expanded', 'true');
        document.documentElement.style.overflow = 'hidden';
    }
    function close(){
        drawer.setAttribute('hidden', '');
        toggle.setAttribute('aria-expanded', 'false');
        document.documentElement.style.overflow = '';
    }
    toggle.addEventListener('click', open);
    drawer.addEventListener('click', function(e){
        if (e.target.closest('[data-nav-close]')) close();
    });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && !drawer.hasAttribute('hidden')) close();
    });
})();
</script>

<?php if (!defined('_MODERN_NAV_CSS_LOADED_')) { define('_MODERN_NAV_CSS_LOADED_', true); ?>
<style>
/* 2-row 헤더 — row 1: 브랜드+검색+액션, row 2: 메뉴 링크 */
.m-nav { background: var(--m-bg); border-bottom: 1px solid var(--m-border); }
.m-nav-row-inner {
    display: flex; align-items: center; gap: 16px;
    width: 100%; min-width: 0;
    padding: 12px 20px; max-width: var(--m-max-7xl); margin: 0 auto;
}
.m-nav-row-top { border-bottom: 1px solid var(--m-border); }
.m-nav-row-links { background: var(--m-surface); }
.m-brand {
    flex-shrink: 1;
    min-width: 0;
    max-width: 42vw;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 700;
}

.m-nav-primary {
    flex-wrap: wrap; gap: 2px;
    padding: 6px 20px;
}
.m-nav-link {
    padding: 7px 12px; border-radius: var(--m-radius);
    font-size: var(--m-text-base); font-weight: 500;
    color: var(--m-text-soft); text-decoration: none;
    transition: background 0.15s, color 0.15s;
}
.m-nav-link:hover { background: var(--m-surface-2); color: var(--m-text); }
.m-nav-link.is-active { background: var(--m-primary-soft); color: var(--m-primary); }

/* g5_menu 1차 항목 + 하위 드롭다운 */
.m-nav-item { position: relative; }
.m-nav-link { display: inline-flex; align-items: center; gap: 4px; }
.m-nav-chev { color: var(--m-text-faint); transition: transform 0.15s; }
.m-nav-item.has-sub:hover .m-nav-chev { transform: rotate(180deg); color: var(--m-primary); }
.m-nav-sub {
    position: absolute; top: calc(100% + 4px); left: 0;
    min-width: 180px; padding: 6px;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    box-shadow: var(--m-shadow-md);
    display: none; z-index: 200;
}
.m-nav-item.has-sub:hover .m-nav-sub,
.m-nav-item.has-sub:focus-within .m-nav-sub { display: block; }
.m-nav-sub-link {
    display: block;
    padding: 8px 12px; border-radius: var(--m-radius-sm);
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    text-decoration: none; white-space: nowrap;
    transition: background 0.15s, color 0.15s;
}
.m-nav-sub-link:hover { background: var(--m-primary-soft); color: var(--m-primary); }

/* 커뮤니티 / 쇼핑몰 segment 토글 */
.m-nav-segment {
    display: inline-flex; padding: 3px;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    border-radius: var(--m-radius); flex-shrink: 0;
}
.m-nav-segment-item {
    padding: 5px 12px; border-radius: var(--m-radius-sm);
    font-size: var(--m-text-sm); font-weight: 500;
    color: var(--m-text-soft); text-decoration: none;
    transition: background 0.15s, color 0.15s;
}
.m-nav-segment-item.is-active {
    /* primary tint 로 통일 — dark 에서 surface 가 surface-2 보다 어두워 invert 되는 문제 회피 */
    background: var(--m-primary-soft); color: var(--m-primary);
    box-shadow: var(--m-shadow);
}

.m-nav-spacer { flex: 1; min-width: 0; }

.m-nav-search-icon-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px; flex-shrink: 0;
    background: transparent; border: 1px solid transparent;
    border-radius: var(--m-radius); color: var(--m-text-soft);
    text-decoration: none;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.m-nav-search-icon-btn:hover {
    background: var(--m-surface-2);
    color: var(--m-primary);
}

.m-nav-utility {
    display: inline-flex; align-items: center; gap: 2px;
    flex-shrink: 0;
}
.m-nav-utility-link {
    padding: 6px 10px; border-radius: var(--m-radius);
    font-size: var(--m-text-base); font-weight: 500;
    color: var(--m-text-soft); text-decoration: none;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}
.m-nav-utility-link:hover { background: var(--m-surface-2); color: var(--m-text); }
.m-nav-utility-link.is-active { color: var(--m-primary); }

/* 장바구니 아이콘 (top nav) — badge 포함, shop 진입 여부와 무관하게 노출 */
.m-nav-cart-link {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px; height: 36px;
    border: 1px solid transparent;
    border-radius: var(--m-radius);
    color: var(--m-text-soft);
    background: transparent;
    text-decoration: none;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.m-nav-cart-link:hover {
    background: var(--m-surface-2);
    color: var(--m-text);
}
.m-nav-cart-link svg { display: block; }
.m-nav-cart-badge {
    position: absolute;
    top: -6px; right: -6px;
    min-width: 18px; height: 18px;
    padding: 0 5px;
    background: var(--m-primary);
    color: #fff;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    line-height: 18px;
    text-align: center;
    box-sizing: border-box;
}

.m-nav-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

.m-nav-mobile-toggle {
    display: none;
    width: 36px; height: 36px; padding: 0;
    background: transparent; border: 1px solid var(--m-border);
    border-radius: var(--m-radius); color: var(--m-text-soft);
    cursor: pointer;
    align-items: center; justify-content: center;
}

@media (max-width: 880px) {
    .m-nav-row-links { display: none; }   /* row 2 메뉴는 햄버거 드로어가 흡수 */
    .m-nav-segment { display: none; }     /* 커뮤니티/쇼핑몰 토글도 드로어로 */
    .m-nav-utility { display: none; }     /* FAQ/Q&A/새글/접속자 도 드로어로 */
    .m-nav-search-icon-btn { margin-left: auto; }
    .m-nav-actions { display: none; }  /* 로그인/회원가입 버튼은 드로어로 흡수, 다크모드 토글은 우하단 .m-float-actions 로 이동 */
    .m-nav-mobile-toggle { display: inline-flex; }
    /* 사이드바(outlogin) 도 모바일에선 숨김 — 동일 정보를 드로어가 노출 */
    .m-side-col { display: none !important; }
}

@media (max-width: 560px) {
    .m-nav-row-inner { gap: 4px; padding: 12px 14px; }
    .m-brand { max-width: calc(100vw - 180px); }
    .m-nav-cart-link { display: none; }
    .m-nav-shop-cart { display: inline-flex; }
}

/* 모바일 드로어 — 우측 슬라이드 인 패널 */
.m-nav-drawer {
    position: fixed; inset: 0; z-index: 10000;
}
.m-nav-drawer-backdrop {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(2px);
    animation: m-nav-fade-in 0.2s ease-out;
}
.m-nav-drawer-panel {
    position: absolute; top: 0; right: 0; bottom: 0;
    width: min(320px, 85vw);
    background: var(--m-bg);
    border-left: 1px solid var(--m-border);
    overflow-y: auto;
    display: flex; flex-direction: column;
    animation: m-nav-slide-in 0.22s ease-out;
}
@keyframes m-nav-fade-in { from { opacity: 0 } to { opacity: 1 } }
@keyframes m-nav-slide-in { from { transform: translateX(100%) } to { transform: translateX(0) } }

.m-nav-drawer-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid var(--m-border);
}
.m-nav-drawer-title { font-size: var(--m-text-base); font-weight: 600; color: var(--m-text-soft); }
.m-nav-drawer-close {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px;
    background: transparent; border: 1px solid var(--m-border);
    border-radius: var(--m-radius); color: var(--m-text-soft);
    cursor: pointer;
}
.m-nav-drawer-close:hover { background: var(--m-surface-2); color: var(--m-text); }

.m-nav-drawer-me {
    padding: 16px;
    border-bottom: 1px solid var(--m-border);
    background: var(--m-surface);
}
.m-nav-drawer-me-row { display: flex; align-items: center; gap: 12px; }
.m-nav-drawer-avatar {
    display: inline-flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; flex-shrink: 0;
    border-radius: 50%;
    background: var(--m-primary); color: #fff;
    font-size: var(--m-text-md); font-weight: 700;
}
.m-nav-drawer-me-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.m-nav-drawer-nick { font-size: var(--m-text-md); font-weight: 600; color: var(--m-text); }
.m-nav-drawer-edit { font-size: var(--m-text-xs); color: var(--m-text-muted); text-decoration: none; }
.m-nav-drawer-edit:hover { color: var(--m-primary); }

.m-nav-drawer-stats {
    list-style: none; margin: 12px 0 0; padding: 0;
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;
}
.m-nav-drawer-stats a {
    display: flex; flex-direction: column; align-items: center; gap: 2px;
    padding: 8px 4px; border-radius: var(--m-radius);
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    text-decoration: none;
}
.m-nav-drawer-stats a:hover { border-color: var(--m-primary); }
.m-nav-drawer-stat-label { font-size: var(--m-text-xs); color: var(--m-text-muted); }
.m-nav-drawer-stat-value { font-size: var(--m-text-md); font-weight: 700; color: var(--m-text); font-feature-settings: "tnum"; }

.m-nav-drawer-links {
    display: flex; flex-direction: column;
    padding: 8px 8px;
    flex: 1; min-height: 0;
}
.m-nav-drawer-link {
    padding: 12px 12px; border-radius: var(--m-radius);
    font-size: var(--m-text-md); font-weight: 500;
    color: var(--m-text-soft); text-decoration: none;
}
.m-nav-drawer-link:hover { background: var(--m-surface-2); color: var(--m-text); }
.m-nav-drawer-link.is-active { background: var(--m-primary-soft); color: var(--m-primary); }
.m-nav-drawer-sublink { padding-left: 28px !important; font-size: var(--m-text-sm) !important; }

/* 드로어 안 segment 토글 */
.m-nav-drawer-segment {
    display: flex; gap: 4px; padding: 3px;
    margin: 12px 12px 0;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
}
.m-nav-drawer-segment-item {
    flex: 1; text-align: center;
    padding: 7px 10px; border-radius: var(--m-radius-sm);
    font-size: var(--m-text-sm); font-weight: 500;
    color: var(--m-text-soft); text-decoration: none;
}
.m-nav-drawer-segment-item.is-active {
    background: var(--m-primary-soft); color: var(--m-primary);
    box-shadow: var(--m-shadow);
}

.m-nav-drawer-shop {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px;
    padding: 12px;
    border-bottom: 1px solid var(--m-border);
}
.m-nav-drawer-shop-link {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    padding: 8px 10px;
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    background: var(--m-surface);
    color: var(--m-text-soft);
    font-size: var(--m-text-sm);
    font-weight: 600;
    text-decoration: none;
}
.m-nav-drawer-shop-link:hover,
.m-nav-drawer-shop-link.is-active {
    border-color: var(--m-primary);
    background: var(--m-primary-soft);
    color: var(--m-primary);
}
.m-nav-drawer-shop-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: var(--m-primary);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    line-height: 18px;
    text-align: center;
}

.m-nav-drawer-actions {
    display: flex; flex-direction: column; gap: 6px;
    padding: 12px 16px 18px;
    border-top: 1px solid var(--m-border);
}
.m-nav-drawer-actions .m-btn { width: 100%; }
</style>
<?php } ?>
