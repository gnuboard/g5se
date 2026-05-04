<?php
/*
 * /admin/boardgroup_form — 그룹 추가/수정 폼.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

$sub_menu = '300200';
auth_check_menu($auth, $sub_menu, 'w');

$w     = isset($_GET['w']) ? (string)$_GET['w'] : '';
$gr_id = isset($_GET['gr_id']) ? trim((string)$_GET['gr_id']) : '';
$sfl   = isset($_GET['sfl']) ? (string)$_GET['sfl'] : '';
$stx   = isset($_GET['stx']) ? (string)$_GET['stx'] : '';
$sst   = isset($_GET['sst']) ? (string)$_GET['sst'] : '';
$sod   = isset($_GET['sod']) ? (string)$_GET['sod'] : '';
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($is_admin !== 'super' && $w === '') {
    admin_layout_start('그룹 생성', 'groups');
    echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">최고관리자만 접근 가능합니다.</div></main>';
    admin_layout_end();
    exit;
}

// gr_device 컬럼 보장
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_device` ENUM('both','pc','mobile') NOT NULL DEFAULT 'both' AFTER `gr_subject` ", false);

$gr_default = ['gr_id'=>'','gr_subject'=>'','gr_device'=>'both','gr_admin'=>'','gr_use_access'=>0,'gr_order'=>0];
for ($i = 1; $i <= 10; $i++) { $gr_default["gr_{$i}_subj"] = ''; $gr_default["gr_{$i}"] = ''; }

if ($w === 'u') {
    if (!$gr_id) { header('Location: /admin/boardgroup_list', true, 302); exit; }
    $row = sql_fetch(" select * from {$g5['group_table']} where gr_id = '".addslashes($gr_id)."' ");
    if (empty($row['gr_id'])) {
        admin_layout_start('그룹 수정', 'groups');
        echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">존재하지 않는 그룹입니다.</div></main>';
        admin_layout_end();
        exit;
    }
    $gr = array_merge($gr_default, $row);
    $page_title = '그룹 수정';

    $row_cnt = sql_fetch(" select count(*) as cnt from {$g5['group_member_table']} where gr_id = '".addslashes($gr_id)."' ");
    $member_count = (int)$row_cnt['cnt'];
} else {
    $gr = $gr_default;
    $page_title = '그룹 생성';
    $member_count = 0;
}

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

admin_layout_start($page_title, 'groups');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex items-center gap-3 mb-6">
        <a href="/admin/boardgroup_list" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="목록으로">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold tracking-tight"><?php echo $h($page_title) ?></h2>
            <p class="text-xs text-slate-500 mt-0.5"><?php echo $w==='u' ? $h($gr_id).' 그룹의 정보를 수정합니다' : '새 게시판 그룹을 만듭니다' ?></p>
        </div>
    </header>

    <form id="fboardgroup" action="/admin/boardgroup_form_update" method="post" autocomplete="off" class="space-y-4">
        <input type="hidden" name="w"     value="<?php echo $h($w) ?>">
        <input type="hidden" name="sfl"   value="<?php echo $h($sfl) ?>">
        <input type="hidden" name="stx"   value="<?php echo $h($stx) ?>">
        <input type="hidden" name="sst"   value="<?php echo $h($sst) ?>">
        <input type="hidden" name="sod"   value="<?php echo $h($sod) ?>">
        <input type="hidden" name="page"  value="<?php echo (int)$page ?>">
        <input type="hidden" name="token" value="<?php echo get_admin_token() ?>">

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">기본 정보</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="gr_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">그룹 ID <span class="text-admin-primary-600">*</span></label>
                    <input type="text" name="gr_id" id="gr_id" value="<?php echo $h($gr['gr_id']) ?>"
                           <?php echo $w==='u' ? 'readonly' : 'required pattern="[A-Za-z0-9_]+"' ?>
                           maxlength="10"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 <?php echo $w==='u' ? 'opacity-60' : '' ?> focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                    <?php if ($w !== 'u'): ?>
                    <p class="mt-1 text-xs text-slate-500">영문/숫자/_ 만 (공백 없이, 최대 10자)</p>
                    <?php else: ?>
                    <a href="<?php echo G5_BBS_URL ?>/group.php?gr_id=<?php echo urlencode($gr['gr_id']) ?>" class="mt-1 inline-block text-xs text-admin-primary-700 dark:text-admin-primary-300 hover:underline" target="_blank">→ 그룹 페이지 바로가기</a>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="gr_subject" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">그룹 제목 <span class="text-admin-primary-600">*</span></label>
                    <input type="text" name="gr_subject" id="gr_subject" value="<?php echo $h($gr['gr_subject']) ?>" required maxlength="255"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
                <div>
                    <label for="gr_device" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">접속 기기</label>
                    <select name="gr_device" id="gr_device" class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                        <option value="both"   <?php echo $gr['gr_device']==='both'  ?'selected':'' ?>>모두 (PC + 모바일)</option>
                        <option value="pc"     <?php echo $gr['gr_device']==='pc'    ?'selected':'' ?>>PC 전용</option>
                        <option value="mobile" <?php echo $gr['gr_device']==='mobile'?'selected':'' ?>>모바일 전용</option>
                    </select>
                </div>
                <div>
                    <label for="gr_admin" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">그룹 관리자</label>
                    <?php if ($is_admin === 'super'): ?>
                    <input type="text" name="gr_admin" id="gr_admin" value="<?php echo $h($gr['gr_admin']) ?>" maxlength="20"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500"
                           placeholder="회원 아이디">
                    <?php else: ?>
                    <input type="hidden" name="gr_admin" value="<?php echo $h($gr['gr_admin']) ?>">
                    <p class="h-10 px-3 inline-flex items-center text-slate-700 dark:text-slate-300"><?php echo $h($gr['gr_admin']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">접근 제어</h3>
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="gr_use_access" value="1" <?php echo $gr['gr_use_access']?'checked':'' ?> class="rounded border-slate-300">
                    <span class="text-sm text-slate-700 dark:text-slate-300">접근 회원 사용</span>
                </label>
                <span class="text-xs text-slate-500">— 켜면 지정한 회원만 그룹 내 게시판에 접근 가능</span>
            </div>
            <?php if ($w === 'u'): ?>
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-sm text-slate-700 dark:text-slate-300">접근 회원수 <strong><?php echo number_format($member_count) ?></strong>명</span>
                <a href="/admin/boardgroupmember_list?gr_id=<?php echo urlencode($gr_id) ?>" class="inline-flex items-center h-9 px-3 rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">접근 회원 관리</a>
            </div>
            <?php endif; ?>
        </section>

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <details>
                <summary class="cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-300 select-none">여분 필드 (gr_1 ~ gr_10) <span class="text-xs font-normal text-slate-400">— 펼치기</span></summary>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <div class="flex items-center gap-2">
                        <span class="w-6 text-xs font-mono text-slate-400 text-right"><?php echo $i ?></span>
                        <input type="text" name="gr_<?php echo $i ?>_subj" value="<?php echo $h($gr['gr_'.$i.'_subj']) ?>" placeholder="제목"
                               class="w-32 h-9 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <input type="text" name="gr_<?php echo $i ?>" value="<?php echo $h($gr['gr_'.$i]) ?>" placeholder="내용"
                               class="flex-1 h-9 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                    </div>
                <?php endfor; ?>
                </div>
            </details>
        </section>

        <div class="flex items-center justify-end gap-2">
            <a href="/admin/boardgroup_list" class="h-10 px-5 inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">취소</a>
            <button type="submit" class="h-10 px-6 inline-flex items-center rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-semibold">저장</button>
        </div>
    </form>

</main>

<?php
admin_layout_end();
