<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
// 댓글 글자수 제한 (gnuboard 표준 변수)
var char_min = parseInt(<?php echo $comment_min ?>);
var char_max = parseInt(<?php echo $comment_max ?>);
</script>

<!-- 댓글 시작 { -->
<section id="bo_vc" class="m-comments">
    <header class="m-comments-head">
        <h2 class="m-comments-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            댓글 <strong><?php echo $view['wr_comment'] ?></strong>
        </h2>
        <button type="button" class="m-icon-btn cmt_btn" aria-label="댓글 토글" title="댓글 펼치기/접기">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
    </header>

    <div class="m-comments-body">
        <?php
        $cmt_amt = count($list);
        if ($cmt_amt === 0) { ?>
        <p class="m-comments-empty">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.4;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>아직 댓글이 없습니다. 첫 댓글을 남겨주세요.</span>
        </p>
        <?php } ?>

        <?php for ($i = 0; $i < $cmt_amt; $i++) {
            $comment_id = $list[$i]['wr_id'];
            $cmt_depth  = strlen($list[$i]['wr_comment_reply']);
            $comment    = $list[$i]['content'];
            $comment    = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $comment);
            $cmt_sv     = $cmt_amt - $i + 1;
            $c_reply_href = $comment_common_url.'&amp;c_id='.$comment_id.'&amp;w=c#bo_vc_w';
            $c_edit_href  = $comment_common_url.'&amp;c_id='.$comment_id.'&amp;w=cu#bo_vc_w';
            $is_comment_reply_edit = ($list[$i]['is_reply'] || $list[$i]['is_edit'] || $list[$i]['is_del']) ? 1 : 0;
            $is_secret = strstr($list[$i]['wr_option'], "secret");
        ?>
        <article id="c_<?php echo $comment_id ?>" class="m-comment <?php echo $cmt_depth ? 'm-comment-reply' : '' ?>" style="<?php echo $cmt_depth ? 'margin-left: '.($cmt_depth * 28).'px;' : '' ?>">
            <div class="m-comment-avatar"><?php echo get_member_profile_img($list[$i]['mb_id']) ?></div>

            <div class="m-comment-main">
                <header class="m-comment-head" style="z-index: <?php echo $cmt_sv ?>;">
                    <span class="m-comment-name">
                        <?php if ($cmt_depth) { ?>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 3px; opacity: 0.6;"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                        <?php } ?>
                        <?php echo $list[$i]['name'] ?>
                        <?php if ($is_ip_view) { ?><span class="m-comment-ip">(<?php echo $list[$i]['ip'] ?>)</span><?php } ?>
                    </span>
                    <time class="m-comment-time" datetime="<?php echo date('Y-m-d\TH:i:s+09:00', strtotime($list[$i]['datetime'])) ?>"><?php echo $list[$i]['datetime'] ?></time>
                    <?php @include(G5_SNS_PATH.'/view_comment_list.sns.skin.php'); ?>
                </header>

                <div class="m-comment-content cmt_contents">
                    <?php if ($is_secret) { ?>
                    <span class="m-comment-secret">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        비밀
                    </span>
                    <?php } ?>
                    <?php echo $comment ?>

                    <?php if ($is_comment_reply_edit && $w == 'cu') {
                        $sql = " select wr_id, wr_content, mb_id from $write_table where wr_id = '$c_id' and wr_is_comment = '1' ";
                        $cmt = sql_fetch($sql);
                        if (isset($cmt)) {
                            if (!($is_admin || ($member['mb_id'] == $cmt['mb_id'] && $cmt['mb_id']))) $cmt['wr_content'] = '';
                            $c_wr_content = $cmt['wr_content'];
                        }
                    } ?>
                </div>

                <span id="edit_<?php echo $comment_id ?>" class="bo_vc_w m-comment-form-slot"></span>
                <span id="reply_<?php echo $comment_id ?>" class="bo_vc_w m-comment-form-slot"></span>

                <input type="hidden" value="<?php echo $is_secret ?>" id="secret_comment_<?php echo $comment_id ?>">
                <textarea id="save_comment_<?php echo $comment_id ?>" style="display:none"><?php echo get_text($list[$i]['content1'], 0) ?></textarea>

                <?php if ($is_comment_reply_edit) { ?>
                <div class="m-comment-actions">
                    <?php if ($list[$i]['is_reply']) { ?>
                    <a href="<?php echo $c_reply_href ?>" onclick="comment_box('<?php echo $comment_id ?>', 'c'); return false;" class="m-comment-action">답글</a>
                    <?php } ?>
                    <?php if ($list[$i]['is_edit']) { ?>
                    <a href="<?php echo $c_edit_href ?>" onclick="comment_box('<?php echo $comment_id ?>', 'cu'); return false;" class="m-comment-action">수정</a>
                    <?php } ?>
                    <?php if ($list[$i]['is_del']) { ?>
                    <a href="<?php echo $list[$i]['del_link'] ?>" onclick="return comment_delete();" class="m-comment-action m-comment-action-danger">삭제</a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </article>
        <?php } ?>
    </div>
</section>
<!-- } 댓글 끝 -->

<?php if ($is_comment_write) {
    if ($w == '') $w = 'c';
?>
<!-- 댓글 쓰기 시작 { -->
<aside id="bo_vc_w" class="m-comment-write bo_vc_w">
    <h2 class="m-comment-write-title">
        <?php echo $is_member ? get_text($member['mb_nick']).' 님의 댓글 작성' : '댓글 작성' ?>
    </h2>
    <form name="fviewcomment" id="fviewcomment" action="<?php echo $comment_action_url ?>" onsubmit="return fviewcomment_submit(this);" method="post" autocomplete="off">
        <input type="hidden" name="w"          value="<?php echo $w ?>"      id="w">
        <input type="hidden" name="bo_table"   value="<?php echo $bo_table ?>">
        <input type="hidden" name="wr_id"      value="<?php echo $wr_id ?>">
        <input type="hidden" name="comment_id" value="<?php echo $c_id ?>"   id="comment_id">
        <input type="hidden" name="sca"        value="<?php echo $sca ?>">
        <input type="hidden" name="sfl"        value="<?php echo $sfl ?>">
        <input type="hidden" name="stx"        value="<?php echo $stx ?>">
        <input type="hidden" name="spt"        value="<?php echo $spt ?>">
        <input type="hidden" name="page"       value="<?php echo $page ?>">
        <input type="hidden" name="is_good"    value="">

        <div class="m-comment-textarea-wrap">
            <textarea id="wr_content" name="wr_content" maxlength="10000" required class="m-input m-comment-textarea" placeholder="댓글 내용을 입력해 주세요"
                <?php if ($comment_min || $comment_max) { ?>onkeyup="check_byte('wr_content', 'char_count');"<?php } ?>><?php echo $c_wr_content ?? '' ?></textarea>
            <?php if ($comment_min || $comment_max) { ?>
            <div id="char_cnt" class="m-comment-charcount"><span id="char_count"></span> 글자</div>
            <script>check_byte('wr_content', 'char_count');</script>
            <?php } ?>
        </div>

        <script>
        $(document).on("keyup change", "textarea#wr_content[maxlength]", function() {
            var str = $(this).val(), mx = parseInt($(this).attr("maxlength"));
            if (str.length > mx) { $(this).val(str.substr(0, mx)); return false; }
        });
        </script>

        <div class="m-comment-write-row">
            <div class="m-comment-write-info">
                <?php if ($is_guest) { ?>
                <input type="text" name="wr_name" value="<?php echo get_cookie("ck_sns_name") ?>" id="wr_name" required class="m-input" maxlength="20" placeholder="이름" style="max-width: 140px;">
                <input type="password" name="wr_password" id="wr_password" required class="m-input" maxlength="20" placeholder="비밀번호" style="max-width: 140px;">
                <?php } ?>

                <?php if ($board['bo_use_sns'] && ($config['cf_facebook_appid'] || $config['cf_twitter_key'])) { ?>
                <span id="bo_vc_send_sns"></span>
                <?php } ?>
            </div>
            <div class="m-comment-write-actions">
                <label class="m-check">
                    <input type="checkbox" name="wr_secret" value="secret" id="wr_secret">
                    <span>비밀글</span>
                </label>
                <button type="submit" id="btn_submit" class="m-btn m-btn-primary" style="width: auto; padding: 10px 18px;">등록</button>
            </div>
        </div>

        <?php if ($is_guest && !empty($captcha_html)) { ?>
        <div class="m-comment-captcha m-captcha-wrap"><?php echo $captcha_html ?></div>
        <?php } ?>
    </form>
</aside>

<script>
var save_before = '';
var save_html = document.getElementById('bo_vc_w').innerHTML;

function good_and_write() {
    var f = document.fviewcomment;
    if (fviewcomment_submit(f)) { f.is_good.value = 1; f.submit(); }
    else f.is_good.value = 0;
}

function fviewcomment_submit(f) {
    var pattern = /(^\s*)|(\s*$)/g;
    f.is_good.value = 0;

    var subject = "", content = "";
    $.ajax({
        url: g5_bbs_url + "/ajax.filter.php", type: "POST",
        data: { "subject": "", "content": f.wr_content.value },
        dataType: "json", async: false, cache: false,
        success: function(data) { subject = data.subject; content = data.content; }
    });
    if (content) { alert("내용에 금지단어('" + content + "')가 포함되어있습니다"); f.wr_content.focus(); return false; }

    document.getElementById('wr_content').value = document.getElementById('wr_content').value.replace(pattern, "");
    if (char_min > 0 || char_max > 0) {
        check_byte('wr_content', 'char_count');
        var cnt = parseInt(document.getElementById('char_count').innerHTML);
        if (char_min > 0 && char_min > cnt) { alert("댓글은 " + char_min + "글자 이상 쓰셔야 합니다."); return false; }
        if (char_max > 0 && char_max < cnt) { alert("댓글은 " + char_max + "글자 이하로 쓰셔야 합니다."); return false; }
    } else if (!document.getElementById('wr_content').value) {
        alert("댓글을 입력하여 주십시오."); return false;
    }

    if (typeof(f.wr_name) != 'undefined') {
        f.wr_name.value = f.wr_name.value.replace(pattern, "");
        if (f.wr_name.value == '') { alert('이름이 입력되지 않았습니다.'); f.wr_name.focus(); return false; }
    }
    if (typeof(f.wr_password) != 'undefined') {
        f.wr_password.value = f.wr_password.value.replace(pattern, "");
        if (f.wr_password.value == '') { alert('비밀번호가 입력되지 않았습니다.'); f.wr_password.focus(); return false; }
    }

    <?php if ($is_guest) echo chk_captcha_js(); ?>

    set_comment_token(f);
    document.getElementById("btn_submit").disabled = "disabled";
    return true;
}

function comment_box(comment_id, work) {
    var el_id, form_el = 'fviewcomment',
        respond = document.getElementById(form_el);

    if (comment_id) el_id = (work == 'c') ? 'reply_' + comment_id : 'edit_' + comment_id;
    else            el_id = 'bo_vc_w';

    if (save_before != el_id) {
        if (save_before) document.getElementById(save_before).style.display = 'none';
        document.getElementById(el_id).style.display = '';
        document.getElementById(el_id).appendChild(respond);
        document.getElementById('wr_content').value = '';

        if (work == 'cu') {
            document.getElementById('wr_content').value = document.getElementById('save_comment_' + comment_id).value;
            if (typeof char_count != 'undefined') check_byte('wr_content', 'char_count');
            document.getElementById('wr_secret').checked = !!document.getElementById('secret_comment_' + comment_id).value;
        }

        document.getElementById('comment_id').value = comment_id;
        document.getElementById('w').value = work;
        if (save_before) $("#captcha_reload").trigger("click");
        save_before = el_id;
    }
}

function comment_delete() {
    return confirm("이 댓글을 삭제하시겠습니까?");
}

comment_box('', 'c'); // 초기 폼 노출

<?php if ($board['bo_use_sns'] && ($config['cf_facebook_appid'] || $config['cf_twitter_key'])) { ?>
$(function() {
    $("#bo_vc_send_sns").load("<?php echo G5_SNS_URL ?>/view_comment_write.sns.skin.php?bo_table=<?php echo $bo_table ?>", function() {
        save_html = document.getElementById('bo_vc_w').innerHTML;
    });
});
<?php } ?>
</script>
<?php } ?>
<!-- } 댓글 쓰기 끝 -->

<style>
/* ──────────────────────────────────────────────
   댓글 영역 — view 카드 안에서 시작 (article.m-view 다음 형제로 위치)
   ────────────────────────────────────────────── */
.m-comments {
    margin: 18px 14px 0;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-lg);
    padding: 0;
    overflow: hidden;
}
.m-comments-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 24px;
    border-bottom: 1px solid var(--m-border);
}
.m-comments-title {
    display: flex; align-items: center; gap: 6px;
    font-size: var(--m-text-base); font-weight: 600;
    color: var(--m-text-soft);
}
.m-comments-title strong { color: var(--m-primary); font-weight: 700; }

.m-comments-body { padding: 4px 24px; }

.m-comments-empty {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    padding: 32px 16px; margin: 0;
    color: var(--m-text-faint); font-size: var(--m-text-sm); text-align: center;
}

/* 개별 댓글 */
.m-comment {
    display: flex; gap: 12px;
    padding: 16px 0;
    border-bottom: 1px solid var(--m-border);
}
.m-comments-body > .m-comment:last-child { border-bottom: 0; }
.m-comment-reply {
    border-left: 2px solid var(--m-border);
    padding-left: 12px;
}

.m-comment-avatar { flex-shrink: 0; }
.m-comment-avatar img {
    width: 36px; height: 36px; border-radius: 50%;
    border: 1px solid var(--m-border); display: block;
}

.m-comment-main { flex: 1; min-width: 0; }
.m-comment-head {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 6px;
}
.m-comment-name {
    display: inline-flex; align-items: center;
    font-size: var(--m-text-base); font-weight: 600;
    color: var(--m-text);
}
.m-comment-ip { color: var(--m-text-faint); font-weight: 400; font-size: var(--m-text-sm); margin-left: 4px; }
.m-comment-time { font-size: var(--m-text-xs); color: var(--m-text-faint); }

.m-comment-content {
    font-size: var(--m-text-md); line-height: var(--m-leading-relaxed);
    color: var(--m-text); word-break: break-word;
}
.m-comment-content a { color: var(--m-primary); }

.m-comment-secret {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 7px; border-radius: 999px;
    background: rgba(245,158,11,0.15); color: #d97706;
    font-size: var(--m-text-xs); font-weight: 600;
    margin-right: 6px; vertical-align: middle;
}

.m-comment-form-slot:empty { display: none; }
.m-comment-form-slot { display: block; margin-top: 12px; }

.m-comment-actions {
    display: flex; gap: 4px; margin-top: 8px;
}
.m-comment-action {
    padding: 4px 10px; border-radius: var(--m-radius-sm);
    font-size: var(--m-text-xs); color: var(--m-text-muted);
    text-decoration: none;
    transition: background 0.15s, color 0.15s;
}
.m-comment-action:hover { background: var(--m-surface-2); color: var(--m-text); }
.m-comment-action-danger:hover { background: rgba(239,68,68,0.1); color: #ef4444; }

/* ──────────────────────────────────────────────
   댓글 작성 폼
   ────────────────────────────────────────────── */
.m-comment-write {
    margin: 18px 14px 14px;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-lg);
    padding: 24px 28px 26px;
}
.m-comment-write-title {
    font-size: var(--m-text-base); font-weight: 600;
    color: var(--m-text-soft); margin-bottom: 16px;
}
.m-comment-textarea-wrap { position: relative; }
.m-comment-textarea {
    min-height: 96px; resize: vertical;
    padding: 12px 14px; line-height: var(--m-leading-relaxed);
    font-family: inherit;
}
.m-comment-charcount {
    position: absolute; bottom: 8px; right: 12px;
    font-size: var(--m-text-xs); color: var(--m-text-faint);
    pointer-events: none;
}

.m-comment-write-row {
    display: flex; flex-wrap: wrap; gap: 10px;
    align-items: center; justify-content: space-between;
    margin-top: 14px;
}
.m-comment-write-info { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.m-comment-write-info .m-input { padding: 8px 10px; font-size: var(--m-text-sm); }
.m-comment-write-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

.m-comment-captcha { margin-top: 18px; padding-top: 16px; border-top: 1px dashed var(--m-border); }

/* 댓글 수정/답글 form 이 슬롯에 들어갔을 때 자연스럽게 보이도록 */
.m-comment .m-comment-write { margin-top: 12px; }
</style>

<script>
// 댓글 토글 버튼
jQuery(function($) {
    $(".cmt_btn").click(function(e) {
        e.preventDefault();
        $(this).toggleClass("is-collapsed");
        $("#bo_vc .m-comments-body").slideToggle(150);
    });
});
</script>
