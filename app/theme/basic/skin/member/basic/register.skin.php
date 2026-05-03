<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 회원가입 약관 동의 시작 { -->
<div class="m-shell">

    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-center" style="align-items: start;">
        <div class="m-card" style="width: 100%; max-width: 640px;">

            <div style="margin-bottom: 24px;">
                <h1 style="font-size: 22px; margin-bottom: 6px;">회원가입</h1>
                <p style="font-size: 13px; color: var(--m-text-muted);">
                    가입을 위해 아래 약관에 동의해 주세요.
                </p>
            </div>

            <?php @include_once(get_social_skin_path().'/social_register.skin.php'); ?>

            <form name="fregister" id="fregister" action="<?php echo $register_action_url ?>" onsubmit="return fregister_submit(this);" method="POST" autocomplete="off">

                <!-- 약관 1: 회원가입약관 -->
                <section class="m-terms-section">
                    <h2 class="m-terms-title">(필수) 회원가입약관</h2>
                    <div class="m-terms-box"><?php echo nl2br(get_text($config['cf_stipulation'])) ?></div>
                    <label class="m-check m-check-block">
                        <input type="checkbox" name="agree" value="1" id="agree11">
                        <span>회원가입약관의 내용에 동의합니다.</span>
                    </label>
                </section>

                <!-- 약관 2: 개인정보 -->
                <section class="m-terms-section">
                    <h2 class="m-terms-title">(필수) 개인정보 수집 및 이용</h2>
                    <div class="m-terms-box">
                        <table class="m-terms-table">
                            <thead>
                                <tr>
                                    <th>목적</th>
                                    <th>항목</th>
                                    <th>보유기간</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>이용자 식별 및 본인여부 확인</td>
                                    <td>아이디, 이름, 비밀번호<?php echo ($config['cf_cert_use'])? ", 생년월일, 휴대폰 번호, 암호화된 개인식별부호(CI)" : ""; ?></td>
                                    <td>회원 탈퇴 시까지</td>
                                </tr>
                                <tr>
                                    <td>고객서비스 이용에 관한 통지, CS 대응을 위한 이용자 식별</td>
                                    <td>연락처 (이메일, 휴대전화번호)</td>
                                    <td>회원 탈퇴 시까지</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <label class="m-check m-check-block">
                        <input type="checkbox" name="agree2" value="1" id="agree21">
                        <span>개인정보 수집 및 이용의 내용에 동의합니다.</span>
                    </label>
                </section>

                <!-- 전체 동의 -->
                <div class="m-terms-allagree">
                    <label class="m-check m-check-block">
                        <input type="checkbox" name="chk_all" id="chk_all">
                        <span style="font-weight: 600; color: var(--m-text);">위 약관에 모두 동의합니다.</span>
                    </label>
                </div>

                <div style="display: flex; gap: 8px; margin-top: 24px;">
                    <a href="<?php echo G5_URL ?>" class="m-btn m-btn-secondary" style="flex: 1;">취소</a>
                    <button type="submit" class="m-btn m-btn-primary" style="flex: 2;">다음 단계</button>
                </div>
            </form>

            <p style="font-size: 12px; color: var(--m-text-faint); text-align: center; margin-top: 18px;">
                이미 계정이 있으신가요?
                <a href="<?php echo G5_BBS_URL ?>/login.php" class="m-link">로그인</a>
            </p>

        </div>
    </main>
    <?php require G5_THEME_PATH.'/modern/_footer.inc.php'; ?>
</div>

<style>
.m-terms-section { margin-bottom: 20px; }
.m-terms-title {
    font-size: 14px; font-weight: 600;
    color: var(--m-text); margin-bottom: 8px;
}
.m-terms-box {
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    padding: 14px 16px;
    height: 140px; overflow-y: auto;
    font-size: 12px; line-height: 1.7;
    color: var(--m-text-soft);
    margin-bottom: 10px;
}
.m-terms-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.m-terms-table th {
    text-align: left; padding: 8px 10px;
    border-bottom: 1px solid var(--m-border);
    font-weight: 600; color: var(--m-text);
    background: var(--m-surface);
}
.m-terms-table td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--m-border);
    vertical-align: top;
    color: var(--m-text-soft);
}
.m-terms-table tr:last-child td { border-bottom: 0; }

.m-check-block {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 12px;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    cursor: pointer; transition: border-color 0.15s, background 0.15s;
}
.m-check-block:hover { border-color: var(--m-border-hover); }
.m-check-block:has(input:checked) {
    background: var(--m-primary-soft);
    border-color: var(--m-primary);
}

.m-terms-allagree {
    margin-top: 8px; padding-top: 16px;
    border-top: 1px dashed var(--m-border);
}
</style>

<script>
function fregister_submit(f) {
    if (!f.agree.checked) {
        alert("회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
        f.agree.focus();
        return false;
    }
    if (!f.agree2.checked) {
        alert("개인정보 수집 및 이용의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
        f.agree2.focus();
        return false;
    }
    return true;
}

// 전체 동의 체크박스 (jQuery 의존 제거 — vanilla 로 작성)
document.getElementById('chk_all').addEventListener('change', function() {
    var checked = this.checked;
    document.querySelectorAll('input[name^="agree"]').forEach(function(cb) {
        cb.checked = checked;
    });
});

// 개별 체크박스 변경 시 chk_all 동기화
document.querySelectorAll('input[name="agree"], input[name="agree2"]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var allChecked = document.getElementById('agree11').checked && document.getElementById('agree21').checked;
        document.getElementById('chk_all').checked = allChecked;
    });
});
</script>
<!-- } 회원가입 약관 동의 끝 -->
