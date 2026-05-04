<?php
// /admin/member_form — 모던 회원 추가/수정 폼.
// gnuboard adm/member_form.php 의 필드 구성을 그대로 가져오되 Tailwind 카드형 UI 로 재포장.
// 폼 action 은 /admin/member_form_update (그 파일이 chdir + 기존 adm/member_form_update.php 호출).
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_PATH.'/adm/admin.lib.php';

$sub_menu = '200100';   // 회원 관리
auth_check_menu($auth, $sub_menu, 'w');

$w     = isset($_GET['w']) ? $_GET['w'] : '';
$mb_id = isset($_GET['mb_id']) ? trim((string)$_GET['mb_id']) : '';
$sfl   = isset($_GET['sfl']) ? $_GET['sfl'] : '';
$stx   = isset($_GET['stx']) ? $_GET['stx'] : '';
$sst   = isset($_GET['sst']) ? $_GET['sst'] : '';
$sod   = isset($_GET['sod']) ? $_GET['sod'] : '';
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status = isset($_GET['status']) ? $_GET['status'] : '';

// 빈 기본 데이터
$mb_default = [
    'mb_id'=>'', 'mb_password'=>'', 'mb_name'=>'', 'mb_nick'=>'',
    'mb_level'=>(int)$config['cf_register_level'],
    'mb_email'=>'', 'mb_homepage'=>'', 'mb_hp'=>'', 'mb_tel'=>'',
    'mb_zip1'=>'', 'mb_zip2'=>'', 'mb_addr1'=>'', 'mb_addr2'=>'', 'mb_addr3'=>'',
    'mb_certify'=>'', 'mb_adult'=>0, 'mb_open'=>1, 'mb_mailling'=>1, 'mb_sms'=>1,
    'mb_marketing_agree'=>0, 'mb_thirdparty_agree'=>0,
    'mb_signature'=>'', 'mb_profile'=>'', 'mb_memo'=>'',
    'mb_point'=>0, 'mb_recommend'=>'',
    'mb_leave_date'=>'', 'mb_intercept_date'=>'',
];

if ($w === 'u') {
    if (!$mb_id) {
        header('Location: /admin/member_list', true, 302);
        exit;
    }
    $row = get_member($mb_id);
    if (empty($row['mb_id'])) {
        admin_layout_start('회원 수정', 'members');
        echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">존재하지 않는 회원자료입니다.</div></main>';
        admin_layout_end();
        exit;
    }
    if ($is_admin !== 'super' && $row['mb_level'] >= $member['mb_level']) {
        admin_layout_start('회원 수정', 'members');
        echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.</div></main>';
        admin_layout_end();
        exit;
    }
    $mb = array_merge($mb_default, $row);
    $page_title = '회원 수정';
} else {
    $mb = $mb_default;
    $page_title = '회원 추가';
}

// 헬퍼
$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// 회원권한 select (gnuboard 의 get_member_level_select 사용 — option 만 출력)
ob_start();
echo get_member_level_select('mb_level', 1, $member['mb_level'], $mb['mb_level']);
$level_select_html = ob_get_clean();

admin_layout_start($page_title, 'members');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-5xl w-full mx-auto">

    <header class="flex items-center gap-3 mb-6">
        <a href="/admin/member_list" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="목록으로">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold tracking-tight"><?php echo $h($page_title) ?></h2>
            <p class="text-xs text-slate-500 mt-0.5"><?php echo $w === 'u' ? $h($mb_id).' 의 정보를 수정합니다' : '새 회원을 추가합니다' ?></p>
        </div>
    </header>

    <form name="fmember" id="fmember" action="/admin/member_form_update" method="post" enctype="multipart/form-data" autocomplete="off" class="space-y-4">
        <input type="hidden" name="w"      value="<?php echo $h($w) ?>">
        <input type="hidden" name="sfl"    value="<?php echo $h($sfl) ?>">
        <input type="hidden" name="stx"    value="<?php echo $h($stx) ?>">
        <input type="hidden" name="sst"    value="<?php echo $h($sst) ?>">
        <input type="hidden" name="sod"    value="<?php echo $h($sod) ?>">
        <input type="hidden" name="page"   value="<?php echo (int)$page ?>">
        <input type="hidden" name="status" value="<?php echo $h($status) ?>">
        <input type="hidden" name="token"  value="<?php echo get_admin_token() ?>">

        <!-- 1. 기본 정보 -->
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">기본 정보</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="mb_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">아이디 <span class="text-admin-primary-600">*</span></label>
                    <input type="text" name="mb_id" id="mb_id" value="<?php echo $h($mb['mb_id']) ?>"
                           <?php echo $w==='u' ? 'readonly' : 'required' ?>
                           maxlength="20"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 <?php echo $w==='u' ? 'opacity-60' : '' ?> focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
                <div>
                    <label for="mb_password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        비밀번호 <?php echo $w!=='u' ? '<span class="text-admin-primary-600">*</span>' : '<span class="text-xs font-normal text-slate-400">(변경 시에만 입력)</span>' ?>
                    </label>
                    <input type="password" name="mb_password" id="mb_password" maxlength="20" autocomplete="new-password" <?php echo $w!=='u' ? 'required' : '' ?>
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                    <div id="mb_password_captcha_wrap" class="mt-2" style="display:none">
                        <?php
                        require_once G5_CAPTCHA_PATH.'/captcha.lib.php';
                        echo captcha_html();
                        ?>
                    </div>
                </div>
                <div>
                    <label for="mb_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">이름 <span class="text-admin-primary-600">*</span></label>
                    <input type="text" name="mb_name" id="mb_name" value="<?php echo $h($mb['mb_name']) ?>" required maxlength="20"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
                <div>
                    <label for="mb_nick" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">닉네임 <span class="text-admin-primary-600">*</span></label>
                    <input type="text" name="mb_nick" id="mb_nick" value="<?php echo $h($mb['mb_nick']) ?>" required maxlength="20"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
                <div>
                    <label for="mb_level" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">회원 권한</label>
                    <?php
                    // gnuboard 의 select 마크업을 가져온 뒤 토큰 클래스 부여 (간단 치환)
                    echo str_replace(
                        '<select id="mb_level" name="mb_level"',
                        '<select id="mb_level" name="mb_level" class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500"',
                        $level_select_html
                    );
                    ?>
                </div>
                <div>
                    <label for="mb_point" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">포인트</label>
                    <input type="number" name="mb_point" id="mb_point" value="<?php echo (int)$mb['mb_point'] ?>"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 tabular-nums focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
            </div>
        </section>

        <!-- 2. 연락처 -->
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">연락처</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="mb_email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">이메일 <span class="text-admin-primary-600">*</span></label>
                    <input type="email" name="mb_email" id="mb_email" value="<?php echo $h($mb['mb_email']) ?>" required maxlength="100"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
                <div>
                    <label for="mb_homepage" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">홈페이지</label>
                    <input type="url" name="mb_homepage" id="mb_homepage" value="<?php echo $h($mb['mb_homepage']) ?>" maxlength="255"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
                <div>
                    <label for="mb_hp" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">휴대폰</label>
                    <input type="tel" name="mb_hp" id="mb_hp" value="<?php echo $h($mb['mb_hp']) ?>" maxlength="20"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
                <div>
                    <label for="mb_tel" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">전화번호</label>
                    <input type="tel" name="mb_tel" id="mb_tel" value="<?php echo $h($mb['mb_tel']) ?>" maxlength="20"
                           class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
            </div>
        </section>

        <!-- 3. 주소 -->
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">주소</h3>
            <div class="space-y-3">
                <div class="flex gap-2">
                    <input type="text" name="mb_zip" id="mb_zip" value="<?php echo $h($mb['mb_zip1'].$mb['mb_zip2']) ?>" maxlength="6" placeholder="우편번호"
                           class="w-32 h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 tabular-nums focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500" readonly>
                    <button type="button" id="mb_zip_btn" class="h-10 px-4 rounded-md border border-slate-200 dark:border-slate-700 text-sm hover:bg-slate-50 dark:hover:bg-slate-800">우편번호 검색</button>
                </div>
                <input type="text" name="mb_addr1" id="mb_addr1" value="<?php echo $h($mb['mb_addr1']) ?>" placeholder="기본주소"
                       class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500" readonly>
                <input type="text" name="mb_addr2" id="mb_addr2" value="<?php echo $h($mb['mb_addr2']) ?>" placeholder="상세주소"
                       class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                <input type="text" name="mb_addr3" id="mb_addr3" value="<?php echo $h($mb['mb_addr3']) ?>" placeholder="참고주소"
                       class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                <input type="hidden" name="mb_addr_jibeon" value="<?php echo $h($mb['mb_addr_jibeon'] ?? '') ?>">
            </div>
        </section>

        <!-- 4. 본인인증 -->
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">본인인증</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">인증 방법</span>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach (['simple'=>'간편인증','hp'=>'휴대폰','ipin'=>'아이핀'] as $v=>$lbl) { ?>
                        <label class="inline-flex items-center gap-1.5 text-sm">
                            <input type="radio" name="mb_certify_case" value="<?php echo $v ?>" <?php echo $mb['mb_certify']===$v ? 'checked' : '' ?> class="accent-admin-primary-600">
                            <?php echo $lbl ?>
                        </label>
                        <?php } ?>
                    </div>
                </div>
                <div>
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">인증 여부 / 성인</span>
                    <div class="flex flex-wrap gap-3">
                        <label class="inline-flex items-center gap-1.5 text-sm">
                            <input type="checkbox" name="mb_certify" value="1" <?php echo !empty($mb['mb_certify']) ? 'checked' : '' ?> class="accent-admin-primary-600">
                            본인인증
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-sm">
                            <input type="checkbox" name="mb_adult" value="1" <?php echo !empty($mb['mb_adult']) ? 'checked' : '' ?> class="accent-admin-primary-600">
                            성인 인증
                        </label>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. 알림 / 동의 -->
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">알림 / 동의</h3>
            <div class="grid sm:grid-cols-2 gap-3 text-sm">
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="mb_open"     value="1" <?php echo !empty($mb['mb_open']) ? 'checked' : '' ?> class="accent-admin-primary-600">정보 공개</label>
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="mb_mailling" value="1" <?php echo !empty($mb['mb_mailling']) ? 'checked' : '' ?> class="accent-admin-primary-600">메일 수신</label>
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="mb_sms"      value="1" <?php echo !empty($mb['mb_sms']) ? 'checked' : '' ?> class="accent-admin-primary-600">SMS 수신</label>
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="mb_marketing_agree" value="1" <?php echo !empty($mb['mb_marketing_agree']) ? 'checked' : '' ?> class="accent-admin-primary-600">마케팅 정보 수신 동의</label>
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="mb_thirdparty_agree" value="1" <?php echo !empty($mb['mb_thirdparty_agree']) ? 'checked' : '' ?> class="accent-admin-primary-600">제3자 정보 제공 동의</label>
            </div>
        </section>

        <!-- 6. 회원 상태 -->
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">회원 상태</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="mb_leave_date" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">탈퇴일 (YYYYMMDD)</label>
                    <div class="flex gap-2">
                        <input type="text" name="mb_leave_date" id="mb_leave_date" value="<?php echo $h($mb['mb_leave_date']) ?>" maxlength="8" placeholder="비어있음"
                               class="flex-1 h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 tabular-nums focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                        <button type="button" onclick="document.getElementById('mb_leave_date').value=document.getElementById('mb_leave_date').value?'':<?php echo (int)date('Ymd') ?>;" class="h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 text-xs hover:bg-slate-50 dark:hover:bg-slate-800">오늘</button>
                    </div>
                </div>
                <div>
                    <label for="mb_intercept_date" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">차단일 (YYYYMMDD)</label>
                    <div class="flex gap-2">
                        <input type="text" name="mb_intercept_date" id="mb_intercept_date" value="<?php echo $h($mb['mb_intercept_date']) ?>" maxlength="8" placeholder="비어있음"
                               class="flex-1 h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 tabular-nums focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                        <button type="button" onclick="document.getElementById('mb_intercept_date').value=document.getElementById('mb_intercept_date').value?'':<?php echo (int)date('Ymd') ?>;" class="h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 text-xs hover:bg-slate-50 dark:hover:bg-slate-800">오늘</button>
                    </div>
                </div>
                <div class="sm:col-span-2 text-xs text-slate-500">날짜를 채우면 해당 날짜에 탈퇴/차단 처리됩니다. 비우면 정상.</div>
            </div>
        </section>

        <!-- 7. 인사말 / 메모 -->
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">서명 / 인사말 / 메모</h3>
            <div class="space-y-4">
                <div>
                    <label for="mb_signature" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">서명</label>
                    <textarea name="mb_signature" id="mb_signature" rows="3"
                              class="w-full px-3 py-2 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500 leading-relaxed"><?php echo $h($mb['mb_signature']) ?></textarea>
                </div>
                <div>
                    <label for="mb_profile" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">인사말 (자기소개)</label>
                    <textarea name="mb_profile" id="mb_profile" rows="4"
                              class="w-full px-3 py-2 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500 leading-relaxed"><?php echo $h($mb['mb_profile']) ?></textarea>
                </div>
                <div>
                    <label for="mb_memo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">관리자 메모 <span class="text-xs font-normal text-slate-400">(회원에겐 보이지 않음)</span></label>
                    <textarea name="mb_memo" id="mb_memo" rows="3"
                              class="w-full px-3 py-2 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500 leading-relaxed"><?php echo $h($mb['mb_memo']) ?></textarea>
                </div>
            </div>
        </section>

        <!-- 8. 추가 정보 (mb_1 ~ mb_10) -->
        <details class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <summary class="cursor-pointer p-5 sm:p-6 text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 select-none">추가 정보 (mb_1 ~ mb_10)</summary>
            <div class="px-5 sm:px-6 pb-5 sm:pb-6 grid sm:grid-cols-2 gap-3">
                <?php for ($i = 1; $i <= 10; $i++) { ?>
                <div>
                    <label for="mb_<?php echo $i ?>" class="block text-xs text-slate-500 mb-1">mb_<?php echo $i ?></label>
                    <input type="text" name="mb_<?php echo $i ?>" id="mb_<?php echo $i ?>" value="<?php echo $h($mb['mb_'.$i] ?? '') ?>" maxlength="255"
                           class="w-full h-9 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-admin-primary-500/30 focus:border-admin-primary-500">
                </div>
                <?php } ?>
            </div>
        </details>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="/admin/member_list" class="h-10 px-5 inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">취소</a>
            <button type="submit" class="h-10 px-5 inline-flex items-center rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium">
                <?php echo $w === 'u' ? '저장' : '회원 추가' ?>
            </button>
        </div>
    </form>

</main>

<script>
// 비밀번호 입력 시 캡챠 영역 토글 (gnuboard 의 chk_captcha 는 password 변경 시 검증 필수)
(function () {
    var pw = document.getElementById('mb_password');
    var wrap = document.getElementById('mb_password_captcha_wrap');
    if (!pw || !wrap) return;
    function sync() { wrap.style.display = pw.value ? '' : 'none'; }
    pw.addEventListener('input', sync);
    sync();
})();
</script>

<?php
admin_layout_end();
