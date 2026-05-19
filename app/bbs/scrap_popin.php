<?php
include_once('./_common.php');

include_once(G5_PATH.'/head.sub.php');

$scrap_url = G5_URL.'/scrap';

if ($is_guest) {
    $href = G5_URL.'/login?'.$qstr.'&amp;url='.urlencode(get_pretty_url($bo_table, $wr_id));
    $href2 = str_replace('&amp;', '&', $href);
    echo <<<HEREDOC
    <script>
        alert('회원만 접근 가능합니다.');
        if (window.opener) {
            opener.location.href = '$href2';
            window.close();
        } else {
            top.location.href = '$href2';
        }
    </script>
    <noscript>
    <p>회원만 접근 가능합니다.</p>
    <a href="$href">로그인하기</a>
    </noscript>
HEREDOC;
    exit;
}

echo <<<HEREDOC
<script>
    if (window.name != 'win_scrap') {
        alert('올바른 방법으로 사용해 주십시오.');
        if (window.parent && window.parent !== window && window.parent.G5PopupLayer)
            window.parent.G5PopupLayer.close();
        else
            window.close();
    }
</script>
HEREDOC;

if ($write['wr_is_comment'])
    alert_close('코멘트는 스크랩 할 수 없습니다.');

$row = sql_pdo_fetch(" select count(*) as cnt from {$g5['scrap_table']}
            where mb_id = :mb_id
            and bo_table = :bo_table
            and wr_id = :wr_id ",
            [':mb_id' => $member['mb_id'], ':bo_table' => $bo_table, ':wr_id' => $wr_id]);
if ($row['cnt']) {

    $back_url = get_pretty_url($bo_table, $wr_id);

    echo <<<HEREDOC
    <script>
    if (confirm('이미 스크랩하신 글 입니다.\\n\\n지금 스크랩을 확인하시겠습니까?'))
        document.location.href = '{$scrap_url}';
    else if (window.parent && window.parent !== window && window.parent.G5PopupLayer)
        window.parent.G5PopupLayer.close();
    else
        window.close();
    </script>
    <noscript>
    <p>이미 스크랩하신 글 입니다.</p>
    <a href="{$scrap_url}">스크랩 확인하기</a>
    <a href="{$back_url}">돌아가기</a>
    </noscript>
HEREDOC;
    exit;
}

include_once($member_skin_path.'/scrap_popin.skin.php');

include_once(G5_PATH.'/tail.sub.php');
