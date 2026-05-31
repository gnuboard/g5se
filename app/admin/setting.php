<?php
/**
 * /admin/setting — 사이트 설정 관리
 *
 * SETTINGS_SCHEMA 의 모든 그룹을 카드로 렌더. admin 은 값만 편집.
 * super-admin gate + CSRF + PRG (POST 는 별도 task).
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

// 토스트용 GET 파라미터 — 등록된 키만 허용
$_saved_key = (isset($_GET['saved']) && isset($_schemas[$_GET['saved']])) ? (string)$_GET['saved'] : '';
$_reset_key = (isset($_GET['reset']) && isset($_schemas[$_GET['reset']])) ? (string)$_GET['reset'] : '';

// ── POST 처리 (save) ──────────────────────────────────────────────
$_action = isset($_POST['action']) ? (string)$_POST['action'] : '';
$_post_key = isset($_POST['key']) ? (string)$_POST['key'] : '';
$_form_values = isset($_POST['v']) && is_array($_POST['v']) ? $_POST['v'] : [];

if ($_action && (!isset($_POST['token']) || !hash_equals($_csrf, (string)$_POST['token']))) {
    alert('보안 토큰이 일치하지 않습니다.');
}

$_errors = [];
$_values_override = [];

// save 액션: 검증 → setting_put → PRG redirect
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
        // 검증 실패 — 같은 페이지 재렌더 (입력값 유지 + 에러 표시)
        $_errors[$_post_key] = implode(' / ', $errors);
        // 사용자가 막 입력한 값을 prefill 로 사용 — 단 password 는 폼에서 항상 빈 input
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
        // setting_put 성공 후 CSRF 발급 + PRG
        setting_put($_post_key, $values_to_put);
        $_SESSION['_setting_token'] = bin2hex(random_bytes(16));
        header('Location: /admin/setting?saved='.urlencode($_post_key), true, 303);
        exit;
    }
}

// 그룹별 현재 값. POST 검증 실패 시 $_values_override 에 부분 채워짐.
$_values = [];
foreach ($_schemas as $key => $schema) {
    if (isset($_values_override[$key])) {
        $_values[$key] = $_values_override[$key];
        continue;
    }
    try {
        $_values[$key] = setting($key);
    } catch (\Throwable $e) {
        $_values[$key] = [];
        if (!isset($_errors[$key])) {
            $_errors[$key] = 'g5_setting 테이블이 없습니다. /admin/db_migrate 에서 생성하세요.';
        }
    }
}

admin_layout_start($g5['title'], 'core');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h1 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']); ?></h1>
</header>

<?php if ($_saved_key) { ?>
<div class="setting-toast setting-toast-ok"><?php echo htmlspecialchars($_saved_key); ?> 설정이 저장되었습니다.</div>
<?php } ?>
<?php if ($_reset_key) { ?>
<div class="setting-toast setting-toast-ok"><?php echo htmlspecialchars($_reset_key); ?> 설정이 기본값으로 리셋되었습니다.</div>
<?php } ?>

<div class="setting-cards">

<?php if (!$_schemas) { ?>
    <div class="setting-empty">아직 등록된 설정 schema 가 없습니다. <code>app/lib/setting.lib.php</code> 의 <code>SETTINGS_SCHEMA</code> 배열에 항목을 추가하세요.</div>
<?php } ?>

<?php foreach ($_schemas as $key => $schema) { ?>
    <section class="setting-card" id="card-<?php echo htmlspecialchars($key); ?>">
        <header class="setting-card-head">
            <h2 class="setting-card-title"><?php echo htmlspecialchars($schema['title']); ?></h2>
            <?php if (!empty($schema['description'])) { ?>
            <p class="setting-card-desc"><?php echo htmlspecialchars($schema['description']); ?></p>
            <?php } ?>
        </header>

        <?php if (isset($_errors[$key])) { ?>
        <div class="setting-error"><?php echo htmlspecialchars($_errors[$key]); ?></div>
        <?php } ?>

        <form method="post" class="setting-form">
            <input type="hidden" name="token" value="<?php echo $_csrf; ?>">
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
                        onclick="return confirm('<?php echo htmlspecialchars($schema['title']); ?> 설정을 기본값으로 리셋합니다 (저장된 값 손실). 계속하시겠습니까?');">↺ 기본값으로 리셋</button>
            </div>
        </form>
    </section>
<?php } ?>

</div><!-- /.setting-cards -->
</main>

<style>
.setting-cards { display: flex; flex-direction: column; gap: 1.25rem; max-width: none; }
.setting-card {
    background: var(--m-surface, #fff);
    border: 1px solid var(--m-border, #e2e8f0);
    border-radius: 0.75rem;
    padding: 1.25rem 1.5rem;
}
.setting-card-head { margin-bottom: 1rem; }
.setting-card-title { font-size: 1.05rem; font-weight: 700; margin: 0 0 0.25rem; color: var(--m-text, #0f172a); }
.setting-card-desc  { font-size: 0.85rem; color: var(--m-text-muted, #64748b); margin: 0; }
.setting-form       { display: flex; flex-direction: column; gap: 0.9rem; }
.setting-field      { display: flex; flex-direction: column; gap: 0.35rem; }
.setting-label      { font-size: 0.85rem; font-weight: 600; color: var(--m-text, #0f172a); }
.setting-req        { color: #ef4444; }
.setting-input      {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--m-border, #cbd5e1);
    border-radius: 0.375rem;
    background: var(--m-surface, #fff);
    color: var(--m-text, #0f172a);
    font-size: 0.9rem;
}
.setting-input:focus { outline: 2px solid var(--m-border-hover, #94a3b8); outline-offset: -1px; }
.setting-help       { font-size: 0.78rem; color: var(--m-text-muted, #64748b); margin: 0; }
.setting-check      { display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.9rem; color: var(--m-text, #0f172a); cursor: pointer; }
.setting-actions    { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
.setting-btn        { padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; border: 1px solid transparent; }
.setting-btn-save   { background: #2563eb; color: #fff; border-color: #2563eb; }
.setting-btn-save:hover  { background: #1d4ed8; border-color: #1d4ed8; }
.setting-btn-reset  { background: var(--m-surface-2, #f1f5f9); color: var(--m-text, #0f172a); border-color: var(--m-border, #cbd5e1); }
.setting-btn-reset:hover { background: var(--m-border, #cbd5e1); }
.setting-empty      { padding: 1.5rem; text-align: center; color: var(--m-text-muted, #64748b); background: var(--m-surface, #fff); border: 1px dashed var(--m-border, #cbd5e1); border-radius: 0.5rem; }
.setting-error      { padding: 0.6rem 0.8rem; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.3); color: #b91c1c; border-radius: 0.375rem; margin-bottom: 0.75rem; font-size: 0.85rem; }
.setting-toast      { padding: 0.6rem 0.9rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.9rem; font-weight: 500; }
.setting-toast-ok   { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #047857; }
</style>

<?php admin_layout_end(); ?>
