<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 현재접속자 시작 { -->
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">
            <header class="m-connect-head">
                <h1 class="m-connect-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    현재 접속자
                </h1>
                <p class="m-connect-sub">
                    총 <strong><?php echo count($list) ?></strong>명이 접속 중입니다.
                </p>
            </header>

            <?php if (count($list) === 0) { ?>
            <div class="m-card m-connect-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p>현재 접속 중인 사용자가 없습니다.</p>
            </div>
            <?php } else { ?>
            <ul class="m-connect-list">
                <?php for ($i = 0; $i < count($list); $i++) {
                    $location = $list[$i]['lo_location'];
                    if ($list[$i]['lo_url'] && $is_admin == 'super') {
                        $display_location = '<a href="'.$list[$i]['lo_url'].'">'.$location.'</a>';
                    } else {
                        $display_location = $location;
                    }
                    $is_member_row = !empty($list[$i]['mb_id']);
                ?>
                <li class="m-connect-item<?php echo $is_member_row ? ' is-member' : '' ?>">
                    <span class="m-connect-num"><?php echo $list[$i]['num'] ?></span>
                    <span class="m-connect-avatar">
                        <?php if ($is_member_row) { ?>
                            <?php echo get_member_profile_img($list[$i]['mb_id']); ?>
                        <?php } else { ?>
                            <span class="m-connect-guest" title="비회원">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/></svg>
                            </span>
                        <?php } ?>
                    </span>
                    <div class="m-connect-info">
                        <span class="m-connect-name"><?php echo $list[$i]['name'] ?></span>
                        <?php if ($display_location) { ?>
                        <span class="m-connect-location"><?php echo $display_location ?></span>
                        <?php } ?>
                    </div>
                </li>
                <?php } ?>
            </ul>
            <?php } ?>
        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>

    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>

<style>
.m-connect-head { margin-bottom: 18px; }
.m-connect-title {
    display: flex; align-items: center; gap: 8px;
    margin: 0 0 4px;
    font-size: var(--m-text-2xl); font-weight: 700;
    color: var(--m-text); letter-spacing: -0.01em;
}
.m-connect-title svg { color: var(--m-primary); flex-shrink: 0; }
.m-connect-sub { margin: 0; font-size: var(--m-text-sm); color: var(--m-text-muted); }
.m-connect-sub strong { color: var(--m-primary); font-weight: 700; }

.m-connect-empty {
    padding: 60px 24px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.m-connect-empty svg { color: var(--m-text-faint); margin-bottom: 4px; }
.m-connect-empty p { margin: 0; color: var(--m-text-muted); font-size: var(--m-text-md); }

.m-connect-list {
    list-style: none; margin: 0; padding: 0;
    display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 8px;
}
.m-connect-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 14px;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    transition: border-color 0.15s, background 0.15s;
}
.m-connect-item:hover { border-color: var(--m-border-hover); background: var(--m-surface-2); }
.m-connect-item.is-member { border-left: 3px solid var(--m-primary); padding-left: 11px; }

.m-connect-num {
    flex-shrink: 0;
    font-size: var(--m-text-xs); font-weight: 700;
    color: var(--m-text-faint);
    font-feature-settings: "tnum"; min-width: 28px;
}
.m-connect-avatar { display: inline-flex; align-items: center; flex-shrink: 0; }
.m-connect-avatar img {
    width: 32px; height: 32px; border-radius: 50%;
    border: 1px solid var(--m-border); object-fit: cover;
}
.m-connect-guest {
    width: 32px; height: 32px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    color: var(--m-text-faint);
}
.m-connect-info {
    flex: 1; min-width: 0;
    display: flex; flex-direction: column; gap: 2px;
}
.m-connect-name {
    font-size: var(--m-text-base); font-weight: 600; color: var(--m-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-connect-location {
    font-size: var(--m-text-xs); color: var(--m-text-muted);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-connect-location a { color: inherit; text-decoration: none; }
.m-connect-location a:hover { color: var(--m-primary); text-decoration: underline; }
</style>
<!-- } 현재접속자 끝 -->
