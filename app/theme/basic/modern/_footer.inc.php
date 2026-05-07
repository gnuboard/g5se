<?php
/*
 * 모던 footer — 모든 페이지 하단 일관된 디자인 제공.
 * 사용: require G5_THEME_PATH.'/modern/_footer.inc.php';
 *
 * 4-column 레이아웃:
 *   1) 사이트 메뉴 (회사소개·약관·개인정보 등 정적 페이지 링크)
 *   2) 사이트 정보 (gnuboard config 의 회사 정보)
 *   3) 공지사항 최근 (notice 게시판 최신 5개)
 *   4) 접속자 통계 (오늘/어제/최대/전체)
 * 하단 1줄: 저작권/동기화 정보
 */

if (!defined('_GNUBOARD_')) return;

global $config, $g5, $default;

// 공지사항 최근 5건 (notice 게시판이 있으면)
$_ft_notices = [];
$notice_table = $g5['write_prefix'].'notice';
$notice_check = sql_fetch(" SHOW TABLES LIKE '{$notice_table}' ");
if ($notice_check) {
    $rs = sql_query(" SELECT wr_id, wr_subject FROM `{$notice_table}` WHERE wr_is_comment = 0 ORDER BY wr_num, wr_reply LIMIT 5 ");
    while ($r = sql_fetch_array($rs)) $_ft_notices[] = $r;
}

// 접속자 통계 — gnuboard 는 g5_config.cf_visit 에 "오늘:N,어제:N,최대:N,전체:N" 문자열로 저장.
// (g5_visit_sum 테이블은 일자별 합계만 있어 직접 집계할 수 없음.)
$_ft_visit = ['today' => 0, 'yesterday' => 0, 'max' => 0, 'total' => 0];
if (!empty($config['cf_visit']) && preg_match('/오늘:(\d+),어제:(\d+),최대:(\d+),전체:(\d+)/', $config['cf_visit'], $_vm)) {
    $_ft_visit['today']     = (int)$_vm[1];
    $_ft_visit['yesterday'] = (int)$_vm[2];
    $_ft_visit['max']       = (int)$_vm[3];
    $_ft_visit['total']     = (int)$_vm[4];
}

// 현재 접속자 — g5_login 의 lo_datetime 가 cf_login_minutes 분 이내인 row 수
$_ft_now = 0;
$_login_minutes = (int)($config['cf_login_minutes'] ?? 10);
if ($_login_minutes > 0) {
    $_r = @sql_pdo_fetch(
        " select count(*) as cnt from {$g5['login_table']} where lo_datetime >= :cutoff ",
        [':cutoff' => date('Y-m-d H:i:s', G5_SERVER_TIME - 60 * $_login_minutes)]
    );
    $_ft_now = (int)($_r['cnt'] ?? 0);
}

$_ft_year = date('Y');
?>

<footer class="m-footer">
    <div class="m-footer-inner">

        <!-- 컬럼 1: 사이트 메뉴 -->
        <div class="m-footer-col">
            <h3 class="m-footer-title">사이트</h3>
            <ul class="m-footer-list">
                <li><a href="<?php echo G5_BBS_URL ?>/content.php?co_id=company">회사소개</a></li>
                <li><a href="<?php echo G5_BBS_URL ?>/content.php?co_id=privacy">개인정보처리방침</a></li>
                <li><a href="<?php echo G5_BBS_URL ?>/content.php?co_id=provision">서비스이용약관</a></li>
                <li><a href="<?php echo G5_BBS_URL ?>/new.php">새글</a></li>
            </ul>
        </div>

        <!-- 컬럼 2: 사이트 정보 — $default (g5_shop_default) 우선, 없으면 $config 폴백 -->
        <?php
        // 회사 정보 fallback chain — shop default 가 풍부, config 는 이메일 정도만
        $_ft_company = [
            'name'    => $default['de_admin_company_name']      ?? $config['cf_company_name']      ?? '',
            'owner'   => $default['de_admin_company_owner']     ?? $config['cf_company_owner']     ?? '',
            'addr'    => $default['de_admin_company_addr']      ?? $config['cf_company_addr']      ?? '',
            'saupja'  => $default['de_admin_company_saupja_no'] ?? $config['cf_company_saupja_no'] ?? '',
            'tongsin' => $default['de_admin_tongsin_no']        ?? '',
            'tel'     => $default['de_admin_company_tel']       ?? $config['cf_company_phone']     ?? '',
            'fax'     => $default['de_admin_company_fax']       ?? '',
            'info'    => $default['de_admin_info_name']         ?? '',
            'email'   => $config['cf_admin_email']              ?? '',
        ];
        ?>
        <div class="m-footer-col">
            <h3 class="m-footer-title">사이트 정보</h3>
            <dl class="m-footer-info">
                <?php if ($_ft_company['name']) { ?>
                <dt>회사명</dt><dd><?php echo get_text($_ft_company['name']) ?></dd>
                <?php } ?>
                <?php if ($_ft_company['owner']) { ?>
                <dt>대표</dt><dd><?php echo get_text($_ft_company['owner']) ?></dd>
                <?php } ?>
                <?php if ($_ft_company['addr']) { ?>
                <dt>주소</dt><dd><?php echo get_text($_ft_company['addr']) ?></dd>
                <?php } ?>
                <?php if ($_ft_company['saupja']) { ?>
                <dt>사업자번호</dt><dd><?php echo get_text($_ft_company['saupja']) ?></dd>
                <?php } ?>
                <?php if ($_ft_company['tongsin']) { ?>
                <dt>통신판매</dt><dd><?php echo get_text($_ft_company['tongsin']) ?></dd>
                <?php } ?>
                <?php if ($_ft_company['tel']) { ?>
                <dt>전화</dt><dd><?php echo get_text($_ft_company['tel']) ?></dd>
                <?php } ?>
                <?php if ($_ft_company['fax']) { ?>
                <dt>팩스</dt><dd><?php echo get_text($_ft_company['fax']) ?></dd>
                <?php } ?>
                <?php if ($_ft_company['info']) { ?>
                <dt>책임자</dt><dd><?php echo get_text($_ft_company['info']) ?></dd>
                <?php } ?>
                <?php if ($_ft_company['email']) { ?>
                <dt>이메일</dt><dd><a href="mailto:<?php echo $_ft_company['email'] ?>"><?php echo $_ft_company['email'] ?></a></dd>
                <?php } ?>
            </dl>
        </div>

        <!-- 컬럼 3: 공지사항 -->
        <div class="m-footer-col">
            <h3 class="m-footer-title">
                공지사항
                <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=notice" class="m-footer-more">전체보기 →</a>
            </h3>
            <?php if ($_ft_notices) { ?>
            <ul class="m-footer-list">
                <?php foreach ($_ft_notices as $_n) { ?>
                <li><a href="<?php echo G5_URL ?>/board/notice/<?php echo $_n['wr_id'] ?>"><?php echo get_text(cut_str($_n['wr_subject'], 32, '…')) ?></a></li>
                <?php } ?>
            </ul>
            <?php } else { ?>
            <p class="m-footer-empty">게시물이 없습니다.</p>
            <?php } ?>
        </div>

        <!-- 컬럼 4: 접속자 통계 + 현재 접속자 -->
        <div class="m-footer-col">
            <h3 class="m-footer-title">접속자</h3>
            <dl class="m-footer-stats">
                <dt>현재</dt><dd><a href="/connect" class="m-footer-online" title="현재 접속자 보기"><strong><?php echo number_format($_ft_now) ?></strong></a></dd>
                <dt>오늘</dt><dd><?php echo number_format($_ft_visit['today']) ?></dd>
                <dt>어제</dt><dd><?php echo number_format($_ft_visit['yesterday']) ?></dd>
                <dt>최대</dt><dd><?php echo number_format($_ft_visit['max']) ?></dd>
                <dt>전체</dt><dd><?php echo number_format($_ft_visit['total']) ?></dd>
            </dl>
        </div>

    </div>

    <div class="m-footer-bottom">
        <span>© <?php echo $_ft_year ?> <?php echo isset($config['cf_title']) && $config['cf_title'] ? get_text($config['cf_title']) : 'gnu5se' ?>.</span>
        <span class="m-footer-bottom-meta">gnuboard5 modernization sandbox</span>
    </div>
</footer>

<?php if (!defined('_MODERN_FOOTER_CSS_LOADED_')) { define('_MODERN_FOOTER_CSS_LOADED_', true); ?>
<style>
.m-footer {
    margin-top: auto;
    background: var(--m-surface);
    border-top: 1px solid var(--m-border);
}
.m-footer-inner {
    max-width: var(--m-max-7xl); margin: 0 auto;
    padding: 40px 20px 24px;
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px;
}
@media (max-width: 880px) {
    .m-footer-inner { grid-template-columns: repeat(2, 1fr); gap: 24px; padding-top: 32px; }
}
@media (max-width: 540px) {
    .m-footer-inner { grid-template-columns: 1fr; }
}

.m-footer-col { min-width: 0; }
.m-footer-title {
    font-size: var(--m-text-base); font-weight: 600; color: var(--m-text);
    margin-bottom: 14px;
    display: flex; align-items: center; justify-content: space-between;
}
.m-footer-more {
    font-size: var(--m-text-xs); font-weight: 500;
    color: var(--m-text-muted); text-decoration: none;
}
.m-footer-more:hover { color: var(--m-primary); }

.m-footer-list { list-style: none; padding: 0; margin: 0; }
.m-footer-list li { margin-bottom: 6px; }
.m-footer-list a {
    font-size: var(--m-text-sm); color: var(--m-text-muted);
    text-decoration: none;
    display: inline-block;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    max-width: 100%;
}
.m-footer-list a:hover { color: var(--m-primary); }

.m-footer-info, .m-footer-stats {
    margin: 0; font-size: var(--m-text-sm);
    display: grid; grid-template-columns: 70px 1fr; gap: 4px 10px;
}
.m-footer-info dt, .m-footer-stats dt { color: var(--m-text-faint); }
.m-footer-info dd, .m-footer-stats dd { margin: 0; color: var(--m-text-soft); }
.m-footer-info dd a { color: inherit; text-decoration: none; }
.m-footer-info dd a:hover { color: var(--m-primary); }
.m-footer-stats dd { font-weight: 600; color: var(--m-text); text-align: right; }
.m-footer-online { color: var(--m-primary); text-decoration: none; }
.m-footer-online:hover { text-decoration: underline; }

.m-footer-empty { font-size: var(--m-text-sm); color: var(--m-text-faint); margin: 0; }

.m-footer-bottom {
    max-width: var(--m-max-7xl); margin: 0 auto;
    padding: 14px 20px;
    border-top: 1px solid var(--m-border);
    display: flex; justify-content: space-between; align-items: center;
    font-size: var(--m-text-xs); color: var(--m-text-faint);
}
.m-footer-bottom-meta { opacity: 0.7; }
@media (max-width: 540px) {
    .m-footer-bottom { flex-direction: column; gap: 4px; }
}
</style>
<?php } ?>
