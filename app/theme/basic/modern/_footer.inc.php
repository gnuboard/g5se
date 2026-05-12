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
<footer class="m-footer">
    <div class="m-footer-inner">

        <!-- 좌측: 사이트 정보 — 가로 인라인 (라벨: 값) 항목들 -->
        <div class="m-footer-col m-footer-col-info">
            <h3 class="m-footer-title">사이트 정보</h3>
            <ul class="m-footer-info-row">
                <?php if ($_ft_company['name'])    { ?><li><span>회사명</span><b><?php echo get_text($_ft_company['name'])    ?></b></li><?php } ?>
                <?php if ($_ft_company['owner'])   { ?><li><span>대표</span><b><?php echo get_text($_ft_company['owner'])   ?></b></li><?php } ?>
                <?php if ($_ft_company['saupja'])  { ?><li><span>사업자번호</span><b><?php echo get_text($_ft_company['saupja'])  ?></b></li><?php } ?>
                <?php if ($_ft_company['tongsin']) { ?><li><span>통신판매</span><b><?php echo get_text($_ft_company['tongsin']) ?></b></li><?php } ?>
                <?php if ($_ft_company['info'] || $_ft_company['email']) { ?><li class="m-footer-info-break" aria-hidden="true"></li><?php } ?>
                <?php if ($_ft_company['info'])    { ?><li><span>책임자</span><b><?php echo get_text($_ft_company['info'])    ?></b></li><?php } ?>
                <?php if ($_ft_company['email'])   { ?><li><span>이메일</span><b><a href="mailto:<?php echo $_ft_company['email'] ?>"><?php echo $_ft_company['email'] ?></a></b></li><?php } ?>
                <?php if ($_ft_company['tel'] || $_ft_company['fax']) { ?><li class="m-footer-info-break" aria-hidden="true"></li><?php } ?>
                <?php if ($_ft_company['tel'])     { ?><li><span>전화</span><b><?php echo get_text($_ft_company['tel'])     ?></b></li><?php } ?>
                <?php if ($_ft_company['fax'])     { ?><li><span>팩스</span><b><?php echo get_text($_ft_company['fax'])     ?></b></li><?php } ?>
                <?php if ($_ft_company['addr'])    { ?><li class="m-footer-info-addr"><span>주소</span><b><?php echo get_text($_ft_company['addr']) ?></b></li><?php } ?>
            </ul>
        </div>

        <!-- 우측: 접속자 -->
        <div class="m-footer-col m-footer-col-stats">
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

    <!-- 사이트 메뉴 — 맨 아래 한 줄 -->
    <nav class="m-footer-menu" aria-label="사이트 메뉴">
        <a href="<?php echo G5_BBS_URL ?>/content/company">회사소개</a>
        <span class="m-footer-menu-sep">·</span>
        <a href="<?php echo G5_BBS_URL ?>/content/privacy">개인정보처리방침</a>
        <span class="m-footer-menu-sep">·</span>
        <a href="<?php echo G5_BBS_URL ?>/content/provision">서비스이용약관</a>
        <span class="m-footer-menu-sep">·</span>
        <a href="<?php echo G5_BBS_URL ?>/new">새글</a>
    </nav>

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
    padding: 32px 20px 16px;
    display: grid; grid-template-columns: 1fr 220px; gap: 32px;
    align-items: start;
}
@media (max-width: 768px) {
    .m-footer-inner { grid-template-columns: 1fr; gap: 24px; padding-top: 24px; }
}

.m-footer-col { min-width: 0; }
.m-footer-title {
    font-size: var(--m-text-base); font-weight: 600; color: var(--m-text);
    margin: 0 0 12px;
}

/* 사이트 정보 — 가로 인라인 chip 스타일 (라벨: 값) */
.m-footer-info-row {
    list-style: none;
    margin: 0; padding: 0;
    display: flex; flex-wrap: wrap;
    gap: 6px 12px;
    font-size: var(--m-text-sm);
}
.m-footer-info-row li {
    display: inline-flex;
    gap: 6px;
    align-items: center;
    line-height: 1.4;
    min-width: 0;
}
.m-footer-info-row li.m-footer-info-addr { flex-basis: 100%; }
/* flex line-break — 전화/팩스 줄을 주소 바로 위에 따로 배치 */
.m-footer-info-row li.m-footer-info-break {
    flex-basis: 100%;
    height: 0; padding: 0; margin: 0; border: 0;
    gap: 0;
}
.m-footer-info-row span {
    color: var(--m-text-faint);
    font-size: 0.92em;
}
.m-footer-info-row b {
    color: var(--m-text);
    font-weight: 500;
    min-width: 0;
    overflow-wrap: anywhere;
}
.m-footer-info-row a { color: inherit; text-decoration: none; }
.m-footer-info-row a:hover { color: var(--m-primary); }

/* 접속자 통계 (우측, key/value grid 유지) */
.m-footer-stats {
    margin: 0; font-size: var(--m-text-sm);
    display: grid; grid-template-columns: 70px 1fr; gap: 4px 10px;
}
.m-footer-stats dt { color: var(--m-text-faint); }
.m-footer-stats dd { margin: 0; font-weight: 600; color: var(--m-text); text-align: right; }
.m-footer-online { color: var(--m-primary); text-decoration: none; }
.m-footer-online:hover { text-decoration: underline; }

/* 사이트 메뉴 (맨 아래 한 줄) — bottom 여백 포함 */
.m-footer-menu {
    max-width: var(--m-max-7xl); margin: 0 auto;
    padding: 12px 20px 24px;
    border-top: 1px solid var(--m-border);
    display: flex; flex-wrap: wrap; align-items: center;
    gap: 6px;
    font-size: var(--m-text-sm);
}
.m-footer-menu a { color: var(--m-text-soft); text-decoration: none; padding: 2px 4px; }
.m-footer-menu a:hover { color: var(--m-primary); }
.m-footer-menu-sep { color: var(--m-text-faint); }

@media (max-width: 560px) {
    .m-footer-col-stats {
        display: none;
    }
    .m-footer-info-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 6px;
    }
    .m-footer-info-row li,
    .m-footer-info-row li.m-footer-info-addr {
        display: grid;
        grid-template-columns: 68px minmax(0, 1fr);
        align-items: baseline;
        gap: 8px;
        min-width: 0;
    }
    .m-footer-info-row li.m-footer-info-break {
        display: none;
    }
    .m-footer-stats {
        max-width: 180px;
    }
}
</style>
<?php } ?>
