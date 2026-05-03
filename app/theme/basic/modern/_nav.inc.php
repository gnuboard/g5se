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

global $is_member, $config;

// 메인 nav 링크 — 필요시 여기에 더 추가
$_nav_links = [
    ['/',                       '홈'],
    ['/board/notice',           '공지'],
    ['/board/free',             '자유게시판'],
    ['/new',                    '새글'],
    ['/faq',                    'FAQ'],
];
$_cur_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>

<header class="m-nav">
    <div class="m-nav-inner">
        <a href="<?php echo G5_URL ?>" class="m-brand"><?php echo isset($config['cf_title']) && $config['cf_title'] ? get_text($config['cf_title']) : 'gnu5se' ?></a>

        <nav class="m-nav-primary">
            <?php foreach ($_nav_links as $_link) {
                $href  = $_link[0];
                $label = $_link[1];
                $active = ($href === '/' && $_cur_path === '/') || ($href !== '/' && strpos($_cur_path, parse_url($href, PHP_URL_PATH)) === 0);
            ?>
            <a href="<?php echo $href ?>" class="m-nav-link<?php echo $active ? ' is-active' : '' ?>"><?php echo $label ?></a>
            <?php } ?>
        </nav>

        <form action="/search" method="get" class="m-nav-search" role="search">
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <input type="hidden" name="sop" value="and">
            <svg class="m-nav-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="stx" placeholder="검색" class="m-nav-search-input" maxlength="20">
        </form>

        <div class="m-nav-actions">
            <?php if ($is_member) { ?>
                <a href="<?php echo G5_BBS_URL ?>/logout.php" class="m-btn m-btn-ghost">로그아웃</a>
            <?php } else { ?>
                <a href="<?php echo G5_BBS_URL ?>/login.php" class="m-btn m-btn-ghost">로그인</a>
                <a href="<?php echo G5_BBS_URL ?>/register.php" class="m-btn m-btn-secondary" style="width: auto; padding: 8px 14px;">회원가입</a>
            <?php } ?>
        </div>

        <button type="button" class="m-nav-mobile-toggle" aria-label="메뉴 열기">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
    </div>
</header>

<?php if (!defined('_MODERN_NAV_CSS_LOADED_')) { define('_MODERN_NAV_CSS_LOADED_', true); ?>
<style>
.m-nav-inner {
    display: flex; align-items: center; gap: 16px;
    padding: 12px 20px; max-width: var(--m-max-7xl); margin: 0 auto;
}
.m-brand { flex-shrink: 0; }

.m-nav-primary {
    display: flex; align-items: center; gap: 4px;
    flex-shrink: 0;
}
.m-nav-link {
    padding: 7px 12px; border-radius: var(--m-radius);
    font-size: var(--m-text-base); font-weight: 500;
    color: var(--m-text-soft); text-decoration: none;
    transition: background 0.15s, color 0.15s;
}
.m-nav-link:hover { background: var(--m-surface-2); color: var(--m-text); }
.m-nav-link.is-active { background: var(--m-primary-soft); color: var(--m-primary); }

.m-nav-search {
    flex: 1; max-width: 400px;
    position: relative;
}
.m-nav-search-icon {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: var(--m-text-faint); pointer-events: none;
}
.m-nav-search-input {
    width: 100%; padding: 8px 12px 8px 36px; box-sizing: border-box;
    background: var(--m-surface-2);
    border: 1px solid transparent;
    border-radius: var(--m-radius);
    color: var(--m-text);
    font-size: var(--m-text-base); font-family: inherit;
    outline: none; transition: border-color 0.15s, background 0.15s;
}
.m-nav-search-input:focus {
    border-color: var(--m-primary);
    background: var(--m-surface);
    box-shadow: 0 0 0 3px var(--m-primary-soft);
}
.m-nav-search-input::placeholder { color: var(--m-text-faint); }

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
    .m-nav-primary { display: none; }
    .m-nav-search { max-width: none; }
    .m-nav-actions a:not(.m-btn-secondary) { display: none; }   /* 모바일 좁을 때 로그인 텍스트 버튼 숨김 — 햄버거에서 진입 가정 */
    .m-nav-mobile-toggle { display: inline-flex; }
}
@media (max-width: 540px) {
    .m-nav-search { display: none; }
}
</style>
<?php } ?>
