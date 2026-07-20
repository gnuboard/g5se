<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$board_skin = $board['bo_skin'];
// 모던 스킨(자기 head 를 직접 그리는 스킨) 여부 판정.
//
// 'theme/basic' 이라는 스킨 값은 특정 테마가 아니라
// "현재 활성 테마(cf_theme)의 basic 스킨" 을 의미한다. (get_skin_path() 참고)
// basic 을 복사해 만든 테마도 스킨 값은 그대로 theme/basic 이고, 자기 테마의
// modern/_head.inc.php 로 상단을 직접 그리는 모던 스킨이다. 따라서 테마 이름('basic')이
// 아니라 모던 스킨의 실체 마커인 modern/_head.inc.php 존재 여부로 판정한다.
// 모던 인프라가 없는 테마의 클래식 theme/basic 스킨은 종전대로 기본 상단(_head.php)이 인클루드된다.
$is_modern_theme_board = ($board_skin === 'theme/basic' && is_file(G5_THEME_PATH.'/modern/_head.inc.php'));
$g5['board_content_head_html'] = '';
$g5['board_content_tail_html'] = '';

// 게시판 관리의 상단 내용
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

if (!$is_modern_theme_board) {
    echo $g5['board_content_head_html'];
}
