<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$get_max_cnt = 0;

if ((int) $total_po_cnt > 0){
    foreach( $list as $k => $v ) {
        $get_max_cnt = max( array( $get_max_cnt, $v['cnt'] ) );     // 가장 높은 투표수를 뽑습니다.
    }
}

// g5se: modern 토큰 (var(--m-*)) 로드 — popup 창 단독 페이지 (.m-popup 컴포넌트 + 다크모드)
if(defined('G5_THEME_PATH') && is_file(G5_THEME_PATH.'/modern/_head.inc.php')) {
    require_once(G5_THEME_PATH.'/modern/_head.inc.php');
}
?>
<style>
/* g5se: 설문조사 결과 popup — modern token 기반 + 다크모드 */
#poll_result {
    background: var(--m-bg);
    color: var(--m-text);
    padding: 20px 18px;
    min-height: 100vh;
    box-sizing: border-box;
    max-width: 720px;
    margin: 0 auto;
}
.m-poll-result-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--m-border);
}
.m-poll-result-title {
    display: flex; align-items: center; gap: 8px;
    font-size: var(--m-text-lg); font-weight: 700;
    color: var(--m-text); margin: 0;
}
.m-poll-result-title svg { color: var(--m-primary); }
.m-poll-total-tag {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px;
    background: var(--m-primary-soft);
    color: var(--m-primary);
    border-radius: 999px;
    font-size: var(--m-text-sm); font-weight: 600;
    flex-shrink: 0;
}

/* 결과 그래프 카드 */
#poll_result_list {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    padding: 18px 20px;
    margin: 0 0 16px;
}
#poll_result_list h2 {
    font-size: var(--m-text-md); font-weight: 700;
    color: var(--m-text);
    margin: 0 0 14px;
    padding: 0;
    background: transparent;
    border: 0;
    text-align: left;
    border-radius: 0;
}
#poll_result_list ol {
    list-style: none;
    margin: 0; padding: 0;
    display: flex; flex-direction: column;
    gap: 14px;
}
#poll_result_list li {
    margin: 0;
    list-style: none;
    position: relative;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 4px 12px;
    align-items: center;
}
#poll_result_list li > span:first-child {
    font-size: var(--m-text-sm);
    color: var(--m-text);
    font-weight: 500;
    grid-column: 1;
    grid-row: 1;
}
.poll_numerical {
    position: static !important;
    grid-column: 2;
    grid-row: 1;
    text-align: right;
    display: flex; align-items: baseline; gap: 8px;
}
.poll_numerical .poll_cnt {
    font-size: var(--m-text-xs);
    color: var(--m-text-muted);
    font-weight: 500;
    text-align: right;
}
.poll_numerical .poll_percent {
    font-size: var(--m-text-md);
    color: var(--m-text-soft);
    font-weight: 700;
    letter-spacing: -0.01em;
    font-feature-settings: "tnum";
}
.poll_result_graph {
    grid-column: 1 / -1;
    grid-row: 2;
    position: relative;
    width: 100%;
    height: 8px;
    background: var(--m-surface-2);
    border-radius: 999px;
    overflow: hidden;
    box-shadow: none;
    margin: 0;
}
.poll_result_graph span {
    position: absolute; top: 0; left: 0;
    height: 100%;
    background: var(--m-text-faint);
    border-radius: 999px;
    transition: width 0.4s ease;
}
/* 1위 강조 */
.poll_1st .poll_result_graph span { background: var(--m-primary); }
.poll_1st .poll_percent { color: var(--m-primary); }
.poll_1st > span:first-child { font-weight: 700; }

/* 기타의견 카드 */
#poll_result_cmt {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    padding: 18px 20px;
    margin: 0 0 16px;
}
#poll_result_cmt > h2 {
    position: static !important;
    font-size: var(--m-text-md); font-weight: 700;
    color: var(--m-text);
    margin: 0 0 14px;
    line-height: 1.4;
    overflow: visible;
    text-indent: 0;
}
#poll_result_cmt article {
    margin: 0 0 12px;
    padding: 12px 14px;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-sm);
    position: relative;
}
#poll_result_cmt article header {
    display: flex; align-items: center; gap: 8px;
    flex-wrap: wrap;
    margin: 0 0 6px;
    font-size: var(--m-text-xs);
    color: var(--m-text-soft);
}
#poll_result_cmt article header h2 {
    position: absolute; left: -9999px;
}
#poll_result_cmt .poll_datetime {
    color: var(--m-text-faint);
    font-size: var(--m-text-xs);
}
#poll_result_cmt .poll_datetime i { margin-right: 3px; }
.poll_cmt_del { margin-left: auto; }
.poll_cmt_del a {
    color: var(--m-text-faint);
    font-size: 13px;
    margin: 0;
    padding: 4px;
    transition: color 0.15s;
}
.poll_cmt_del a:hover { color: #ef4444; }
#poll_result_cmt p {
    margin: 0;
    padding: 0;
    line-height: 1.5;
    color: var(--m-text);
    font-size: var(--m-text-sm);
}

/* 의견 입력 폼 */
#poll_other_q {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid var(--m-border);
}
#poll_result_wcmt {
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-sm);
    padding: 12px 14px;
    margin-bottom: 10px;
}
#poll_result_wcmt h3 {
    font-size: var(--m-text-sm);
    color: var(--m-text);
    font-weight: 600;
    margin: 0 0 8px;
    padding: 0;
    border: 0;
    display: flex; align-items: center; gap: 6px;
    background: transparent;
}
#poll_result_wcmt h3 span {
    background: var(--m-primary-soft);
    color: var(--m-primary);
    padding: 2px 8px;
    border-radius: 999px;
    font-size: var(--m-text-xs);
    font-weight: 600;
    margin-right: 0;
    display: inline-block;
}
#poll_result_wcmt input[type="text"],
#poll_result_wcmt .full_input {
    width: 100%;
    height: auto;
    padding: 8px 12px !important;
    background: var(--m-surface) !important;
    border: 1px solid var(--m-border) !important;
    border-radius: var(--m-radius-sm) !important;
    color: var(--m-text) !important;
    font-size: var(--m-text-sm);
    box-sizing: border-box;
}
#poll_result_wcmt input:focus,
.poll_guest input:focus { outline: 2px solid var(--m-primary); outline-offset: 1px; }
.poll_guest {
    display: flex; gap: 8px; align-items: center;
    margin-bottom: 10px;
    flex-wrap: wrap;
}
.poll_guest input {
    padding: 8px 12px;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-sm);
    color: var(--m-text);
    font-size: var(--m-text-sm);
}
#poll_other_q .btn_submit {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 8px 18px;
    background: var(--m-primary);
    color: #fff !important;
    border: 1px solid var(--m-primary);
    border-radius: var(--m-radius-sm);
    font-size: var(--m-text-sm);
    font-weight: 600;
    cursor: pointer;
    margin-top: 4px;
}
#poll_other_q .btn_submit:hover { background: var(--m-primary-hover); }

/* 다른 투표 list */
#poll_result_oth {
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    padding: 18px 20px;
    margin: 0 0 16px;
}
#poll_result_oth h2 {
    font-size: var(--m-text-md); font-weight: 700;
    color: var(--m-text);
    margin: 0 0 12px;
    padding: 0;
}
#poll_result_oth ul {
    list-style: none;
    margin: 0; padding: 0;
    border: 0;
    background: transparent;
    display: flex; flex-direction: column;
    gap: 4px;
}
#poll_result_oth li {
    border: 0;
    border-top: 1px solid var(--m-border);
    line-height: 1.4;
    position: relative;
}
#poll_result_oth li:first-child { border-top: 0; }
#poll_result_oth a {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px;
    padding: 10px 4px;
    color: var(--m-text);
    text-decoration: none;
    font-size: var(--m-text-sm);
    transition: color 0.15s;
}
#poll_result_oth a:hover { color: var(--m-primary); }
#poll_result_oth li span {
    position: static !important;
    color: var(--m-text-faint);
    font-size: var(--m-text-xs);
    flex-shrink: 0;
    bottom: auto !important;
    right: auto !important;
}

/* 창닫기 버튼 */
.win_btn {
    display: flex; justify-content: flex-end;
    margin-top: 8px;
}
.win_btn .btn_close {
    display: inline-flex; align-items: center;
    padding: 9px 20px;
    background: var(--m-surface-2);
    color: var(--m-text);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius-sm);
    font-size: var(--m-text-sm);
    font-weight: 600;
    cursor: pointer;
}
.win_btn .btn_close:hover {
    background: var(--m-primary);
    color: #fff;
    border-color: var(--m-primary);
}

@media (max-width: 480px) {
    #poll_result { padding: 14px 12px; }
    #poll_result_list, #poll_result_cmt, #poll_result_oth { padding: 14px 14px; }
    .poll_numerical { gap: 6px; }
    .poll_numerical .poll_percent { font-size: var(--m-text-sm); }
}
</style>

<!-- 설문조사 결과 시작 { -->
<div id="poll_result" class="new_win">
    <header class="m-poll-result-head">
        <h1 class="m-poll-result-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><rect x="7" y="13" width="3" height="5"/><rect x="12" y="9" width="3" height="9"/><rect x="17" y="5" width="3" height="13"/></svg>
            설문조사 결과
        </h1>
        <span class="m-poll-total-tag">전체 <?php echo $nf_total_po_cnt ?> 표</span>
    </header>

    <!-- 설문조사 결과 그래프 시작 { -->
    <section id="poll_result_list">
        <h2><?php echo $po_subject ?></h2>
        <ol>
            <?php
            for ($i=1; $i<=count($list); $i++) {
                // 가장 높은 투표수와 같으면 li 태그에 poll_1st 클래스가 붙습니다.
                $poll_1st_class = ($get_max_cnt && ((int) $list[$i]['cnt'] === (int) $get_max_cnt)) ? 'poll_1st' : '';
            ?>
            <li class="<?php echo $poll_1st_class; ?>">
                <span><?php echo $list[$i]['content'] ?></span>
                <div class="poll_numerical">
                    <strong class="poll_cnt"><?php echo $list[$i]['cnt'] ?> 표</strong>
                    <span class="poll_percent"><?php echo number_format($list[$i]['rate'], 1) ?>%</span>
                </div>
                <div class="poll_result_graph">
                    <span style="width:<?php echo number_format($list[$i]['rate'], 1) ?>%"></span>
                </div>
            </li>
            <?php }  ?>
        </ol>
    </section>
    <!-- } 설문조사 결과 그래프 끝 -->

    <!-- 설문조사 기타의견 시작 { -->
    <?php if ($is_etc) {  ?>
    <section id="poll_result_cmt">
        <h2>이 설문에 대한 기타의견</h2>

        <?php for ($i=0; $i<count($list2); $i++) {  ?>
        <article>
            <header>
                <h2><?php echo $list2[$i]['pc_name'] ?><span class="sound_only">님의 의견</span></h2>
                <?php echo $list2[$i]['name'] ?>
                <span class="poll_datetime"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo $list2[$i]['datetime'] ?></span>
                <span class="poll_cmt_del"><?php if ($list2[$i]['del']) { echo $list2[$i]['del']."<i class=\"fa fa-trash-o\" aria-hidden=\"true\"></i><span class=\"sound_only\">삭제</span></a>"; }  ?></span>
            </header>
            <p>
                <?php echo $list2[$i]['idea'] ?>
            </p>
        </article>
        <?php }  ?>

        <?php if ($member['mb_level'] >= $po['po_level']) {  ?>
        <form name="fpollresult" action="/poll_etc_update" onsubmit="return fpollresult_submit(this);" method="post" autocomplete="off" id="poll_other_q">
        <input type="hidden" name="po_id" value="<?php echo $po_id ?>">
        <input type="hidden" name="w" value="">
        <input type="hidden" name="skin_dir" value="<?php echo urlencode($skin_dir); ?>">
        <?php if ($is_member) {  ?><input type="hidden" name="pc_name" value="<?php echo get_text(cut_str($member['mb_nick'],255)) ?>"><?php }  ?>
        <div id="poll_result_wcmt">
        	<h3><span>기타의견</span><?php echo $po_etc ?></h3>
            <div>
                <label for="pc_idea" class="sound_only">의견<strong>필수</strong></label>
                <input type="text" id="pc_idea" name="pc_idea" required class="full_input required" size="47" maxlength="100" placeholder="의견을 입력해주세요">
            </div>
        </div>
        <?php if ($is_guest) {  ?>
        <div class="poll_guest">
            <label for="pc_name" class="sound_only">이름<strong>필수</strong></label>
            <input type="text" name="pc_name" id="pc_name" required class="full_input required" size="20" placeholder="이름">
        </div>
        <?php echo captcha_html(); ?>
    	<?php } ?>
		<button type="submit" class="btn_submit">의견남기기</button>
        </form>
        <?php }  ?>

    </section>
    <?php }  ?>
    <!-- } 설문조사 기타의견 끝 -->

    <!-- 설문조사 다른 결과 보기 시작 { -->
    <aside id="poll_result_oth">
        <h2>다른 투표 결과 보기</h2>
        <ul>
            <?php for ($i=0; $i<count($list3); $i++) {  ?>
            <li><a href="/poll_result?po_id=<?php echo $list3[$i]['po_id'] ?>&amp;skin_dir=<?php echo urlencode($skin_dir); ?>"> <?php echo $list3[$i]['subject'] ?> </a><span><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo $list3[$i]['date'] ?></span></li>
            <?php }  ?>
        </ul>
    </aside>
    <!-- } 설문조사 다른 결과 보기 끝 -->

    <div class="win_btn">
        <button type="button" onclick="window.close();" class="btn_close">창닫기</button>
    </div>
</div>

<script>
$(function() {
    $(".poll_delete").click(function() {
        if(!confirm("해당 기타의견을 삭제하시겠습니까?"))
            return false;
    });
});

function fpollresult_submit(f)
{
    <?php if ($is_guest) { echo chk_captcha_js(); }  ?>

    return true;
}
</script>
<!-- } 설문조사 결과 끝 -->
