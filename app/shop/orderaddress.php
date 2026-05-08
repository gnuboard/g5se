<?php
include_once('./_common.php');

if(!$is_member)
    alert_close('회원이시라면 회원로그인 후 이용해 주십시오.');

$ad_id = isset($_REQUEST['ad_id']) ? (int) $_REQUEST['ad_id'] : 0;

if($w == 'd') {
    sql_pdo_query(" delete from {$g5['g5_shop_order_address_table']} where mb_id = :mb_id and ad_id = :ad_id ",
                  [':mb_id' => $member['mb_id'], ':ad_id' => $ad_id]);
    goto_url($_SERVER['SCRIPT_NAME']);
}

$sql_common = " from {$g5['g5_shop_order_address_table']} where mb_id = :mb_id ";

$row = sql_pdo_fetch(" select count(ad_id) as cnt " . $sql_common, [':mb_id' => $member['mb_id']]);
$total_count = (int)$row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$result = sql_pdo_query(" select * $sql_common order by ad_default desc, ad_id desc limit ".(int)$from_record.', '.(int)$rows.' ',
                       [':mb_id' => $member['mb_id']]);

$_addr_empty = !sql_num_rows($result);

$order_action_url = G5_HTTPS_SHOP_URL.'/orderaddressupdate.php';

// gnu5se: 반응형 단일 마크업 정책 — G5_IS_MOBILE 분기 제거. 데스크탑 markup + 미디어쿼리만 사용.

// 테마에 orderaddress.php 있으면 include
if(defined('G5_THEME_SHOP_PATH')) {
    $theme_orderaddress_file = G5_THEME_SHOP_PATH.'/orderaddress.php';
    if(is_file($theme_orderaddress_file)) {
        include_once($theme_orderaddress_file);
        return;
        unset($theme_orderaddress_file);
    }
}

$g5['title'] = '배송지 목록';
include_once(G5_PATH.'/head.sub.php');
// gnu5se: modern 토큰 + .m-popup 컴포넌트 로드
if(defined('G5_THEME_PATH') && is_file(G5_THEME_PATH.'/modern/_head.inc.php')) {
    require_once(G5_THEME_PATH.'/modern/_head.inc.php');
}
?>
<style>
/* gnu5se: 배송지 목록 popup — coupon 과 동일한 .m-popup shell + 카드 list */

/* 카드 list */
.adr-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}
.adr-card {
    display: grid;
    grid-template-columns: auto 1fr 200px;
    gap: 18px;
    align-items: center;
    padding: 18px 20px;
    background: var(--m-surface);
    border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
    transition: border-color 0.15s;
}
.adr-card:hover { border-color: var(--m-border-hover); }
.adr-check { display: flex; align-items: center; }
/* legacy .selec_chk 의 absolute hide 무력화 — 카드 안에선 native 체크박스 노출 */
.adr-check input[type="checkbox"],
.adr-check input.selec_chk {
    position: static !important;
    visibility: visible !important;
    width: 20px !important;
    height: 20px !important;
    opacity: 1 !important;
    z-index: auto !important;
    overflow: visible !important;
    margin: 0;
    accent-color: var(--m-primary);
    cursor: pointer;
}
/* legacy input[type="radio"] hidden 무력화 */
.adr-default input[type="radio"] {
    position: static !important;
    visibility: visible !important;
    width: 14px !important;
    height: 14px !important;
    opacity: 1 !important;
    z-index: auto !important;
    overflow: visible !important;
    margin: 0;
    accent-color: var(--m-primary);
    text-indent: 0 !important;
}
.adr-info { display: grid; gap: 6px; min-width: 0; }
.adr-info-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.adr-info-label { color: var(--m-text-soft); font-size: 0.85em; min-width: 56px; }
.adr-info-value { color: var(--m-text); }
.adr-info-name { color: var(--m-text); font-weight: 700; font-size: 1.05em; }
.adr-info-tel { color: var(--m-text-soft); font-size: 0.9em; }
.adr-info-subject input {
    width: 100%; max-width: 220px;
    padding: 6px 10px;
    background: var(--m-surface-2);
    border: 1px solid var(--m-border);
    border-radius: 6px;
    color: var(--m-text);
    box-sizing: border-box;
}
.adr-actions { display: flex; flex-direction: column; gap: 6px; }
.adr-btn {
    padding: 6px 10px;
    background: var(--m-surface-2);
    color: var(--m-text);
    border: 1px solid var(--m-border);
    border-radius: 6px;
    text-align: center;
    text-decoration: none;
    font-size: 0.85em;
    font-weight: 500;
    cursor: pointer;
    line-height: 1.3;
}
.adr-btn:hover { border-color: var(--m-primary); color: var(--m-primary); }
.adr-btn-select { background: var(--m-primary) !important; color: #fff !important; border-color: var(--m-primary) !important; }
.adr-btn-select:hover { opacity: 0.9; }
.adr-btn-delete:hover { color: #ef4444; border-color: #ef4444; }
.adr-default {
    display: flex; align-items: center; justify-content: center; gap: 4px;
    padding: 6px 10px;
    background: var(--m-surface-2);
    color: var(--m-text);
    border: 1px solid var(--m-border);
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85em;
    line-height: 1.3;
}
.adr-default input { accent-color: var(--m-primary); }
.adr-default.is-default,
.adr-default:has(input[type="radio"]:checked) {
    background: var(--m-primary);
    color: #fff;
    border-color: var(--m-primary);
}

/* 빈 상태 — coupon 과 동일 톤 */
.adr-empty {
    padding: 50px 20px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    background: var(--m-surface); border: 1px dashed var(--m-border);
    border-radius: var(--m-radius);
    list-style: none;
}
.adr-empty svg { color: var(--m-text-faint); }
.adr-empty p { margin: 0; color: var(--m-text-muted); font-size: var(--m-text-sm); }

/* 모바일 — 카드를 stack */
@media (max-width: 768px) {
    .adr-card {
        grid-template-columns: 1fr;
        gap: 12px;
        padding: 14px 16px;
    }
    .adr-actions {
        flex-direction: row;
        gap: 6px;
    }
    .adr-actions > * {
        flex: 1;
    }
    .adr-info-subject input { max-width: 100%; }
}
</style>

<!-- 배송지 목록 시작 { -->
<div class="m-popup">
    <header class="m-popup-head">
        <h1 class="m-popup-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            배송지 목록
        </h1>
    </header>

    <?php if ($_addr_empty) { ?>
    <div class="adr-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <p>저장된 배송지가 없습니다.</p>
    </div>

    <div class="m-popup-actions">
        <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:9px 20px;">창닫기</button>
    </div>

    <?php } else { ?>
    <form name="forderaddress" method="post" action="<?php echo $order_action_url; ?>" autocomplete="off">
        <div class="adr-list">
            <?php
            $sep = chr(30);
            for($i=0; $row=sql_fetch_array($result); $i++) {
                $addr = $row['ad_name'].$sep.$row['ad_tel'].$sep.$row['ad_hp'].$sep.$row['ad_zip1'].$sep.$row['ad_zip2'].$sep.$row['ad_addr1'].$sep.$row['ad_addr2'].$sep.$row['ad_addr3'].$sep.$row['ad_jibeon'].$sep.$row['ad_subject'];
                $addr = get_text($addr);
                $is_default = !empty($row['ad_default']);
            ?>
            <div class="adr-card">
                <div class="adr-check">
                    <input type="hidden" name="ad_id[<?php echo $i; ?>]" value="<?php echo $row['ad_id']; ?>">
                    <input type="checkbox" name="chk[]" value="<?php echo $i; ?>" id="chk_<?php echo $i; ?>" class="selec_chk">
                </div>
                <div class="adr-info">
                    <div class="adr-info-row">
                        <span class="adr-info-name"><?php echo get_text($row['ad_name']); ?></span>
                        <span class="adr-info-tel"><?php echo $row['ad_tel']; ?> / <?php echo $row['ad_hp']; ?></span>
                    </div>
                    <div class="adr-info-row">
                        <span class="adr-info-value"><?php echo print_address($row['ad_addr1'], $row['ad_addr2'], $row['ad_addr3'], $row['ad_jibeon']); ?></span>
                    </div>
                    <div class="adr-info-row adr-info-subject">
                        <span class="adr-info-label">배송지명</span>
                        <input type="text" name="ad_subject[<?php echo $i; ?>]" id="ad_subject<?php echo $i; ?>" maxlength="20" placeholder="별칭 (예: 집/회사)" value="<?php echo get_text($row['ad_subject']); ?>">
                    </div>
                </div>
                <div class="adr-actions">
                    <input type="hidden" class="adr-payload" value="<?php echo $addr; ?>">
                    <button type="button" class="sel_address adr-btn adr-btn-select">선택</button>
                    <a href="/shop/orderaddress?w=d&amp;ad_id=<?php echo $row['ad_id']; ?>" class="del_address adr-btn adr-btn-delete">삭제</a>
                    <label class="adr-default <?php echo $is_default ? 'is-default' : ''; ?>">
                        <input type="radio" name="ad_default" value="<?php echo $row['ad_id']; ?>" id="ad_default<?php echo $i; ?>" <?php if($is_default) echo 'checked="checked"'; ?>>
                        기본배송지
                    </label>
                </div>
            </div>
            <?php } ?>
        </div>

        <?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

        <div class="m-popup-actions">
            <input type="submit" name="act_button" value="선택수정" class="m-btn m-btn-primary btn_submit" style="width:auto; padding:9px 20px;">
            <button type="button" onclick="window.close();" class="m-btn m-btn-ghost" style="width:auto; padding:9px 20px;">창닫기</button>
        </div>
    </form>
    <?php } ?>
</div>
<!-- } 배송지 목록 끝 -->

<?php if (!$_addr_empty) { ?>
<script>
$(function() {
    $(".sel_address").on("click", function() {
        var addr = $(this).siblings("input").val().split(String.fromCharCode(30));

        var f = window.opener.forderform;
        f.od_b_name.value        = addr[0];
        f.od_b_tel.value         = addr[1];
        f.od_b_hp.value          = addr[2];
        f.od_b_zip.value         = addr[3] + addr[4];
        f.od_b_addr1.value       = addr[5];
        f.od_b_addr2.value       = addr[6];
        f.od_b_addr3.value       = addr[7];
        f.od_b_addr_jibeon.value = addr[8];
        f.ad_subject.value       = addr[9];

        var zip1 = addr[3].replace(/[^0-9]/g, "");
        var zip2 = addr[4].replace(/[^0-9]/g, "");

        if(zip1 != "" && zip2 != "") {
            var code = String(zip1) + String(zip2);

            if(window.opener.zipcode != code) {
                window.opener.zipcode = code;
                window.opener.calculate_sendcost(code);
            }
        }

        window.close();
    });

    $(".del_address").on("click", function() {
        return confirm("배송지 목록을 삭제하시겠습니까?");
    });

    // 전체선택 부분
    $("#chk_all").on("click", function() {
        if($(this).is(":checked")) {
            $("input[name^='chk[']").attr("checked", true);
        } else {
            $("input[name^='chk[']").attr("checked", false);
        }
    });

    $(".btn_submit").on("click", function() {
        if($("input[name^='chk[']:checked").length==0 ){
            alert("수정하실 항목을 하나 이상 선택하세요.");
            return false;
        }
    });

});
</script>
<?php } ?>

<?php
include_once(G5_PATH.'/tail.sub.php');
