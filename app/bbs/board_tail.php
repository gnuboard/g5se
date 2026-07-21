<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$board_skin = $board['bo_skin'];
// 활성 테마의 게시판 스킨(theme/*)이 모던 레이아웃을 직접 출력하는지 판정한다.
// 복사하거나 추가한 테마 스킨도 동일하게 기존 하단 출력을 생략해야 한다.
$is_modern_theme_board = (strpos($board_skin, 'theme/') === 0
    && is_file(G5_THEME_PATH.'/modern/_head.inc.php'));
$g5['board_content_tail_html'] = isset($g5['board_content_tail_html']) ? $g5['board_content_tail_html'] : '';

// 게시판 관리의 하단 파일 경로
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
