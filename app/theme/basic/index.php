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

// gnuboard 의 head/tail 은 sub 버전으로 — 게시판(bbs/board.php)도 head.sub.php 사용하므로
// 동일하게 맞춰 default.css 의 chrome cascade 영향을 제거 (자간 등 미세한 차이 발생 원인 차단).
include_once(G5_PATH.'/head.sub.php');
?>

<div class="m-shell">

    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <?php
    // 팝업레이어 — admin/newwinform 에서 등록한 newwin 을 메인에서 노출 (legacy head.php 에서
    // _INDEX_ 가드로 include 하던 것을 modern shell 에 직접 이식).
    include G5_BBS_PATH.'/newwin.inc.php';
    ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 48px;">
        <!-- 좌측: 메인 콘텐츠 -->
        <div class="m-main-col">
            <section style="text-align: center; padding: 32px 16px 48px;">
                <h1 style="font-size: var(--m-text-display); margin-bottom: 14px;">
                    <?php if ($is_member) { ?>
                        안녕하세요, <span style="color: var(--m-primary);"><?php echo get_text($member['mb_nick']) ?></span> 님
                    <?php } else { ?>
                        환영합니다
                    <?php } ?>
                </h1>
                <p style="font-size: var(--m-text-lg); color: var(--m-text-muted); max-width: 560px; margin: 0 auto;">
                    gnuboard5 위에 모던 디자인 시스템을 얹어 점진적으로 새로 빚어가는 사이트입니다.
                </p>
            </section>

            <section>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
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
                       class="m-card m-board-card"
                       style="text-decoration: none;">
                        <div style="font-size: var(--m-text-sm); color: var(--m-text-faint); font-weight: 500; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            <?php echo htmlspecialchars($bo_table) ?>
                        </div>
                        <div style="font-size: var(--m-text-lg); font-weight: 600; color: var(--m-text);">
                            <?php echo $bo_subject ?>
                        </div>
                    </a>
                    <?php } ?>
                </div>
            </section>
        </div>

        <!-- 우측: 사이드바 (outlogin 등) -->
        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>

    <?php require G5_THEME_PATH.'/modern/_footer.inc.php'; ?>

</div>

<style>
/* 메인 페이지 게시판 카드 hover (m-with-sidebar 는 _head.inc.php 에서 공통 정의됨) */
.m-board-card { transition: border-color 0.15s, transform 0.15s; }
.m-board-card:hover { border-color: var(--m-border-hover); transform: translateY(-2px); }
</style>

<?php
// 게시판(bbs/board.php)과 동일하게 tail.sub.php 사용
include_once(G5_PATH.'/tail.sub.php');
