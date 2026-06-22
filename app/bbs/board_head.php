<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$board_skin = G5_IS_MOBILE ? $board['bo_mobile_skin'] : $board['bo_skin'];
// 모던 스킨(자기 head 를 직접 그리는 basic 테마의 스킨) 여부 판정.
//
// 'theme/basic' 이라는 스킨 값은 특정 테마를 가리키는 게 아니라
// "현재 활성 테마(cf_theme)의 basic 스킨" 을 의미한다. (get_skin_path() 참고)
// 따라서 스킨 이름만으로 판정하면 basic 이 아닌 다른 테마(예: mytheme)의
// theme/basic 스킨까지 모던 스킨으로 잘못 잡혀 기본 상단(_head.php)이 출력되지 않는다.
//
// 자기 head 를 직접 그리는 것은 'basic' 테마뿐이므로, 활성 테마가 실제 'basic' 일 때만
// 모던 스킨으로 취급한다. 그 외 테마의 theme/basic 스킨은 일반 스킨처럼 _head.php 가 인클루드된다.
$is_modern_theme_board = ($board_skin === 'theme/basic' && trim($config['cf_theme']) === 'basic');
$g5['board_content_head_html'] = '';
$g5['board_content_tail_html'] = '';

// 게시판 관리의 상단 내용
if (G5_IS_MOBILE) {
    // 모바일의 경우 설정을 따르지 않는다.
    if (!$is_modern_theme_board) {
        include_once(G5_BBS_PATH.'/_head.php');
    }
    $g5['board_content_head_html'] = run_replace('board_mobile_content_head', html_purifier(stripslashes($board['bo_mobile_content_head'])), $board);
    $g5['board_content_tail_html'] = run_replace('board_mobile_content_tail', html_purifier(stripslashes($board['bo_mobile_content_tail'])), $board);
} else {
    // 상단 파일 경로를 입력하지 않았다면 기본 상단 파일도 include 하지 않음
    if (!$is_modern_theme_board && trim($board['bo_include_head'])) {
        if (is_include_path_check($board['bo_include_head'])) {  //파일경로 체크
            @include ($board['bo_include_head']);
        } else {    //파일경로가 올바르지 않으면 기본파일을 가져옴
            include_once(G5_BBS_PATH.'/_head.php');
        }
    }
    $g5['board_content_head_html'] = run_replace('board_content_head', html_purifier(stripslashes($board['bo_content_head'])), $board);
    $g5['board_content_tail_html'] = run_replace('board_content_tail', html_purifier(stripslashes($board['bo_content_tail'])), $board);
}

if (!$is_modern_theme_board) {
    echo $g5['board_content_head_html'];
}
