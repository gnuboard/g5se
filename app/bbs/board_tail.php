<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$board_skin = G5_IS_MOBILE ? $board['bo_mobile_skin'] : $board['bo_skin'];
$is_modern_theme_board = ($board_skin === 'theme/basic');
$g5['board_content_tail_html'] = isset($g5['board_content_tail_html']) ? $g5['board_content_tail_html'] : '';

// 게시판 관리의 하단 파일 경로
if (G5_IS_MOBILE) {
    if ($g5['board_content_tail_html'] === '') {
        $g5['board_content_tail_html'] = run_replace('board_mobile_content_tail', html_purifier(stripslashes($board['bo_mobile_content_tail'])), $board);
    }
    // 모바일의 경우 설정을 따르지 않는다.
    if (!$is_modern_theme_board) {
        echo $g5['board_content_tail_html'];
        include_once(G5_BBS_PATH.'/_tail.php');
    }
} else {
    if ($g5['board_content_tail_html'] === '') {
        $g5['board_content_tail_html'] = run_replace('board_content_tail', html_purifier(stripslashes($board['bo_content_tail'])), $board);
    }
    // 하단 파일 경로를 입력하지 않았다면 기본 하단 파일도 include 하지 않음
    if (!$is_modern_theme_board) {
        echo $g5['board_content_tail_html'];
    }
    if (!$is_modern_theme_board && trim($board['bo_include_tail'])) {
        if (is_include_path_check($board['bo_include_tail'])) {  //파일경로 체크
            @include ($board['bo_include_tail']);
        } else {    //파일경로가 올바르지 않으면 기본파일을 가져옴
            include_once(G5_BBS_PATH.'/_tail.php');
        }
    }
}
