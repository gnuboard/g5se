<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');

add_javascript('<script src="'.G5_JS_URL.'/jquery.register_form.js"></script>', 0);
if ($config['cf_cert_use'] && ($config['cf_cert_simple'] || $config['cf_cert_ipin'] || $config['cf_cert_hp']))
    add_javascript('<script src="'.G5_JS_URL.'/certify.js?v='.G5_JS_VER.'"></script>', 0);
?>

<!-- 회원정보 입력/수정 시작 { -->
<div class="m-shell">

    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-center" style="align-items: start;">
    <div style="width: 100%; max-width: 720px;">

        <div style="margin-bottom: 24px; text-align: center;">
            <h1 style="font-size: 24px; margin-bottom: 6px;"><?php echo $w == '' ? '회원가입' : '회원정보 수정' ?></h1>
            <p style="font-size: 13px; color: var(--m-text-muted);">
                <?php echo $w == '' ? '계정 정보를 입력해 주세요.' : '회원정보를 수정합니다.' ?>
            </p>
        </div>

        <form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">

            <input type="hidden" name="w"          value="<?php echo $w ?>">
            <input type="hidden" name="url"        value="<?php echo $urlencode ?>">
            <input type="hidden" name="agree"      value="<?php echo $agree ?>">
            <input type="hidden" name="agree2"     value="<?php echo $agree2 ?>">
            <input type="hidden" name="cert_type"  value="<?php echo $member['mb_certify']; ?>">
            <input type="hidden" name="cert_no"    value="">
            <?php if (isset($member['mb_sex'])) { ?>
            <input type="hidden" name="mb_sex"     value="<?php echo $member['mb_sex'] ?>">
            <?php } ?>
            <?php if (isset($member['mb_nick_date']) && $member['mb_nick_date'] > date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400))) { ?>
            <input type="hidden" name="mb_nick_default" value="<?php echo get_text($member['mb_nick']) ?>">
            <input type="hidden" name="mb_nick"         value="<?php echo get_text($member['mb_nick']) ?>">
            <?php } ?>

            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <!-- 1. 계정 정보 -->
            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <div class="m-card m-form-section">
                <h2 class="m-section-title">계정 정보</h2>

                <div class="m-form-row">
                    <label for="reg_mb_id" class="m-label">아이디 (필수)</label>
                    <input type="text" name="mb_id" id="reg_mb_id" class="m-input" minlength="3" maxlength="20" placeholder="영문, 숫자, _ (3자 이상)" value="<?php echo $member['mb_id'] ?>" <?php echo $required.' '.$readonly ?>>
                    <span id="msg_mb_id" class="m-form-msg"></span>
                </div>

                <div class="m-form-grid-2">
                    <div class="m-form-row">
                        <label for="reg_mb_password" class="m-label">비밀번호 (필수)</label>
                        <input type="password" name="mb_password" id="reg_mb_password" class="m-input" minlength="3" maxlength="20" placeholder="비밀번호" <?php echo $required ?>>
                    </div>
                    <div class="m-form-row">
                        <label for="reg_mb_password_re" class="m-label">비밀번호 확인 (필수)</label>
                        <input type="password" name="mb_password_re" id="reg_mb_password_re" class="m-input" minlength="3" maxlength="20" placeholder="비밀번호 확인" <?php echo $required ?>>
                    </div>
                </div>
            </div>

            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <!-- 2. 개인정보 -->
            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <div class="m-card m-form-section">
                <h2 class="m-section-title">개인 정보</h2>

                <?php if ($config['cf_cert_use']) {
                    $desc_name  = '<span class="m-form-hint">본인확인 시 자동입력</span>';
                    $desc_phone = '<span class="m-form-hint">본인확인 시 자동입력</span>';
                    if (!$config['cf_cert_simple'] && !$config['cf_cert_hp'] && $config['cf_cert_ipin']) {
                        $desc_phone = '';
                    }
                ?>
                <div class="m-form-row">
                    <label class="m-label">본인확인 (필수)</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php if ($config['cf_cert_simple']) { ?>
                        <button type="button" id="win_sa_kakao_cert" class="m-btn m-btn-secondary win_sa_cert" style="width:auto; padding: 8px 14px;" data-type="">간편인증</button>
                        <?php } ?>
                        <?php if ($config['cf_cert_hp']) { ?>
                        <button type="button" id="win_hp_cert" class="m-btn m-btn-secondary" style="width:auto; padding: 8px 14px;">휴대폰 본인확인</button>
                        <?php } ?>
                        <?php if ($config['cf_cert_ipin']) { ?>
                        <button type="button" id="win_ipin_cert" class="m-btn m-btn-secondary" style="width:auto; padding: 8px 14px;">아이핀 본인확인</button>
                        <?php } ?>
                    </div>
                    <noscript><div class="m-form-hint">본인확인을 위해서는 자바스크립트 사용이 가능해야 합니다.</div></noscript>
                    <?php if ($member['mb_certify']) {
                        $mb_cert_label = ['simple'=>'간편인증','ipin'=>'아이핀','hp'=>'휴대폰'][$member['mb_certify']] ?? $member['mb_certify'];
                    ?>
                    <div id="msg_certify" class="m-form-success">
                        <strong><?php echo $mb_cert_label ?> 본인확인</strong><?php if ($member['mb_adult']) { ?> 및 <strong>성인인증</strong><?php } ?> 완료
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>

                <div class="m-form-row">
                    <label for="reg_mb_name" class="m-label">이름 (필수) <?php echo $desc_name ?? '' ?></label>
                    <input type="text" id="reg_mb_name" name="mb_name" class="m-input" placeholder="이름" value="<?php echo get_text($member['mb_name']) ?>" <?php echo $required.' '.$name_readonly ?>>
                </div>

                <?php if ($req_nick) { ?>
                <div class="m-form-row">
                    <label for="reg_mb_nick" class="m-label">닉네임 (필수)</label>
                    <input type="hidden" name="mb_nick_default" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : '' ?>">
                    <input type="text" name="mb_nick" id="reg_mb_nick" class="m-input nospace" maxlength="20" placeholder="한글 2자 또는 영문 4자 이상" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : '' ?>" required>
                    <span id="msg_mb_nick" class="m-form-msg"></span>
                </div>
                <?php } ?>

                <div class="m-form-row">
                    <label for="reg_mb_email" class="m-label">E-mail (필수)</label>
                    <input type="hidden" name="old_email" value="<?php echo $member['mb_email'] ?>">
                    <input type="email" name="mb_email" id="reg_mb_email" class="m-input email" maxlength="100" placeholder="example@email.com" value="<?php echo isset($member['mb_email']) ? $member['mb_email'] : '' ?>" required>
                    <?php if ($config['cf_use_email_certify']) { ?>
                    <span class="m-form-hint">
                        <?php echo $w == '' ? 'E-mail 로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다.' : 'E-mail 주소를 변경하시면 다시 인증하셔야 합니다.' ?>
                    </span>
                    <?php } ?>
                </div>

                <?php if ($config['cf_use_homepage']) { ?>
                <div class="m-form-row">
                    <label for="reg_mb_homepage" class="m-label">홈페이지<?php if ($config['cf_req_homepage']) echo ' (필수)' ?></label>
                    <input type="text" name="mb_homepage" id="reg_mb_homepage" class="m-input" maxlength="255" placeholder="https://" value="<?php echo get_text($member['mb_homepage']) ?>" <?php echo $config['cf_req_homepage'] ? 'required' : '' ?>>
                </div>
                <?php } ?>

                <?php if ($config['cf_use_tel']) { ?>
                <div class="m-form-row">
                    <label for="reg_mb_tel" class="m-label">전화번호<?php if ($config['cf_req_tel']) echo ' (필수)' ?></label>
                    <input type="tel" name="mb_tel" id="reg_mb_tel" class="m-input" maxlength="20" placeholder="02-1234-5678" value="<?php echo get_text($member['mb_tel']) ?>" <?php echo $config['cf_req_tel'] ? 'required' : '' ?>>
                </div>
                <?php } ?>

                <?php if ($config['cf_use_hp'] || ($config['cf_cert_use'] && ($config['cf_cert_hp'] || $config['cf_cert_simple']))) { ?>
                <div class="m-form-row">
                    <label for="reg_mb_hp" class="m-label">휴대폰번호<?php if (!empty($hp_required)) echo ' (필수)' ?> <?php echo $desc_phone ?? '' ?></label>
                    <input type="tel" name="mb_hp" id="reg_mb_hp" class="m-input" maxlength="20" placeholder="010-0000-0000" value="<?php echo get_text($member['mb_hp']) ?>" <?php echo $hp_required.' '.$hp_readonly ?>>
                    <?php if ($config['cf_cert_use'] && ($config['cf_cert_hp'] || $config['cf_cert_simple'])) { ?>
                    <input type="hidden" name="old_mb_hp" value="<?php echo get_text($member['mb_hp']) ?>">
                    <?php } ?>
                </div>
                <?php } ?>

                <?php if ($config['cf_use_addr']) { ?>
                <div class="m-form-row">
                    <label class="m-label">주소<?php if ($config['cf_req_addr']) echo ' (필수)' ?></label>
                    <div class="m-input-with-action" style="margin-bottom: 8px;">
                        <input type="text" name="mb_zip" id="reg_mb_zip" class="m-input" size="5" maxlength="6" placeholder="우편번호" value="<?php echo $member['mb_zip1'].$member['mb_zip2'] ?>" <?php echo $config['cf_req_addr'] ? 'required' : '' ?> readonly>
                        <button type="button" class="m-btn m-btn-secondary" style="width: auto; padding: 8px 14px;" onclick="win_zip('fregisterform', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');">주소 검색</button>
                    </div>
                    <input type="text" name="mb_addr1" id="reg_mb_addr1" class="m-input" placeholder="기본주소" value="<?php echo get_text($member['mb_addr1']) ?>" <?php echo $config['cf_req_addr'] ? 'required' : '' ?> style="margin-bottom: 8px;">
                    <input type="text" name="mb_addr2" id="reg_mb_addr2" class="m-input" placeholder="상세주소" value="<?php echo get_text($member['mb_addr2']) ?>" style="margin-bottom: 8px;">
                    <input type="text" name="mb_addr3" id="reg_mb_addr3" class="m-input" placeholder="참고항목" value="<?php echo get_text($member['mb_addr3']) ?>" readonly>
                    <input type="hidden" name="mb_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']) ?>">
                </div>
                <?php } ?>
            </div>

            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <!-- 3. 기타 개인설정 -->
            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <?php $has_extra = $config['cf_use_signature'] || $config['cf_use_profile'] || ($config['cf_use_member_icon'] && $member['mb_level'] >= $config['cf_icon_level']) || ($w == '' && $config['cf_use_recommend']); ?>
            <?php if ($has_extra) { ?>
            <div class="m-card m-form-section">
                <h2 class="m-section-title">기타 설정</h2>

                <?php if ($config['cf_use_signature']) { ?>
                <div class="m-form-row">
                    <label for="reg_mb_signature" class="m-label">서명<?php if ($config['cf_req_signature']) echo ' (필수)' ?></label>
                    <textarea name="mb_signature" id="reg_mb_signature" class="m-input m-textarea" placeholder="서명" <?php echo $config['cf_req_signature'] ? 'required' : '' ?>><?php echo $member['mb_signature'] ?></textarea>
                </div>
                <?php } ?>

                <?php if ($config['cf_use_profile']) { ?>
                <div class="m-form-row">
                    <label for="reg_mb_profile" class="m-label">자기소개</label>
                    <textarea name="mb_profile" id="reg_mb_profile" class="m-input m-textarea" placeholder="자기소개" <?php echo $config['cf_req_profile'] ? 'required' : '' ?>><?php echo $member['mb_profile'] ?></textarea>
                </div>
                <?php } ?>

                <?php if ($config['cf_use_member_icon'] && $member['mb_level'] >= $config['cf_icon_level']) { ?>
                <div class="m-form-row">
                    <label for="reg_mb_icon" class="m-label">회원 아이콘</label>
                    <input type="file" name="mb_icon" id="reg_mb_icon" accept="image/gif,image/jpeg,image/png" class="m-input m-file">
                    <span class="m-form-hint">최대 <?php echo $config['cf_member_icon_width'] ?>×<?php echo $config['cf_member_icon_height'] ?>px, gif/jpg/png</span>
                    <?php if ($w == 'u' && file_exists($mb_icon_path)) { ?>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                        <img src="<?php echo $mb_icon_url ?>" alt="회원아이콘" style="border: 1px solid var(--m-border); border-radius: 4px;">
                        <label class="m-check"><input type="checkbox" name="del_mb_icon" value="1" id="del_mb_icon"><span>삭제</span></label>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>

                <?php if ($member['mb_level'] >= $config['cf_icon_level'] && $config['cf_member_img_size'] && $config['cf_member_img_width'] && $config['cf_member_img_height']) { ?>
                <div class="m-form-row">
                    <label for="reg_mb_img" class="m-label">회원 이미지</label>
                    <input type="file" name="mb_img" id="reg_mb_img" accept="image/gif,image/jpeg,image/png" class="m-input m-file">
                    <span class="m-form-hint">최대 <?php echo $config['cf_member_img_width'] ?>×<?php echo $config['cf_member_img_height'] ?>px, gif/jpg/png</span>
                    <?php if ($w == 'u' && file_exists($mb_img_path)) { ?>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                        <img src="<?php echo $mb_img_url ?>" alt="회원이미지" style="border: 1px solid var(--m-border); border-radius: 4px; max-height: 80px;">
                        <label class="m-check"><input type="checkbox" name="del_mb_img" value="1" id="del_mb_img"><span>삭제</span></label>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>

                <?php if (isset($member['mb_open_date']) && $member['mb_open_date'] <= date("Y-m-d", G5_SERVER_TIME - ($config['cf_open_modify'] * 86400)) || empty($member['mb_open_date'])) { ?>
                <div class="m-form-row">
                    <label class="m-check m-check-block">
                        <input type="checkbox" name="mb_open" value="1" id="reg_mb_open" <?php echo ($w == '' || $member['mb_open']) ? 'checked' : '' ?>>
                        <span>다른 회원이 내 정보를 볼 수 있도록 허용 <span class="m-form-hint" style="margin-left: 4px;">(변경 후 <?php echo (int)$config['cf_open_modify'] ?>일간 재변경 불가)</span></span>
                    </label>
                    <input type="hidden" name="mb_open_default" value="<?php echo $member['mb_open'] ?>">
                </div>
                <?php } else { ?>
                <div class="m-form-row">
                    <span class="m-form-hint">정보공개는 <?php echo date("Y년 m월 j일", isset($member['mb_open_date']) ? strtotime("{$member['mb_open_date']} 00:00:00") + $config['cf_open_modify'] * 86400 : G5_SERVER_TIME + $config['cf_open_modify'] * 86400) ?> 까지 변경할 수 없습니다.</span>
                    <input type="hidden" name="mb_open" value="<?php echo $member['mb_open'] ?>">
                </div>
                <?php } ?>

                <?php if ($w == 'u' && function_exists('social_member_provider_manage')) social_member_provider_manage(); ?>

                <?php if ($w == '' && $config['cf_use_recommend']) { ?>
                <div class="m-form-row">
                    <label for="reg_mb_recommend" class="m-label">추천인 아이디</label>
                    <input type="text" name="mb_recommend" id="reg_mb_recommend" class="m-input" placeholder="추천인 아이디 (선택)">
                </div>
                <?php } ?>
            </div>
            <?php } ?>

            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <!-- 4. 수신 설정 (광고/마케팅) -->
            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <?php if ($config['cf_use_promotion'] == 1) { ?>
            <div class="m-card m-form-section">
                <h2 class="m-section-title">수신 설정 <span class="m-form-hint" style="font-weight: 400;">(선택 항목)</span></h2>

                <div class="m-form-row">
                    <label class="m-check m-check-block">
                        <input type="checkbox" name="mb_marketing_agree" value="1" id="reg_mb_marketing_agree" <?php echo $member['mb_marketing_agree'] ? 'checked' : '' ?> class="marketing-sync">
                        <span>마케팅 목적 개인정보 수집·이용에 동의합니다</span>
                    </label>
                    <button type="button" class="m-btn m-btn-ghost js-open-consent" style="font-size: 12px; padding: 4px 8px; margin-top: 4px;" data-title="마케팅 목적의 개인정보 수집 및 이용" data-template="#tpl_marketing" data-check="#reg_mb_marketing_agree" aria-controls="consentDialog">자세히보기</button>
                    <input type="hidden" name="mb_marketing_agree_default" value="<?php echo $member['mb_marketing_agree'] ?>">
                    <?php if ($member['mb_marketing_agree'] == 1 && $member['mb_marketing_date'] != "0000-00-00 00:00:00") { ?>
                    <span class="m-form-hint">동의일자: <?php echo $member['mb_marketing_date'] ?></span>
                    <?php } ?>
                    <template id="tpl_marketing">
                        * 목적: 서비스 마케팅 및 프로모션<br>
                        * 항목: 이름, 이메일<?php echo ($config['cf_use_hp'] || ($config['cf_cert_use'] && ($config['cf_cert_hp'] || $config['cf_cert_simple']))) ? ', 휴대폰 번호' : '' ?><br>
                        * 보유기간: 회원 탈퇴 시까지<br>
                        동의를 거부하셔도 서비스 기본 이용은 가능하나, 맞춤형 혜택 제공은 제한될 수 있습니다.
                    </template>
                </div>

                <div class="m-form-row">
                    <label class="m-check m-check-block">
                        <input type="checkbox" name="mb_promotion_agree" value="1" id="reg_mb_promotion_agree" class="marketing-sync parent-promo">
                        <span>광고성 정보 수신에 동의합니다 (이메일 / SMS · 카카오톡)</span>
                    </label>
                    <button type="button" class="m-btn m-btn-ghost js-open-consent" style="font-size: 12px; padding: 4px 8px; margin-top: 4px;" data-title="광고성 정보 수신 동의" data-template="#tpl_promotion" data-check="#reg_mb_promotion_agree" data-check-group=".child-promo" aria-controls="consentDialog">자세히보기</button>

                    <div style="margin-top: 10px; padding-left: 12px; display: flex; flex-direction: column; gap: 8px;">
                        <label class="m-check m-check-block">
                            <input type="checkbox" name="mb_mailling" value="1" id="reg_mb_mailling" <?php echo $member['mb_mailling'] ? 'checked' : '' ?> class="child-promo">
                            <span>광고성 이메일 수신 동의 <?php if ($w == 'u' && $member['mb_mailling'] == 1 && $member['mb_mailling_date'] != "0000-00-00 00:00:00") echo '<span class="m-form-hint" style="margin-left:4px;">(동의일자: '.$member['mb_mailling_date'].')</span>' ?></span>
                        </label>
                        <input type="hidden" name="mb_mailling_default" value="<?php echo $member['mb_mailling'] ?>">

                        <?php if ($config['cf_use_hp'] || $config['cf_req_hp']) { ?>
                        <label class="m-check m-check-block">
                            <input type="checkbox" name="mb_sms" value="1" id="reg_mb_sms" <?php echo $member['mb_sms'] ? 'checked' : '' ?> class="child-promo">
                            <span>광고성 SMS / 카카오톡 수신 동의 <?php if ($w == 'u' && $member['mb_sms'] == 1 && $member['mb_sms_date'] != "0000-00-00 00:00:00") echo '<span class="m-form-hint" style="margin-left:4px;">(동의일자: '.$member['mb_sms_date'].')</span>' ?></span>
                        </label>
                        <input type="hidden" name="mb_sms_default" value="<?php echo $member['mb_sms'] ?>">
                        <?php } ?>
                    </div>

                    <template id="tpl_promotion">
                        수집·이용에 동의한 개인정보를 이용하여 이메일/SMS/카카오톡 등으로 오전 8시~오후 9시에 광고성 정보를 전송할 수 있습니다.<br>
                        동의는 언제든지 마이페이지에서 철회할 수 있습니다.
                    </template>
                </div>

                <?php
                $usedCompanies = [];
                if (!empty($config['cf_sms_use'])) {
                    $companies = ['icode' => '아이코드'];
                    if (isset($companies[$config['cf_sms_use']])) $usedCompanies[] = $companies[$config['cf_sms_use']];
                }
                if (!empty($usedCompanies)) { ?>
                <div class="m-form-row">
                    <label class="m-check m-check-block">
                        <input type="checkbox" name="mb_thirdparty_agree" value="1" id="reg_mb_thirdparty_agree" <?php echo $member['mb_thirdparty_agree'] ? 'checked' : '' ?> class="marketing-sync">
                        <span>개인정보 제3자 제공에 동의합니다</span>
                    </label>
                    <button type="button" class="m-btn m-btn-ghost js-open-consent" style="font-size: 12px; padding: 4px 8px; margin-top: 4px;" data-title="개인정보 제3자 제공 동의" data-template="#tpl_thirdparty" data-check="#reg_mb_thirdparty_agree" aria-controls="consentDialog">자세히보기</button>
                    <input type="hidden" name="mb_thirdparty_agree_default" value="<?php echo $member['mb_thirdparty_agree'] ?>">
                    <?php if ($member['mb_thirdparty_agree'] == 1 && $member['mb_thirdparty_date'] != "0000-00-00 00:00:00") { ?>
                    <span class="m-form-hint">동의일자: <?php echo $member['mb_thirdparty_date'] ?></span>
                    <?php } ?>
                    <template id="tpl_thirdparty">
                        * 목적: 상품/서비스, 사은/판촉행사, 이벤트 등의 마케팅 안내(카카오톡 등)<br>
                        * 항목: 이름, 휴대폰 번호<br>
                        * 제공받는 자: <?php echo implode(', ', $usedCompanies) ?><br>
                        * 보유기간: 제공 목적 서비스 기간 또는 동의 철회 시까지
                    </template>
                </div>
                <?php } ?>
            </div>
            <?php } ?>

            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <!-- 5. 자동등록방지 -->
            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <div class="m-card m-form-section">
                <h2 class="m-section-title">자동등록방지</h2>
                <div class="m-form-row m-captcha-wrap">
                    <?php echo captcha_html(); ?>
                </div>
            </div>

            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <!-- 액션 버튼 -->
            <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
            <div style="display: flex; gap: 8px; margin: 24px 0;">
                <a href="<?php echo G5_URL ?>" class="m-btn m-btn-secondary" style="flex: 1;">취소</a>
                <button type="submit" id="btn_submit" class="m-btn m-btn-primary" style="flex: 2;" accesskey="s">
                    <?php echo $w == '' ? '회원가입' : '정보 수정' ?>
                </button>
            </div>
        </form>

    </div>
    </main>
    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>

<style>
/* 회원가입 폼 전용 컴포넌트 */
.m-form-section { margin-bottom: 16px; padding: 24px 28px; }
.m-form-section:last-of-type { margin-bottom: 0; }

.m-section-title {
    font-size: 15px; font-weight: 600;
    color: var(--m-text); margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--m-border);
}

.m-form-row { margin-bottom: 14px; }
.m-form-row:last-child { margin-bottom: 0; }

.m-form-grid-2 {
    display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
}
@media (max-width: 480px) {
    .m-form-grid-2 { grid-template-columns: 1fr; }
}

.m-form-msg {
    display: block; font-size: 12px;
    color: var(--m-text-muted); margin-top: 4px; min-height: 14px;
}
.m-form-hint {
    display: inline-block; font-size: 12px;
    color: var(--m-text-faint); font-weight: 400;
    margin-left: 4px;
}
.m-form-success {
    margin-top: 8px; padding: 8px 12px;
    background: rgba(16,185,129,0.08); color: #047857;
    border: 1px solid rgba(16,185,129,0.25);
    border-radius: var(--m-radius-sm);
    font-size: 13px;
}

.m-input-with-action {
    display: flex; gap: 8px;
}
.m-input-with-action .m-input { flex: 1; }

.m-textarea {
    min-height: 88px; padding: 10px 12px; resize: vertical;
    font-family: inherit; line-height: 1.5;
}

.m-file {
    padding: 8px 12px; font-size: 13px;
}
.m-file::file-selector-button {
    background: var(--m-surface-2); color: var(--m-text-soft);
    border: 1px solid var(--m-border); border-radius: var(--m-radius-sm);
    padding: 4px 12px; margin-right: 10px; cursor: pointer;
    font-family: inherit; font-size: 12px;
}

.m-check-block:has(input:checked) {
    background: var(--m-primary-soft);
    border-color: var(--m-primary);
}

/* 캡차 영역 — 공통 _head.inc.php 정의 사용 */
</style>

<?php include_once(__DIR__ . '/consent_modal.inc.php'); ?>

<script>
// gnuboard 표준 인증 핸들러 (jquery.register_form.js 와 certify.js 가 의존)
$(function() {
    $("#reg_zip_find").css("display", "inline-block");
    var pageTypeParam = "pageType=register";

    <?php if ($config['cf_cert_use'] && $config['cf_cert_simple']) { ?>
    var url = "<?php echo G5_INICERT_URL; ?>/ini_request.php";
    $(".win_sa_cert").click(function() {
        if (!cert_confirm()) return false;
        var type = $(this).data("type");
        var params = "?directAgency=" + type + "&" + pageTypeParam;
        call_sa(url + params);
    });
    <?php } ?>
    <?php if ($config['cf_cert_use'] && $config['cf_cert_ipin']) { ?>
    $("#win_ipin_cert").click(function() {
        if (!cert_confirm()) return false;
        var url = "<?php echo G5_OKNAME_URL; ?>/ipin1.php?" + pageTypeParam;
        certify_win_open('kcb-ipin', url);
    });
    <?php } ?>
    <?php if ($config['cf_cert_use'] && $config['cf_cert_hp']) { ?>
    $("#win_hp_cert").click(function() {
        if (!cert_confirm()) return false;
        <?php
        switch ($config['cf_cert_hp']) {
            case 'kcb': $cert_url = G5_OKNAME_URL.'/hpcert1.php';      $cert_type = 'kcb-hp'; break;
            case 'kcp': $cert_url = G5_KCPCERT_URL.'/kcpcert_form.php'; $cert_type = 'kcp-hp'; break;
            case 'lg':  $cert_url = G5_LGXPAY_URL.'/AuthOnlyReq.php';   $cert_type = 'lg-hp';  break;
            default:    echo 'alert("기본환경설정에서 휴대폰 본인확인 설정을 해주십시오"); return false;'; break;
        }
        ?>
        certify_win_open("<?php echo $cert_type; ?>", "<?php echo $cert_url; ?>?" + pageTypeParam);
    });
    <?php } ?>
});

// submit 검증 — gnuboard 표준 동작 그대로 (jquery.register_form.js 의 reg_mb_*_check 함수 사용)
function fregisterform_submit(f) {
    if (f.w.value == "") {
        var msg = reg_mb_id_check();
        if (msg) { alert(msg); f.mb_id.select(); return false; }
    }
    if (f.w.value == "" && f.mb_password.value.length < 3) {
        alert("비밀번호를 3글자 이상 입력하십시오."); f.mb_password.focus(); return false;
    }
    if (f.mb_password.value != f.mb_password_re.value) {
        alert("비밀번호가 같지 않습니다."); f.mb_password_re.focus(); return false;
    }
    if (f.mb_password.value.length > 0 && f.mb_password_re.value.length < 3) {
        alert("비밀번호를 3글자 이상 입력하십시오."); f.mb_password_re.focus(); return false;
    }
    if (f.w.value == "" && f.mb_name.value.length < 1) {
        alert("이름을 입력하십시오."); f.mb_name.focus(); return false;
    }
    <?php if ($w == '' && $config['cf_cert_use'] && $config['cf_cert_req']) { ?>
    if (f.cert_no.value == "") {
        alert("회원가입을 위해서는 본인확인을 해주셔야 합니다."); return false;
    }
    <?php } ?>
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick && f.mb_nick.defaultValue != f.mb_nick.value)) {
        var msg = reg_mb_nick_check();
        if (msg) { alert(msg); f.reg_mb_nick && f.reg_mb_nick.select(); return false; }
    }
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
        var msg = reg_mb_email_check();
        if (msg) { alert(msg); f.reg_mb_email.select(); return false; }
    }
    <?php if (($config['cf_use_hp'] || $config['cf_cert_hp']) && $config['cf_req_hp']) { ?>
    var msg = reg_mb_hp_check();
    if (msg) { alert(msg); f.reg_mb_hp.select(); return false; }
    <?php } ?>

    if (typeof f.mb_icon != "undefined" && f.mb_icon.value && !f.mb_icon.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
        alert("회원아이콘이 이미지 파일이 아닙니다."); f.mb_icon.focus(); return false;
    }
    if (typeof f.mb_img != "undefined" && f.mb_img.value && !f.mb_img.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
        alert("회원이미지가 이미지 파일이 아닙니다."); f.mb_img.focus(); return false;
    }
    if (typeof f.mb_recommend != "undefined" && f.mb_recommend.value) {
        if (f.mb_id.value == f.mb_recommend.value) {
            alert("본인을 추천할 수 없습니다."); f.mb_recommend.focus(); return false;
        }
        var msg = reg_mb_recommend_check();
        if (msg) { alert(msg); f.mb_recommend.select(); return false; }
    }

    <?php echo chk_captcha_js(); ?>

    document.getElementById("btn_submit").disabled = "disabled";
    return true;
}

// 광고성 정보 수신 동의 — 부모/자식 체크박스 동기화
document.addEventListener('DOMContentLoaded', function () {
    var parentPromo = document.getElementById('reg_mb_promotion_agree');
    var childPromo  = Array.from(document.querySelectorAll('.child-promo'));
    if (!parentPromo || childPromo.length === 0) return;

    var syncParentFromChildren = function() {
        parentPromo.checked = childPromo.some(function(cb){ return cb.checked; });
    };
    var syncChildrenFromParent = function() {
        var c = parentPromo.checked;
        childPromo.forEach(function(cb){
            cb.checked = c;
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        });
    };
    syncParentFromChildren();
    parentPromo.addEventListener('change', syncChildrenFromParent);
    childPromo.forEach(function(cb){ cb.addEventListener('change', syncParentFromChildren); });
});
</script>
<!-- } 회원정보 입력/수정 끝 -->
