<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use'] || $default['de_easy_pay_use']) {
    add_javascript('<script language="javascript" type="text/javascript" src="'.$stdpay_js_url.'" charset="UTF-8"></script>', 10);
?>

<script language=javascript>
function make_signature(frm)
{
    // 데이터 암호화 처리
    var result = true;
    $.ajax({
        url: g5_url+"/shop/inicis/makesignature.php",
        type: "POST",
        data: {
            price : frm.good_mny.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            if(data.error == "") {
                frm.timestamp.value = data.timestamp;
                frm.signature.value = data.sign;
                frm.mKey.value = data.mKey;
            } else {
                alert(data.error);
                result = false;
            }
        }
    });

    return result;
}

function paybtn(f) {
    var initialFrames = Array.prototype.slice.call(document.getElementsByTagName("iframe"));
    var backdrop = document.getElementById("g5se-inicis-backdrop");

    if (!backdrop) {
        backdrop = document.createElement("div");
        backdrop.id = "g5se-inicis-backdrop";
        backdrop.setAttribute("aria-hidden", "true");
        backdrop.style.cssText = "position:fixed;inset:0;z-index:2147483000;background:rgba(4,10,24,.68);backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);";
        document.body.appendChild(backdrop);
    }

    INIStdPay.pay(f.id);

    // 이니시스 overlay는 화면 전체 크기의 흰 iframe을 생성한다.
    // iframe은 결제창 크기로 제한하고 바깥에는 주문서가 비치는 딤 배경을 유지한다.
    var attempts = 0;
    var payFrame = null;
    var frameTimer = window.setInterval(function() {
        attempts++;

        if (!payFrame) {
            var frames = Array.prototype.slice.call(document.getElementsByTagName("iframe")).filter(function(frame) {
                return initialFrames.indexOf(frame) === -1;
            });
            frames.sort(function(a, b) {
                var aRect = a.getBoundingClientRect();
                var bRect = b.getBoundingClientRect();
                return (bRect.width * bRect.height) - (aRect.width * aRect.height);
            });
            if (frames.length) {
                var largestRect = frames[0].getBoundingClientRect();
                if ((largestRect.width * largestRect.height) > 100000 || attempts > 10) payFrame = frames[0];
            }
        }

        if (payFrame && payFrame.isConnected) {
            payFrame.style.setProperty("position", "fixed", "important");
            payFrame.style.setProperty("left", "50%", "important");
            payFrame.style.setProperty("top", "50%", "important");
            payFrame.style.setProperty("width", "min(1180px, calc(100vw - 32px))", "important");
            payFrame.style.setProperty("height", "min(900px, calc(100vh - 32px))", "important");
            payFrame.style.setProperty("transform", "translate(-50%, -50%)", "important");
            payFrame.style.setProperty("z-index", "2147483001", "important");
            payFrame.style.setProperty("border", "0", "important");
            payFrame.style.setProperty("background", "transparent", "important");
            payFrame.style.setProperty("box-shadow", "0 24px 70px rgba(0,0,0,.38)", "important");

            var parent = payFrame.parentElement;
            while (parent && parent !== document.body) {
                parent.style.setProperty("background", "transparent", "important");
                parent = parent.parentElement;
            }
        }

        var frameHidden = false;
        var visibilityTarget = payFrame;
        while (visibilityTarget && visibilityTarget !== document.body) {
            var targetStyle = window.getComputedStyle(visibilityTarget);
            if (targetStyle.display === "none" || targetStyle.visibility === "hidden") {
                frameHidden = true;
                break;
            }
            visibilityTarget = visibilityTarget.parentElement;
        }
        var frameClosed = payFrame && (!payFrame.isConnected || (attempts > 10 && frameHidden));
        if (frameClosed || attempts > 3600) {
            window.clearInterval(frameTimer);
            if (backdrop && backdrop.parentNode) backdrop.parentNode.removeChild(backdrop);
        }
    }, 100);
}
</script>
<?php }
