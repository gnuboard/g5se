<?php
/*
 * /admin/visit.sub.php — 방문자 통계 페이지 공통 sub (필터 폼 + 탭 nav).
 * modern shell: 부모 visit_*.php 가 admin_layout_start 까지 emit 한 후
 * 이 파일을 include 하면 됨. 자체적으로 admin shell wrap 하지 않음.
 */
if (!defined('_GNUBOARD_')) exit;

require_once G5_LIB_PATH.'/visit.lib.php';

if (empty($fr_date) || ! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = G5_TIME_YMD;
if (empty($to_date) || ! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = G5_TIME_YMD;

$qstr = "fr_date=".$fr_date."&amp;to_date=".$to_date;
$query_string = $qstr ? '?'.$qstr : '';
?>

<!-- jQuery UI datepicker 자산 / 한글 locale 은 _layout.php 에서 한 번만 로드. -->
<form name="fvisit" id="fvisit" class="local_sch03 local_sch" method="get">
<div class="sch_last">
    <strong>기간별검색</strong>
    <input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="frm_input" size="11" maxlength="10">
    <label for="fr_date" class="sound_only">시작일</label>
    ~
    <input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="frm_input" size="11" maxlength="10">
    <label for="to_date" class="sound_only">종료일</label>
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<ul class="anchor">
    <li><a href="<?php echo G5_ADMIN_URL ?>/visit_list<?php echo $query_string ?>">접속자</a></li>
    <li><a href="<?php echo G5_ADMIN_URL ?>/visit_domain<?php echo $query_string ?>">도메인</a></li>
    <li><a href="<?php echo G5_ADMIN_URL ?>/visit_browser<?php echo $query_string ?>">브라우저</a></li>
    <li><a href="<?php echo G5_ADMIN_URL ?>/visit_os<?php echo $query_string ?>">운영체제</a></li>
    <?php if(defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE) { ?>
    <li><a href="<?php echo G5_ADMIN_URL ?>/visit_device<?php echo $query_string ?>">접속기기</a></li>
    <?php } ?>
    <li><a href="<?php echo G5_ADMIN_URL ?>/visit_hour<?php echo $query_string ?>">시간</a></li>
    <li><a href="<?php echo G5_ADMIN_URL ?>/visit_week<?php echo $query_string ?>">요일</a></li>
    <li><a href="<?php echo G5_ADMIN_URL ?>/visit_date<?php echo $query_string ?>">일</a></li>
    <li><a href="<?php echo G5_ADMIN_URL ?>/visit_month<?php echo $query_string ?>">월</a></li>
    <li><a href="<?php echo G5_ADMIN_URL ?>/visit_year<?php echo $query_string ?>">년</a></li>
</ul>

<script>
$(function(){
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
});

function fvisit_submit(act)
{
    var f = document.fvisit;
    f.action = act;
    f.submit();
}
</script>
