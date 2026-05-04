<?php
/*
 * /admin/content_form — 내용 추가/수정 폼.
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';
require_once G5_EDITOR_LIB;

$sub_menu = '300600';
auth_check_menu($auth, $sub_menu, 'w');

$w     = isset($_GET['w']) ? (string)$_GET['w'] : '';
$co_id = isset($_GET['co_id']) ? preg_replace('/[^a-z0-9_]/i', '', (string)$_GET['co_id']) : '';

// 컬럼 lazy migration (gnuboard 가 contentform 에서 함)
sql_query(" ALTER TABLE `{$g5['content_table']}` ADD `co_include_head` VARCHAR(255) NOT NULL, ADD `co_include_tail` VARCHAR(255) NOT NULL ", false);
sql_query(" ALTER TABLE `{$g5['content_table']}` ADD `co_tag_filter_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `co_content` ", false);
sql_query(" ALTER TABLE `{$g5['content_table']}` ADD `co_mobile_content` longtext NOT NULL AFTER `co_content` ", false);
sql_query(" ALTER TABLE `{$g5['content_table']}` ADD `co_skin` varchar(255) NOT NULL DEFAULT '' AFTER `co_mobile_content`, ADD `co_mobile_skin` varchar(255) NOT NULL DEFAULT '' AFTER `co_skin` ", false);

$co_default = [
    'co_id'=>'', 'co_subject'=>'', 'co_content'=>'', 'co_mobile_content'=>'',
    'co_include_head'=>'', 'co_include_tail'=>'', 'co_tag_filter_use'=>1,
    'co_html'=>2, 'co_skin'=>'basic', 'co_mobile_skin'=>'basic',
];

if ($w === 'u') {
    if (!$co_id) { header('Location: /admin/content_list', true, 302); exit; }
    $row = sql_fetch(" select * from {$g5['content_table']} where co_id = '".addslashes($co_id)."' ");
    if (empty($row['co_id'])) {
        admin_layout_start('내용 수정', 'contents');
        echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">등록된 자료가 없습니다.</div></main>';
        admin_layout_end();
        exit;
    }
    $co = array_merge($co_default, $row);
    $page_title = '내용 수정';
} else {
    $co = $co_default;
    $page_title = '내용 추가';
}

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// 스킨 select 마크업 (Tailwind 클래스 주입)
$select_cls = 'h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800';
ob_start();
echo get_skin_select('content', 'co_skin', 'co_skin', $co['co_skin'], 'required');
$skin_html = str_replace('<select ', '<select class="'.$select_cls.'" ', ob_get_clean());
ob_start();
echo get_mobile_skin_select('content', 'co_mobile_skin', 'co_mobile_skin', $co['co_mobile_skin'], 'required');
$mskin_html = str_replace('<select ', '<select class="'.$select_cls.'" ', ob_get_clean());

// 헤더/테일 이미지 미리보기
$himg_path = G5_DATA_PATH.'/content/'.$co['co_id'].'_h';
$timg_path = G5_DATA_PATH.'/content/'.$co['co_id'].'_t';
$himg_exists = $co['co_id'] && file_exists($himg_path);
$timg_exists = $co['co_id'] && file_exists($timg_path);

admin_layout_start($page_title, 'contents');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-5xl w-full mx-auto">

    <header class="flex items-center gap-3 mb-6">
        <a href="/admin/content_list" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="목록으로">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold tracking-tight"><?php echo $h($page_title) ?></h2>
            <p class="text-xs text-slate-500 mt-0.5"><?php echo $w==='u' ? $h($co_id).' 의 내용을 수정합니다' : '새 내용 페이지를 추가합니다' ?></p>
        </div>
    </header>

    <form id="frmcontentform" action="/admin/content_form_update" method="post" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="w"        value="<?php echo $h($w) ?>">
        <input type="hidden" name="co_html"  value="1">
        <input type="hidden" name="token"    value="<?php echo get_admin_token() ?>">

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">기본 정보</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="co_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">ID <span class="text-admin-primary-600">*</span></label>
                    <input type="text" name="co_id" id="co_id" value="<?php echo $h($co['co_id']) ?>"
                           <?php echo $w==='u' ? 'readonly' : 'required pattern="[A-Za-z0-9_]+"' ?> maxlength="20"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 <?php echo $w==='u' ? 'opacity-60' : '' ?> focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                    <?php if ($w!=='u'): ?>
                    <p class="mt-1 text-xs text-slate-500">영문/숫자/_ 만 (최대 20자)</p>
                    <?php else: ?>
                    <a href="<?php echo $h(get_pretty_url('content', $co_id)) ?>" target="_blank" class="mt-1 inline-block text-xs text-admin-primary-700 dark:text-admin-primary-300 hover:underline">→ 내용 페이지 보기</a>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="co_subject" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">제목 <span class="text-admin-primary-600">*</span></label>
                    <input type="text" name="co_subject" id="co_subject" value="<?php echo $h($co['co_subject']) ?>" required maxlength="255"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">스킨 디렉토리 <span class="text-admin-primary-600">*</span></label>
                    <?php echo $skin_html ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">모바일 스킨 <span class="text-admin-primary-600">*</span></label>
                    <?php echo $mskin_html ?>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">내용 (PC)</h3>
            <?php echo editor_html('co_content', get_text(html_purifier($co['co_content']), 0)) ?>
        </section>

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">모바일 내용 <span class="text-xs font-normal text-slate-400">— 비워두면 PC 내용이 사용됨</span></h3>
            <?php echo editor_html('co_mobile_content', get_text(html_purifier($co['co_mobile_content']), 0)) ?>
        </section>

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">파일 경로</h3>
            <div class="space-y-3">
                <div>
                    <label for="co_include_head" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">상단 파일 경로</label>
                    <input type="text" name="co_include_head" id="co_include_head" value="<?php echo $h($co['co_include_head']) ?>" maxlength="255"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono text-sm">
                    <p class="mt-1 text-xs text-slate-500">비워두면 기본 상단 파일 사용</p>
                </div>
                <div>
                    <label for="co_include_tail" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">하단 파일 경로</label>
                    <input type="text" name="co_include_tail" id="co_include_tail" value="<?php echo $h($co['co_include_tail']) ?>" maxlength="255"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono text-sm">
                    <p class="mt-1 text-xs text-slate-500">비워두면 기본 하단 파일 사용</p>
                </div>
            </div>
            <div id="admin_captcha_box" class="mt-4 hidden">
                <div class="rounded-md border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/30 p-3 text-amber-800 dark:text-amber-200 text-sm">
                    <p class="mb-2 font-medium">파일 경로를 변경하셨습니다 — 자동등록방지 입력</p>
                    <?php
                    require_once G5_CAPTCHA_PATH.'/captcha.lib.php';
                    echo captcha_html();
                    $captcha_js = chk_captcha_js();
                    ?>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">상단 / 하단 이미지</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="co_himg" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">상단 이미지</label>
                    <input type="file" name="co_himg" id="co_himg" accept="image/*"
                           class="block w-full text-sm file:mr-3 file:h-9 file:px-3 file:rounded-md file:border-0 file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-slate-700">
                    <?php if ($himg_exists): ?>
                    <div class="mt-3 rounded-md border border-slate-200 dark:border-slate-800 p-2">
                        <img src="<?php echo G5_DATA_URL ?>/content/<?php echo $h($co['co_id']) ?>_h" class="w-full h-auto rounded" alt="">
                        <label class="mt-2 inline-flex items-center gap-1.5 text-xs text-rose-700 dark:text-rose-300">
                            <input type="checkbox" name="co_himg_del" value="1" class="rounded border-slate-300"> 삭제
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="co_timg" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">하단 이미지</label>
                    <input type="file" name="co_timg" id="co_timg" accept="image/*"
                           class="block w-full text-sm file:mr-3 file:h-9 file:px-3 file:rounded-md file:border-0 file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-slate-700">
                    <?php if ($timg_exists): ?>
                    <div class="mt-3 rounded-md border border-slate-200 dark:border-slate-800 p-2">
                        <img src="<?php echo G5_DATA_URL ?>/content/<?php echo $h($co['co_id']) ?>_t" class="w-full h-auto rounded" alt="">
                        <label class="mt-2 inline-flex items-center gap-1.5 text-xs text-rose-700 dark:text-rose-300">
                            <input type="checkbox" name="co_timg_del" value="1" class="rounded border-slate-300"> 삭제
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="flex items-center justify-end gap-2">
            <a href="/admin/content_list" class="h-10 px-5 inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">취소</a>
            <button type="submit" class="h-10 px-6 inline-flex items-center rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-semibold">저장</button>
        </div>
    </form>

</main>

<script>
(function () {
    var orig_head = <?php echo json_encode((string)$co['co_include_head']) ?>;
    var orig_tail = <?php echo json_encode((string)$co['co_include_tail']) ?>;
    var captchaBox = document.getElementById('admin_captcha_box');
    var head = document.getElementById('co_include_head');
    var tail = document.getElementById('co_include_tail');
    var captchaUsed = false;

    function check() {
        var h = (head.value || '').trim(), t = (tail.value || '').trim();
        var changed = (h !== orig_head) || (t !== orig_tail);
        captchaBox.classList.toggle('hidden', !changed);
        captchaUsed = changed;
        if (changed) {
            // 서버 세션에 캡챠 사용 플래그 세팅
            try { jQuery.post(g5_admin_url + '/ajax.use_captcha.php', { admin_use_captcha: '1' }); } catch (e) {}
        }
    }
    if (head && tail) {
        head.addEventListener('input', check);
        tail.addEventListener('input', check);
    }

    var f = document.getElementById('frmcontentform');
    if (f) f.addEventListener('submit', function (e) {
        var coContent = (f.co_content && f.co_content.value || '').trim();
        if (!coContent) {
            e.preventDefault();
            alert('내용을 입력해 주십시오.');
            f.co_content && f.co_content.focus();
            return;
        }
        if (captchaUsed) {
            <?php echo $captcha_js; ?>
        }
    });
})();
</script>

<?php
admin_layout_end();
