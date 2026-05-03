<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 폼메일 시작 { -->
<div class="m-popup">
    <header class="m-popup-head">
        <h1 class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <strong><?php echo $name ?></strong>님께 메일보내기
        </h1>
    </header>

    <form name="fformmail" action="/formmail_send" onsubmit="return fformmail_submit(this);" method="post" enctype="multipart/form-data" class="m-formmail">
        <input type="hidden" name="to" value="<?php echo $email ?>">
        <input type="hidden" name="attach" value="2">
        <?php if ($is_member) { ?>
        <input type="hidden" name="fnick" value="<?php echo get_text($member['mb_nick']) ?>">
        <input type="hidden" name="fmail" value="<?php echo $member['mb_email'] ?>">
        <?php } ?>

        <?php if (!$is_member) { ?>
        <div class="m-formmail-row">
            <div class="m-formmail-field">
                <label for="fnick" class="m-label">이름 <span class="m-label-req">필수</span></label>
                <input type="text" name="fnick" id="fnick" required class="m-input" placeholder="이름">
            </div>
            <div class="m-formmail-field">
                <label for="fmail" class="m-label">E-mail <span class="m-label-req">필수</span></label>
                <input type="email" name="fmail" id="fmail" required class="m-input" placeholder="email@example.com">
            </div>
        </div>
        <?php } ?>

        <div class="m-formmail-field">
            <label for="subject" class="m-label">제목 <span class="m-label-req">필수</span></label>
            <input type="text" name="subject" id="subject" required class="m-input" placeholder="메일 제목">
        </div>

        <div class="m-formmail-field">
            <span class="m-label">형식</span>
            <div class="m-formmail-radios">
                <label class="m-formmail-radio"><input type="radio" name="type" value="0" checked> <span>TEXT</span></label>
                <label class="m-formmail-radio"><input type="radio" name="type" value="1"> <span>HTML</span></label>
                <label class="m-formmail-radio"><input type="radio" name="type" value="2"> <span>TEXT + HTML</span></label>
            </div>
        </div>

        <div class="m-formmail-field">
            <label for="content" class="m-label">내용 <span class="m-label-req">필수</span></label>
            <textarea name="content" id="content" required class="m-input m-formmail-textarea"></textarea>
        </div>

        <div class="m-formmail-field">
            <span class="m-label">첨부 파일</span>
            <div class="m-formmail-files">
                <label class="m-formmail-file" for="file1">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                    <span class="m-formmail-file-label">파일 1 선택</span>
                    <input type="file" name="file1" id="file1" class="m-formmail-file-input">
                </label>
                <label class="m-formmail-file" for="file2">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                    <span class="m-formmail-file-label">파일 2 선택</span>
                    <input type="file" name="file2" id="file2" class="m-formmail-file-input">
                </label>
            </div>
            <p class="m-memo-hint">첨부는 누락될 수 있으니 발송 후 확인해 주세요.</p>
        </div>

        <div class="m-formmail-field">
            <?php echo captcha_html(); ?>
        </div>

        <div class="m-popup-actions">
            <button type="submit" id="btn_submit" class="m-btn" style="width:auto; padding:10px 22px;">메일발송</button>
            <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:9px 20px;">창닫기</button>
        </div>
    </form>
</div>

<script>
with (document.fformmail) {
    if (typeof fname != "undefined")     fname.focus();
    else if (typeof subject != "undefined") subject.focus();
}

function fformmail_submit(f) {
    <?php echo chk_captcha_js();  ?>
    if (f.file1.value || f.file2.value) {
        if (!confirm("첨부파일의 용량이 큰경우 전송시간이 오래 걸립니다.\n\n메일보내기가 완료되기 전에 창을 닫거나 새로고침 하지 마십시오.")) return false;
    }
    document.getElementById('btn_submit').disabled = true;
    return true;
}

// 파일 선택 시 라벨에 파일명 반영
['file1','file2'].forEach(function(id){
    var input = document.getElementById(id);
    if (!input) return;
    input.addEventListener('change', function(){
        var label = input.closest('.m-formmail-file').querySelector('.m-formmail-file-label');
        label.textContent = input.files[0] ? input.files[0].name : ('파일 ' + id.slice(-1) + ' 선택');
    });
});
</script>

<style>
.m-formmail { display: flex; flex-direction: column; gap: 8px; }
.m-formmail-field { display: flex; flex-direction: column; gap: 3px; }
.m-formmail-field .m-label { margin-bottom: 0; }
.m-formmail-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
@media (max-width: 480px) { .m-formmail-row { grid-template-columns: 1fr; } }

.m-formmail-radios { display: flex; gap: 14px; padding: 2px 0; }
.m-formmail-radio {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: var(--m-text-sm); color: var(--m-text-soft);
    cursor: pointer;
}
.m-formmail-radio input { accent-color: var(--m-primary); }

.m-formmail-textarea {
    min-height: 100px; max-height: 30vh;
    padding: 8px 10px; font-family: inherit; resize: vertical;
    line-height: var(--m-leading);
}

.m-formmail-files { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
@media (max-width: 480px) { .m-formmail-files { grid-template-columns: 1fr; } }
.m-formmail-file {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 10px; min-width: 0;
    background: var(--m-surface); border: 1px dashed var(--m-border-hover);
    border-radius: var(--m-radius);
    font-size: var(--m-text-xs); color: var(--m-text-soft);
    cursor: pointer; transition: border-color 0.15s, color 0.15s;
}
.m-formmail-file:hover { border-color: var(--m-primary); color: var(--m-primary); }
.m-formmail-file svg { color: var(--m-text-faint); flex-shrink: 0; }
.m-formmail-file:hover svg { color: var(--m-primary); }
.m-formmail-file-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
.m-formmail-file-input { position: absolute; left: -9999px; opacity: 0; width: 0; height: 0; }
</style>
<!-- } 폼메일 끝 -->
