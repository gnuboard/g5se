<?php
/**
 * /admin/setting — 사이트 설정 관리
 *
 * 두 모드:
 *  - 목록 (?key 없음)        : 등록된 그룹 테이블 + 편집 링크 + 저장 상태
 *  - 편집 (?key=<group_key>) : 그룹 폼 + 저장/리셋 액션 + 토스트
 *
 * POST 액션 결과는 세션 flash 로 전달 — URL 에 액션 흔적 남기지 않음
 * (위변조된 ?saved=/?reset= 링크로 admin 을 속이지 못하게).
 */
$sub_menu = "100150";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

if ($member['mb_id'] !== $config['cf_admin']) {
    alert('최고 관리자만 접근 가능합니다.');
}

$g5['title'] = '설정 관리';

require_once __DIR__.'/admin.lib.php';  // get_admin_token / check_admin_token

$_schemas = setting_schemas();

// 모드 결정: ?key 가 등록된 스키마면 edit, 아니면 list
$_edit_key = (isset($_GET['key']) && isset($_schemas[$_GET['key']])) ? (string)$_GET['key'] : '';

// ── POST 처리 ─────────────────────────────────────────────────────
$_action = isset($_POST['action']) ? (string)$_POST['action'] : '';
$_post_key = isset($_POST['key']) ? (string)$_POST['key'] : '';
$_form_values = isset($_POST['v']) && is_array($_POST['v']) ? $_POST['v'] : [];

// gnuboard admin 토큰 검증 (admin.js 가 form submit 시 ajax.token.php 로 새 토큰을 채워줌)
if ($_action) {
    check_admin_token();
}

$_errors = [];
$_values_override = [];

// save 액션: 검증 → setting_put → flash + PRG redirect
if ($_action === 'save' && isset($_schemas[$_post_key])) {
    $schema = $_schemas[$_post_key];
    $errors = [];
    $values_to_put = [];

    foreach ($schema['fields'] as $fkey => $field) {
        $raw = $_form_values[$fkey] ?? null;
        $type = $field['type'];
        $required = !empty($field['required']);

        if ($type === 'text') {
            $val = is_string($raw) ? trim($raw) : '';
            if ($required && $val === '') {
                $errors[] = $field['label'].' 은(는) 필수입니다.';
                continue;
            }
            $values_to_put[$fkey] = $val;
        } elseif ($type === 'number') {
            $str = is_string($raw) ? trim($raw) : '';
            if ($required && $str === '') {
                $errors[] = $field['label'].' 은(는) 필수입니다.';
                continue;
            }
            if ($str === '') {
                $values_to_put[$fkey] = (int)$field['default'];
                continue;
            }
            $val = (int)$str;
            if (isset($field['min']) && $val < (int)$field['min']) {
                $errors[] = $field['label'].' 은(는) '.(int)$field['min'].' 이상이어야 합니다.';
                continue;
            }
            if (isset($field['max']) && $val > (int)$field['max']) {
                $errors[] = $field['label'].' 은(는) '.(int)$field['max'].' 이하이어야 합니다.';
                continue;
            }
            $values_to_put[$fkey] = $val;
        } elseif ($type === 'select') {
            $val = is_string($raw) ? $raw : '';
            $allowed = array_keys($field['options'] ?? []);
            if (!in_array($val, array_map('strval', $allowed), true)) {
                $errors[] = $field['label'].' 의 값이 잘못되었습니다.';
                continue;
            }
            $values_to_put[$fkey] = $val;
        } elseif ($type === 'bool') {
            $values_to_put[$fkey] = !empty($raw);
        } elseif ($type === 'password') {
            $val = is_string($raw) ? $raw : '';
            if ($val === '') {
                // 빈 입력 = 기존 값 유지 — values_to_put 에서 제외
                continue;
            }
            $values_to_put[$fkey] = $val;
        }
    }

    if ($errors) {
        // 검증 실패: 같은 키 edit 모드로 재렌더 + 입력값 유지 + 에러
        $_edit_key = $_post_key;
        $_errors[$_post_key] = implode(' / ', $errors);
        try {
            $merged = setting($_post_key);
        } catch (\Throwable $e) {
            $merged = [];
            foreach ($schema['fields'] as $fkey => $field) {
                $merged[$fkey] = $field['default'];
            }
        }
        foreach ($schema['fields'] as $fkey => $field) {
            if (array_key_exists($fkey, $_form_values) && $field['type'] !== 'password') {
                $raw = $_form_values[$fkey];
                if ($field['type'] === 'bool') {
                    $merged[$fkey] = !empty($raw);
                } elseif ($field['type'] === 'number') {
                    $merged[$fkey] = is_string($raw) && $raw !== '' ? (int)$raw : $field['default'];
                } else {
                    $merged[$fkey] = is_string($raw) ? $raw : '';
                }
            }
        }
        $_values_override = [$_post_key => $merged];
    } else {
        setting_put($_post_key, $values_to_put);
        $_SESSION['_setting_flash'] = ['type' => 'saved', 'key' => $_post_key];
        header('Location: /admin/setting?key='.urlencode($_post_key), true, 303);
        exit;
    }
}

// reset 액션: DELETE → flash + PRG redirect
if ($_action === 'reset' && isset($_schemas[$_post_key])) {
    sql_pdo_query(
        "DELETE FROM `".G5_TABLE_PREFIX."setting` WHERE s_key = ?",
        [$_post_key]
    );
    $_SESSION['_setting_flash'] = ['type' => 'reset', 'key' => $_post_key];
    header('Location: /admin/setting?key='.urlencode($_post_key), true, 303);
    exit;
}

// sync_schema 액션: 테이블 ensure + schema 중 row 없는 그룹만 default 로 INSERT
if ($_action === 'sync_schema') {
    $sync = setting_sync();
    $_SESSION['_setting_flash'] = ['type' => 'sync', 'sync' => $sync];
    header('Location: /admin/setting', true, 303);
    exit;
}

// ── 플래시 읽기 (1 회) ───────────────────────────────────────────
$_flash = $_SESSION['_setting_flash'] ?? null;
unset($_SESSION['_setting_flash']);
if ($_flash) {
    if ($_flash['type'] === 'sync') {
        // sync flash 는 key 무관, 그대로 유지
    } elseif (!isset($_flash['key']) || !isset($_schemas[$_flash['key']])) {
        $_flash = null;
    }
}

// ── 모드별 데이터 준비 ────────────────────────────────────────────
$_values = [];
$_saved_keys = [];

if ($_edit_key !== '') {
    // 편집 모드 — 단일 그룹 값
    if (isset($_values_override[$_edit_key])) {
        $_values[$_edit_key] = $_values_override[$_edit_key];
    } else {
        try {
            $_values[$_edit_key] = setting($_edit_key);
        } catch (\Throwable $e) {
            $_values[$_edit_key] = [];
            if (!isset($_errors[$_edit_key])) {
                $_errors[$_edit_key] = 'g5_setting 테이블이 없습니다. /admin/db_migrate 에서 생성하세요.';
            }
        }
    }
} else {
    // 목록 모드 — 저장된 키 집합 한 번에 SELECT
    $_table_missing = false;
    try {
        $_rs = sql_pdo_query("SELECT s_key FROM `".G5_TABLE_PREFIX."setting`");
        while ($_r = sql_fetch_array($_rs)) {
            $_saved_keys[$_r['s_key']] = true;
        }
    } catch (\Throwable) {
        // 테이블 미생성 — 모두 default 로 표시
        $_table_missing = true;
    }
    // schema 중 DB 에 없는 그룹 목록 (업데이트 버튼 활성 여부 판단)
    $_missing_groups = [];
    foreach (array_keys($_schemas) as $_k) {
        if (!isset($_saved_keys[$_k])) $_missing_groups[] = $_k;
    }
    $_sync_needed = $_table_missing || !empty($_missing_groups);
}

admin_layout_start($g5['title'], 'core');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

<?php if ($_edit_key === '') { ?>
    <header class="flex items-center gap-3 mb-5">
        <h1 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']); ?></h1>
        <?php if (!empty($_sync_needed)) {
            $_sync_parts = [];
            if (!empty($_table_missing)) $_sync_parts[] = '테이블 생성 필요';
            if (!empty($_missing_groups)) $_sync_parts[] = count($_missing_groups).'개 그룹 추가 필요';
            $_sync_title = '동기화 필요: '.implode(' · ', $_sync_parts);
        ?>
            <form method="post" class="ml-auto" onsubmit="return confirm('schema 의 모든 그룹 중 DB 에 없는 것만 기본값으로 추가합니다. 기존 저장값은 유지됩니다. 계속할까요?');">
                <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
                <input type="hidden" name="action" value="sync_schema">
                <button type="submit" class="setting-btn-sync" title="<?php echo htmlspecialchars($_sync_title); ?>">⟳ 업데이트 (<?php echo htmlspecialchars(implode(' · ', $_sync_parts)); ?>)</button>
            </form>
        <?php } ?>
    </header>
<?php } else {
    $_edit_schema = $_schemas[$_edit_key];
?>
    <header class="flex items-center gap-3 mb-5">
        <a href="/admin/setting" class="setting-back">← 목록으로</a>
        <h1 class="text-xl font-bold tracking-tight">
            <?php echo htmlspecialchars($_edit_schema['title']); ?>
        </h1>
    </header>
<?php } ?>

<?php if ($_flash) { ?>
<div class="setting-toast setting-toast-ok">
    <?php if ($_flash['type'] === 'saved') {
        $_flash_title = $_schemas[$_flash['key']]['title'];
        echo htmlspecialchars($_flash_title).' 설정이 저장되었습니다.';
    } elseif ($_flash['type'] === 'reset') {
        $_flash_title = $_schemas[$_flash['key']]['title'];
        echo htmlspecialchars($_flash_title).' 설정이 기본값으로 리셋되었습니다.';
    } elseif ($_flash['type'] === 'sync') {
        $_s = $_flash['sync'] ?? ['table_created' => false, 'inserted' => []];
        $_msgs = [];
        if (!empty($_s['table_created'])) $_msgs[] = 'g5_setting 테이블 생성됨';
        if (!empty($_s['inserted'])) {
            $_titles = [];
            foreach ($_s['inserted'] as $_k) {
                $_titles[] = ($_schemas[$_k]['title'] ?? $_k).' ('.$_k.')';
            }
            $_msgs[] = count($_s['inserted']).'개 그룹 추가: '.implode(', ', $_titles);
        }
        if (!$_msgs) $_msgs[] = '추가할 그룹이 없습니다 — 모든 schema 가 이미 동기화됨.';
        echo htmlspecialchars(implode(' · ', $_msgs));
    } ?>
</div>
<?php } ?>

<?php if ($_edit_key === '') { ?>
    <!-- ─── 목록 모드 ─── -->
    <?php if (!$_schemas) { ?>
        <div class="setting-empty">
            아직 등록된 설정 schema 가 없습니다. <code>app/lib/setting.lib.php</code> 의 <code>SETTINGS_SCHEMA</code> 배열에 항목을 추가하세요.
        </div>
    <?php } else { ?>
        <div class="setting-list-wrap">
            <table class="setting-list">
                <thead>
                    <tr>
                        <th class="setting-col-key">키</th>
                        <th class="setting-col-title">제목</th>
                        <th class="setting-col-desc">설명</th>
                        <th class="setting-col-status">상태</th>
                        <th class="setting-col-edit"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($_schemas as $_k => $_s) { ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($_k); ?></code></td>
                        <td class="setting-cell-title"><?php echo htmlspecialchars($_s['title']); ?></td>
                        <td class="setting-cell-desc"><?php echo htmlspecialchars($_s['description'] ?? ''); ?></td>
                        <td>
                            <?php if (isset($_saved_keys[$_k])) { ?>
                                <span class="setting-badge setting-badge-saved">✓ 저장됨</span>
                            <?php } else { ?>
                                <span class="setting-badge setting-badge-default">기본값</span>
                            <?php } ?>
                        </td>
                        <td class="setting-col-edit">
                            <a href="/admin/setting?key=<?php echo urlencode($_k); ?>" class="setting-btn-edit">편집</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

<?php } else {
    // ─── 편집 모드 — 단일 카드 ───
    $key = $_edit_key;
    $schema = $_schemas[$key];
?>
    <section class="setting-card" id="card-<?php echo htmlspecialchars($key); ?>">
        <?php if (!empty($schema['description'])) { ?>
        <p class="setting-card-desc"><?php echo htmlspecialchars($schema['description']); ?></p>
        <?php } ?>

        <?php if (!empty($schema['notes'])) { ?>
        <details class="setting-notes-wrap">
            <summary class="setting-notes-summary">📘 설정 가이드 보기</summary>
            <div class="setting-notes"><?php echo $schema['notes']; /* schema 는 코드 상수 — htmlspecialchars 안 함 (HTML 허용) */ ?></div>
        </details>
        <?php } ?>

        <?php if (isset($_errors[$key])) { ?>
        <div class="setting-error"><?php echo htmlspecialchars($_errors[$key]); ?></div>
        <?php } ?>

        <form method="post" class="setting-form">
            <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
            <input type="hidden" name="key"   value="<?php echo htmlspecialchars($key); ?>">

            <?php foreach ($schema['fields'] as $fkey => $field) {
                $cur = $_values[$key][$fkey] ?? ($field['default'] ?? '');
                $required = !empty($field['required']);
                $input_name = "v[".$fkey."]";
                $input_id = "f-".$key."-".$fkey;
            ?>
            <div class="setting-field setting-field-<?php echo htmlspecialchars($field['type']); ?>">
                <?php if ($field['type'] !== 'bool') { ?>
                <label class="setting-label" for="<?php echo $input_id; ?>">
                    <?php echo htmlspecialchars($field['label']); ?>
                    <?php if ($required) echo ' <span class="setting-req">*</span>'; ?>
                </label>
                <?php } ?>

                <?php if ($field['type'] === 'text') { ?>
                    <input type="text" id="<?php echo $input_id; ?>" name="<?php echo $input_name; ?>"
                           value="<?php echo htmlspecialchars((string)$cur); ?>"
                           class="setting-input"<?php if ($required) echo ' required'; ?>>
                <?php } elseif ($field['type'] === 'number') { ?>
                    <input type="number" id="<?php echo $input_id; ?>" name="<?php echo $input_name; ?>"
                           value="<?php echo htmlspecialchars((string)$cur); ?>"
                           <?php if (isset($field['min'])) echo 'min="'.(int)$field['min'].'" '; ?>
                           <?php if (isset($field['max'])) echo 'max="'.(int)$field['max'].'" '; ?>
                           class="setting-input"<?php if ($required) echo ' required'; ?>>
                <?php } elseif ($field['type'] === 'select') { ?>
                    <select id="<?php echo $input_id; ?>" name="<?php echo $input_name; ?>" class="setting-input">
                        <?php foreach (($field['options'] ?? []) as $ov => $ol) { ?>
                        <option value="<?php echo htmlspecialchars((string)$ov); ?>"<?php if ((string)$cur === (string)$ov) echo ' selected'; ?>>
                            <?php echo htmlspecialchars((string)$ol); ?>
                        </option>
                        <?php } ?>
                    </select>
                <?php } elseif ($field['type'] === 'bool') { ?>
                    <label class="setting-check">
                        <input type="checkbox" id="<?php echo $input_id; ?>" name="<?php echo $input_name; ?>" value="1"<?php if (!empty($cur)) echo ' checked'; ?>>
                        <?php echo htmlspecialchars($field['label']); ?>
                    </label>
                <?php } elseif ($field['type'] === 'password') {
                    $has_stored = !empty($cur);
                ?>
                    <input type="password" id="<?php echo $input_id; ?>" name="<?php echo $input_name; ?>"
                           value=""
                           placeholder="<?php echo $has_stored ? '••••••••' : ''; ?>"
                           class="setting-input" autocomplete="new-password">
                <?php } ?>

                <?php if (!empty($field['help'])) { ?>
                <p class="setting-help"><?php echo htmlspecialchars($field['help']); ?></p>
                <?php } ?>
            </div>
            <?php } ?>

            <div class="setting-actions">
                <button type="submit" name="action" value="save"  class="setting-btn setting-btn-save">💾 저장</button>
                <button type="submit" name="action" value="reset" class="setting-btn setting-btn-reset"
                        onclick="return confirm('<?php echo htmlspecialchars($schema['title'], ENT_QUOTES); ?> 설정을 기본값으로 리셋합니다 (저장된 값 손실). 계속하시겠습니까?');">↺ 기본값으로 리셋</button>
            </div>
        </form>
    </section>
<?php } ?>

</main>

<style>
/* 공통 — slate-* 토큰은 admin.css 에 정의됨 (--slate-50 ~ --slate-950).
   다크모드는 [data-theme="dark"] 명시 오버라이드. */
.setting-toast       { padding: 0.6rem 0.9rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.9rem; font-weight: 500; }
.setting-toast-ok    { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #047857; }
[data-theme="dark"] .setting-toast-ok { background: rgba(16,185,129,0.15); border-color: rgba(52,211,153,0.4); color: #34d399; }

.setting-empty       { padding: 1.5rem; text-align: center; color: var(--slate-500); background: #fff; border: 1px dashed var(--slate-300); border-radius: 0.5rem; }
[data-theme="dark"] .setting-empty { color: var(--slate-400); background: var(--slate-800); border-color: var(--slate-700); }

.setting-back        { font-size: 0.85rem; color: var(--slate-500); text-decoration: none; padding: 0.3rem 0.6rem; border-radius: 0.375rem; }
.setting-back:hover  { background: var(--slate-100); color: var(--slate-900); }
[data-theme="dark"] .setting-back { color: var(--slate-400); }
[data-theme="dark"] .setting-back:hover { background: var(--slate-700); color: var(--slate-100); }

/* 목록 테이블 */
.setting-list-wrap   { background: #fff; border: 1px solid var(--slate-200); border-radius: 0.75rem; overflow: hidden; }
[data-theme="dark"] .setting-list-wrap { background: var(--slate-800); border-color: var(--slate-700); }

.setting-list        { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.setting-list th,
.setting-list td     { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--slate-200); vertical-align: middle; color: var(--slate-900); }
[data-theme="dark"] .setting-list th,
[data-theme="dark"] .setting-list td { border-bottom-color: var(--slate-700); color: var(--slate-100); }

.setting-list thead th { background: var(--slate-50); font-weight: 600; color: var(--slate-600); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; }
[data-theme="dark"] .setting-list thead th { background: var(--slate-900); color: var(--slate-400); }

.setting-list tbody tr:last-child td { border-bottom: 0; }
.setting-list tbody tr:hover td { background: var(--slate-50); }
[data-theme="dark"] .setting-list tbody tr:hover td { background: var(--slate-700); }

.setting-list code   { padding: 0.15em 0.4em; background: var(--slate-100); border-radius: 0.25rem; font-size: 0.85em; color: var(--slate-900); }
[data-theme="dark"] .setting-list code { background: var(--slate-900); color: var(--slate-100); }

.setting-cell-title  { font-weight: 600; }
.setting-cell-desc   { color: var(--slate-600); font-size: 0.85rem; }
[data-theme="dark"] .setting-cell-desc { color: var(--slate-400); }

.setting-col-edit    { text-align: right; }

.setting-badge       { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 0.375rem; font-size: 0.78rem; font-weight: 600; }
.setting-badge-saved { background: rgba(16,185,129,0.12); color: #047857; }
[data-theme="dark"] .setting-badge-saved { background: rgba(16,185,129,0.2); color: #34d399; }
.setting-badge-default { background: var(--slate-100); color: var(--slate-600); }
[data-theme="dark"] .setting-badge-default { background: var(--slate-700); color: var(--slate-300); }

.setting-btn-edit    { background: var(--slate-100); color: var(--slate-900); border: 1px solid var(--slate-200); padding: 0.35rem 0.75rem; border-radius: 0.375rem; font-size: 0.82rem; text-decoration: none; }
.setting-btn-edit:hover { background: var(--slate-200); }
[data-theme="dark"] .setting-btn-edit { background: var(--slate-700); color: var(--slate-100); border-color: var(--slate-600); }
[data-theme="dark"] .setting-btn-edit:hover { background: var(--slate-600); }

.setting-btn-sync    { background: #3b82f6; color: #fff; border: 1px solid #3b82f6; padding: 0.45rem 0.9rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; cursor: pointer; }
.setting-btn-sync:hover { background: #2563eb; border-color: #2563eb; }
[data-theme="dark"] .setting-btn-sync { background: #2563eb; border-color: #2563eb; }
[data-theme="dark"] .setting-btn-sync:hover { background: #1d4ed8; border-color: #1d4ed8; }

/* 편집 카드 */
.setting-card        { background: #fff; border: 1px solid var(--slate-200); border-radius: 0.75rem; padding: 1.25rem 1.5rem; }
[data-theme="dark"] .setting-card { background: var(--slate-800); border-color: var(--slate-700); }

.setting-card-desc   { font-size: 0.9rem; color: var(--slate-600); margin: 0 0 1rem; }
[data-theme="dark"] .setting-card-desc { color: var(--slate-400); }

.setting-notes-wrap     { margin: 0 0 1.25rem; }
.setting-notes-summary  { cursor: pointer; padding: 0.55rem 0.85rem; background: rgba(59,130,246,0.05); border: 1px solid rgba(59,130,246,0.25); border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--slate-700); list-style: none; user-select: none; }
.setting-notes-summary::-webkit-details-marker { display: none; }
.setting-notes-summary::before { content: "▶"; display: inline-block; margin-right: 0.45rem; font-size: 0.7em; transition: transform 0.15s; }
.setting-notes-wrap[open] > .setting-notes-summary::before { transform: rotate(90deg); }
.setting-notes-summary:hover { background: rgba(59,130,246,0.1); }
[data-theme="dark"] .setting-notes-summary { background: rgba(59,130,246,0.1); border-color: rgba(59,130,246,0.4); color: var(--slate-200); }
[data-theme="dark"] .setting-notes-summary:hover { background: rgba(59,130,246,0.18); }
.setting-notes-wrap[open] > .setting-notes-summary { border-radius: 0.5rem 0.5rem 0 0; border-bottom: 0; }
.setting-notes-wrap[open] > .setting-notes { border-top: 0; border-radius: 0 0 0.5rem 0.5rem; margin: 0; }

.setting-notes       { padding: 1rem 1.25rem; background: rgba(59,130,246,0.05); border: 1px solid rgba(59,130,246,0.25); border-radius: 0.5rem; font-size: 0.85rem; line-height: 1.55; color: var(--slate-700); }
[data-theme="dark"] .setting-notes { background: rgba(59,130,246,0.1); border-color: rgba(59,130,246,0.4); color: var(--slate-200); }
.setting-notes h4    { font-size: 0.95rem; font-weight: 700; margin: 0.6rem 0 0.4rem; color: var(--slate-900); }
.setting-notes h4:first-child { margin-top: 0; }
[data-theme="dark"] .setting-notes h4 { color: var(--slate-50); }
.setting-notes ul    { margin: 0.25rem 0 0.5rem 1.25rem; padding: 0; }
.setting-notes li    { margin: 0.15rem 0; }
.setting-notes code  { background: var(--slate-100); padding: 0.1em 0.35em; border-radius: 0.25rem; font-size: 0.92em; color: var(--slate-900); }
[data-theme="dark"] .setting-notes code { background: var(--slate-900); color: var(--slate-100); }
.setting-notes a     { color: #2563eb; text-decoration: underline; }
.setting-notes a:hover { color: #1d4ed8; }
[data-theme="dark"] .setting-notes a { color: #60a5fa; }
[data-theme="dark"] .setting-notes a:hover { color: #93c5fd; }
.setting-notes p     { margin: 0.4rem 0; }
.setting-notes em    { font-style: normal; color: #b91c1c; font-weight: 600; }
[data-theme="dark"] .setting-notes em { color: #fca5a5; }

.setting-form        { display: flex; flex-direction: column; gap: 0.9rem; }
.setting-field       { display: flex; flex-direction: column; gap: 0.35rem; }

.setting-label       { font-size: 0.85rem; font-weight: 600; color: var(--slate-900); }
[data-theme="dark"] .setting-label { color: var(--slate-100); }

.setting-req         { color: #ef4444; }

.setting-input       {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--slate-300);
    border-radius: 0.375rem;
    background: #fff;
    color: var(--slate-900);
    font-size: 0.9rem;
}
.setting-input:focus { outline: 2px solid var(--slate-400); outline-offset: -1px; }
[data-theme="dark"] .setting-input { background: var(--slate-900); border-color: var(--slate-700); color: var(--slate-100); }
[data-theme="dark"] .setting-input:focus { outline-color: var(--slate-500); }
[data-theme="dark"] .setting-input::placeholder { color: var(--slate-500); }

.setting-help        { font-size: 0.78rem; color: var(--slate-600); margin: 0; }
[data-theme="dark"] .setting-help { color: var(--slate-400); }

.setting-check       { display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.9rem; color: var(--slate-900); cursor: pointer; }
[data-theme="dark"] .setting-check { color: var(--slate-100); }

.setting-actions     { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
.setting-btn         { padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; border: 1px solid transparent; }

.setting-btn-save    { background: #2563eb; color: #fff; border-color: #2563eb; }
.setting-btn-save:hover { background: #1d4ed8; border-color: #1d4ed8; }
[data-theme="dark"] .setting-btn-save { background: #3b82f6; border-color: #3b82f6; }
[data-theme="dark"] .setting-btn-save:hover { background: #60a5fa; border-color: #60a5fa; }

.setting-btn-reset   { background: var(--slate-100); color: var(--slate-900); border-color: var(--slate-200); }
.setting-btn-reset:hover { background: var(--slate-200); }
[data-theme="dark"] .setting-btn-reset { background: var(--slate-700); color: var(--slate-100); border-color: var(--slate-600); }
[data-theme="dark"] .setting-btn-reset:hover { background: var(--slate-600); }

.setting-error       { padding: 0.6rem 0.8rem; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.3); color: #b91c1c; border-radius: 0.375rem; margin-bottom: 0.75rem; font-size: 0.85rem; }
[data-theme="dark"] .setting-error { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.4); color: #fca5a5; }
</style>

<?php admin_layout_end(); ?>
