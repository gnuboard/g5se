<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');

$can_see = ($member['mb_level'] >= $mb['mb_level']);
?>

<!-- 자기소개 시작 { -->
<div class="m-popup">
    <header class="m-popup-head">
        <h1 class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <strong><?php echo $mb_nick ?></strong>님의 프로필
        </h1>
    </header>

    <section class="m-profile-card">
        <div class="m-profile-avatar"><?php echo get_member_profile_img($mb['mb_id']); ?></div>
        <div class="m-profile-name">
            <div class="m-profile-nick"><?php echo $mb_nick ?></div>
            <div class="m-profile-id"><?php echo get_text($mb['mb_id']) ?></div>
        </div>
    </section>

    <dl class="m-profile-grid">
        <div class="m-profile-item">
            <dt>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                회원권한
            </dt>
            <dd><?php echo $mb['mb_level'] ?></dd>
        </div>
        <div class="m-profile-item">
            <dt>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                포인트
            </dt>
            <dd><?php echo number_format($mb['mb_point']) ?></dd>
        </div>
        <div class="m-profile-item">
            <dt>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                회원가입일
            </dt>
            <dd>
                <?php if ($can_see) { ?>
                    <?php echo substr($mb['mb_datetime'], 0, 10) ?>
                    <span class="m-profile-sub">(<?php echo number_format($mb_reg_after) ?> 일)</span>
                <?php } else { ?>
                    <span class="m-profile-faint">알 수 없음</span>
                <?php } ?>
            </dd>
        </div>
        <div class="m-profile-item">
            <dt>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v6m0 0l-3-3m3 3l3-3"/><circle cx="12" cy="14" r="8"/></svg>
                최종접속일
            </dt>
            <dd>
                <?php if ($can_see) { ?>
                    <?php echo $mb['mb_today_login'] ?>
                <?php } else { ?>
                    <span class="m-profile-faint">알 수 없음</span>
                <?php } ?>
            </dd>
        </div>
        <?php if ($mb_homepage) { ?>
        <div class="m-profile-item m-profile-item-wide">
            <dt>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                홈페이지
            </dt>
            <dd><a href="<?php echo $mb_homepage ?>" target="_blank" rel="noopener"><?php echo $mb_homepage ?></a></dd>
        </div>
        <?php } ?>
    </dl>

    <section class="m-profile-bio">
        <h2 class="m-profile-bio-title">인사말</h2>
        <div class="m-profile-bio-body">
            <?php if (trim(strip_tags((string) $mb_profile)) !== '') { ?>
                <?php echo $mb_profile ?>
            <?php } else { ?>
                <span class="m-profile-faint">작성된 인사말이 없습니다.</span>
            <?php } ?>
        </div>
    </section>

    <div class="m-popup-actions">
        <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:9px 20px;">창닫기</button>
    </div>
</div>

<style>
.m-profile-card {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 16px; margin-bottom: 12px;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-lg);
}
.m-profile-avatar { display: inline-flex; flex-shrink: 0; }
.m-profile-avatar img {
    width: 56px; height: 56px; border-radius: 50%;
    border: 1px solid var(--m-border); object-fit: cover;
}
.m-profile-name { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.m-profile-nick { font-size: var(--m-text-lg); font-weight: 700; color: var(--m-text); }
.m-profile-id { font-size: var(--m-text-xs); color: var(--m-text-muted); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }

.m-profile-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
    margin: 0 0 12px;
}
@media (max-width: 480px) { .m-profile-grid { grid-template-columns: 1fr; } }
.m-profile-item {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    padding: 10px 12px;
    display: flex; flex-direction: column; gap: 4px;
}
.m-profile-item-wide { grid-column: 1 / -1; }
.m-profile-item dt {
    display: flex; align-items: center; gap: 5px;
    font-size: var(--m-text-xs); color: var(--m-text-muted); font-weight: 500;
}
.m-profile-item dt svg { color: var(--m-text-faint); }
.m-profile-item dd {
    margin: 0; font-size: var(--m-text-md); color: var(--m-text); font-weight: 600;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-profile-item dd a { color: var(--m-primary); text-decoration: none; word-break: break-all; }
.m-profile-item dd a:hover { text-decoration: underline; }
.m-profile-sub { margin-left: 4px; font-weight: 400; font-size: var(--m-text-xs); color: var(--m-text-muted); }
.m-profile-faint { color: var(--m-text-faint); font-weight: 400; font-size: var(--m-text-sm); }

.m-profile-bio {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    padding: 14px 16px;
}
.m-profile-bio-title {
    margin: 0 0 8px;
    font-size: var(--m-text-sm); font-weight: 600; color: var(--m-text-soft);
    letter-spacing: 0.02em;
}
.m-profile-bio-body {
    font-size: var(--m-text-md); line-height: var(--m-leading-relaxed);
    color: var(--m-text); word-break: break-word; white-space: pre-wrap;
}
</style>
<!-- } 자기소개 끝 -->
