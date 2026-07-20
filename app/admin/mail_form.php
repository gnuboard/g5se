<?php
/*
 * /admin/mail_form — 회원메일 입력/수정.
 */
$sub_menu = "200300";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
require_once G5_EDITOR_LIB;
auth_check_menu($auth, $sub_menu, 'r');

$html_title = '회원메일';

$ma_id = isset($_GET['ma_id']) ? (int) $_GET['ma_id'] : 0;
$ma = array('ma_id' => 0, 'ma_subject' => '', 'ma_content' => '');

if ($w == 'u') {
    $html_title .= '수정';
    $readonly = ' readonly';

    $sql = " select * from {$g5['mail_table']} where ma_id = '{$ma_id}' ";
    $ma = sql_pdo_fetch($sql);
    if (!$ma['ma_id']) {
        alert('등록된 자료가 없습니다.');
    }
} else {
    $html_title .= '입력';
}

$g5['title'] = $html_title;
admin_layout_start($g5['title'], 'mail');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">

<div class="local_desc">
    <p>메일 내용에 {이름} , {닉네임} , {회원아이디} , {이메일} 처럼 내용에 삽입하면 해당 내용에 맞게 변환하여 메일을 발송합니다.</p>
</div>

<form name="fmailform" id="fmailform" action="<?php echo G5_ADMIN_URL; ?>/mail_update" onsubmit="return fmailform_check(this);" method="post">
    <input type="hidden" name="w" value="<?php echo $w ?>" id="w">
    <input type="hidden" name="ma_id" value="<?php echo $ma['ma_id'] ?>" id="ma_id">
    <input type="hidden" name="token" value="" id="token">

    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row"><label for="ma_subject">메일 제목<strong class="sound_only">필수</strong></label></th>
                    <td><input type="text" name="ma_subject" value="<?php echo get_sanitize_input($ma['ma_subject']); ?>" id="ma_subject" required class="required frm_input" size="100"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ma_content">메일 내용<strong class="sound_only">필수</strong></label></th>
                    <td><?php echo editor_html("ma_content", get_text(html_purifier($ma['ma_content']), 0)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top ">
        <input type="submit" class="btn_submit btn" accesskey="s" value="확인">
    </div>
</form>

<script>
    function fmailform_check(f) {
        errmsg = "";
        errfld = "";

        check_field(f.ma_subject, "제목을 입력하세요.");
        //check_field(f.ma_content, "내용을 입력하세요.");

        if (errmsg != "") {
            alert(errmsg);
            errfld.focus();
            return false;
        }

        <?php echo get_editor_js("ma_content"); ?>
        <?php echo chk_editor_js("ma_content"); ?>

        return true;
    }

    document.fmailform.ma_subject.focus();
</script>

<?php
?>
</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/mail_form — modern shell wrap end
