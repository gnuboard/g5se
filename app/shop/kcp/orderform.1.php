<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// kcp 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use'] || $default['de_easy_pay_use']) {
?>
<script type="text/javascript">
/****************************************************************/
/* m_Completepayment  설명                                      */
/****************************************************************/
/* 인증완료시 재귀 함수                                         */
/* 해당 함수명은 절대 변경하면 안됩니다.                        */
/* 해당 함수의 위치는 payplus.js 보다먼저 선언되어여 합니다.    */
/* Web 방식의 경우 리턴 값이 form 으로 넘어옴                   */
/* EXE 방식의 경우 리턴 값이 json 으로 넘어옴                   */
/****************************************************************/
function m_Completepayment( FormOrJson, closeEvent )
{
    var frm = document.forderform;

    /********************************************************************/
    /* FormOrJson은 가맹점 임의 활용 금지                               */
    /* frm 값에 FormOrJson 값이 설정 됨 frm 값으로 활용 하셔야 됩니다.  */
    /* FormOrJson 값을 활용 하시려면 기술지원팀으로 문의바랍니다.       */
    /********************************************************************/
    GetField( frm, FormOrJson );

    $("body").css({
        "position": "",
        "width": "",
        "top" : ""
    });

    if( frm.res_cd.value == "0000" )
    {
        document.getElementById("display_pay_button").style.display = "none" ;
        document.getElementById("display_pay_process").style.display = "" ;

        frm.submit();
    }
    else
    {
        alert( "[" + frm.res_cd.value + "] " + frm.res_msg.value );

        closeEvent();
    }
}
</script>

<script src="<?php echo $g_conf_js_url; ?>"></script>
<script>
/* Payplus Plug-in 실행 */
function jsf__pay( form )
{
    console.log('[gnu5se] jsf__pay entered. KCP_Pay_Execute typeof:', typeof KCP_Pay_Execute);

    // gnu5se 진단 — KCP 가 DOM 에 무엇을 inject 하는지 추적
    var __kcpObserver = new MutationObserver(function(muts) {
        muts.forEach(function(m) {
            m.addedNodes.forEach(function(n) {
                if (n.nodeType === 1) {
                    console.log('[gnu5se] DOM added:', n.tagName, n.id ? '#'+n.id : '', n.className ? '.'+n.className : '', 'parent:', n.parentNode && n.parentNode.tagName);
                }
            });
        });
    });
    __kcpObserver.observe(document.body, { childList: true, subtree: true });
    setTimeout(function(){ __kcpObserver.disconnect(); }, 5000);

    try
    {
        if (typeof KCP_Pay_Execute === 'undefined') {
            console.error('[gnu5se] KCP_Pay_Execute is undefined — payplus_web.jsp 가 로드 안 됨 (cross-origin document.write 차단 등). 페이지 새로고침 또는 결제대행 스크립트 로드 확인.');
            alert('결제 모듈 로드에 실패했습니다. 페이지를 새로고침 후 다시 시도해 주세요.');
            return;
        }
        console.log('[gnu5se] calling KCP_Pay_Execute...');
        KCP_Pay_Execute( form );
        console.log('[gnu5se] KCP_Pay_Execute returned (popup should now be open)');
        // body position:fixed 가 KCP modal 에 영향 줄 수 있음 — 일단 주석 처리하고 동작 확인
        // $("body").css({"position":"fixed","width":"100%","top":"0"});
    }
    catch (e)
    {
        console.warn('[gnu5se] jsf__pay caught exception (might be normal IE flow):', e);
        if (e && e.message && e.message.indexOf('정상종료') === -1) {
            console.error('[gnu5se] KCP_Pay_Execute 실패:', e);
        }
    }
    console.log('[gnu5se] jsf__pay exiting');
}
</script>
<?php }