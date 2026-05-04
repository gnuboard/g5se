<?php
include_once('./_common.php');

$act = isset($act) ? strip_tags($act) : '';
$count_chk_bo_table = (isset($_POST['chk_bo_table']) && is_array($_POST['chk_bo_table'])) ? count($_POST['chk_bo_table']) : 0;

// 게시판 관리자 이상 복사, 이동 가능
if ($is_admin != 'board' && $is_admin != 'group' && $is_admin != 'super')
    alert_close('게시판 관리자 이상 접근이 가능합니다.');

if ($sw != 'move' && $sw != 'copy')
    alert('sw 값이 제대로 넘어오지 않았습니다.');

if(! $count_chk_bo_table)
    alert('게시물을 '.$act.'할 게시판을 한개 이상 선택해 주십시오.', $url);

// 원본 파일 디렉토리
$src_dir = G5_DATA_PATH.'/file/'.$bo_table;

$save = array();
$save_count_write = 0;
$save_count_comment = 0;
$cnt = 0;

$wr_id_list = isset($_POST['wr_id_list']) ? preg_replace('/[^0-9\,]/', '', $_POST['wr_id_list']) : '';

// $wr_id_list 는 preg_replace 로 숫자/콤마만 남겨 안전 — IN 절 식별자 영역이라 보간 유지
$result = sql_pdo_query(" select distinct wr_num from $write_table where wr_id in ({$wr_id_list}) order by wr_id ");
while ($row = sql_fetch_array($result))
{
    $save[$cnt]['wr_contents'] = array();

    $wr_num = $row['wr_num'];
    for ($i=0; $i<$count_chk_bo_table; $i++)
    {
        $move_bo_table = isset($_POST['chk_bo_table'][$i]) ? preg_replace('/[^a-z0-9_]/i', '', $_POST['chk_bo_table'][$i]) : '';

        // 취약점 18-0075 참고 — $move_bo_table 은 이미 preg_replace 로 [a-z0-9_] 만 남겨 안전
        $move_board = sql_pdo_fetch("select * from {$g5['board_table']} where bo_table = :bo_table",
                                    [':bo_table' => $move_bo_table]);
        // 존재하지 않다면
        if( !$move_board['bo_table'] ) continue;

        $move_write_table = $g5['write_prefix'] . $move_bo_table;

        $src_dir = G5_DATA_PATH.'/file/'.$bo_table; // 원본 디렉토리
        $dst_dir = G5_DATA_PATH.'/file/'.$move_bo_table; // 복사본 디렉토리

        $count_write = 0;
        $count_comment = 0;

        // get_next_num 함수는 mysql 지연시 중복이 될수 있는 문제로 더 이상 사용하지 않습니다.
        // $next_wr_num = get_next_num($move_write_table);
        $next_wr_num = 0;

        $result2 = sql_pdo_query(" select * from $write_table where wr_num = :wr_num order by wr_parent, wr_is_comment, wr_comment desc, wr_id ",
                                 [':wr_num' => $wr_num]);
        while ($row2 = sql_fetch_array($result2))
        {
            $save[$cnt]['wr_contents'][] = $row2['wr_content'];

            $nick = cut_str($member['mb_nick'], $config['cf_cut_name']);
            if (!$row2['wr_is_comment'] && $config['cf_use_copy_log']) {
                if(strpos($row2['wr_option'], 'html') !== false) {
                    $log_tag1 = '<div class="content_'.$sw.'">';
                    $log_tag2 = '</div>';
                } else {
                    $log_tag1 = "\n";
                    $log_tag2 = '';
                }

                $row2['wr_content'] .= "\n".$log_tag1.'[이 게시물은 '.$nick.'님에 의해 '.G5_TIME_YMDHIS.' '.$board['bo_subject'].'에서 '.($sw == 'copy' ? '복사' : '이동').' 됨]'.$log_tag2;
            }

            // 게시글 추천, 비추천수
            $wr_good = $wr_nogood = 0;
            if ($sw == 'move' && $i == 0) {
                $wr_good = $row2['wr_good'];
                $wr_nogood = $row2['wr_nogood'];
            }

            // INSERT — $row2 는 DB 에서 fetch 한 값 (이미 unescaped) → addslashes 제거
            $insert_params = [
                ':wr_reply'         => $row2['wr_reply'],
                ':wr_is_comment'    => $row2['wr_is_comment'],
                ':wr_comment'       => $row2['wr_comment'],
                ':wr_comment_reply' => $row2['wr_comment_reply'],
                ':ca_name'          => $row2['ca_name'],
                ':wr_option'        => $row2['wr_option'],
                ':wr_subject'       => $row2['wr_subject'],
                ':wr_content'       => $row2['wr_content'],
                ':wr_link1'         => $row2['wr_link1'],
                ':wr_link2'         => $row2['wr_link2'],
                ':wr_link1_hit'     => $row2['wr_link1_hit'],
                ':wr_link2_hit'     => $row2['wr_link2_hit'],
                ':wr_hit'           => $row2['wr_hit'],
                ':wr_good'          => $wr_good,
                ':wr_nogood'        => $wr_nogood,
                ':mb_id'            => $row2['mb_id'],
                ':wr_password'      => $row2['wr_password'],
                ':wr_name'          => $row2['wr_name'],
                ':wr_email'         => $row2['wr_email'],
                ':wr_homepage'      => $row2['wr_homepage'],
                ':wr_datetime'      => $row2['wr_datetime'],
                ':wr_file'          => $row2['wr_file'],
                ':wr_last'          => $row2['wr_last'],
                ':wr_ip'            => $row2['wr_ip'],
                ':wr_1'  => $row2['wr_1'],  ':wr_2'  => $row2['wr_2'],
                ':wr_3'  => $row2['wr_3'],  ':wr_4'  => $row2['wr_4'],
                ':wr_5'  => $row2['wr_5'],  ':wr_6'  => $row2['wr_6'],
                ':wr_7'  => $row2['wr_7'],  ':wr_8'  => $row2['wr_8'],
                ':wr_9'  => $row2['wr_9'],  ':wr_10' => $row2['wr_10'],
            ];
            $wr_num_sql = $next_wr_num ? ':wr_num' : "(SELECT IFNULL(MIN(wr_num) - 1, -1) FROM $move_write_table as sq) ";
            if ($next_wr_num) $insert_params[':wr_num'] = $next_wr_num;

            sql_pdo_query(" insert into $move_write_table set
                wr_num = {$wr_num_sql},
                wr_reply = :wr_reply, wr_is_comment = :wr_is_comment,
                wr_comment = :wr_comment, wr_comment_reply = :wr_comment_reply,
                ca_name = :ca_name, wr_option = :wr_option,
                wr_subject = :wr_subject, wr_content = :wr_content,
                wr_link1 = :wr_link1, wr_link2 = :wr_link2,
                wr_link1_hit = :wr_link1_hit, wr_link2_hit = :wr_link2_hit,
                wr_hit = :wr_hit, wr_good = :wr_good, wr_nogood = :wr_nogood,
                mb_id = :mb_id, wr_password = :wr_password,
                wr_name = :wr_name, wr_email = :wr_email, wr_homepage = :wr_homepage,
                wr_datetime = :wr_datetime, wr_file = :wr_file,
                wr_last = :wr_last, wr_ip = :wr_ip,
                wr_1 = :wr_1, wr_2 = :wr_2, wr_3 = :wr_3, wr_4 = :wr_4, wr_5 = :wr_5,
                wr_6 = :wr_6, wr_7 = :wr_7, wr_8 = :wr_8, wr_9 = :wr_9, wr_10 = :wr_10 ",
                $insert_params);

            $insert_id = sql_insert_id();

            if ($next_wr_num === 0) {
                $tmp = sql_pdo_fetch("select wr_num from $move_write_table where wr_id = :wr_id",
                                     [':wr_id' => $insert_id]);
                $next_wr_num = $tmp['wr_num'];
            }

            // 코멘트가 아니라면
            if (!$row2['wr_is_comment'])
            {
                $save_parent = $insert_id;

                $result3 = sql_pdo_query(" select * from {$g5['board_file_table']} where bo_table = :bo_table and wr_id = :wr_id order by bf_no ",
                                         [':bo_table' => $bo_table, ':wr_id' => $row2['wr_id']]);
                for ($k=0; $row3 = sql_fetch_array($result3); $k++)
                {
                    $copy_file_name = '';
                    
                    if ($row3['bf_file'])
                    {
                        // 원본파일을 복사하고 퍼미션을 변경
                        // 제이프로님 코드제안 적용

                        $copy_file_name = $row3['bf_file'];

                        if($bo_table === $move_bo_table){
                            if(preg_match('/_copy(\d+)?_(\d+)_/', $copy_file_name, $match)){

                                $number = isset($match[1]) ? (int) $match[1] : 0;
                                $replace_str = '_copy'.($number + 1).'_'.$insert_id.'_';
                                $copy_file_name = preg_replace('/_copy(\d+)?_(\d+)_/', $replace_str, $copy_file_name);
                            } else {
                                $copy_file_name = $row2['wr_id'].'_copy_'.$insert_id.'_'.$row3['bf_file'];
                            }
                        }

                        $is_exist_file = is_file($src_dir.'/'.$row3['bf_file']) && file_exists($src_dir.'/'.$row3['bf_file']);
                        if( $is_exist_file ){
                            @copy($src_dir.'/'.$row3['bf_file'], $dst_dir.'/'.$copy_file_name);
                            @chmod($dst_dir.'/'.$row3['bf_file'], G5_FILE_PERMISSION);
                        }

                        $row3 = run_replace('bbs_move_update_file', $row3, $copy_file_name, $bo_table, $move_bo_table, $insert_id);
                    }

                    sql_pdo_query(" insert into {$g5['board_file_table']} set
                                bo_table = :bo_table, wr_id = :wr_id, bf_no = :bf_no,
                                bf_source = :bf_source, bf_file = :bf_file,
                                bf_download = :bf_download, bf_content = :bf_content,
                                bf_fileurl = :bf_fileurl, bf_thumburl = :bf_thumburl,
                                bf_storage = :bf_storage, bf_filesize = :bf_filesize,
                                bf_width = :bf_width, bf_height = :bf_height,
                                bf_type = :bf_type, bf_datetime = :bf_datetime ",
                        [
                            ':bo_table'    => $move_bo_table,
                            ':wr_id'       => $insert_id,
                            ':bf_no'       => $row3['bf_no'],
                            ':bf_source'   => $row3['bf_source'],
                            ':bf_file'     => $copy_file_name,
                            ':bf_download' => $row3['bf_download'],
                            ':bf_content'  => $row3['bf_content'],
                            ':bf_fileurl'  => $row3['bf_fileurl'],
                            ':bf_thumburl' => $row3['bf_thumburl'],
                            ':bf_storage'  => $row3['bf_storage'],
                            ':bf_filesize' => $row3['bf_filesize'],
                            ':bf_width'    => $row3['bf_width'],
                            ':bf_height'   => $row3['bf_height'],
                            ':bf_type'     => $row3['bf_type'],
                            ':bf_datetime' => $row3['bf_datetime'],
                        ]);

                    if ($sw == 'move' && $row3['bf_file'])
                        $save[$cnt]['bf_file'][$k] = $src_dir.'/'.$row3['bf_file'];
                }

                $count_write++;

                if ($sw == 'move' && $i == 0)
                {
                    $move_params = [':move_bo_table' => $move_bo_table, ':save_parent' => $save_parent,
                                    ':bo_table' => $bo_table, ':wr_id' => $row2['wr_id']];
                    // 스크랩 이동
                    sql_pdo_query(" update {$g5['scrap_table']} set bo_table = :move_bo_table, wr_id = :save_parent
                                    where bo_table = :bo_table and wr_id = :wr_id ", $move_params);

                    // 최신글 이동
                    sql_pdo_query(" update {$g5['board_new_table']} set bo_table = :move_bo_table, wr_id = :save_parent, wr_parent = :save_parent
                                    where bo_table = :bo_table and wr_id = :wr_id ", $move_params);

                    // 추천데이터 이동
                    sql_pdo_query(" update {$g5['board_good_table']} set bo_table = :move_bo_table, wr_id = :save_parent
                                    where bo_table = :bo_table and wr_id = :wr_id ", $move_params);
                }
            }
            else
            {
                $count_comment++;

                if ($sw == 'move')
                {
                    // 최신글 이동
                    sql_pdo_query(" update {$g5['board_new_table']} set bo_table = :move_bo_table, wr_id = :insert_id, wr_parent = :save_parent
                                    where bo_table = :bo_table and wr_id = :wr_id ",
                        [':move_bo_table' => $move_bo_table, ':insert_id' => $insert_id, ':save_parent' => $save_parent,
                         ':bo_table' => $bo_table, ':wr_id' => $row2['wr_id']]);
                }
            }

            sql_pdo_query(" update $move_write_table set wr_parent = :save_parent where wr_id = :insert_id ",
                          [':save_parent' => $save_parent, ':insert_id' => $insert_id]);

            if ($sw == 'move')
                $save[$cnt]['wr_id'] = $row2['wr_parent'];

            $cnt++;

            run_event('bbs_move_copy', $row2, $move_bo_table, $insert_id, $next_wr_num, $sw);
        }

        sql_pdo_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + :cw where bo_table = :bo_table ",
                      [':cw' => $count_write, ':bo_table' => $move_bo_table]);
        sql_pdo_query(" update {$g5['board_table']} set bo_count_comment = bo_count_comment + :cc where bo_table = :bo_table ",
                      [':cc' => $count_comment, ':bo_table' => $move_bo_table]);

        delete_cache_latest($move_bo_table);
    }

    $save_count_write += $count_write;
    $save_count_comment += $count_comment;
}

delete_cache_latest($bo_table);

if ($sw == 'move')
{
    $save_cnt = count($save);
    for ($i=0; $i<$save_cnt; $i++)
    {
        if( isset($save[$i]['bf_file']) && $save[$i]['bf_file'] ){
            $bf_file_cnt = count($save[$i]['bf_file']);
            for ($k=0; $k<$bf_file_cnt; $k++) {
                $del_file = run_replace('delete_file_path', clean_relative_paths($save[$i]['bf_file'][$k]), $save[$i]);

                if ( is_file($del_file) && file_exists($del_file) ){
                    @unlink($del_file);
                }
                
                // 썸네일 파일 삭제, 먼지손 님 코드 제안
                delete_board_thumbnail($bo_table, basename($save[$i]['bf_file'][$k]));
            }
        }
        
        $wr_contents_cnt = count($save[$i]['wr_contents']);
        for ($k=0; $k<$wr_contents_cnt; $k++){
            delete_editor_thumbnail($save[$i]['wr_contents'][$k]);
        }

        sql_pdo_query(" delete from $write_table where wr_parent = :wr_parent ",
                      [':wr_parent' => $save[$i]['wr_id']]);
        sql_pdo_query(" delete from {$g5['board_new_table']} where bo_table = :bo_table and wr_id = :wr_id ",
                      [':bo_table' => $bo_table, ':wr_id' => $save[$i]['wr_id']]);
        sql_pdo_query(" delete from {$g5['board_file_table']} where bo_table = :bo_table and wr_id = :wr_id ",
                      [':bo_table' => $bo_table, ':wr_id' => $save[$i]['wr_id']]);
    }

    // 공지사항이 이동되는 경우의 처리 begin
    $arr = array();
    $row = sql_pdo_fetch(" select bo_notice from {$g5['board_table']} where bo_table = :bo_table ",
                         [':bo_table' => $bo_table]);
    $arr_notice = explode(',', $row['bo_notice']);
    $arr_notice_cnt = count($arr_notice);
    for ($i=0; $i<$arr_notice_cnt; $i++) {
        $move_id = (int)$arr_notice[$i];
        // 게시판에 wr_id 가 있다면 이동한게 아니므로 bo_notice 에 다시 넣음
        $row2 = sql_pdo_fetch(" select count(*) as cnt from $write_table where wr_id = :wr_id ",
                              [':wr_id' => $move_id]);
        if ($row2['cnt']) {
            $arr[] = $move_id;
        }
        $bo_notice = implode(',', $arr);
    }
    // 공지사항이 이동되는 경우의 처리 end

    sql_pdo_query(" update {$g5['board_table']} set
                        bo_notice         = :bo_notice,
                        bo_count_write   = bo_count_write   - :scw,
                        bo_count_comment = bo_count_comment - :scc
                    where bo_table = :bo_table ",
                  [':bo_notice' => $bo_notice, ':scw' => $save_count_write, ':scc' => $save_count_comment, ':bo_table' => $bo_table]);
}

$msg = '해당 게시물을 선택한 게시판으로 '.$act.' 하였습니다.';
$opener_href  = get_pretty_url($bo_table,'','&amp;page='.$page.'&amp;'.$qstr);
$opener_href1 = str_replace('&amp;', '&', $opener_href);

run_event('bbs_move_update', $bo_table, $chk_bo_table, $wr_id_list, $opener_href);
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<script>
alert("<?php echo $msg; ?>");
opener.document.location.href = "<?php echo $opener_href1; ?>";
window.close();
</script>
<noscript>
<p>
    <?php echo $msg; ?>
</p>
<a href="<?php echo $opener_href; ?>">돌아가기</a>
</noscript>