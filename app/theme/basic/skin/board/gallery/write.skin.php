<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 게시물 작성/수정 시작 { -->
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">

            <header class="m-write-head">
                <h1 class="m-write-title">
                    <?php echo isset($g5['title']) && $g5['title'] ? $g5['title'] : ($w === 'u' ? '글 수정' : '글쓰기') ?>
                </h1>
                <p class="m-write-sub"><?php echo $board['bo_subject'] ?></p>
            </header>

            <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="uid"      value="<?php echo get_uniqid() ?>">
                <input type="hidden" name="w"        value="<?php echo $w ?>">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
                <input type="hidden" name="wr_id"    value="<?php echo $wr_id ?>">
                <input type="hidden" name="sca"      value="<?php echo $sca ?>">
                <input type="hidden" name="sfl"      value="<?php echo $sfl ?>">
                <input type="hidden" name="stx"      value="<?php echo $stx ?>">
                <input type="hidden" name="spt"      value="<?php echo $spt ?>">
                <input type="hidden" name="sst"      value="<?php echo $sst ?>">
                <input type="hidden" name="sod"      value="<?php echo $sod ?>">
                <input type="hidden" name="page"     value="<?php echo $page ?>">
                <input type="hidden" name="token"    value="<?php echo get_write_token($bo_table); ?>">
                <?php
                // 옵션 체크박스 (공지/html/비밀글/답변메일)
                $option = '';
                $option_hidden = '';
                if ($is_notice || $is_html || $is_secret || $is_mail) {
                    if ($is_notice) {
                        $option .= '<label class="m-check"><input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'><span>공지</span></label>';
                    }
                    if ($is_html) {
                        if ($is_dhtml_editor) {
                            $option_hidden .= '<input type="hidden" value="html1" name="html">';
                        } else {
                            $option .= '<label class="m-check"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'><span>HTML</span></label>';
                        }
                    }
                    if ($is_secret) {
                        if ($is_admin || $is_secret == 1) {
                            $option .= '<label class="m-check"><input type="checkbox" id="secret" name="secret" value="secret" '.$secret_checked.'><span>비밀글</span></label>';
                        } else {
                            $option_hidden .= '<input type="hidden" name="secret" value="secret">';
                        }
                    }
                    if ($is_mail) {
                        $option .= '<label class="m-check"><input type="checkbox" id="mail" name="mail" value="mail" '.$recv_email_checked.'><span>답변메일받기</span></label>';
                    }
                }
                echo $option_hidden;
                ?>

                <!-- 1. 카테고리 + 옵션 -->
                <?php if ($is_category || $option) { ?>
                <div class="m-card m-write-section">
                    <div class="m-write-row m-write-row-flex">
                        <?php if ($is_category) { ?>
                        <div style="flex: 1; min-width: 180px;">
                            <label for="ca_name" class="m-label">분류 (필수)</label>
                            <select name="ca_name" id="ca_name" required class="m-input">
                                <option value="">분류를 선택하세요</option>
                                <?php echo $category_option ?>
                            </select>
                        </div>
                        <?php } ?>
                        <?php if ($option) { ?>
                        <div style="flex: 2;">
                            <label class="m-label">옵션</label>
                            <div class="m-write-options"><?php echo $option ?></div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

                <!-- 2. 작성자 정보 -->
                <?php if ($is_name || $is_password || $is_email || $is_homepage) { ?>
                <div class="m-card m-write-section">
                    <div class="m-form-grid-2">
                        <?php if ($is_name) { ?>
                        <div class="m-write-row">
                            <label for="wr_name" class="m-label">이름 (필수)</label>
                            <input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="m-input" placeholder="이름">
                        </div>
                        <?php } ?>
                        <?php if ($is_password) { ?>
                        <div class="m-write-row">
                            <label for="wr_password" class="m-label">비밀번호 <?php echo $password_required ? '(필수)' : '' ?></label>
                            <input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="m-input" placeholder="<?php echo $w === 'u' ? '변경 시에만 입력' : '비밀번호' ?>">
                        </div>
                        <?php } ?>
                        <?php if ($is_email) { ?>
                        <div class="m-write-row">
                            <label for="wr_email" class="m-label">이메일</label>
                            <input type="email" name="wr_email" value="<?php echo $email ?>" id="wr_email" class="m-input" placeholder="example@email.com">
                        </div>
                        <?php } ?>
                        <?php if ($is_homepage) { ?>
                        <div class="m-write-row">
                            <label for="wr_homepage" class="m-label">홈페이지</label>
                            <input type="text" name="wr_homepage" value="<?php echo $homepage ?>" id="wr_homepage" class="m-input" placeholder="https://">
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

                <!-- 3. 제목 + 임시저장 -->
                <div class="m-card m-write-section">
                    <div class="m-write-row">
                        <label for="wr_subject" class="m-label">제목 (필수)</label>
                        <div id="autosave_wrapper" class="m-write-subject-wrap">
                            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="m-input" maxlength="255" placeholder="제목을 입력해 주세요">
                            <?php if ($is_member) { ?>
                            <script src="<?php echo G5_JS_URL ?>/autosave.js"></script>
                            <?php if ($editor_content_js) echo $editor_content_js; ?>
                            <button type="button" id="btn_autosave" class="m-btn m-btn-secondary" style="width: auto; padding: 9px 12px;">
                                임시저장 (<span id="autosave_count"><?php echo $autosave_count ?></span>)
                            </button>
                            <div id="autosave_pop" class="m-autosave-pop" hidden>
                                <strong>임시 저장된 글</strong>
                                <ul></ul>
                                <div style="text-align: right;"><button type="button" class="autosave_close m-btn m-btn-ghost" style="width: auto; padding: 6px 12px;">닫기</button></div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- 4. 내용 -->
                <div class="m-card m-write-section">
                    <div class="m-write-row">
                        <label for="wr_content" class="m-label">내용 (필수)</label>
                        <?php if ($write_min || $write_max) { ?>
                        <p class="m-write-hint" id="char_count_desc">최소 <strong><?php echo $write_min ?></strong>자, 최대 <strong><?php echo $write_max ?></strong>자까지 입력 가능</p>
                        <?php } ?>
                        <div class="m-write-content-wrap <?php echo $is_dhtml_editor ? $config['cf_editor'] : '' ?>">
                            <?php echo $editor_html ?>
                        </div>
                        <?php if ($write_min || $write_max) { ?>
                        <div id="char_count_wrap" class="m-write-charcount"><span id="char_count"></span> 글자</div>
                        <?php } ?>
                    </div>
                </div>

                <!-- 5. 링크 -->
                <?php if ($is_link) { ?>
                <div class="m-card m-write-section">
                    <label class="m-label">관련 링크</label>
                    <?php for ($i = 1; $i <= G5_LINK_COUNT; $i++) { ?>
                    <div class="m-write-link-row">
                        <span class="m-write-link-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </span>
                        <input type="text" name="wr_link<?php echo $i ?>" value="<?php if ($w == 'u') echo $write['wr_link'.$i] ?>" id="wr_link<?php echo $i ?>" class="m-input" placeholder="링크 URL #<?php echo $i ?>">
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>

                <!-- 6. 파일 첨부 -->
                <?php if ($is_file && $file_count) { ?>
                <div class="m-card m-write-section">
                    <label class="m-label">파일 첨부 <span class="m-write-hint" style="margin-left: 6px;">(최대 <?php echo $upload_max_filesize ?>)</span></label>
                    <?php for ($i = 0; $i < $file_count; $i++) { ?>
                    <div class="m-write-file-row">
                        <input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" title="파일첨부 <?php echo $i+1 ?>" class="m-input m-file" style="flex: 1; min-width: 200px;">
                        <?php if ($is_file_content) { ?>
                        <input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : '' ?>" class="m-input" placeholder="파일 설명" style="flex: 2; min-width: 200px;">
                        <?php } ?>
                        <?php if ($w == 'u' && $file[$i]['file']) { ?>
                        <label class="m-check m-write-file-del">
                            <input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i ?>]" value="1">
                            <span><?php echo $file[$i]['source'].' ('.$file[$i]['size'].')' ?> 삭제</span>
                        </label>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>

                <!-- 7. 자동등록방지 -->
                <?php if ($is_use_captcha) { ?>
                <div class="m-card m-write-section">
                    <label class="m-label">자동등록방지</label>
                    <div class="m-captcha-wrap"><?php echo $captcha_html ?></div>
                </div>
                <?php } ?>

                <!-- 액션 버튼 -->
                <div class="m-write-actions">
                    <a href="<?php echo get_pretty_url($bo_table) ?>" class="m-btn m-btn-secondary" style="flex: 1; max-width: 160px;">취소</a>
                    <button type="submit" id="btn_submit" accesskey="s" class="m-btn m-btn-primary" style="flex: 2; max-width: 320px;">
                        <?php echo $w === 'u' ? '수정 완료' : '작성 완료' ?>
                    </button>
                </div>
            </form>
        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>
    <?php require G5_THEME_PATH.'/modern/_footer.inc.php'; ?>
</div>

<style>
.m-write-head { margin-bottom: 16px; }
.m-write-title { font-size: var(--m-text-2xl); font-weight: 700; color: var(--m-text); margin-bottom: 4px; }
.m-write-sub { font-size: var(--m-text-sm); color: var(--m-text-muted); }

.m-write-section { margin-bottom: 14px; padding: 20px 24px; }

.m-write-row-flex { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-start; }

.m-write-options { display: flex; flex-wrap: wrap; gap: 16px; padding-top: 4px; }
.m-write-options .m-check span { font-size: var(--m-text-md); }

.m-write-hint { font-size: var(--m-text-sm); color: var(--m-text-muted); margin-top: -2px; margin-bottom: 8px; }
.m-write-hint strong { color: var(--m-text); font-weight: 600; }

.m-write-subject-wrap { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; position: relative; }
.m-write-subject-wrap .m-input { flex: 1; min-width: 200px; }

.m-autosave-pop {
    position: absolute; top: 100%; right: 0;
    margin-top: 6px; min-width: 280px; max-width: 100%;
    background: var(--m-surface); border: 1px solid var(--m-border);
    border-radius: var(--m-radius); box-shadow: var(--m-shadow-md);
    padding: 14px; z-index: 50;
}
.m-autosave-pop strong { display: block; font-size: var(--m-text-base); margin-bottom: 8px; color: var(--m-text); }
.m-autosave-pop ul { list-style: none; padding: 0; margin: 0 0 8px 0; max-height: 240px; overflow-y: auto; font-size: var(--m-text-sm); }

/* 임시저장 항목: 제목 ─ (날짜 + 삭제) 한 줄 */
.m-autosave-pop ul li {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px;
    border-bottom: 1px solid var(--m-border);
}
.m-autosave-pop ul li:last-child { border-bottom: 0; }
.m-autosave-pop ul li:hover { background: var(--m-surface-2); }
.m-autosave-pop ul li .autosave_load {
    flex: 1; min-width: 0;
    color: var(--m-text); text-decoration: none;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.m-autosave-pop ul li .autosave_load:hover { color: var(--m-primary); }
.m-autosave-pop ul li > span {
    display: inline-flex; align-items: center; gap: 8px;
    flex-shrink: 0;
    font-size: var(--m-text-xs); color: var(--m-text-faint);
}
.m-autosave-pop ul li .autosave_del {
    appearance: none; background: transparent;
    border: 1px solid var(--m-border); border-radius: var(--m-radius-sm);
    padding: 2px 8px; font-size: var(--m-text-xs);
    color: var(--m-text-muted); cursor: pointer;
}
.m-autosave-pop ul li .autosave_del:hover {
    background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.4); color: #ef4444;
}

.m-write-content-wrap { margin-top: 4px; }
.m-write-content-wrap textarea {
    width: 100%; box-sizing: border-box;
    min-height: 320px; padding: 14px; resize: vertical;
    background: var(--m-surface); color: var(--m-text);
    border: 1px solid var(--m-border-hover); border-radius: var(--m-radius);
    font-family: inherit; font-size: var(--m-text-md); line-height: var(--m-leading-relaxed);
    outline: none; transition: border-color 0.15s, box-shadow 0.15s;
}
.m-write-content-wrap textarea:focus {
    border-color: var(--m-primary);
    box-shadow: 0 0 0 3px var(--m-primary-soft);
}
.m-write-charcount { text-align: right; margin-top: 6px; font-size: var(--m-text-sm); color: var(--m-text-faint); }

.m-write-link-row, .m-write-file-row {
    display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
    margin-top: 8px;
}
.m-write-link-row:first-of-type, .m-write-file-row:first-of-type { margin-top: 6px; }
.m-write-link-icon {
    width: 36px; height: 36px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    border-radius: var(--m-radius); color: var(--m-text-faint);
}
.m-write-link-row .m-input { flex: 1; min-width: 200px; }
/* 모바일에서는 폭이 좁아 아이콘이 별도 줄로 떨어지므로 제거 */
@media (max-width: 540px) {
    .m-write-link-icon { display: none; }
    .m-write-link-row .m-input { min-width: 0; }
}
.m-write-file-del { flex-basis: 100%; padding-left: 4px; }
.m-write-file-del span { font-size: var(--m-text-sm); color: var(--m-text-muted); }

.m-write-actions { display: flex; gap: 10px; justify-content: flex-end; margin: 20px 0 0; }
@media (max-width: 540px) {
    .m-write-actions { flex-direction: column; }
    .m-write-actions .m-btn { max-width: none !important; flex: 1 !important; }
}

/* m-form-grid-2 — 회원가입에서도 사용. 안전하게 재정의 (이미 있으면 동일 결과) */
.m-form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 540px) { .m-form-grid-2 { grid-template-columns: 1fr; } }
</style>

<script>
<?php if ($write_min || $write_max) { ?>
var char_min = parseInt(<?php echo $write_min ?>);
var char_max = parseInt(<?php echo $write_max ?>);
check_byte("wr_content", "char_count");
$(function() {
    $("#wr_content").on("keyup", function() { check_byte("wr_content", "char_count"); });
});
<?php } ?>

function html_auto_br(obj) {
    if (obj.checked) {
        var result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을 <br> 태그로 변환하는 기능입니다.");
        obj.value = result ? "html2" : "html1";
    } else {
        obj.value = "";
    }
}

function fwrite_submit(f) {
    <?php echo $editor_js; ?>

    var subject = "", content = "";
    $.ajax({
        url: g5_bbs_url + "/ajax.filter.php", type: "POST",
        data: { "subject": f.wr_subject.value, "content": f.wr_content.value },
        dataType: "json", async: false, cache: false,
        success: function(data) { subject = data.subject; content = data.content; }
    });

    if (subject) { alert("제목에 금지단어('"+subject+"')가 포함되어있습니다"); f.wr_subject.focus(); return false; }
    if (content) {
        alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
        if (typeof(ed_wr_content) != "undefined") ed_wr_content.returnFalse();
        else f.wr_content.focus();
        return false;
    }

    if (document.getElementById("char_count")) {
        if (char_min > 0 || char_max > 0) {
            var cnt = parseInt(check_byte("wr_content", "char_count"));
            if (char_min > 0 && char_min > cnt) { alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다."); return false; }
            if (char_max > 0 && char_max < cnt) { alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다."); return false; }
        }
    }

    <?php echo $captcha_js; ?>

    document.getElementById("btn_submit").disabled = "disabled";
    return true;
}
</script>
<!-- } 게시물 작성/수정 끝 -->
