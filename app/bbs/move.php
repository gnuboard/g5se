<?php
include_once('./_common.php');

$sw = isset($_REQUEST['sw']) ? clean_xss_tags($_REQUEST['sw'], 1, 1) : '';

if ($sw === 'move')
    $act = '이동';
else if ($sw === 'copy')
    $act = '복사';
else
    alert('sw 값이 제대로 넘어오지 않았습니다.');

// 게시판 관리자 이상 복사, 이동 가능
if ($is_admin != 'board' && $is_admin != 'group' && $is_admin != 'super')
    alert_close("게시판 관리자 이상 접근이 가능합니다.");

$g5['title'] = '게시물 ' . $act;
if (defined('G5_THEME_PATH') && is_file(G5_THEME_PATH.'/modern/_head.inc.php')) {
    require_once(G5_THEME_PATH.'/modern/_head.inc.php');
}
include_once(G5_PATH.'/head.sub.php');

$move_update_url = G5_URL.'/move_update';

$wr_id_list = '';
if ($wr_id)
    $wr_id_list = $wr_id;
else {
    $comma = '';

    $count_chk_wr_id = (isset($_POST['chk_wr_id']) && is_array($_POST['chk_wr_id'])) ? count($_POST['chk_wr_id']) : 0;

    for ($i=0; $i<$count_chk_wr_id; $i++) {
        $wr_id_val = isset($_POST['chk_wr_id'][$i]) ? preg_replace('/[^0-9]/', '', $_POST['chk_wr_id'][$i]) : 0;
        $wr_id_list .= $comma . $wr_id_val;
        $comma = ',';
    }
}

// 원본 게시판을 선택 할 수 있도록 함.
$sql = " select * from {$g5['board_table']} a, {$g5['group_table']} b where a.gr_id = b.gr_id ";
$params = [];
if ($is_admin == 'group') {
    $sql .= " and b.gr_admin = :mb_id ";
    $params[':mb_id'] = $member['mb_id'];
} else if ($is_admin == 'board') {
    $sql .= " and a.bo_admin = :mb_id ";
    $params[':mb_id'] = $member['mb_id'];
}
$sql .= " order by a.gr_id, a.bo_order, a.bo_table ";
$result = sql_pdo_query($sql, $params);

$list = array();

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $list[$i] = $row;
}
?>

<div id="copymove" class="m-popup m-move-popup">
    <header class="m-popup-head">
        <h1 id="win_title" class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 7h12"/><path d="M8 12h12"/><path d="M8 17h12"/><path d="M3 7h.01"/><path d="M3 12h.01"/><path d="M3 17h.01"/></svg>
            <?php echo $g5['title'] ?>
        </h1>
        <p class="m-popup-sub"><?php echo $act ?>할 게시판을 한 개 이상 선택해 주십시오.</p>
    </header>

    <form name="fboardmoveall" method="post" action="<?php echo $move_update_url; ?>" onsubmit="return fboardmoveall_submit(this);" class="m-move-form">
    <input type="hidden" name="sw" value="<?php echo $sw ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id_list" value="<?php echo $wr_id_list ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="act" value="<?php echo $act ?>">
    <input type="hidden" name="url" value="<?php echo get_text(clean_xss_tags($_SERVER['HTTP_REFERER'])); ?>">

    <div class="m-move-table-wrap">
        <table class="m-move-table">
        <caption><?php echo $act ?>할 게시판을 한개 이상 선택하여 주십시오.</caption>
        <thead>
        <tr>
            <th scope="col">
                <label for="chkall" class="sound_only">현재 페이지 게시판 전체</label>
                <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
            </th>
            <th scope="col">게시판</th>
        </tr>
        </thead>
        <tbody>
        <?php for ($i=0; $i<count($list); $i++) {
            $atc_mark = '';
            $atc_bg = '';
            if ($list[$i]['bo_table'] == $bo_table) { // 게시물이 현재 속해 있는 게시판이라면
                $atc_mark = '<span class="copymove_current">현재<span class="sound_only">게시판</span></span>';
                $atc_bg = 'copymove_currentbg';
            }
        ?>
        <tr class="<?php echo $atc_bg; ?>">
            <td class="td_chk">
                <label for="chk<?php echo $i ?>" class="sound_only"><?php echo $list[$i]['bo_table'] ?></label>
                <input type="checkbox" value="<?php echo $list[$i]['bo_table'] ?>" id="chk<?php echo $i ?>" name="chk_bo_table[]">
            </td>
            <td>
                <label for="chk<?php echo $i ?>">
                    <?php
                    echo $list[$i]['gr_subject'] . ' &gt; ';
                    $save_gr_subject = $list[$i]['gr_subject'];
                    ?>
                    <?php echo $list[$i]['bo_subject'] ?> (<?php echo $list[$i]['bo_table'] ?>)
                    <?php echo $atc_mark; ?>
                </label>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>

    <div class="m-popup-actions m-move-actions">
        <button type="button" class="m-btn m-btn-ghost btn_close">창닫기</button>
        <button type="submit" id="btn_submit" class="m-btn m-btn-primary"><?php echo $act ?></button>
    </div>
    </form>

</div>

<style>
.m-move-popup {
    padding: 18px;
}
.m-move-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.m-move-table-wrap {
    overflow: auto;
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    background: var(--m-surface);
}
.m-move-table {
    width: 100%;
    border: 0;
    border-collapse: collapse;
    table-layout: fixed;
}
.m-move-table caption {
    position: absolute;
    width: 1px;
    height: 1px;
    margin: -1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
}
.m-move-table th,
.m-move-table td {
    border-bottom: 1px solid var(--m-border);
    color: var(--m-text);
}
.m-move-table thead th {
    padding: 10px 12px;
    background: var(--m-surface-2);
    font-size: var(--m-text-sm);
    font-weight: 700;
}
.m-move-table tbody td {
    padding: 12px;
    font-size: var(--m-text-md);
    vertical-align: middle;
}
.m-move-table tbody tr:last-child td {
    border-bottom: 0;
}
.m-move-table tbody tr:hover td {
    background: var(--m-surface-2);
}
.m-move-table .td_chk {
    width: 48px;
    text-align: center;
}
.m-move-table th:first-child {
    width: 48px;
}
.m-move-table label {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    word-break: break-word;
    cursor: pointer;
}
.m-move-table input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: var(--m-primary);
}
.copymove_current {
    display: inline-flex;
    align-items: center;
    flex: 0 0 auto;
    padding: 3px 7px;
    border-radius: var(--m-radius-sm);
    background: var(--m-primary-soft);
    color: var(--m-primary);
    font-size: var(--m-text-xs);
    font-weight: 700;
}
.m-move-table tbody tr.copymove_currentbg > td {
    background: var(--m-surface-2) !important;
    color: var(--m-text) !important;
}
.m-move-table tbody tr.copymove_currentbg:hover > td {
    background: var(--m-surface-2) !important;
}
.m-move-table tbody tr.copymove_currentbg label {
    color: var(--m-text) !important;
}
.m-move-table tbody tr.copymove_currentbg .copymove_current {
    background: var(--m-primary-soft) !important;
    color: var(--m-primary) !important;
}
[data-theme="dark"] .m-move-table tbody tr.copymove_currentbg > td {
    background: #111827 !important;
    color: var(--m-text) !important;
}
[data-theme="dark"] .m-move-table tbody tr.copymove_currentbg:hover > td {
    background: #172033 !important;
}
[data-theme="dark"] .m-move-table tbody tr.copymove_currentbg .copymove_current {
    background: rgba(59, 130, 246, 0.22) !important;
    color: #bfdbfe !important;
}
.m-move-actions {
    justify-content: flex-end;
    margin-top: 0;
}
.m-move-actions .m-btn {
    width: auto;
    min-width: 88px;
    padding: 9px 18px;
}
</style>

<script>
$(function() {
    $(".btn_close").click(function() {
        window.close();
    });
});

function all_checked(sw) {
    var f = document.fboardmoveall;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_bo_table[]")
            f.elements[i].checked = sw;
    }
}

function fboardmoveall_submit(f)
{
    var check = false;

    if (typeof(f.elements['chk_bo_table[]']) == 'undefined')
        ;
    else {
        if (typeof(f.elements['chk_bo_table[]'].length) == 'undefined') {
            if (f.elements['chk_bo_table[]'].checked)
                check = true;
        } else {
            for (i=0; i<f.elements['chk_bo_table[]'].length; i++) {
                if (f.elements['chk_bo_table[]'][i].checked) {
                    check = true;
                    break;
                }
            }
        }
    }

    if (!check) {
        alert('게시물을 '+f.act.value+'할 게시판을 한개 이상 선택해 주십시오.');
        return false;
    }

    document.getElementById('btn_submit').disabled = true;

    f.action = <?php echo json_encode($move_update_url, JSON_UNESCAPED_SLASHES); ?>;
    return true;
}
</script>

<?php
run_event('move_html_footer');
include_once(G5_PATH.'/tail.sub.php');
