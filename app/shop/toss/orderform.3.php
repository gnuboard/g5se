<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<div id="display_pay_button" class="btn_confirm" style="display:none">
    <?php if($default['de_card_test']) { ?>
    <div class="pay_test_notice">안내: 테스트 결제로 설정되어 있어 실제 결제가 이루어지지 않습니다.</div>
    <?php } ?>
    <input type="button" value="주문하기" class="btn_submit" onclick="forderform_check(this.form);"/>
    <a href="javascript:history.go(-1);" class="btn01">취소</a>
</div>
<div id="display_pay_process" style="display:none">
    <img src="<?php echo G5_URL; ?>/shop/img/loading.gif" alt="">
    <span>주문완료 중입니다. 잠시만 기다려 주십시오.</span>
</div>

<script>
document.getElementById("display_pay_button").style.display = "" ;
</script>
