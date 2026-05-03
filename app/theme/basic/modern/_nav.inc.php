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

        <button type="button" class="m-nav-mobile-toggle" aria-label="메뉴 열기" aria-expanded="false" aria-controls="m-nav-drawer">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
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
                        <a href="/member_confirm" class="m-nav-drawer-edit">정보 수정 →</a>
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

            <nav class="m-nav-drawer-links">
                <?php foreach ($_nav_links as $_link) {
                    $href  = $_link[0];
                    $label = $_link[1];
                    $active = ($href === '/' && $_cur_path === '/') || ($href !== '/' && strpos($_cur_path, parse_url($href, PHP_URL_PATH)) === 0);
                ?>
                <a href="<?php echo $href ?>" class="m-nav-drawer-link<?php echo $active ? ' is-active' : '' ?>"><?php echo $label ?></a>
                <?php } ?>
            </nav>

            <div class="m-nav-drawer-actions">
                <?php if ($is_member) { ?>
                    <?php if ($is_admin) { ?>
                    <a href="<?php echo G5_ADMIN_URL ?>/" class="m-btn m-btn-ghost">관리자</a>
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
    .m-nav-actions { display: none; }   /* 모바일에선 햄버거 드로어 안에서 처리 */
    .m-nav-mobile-toggle { display: inline-flex; }
    /* 사이드바(outlogin) 도 모바일에선 숨김 — 동일 정보를 드로어가 노출 */
    .m-side-col { display: none !important; }
}
@media (max-width: 540px) {
    .m-nav-search { display: none; }
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

.m-nav-drawer-actions {
    display: flex; flex-direction: column; gap: 6px;
    padding: 12px 16px 18px;
    border-top: 1px solid var(--m-border);
}
.m-nav-drawer-actions .m-btn { width: 100%; }
</style>
<?php } ?>
