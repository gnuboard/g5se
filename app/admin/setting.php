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

// CSRF 토큰 (세션 키 _setting_token)
if (!isset($_SESSION['_setting_token'])) {
    $_SESSION['_setting_token'] = bin2hex(random_bytes(16));
}
$_csrf = $_SESSION['_setting_token'];

$_schemas = setting_schemas();

// 모드 결정: ?key 가 등록된 스키마면 edit, 아니면 list
$_edit_key = (isset($_GET['key']) && isset($_schemas[$_GET['key']])) ? (string)$_GET['key'] : '';

// ── POST 처리 ─────────────────────────────────────────────────────
$_action = isset($_POST['action']) ? (string)$_POST['action'] : '';
$_post_key = isset($_POST['key']) ? (string)$_POST['key'] : '';
$_form_values = isset($_POST['v']) && is_array($_POST['v']) ? $_POST['v'] : [];

if ($_action && (!isset($_POST['token']) || !hash_equals($_csrf, (string)$_POST['token']))) {
    alert('보안 토큰이 일치하지 않습니다.');
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
        $_SESSION['_setting_token'] = bin2hex(random_bytes(16));
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
    $_SESSION['_setting_token'] = bin2hex(random_bytes(16));
    $_SESSION['_setting_flash'] = ['type' => 'reset', 'key' => $_post_key];
    header('Location: /admin/setting?key='.urlencode($_post_key), true, 303);
    exit;
}

// ── 플래시 읽기 (1 회) ───────────────────────────────────────────
$_flash = $_SESSION['_setting_flash'] ?? null;
unset($_SESSION['_setting_flash']);
if ($_flash && (!isset($_flash['key']) || !isset($_schemas[$_flash['key']]))) {
    $_flash = null;
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
    try {
        $_rs = sql_pdo_query("SELECT s_key FROM `".G5_TABLE_PREFIX."setting`");
        while ($_r = sql_fetch_array($_rs)) {
            $_saved_keys[$_r['s_key']] = true;
        }
    } catch (\Throwable $e) {
        // 테이블 미생성 — 모두 default 로 표시
    }
}

admin_layout_start($g5['title'], 'core');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

<?php if ($_edit_key === '') { ?>
    <header class="flex items-center gap-3 mb-5">
        <h1 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']); ?></h1>
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

<?php if ($_flash) {
    $_flash_title = $_schemas[$_flash['key']]['title'];
?>
<div class="setting-toast setting-toast-ok">
    <?php if ($_flash['type'] === 'saved') {
        echo htmlspecialchars($_flash_title).' 설정이 저장되었습니다.';
    } elseif ($_flash['type'] === 'reset') {
        echo htmlspecialchars($_flash_title).' 설정이 기본값으로 리셋되었습니다.';
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

        <?php if (isset($_errors[$key])) { ?>
        <div class="setting-error"><?php echo htmlspecialchars($_errors[$key]); ?></div>
        <?php } ?>

        <form method="post" class="setting-form">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_csrf); ?>">
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
/* 공통 */
.setting-toast       { padding: 0.6rem 0.9rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.9rem; font-weight: 500; }
.setting-toast-ok    { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #047857; }
.setting-empty       { padding: 1.5rem; text-align: center; color: var(--m-text-muted, #64748b); background: var(--m-surface, #fff); border: 1px dashed var(--m-border, #cbd5e1); border-radius: 0.5rem; }
.setting-back        { font-size: 0.85rem; color: var(--m-text-muted, #64748b); text-decoration: none; padding: 0.3rem 0.6rem; border-radius: 0.375rem; }
.setting-back:hover  { background: var(--m-surface-2, #f1f5f9); color: var(--m-text, #0f172a); }

/* 목록 테이블 */
.setting-list-wrap   { background: var(--m-surface, #fff); border: 1px solid var(--m-border, #e2e8f0); border-radius: 0.75rem; overflow: hidden; }
.setting-list        { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.setting-list th,
.setting-list td     { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--m-border, #e2e8f0); vertical-align: middle; }
.setting-list thead th { background: var(--m-surface-2, #f1f5f9); font-weight: 600; color: var(--m-text-muted, #64748b); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; }
.setting-list tbody tr:last-child td { border-bottom: 0; }
.setting-list code   { padding: 0.15em 0.4em; background: var(--m-surface-2, #f1f5f9); border-radius: 0.25rem; font-size: 0.85em; color: var(--m-text, #0f172a); }
.setting-cell-title  { font-weight: 600; color: var(--m-text, #0f172a); }
.setting-cell-desc   { color: var(--m-text-muted, #64748b); font-size: 0.85rem; }
.setting-col-edit    { text-align: right; }
.setting-badge       { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 0.375rem; font-size: 0.78rem; font-weight: 600; }
.setting-badge-saved { background: rgba(16,185,129,0.12); color: #047857; }
.setting-badge-default { background: var(--m-surface-2, #f1f5f9); color: var(--m-text-muted, #64748b); }
.setting-btn-edit    { background: var(--m-surface-2, #f1f5f9); color: var(--m-text, #0f172a); border: 1px solid var(--m-border, #cbd5e1); padding: 0.35rem 0.75rem; border-radius: 0.375rem; font-size: 0.82rem; text-decoration: none; }
.setting-btn-edit:hover { background: var(--m-border, #cbd5e1); }

/* 편집 카드 */
.setting-card {
    background: var(--m-surface, #fff);
    border: 1px solid var(--m-border, #e2e8f0);
    border-radius: 0.75rem;
    padding: 1.25rem 1.5rem;
}
.setting-card-desc   { font-size: 0.9rem; color: var(--m-text-muted, #64748b); margin: 0 0 1rem; }
.setting-form        { display: flex; flex-direction: column; gap: 0.9rem; }
.setting-field       { display: flex; flex-direction: column; gap: 0.35rem; }
.setting-label       { font-size: 0.85rem; font-weight: 600; color: var(--m-text, #0f172a); }
.setting-req         { color: #ef4444; }
.setting-input       {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--m-border, #cbd5e1);
    border-radius: 0.375rem;
    background: var(--m-surface, #fff);
    color: var(--m-text, #0f172a);
    font-size: 0.9rem;
}
.setting-input:focus { outline: 2px solid var(--m-border-hover, #94a3b8); outline-offset: -1px; }
.setting-help        { font-size: 0.78rem; color: var(--m-text-muted, #64748b); margin: 0; }
.setting-check       { display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.9rem; color: var(--m-text, #0f172a); cursor: pointer; }
.setting-actions     { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
.setting-btn         { padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; border: 1px solid transparent; }
.setting-btn-save    { background: #2563eb; color: #fff; border-color: #2563eb; }
.setting-btn-save:hover { background: #1d4ed8; border-color: #1d4ed8; }
.setting-btn-reset   { background: var(--m-surface-2, #f1f5f9); color: var(--m-text, #0f172a); border-color: var(--m-border, #cbd5e1); }
.setting-btn-reset:hover { background: var(--m-border, #cbd5e1); }
.setting-error       { padding: 0.6rem 0.8rem; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.3); color: #b91c1c; border-radius: 0.375rem; margin-bottom: 0.75rem; font-size: 0.85rem; }
</style>

<?php admin_layout_end(); ?>
