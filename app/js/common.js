// 전역 변수
var errmsg = "";
var errfld = null;

// 필드 검사
function check_field(fld, msg)
{
    if ((fld.value = trim(fld.value)) == "")
        error_field(fld, msg);
    else
        clear_field(fld);
    return;
}

// 필드 오류 표시
function error_field(fld, msg)
{
    if (msg != "")
        errmsg += msg + "\n";
    if (!errfld) errfld = fld;
    fld.style.background = "#BDDEF7";
}

// 필드를 깨끗하게
function clear_field(fld)
{
    fld.style.background = "#FFFFFF";
}

function trim(s)
{
    var t = "";
    var from_pos = to_pos = 0;

    for (i=0; i<s.length; i++)
    {
        if (s.charAt(i) == ' ')
            continue;
        else
        {
            from_pos = i;
            break;
        }
    }

    for (i=s.length; i>=0; i--)
    {
        if (s.charAt(i-1) == ' ')
            continue;
        else
        {
            to_pos = i;
            break;
        }
    }

    t = s.substring(from_pos, to_pos);
    //				alert(from_pos + ',' + to_pos + ',' + t+'.');
    return t;
}

// 자바스크립트로 PHP의 number_format 흉내를 냄
// 숫자에 , 를 출력
function number_format(data)
{

    var tmp = '';
    var number = '';
    var cutlen = 3;
    var comma = ',';
    var i;
    
    data = data + '';

    var sign = data.match(/^[\+\-]/);
    if(sign) {
        data = data.replace(/^[\+\-]/, "");
    }

    len = data.length;
    mod = (len % cutlen);
    k = cutlen - mod;
    for (i=0; i<data.length; i++)
    {
        number = number + data.charAt(i);

        if (i < data.length - 1)
        {
            k++;
            if ((k % cutlen) == 0)
            {
                number = number + comma;
                k = 0;
            }
        }
    }

    if(sign != null)
        number = sign+number;

    return number;
}

// 내부 팝업 레이어
(function(win, doc) {
    var popup = null;
    var popupFrame = null;
    var popupTitle = null;
    var popupBody = null;
    var popupLastFocus = null;

    function is_internal_url(url)
    {
        var link = doc.createElement("a");
        link.href = url;

        return link.protocol === win.location.protocol && link.host === win.location.host;
    }

    function add_layer_param(url)
    {
        var link = doc.createElement("a");
        link.href = url;

        if (!is_internal_url(link.href))
            return url;

        if (/[?&]g5_layer=1(?:&|$)/.test(link.search))
            return link.href;

        link.search += (link.search ? "&" : "?") + "g5_layer=1";
        return link.href;
    }

    function get_popup_size(opt)
    {
        var width = 720;
        var height = 640;
        var match;

        if (opt) {
            match = String(opt).match(/(?:^|[, ])width\s*=\s*([0-9]+)/i);
            if (match)
                width = parseInt(match[1], 10);

            match = String(opt).match(/(?:^|[, ])height\s*=\s*([0-9]+)/i);
            if (match)
                height = parseInt(match[1], 10);
        }

        return { width: width, height: height };
    }

    function get_popup_title(winname)
    {
        var titles = {
            win_point: "포인트",
            win_memo: "쪽지",
            win_email: "메일 보내기",
            win_profile: "자기소개",
            win_scrap: "스크랩",
            win_password_lost: "비밀번호 찾기",
            win_poll: "설문 결과",
            win_coupon: "쿠폰",
            wformmail: "메일 보내기",
            largeimage: "이미지 보기",
            itemrecommend: "상품 추천",
            itemstocksms: "재입고 알림",
            win_target: "상품검색",
            win_member: "회원검색",
            win_address: "배송지 목록"
        };

        return titles[winname] || "팝업";
    }

    function is_escape_key(event)
    {
        event = event || win.event;
        return event && (event.key === "Escape" || event.keyCode === 27);
    }

    function ensure_popup()
    {
        var close_btn;

        if (popup)
            return;

        popup = doc.createElement("div");
        popup.className = "g5-popup-layer";
        popup.setAttribute("hidden", "hidden");
        popup.innerHTML = '' +
            '<div class="g5-popup-layer__backdrop" data-g5-popup-close></div>' +
            '<section class="g5-popup-layer__dialog" role="dialog" aria-modal="true" aria-labelledby="g5-popup-layer-title">' +
                '<header class="g5-popup-layer__head">' +
                    '<div class="g5-popup-layer__title-wrap">' +
                        '<span class="g5-popup-layer__mark" aria-hidden="true"></span>' +
                        '<h2 id="g5-popup-layer-title" class="g5-popup-layer__title"></h2>' +
                    '</div>' +
                    '<button type="button" class="g5-popup-layer__close" data-g5-popup-close aria-label="닫기">' +
                        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' +
                    '</button>' +
                '</header>' +
                '<div class="g5-popup-layer__body"></div>' +
            '</section>';

        doc.body.appendChild(popup);

        popupTitle = doc.getElementById("g5-popup-layer-title");
        popupBody = popup.querySelector(".g5-popup-layer__body");
        close_btn = popup.querySelector(".g5-popup-layer__close");

        popup.addEventListener("click", function(event) {
            if (event.target && event.target.closest && event.target.closest("[data-g5-popup-close]"))
                win.G5PopupLayer.close();
        });

        doc.addEventListener("keydown", function(event) {
            if (!popup.hasAttribute("hidden") && is_escape_key(event))
                win.G5PopupLayer.close();
        });

        if (close_btn)
            close_btn.focus();
    }

    function create_popup_frame(winname)
    {
        popupBody.innerHTML = "";
        popupFrame = doc.createElement("iframe");
        popupFrame.className = "g5-popup-layer__frame";
        popupFrame.title = "팝업 내용";
        popupFrame.name = winname || "g5_popup_layer";

        popupFrame.addEventListener("load", function() {
            try {
                var frame_win = popupFrame.contentWindow;
                var frame_doc = frame_win.document;
                frame_win.close = function() {
                    win.G5PopupLayer.close();
                };
                frame_win.G5PopupLayer = win.G5PopupLayer;
                if (frame_doc) {
                    frame_doc.addEventListener("keydown", function(event) {
                        if (is_escape_key(event))
                            win.G5PopupLayer.close();
                    });
                }
            } catch (e) {
                return;
            }
        });

        popupBody.appendChild(popupFrame);
        return popupFrame;
    }

    function inject_popup_style()
    {
        var style;

        if (doc.getElementById("g5-popup-layer-style"))
            return;

        style = doc.createElement("style");
        style.id = "g5-popup-layer-style";
        style.type = "text/css";
        style.appendChild(doc.createTextNode(
            ".g5-popup-layer{position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;padding:18px;box-sizing:border-box}" +
            ".g5-popup-layer[hidden]{display:none}" +
            ".g5-popup-layer__backdrop{position:absolute;inset:0;background:rgba(15,23,42,.58);backdrop-filter:blur(2px)}" +
            ".g5-popup-layer__dialog{position:relative;z-index:1;display:flex;flex-direction:column;width:min(var(--g5-popup-width,720px),calc(100vw - 24px));height:min(var(--g5-popup-height,640px),calc(100vh - 24px));background:var(--m-bg,#f8fafc);border:1px solid var(--m-border,#e2e8f0);border-radius:14px;box-shadow:0 24px 70px rgba(15,23,42,.32);overflow:hidden}" +
            ".g5-popup-layer__head{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:50px;padding:0 10px 0 18px;border-bottom:1px solid var(--m-border,#e2e8f0);background:color-mix(in srgb,var(--m-surface,#fff) 92%,var(--m-primary,#2563eb) 8%);color:var(--m-text,#0f172a)}" +
            ".g5-popup-layer__title-wrap{display:flex;align-items:center;gap:10px;min-width:0}" +
            ".g5-popup-layer__mark{width:4px;height:24px;border-radius:999px;background:var(--m-primary,#2563eb);box-shadow:0 0 0 3px var(--m-primary-soft,rgba(37,99,235,.12));flex:0 0 auto}" +
            ".g5-popup-layer__title{margin:0;min-width:0;font-size:14px;font-weight:750;color:var(--m-text,#0f172a);line-height:1.3;letter-spacing:0}" +
            ".g5-popup-layer__close{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;padding:0;border:1px solid var(--m-border,#e2e8f0);border-radius:var(--m-radius,8px);background:transparent;color:var(--m-text-soft,#475569);cursor:pointer}" +
            ".g5-popup-layer__close svg{display:block;pointer-events:none}" +
            ".g5-popup-layer__close:hover{background:var(--m-surface-2,var(--m-surface,#fff));color:var(--m-text,#0f172a)}" +
            ".g5-popup-layer__body{display:flex;flex:1;min-height:0}" +
            ".g5-popup-layer__frame{display:block;flex:1;width:100%;min-height:0;border:0;background:var(--m-bg,#f8fafc)}" +
            "[data-theme=dark] .g5-popup-layer__dialog{background:var(--m-bg,#0a0e1a);border-color:var(--m-border,#2a3344)}" +
            "[data-theme=dark] .g5-popup-layer__head{background:color-mix(in srgb,var(--m-surface,#131825) 92%,var(--m-primary,#3b82f6) 8%);border-bottom-color:var(--m-border,#2a3344);color:var(--m-text,#f1f5f9)}" +
            "[data-theme=dark] .g5-popup-layer__title{color:var(--m-text,#f1f5f9)}" +
            "[data-theme=dark] .g5-popup-layer__close{border-color:var(--m-border,#2a3344);color:var(--m-text-soft,#cbd5e1)}" +
            "[data-theme=dark] .g5-popup-layer__close:hover{background:var(--m-surface-2,#1c2230);border-color:var(--m-border-hover,#3d4a5e);color:var(--m-text,#f1f5f9)}" +
            "[data-theme=dark] .g5-popup-layer__frame{background:var(--m-bg,#0a0e1a)}" +
            "body.g5-popup-layer-open{overflow:hidden}" +
            "@media (max-width:640px){.g5-popup-layer{padding:0}.g5-popup-layer__dialog{width:100vw;height:100vh;border:0;border-radius:0}.g5-popup-layer__head{min-height:48px}}"
        ));
        doc.head.appendChild(style);
    }

    win.G5PopupLayer = {
        isInternal: is_internal_url,
        open: function(url, winname, opt, title) {
            var size;

            if (!doc.body || !is_internal_url(url))
                return null;

            ensure_popup();
            inject_popup_style();

            popupLastFocus = doc.activeElement;
            size = get_popup_size(opt);

            popupTitle.innerHTML = title || get_popup_title(winname);
            popup.querySelector(".g5-popup-layer__dialog").style.setProperty("--g5-popup-width", size.width + "px");
            popup.querySelector(".g5-popup-layer__dialog").style.setProperty("--g5-popup-height", size.height + "px");
            popupFrame = create_popup_frame(winname);
            popupFrame.src = add_layer_param(url);
            popup.removeAttribute("hidden");
            if (doc.body.className.indexOf("g5-popup-layer-open") === -1)
                doc.body.className += (doc.body.className ? " " : "") + "g5-popup-layer-open";

            setTimeout(function() {
                try { popupFrame.focus(); } catch (e) {}
            }, 0);

            return {
                focus: function() {
                    try { popupFrame.focus(); } catch (e) {}
                },
                close: win.G5PopupLayer.close
            };
        },
        close: function() {
            if (!popup)
                return;

            popup.setAttribute("hidden", "hidden");
            if (popupBody)
                popupBody.innerHTML = "";
            popupFrame = null;
            doc.body.className = doc.body.className.replace(/(?:^|\s)g5-popup-layer-open(?!\S)/g, "");

            if (popupLastFocus && popupLastFocus.focus) {
                try { popupLastFocus.focus(); } catch (e) {}
            }
        }
    };
})(window, document);

// 새 창
function popup_window(url, winname, opt, title)
{
    var layer = null;

    if (window.G5PopupLayer) {
        try {
            layer = window.G5PopupLayer.open(url, winname, opt, title);
        } catch (e) {
            layer = null;
        }

        if (layer)
            return layer;
    }

    if (window.G5PopupLayer && window.G5PopupLayer.isInternal && window.G5PopupLayer.isInternal(url)) {
        window.location.href = url;
        return { focus: function() {}, close: function() {} };
    }

    return window.open(url, winname, opt);
}


// 폼메일 창
function popup_formmail(url)
{
    opt = 'scrollbars=yes,width=417,height=385,top=10,left=20';
    popup_window(url, "wformmail", opt);
}

// , 를 없앤다.
function no_comma(data)
{
    var tmp = '';
    var comma = ',';
    var i;

    for (i=0; i<data.length; i++)
    {
        if (data.charAt(i) != comma)
            tmp += data.charAt(i);
    }
    return tmp;
}

// 삭제 검사 확인
function del(href)
{
    if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
        window.location.href = href;
    }
}

// 쿠키 입력
function set_cookie(name, value, expirehours, domain)
{
    var today = new Date();
    today.setTime(today.getTime() + (60*60*1000*expirehours));
    document.cookie = name + "=" + escape( value ) + "; path=/; expires=" + today.toGMTString() + ";";
    if (domain) {
        document.cookie += "domain=" + domain + ";";
    }
}

// 쿠키 얻음
function get_cookie(name)
{
	var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
	if (match) return unescape(match[2]);
	return "";
}

// 쿠키 지움
function delete_cookie(name)
{
    var today = new Date();

    today.setTime(today.getTime() - 1);
    var value = get_cookie(name);
    if(value != "")
        document.cookie = name + "=" + value + "; path=/; expires=" + today.toGMTString();
}

var last_id = null;
function menu(id)
{
    if (id != last_id)
    {
        if (last_id != null)
            document.getElementById(last_id).style.display = "none";
        document.getElementById(id).style.display = "block";
        last_id = id;
    }
    else
    {
        document.getElementById(id).style.display = "none";
        last_id = null;
    }
}

function textarea_decrease(id, row)
{
    if (document.getElementById(id).rows - row > 0)
        document.getElementById(id).rows -= row;
}

function textarea_original(id, row)
{
    document.getElementById(id).rows = row;
}

function textarea_increase(id, row)
{
    document.getElementById(id).rows += row;
}

// 글숫자 검사
function check_byte(content, target)
{
    var i = 0;
    var cnt = 0;
    var ch = '';
    var cont = document.getElementById(content).value;

    for (i=0; i<cont.length; i++) {
        ch = cont.charAt(i);
        if (escape(ch).length > 4) {
            cnt += 2;
        } else {
            cnt += 1;
        }
    }
    // 숫자를 출력
    document.getElementById(target).innerHTML = cnt;

    return cnt;
}

// 브라우저에서 오브젝트의 왼쪽 좌표
function get_left_pos(obj)
{
    var parentObj = null;
    var clientObj = obj;
    //var left = obj.offsetLeft + document.body.clientLeft;
    var left = obj.offsetLeft;

    while((parentObj=clientObj.offsetParent) != null)
    {
        left = left + parentObj.offsetLeft;
        clientObj = parentObj;
    }

    return left;
}

// 브라우저에서 오브젝트의 상단 좌표
function get_top_pos(obj)
{
    var parentObj = null;
    var clientObj = obj;
    //var top = obj.offsetTop + document.body.clientTop;
    var top = obj.offsetTop;

    while((parentObj=clientObj.offsetParent) != null)
    {
        top = top + parentObj.offsetTop;
        clientObj = parentObj;
    }

    return top;
}

function flash_movie(src, ids, width, height, wmode)
{
    var wh = "";
    if (parseInt(width) && parseInt(height))
        wh = " width='"+width+"' height='"+height+"' ";
    return "<object classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0' "+wh+" id="+ids+"><param name=wmode value="+wmode+"><param name=movie value="+src+"><param name=quality value=high><embed src="+src+" quality=high wmode="+wmode+" type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?p1_prod_version=shockwaveflash' "+wh+"></embed></object>";
}

function obj_movie(src, ids, width, height, autostart)
{
    var wh = "";
    if (parseInt(width) && parseInt(height))
        wh = " width='"+width+"' height='"+height+"' ";
    if (!autostart) autostart = false;
    return "<embed src='"+src+"' "+wh+" autostart='"+autostart+"'></embed>";
}

function doc_write(cont)
{
    document.write(cont);
}

var win_password_lost = function(href) {
    popup_window(href, "win_password_lost", "left=50, top=50, width=617, height=430, scrollbars=1");
}

$(document).ready(function(){
    $("#login_password_lost, #ol_password_lost").click(function(){
        win_password_lost(this.href);
        return false;
    });
});

/**
 * 포인트 창
 **/
var win_point = function(href) {
    var new_win = popup_window(href, 'win_point', 'left=100,top=100,width=600, height=600, scrollbars=1');
    new_win.focus();
}

/**
 * 쪽지 창
 **/
var win_memo = function(href) {
    var new_win = popup_window(href, 'win_memo', 'left=100,top=100,width=620,height=560,scrollbars=1');
    new_win.focus();
}

/**
 * 쪽지 창
 **/
var check_goto_new = function(href, event) {
    if( !(typeof g5_is_mobile != "undefined" && g5_is_mobile) ){
        if (window.opener && window.opener.document && window.opener.document.getElementById) {
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            window.open(href);
            //window.opener.document.location.href = href;
        }
    }
}

/**
 * 메일 창
 **/
var win_email = function(href) {
    var new_win = popup_window(href, 'win_email', 'left=100,top=100,width=600,height=640,scrollbars=1');
    new_win.focus();
}

/**
 * 자기소개 창
 **/
var win_profile = function(href) {
    var new_win = popup_window(href, 'win_profile', 'left=100,top=100,width=620,height=560,scrollbars=1');
    new_win.focus();
}

/**
 * 스크랩 창
 **/
var win_scrap = function(href) {
    var new_win = popup_window(href, 'win_scrap', 'left=100,top=100,width=600,height=600,scrollbars=1');
    new_win.focus();
}

/**
 * 홈페이지 창
 **/
var win_homepage = function(href) {
    var new_win = window.open(href, 'win_homepage', '');
    new_win.focus();
}

/**
 * 우편번호 창
 **/
var win_zip = function(frm_name, frm_zip, frm_addr1, frm_addr2, frm_addr3, frm_jibeon) {
    if(typeof daum === "undefined"){
        alert("KAKAO 우편번호 서비스 postcode.v2.js 파일이 로드되지 않았습니다.");
        return false;
    }

    // 핀치 줌 현상 제거
    var vContent = "width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10";
    $("#meta_viewport").attr("content", vContent + ",user-scalable=no");

    var zip_case = 1;   //0이면 레이어, 1이면 페이지에 끼워 넣기, 2이면 새창

    var complete_fn = function(data){
        // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

        // 각 주소의 노출 규칙에 따라 주소를 조합한다.
        // 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
        var fullAddr = ''; // 최종 주소 변수
        var extraAddr = ''; // 조합형 주소 변수

        // 사용자가 선택한 주소 타입에 따라 해당 주소 값을 가져온다.
        if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
            fullAddr = data.roadAddress;

        } else { // 사용자가 지번 주소를 선택했을 경우(J)
            fullAddr = data.jibunAddress;
        }

        // 사용자가 선택한 주소가 도로명 타입일때 조합한다.
        if(data.userSelectedType === 'R'){
            //법정동명이 있을 경우 추가한다.
            if(data.bname !== ''){
                extraAddr += data.bname;
            }
            // 건물명이 있을 경우 추가한다.
            if(data.buildingName !== ''){
                extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
            }
            // 조합형주소의 유무에 따라 양쪽에 괄호를 추가하여 최종 주소를 만든다.
            extraAddr = (extraAddr !== '' ? ' ('+ extraAddr +')' : '');
        }

        // 우편번호와 주소 정보를 해당 필드에 넣고, 커서를 상세주소 필드로 이동한다.
        var of = document[frm_name];

        of[frm_zip].value = data.zonecode;

        of[frm_addr1].value = fullAddr;
        of[frm_addr3].value = extraAddr;

        if(of[frm_jibeon] !== undefined){
            of[frm_jibeon].value = data.userSelectedType;
        }
        
        setTimeout(function(){
            $("#meta_viewport").attr("content", vContent);
            of[frm_addr2].focus();
        } , 100);
    };

    switch(zip_case) {
        case 1 :    //iframe을 이용하여 페이지에 끼워 넣기
            var daum_pape_id = 'daum_juso_page'+frm_zip,
                element_wrap = document.getElementById(daum_pape_id),
                currentScroll = Math.max(document.body.scrollTop, document.documentElement.scrollTop);
            if (element_wrap == null) {
                element_wrap = document.createElement("div");
                element_wrap.setAttribute("id", daum_pape_id);
                element_wrap.style.cssText = 'display:none;border:1px solid;left:0;width:100%;height:300px;margin:5px 0;position:relative;-webkit-overflow-scrolling:touch;';
                element_wrap.innerHTML = '<img src="//t1.kakaocdn.net/postcode/resource/images/close.png" id="btnFoldWrap" style="cursor:pointer;position:absolute;right:0px;top:-21px;z-index:1" class="close_daum_juso" alt="접기 버튼">';
                jQuery('form[name="'+frm_name+'"]').find('input[name="'+frm_addr1+'"]').before(element_wrap);
                jQuery("#"+daum_pape_id).off("click", ".close_daum_juso").on("click", ".close_daum_juso", function(e){
                    e.preventDefault();
                    $("#meta_viewport").attr("content", vContent);
                    jQuery(this).parent().hide();
                });
            }

            new kakao.Postcode({
                oncomplete: function(data) {
                    complete_fn(data);
                    // iframe을 넣은 element를 안보이게 한다.
                    element_wrap.style.display = 'none';
                    // 우편번호 찾기 화면이 보이기 이전으로 scroll 위치를 되돌린다.
                    document.body.scrollTop = currentScroll;
                },
                // 우편번호 찾기 화면 크기가 조정되었을때 실행할 코드를 작성하는 부분.
                // iframe을 넣은 element의 높이값을 조정한다.
                onresize : function(size) {
                    element_wrap.style.height = size.height + "px";
                },
                maxSuggestItems : g5_is_mobile ? 6 : 10,
                width : '100%',
                height : '100%'
            }).embed(element_wrap);

            // iframe을 넣은 element를 보이게 한다.
            element_wrap.style.display = 'block';
            break;
        case 2 :    //새창으로 띄우기
            new kakao.Postcode({
                oncomplete: function(data) {
                    complete_fn(data);
                }
            }).open();
            break;
        default :   //iframe을 이용하여 레이어 띄우기
            var rayer_id = 'daum_juso_rayer'+frm_zip,
                element_layer = document.getElementById(rayer_id);
            if (element_layer == null) {
                element_layer = document.createElement("div");
                element_layer.setAttribute("id", rayer_id);
                element_layer.style.cssText = 'display:none;border:5px solid;position:fixed;width:300px;height:460px;left:50%;margin-left:-155px;top:50%;margin-top:-235px;overflow:hidden;-webkit-overflow-scrolling:touch;z-index:10000';
                element_layer.innerHTML = '<img src="//t1.kakaocdn.net/localimg/localimages/07/postcode/320/close.png" id="btnCloseLayer" style="cursor:pointer;position:absolute;right:-3px;top:-3px;z-index:1" class="close_daum_juso" alt="닫기 버튼">';
                document.body.appendChild(element_layer);
                jQuery("#"+rayer_id).off("click", ".close_daum_juso").on("click", ".close_daum_juso", function(e){
                    e.preventDefault();
                    $("#meta_viewport").attr("content", vContent);
                    jQuery(this).parent().hide();
                });
            }

            new kakao.Postcode({
                oncomplete: function(data) {
                    complete_fn(data);
                    // iframe을 넣은 element를 안보이게 한다.
                    element_layer.style.display = 'none';
                },
                maxSuggestItems : g5_is_mobile ? 6 : 10,
                width : '100%',
                height : '100%'
            }).embed(element_layer);

            // iframe을 넣은 element를 보이게 한다.
            element_layer.style.display = 'block';
    }
}

/**
 * 새로운 비밀번호 분실 창 : 101123
 **/
win_password_lost = function(href)
{
    var new_win = popup_window(href, 'win_password_lost', 'width=617, height=430, scrollbars=1');
    new_win.focus();
}

/**
 * 설문조사 결과
 **/
var win_poll = function(href) {
    var new_win = popup_window(href, 'win_poll', 'width=616, height=560, scrollbars=1');
    new_win.focus();
}

/**
 * 쿠폰
 **/
var win_coupon = function(href) {
    var new_win = popup_window(href, "win_coupon", "left=100,top=100,width=700, height=600, scrollbars=1");
    new_win.focus();
}


/**
 * 스크린리더 미사용자를 위한 스크립트 - 지운아빠 2013-04-22
 * alt 값만 갖는 그래픽 링크에 마우스오버 시 title 값 부여, 마우스아웃 시 title 값 제거
 **/
$(function() {
    $('a img').mouseover(function() {
        $a_img_title = $(this).attr('alt');
        $(this).attr('title', $a_img_title);
    }).mouseout(function() {
        $(this).attr('title', '');
    });
});

/**
 * 텍스트 리사이즈
**/
function font_resize(id, rmv_class, add_class, othis)
{
    var $el = $("#"+id);

	if((typeof rmv_class !== "undefined" && rmv_class) || (typeof add_class !== "undefined" && add_class)){
		$el.removeClass(rmv_class).addClass(add_class);

		set_cookie("ck_font_resize_rmv_class", rmv_class, 1, g5_cookie_domain);
		set_cookie("ck_font_resize_add_class", add_class, 1, g5_cookie_domain);
	}

    if(typeof othis !== "undefined"){
        $(othis).addClass('select').siblings().removeClass('select');
    }
}

/**
 * 댓글 수정 토큰
**/
function set_comment_token(f)
{
    if(typeof f.token === "undefined")
        $(f).prepend('<input type="hidden" name="token" value="">');

    $.ajax({
        url: g5_bbs_url+"/ajax.comment_token.php",
        type: "GET",
        dataType: "json",
        async: false,
        cache: false,
        success: function(data, textStatus) {
            f.token.value = data.token;
        }
    });
}

$(function(){
    $(".win_point").click(function() {
        win_point(this.href);
        return false;
    });

    $(".win_memo").click(function() {
        win_memo(this.href);
        return false;
    });

    $(".win_email").click(function() {
        win_email(this.href);
        return false;
    });

    $(".win_scrap").click(function() {
        win_scrap(this.href);
        return false;
    });

    $(".win_profile").click(function() {
        win_profile(this.href);
        return false;
    });

    $(".win_homepage").click(function() {
        win_homepage(this.href);
        return false;
    });

    $(".win_password_lost").click(function() {
        win_password_lost(this.href);
        return false;
    });

    /*
    $(".win_poll").click(function() {
        win_poll(this.href);
        return false;
    });
    */

    $(".win_coupon").click(function() {
        win_coupon(this.href);
        return false;
    });

    // 사이드뷰
    var sv_hide = false;
    $(".sv_member, .sv_guest").click(function() {
        $(".sv").removeClass("sv_on");
        $(this).closest(".sv_wrap").find(".sv").addClass("sv_on");
    });

    $(".sv, .sv_wrap").hover(
        function() {
            sv_hide = false;
        },
        function() {
            sv_hide = true;
        }
    );

    $(".sv_member, .sv_guest").focusin(function() {
        sv_hide = false;
        $(".sv").removeClass("sv_on");
        $(this).closest(".sv_wrap").find(".sv").addClass("sv_on");
    });

    $(".sv a").focusin(function() {
        sv_hide = false;
    });

    $(".sv a").focusout(function() {
        sv_hide = true;
    });

    // 셀렉트 ul
    var sel_hide = false;
    $('.sel_btn').click(function() {
        $('.sel_ul').removeClass('sel_on');
        $(this).siblings('.sel_ul').addClass('sel_on');
    });

    $(".sel_wrap").hover(
        function() {
            sel_hide = false;
        },
        function() {
            sel_hide = true;
        }
    );

    $('.sel_a').focusin(function() {
        sel_hide = false;
    });

    $('.sel_a').focusout(function() {
        sel_hide = true;
    });

    $(document).click(function() {
        if(sv_hide) { // 사이드뷰 해제
            $(".sv").removeClass("sv_on");
        }
        if (sel_hide) { // 셀렉트 ul 해제
            $('.sel_ul').removeClass('sel_on');
        }
    });

    $(document).focusin(function() {
        if(sv_hide) { // 사이드뷰 해제
            $(".sv").removeClass("sv_on");
        }
        if (sel_hide) { // 셀렉트 ul 해제
            $('.sel_ul').removeClass('sel_on');
        }
    });

    $(document).on( "keyup change", "textarea#wr_content[maxlength]", function(){
        var str = $(this).val();
        var mx = parseInt($(this).attr("maxlength"));
        if (str.length > mx) {
            $(this).val(str.substr(0, mx));
            return false;
        }
    });
});

function get_write_token(bo_table)
{
    var token = "";

    $.ajax({
        type: "POST",
        url: g5_bbs_url+"/write_token.php",
        data: { bo_table: bo_table },
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            if(data.error) {
                alert(data.error);
                if(data.url)
                    document.location.href = data.url;

                return false;
            }

            token = data.token;
        }
    });

    return token;
}

$(function() {
    $(document).on("click", "form[name=fwrite] input:submit, form[name=fwrite] button:submit, form[name=fwrite] input:image", function() {
        var f = this.form;

        if (typeof(f.bo_table) == "undefined") {
            return;
        }

        var bo_table = f.bo_table.value;
        var token = get_write_token(bo_table);

        if(!token) {
            alert("토큰 정보가 올바르지 않습니다.");
            return false;
        }

        var $f = $(f);

        if(typeof f.token === "undefined")
            $f.prepend('<input type="hidden" name="token" value="">');

        $f.find("input[name=token]").val(token);

        return true;
    });
});
