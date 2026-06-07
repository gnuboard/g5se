<?php
/*
 * /admin/board_copy — 게시판 복사 폼 조각.
 * PopupManager 모달(board_list / board_form)이 fetch 해서 #popupBody 에 주입한다.
 * 단독 페이지/팝업이 아니므로 shell·head.sub·admin.js 를 출력하지 않는다.
 */
$sub_menu = "300100";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
auth_check_menu($auth, $sub_menu, 'w');

if (empty($bo_table)) {
    echo '<p class="legacy-admin-content" style="color:#e11d48;">정상적인 방법으로 이용해주세요.</p>';
    return;
}

// 게시판명 금지어 (board 관리자 권한이 아닐 때만 클라이언트 검증에 사용)
$banned = (!$w) ? get_bo_table_banned_word() : array();
?>
<form name="fboardcopy" id="fboardcopy"
      action="<?php echo G5_ADMIN_URL; ?>/board_copy_update"
      method="post"
      class="legacy-admin-content p-4"
      data-banned="<?php echo htmlspecialchars(json_encode($banned), ENT_QUOTES); ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="token" value="">
    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption>게시판 복사</caption>
            <tbody>
                <tr>
                    <th scope="row">원본 테이블명</th>
                    <td><?php echo $bo_table ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="target_table">복사 테이블명<strong class="sound_only">필수</strong></label></th>
                    <td><input type="text" name="target_table" id="target_table" required class="required alnum_ frm_input" maxlength="20">영문자, 숫자, _ 만 가능 (공백없이)</td>
                </tr>
                <tr>
                    <th scope="row"><label for="target_subject">게시판 제목<strong class="sound_only">필수</strong></label></th>
                    <td><input type="text" name="target_subject" value="[복사본] <?php echo get_sanitize_input($board['bo_subject']); ?>" id="target_subject" required class="required frm_input" maxlength="120"></td>
                </tr>
                <tr>
                    <th scope="row">복사 유형</th>
                    <td>
                        <input type="radio" name="copy_case" value="schema_only" id="copy_case" checked>
                        <label for="copy_case">구조만</label>
                        <input type="radio" name="copy_case" value="schema_data_both" id="copy_case2">
                        <label for="copy_case2">구조와 데이터</label>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="win_btn">
        <input type="submit" class="btn_submit btn" value="복사">
        <input type="button" class="btn_close btn" value="닫기" onclick="PopupManager.close('popupOverlay')">
    </div>
</form>
