<?php
if (!defined('_GNUBOARD_')) exit;
if (!defined('_INDEX_')) define('_INDEX_', false);

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
include_once(G5_PATH.'/head.sub.php');

$_use_shop = defined('G5_USE_SHOP') && G5_USE_SHOP;
?>

<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container" style="padding: 24px 20px 48px;">
        <h1 style="font-size: var(--m-text-2xl); margin: 0 0 6px; letter-spacing: -0.01em;">마이페이지</h1>
        <p style="margin: 0 0 24px; color: var(--m-text-soft);"><?php echo $_my['mb_nick'] ?: $_my['mb_id']; ?> 님 환영합니다.</p>

        <!-- 회원 정보 헤더 카드 -->
        <section class="m-card" style="margin-bottom: 20px; padding: 20px;">
            <div class="my-head">
                <div class="my-head-meta">
                    <h2 class="my-head-name"><?php echo $_my['mb_name'] ?: $_my['mb_id']; ?> <span class="my-head-id">(<?php echo $_my['mb_id']; ?>)</span></h2>
                    <p class="my-head-sub">
                        <?php if ($_my['mb_email']) { ?><span><?php echo $_my['mb_email']; ?></span><?php } ?>
                        <?php if ($_my['mb_datetime']) { ?><span class="my-head-since">가입 <?php echo substr($_my['mb_datetime'], 0, 10); ?></span><?php } ?>
                    </p>
                </div>
                <div class="my-head-actions">
                    <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo urlencode(G5_BBS_URL.'/register_form.php'); ?>" class="my-btn">정보수정</a>
                    <a href="<?php echo G5_BBS_URL ?>/logout.php" class="my-btn my-btn-ghost">로그아웃</a>
                </div>
            </div>
        </section>

        <!-- 활동 카드 그리드 -->
        <h2 class="my-section-title">활동</h2>
        <div class="my-grid">
            <a class="my-card" href="<?php echo G5_BBS_URL ?>/point.php" target="win_point" onclick="window.open(this.href, 'win_point', 'left=100,top=100,width=600,height=600,scrollbars=1'); return false;">
                <div class="my-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <div class="my-card-body">
                    <div class="my-card-label">포인트</div>
                    <div class="my-card-value"><?php echo number_format($_my['point']); ?> <em>점</em></div>
                </div>
            </a>
            <a class="my-card" href="<?php echo G5_BBS_URL ?>/memo.php" target="win_memo" onclick="window.open(this.href, 'win_memo', 'left=100,top=100,width=620,height=500,scrollbars=1'); return false;">
                <div class="my-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div class="my-card-body">
                    <div class="my-card-label">쪽지<?php if ($_my_count['memo_unread'] > 0) { ?> <span class="my-card-badge">N</span><?php } ?></div>
                    <div class="my-card-value">
                        <?php echo number_format($_my_count['memo']); ?> <em>건</em>
                        <?php if ($_my_count['memo_unread'] > 0) { ?><small>(읽지않음 <?php echo $_my_count['memo_unread']; ?>)</small><?php } ?>
                    </div>
                </div>
            </a>
            <a class="my-card" href="<?php echo G5_BBS_URL ?>/scrap.php" target="win_scrap" onclick="window.open(this.href, 'win_scrap', 'left=100,top=100,width=600,height=600,scrollbars=1'); return false;">
                <div class="my-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div class="my-card-body">
                    <div class="my-card-label">스크랩</div>
                    <div class="my-card-value"><?php echo number_format($_my_count['scrap']); ?> <em>건</em></div>
                </div>
            </a>
        </div>

        <?php if ($_use_shop) { ?>
        <!-- 쇼핑 카드 그리드 -->
        <h2 class="my-section-title">쇼핑</h2>
        <div class="my-grid">
            <a class="my-card" href="<?php echo G5_SHOP_URL ?>/cart">
                <div class="my-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </div>
                <div class="my-card-body">
                    <div class="my-card-label">장바구니</div>
                    <div class="my-card-value"><?php echo number_format($_my_count['cart']); ?> <em>개</em></div>
                </div>
            </a>
            <a class="my-card" href="<?php echo G5_SHOP_URL ?>/orderinquiry">
                <div class="my-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                </div>
                <div class="my-card-body">
                    <div class="my-card-label">주문내역</div>
                    <div class="my-card-value"><?php echo number_format($_my_count['order']); ?> <em>건</em></div>
                </div>
            </a>
            <a class="my-card" href="<?php echo G5_SHOP_URL ?>/wishlist">
                <div class="my-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <div class="my-card-body">
                    <div class="my-card-label">위시리스트</div>
                    <div class="my-card-value"><?php echo number_format($_my_count['wish']); ?> <em>개</em></div>
                </div>
            </a>
            <a class="my-card" href="<?php echo G5_SHOP_URL ?>/coupon.php" target="win_coupon" onclick="window.open(this.href, 'win_coupon', 'left=100,top=100,width=700,height=600,scrollbars=1'); return false;">
                <div class="my-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg>
                </div>
                <div class="my-card-body">
                    <div class="my-card-label">쿠폰</div>
                    <div class="my-card-value"><?php echo number_format($_my_count['coupon']); ?> <em>장</em></div>
                </div>
            </a>
            <a class="my-card" href="<?php echo G5_SHOP_URL ?>/orderaddress.php" target="win_address" onclick="window.open(this.href, 'win_address', 'left=100,top=100,width=800,height=600,scrollbars=1'); return false;">
                <div class="my-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div class="my-card-body">
                    <div class="my-card-label">배송지</div>
                    <div class="my-card-value"><?php echo number_format($_my_count['address']); ?> <em>곳</em></div>
                </div>
            </a>
            <a class="my-card" href="<?php echo G5_SHOP_URL ?>/itemuselist">
                <div class="my-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div class="my-card-body">
                    <div class="my-card-label">사용후기</div>
                    <div class="my-card-value">전체보기 →</div>
                </div>
            </a>
        </div>
        <?php } ?>
    </main>

    <?php require G5_THEME_PATH.'/modern/_footer.inc.php'; ?>
</div>

<style>
.my-head { display: flex; gap: 16px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
.my-head-meta { min-width: 0; flex: 1; }
.my-head-name { margin: 0; font-size: 1.2em; color: var(--m-text); font-weight: 700; }
.my-head-id { color: var(--m-text-soft); font-weight: 400; font-size: 0.85em; margin-left: 4px; }
.my-head-sub { margin: 4px 0 0; color: var(--m-text-soft); font-size: 0.92em; display: flex; gap: 12px; flex-wrap: wrap; }
.my-head-since { color: var(--m-text-faint); }
.my-head-actions { display: flex; gap: 6px; flex-wrap: wrap; }
/* a.my-btn 으로 specificity 올림 + !important — default.css 의 a{color:#000} 등 기타 cascade 영향 차단 */
a.my-btn, a.my-btn:link, a.my-btn:visited, a.my-btn:hover, a.my-btn:active {
    display: inline-flex; align-items: center; padding: 8px 16px;
    background: var(--m-primary) !important;
    color: #fff !important;
    border: 1px solid var(--m-primary);
    border-radius: 6px; font-size: 0.9em; font-weight: 700;
    text-decoration: none;
}
a.my-btn:hover { opacity: 0.92; }
a.my-btn-ghost, a.my-btn-ghost:link, a.my-btn-ghost:visited, a.my-btn-ghost:hover {
    background: var(--m-surface-2) !important;
    color: var(--m-text) !important;
    border-color: var(--m-border);
}

.my-section-title {
    margin: 28px 0 12px; font-size: 1em; font-weight: 600; color: var(--m-text);
}
.my-grid {
    display: grid; gap: 12px;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}
.my-card {
    display: flex; align-items: center; gap: 14px;
    padding: 16px 18px;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: 10px;
    text-decoration: none;
    transition: border-color 0.15s, transform 0.15s, box-shadow 0.15s;
}
.my-card:hover {
    border-color: var(--m-primary);
    transform: translateY(-1px);
    box-shadow: var(--m-shadow);
}
.my-card-icon {
    flex-shrink: 0; width: 40px; height: 40px;
    display: flex; align-items: center; justify-content: center;
    background: var(--m-surface-2);
    border-radius: 50%;
    color: var(--m-primary);
}
.my-card-icon svg { width: 20px; height: 20px; }
.my-card-body { min-width: 0; flex: 1; }
.my-card-label {
    font-size: 0.85em; color: var(--m-text-soft); font-weight: 500;
    display: flex; align-items: center; gap: 6px;
}
.my-card-value {
    font-size: 1.15em; font-weight: 700; color: var(--m-text);
    margin-top: 2px;
}
.my-card-value em { font-size: 0.7em; font-weight: 500; color: var(--m-text-soft); font-style: normal; margin-left: 2px; }
.my-card-value small { font-size: 0.6em; font-weight: 400; color: var(--m-text-soft); margin-left: 4px; }
.my-card-badge {
    display: inline-block; min-width: 14px; padding: 0 4px;
    background: #ef4444; color: #fff;
    border-radius: 999px; font-size: 0.7em; font-weight: 700; line-height: 14px;
    text-align: center;
}
</style>

<?php include_once(G5_PATH.'/tail.sub.php'); ?>
