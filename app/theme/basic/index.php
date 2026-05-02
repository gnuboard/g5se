<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit;

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/index.php');
    return;
}

if(G5_COMMUNITY_USE === false) {
    include_once(G5_THEME_SHOP_PATH.'/index.php');
    return;
}

// 공통 모던 디자인 head 주입
require_once(G5_THEME_PATH.'/modern/_head.inc.php');

// gnuboard 의 head/tail 도 호출 — 안에서 chrome 이 출력되지만 m-shell 외부라서 시각적으로 가려짐
include_once(G5_THEME_PATH.'/head.php');
?>

<div class="m-shell">

    <header class="m-nav">
        <div class="m-nav-inner">
            <a href="<?php echo G5_URL ?>" class="m-brand">gnu5se</a>
            <nav class="m-nav-actions">
                <?php if ($is_member) { ?>
                    <span style="font-size: 13px; color: var(--m-text-muted);">
                        <?php echo get_text($member['mb_nick']) ?>
                    </span>
                    <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo urlencode(G5_BBS_URL.'/register_form.php?w=u') ?>" class="m-btn m-btn-ghost">정보수정</a>
                    <a href="<?php echo G5_BBS_URL ?>/logout.php" class="m-btn m-btn-ghost">로그아웃</a>
                <?php } else { ?>
                    <a href="<?php echo G5_BBS_URL ?>/login.php" class="m-btn m-btn-ghost">로그인</a>
                    <a href="<?php echo G5_BBS_URL ?>/register.php" class="m-btn m-btn-secondary" style="width: auto; padding: 8px 14px;">회원가입</a>
                <?php } ?>
            </nav>
        </div>
    </header>

    <main class="m-container" style="padding-top: 48px; padding-bottom: 48px;">

        <section style="text-align: center; padding: 48px 16px 56px;">
            <h1 style="font-size: 38px; letter-spacing: -0.02em; margin-bottom: 14px;">
                <?php if ($is_member) { ?>
                    안녕하세요, <span style="color: var(--m-primary);"><?php echo get_text($member['mb_nick']) ?></span> 님
                <?php } else { ?>
                    환영합니다
                <?php } ?>
            </h1>
            <p style="font-size: 16px; color: var(--m-text-muted); max-width: 560px; margin: 0 auto;">
                gnuboard5 위에 모던 디자인 시스템을 얹어 점진적으로 새로 빚어가는 사이트입니다.
            </p>
        </section>

        <section>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                <?php
                $sql = "select bo_table, bo_subject from `{$g5['board_table']}`
                        where bo_device <> 'mobile'
                        ".($is_admin ? '' : "and bo_use_cert = ''")."
                        order by bo_order, bo_table limit 6";
                $rs = sql_query($sql);
                while ($row = sql_fetch_array($rs)) {
                    $bo_table = $row['bo_table'];
                    $bo_subject = get_text($row['bo_subject']);
                ?>
                <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>"
                   class="m-card"
                   style="text-decoration: none; transition: border-color 0.15s, transform 0.15s;"
                   onmouseover="this.style.borderColor='var(--m-border-hover)'; this.style.transform='translateY(-2px)';"
                   onmouseout="this.style.borderColor='var(--m-border)'; this.style.transform='translateY(0)';">
                    <div style="font-size: 12px; color: var(--m-text-faint); font-weight: 500; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                        <?php echo htmlspecialchars($bo_table) ?>
                    </div>
                    <div style="font-size: 16px; font-weight: 600; color: var(--m-text);">
                        <?php echo $bo_subject ?>
                    </div>
                </a>
                <?php } ?>
            </div>
        </section>

    </main>

    <footer style="margin-top: auto; padding: 24px 20px; border-top: 1px solid var(--m-border); text-align: center; font-size: 12px; color: var(--m-text-faint);">
        gnu5se · gnuboard5 modernization sandbox
    </footer>

</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
