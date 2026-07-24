<?php
/*
 * /admin/popular_list — 인기검색어 목록 (자기 자신으로 POST 해서 삭제 처리).
 */
$sub_menu = "300300";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
auth_check_menu($auth, $sub_menu, 'r');

// 체크된 자료 삭제
if (isset($_POST['chk']) && is_array($_POST['chk'])) {
    check_admin_token();

    for ($i = 0; $i < count($_POST['chk']); $i++) {
        $pp_id = (int) $_POST['chk'][$i];
        sql_pdo_query(" delete from {$g5['popular_table']} where pp_id = :pp_id ", [':pp_id' => $pp_id]);
    }
}

$sql_common  = " from {$g5['popular_table']} a ";
$sql_search  = " where (1) ";
$sql_params  = [];

// $sfl 화이트리스트 (컬럼명은 placeholder 불가)
$allowed_sfl = ['pp_word', 'pp_date', 'pp_ip'];
if ($stx) {
    $sfl_safe = in_array($sfl, $allowed_sfl, true) ? $sfl : 'pp_word';
    switch ($sfl_safe) {
        case 'pp_word':
            $sql_search .= " and ({$sfl_safe} like :stx) ";
            $sql_params[':stx'] = $stx.'%';
            break;
        case 'pp_date':
            $sql_search .= " and ({$sfl_safe} = :stx) ";
            $sql_params[':stx'] = $stx;
            break;
        default:
            $sql_search .= " and ({$sfl_safe} like :stx) ";
            $sql_params[':stx'] = '%'.$stx.'%';
            break;
    }
}

if (!$sst) {
    $sst  = "pp_id";
    $sod = "desc";
}
$allowed_sst = array('pp_id', 'pp_word', 'pp_date', 'pp_ip');
if ($sst && !in_array($sst, $allowed_sst)) $sst = 'pp_id';
if ($sod && !in_array(strtolower($sod), array('asc', 'desc'))) $sod = '';
$sql_order = " order by {$sst} {$sod} ";

$row = sql_pdo_fetch(" select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ", $sql_params);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = max(1, (int)ceil($total_count / max(1, $rows)));  // 전체 페이지 계산
if ($page < 1) {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$page = min($page, $total_page);
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$result = sql_pdo_query(" select * {$sql_common} {$sql_search} {$sql_order} limit ".(int)$from_record.', '.(int)$rows.' ', $sql_params);

$listall = '<a href="'.G5_ADMIN_URL.'/popular_list" class="ov_listall">전체목록</a>';
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$page_url = static function ($target) use ($sst, $sod, $sfl, $stx): string {
    return G5_ADMIN_URL.'/popular_list?'.http_build_query(array_filter([
        'sst' => $sst,
        'sod' => $sod,
        'sfl' => $sfl,
        'stx' => $stx,
        'page' => $target,
    ], static fn($value) => $value !== '' && $value !== null));
};
$page_input_url = str_replace('__PAGE__', '', $page_url('__PAGE__'));

$g5['title'] = '인기검색어관리';
admin_layout_start($g5['title'], 'popular');
?>
<main class="popular-list-page flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex items-center gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
</header>
<div class="legacy-admin-content space-y-4">
<?php

$colspan = 4;
?>

<script>
    var list_update_php = '';
    var list_delete_php = 'popular_list.php';
</script>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">건수</span><span class="ov_num"> <?php echo number_format($total_count) ?>개</span></span>
</div>

<form name="fsearch" id="fsearch" class="popular-search local_sch01 local_sch" method="get">
    <div class="popular-search-fields sch_last">
        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl">
            <option value="pp_word" <?php echo get_selected($sfl, "pp_word"); ?>>검색어</option>
            <option value="pp_date" <?php echo get_selected($sfl, "pp_date"); ?>>등록일</option>
        </select>
        <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="stx" value="<?php echo $h($stx) ?>" id="stx" required class="required frm_input">
        <input type="submit" value="검색" class="btn_submit">
    </div>
</form>

<form name="fpopularlist" id="fpopularlist" class="popular-list-form" method="post" data-floating-actions="off">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">

    <div class="popular-list-scroll tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th scope="col">
                        <label for="chkall" class="sound_only">현재 페이지 인기검색어 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th scope="col"><?php echo subject_sort_link('pp_word') ?>검색어</a></th>
                    <th scope="col">등록일</th>
                    <th scope="col">등록IP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_pdo_fetch_array($result); $i++) {
                    $word = get_text($row['pp_word']);
                    $bg = 'bg' . ($i % 2);
                ?>
                    <tr class="<?php echo $bg; ?> popular-card">
                        <td class="td_chk popular-col-check">
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo $word ?></label>
                            <input type="checkbox" name="chk[]" value="<?php echo $row['pp_id'] ?>" id="chk_<?php echo $i ?>">
                        </td>
                        <td class="td_left popular-col-word" data-label="검색어"><a href="<?php echo G5_ADMIN_URL ?>/popular_list?sfl=pp_word&amp;stx=<?php echo urlencode($word) ?>"><?php echo $word ?></a></td>
                        <td class="popular-col-date" data-label="등록일"><?php echo $h($row['pp_date']) ?></td>
                        <td class="popular-col-ip" data-label="등록 IP"><?php echo $h($row['pp_ip']) ?></td>
                    </tr>
                <?php
                }

                if ($i == 0) {
                    echo '<tr class="popular-empty"><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
                }
                ?>
            </tbody>
        </table>

    </div>

    <?php if ($is_admin == 'super') { ?>
        <div class="popular-list-actions btn_fixed_top">
            <button type="submit" class="btn btn_02">선택삭제</button>
        </div>
    <?php } ?>

</form>

<div class="popular-desktop-pagination">
    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, G5_ADMIN_URL.'/popular_list?'.$qstr.'&amp;page='); ?>
</div>
<?php if ($total_page > 1): ?>
<nav class="popular-mobile-pagination" aria-label="인기검색어 페이지 이동">
    <?php if ($page > 1): ?>
        <a href="<?php echo $h($page_url(1)) ?>">처음</a>
        <a href="<?php echo $h($page_url($page - 1)) ?>">이전</a>
    <?php else: ?>
        <span class="is-disabled">처음</span>
        <span class="is-disabled">이전</span>
    <?php endif; ?>
    <label class="current-page">
        <input type="number" value="<?php echo (int)$page ?>" min="1" max="<?php echo (int)$total_page ?>"
               inputmode="numeric" data-current-page="<?php echo (int)$page ?>"
               data-page-url="<?php echo $h($page_input_url) ?>" aria-label="이동할 페이지">
    </label>
    <?php if ($page < $total_page): ?>
        <a href="<?php echo $h($page_url($page + 1)) ?>">다음</a>
        <a href="<?php echo $h($page_url($total_page)) ?>">맨끝</a>
    <?php else: ?>
        <span class="is-disabled">다음</span>
        <span class="is-disabled">맨끝</span>
    <?php endif; ?>
</nav>
<?php endif; ?>

<script>
    $(function() {
        $('#fpopularlist').submit(function() {
            if (confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
                if (!is_checked("chk[]")) {
                    alert("선택삭제 하실 항목을 하나 이상 선택하세요.");
                    return false;
                }

                return true;
            } else {
                return false;
            }
        });
    });

    document.querySelectorAll('.popular-mobile-pagination .current-page input').forEach(function (input) {
        function moveToPage() {
            var current = Number(input.dataset.currentPage);
            var target = Number(input.value);
            var max = Number(input.max);

            if (!Number.isInteger(target) || target < 1 || target > max) {
                input.value = current;
                input.classList.add('is-invalid');
                window.setTimeout(function () { input.classList.remove('is-invalid'); }, 700);
                return;
            }
            if (target !== current) window.location.href = input.dataset.pageUrl + target;
        }

        input.addEventListener('change', moveToPage);
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                moveToPage();
            }
        });
    });
</script>

<?php
?>
</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/popular_list — modern shell wrap end
