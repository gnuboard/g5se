<?php
if (!defined('_GNUBOARD_')) exit;
?>

<!-- 답변 영역 (관리자: 답변폼 / 일반: 안내 메시지) 시작 { -->
<section class="m-qa-answer-form-wrap">
    <?php if ($is_admin) { ?>
    <header class="m-qa-answer-form-head">
        <span class="m-qa-answer-badge">답변 등록</span>
        <span class="m-qa-answer-form-hint">관리자 권한으로 답변을 작성합니다.</span>
    </header>

    <form name="fanswer" method="post" action="<?php echo G5_BBS_URL ?>/qawrite_update.php" onsubmit="return fwrite_submit(this);" enctype="multipart/form-data" autocomplete="off" class="m-qa-answer-form">
        <input type="hidden" name="qa_id" value="<?php echo (int)$view['qa_id'] ?>">
        <input type="hidden" name="w"     value="a">
        <input type="hidden" name="sca"   value="<?php echo get_text($sca) ?>">
        <input type="hidden" name="stx"   value="<?php echo get_text($stx) ?>">
        <input type="hidden" name="page"  value="<?php echo (int)$page ?>">
        <input type="hidden" name="token" value="<?php echo get_text($token) ?>">

        <?php
        if ($is_dhtml_editor) {
            echo '<input type="hidden" name="qa_html" value="1">';
        }
        ?>

        <div class="m-qa-answer-form-field">
            <label for="qa_subject" class="m-label">답변 제목</label>
            <input type="text" name="qa_subject" id="qa_subject" required class="m-input" maxlength="255" placeholder="답변 제목을 입력하세요">
        </div>

        <div class="m-qa-answer-form-field qa_content_wrap <?php echo $is_dhtml_editor ? $config['cf_editor'] : ''; ?>">
            <label for="qa_content" class="m-label">답변 내용</label>
            <?php echo $editor_html ?>
        </div>

        <?php if (!$is_dhtml_editor) { ?>
        <div class="m-qa-answer-form-field">
            <label class="m-qa-checkbox">
                <input type="checkbox" id="qa_html" name="qa_html" onclick="html_auto_br(this);" value="<?php echo $html_value ?>" <?php echo $html_checked ?>>
                HTML 사용
            </label>
        </div>
        <?php } ?>

        <div class="m-qa-answer-form-field">
            <span class="m-label">첨부 파일</span>
            <div class="m-qa-files">
                <label class="m-qa-file" for="bf_file_1">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                    <span class="m-qa-file-label">파일 1 선택</span>
                    <input type="file" name="bf_file[1]" id="bf_file_1" class="m-qa-file-input" title="용량 <?php echo $upload_max_filesize ?> 이하">
                </label>
                <label class="m-qa-file" for="bf_file_2">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                    <span class="m-qa-file-label">파일 2 선택</span>
                    <input type="file" name="bf_file[2]" id="bf_file_2" class="m-qa-file-input" title="용량 <?php echo $upload_max_filesize ?> 이하">
                </label>
            </div>
        </div>

        <div class="m-qa-answer-form-actions">
            <button type="submit" id="btn_submit" class="m-btn" style="width:auto; padding:10px 22px;" accesskey="s">답변 등록</button>
        </div>
    </form>

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
    // 파일 선택 시 라벨에 파일명 반영
    ['bf_file_1','bf_file_2'].forEach(function(id){
        var input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('change', function(){
            var label = input.closest('.m-qa-file').querySelector('.m-qa-file-label');
            label.textContent = input.files[0] ? input.files[0].name : ('파일 ' + id.slice(-1) + ' 선택');
        });
    });
    </script>

    <?php } else { ?>
    <div class="m-qa-answer-pending">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <p class="m-qa-answer-pending-title">답변 준비 중입니다</p>
        <p class="m-qa-answer-pending-desc">고객님의 문의에 대한 답변이 등록되면 알려드리겠습니다.</p>
    </div>
    <?php } ?>
</section>

<style>
.m-qa-answer-form-wrap {
    margin: 24px 28px;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-lg);
    overflow: hidden;
}
.m-qa-answer-form-head {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 14px 18px;
    background: var(--m-surface);
    border-bottom: 1px solid var(--m-border);
}
.m-qa-answer-form-head .m-qa-answer-badge {
    background: var(--m-primary-soft); color: var(--m-primary);
}
.m-qa-answer-form-hint { font-size: var(--m-text-sm); color: var(--m-text-muted); }

.m-qa-answer-form { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; }
.m-qa-answer-form-field { display: flex; flex-direction: column; gap: 6px; }
.m-qa-answer-form-field .m-label { margin-bottom: 0; }

.m-qa-checkbox {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: var(--m-text-sm); color: var(--m-text-soft); cursor: pointer;
}
.m-qa-checkbox input { accent-color: var(--m-primary); }

.m-qa-files { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
@media (max-width: 540px) { .m-qa-files { grid-template-columns: 1fr; } }
.m-qa-file {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 10px; min-width: 0;
    background: var(--m-surface); border: 1px dashed var(--m-border-hover);
    border-radius: var(--m-radius);
    font-size: var(--m-text-xs); color: var(--m-text-soft);
    cursor: pointer; transition: border-color 0.15s, color 0.15s;
}
.m-qa-file:hover { border-color: var(--m-primary); color: var(--m-primary); }
.m-qa-file svg { color: var(--m-text-faint); flex-shrink: 0; }
.m-qa-file:hover svg { color: var(--m-primary); }
.m-qa-file-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
.m-qa-file-input { position: absolute; left: -9999px; opacity: 0; width: 0; height: 0; }

.m-qa-answer-form-actions { display: flex; justify-content: flex-end; }

.m-qa-answer-pending {
    padding: 36px 24px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.m-qa-answer-pending svg { color: var(--m-primary); margin-bottom: 6px; }
.m-qa-answer-pending-title { margin: 0; font-size: var(--m-text-md); font-weight: 600; color: var(--m-text); }
.m-qa-answer-pending-desc  { margin: 0; font-size: var(--m-text-sm); color: var(--m-text-muted); }
</style>
<!-- } 답변 영역 끝 -->
