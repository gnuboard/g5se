<?php
/*
 * /admin/db_migrate — DB 마이그레이션 도구
 *
 * 1) charset: utf8mb3 → utf8mb4 (이모지 4-byte 지원)
 * 2) zero-date 컬럼 → NULL 허용 + 기존 '0000-00-00[ 00:00:00]' 값 NULL 변환
 *
 * super admin (cf_admin) 전용. POST 액션은 CSRF 토큰 + 명시적 confirm.
 * 각 액션은 단일 원자 단위 (한 테이블 변환 / 한 컬럼 변환) — 부분 실패에 강함.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

if ($member['mb_id'] !== $config['cf_admin']) {
    alert('최고 관리자만 접근 가능합니다.');
}

$g5['title'] = 'DB 마이그레이션';

$_log = [];
$_action = isset($_POST['action']) ? (string)$_POST['action'] : '';

// CSRF — 단순 세션 토큰 (TODO: gnuboard set_session 사용)
if (!isset($_SESSION['_db_migrate_token'])) {
    $_SESSION['_db_migrate_token'] = bin2hex(random_bytes(16));
}
$_csrf = $_SESSION['_db_migrate_token'];

if ($_action && (!isset($_POST['token']) || !hash_equals($_csrf, (string)$_POST['token']))) {
    alert('보안 토큰이 일치하지 않습니다.');
}

// 현재 DB 이름
$_db_row = sql_pdo_fetch("SELECT DATABASE() AS db");
$_db_name = $_db_row['db'] ?? '';

// 액션 처리 ───────────────────────────────────────────────
if ($_action === 'charset_table' && !empty($_POST['table'])) {
    $_t = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table']);
    if ($_t) {
        try {
            sql_pdo_query("ALTER TABLE `$_t` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $_log[] = ['ok', "✓ `$_t` → utf8mb4_unicode_ci 변환 완료"];
        } catch (Throwable $e) {
            $_log[] = ['err', "✗ `$_t` 실패: ".$e->getMessage()];
        }
    }
}
if ($_action === 'zerodate_column' && !empty($_POST['table']) && !empty($_POST['column']) && !empty($_POST['type'])) {
    $_t = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table']);
    $_c = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['column']);
    $_type = strtoupper(preg_replace('/[^A-Za-z]/', '', $_POST['type']));
    if (!in_array($_type, ['DATE', 'DATETIME', 'TIMESTAMP'], true)) {
        $_log[] = ['err', "✗ 잘못된 타입: $_type"];
    } else if ($_t && $_c) {
        $_zero = ($_type === 'DATE') ? '0000-00-00' : '0000-00-00 00:00:00';
        try {
            // 1) NULL 허용 + default NULL 로 컬럼 변경
            sql_pdo_query("ALTER TABLE `$_t` MODIFY `$_c` $_type NULL DEFAULT NULL");
            // 2) 기존 zero 값 → NULL
            $stmt = sql_pdo_query(
                "UPDATE `$_t` SET `$_c` = NULL WHERE `$_c` = :z",
                [':z' => $_zero]
            );
            $affected = $stmt instanceof PDOStatement ? $stmt->rowCount() : 0;
            $_log[] = ['ok', "✓ `$_t`.`$_c` → NULLABLE + $affected 행 0000→NULL"];
        } catch (Throwable $e) {
            $_log[] = ['err', "✗ `$_t`.`$_c` 실패: ".$e->getMessage()];
        }
    }
}

// 새 토큰 발급 (한번 쓰고 폐기)
if ($_action) {
    $_SESSION['_db_migrate_token'] = bin2hex(random_bytes(16));
    $_csrf = $_SESSION['_db_migrate_token'];
}

// 현재 상태 조사 ──────────────────────────────────────────
// charset 상태
$_charset_rows = sql_pdo_query(
    "SELECT t.table_name AS tbl, t.table_collation AS coll, t.table_rows AS rows_est
       FROM information_schema.tables t
      WHERE t.table_schema = :db
      ORDER BY t.table_name",
    [':db' => $_db_name]
);
$_tables_utf8mb3 = [];
$_tables_utf8mb4 = [];
while ($r = sql_fetch_array($_charset_rows)) {
    if (stripos($r['coll'], 'utf8mb4') === 0) {
        $_tables_utf8mb4[] = $r;
    } else {
        $_tables_utf8mb3[] = $r;
    }
}

// zero-date 컬럼
$_zd_rows = sql_pdo_query(
    "SELECT table_name AS tbl, column_name AS col, data_type AS type, is_nullable AS nullable, column_default AS def
       FROM information_schema.columns
      WHERE table_schema = :db
        AND data_type IN ('date','datetime','timestamp')
        AND (column_default IN ('0000-00-00','0000-00-00 00:00:00') OR is_nullable = 'NO')
      ORDER BY table_name, column_name",
    [':db' => $_db_name]
);
$_zd_pending  = [];
$_zd_complete = [];
while ($r = sql_fetch_array($_zd_rows)) {
    $needs = ($r['nullable'] === 'NO') || in_array($r['def'], ['0000-00-00', '0000-00-00 00:00:00'], true);
    if ($needs) {
        $_zd_pending[] = $r;
    } else {
        $_zd_complete[] = $r;
    }
}

// 현재 sql_mode
$_sm = sql_pdo_fetch("SELECT @@sql_mode AS m");
$_sql_mode = $_sm['m'] ?? '';

admin_layout_start($g5['title'], 'core');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h1 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']); ?></h1>
</header>

<div class="legacy-admin-content space-y-6">

    <?php if ($_log) { ?>
    <div class="dbm-log">
        <h2 class="h2_frm">실행 결과</h2>
        <ul>
            <?php foreach ($_log as [$t, $msg]) { ?>
            <li class="dbm-log-<?php echo $t; ?>"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></li>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>

    <!-- Section 1: charset -->
    <section>
        <h2 class="h2_frm">① 문자셋 — utf8mb4 변환 (이모지 지원)</h2>
        <p class="dbm-desc">
            현재 DB <code><?php echo htmlspecialchars($_db_name); ?></code> —
            완료 <strong><?php echo count($_tables_utf8mb4); ?></strong>개 /
            대기 <strong class="dbm-warn"><?php echo count($_tables_utf8mb3); ?></strong>개 (utf8mb3 등)
        </p>

        <?php if ($_tables_utf8mb3) { ?>
        <div class="tbl_head01 tbl_wrap">
            <table>
                <thead><tr><th>테이블</th><th>현재 collation</th><th>예상 행수</th><th>액션</th></tr></thead>
                <tbody>
                    <?php foreach ($_tables_utf8mb3 as $r) { ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($r['tbl']); ?></code></td>
                        <td><?php echo htmlspecialchars($r['coll']); ?></td>
                        <td class="td_num_right"><?php echo number_format((int)$r['rows_est']); ?></td>
                        <td>
                            <form method="post" class="dbm-action" onsubmit="return confirm('테이블 <?php echo htmlspecialchars($r['tbl']); ?> 을 utf8mb4 로 변환합니다.\n장시간 락이 걸릴 수 있습니다 (행수 비례).\n계속하시겠습니까?');">
                                <input type="hidden" name="token" value="<?php echo $_csrf; ?>">
                                <input type="hidden" name="action" value="charset_table">
                                <input type="hidden" name="table" value="<?php echo htmlspecialchars($r['tbl']); ?>">
                                <button type="submit" class="btn_submit dbm-btn">변환</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } else { ?>
        <p class="dbm-ok">✓ 모든 테이블이 utf8mb4 입니다.</p>
        <?php } ?>
    </section>

    <!-- Section 2: zero-date -->
    <section>
        <h2 class="h2_frm">② 0000-00-00 컬럼 → NULL 허용</h2>
        <p class="dbm-desc">
            대기 <strong class="dbm-warn"><?php echo count($_zd_pending); ?></strong>개 컬럼
            (NOT NULL date/datetime 또는 default '0000-00-00...')
        </p>

        <?php if ($_zd_pending) { ?>
        <div class="tbl_head01 tbl_wrap">
            <table>
                <thead>
                    <tr><th>테이블</th><th>컬럼</th><th>타입</th><th>NULL?</th><th>default</th><th>액션</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($_zd_pending as $r) { ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($r['tbl']); ?></code></td>
                        <td><code><?php echo htmlspecialchars($r['col']); ?></code></td>
                        <td><?php echo htmlspecialchars(strtoupper($r['type'])); ?></td>
                        <td><?php echo $r['nullable'] === 'YES' ? 'YES' : '<span class="dbm-warn">NO</span>'; ?></td>
                        <td><?php echo $r['def'] === null ? '<em>NULL</em>' : '<code>'.htmlspecialchars($r['def']).'</code>'; ?></td>
                        <td>
                            <form method="post" class="dbm-action" onsubmit="return confirm('<?php echo htmlspecialchars($r['tbl']); ?>.<?php echo htmlspecialchars($r['col']); ?> 을 NULL 허용 + 0000 값 NULL 변환합니다.\n계속하시겠습니까?');">
                                <input type="hidden" name="token" value="<?php echo $_csrf; ?>">
                                <input type="hidden" name="action" value="zerodate_column">
                                <input type="hidden" name="table" value="<?php echo htmlspecialchars($r['tbl']); ?>">
                                <input type="hidden" name="column" value="<?php echo htmlspecialchars($r['col']); ?>">
                                <input type="hidden" name="type" value="<?php echo htmlspecialchars($r['type']); ?>">
                                <button type="submit" class="btn_submit dbm-btn">변환</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } else { ?>
        <p class="dbm-ok">✓ 처리할 zero-date 컬럼이 없습니다.</p>
        <?php } ?>
    </section>

    <!-- Section 3: sql_mode 정보 -->
    <section>
        <h2 class="h2_frm">③ 현재 sql_mode (참고)</h2>
        <pre class="dbm-pre"><?php echo htmlspecialchars($_sql_mode); ?></pre>
        <p class="dbm-desc">
            마이그레이션 완료 후 <code>my.cnf</code> 에 <code>NO_ZERO_DATE,NO_ZERO_IN_DATE</code> 를 추가하면
            앱이 실수로 다시 0000 값을 INSERT 하는 것을 차단할 수 있습니다.
        </p>
    </section>

</div><!-- /.legacy-admin-content -->
</main>

<style>
.dbm-desc { font-size: 0.85rem; color: var(--slate-600); margin: 0 0 0.75rem; }
.dbm-warn { color: #ef4444; font-weight: 700; }
.dbm-ok { color: #059669; font-weight: 600; padding: 0.75rem 1rem; background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25); border-radius: 0.5rem; }
.dbm-action { display: inline; margin: 0; }
.dbm-btn { padding: 0.3rem 0.8rem; font-size: 0.78rem; }
.dbm-log { padding: 1rem; background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 0.5rem; }
.dbm-log ul { list-style: none; margin: 0; padding: 0; font-family: ui-monospace, monospace; font-size: 0.82rem; line-height: 1.6; }
.dbm-log-ok { color: #059669; }
.dbm-log-err { color: #ef4444; }
.dbm-pre { padding: 0.75rem; background: var(--slate-100); border-radius: 0.375rem; font-size: 0.78rem; overflow-x: auto; word-break: break-all; white-space: pre-wrap; }
[data-theme="dark"] .dbm-log { background: var(--slate-800); border-color: var(--slate-700); }
[data-theme="dark"] .dbm-pre { background: var(--slate-800); color: var(--slate-200); }
[data-theme="dark"] .dbm-desc { color: var(--slate-400); }
.legacy-admin-content code { padding: 0.1em 0.3em; background: var(--slate-100); border-radius: 0.25rem; font-size: 0.92em; }
[data-theme="dark"] .legacy-admin-content code { background: var(--slate-800); color: var(--slate-200); }
</style>

<?php admin_layout_end(); ?>
