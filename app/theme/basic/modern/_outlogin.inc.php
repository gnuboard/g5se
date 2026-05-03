<?php
/*
 * 모던 outlogin 위젯 — 메인/게시판/마이페이지 등 사이드바 어디서나 include 가능.
 * 사용: require G5_THEME_PATH.'/modern/_outlogin.inc.php';
 *
 * $is_member 에 따라 자동 분기:
 *   - 비로그인: 로그인 폼 + 가입/비번찾기 링크
 *   - 로그인 후: 프로필 + 포인트/쪽지/스크랩 카운트 + 빠른 액션
 *
 * 기존 gnuboard outlogin 위젯의 폼 필드명/액션을 그대로 보존 (login_check 호환).
 * CSS 스타일은 한 번만 출력 (가드 상수로 중복 방지).
 */

if (!defined('_GNUBOARD_')) return;

global $is_member, $is_admin, $member, $config, $urlencode;

if ($is_member) {
    $_ol_nick   = get_text(cut_str($member['mb_nick'], $config['cf_cut_name']));
    $_ol_point  = number_format((int)$member['mb_point']);
    $_ol_memo   = function_exists('get_memo_not_read') ? (int)get_memo_not_read($member['mb_id']) : 0;
    $_ol_scrap  = isset($member['mb_scrap_cnt']) ? (int)$member['mb_scrap_cnt'] : 0;
    $_ol_url    = G5_BBS_URL;
    $_ol_member_confirm_url = $_ol_url.'/member_confirm.php?url='.urlencode($_ol_url.'/register_form.php?w=u');
}
$_ol_login_action_url = G5_BBS_URL.'/login_check.php';
$_ol_login_back_url   = isset($urlencode) ? $urlencode : urlencode($_SERVER['REQUEST_URI'] ?? '/');
?>
<?php if (!defined('_MODERN_OUTLOGIN_CSS_LOADED_')) { define('_MODERN_OUTLOGIN_CSS_LOADED_', true); ?>
<style>
.m-outlogin { width: 100%; }
.m-ol-card {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-lg);
    padding: 18px 18px 16px;
    box-shadow: var(--m-shadow);
}
.m-ol-title {
    font-size: var(--m-text-base); font-weight: 600;
    color: var(--m-text-soft);
    margin-bottom: 12px;
    display: flex; align-items: center; justify-content: space-between;
}
.m-ol-title .m-link { font-size: var(--m-text-sm); }

.m-ol-form { display: flex; flex-direction: column; gap: 8px; }
.m-ol-form .m-input { padding: 9px 11px; font-size: var(--m-text-md); }
.m-ol-form .m-check { font-size: var(--m-text-sm); }
.m-ol-form .m-btn { padding: 9px 16px; }

/* 로그인 버튼 밑 한 줄 — 자동로그인 체크박스(좌) + ID/PW 찾기(우) */
.m-ol-row {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 4px;
    font-size: var(--m-text-sm);
}
.m-ol-row .m-check span { font-size: var(--m-text-sm); }
.m-ol-row-link {
    color: var(--m-text-muted); text-decoration: none;
    font-size: var(--m-text-sm);
}
.m-ol-row-link:hover { color: var(--m-primary); }

/* 로그인 후 */
.m-ol-profile {
    display: flex; align-items: center; gap: 10px;
    padding-bottom: 14px; border-bottom: 1px solid var(--m-border);
    margin-bottom: 12px;
}
.m-ol-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg, var(--m-primary) 0%, #8b5cf6 100%);
    display: grid; place-items: center;
    color: white; font-weight: 700; font-size: var(--m-text-md);
    flex-shrink: 0;
}
.m-ol-profile-meta { flex: 1; min-width: 0; }
.m-ol-profile-name {
    font-size: var(--m-text-md); font-weight: 600; color: var(--m-text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.m-ol-profile-suffix { font-weight: 400; color: var(--m-text-muted); }
.m-ol-profile-edit {
    font-size: var(--m-text-xs); color: var(--m-text-muted);
    text-decoration: none; margin-top: 2px; display: inline-block;
}
.m-ol-profile-edit:hover { color: var(--m-primary); }

.m-ol-stats {
    list-style: none; padding: 0; margin: 0 0 12px 0;
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;
}
.m-ol-stat {
    display: flex; flex-direction: column; align-items: center;
    padding: 10px 6px;
    background: var(--m-surface-2); border-radius: var(--m-radius);
    text-decoration: none; color: var(--m-text-soft);
    transition: background 0.15s, color 0.15s;
}
.m-ol-stat:hover { background: var(--m-primary-soft); color: var(--m-primary); }
.m-ol-stat-label { font-size: var(--m-text-xs); color: var(--m-text-faint); }
.m-ol-stat-value {
    font-size: var(--m-text-md); font-weight: 700; color: var(--m-text);
    margin-top: 2px;
}
.m-ol-stat:hover .m-ol-stat-value,
.m-ol-stat:hover .m-ol-stat-label { color: inherit; }

.m-ol-actions { display: flex; gap: 6px; }
.m-ol-actions .m-btn { padding: 8px 12px; font-size: var(--m-text-sm); }

/* 관리자 뱃지 */
.m-ol-admin-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 7px; border-radius: 999px;
    font-size: var(--m-text-xs); font-weight: 600;
    background: rgba(245,158,11,0.15); color: #d97706;
    margin-top: 2px;
}
</style>
<?php } ?>

<aside class="m-outlogin">
<?php if ($is_member) { ?>
    <div class="m-ol-card">
        <!-- 프로필 헤더 -->
        <div class="m-ol-profile">
            <div class="m-ol-avatar"><?php echo mb_strtoupper(mb_substr($_ol_nick, 0, 1, 'UTF-8'), 'UTF-8') ?></div>
            <div class="m-ol-profile-meta">
                <div class="m-ol-profile-name"><?php echo $_ol_nick ?><span class="m-ol-profile-suffix"> 님</span></div>
                <a href="<?php echo $_ol_member_confirm_url ?>" class="m-ol-profile-edit">정보 수정 →</a>
                <?php if ($is_admin == 'super') { ?>
                <span class="m-ol-admin-badge">최고관리자</span>
                <?php } else if ($is_admin) { ?>
                <span class="m-ol-admin-badge"><?php echo $is_admin ?> 관리자</span>
                <?php } ?>
            </div>
        </div>

        <!-- 카운트 -->
        <ul class="m-ol-stats">
            <li><a href="/point" onclick="win_point(this.href); return false;" class="m-ol-stat" title="포인트">
                <span class="m-ol-stat-label">포인트</span>
                <span class="m-ol-stat-value"><?php echo $_ol_point ?></span>
            </a></li>
            <li><a href="/memo" onclick="win_memo(this.href); return false;" class="m-ol-stat" title="안읽은 쪽지">
                <span class="m-ol-stat-label">쪽지</span>
                <span class="m-ol-stat-value"><?php echo $_ol_memo ?></span>
            </a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/scrap.php" class="m-ol-stat" title="스크랩">
                <span class="m-ol-stat-label">스크랩</span>
                <span class="m-ol-stat-value"><?php echo $_ol_scrap ?></span>
            </a></li>
        </ul>

        <!-- 액션 -->
        <div class="m-ol-actions">
            <?php if ($is_admin == 'super' || $is_admin) { ?>
            <a href="<?php echo G5_ADMIN_URL ?>" class="m-btn m-btn-secondary" style="flex: 1;">관리자</a>
            <?php } ?>
            <a href="<?php echo G5_BBS_URL ?>/logout.php" class="m-btn m-btn-secondary" style="flex: 1;">로그아웃</a>
        </div>
    </div>
<?php } else { ?>
    <div class="m-ol-card">
        <div class="m-ol-title">
            <span>로그인</span>
            <a href="<?php echo G5_BBS_URL ?>/register.php" class="m-link">회원가입</a>
        </div>

        <form name="foutlogin" action="<?php echo $_ol_login_action_url ?>" onsubmit="return foutlogin_submit(this);" method="post" autocomplete="off" class="m-ol-form">
            <input type="hidden" name="url" value="<?php echo $_ol_login_back_url ?>">
            <input type="text" name="mb_id" required maxlength="20" class="m-input" placeholder="아이디" autocomplete="username">
            <input type="password" name="mb_password" required maxlength="20" class="m-input" placeholder="비밀번호" autocomplete="current-password">
            <button type="submit" class="m-btn m-btn-primary">로그인</button>
            <div class="m-ol-row">
                <label class="m-check">
                    <input type="checkbox" name="auto_login" value="1" id="ol_auto_login">
                    <span>자동로그인</span>
                </label>
                <a href="<?php echo G5_BBS_URL ?>/password_lost.php" class="m-ol-row-link">ID/PW 찾기</a>
            </div>
        </form>
    </div>
<?php } ?>
</aside>

<?php if (!$is_member && !defined('_MODERN_OUTLOGIN_JS_LOADED_')) { define('_MODERN_OUTLOGIN_JS_LOADED_', true); ?>
<script>
(function() {
    var auto = document.getElementById('ol_auto_login');
    if (auto) auto.addEventListener('click', function() {
        if (this.checked && !confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?")) {
            this.checked = false;
        }
    });
})();
function foutlogin_submit(f) {
    if (typeof jQuery !== 'undefined' && jQuery(document.body).triggerHandler('outlogin1', [f, 'foutlogin']) !== false) return true;
    return true;
}
</script>
<?php } ?>
