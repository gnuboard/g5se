<?php
if (!defined('_GNUBOARD_')) exit;

$delete_str = "";
if ($w == 'x') $delete_str = "댓";
if ($w == 'u') $g5['title'] = $delete_str."글 수정";
else if ($w == 'd' || $w == 'x') $g5['title'] = $delete_str."글 삭제";
else $g5['title'] = $g5['title'];

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 비밀번호 확인 시작 { -->
<div class="m-shell">

    <header class="m-nav">
        <div class="m-nav-inner">
            <a href="<?php echo G5_URL ?>" class="m-brand">gnu5se</a>
            <nav class="m-nav-actions">
                <a href="<?php echo isset($return_url) ? $return_url : G5_URL ?>" class="m-btn m-btn-ghost">취소</a>
            </nav>
        </div>
    </header>

    <main class="m-center">
        <div class="m-card m-card-narrow" style="max-width: 420px;">

            <!-- 잠금 아이콘 -->
            <div style="display: flex; justify-content: center; margin-bottom: 18px;">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: var(--m-primary-soft); display: grid; place-items: center;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--m-primary)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 24px;">
                <h1 style="font-size: 20px; margin-bottom: 8px;"><?php echo $g5['title'] ?></h1>
                <p style="font-size: 13px; color: var(--m-text-muted); line-height: 1.6;">
                    <?php if ($w == 'u') { ?>
                        작성자만 글을 수정할 수 있습니다.<br>
                        글 작성 시 입력한 비밀번호를 입력해 주세요.
                    <?php } else if ($w == 'd' || $w == 'x') { ?>
                        작성자만 <?php echo $delete_str ?>글을 삭제할 수 있습니다.<br>
                        글 작성 시 입력한 비밀번호를 입력해 주세요.
                    <?php } else { ?>
                        비밀글 기능으로 보호된 글입니다.<br>
                        작성자와 관리자만 열람하실 수 있습니다.
                    <?php } ?>
                </p>
            </div>

            <form name="fboardpassword" action="<?php echo $action ?>" method="post">
                <input type="hidden" name="w"          value="<?php echo $w ?>">
                <input type="hidden" name="bo_table"   value="<?php echo $bo_table ?>">
                <input type="hidden" name="wr_id"      value="<?php echo $wr_id ?>">
                <input type="hidden" name="comment_id" value="<?php echo $comment_id ?>">
                <input type="hidden" name="sfl"        value="<?php echo $sfl ?>">
                <input type="hidden" name="stx"        value="<?php echo $stx ?>">
                <input type="hidden" name="page"       value="<?php echo $page ?>">

                <div style="margin-bottom: 18px;">
                    <label for="password_wr_password" class="m-label">비밀번호</label>
                    <input type="password" name="wr_password" id="password_wr_password" required maxlength="20" class="m-input" placeholder="작성 시 입력한 비밀번호" autofocus>
                </div>

                <button type="submit" class="m-btn m-btn-primary">확인</button>
            </form>
        </div>
    </main>
</div>
<!-- } 비밀번호 확인 끝 -->
