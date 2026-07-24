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
$visit_nav_items = array(
    'visit_list'    => '접속자',
    'visit_domain'  => '도메인',
    'visit_browser' => '브라우저',
    'visit_os'      => '운영체제',
);
if (defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE) {
    $visit_nav_items['visit_device'] = '접속기기';
}
$visit_nav_items += array(
    'visit_hour'  => '시간',
    'visit_week'  => '요일',
    'visit_date'  => '일',
    'visit_month' => '월',
    'visit_year'  => '년',
);
$visit_current_page = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_FILENAME);
?>

<!-- jQuery UI datepicker 자산 / 한글 locale 은 _layout.php 에서 한 번만 로드. -->
<form name="fvisit" id="fvisit" class="local_sch03 local_sch" method="get">
<div class="sch_last">
    <strong>기간별검색</strong>
    <div class="visit-date-range">
        <label for="fr_date" class="sound_only">시작일</label>
        <input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="frm_input" size="11" maxlength="10" autocomplete="off" inputmode="numeric">
        <span aria-hidden="true">~</span>
        <label for="to_date" class="sound_only">종료일</label>
        <input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="frm_input" size="11" maxlength="10" autocomplete="off" inputmode="numeric">
    </div>
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<ul class="anchor">
    <?php foreach ($visit_nav_items as $visit_page => $visit_label) { ?>
    <li><a href="<?php echo G5_ADMIN_URL.'/'.$visit_page.$query_string ?>"><?php echo $visit_label ?></a></li>
    <?php } ?>
</ul>

<div class="visit-mobile-nav">
    <label for="visit-mobile-page">통계 항목</label>
    <select id="visit-mobile-page" aria-label="접속자 통계 항목 선택">
        <?php foreach ($visit_nav_items as $visit_page => $visit_label) { ?>
        <option value="<?php echo G5_ADMIN_URL.'/'.$visit_page.$query_string ?>"<?php echo $visit_current_page === $visit_page ? ' selected' : '' ?>><?php echo $visit_label ?></option>
        <?php } ?>
    </select>
</div>

<script>
$(function(){
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    $("#visit-mobile-page").on("change", function() {
        window.location.href = this.value;
    });

    var mobileQuery = window.matchMedia("(max-width: 640px)");

    $(".visit-stats-page .tbl_head01 table").each(function(){
        var table = this;
        var headers = Array.prototype.map.call(table.querySelectorAll("thead th"), function(th) {
            return th.textContent.trim();
        });
        var rows = Array.prototype.filter.call(table.querySelectorAll("tbody tr"), function(row) {
            return !row.querySelector(".empty_table");
        });
        var pageSize = 10;
        var totalPages = Math.ceil(rows.length / pageSize);
        var currentPage = 1;
        var wrap = table.closest(".tbl_wrap");
        var nav;

        table.classList.add(headers.length === 5 ? "visit-stats-five-col" : "visit-stats-four-col");
        rows.forEach(function(row) {
            Array.prototype.forEach.call(row.cells, function(cell, index) {
                cell.dataset.label = headers[index] || "";
                if (cell.querySelector(".visit_bar")) {
                    cell.classList.add("visit-stat-graph");
                }
            });
        });

        if (totalPages > 1) {
            nav = document.createElement("nav");
            nav.className = "visit-stats-mobile-pagination";
            nav.setAttribute("aria-label", "접속자 통계 페이지 이동");
            nav.innerHTML =
                '<button type="button" data-action="first">처음</button>' +
                '<button type="button" data-action="prev">이전</button>' +
                '<label class="current-page"><span class="sound_only">이동할 페이지</span>' +
                '<input type="number" min="1" max="' + totalPages + '" value="1" inputmode="numeric" aria-label="이동할 페이지"></label>' +
                '<button type="button" data-action="next">다음</button>' +
                '<button type="button" data-action="last">맨끝</button>';
            wrap.insertAdjacentElement("afterend", nav);

            var input = nav.querySelector("input");
            function showPage(page) {
                currentPage = Math.max(1, Math.min(totalPages, page));
                rows.forEach(function(row, index) {
                    row.hidden = mobileQuery.matches &&
                        (index < (currentPage - 1) * pageSize || index >= currentPage * pageSize);
                });
                input.value = currentPage;
                nav.querySelector('[data-action="first"]').disabled = currentPage === 1;
                nav.querySelector('[data-action="prev"]').disabled = currentPage === 1;
                nav.querySelector('[data-action="next"]').disabled = currentPage === totalPages;
                nav.querySelector('[data-action="last"]').disabled = currentPage === totalPages;
            }
            nav.addEventListener("click", function(event) {
                var action = event.target.dataset.action;
                if (!action) return;
                if (action === "first") showPage(1);
                if (action === "prev") showPage(currentPage - 1);
                if (action === "next") showPage(currentPage + 1);
                if (action === "last") showPage(totalPages);
            });
            input.addEventListener("change", function() {
                var page = Number(input.value);
                if (!Number.isInteger(page) || page < 1 || page > totalPages) {
                    input.value = currentPage;
                    input.classList.add("is-invalid");
                    window.setTimeout(function() { input.classList.remove("is-invalid"); }, 700);
                    return;
                }
                showPage(page);
            });
            mobileQuery.addEventListener("change", function() { showPage(currentPage); });
            showPage(1);
        }
    });
});

function fvisit_submit(act)
{
    var f = document.fvisit;
    f.action = act;
    f.submit();
}
</script>
