/** 공통 UI 모듈 */
window.CommonUI = {
  bindTabs(tabSelector, contentSelector, options = {}) {
    const tabs = document.querySelectorAll(tabSelector);
    const contents = document.querySelectorAll(contentSelector);

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const tabName = tab.dataset.tab;
        const target = document.getElementById(`tab-${tabName}`);

        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        contents.forEach(c => c.classList.add('is-hidden'));

        if (target) target.classList.remove('is-hidden');

        options.onChange?.(tabName, target);
      });
    });
  }
};

function setHtml(el, markup) {
    if (!el) return; 
    if (markup == null || markup === '') { 
        el.textContent = '';
        return;
    }
    const range = document.createRange();
    range.selectNodeContents(el);
    el.replaceChildren(range.createContextualFragment(markup));
}

/** 팝업 관리 모듈 */
window.PopupManager = {
    open(id, options = {}) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.remove('is-hidden');
            this.bindOutsideClickClose(id);

            if (!options.disableOutsideClose) {
                this.bindOutsideClickClose(id);
            } else {
                this.unbindOutsideClickClose(id);
            }
        }
    },

    close(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('is-hidden');
    },

    toggle(id) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('is-hidden');
    },

    bindOutsideClickClose(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.onclick = () => this.close(id);
    },

    unbindOutsideClickClose(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.onclick = null;
    },

    /**
     * 팝업 콘텐츠 렌더링 (타이틀, 바디, 푸터 구성)
     * @param {string} title - 팝업 제목
     * @param {string} body - 팝업 본문 HTML
     * @param {string} [footer] - 푸터 HTML
     * @param {object} [options] - 팝업 열기 옵션
     */
    render(title, body, footer = '', options = {}) {
        const titleEl = document.getElementById('popupTitle');
        const bodyEl = document.getElementById('popupBody');
        const footerEl = document.getElementById('popupFooter');

        if (titleEl) titleEl.textContent = title;
        if (bodyEl) setHtml(bodyEl, body);
        if (footerEl) setHtml(footerEl, footer);

        this.open('popupOverlay', options);
    }
};

/** 형식 체크 */
function check_all(target) {
    const chkboxes = document.getElementsByName("chk[]");
    let chkall;

    if (target && target.tagName === "FORM") {
        chkall = target.querySelector('input[name="chkall"]');
    } else if (target && target.type === "checkbox") {
        chkall = target;
    }

    if (!chkall) return;

    for (const checkbox of chkboxes) {
        checkbox.checked = chkall.checked;
    }
}


function btn_check(f, act)
{
    if (act == "update") // 선택수정
    {
        f.action = list_update_php;
        str = "수정";
    }
    else if (act == "delete") // 선택삭제
    {
        f.action = list_delete_php;
        str = "삭제";
    }
    else
        return;

    var chk = document.getElementsByName("chk[]");
    var bchk = false;

    for (i=0; i<chk.length; i++)
    {
        if (chk[i].checked)
            bchk = true;
    }

    if (!bchk)
    {
        alert(str + "할 자료를 하나 이상 선택하세요.");
        return;
    }

    if (act == "delete")
    {
        if (!confirm("선택한 자료를 정말 삭제 하시겠습니까?"))
            return;
    }

    f.submit();
}

function is_checked(elements_name)
{
    var checked = false;
    var chk = document.getElementsByName(elements_name);
    for (var i=0; i<chk.length; i++) {
        if (chk[i].checked) {
            checked = true;
        }
    }
    return checked;
}

function delete_confirm(el)
{
    if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
        var token = get_ajax_token();
        var href = el.href.replace(/&token=.+$/g, "");
        if(!token) {
            alert("토큰 정보가 올바르지 않습니다.");
            return false;
        }
        el.href = href+"&token="+token;
        return true;
    } else {
        return false;
    }
}

function delete_confirm2(msg)
{
    if(confirm(msg))
        return true;
    else
        return false;
}

function get_ajax_token()
{
    var token = "",
        admin_csrf_token_key = (typeof g5_admin_csrf_token_key !== "undefined") ? g5_admin_csrf_token_key : "";

    $.ajax({
        type: "POST",
        url: g5_admin_url+"/ajax.token.php",
        data : {admin_csrf_token_key:admin_csrf_token_key},
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

function initAdminFloatingActions()
{
    var forms = document.querySelectorAll("main form");
    var actionLabels = ["확인", "저장", "추가", "수정"];

    forms.forEach(function(form) {
        if (form.querySelector(".admin-floating-actions")) {
            return;
        }

        var source = null;
        var submit = null;

        Array.prototype.forEach.call(
            form.querySelectorAll('input[type="submit"], button[type="submit"], button:not([type])'),
            function(button) {
                if (submit) {
                    return;
                }

                var text = button.tagName === "INPUT" ? button.value : button.textContent;
                if (actionLabels.indexOf((text || "").trim()) !== -1) {
                    submit = button;
                }
            }
        );

        if (!submit) {
            return;
        }

        [
            ".btn_fixed_top",
            ".btn_confirm01",
            ".btn_confirm",
            ".flex.items-center.justify-end"
        ].forEach(function(selector) {
            if (source) {
                return;
            }

            var bar = submit.closest(selector);
            if (bar && form.contains(bar)) {
                source = bar;
            }
        });

        if (!source) {
            source = submit.parentElement;
        }

        var floating = source.cloneNode(true);
        floating.classList.remove("btn_fixed_top", "btn_confirm01", "btn_confirm");
        floating.classList.add("admin-floating-actions");
        floating.setAttribute("aria-label", "폼 저장");

        floating.querySelectorAll("[id]").forEach(function(el) {
            el.removeAttribute("id");
        });

        form.classList.add("has-admin-floating-actions");
        form.insertBefore(floating, form.firstChild);
    });
}

// "맨 위로" 버튼. 하단 플로팅 저장 바가 있으면 그 '안 우측 끝'에 항상 표시(겹침 원천 차단),
// 없으면 우하단 코너에 고정. (initAdminFloatingActions 이후에 호출되어야 함)
function initAdminScrollTop()
{
    if (document.getElementById("admin-scroll-top")) {
        return;
    }
    var btn = document.createElement("button");
    btn.type = "button";
    btn.id = "admin-scroll-top";
    // 'rounded' 포함 클래스 — .legacy-admin-content button:not([class*="rounded"]) 레거시 버튼 스타일(padding 등) 회피
    btn.className = "admin-rounded";
    btn.setAttribute("aria-label", "맨 위로");
    btn.setAttribute("title", "맨 위로");
    btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>';

    btn.addEventListener("click", function() {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

    var bar = document.querySelector("main .admin-floating-actions");
    if (bar) {
        btn.classList.add("in-bar");   // 바 안 우측 끝, 항상 표시
        bar.appendChild(btn);
    } else {
        document.body.appendChild(btn); // 바 없는 페이지: 우하단 코너
    }
}

// 관리자 폼 입력에 브라우저 autocomplete/오토필 차단 (라벨에 주소/메일/이름 키워드가
// 있으면 Chrome 이 폼 단위 off 를 무시하므로 필드 단위로 지정). 비밀번호는 new-password.
function initAdminFormAutocomplete()
{
    var textTypes = ["text", "email", "number", "url", "tel", "search", ""];
    document.querySelectorAll("main form").forEach(function(form) {
        if (!form.hasAttribute("autocomplete")) {
            form.setAttribute("autocomplete", "off");
        }
        form.querySelectorAll("input").forEach(function(inp) {
            var t = (inp.getAttribute("type") || "text").toLowerCase();
            if (t === "password") {
                // 이미 autocomplete 가 있어도 비밀번호 매니저/저장된 비번·이력 노출은 별도 차단해야 함
                if (!inp.hasAttribute("autocomplete")) {
                    inp.setAttribute("autocomplete", "new-password");
                }
                inp.setAttribute("data-1p-ignore", "true");      // 1Password
                inp.setAttribute("data-lpignore", "true");       // LastPass
                inp.setAttribute("data-bwignore", "true");       // Bitwarden
                inp.setAttribute("data-protonpass-ignore", "true"); // Proton Pass
                inp.setAttribute("data-form-type", "other");     // 1Password 힌트
                // Chrome 내장 매니저(저장된 로그인 목록)까지 차단: 로드 시 readonly →
                // 사용자가 포커스/클릭하면 해제해 입력은 정상. (data-* 무시 속성은 Chrome 내장엔 안 먹음)
                // required(신규 회원) 필드는 readonly 가 native 필수검증을 무력화하므로 제외
                if (!inp.required && inp.getAttribute("data-admin-pw-lock") !== "1") {
                    inp.setAttribute("data-admin-pw-lock", "1");
                    inp.setAttribute("readonly", "readonly");
                    var unlock = function() {
                        if (inp.hasAttribute("readonly")) {
                            inp.removeAttribute("readonly");
                        }
                    };
                    inp.addEventListener("focus", unlock);
                    inp.addEventListener("mousedown", unlock);
                    inp.addEventListener("touchstart", unlock, { passive: true });
                }
                return;
            }
            if (inp.hasAttribute("autocomplete")) {
                return;
            }
            if (textTypes.indexOf(t) !== -1) {
                inp.setAttribute("autocomplete", "off");
            }
        });
    });
}

// 비밀번호 마스킹 폴백 — admin 의 data-pw-mask 필드는 type=text + -webkit-text-security 로
// Chrome 내장 비번 매니저를 피한다. 이 속성을 지원하지 않는 브라우저(예: Firefox)에서는
// 마스킹이 안 되므로 실제 password 타입으로 되돌린다.
function initAdminPwMaskFallback()
{
    var probe = document.createElement("input");
    probe.style.setProperty("-webkit-text-security", "disc");
    var supported = probe.style.getPropertyValue("-webkit-text-security") === "disc";
    if (supported) {
        return;
    }
    document.querySelectorAll('input[data-pw-mask="1"]').forEach(function(inp) {
        inp.setAttribute("type", "password");
        inp.classList.remove("admin-pw-mask");
        if (!inp.getAttribute("autocomplete")) {
            inp.setAttribute("autocomplete", "new-password");
        }
    });
}

// 체크박스 바로 뒤의 텍스트('사용' 등)를 <label for> 로 감싸 클릭 시 토글되게 함.
// 이미 <label> 등 엘리먼트가 따라오는 경우(공백 텍스트 포함)는 건드리지 않음.
function initAdminCheckboxLabels()
{
    document.querySelectorAll('main form input[type="checkbox"]').forEach(function(cb) {
        if (!cb.id) {
            return;
        }
        var node = cb.nextSibling;
        if (!node || node.nodeType !== 3) {        // 3 = TEXT_NODE
            return;
        }
        var text = node.textContent;
        if (!text || !text.trim()) {               // 공백뿐이면(뒤에 별도 label 등) 건너뜀
            return;
        }
        var label = document.createElement("label");
        label.setAttribute("for", cb.id);
        label.className = "i-chk-label";
        label.textContent = text.replace(/\s+$/, "");   // 앞 공백(간격)은 유지, 뒤 공백만 제거
        node.parentNode.replaceChild(label, node);
    });
}

$(function() {
    initAdminFloatingActions();
    initAdminScrollTop();
    initAdminPwMaskFallback();
    initAdminFormAutocomplete();
    initAdminCheckboxLabels();

    $(document).on("click", "form input:submit, form button:submit", function() {
        var f = this.form;
        var token = get_ajax_token();

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

/**
 * 게시판 복사 모달 — board_list / board_form 공용.
 * @param {string} boTable - 원본 게시판 테이블명
 * @param {object} [opts]  - { reloadOnSuccess: boolean }
 */
function openBoardCopyModal(boTable, opts) {
    opts = opts || {};
    fetch(g5_admin_url + '/board_copy?bo_table=' + encodeURIComponent(boTable), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(function (res) { return res.text(); })
    .then(function (html) {
        PopupManager.render('게시판 복사', html);
        var form = document.getElementById('fboardcopy');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!boardCopyValidate(form)) return;
            submitBoardCopy(form, opts);
        });
        var tt = document.getElementById('target_table');
        if (tt) tt.focus();
    })
    .catch(function () { alert('복사 폼을 불러오지 못했습니다.'); });
}

/** 제출 전 클라이언트 검증 (금지어 / 원본=대상 동일 방지) */
function boardCopyValidate(form) {
    var banned = [];
    try { banned = JSON.parse(form.dataset.banned || '[]'); } catch (e) {}
    var target = form.target_table.value;
    if (banned.indexOf(target) !== -1) {
        alert('입력한 게시판 TABLE명을 사용할수 없습니다. 다른 이름으로 입력해 주세요.');
        return false;
    }
    if (form.bo_table.value === target) {
        alert('원본 테이블명과 복사할 테이블명이 달라야 합니다.');
        return false;
    }
    return true;
}

/** 복사 폼 AJAX 제출 + 결과 처리 */
function submitBoardCopy(form, opts) {
    var token = (typeof get_ajax_token === 'function') ? get_ajax_token() : '';
    if (!token) { alert('토큰 정보가 올바르지 않습니다.'); return; }
    var fd = new FormData(form);
    fd.set('token', token);
    fetch(form.action, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (!data || !data.success) {
            alert((data && data.message) || '복사에 실패했습니다.');
            return;
        }
        PopupManager.close('popupOverlay');
        alert(data.message || '복사에 성공했습니다.');
        // 복사 성공 후 게시판 목록으로 이동 — 새로 만들어진 게시판 확인.
        location.href = g5_admin_url + '/board_list';
    })
    .catch(function () { alert('요청 처리 중 오류가 발생했습니다.'); });
}
