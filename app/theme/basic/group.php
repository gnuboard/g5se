<?php
if (!defined('_GNUBOARD_')) exit;

if (!$is_admin && $group['gr_device'] == 'mobile')
    alert($group['gr_subject'].' 그룹은 모바일에서만 접근할 수 있습니다.');

$g5['title'] = $group['gr_subject'];

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
// head.php 가 호출되어야 add_stylesheet 큐 (modern 토큰 + Pretendard 등) 가
// 실제 <head> 안에 출력된다. 빠뜨리면 마크업 클래스만 깔리고 스타일이 안 먹는다.
include_once(G5_THEME_PATH.'/head.php');
include_once(G5_LIB_PATH.'/latest.lib.php');

// 그룹에 속한 게시판 카운트
$sql_boards = " select bo_table, bo_subject, bo_count_write
                from {$g5['board_table']}
                where gr_id = :gr_id
                  and bo_list_level <= :mb_level
                  and bo_device <> 'mobile' ";
if (!$is_admin) $sql_boards .= " and bo_use_cert = '' ";
$sql_boards .= " order by bo_order ";
$boards_result = sql_pdo_query($sql_boards, [
    ':gr_id' => $gr_id,
    ':mb_level' => $member['mb_level'],
]);
$boards = [];
while ($row = sql_pdo_fetch_array($boards_result)) $boards[] = $row;
$board_count = count($boards);
?>

<!-- 그룹 페이지 시작 { -->
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">
            <header class="m-group-head">
                <h1 class="m-group-title"><?php echo get_text($group['gr_subject']) ?></h1>
                <p class="m-group-sub">
                    <strong><?php echo $board_count ?></strong>개 게시판
                    <?php if (!empty($group['gr_subject2'])) { ?>
                    <span class="m-group-meta-sep">·</span>
                    <span class="m-group-desc"><?php echo strip_tags($group['gr_subject2']) ?></span>
                    <?php } ?>
                </p>
            </header>

            <?php if ($board_count > 0) { ?>
            <div class="m-group-grid">
                <?php foreach ($boards as $row) { ?>
                <div class="m-group-cell">
                    <?php echo latest('theme/basic', $row['bo_table'], 6, 30); ?>
                </div>
                <?php } ?>
            </div>
            <?php } else { ?>
            <div class="m-card m-group-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                <p>이 그룹에 게시판이 없습니다.</p>
            </div>
            <?php } ?>
        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>

    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>

<style>
.m-group-head { margin-bottom: 22px; }
.m-group-title {
    font-size: var(--m-text-3xl); font-weight: 700;
    color: var(--m-text); margin: 0 0 4px;
    letter-spacing: -0.01em;
}
.m-group-sub { margin: 0; font-size: var(--m-text-sm); color: var(--m-text-muted); }
.m-group-sub strong { color: var(--m-text); font-weight: 600; }
.m-group-meta-sep { color: var(--m-text-faint); margin: 0 6px; }
.m-group-desc { color: var(--m-text-soft); }

.m-group-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}

.m-group-empty {
    padding: 60px 24px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.m-group-empty svg { color: var(--m-text-faint); }
.m-group-empty p { margin: 0; color: var(--m-text-muted); font-size: var(--m-text-md); }

/* latest.skin 위젯 카드 */
.m-latest {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-lg);
    overflow: hidden;
    display: flex; flex-direction: column;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.m-latest:hover { border-color: var(--m-border-hover); box-shadow: var(--m-shadow); }
.m-latest-head {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--m-border);
    background: var(--m-surface-2);
}
.m-latest-title { margin: 0; font-size: var(--m-text-lg); font-weight: 700; }
.m-latest-title a { color: var(--m-text); text-decoration: none; }
.m-latest-title a:hover { color: var(--m-primary); }
.m-latest-more {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: var(--m-text-xs); color: var(--m-text-muted);
    text-decoration: none; flex-shrink: 0;
}
.m-latest-more:hover { color: var(--m-primary); }

.m-latest-list { list-style: none; margin: 0; padding: 0; }
.m-latest-item {
    padding: 10px 16px;
    border-bottom: 1px solid var(--m-border);
}
.m-latest-item:last-child { border-bottom: 0; }
.m-latest-link {
    display: flex; align-items: center; gap: 6px;
    color: var(--m-text); text-decoration: none;
    margin-bottom: 3px;
}
.m-latest-link:hover .m-latest-subject { color: var(--m-primary); }
.m-latest-secret { color: var(--m-text-faint); flex-shrink: 0; }
.m-latest-subject {
    flex: 1; min-width: 0;
    font-size: var(--m-text-sm);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    transition: color 0.15s;
}
.m-latest-subject.is-notice { font-weight: 700; color: var(--m-primary); }
.m-latest-cmt {
    flex-shrink: 0; padding: 1px 6px; border-radius: 999px;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    font-size: 10px; font-weight: 600; color: var(--m-text-muted);
}
.m-latest-tag {
    flex-shrink: 0; padding: 1px 5px; border-radius: 4px;
    font-size: 9px; font-weight: 700; letter-spacing: 0.04em;
}
.m-latest-tag-new { background: #ef4444; color: #fff; }
.m-latest-tag-hot { background: #f59e0b; color: #fff; }
.m-latest-mark { color: var(--m-text-faint); flex-shrink: 0; }

.m-latest-meta {
    display: flex; align-items: center; justify-content: space-between; gap: 6px;
    font-size: var(--m-text-xs); color: var(--m-text-muted);
}
.m-latest-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
.m-latest-name .sv_wrap, .m-latest-name .sv_member, .m-latest-name .sv_guest {
    display: inline !important;
}
.m-latest-date { flex-shrink: 0; color: var(--m-text-faint); }

.m-latest-empty {
    padding: 30px 16px; text-align: center;
    color: var(--m-text-muted); font-size: var(--m-text-sm);
}
</style>
<!-- } 그룹 페이지 끝 -->
<?php
include_once(G5_THEME_PATH.'/tail.php');
