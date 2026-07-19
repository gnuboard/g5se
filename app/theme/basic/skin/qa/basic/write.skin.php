<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 1:1 문의 작성/수정 시작 { -->
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">
            <header class="m-board-head">
                <div>
                    <h1 style="font-size: 22px; margin-bottom: 4px;">
                        <?php echo ($w == 'u' ? '문의 수정' : '1:1 문의 작성') ?>
                    </h1>
                    <p style="font-size: 13px; color: var(--m-text-muted);">
                        <?php echo get_text($qaconfig['qa_title'] ?? '1:1문의') ?> · 답변은 등록된 이메일/휴대폰으로 알림이 갈 수 있습니다.
                    </p>
                </div>
            </header>

            <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="w"     value="<?php echo $w ?>">
                <input type="hidden" name="qa_id" value="<?php echo (int)$qa_id ?>">
                <input type="hidden" name="sca"   value="<?php echo get_text($sca) ?>">
                <input type="hidden" name="stx"   value="<?php echo get_text($stx) ?>">
                <input type="hidden" name="page"  value="<?php echo (int)$page ?>">
                <input type="hidden" name="token" value="<?php echo get_text($token) ?>">
                <?php
                if ($is_dhtml_editor) {
                    echo '<input type="hidden" name="qa_html" value="1">';
                }
                ?>

                <?php if ($category_option) { ?>
                <section class="m-card m-write-section">
                    <div class="m-write-row">
                        <label for="qa_category" class="m-label">분류 <span class="m-label-req">필수</span></label>
                        <select name="qa_category" id="qa_category" required class="m-input">
                            <option value="">분류를 선택하세요</option>
                            <?php echo $category_option ?>
                        </select>
                    </div>
                </section>
                <?php } ?>

                <?php if ($is_email || $is_hp) { ?>
                <section class="m-card m-write-section">
                    <h2 class="m-write-section-title">연락처</h2>
                    <?php if ($is_email) { ?>
                    <div class="m-write-row m-qa-contact-row">
                        <label for="qa_email" class="m-label">이메일</label>
                        <div class="m-qa-contact-input">
                            <input type="email" name="qa_email" id="qa_email" value="<?php echo get_text($write['qa_email']) ?>" <?php echo $req_email ?> class="m-input <?php echo $req_email ?>" maxlength="100" placeholder="email@example.com">
                            <label class="m-qa-checkbox">
                                <input type="checkbox" name="qa_email_recv" id="qa_email_recv" value="1" <?php if ($write['qa_email_recv']) echo 'checked'; ?>>
                                답변 받기
                            </label>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if ($is_hp) { ?>
                    <div class="m-write-row m-qa-contact-row">
                        <label for="qa_hp" class="m-label">휴대폰</label>
                        <div class="m-qa-contact-input">
                            <input type="tel" name="qa_hp" id="qa_hp" value="<?php echo get_text($write['qa_hp']) ?>" <?php echo $req_hp ?> class="m-input <?php echo $req_hp ?>" placeholder="010-0000-0000">
                            <?php if ($qaconfig['qa_use_sms']) { ?>
                            <label class="m-qa-checkbox">
                                <input type="checkbox" name="qa_sms_recv" id="qa_sms_recv" value="1" <?php if ($write['qa_sms_recv']) echo 'checked'; ?>>
                                답변 SMS 알림
                            </label>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </section>
                <?php } ?>

                <section class="m-card m-write-section">
                    <div class="m-write-row">
                        <label for="qa_subject" class="m-label">제목 <span class="m-label-req">필수</span></label>
                        <input type="text" name="qa_subject" id="qa_subject" value="<?php echo get_text($write['qa_subject']) ?>" required class="m-input" maxlength="255" placeholder="문의 제목을 입력하세요">
                    </div>
                </section>

                <section class="m-card m-write-section">
                    <div class="m-write-row qa_content_wrap <?php echo $is_dhtml_editor ? $config['cf_editor'] : ''; ?>">
                        <label for="qa_content" class="m-label">내용 <span class="m-label-req">필수</span></label>
                        <?php echo $editor_html ?>
                    </div>

                    <?php if (!$is_dhtml_editor) { ?>
                    <div class="m-write-row" style="margin-top: 10px;">
                        <label class="m-qa-checkbox">
                            <input type="checkbox" id="qa_html" name="qa_html" onclick="html_auto_br(this);" value="<?php echo $html_value ?>" <?php echo $html_checked ?>>
                            HTML 사용
                        </label>
                    </div>
                    <?php } ?>
                </section>

                <section class="m-card m-write-section">
                    <h2 class="m-write-section-title">첨부 파일</h2>
                    <div class="m-qa-files">
                        <label class="m-qa-file" for="bf_file_1">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                            <span class="m-qa-file-label">파일 1 선택</span>
                            <input type="file" name="bf_file[1]" id="bf_file_1" class="m-qa-file-input" title="용량 <?php echo $upload_max_filesize ?> 이하">
                        </label>
                        <?php if ($w == 'u' && $write['qa_file1']) { ?>
                        <label class="m-qa-file-del">
                            <input type="checkbox" id="bf_file_del1" name="bf_file_del[1]" value="1">
                            <span><strong><?php echo get_text($write['qa_source1']) ?></strong> 파일 삭제</span>
                        </label>
                        <?php } ?>
                    </div>

                    <div class="m-qa-files" style="margin-top: 8px;">
                        <label class="m-qa-file" for="bf_file_2">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                            <span class="m-qa-file-label">파일 2 선택</span>
                            <input type="file" name="bf_file[2]" id="bf_file_2" class="m-qa-file-input" title="용량 <?php echo $upload_max_filesize ?> 이하">
                        </label>
                        <?php if ($w == 'u' && $write['qa_file2']) { ?>
                        <label class="m-qa-file-del">
                            <input type="checkbox" id="bf_file_del2" name="bf_file_del[2]" value="1">
                            <span><strong><?php echo get_text($write['qa_source2']) ?></strong> 파일 삭제</span>
                        </label>
                        <?php } ?>
                    </div>
                </section>

                <div class="m-write-actions">
                    <a href="<?php echo $list_href ?>" class="m-btn m-btn-secondary" style="flex: 1; max-width: 160px;">취소</a>
                    <button type="submit" id="btn_submit" accesskey="s" class="m-btn m-btn-primary" style="flex: 2; max-width: 320px;">
                        <?php echo ($w == 'u' ? '수정 완료' : '문의 등록') ?>
                    </button>
                </div>
            </form>
        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>

    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>

<script>
function html_auto_br(obj) {
    if (obj.checked) {
        var r = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
        obj.value = r ? "2" : "1";
    } else {
        obj.value = "";
    }
}
function fwrite_submit(f) {
    <?php echo $editor_js ?>
    var subject = "", content = "";
    $.ajax({
        url: g5_bbs_url+"/ajax.filter.php", type: "POST",
        data: { "subject": f.qa_subject.value, "content": f.qa_content.value },
        dataType: "json", async: false, cache: false,
        success: function(data) { subject = data.subject; content = data.content; }
    });
    if (subject) { alert("제목에 금지단어('"+subject+"')가 포함되어있습니다"); f.qa_subject.focus(); return false; }
    if (content) {
        alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
        if (typeof(ed_qa_content) != "undefined") ed_qa_content.returnFalse();
        else f.qa_content.focus();
        return false;
    }
    <?php if ($is_hp) { ?>
    var hp = f.qa_hp.value.replace(/[0-9\-]/g, "");
    if (hp.length > 0) { alert("휴대폰번호는 숫자, - 으로만 입력해 주십시오."); return false; }
    <?php } ?>
    $.ajax({
        type: "POST", url: g5_bbs_url+"/ajax.write.token.php",
        data: { 'token_case': 'qa_write' },
        cache: false, async: false, dataType: "json",
        success: function(data) {
            if (typeof data.token !== "undefined") {
                if (typeof f.token === "undefined") $(f).prepend('<input type="hidden" name="token" value="">');
                $(f).find("input[name=token]").val(data.token);
            }
        }
    });
    document.getElementById("btn_submit").disabled = "disabled";
    return true;
}
['bf_file_1','bf_file_2'].forEach(function(id){
    var input = document.getElementById(id);
    if (!input) return;
    input.addEventListener('change', function(){
        var label = input.closest('.m-qa-file').querySelector('.m-qa-file-label');
        label.textContent = input.files[0] ? input.files[0].name : ('파일 ' + id.slice(-1) + ' 선택');
    });
});
</script>

<style>
/* board-common write 레이아웃 — board write.skin 의 동일 규칙 복제 (페이지 공유 안 됨) */
.m-board-head {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; margin-bottom: 16px; flex-wrap: wrap;
}
.m-write-section { margin-bottom: 14px; padding: 20px 24px; }
.m-write-section-title {
    margin: 0 0 12px; padding-bottom: 8px;
    border-bottom: 1px solid var(--m-border);
    font-size: var(--m-text-sm); font-weight: 600;
    color: var(--m-text-soft); letter-spacing: 0.02em;
}
.m-write-row { }
.m-write-row .m-label { margin-bottom: 6px; }
.m-write-row + .m-write-row { margin-top: 14px; }
.m-write-actions {
    display: flex; gap: 10px; justify-content: flex-end;
    margin: 20px 0 0;
}
@media (max-width: 540px) {
    .m-write-actions { flex-direction: column-reverse; }
    .m-write-actions .m-btn { width: 100% !important; max-width: none !important; }
}
.m-label-req {
    font-size: var(--m-text-xs); font-weight: 500;
    color: var(--m-primary); margin-left: 4px;
}

.m-qa-contact-row { }
.m-qa-contact-input { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.m-qa-contact-input .m-input { flex: 1; min-width: 220px; }

/* 첨부 파일 — 클립 svg + dashed-border 라벨 (native input 숨김) */
.m-qa-files { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
@media (max-width: 540px) { .m-qa-files { grid-template-columns: 1fr; } }
.m-qa-file {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 12px; min-width: 0;
    background: var(--m-surface); border: 1px dashed var(--m-border-hover);
    border-radius: var(--m-radius);
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    cursor: pointer; transition: border-color 0.15s, color 0.15s;
}
.m-qa-file:hover { border-color: var(--m-primary); color: var(--m-primary); }
.m-qa-file svg { color: var(--m-text-faint); flex-shrink: 0; display: inline-block; }
.m-qa-file:hover svg { color: var(--m-primary); }
.m-qa-file-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
.m-qa-file-input { position: absolute; left: -9999px; opacity: 0; width: 0; height: 0; }

.m-qa-file-del {
    display: inline-flex; align-items: center; gap: 6px;
    margin-left: 4px;
    font-size: var(--m-text-sm); color: var(--m-text-muted); cursor: pointer;
}
.m-qa-file-del input { accent-color: var(--m-primary); }
.m-qa-file-del strong { font-weight: 600; color: var(--m-text-soft); }
</style>
<!-- } 1:1 문의 작성/수정 끝 -->
