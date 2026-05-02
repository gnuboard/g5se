<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');

if ($config['cf_cert_use'] && ($config['cf_cert_simple'] || $config['cf_cert_ipin'] || $config['cf_cert_hp'])) {
    add_javascript('<script src="'.G5_JS_URL.'/certify.js?v='.G5_JS_VER.'"></script>', 0);
}

$has_cert = $config['cf_cert_use'] != 0 && $config['cf_cert_find'] != 0
    && ($config['cf_cert_simple'] || $config['cf_cert_hp'] || $config['cf_cert_ipin']);
?>

<!-- 회원정보 찾기 시작 { -->
<div class="m-shell">

    <header class="m-nav">
        <div class="m-nav-inner">
            <a href="<?php echo G5_URL ?>" class="m-brand">gnu5se</a>
            <nav class="m-nav-actions">
                <a href="<?php echo G5_BBS_URL ?>/login.php" class="m-btn m-btn-ghost">로그인</a>
            </nav>
        </div>
    </header>

    <main class="m-center">
        <div style="width: 100%; max-width: <?php echo $has_cert ? '720px' : '440px' ?>;">

            <div style="text-align: center; margin-bottom: 24px;">
                <h1 style="font-size: 22px; margin-bottom: 8px;">아이디 / 비밀번호 찾기</h1>
                <p style="font-size: 13px; color: var(--m-text-muted);">
                    아래 방법으로 회원정보를 찾을 수 있습니다.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: <?php echo $has_cert ? 'repeat(auto-fit, minmax(320px, 1fr))' : '1fr' ?>; gap: 16px;">

                <!-- 이메일로 찾기 -->
                <div class="m-card">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--m-primary-soft); display: grid; place-items: center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--m-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <h2 style="font-size: 15px;">이메일로 찾기</h2>
                    </div>
                    <p style="font-size: 13px; color: var(--m-text-muted); margin-bottom: 16px; line-height: 1.6;">
                        가입 시 등록하신 이메일로 아이디·비밀번호 재설정 안내를 보내드립니다.
                    </p>

                    <form name="fpasswordlost" action="<?php echo $action_url ?>" onsubmit="return fpasswordlost_submit(this);" method="post" autocomplete="off">
                        <input type="hidden" name="cert_no" value="">

                        <div style="margin-bottom: 12px;">
                            <label for="mb_email" class="m-label">E-mail</label>
                            <input type="email" name="mb_email" id="mb_email" required maxlength="100" class="m-input" placeholder="가입 시 사용한 이메일">
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label class="m-label">자동등록방지</label>
                            <div class="m-captcha-wrap"><?php echo captcha_html(); ?></div>
                        </div>

                        <button type="submit" class="m-btn m-btn-primary">인증메일 보내기</button>
                    </form>
                </div>

                <?php if ($has_cert) { ?>
                <!-- 본인인증으로 찾기 -->
                <div class="m-card">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16,185,129,0.12); display: grid; place-items: center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12l2 2 4-4"/>
                                <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c2.97 0 5.6 1.44 7.24 3.66"/>
                            </svg>
                        </div>
                        <h2 style="font-size: 15px;">본인인증으로 찾기</h2>
                    </div>
                    <p style="font-size: 13px; color: var(--m-text-muted); margin-bottom: 16px; line-height: 1.6;">
                        본인인증 후 즉시 가입정보 확인이 가능합니다.
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <?php if (!empty($config['cf_cert_simple'])) { ?>
                        <button type="button" id="win_sa_kakao_cert" class="m-btn m-btn-secondary win_sa_cert" data-type="">간편인증</button>
                        <?php } ?>
                        <?php if (!empty($config['cf_cert_hp'])) { ?>
                        <button type="button" id="win_hp_cert" class="m-btn m-btn-secondary">휴대폰 본인확인</button>
                        <?php } ?>
                        <?php if (!empty($config['cf_cert_ipin'])) { ?>
                        <button type="button" id="win_ipin_cert" class="m-btn m-btn-secondary">아이핀 본인확인</button>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

            </div>

            <p style="text-align: center; font-size: 12px; color: var(--m-text-faint); margin-top: 18px;">
                계정이 없으신가요?
                <a href="<?php echo G5_BBS_URL ?>/register.php" class="m-link">회원가입</a>
            </p>

        </div>
    </main>
</div>

<style>
/* 캡차 영역 — register_form 의 m-captcha-wrap 과 동일 (다크모드 대응) */
.m-captcha-wrap #captcha {
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
    border: 0; padding: 0; margin: 0;
}
.m-captcha-wrap #captcha legend { position: absolute; left: -9999px; }
.m-captcha-wrap #captcha_img {
    display: block; height: 44px; width: auto;
    border: 1px solid var(--m-border-hover);
    border-radius: var(--m-radius); background: white;
}
.m-captcha-wrap #captcha_key {
    flex: 1; min-width: 120px;
    padding: 10px 12px; box-sizing: border-box;
    background: var(--m-surface) !important; color: var(--m-text) !important;
    border: 1px solid var(--m-border-hover);
    border-radius: var(--m-radius);
    font-size: 14px; font-family: inherit; outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.m-captcha-wrap #captcha_key:focus {
    border-color: var(--m-primary);
    box-shadow: 0 0 0 3px var(--m-primary-soft);
}
.m-captcha-wrap #captcha_reload, .m-captcha-wrap #captcha_mp3 {
    width: 38px; height: 38px; padding: 0;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    cursor: pointer; color: var(--m-text-soft);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0;
}
.m-captcha-wrap #captcha_reload::before, .m-captcha-wrap #captcha_mp3::before {
    content: ''; display: block; width: 16px; height: 16px;
    background: currentColor;
}
.m-captcha-wrap #captcha_reload::before {
    -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'><path d='M3 12a9 9 0 0 1 15-6.7L21 8'/><path d='M21 3v5h-5'/><path d='M21 12a9 9 0 0 1-15 6.7L3 16'/><path d='M3 21v-5h5'/></svg>") no-repeat center / contain;
            mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'><path d='M3 12a9 9 0 0 1 15-6.7L21 8'/><path d='M21 3v5h-5'/><path d='M21 12a9 9 0 0 1-15 6.7L3 16'/><path d='M3 21v-5h5'/></svg>") no-repeat center / contain;
}
.m-captcha-wrap #captcha_mp3::before {
    -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'><polygon points='11 5 6 9 2 9 2 15 6 15 11 19 11 5'/><path d='M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07'/></svg>") no-repeat center / contain;
            mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'><polygon points='11 5 6 9 2 9 2 15 6 15 11 19 11 5'/><path d='M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07'/></svg>") no-repeat center / contain;
}
.m-captcha-wrap #captcha_reload:hover, .m-captcha-wrap #captcha_mp3:hover {
    background: var(--m-border); color: var(--m-text);
}
.m-captcha-wrap #captcha_info {
    display: block; flex-basis: 100%;
    font-size: 12px; color: var(--m-text-faint); margin-top: 4px;
}
</style>

<script>
$(function() {
    var pageTypeParam = "pageType=find";

    <?php if ($config['cf_cert_use'] && $config['cf_cert_simple']) { ?>
    var url = "<?php echo G5_INICERT_URL; ?>/ini_request.php";
    $(".win_sa_cert").click(function() {
        var type = $(this).data("type");
        var params = "?directAgency=" + type + "&" + pageTypeParam;
        call_sa(url + params);
    });
    <?php } ?>
    <?php if ($config['cf_cert_use'] && $config['cf_cert_ipin']) { ?>
    $("#win_ipin_cert").click(function() {
        var url = "<?php echo G5_OKNAME_URL; ?>/ipin1.php?" + pageTypeParam;
        certify_win_open('kcb-ipin', url);
    });
    <?php } ?>
    <?php if ($config['cf_cert_use'] && $config['cf_cert_hp']) { ?>
    $("#win_hp_cert").click(function() {
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

function fpasswordlost_submit(f) {
    <?php echo chk_captcha_js(); ?>
    return true;
}
</script>
<!-- } 회원정보 찾기 끝 -->
