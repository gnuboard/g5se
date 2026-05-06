<?php
$sub_menu = '300100';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

check_demo();

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$bo_table       = isset($_POST['bo_table']) ? substr(preg_replace('/[^a-z0-9_]/i', '', $_POST['bo_table']), 0, 20) : null;
$target_table   = isset($_POST['target_table']) ? trim($_POST['target_table']) : '';
$target_subject = isset($_POST['target_subject']) ? trim($_POST['target_subject']) : '';

$target_subject = strip_tags(clean_xss_attributes($target_subject));

$file_copy      = array();

if (empty($bo_table)) {
    alert("원본 테이블 정보가 없습니다.");
}

if (!preg_match('/[A-Za-z0-9_]{1,20}/', $target_table)) {
    alert('게시판 TABLE명은 공백없이 영문자, 숫자, _ 만 사용 가능합니다. (20자 이내)');
}

$target_table = substr(preg_replace('/[^a-z0-9_]/i', '', $target_table), 0, 20);

// 게시판명이 금지된 단어로 되어 있으면
if ($w == '' && in_array($target_table, get_bo_table_banned_word())) {
    alert('입력한 게시판 TABLE명을 사용할수 없습니다. 다른 이름으로 입력해 주세요.');
}

$row = sql_pdo_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = :target_table ",
                    [':target_table' => $target_table]);
if ($row['cnt']) {
    alert($target_table . '은(는) 이미 존재하는 게시판 테이블명 입니다.\\n복사할 테이블명으로 사용할 수 없습니다.');
}

// 게시판 테이블 생성 — table 명에 변수 들어가므로 (placeholder 불가) 영숫자 검증된 값만 보간
$sql = get_table_define($g5['write_prefix'] . $bo_table);
$sql = str_replace($g5['write_prefix'] . $bo_table, $g5['write_prefix'] . $target_table, $sql);
sql_query($sql, false);

// 구조만 복사시에는 공지사항 번호는 복사하지 않는다.
if ($copy_case == 'schema_only') {
    $board['bo_notice'] = '';
}

// 게시판 정보 — $board[*] 컬럼명을 그대로 named placeholder 로 매핑
$copy_cols = [
    'gr_id', 'bo_device', 'bo_admin',
    'bo_list_level', 'bo_read_level', 'bo_write_level', 'bo_reply_level', 'bo_comment_level',
    'bo_upload_level', 'bo_download_level', 'bo_html_level', 'bo_link_level',
    'bo_count_modify', 'bo_count_delete',
    'bo_read_point', 'bo_write_point', 'bo_comment_point', 'bo_download_point',
    'bo_use_category', 'bo_category_list', 'bo_use_sideview', 'bo_use_file_content',
    'bo_use_secret', 'bo_use_dhtml_editor', 'bo_use_rss_view', 'bo_use_good', 'bo_use_nogood',
    'bo_use_name', 'bo_use_signature', 'bo_use_ip_view', 'bo_use_list_view',
    'bo_use_list_content', 'bo_use_list_file',
    'bo_table_width', 'bo_subject_len', 'bo_mobile_subject_len', 'bo_page_rows', 'bo_mobile_page_rows',
    'bo_new', 'bo_hot', 'bo_image_width',
    'bo_skin', 'bo_mobile_skin', 'bo_include_head', 'bo_include_tail',
    'bo_content_head', 'bo_content_tail', 'bo_mobile_content_head', 'bo_mobile_content_tail',
    'bo_insert_content',
    'bo_gallery_cols', 'bo_gallery_width', 'bo_gallery_height',
    'bo_mobile_gallery_width', 'bo_mobile_gallery_height',
    'bo_upload_size', 'bo_reply_order', 'bo_use_search', 'bo_order', 'bo_notice', 'bo_upload_count',
    'bo_use_email', 'bo_use_cert', 'bo_use_sns', 'bo_use_captcha', 'bo_sort_field',
];
for ($i = 1; $i <= 10; $i++) {
    $copy_cols[] = 'bo_'.$i.'_subj';
    $copy_cols[] = 'bo_'.$i;
}

$set_parts = ['bo_table = :bo_table', 'bo_subject = :bo_subject'];
$insert_params = [':bo_table' => $target_table, ':bo_subject' => $target_subject];
foreach ($copy_cols as $col) {
    $set_parts[] = "$col = :$col";
    $insert_params[':'.$col] = isset($board[$col]) ? $board[$col] : '';
}
sql_pdo_query(" insert into {$g5['board_table']} set ".implode(', ', $set_parts)." ", $insert_params);

// 게시판 폴더 생성
@mkdir(G5_DATA_PATH . '/file/' . $target_table, G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH . '/file/' . $target_table, G5_DIR_PERMISSION);

// 디렉토리에 있는 파일의 목록을 보이지 않게 한다.
$board_path = G5_DATA_PATH . '/file/' . $target_table;
$file = $board_path . '/index.php';
$f = @fopen($file, 'w');
@fwrite($f, '');
@fclose($f);
@chmod($file, G5_FILE_PERMISSION);

$copy_file = 0;
if ($copy_case == 'schema_data_both') {
    $d = dir(G5_DATA_PATH . '/file/' . $bo_table);
    while ($entry = $d->read()) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // 김선용 201007 :
        if (is_dir(G5_DATA_PATH . '/file/' . $bo_table . '/' . $entry)) {
            $dd = dir(G5_DATA_PATH . '/file/' . $bo_table . '/' . $entry);
            @mkdir(G5_DATA_PATH . '/file/' . $target_table . '/' . $entry, G5_DIR_PERMISSION);
            @chmod(G5_DATA_PATH . '/file/' . $target_table . '/' . $entry, G5_DIR_PERMISSION);
            while ($entry2 = $dd->read()) {
                if ($entry2 == '.' || $entry2 == '..') {
                    continue;
                }
                @copy(G5_DATA_PATH . '/file/' . $bo_table . '/' . $entry . '/' . $entry2, G5_DATA_PATH . '/file/' . $target_table . '/' . $entry . '/' . $entry2);
                @chmod(G5_DATA_PATH . '/file/' . $target_table . '/' . $entry . '/' . $entry2, G5_DIR_PERMISSION);
                $copy_file++;
            }
            $dd->close();
        } else {
            @copy(G5_DATA_PATH . '/file/' . $bo_table . '/' . $entry, G5_DATA_PATH . '/file/' . $target_table . '/' . $entry);
            @chmod(G5_DATA_PATH . '/file/' . $target_table . '/' . $entry, G5_DIR_PERMISSION);
            $copy_file++;
        }
    }
    $d->close();

    run_event('admin_board_copy_file', $bo_table, $target_table);

    // 글복사 — 테이블명 보간 (영숫자 검증된 값)
    $sql = " insert into {$g5['write_prefix']}$target_table select * from {$g5['write_prefix']}$bo_table ";
    sql_query($sql, false);

    // 게시글수 저장
    $row = sql_pdo_fetch(" select bo_count_write, bo_count_comment from {$g5['board_table']} where bo_table = :bo_table ",
                        [':bo_table' => $bo_table]);
    sql_pdo_query(" update {$g5['board_table']} set bo_count_write = :bo_count_write, bo_count_comment = :bo_count_comment where bo_table = :target_table ",
                  [':bo_count_write' => $row['bo_count_write'], ':bo_count_comment' => $row['bo_count_comment'], ':target_table' => $target_table]);

    // 4.00.01
    $stmt = sql_pdo_query(" select * from {$g5['board_file_table']} where bo_table = :bo_table ", [':bo_table' => $bo_table], false);
    for ($i = 0; $stmt && ($row = sql_fetch_array($stmt)); $i++) {
        $file_copy[$i] = $row;
    }
}

if (count($file_copy)) {
    for ($i = 0; $i < count($file_copy); $i++) {
        $file_copy[$i] = run_replace('admin_copy_update_file', $file_copy[$i], $file_copy[$i]['bf_file'], $bo_table, $target_table);

        sql_pdo_query(" insert into {$g5['board_file_table']}
                           set bo_table = :bo_table, wr_id = :wr_id, bf_no = :bf_no,
                               bf_source = :bf_source, bf_file = :bf_file, bf_download = :bf_download,
                               bf_content = :bf_content, bf_fileurl = :bf_fileurl, bf_thumburl = :bf_thumburl,
                               bf_storage = :bf_storage, bf_filesize = :bf_filesize,
                               bf_width = :bf_width, bf_height = :bf_height,
                               bf_type = :bf_type, bf_datetime = :bf_datetime ",
                      [
                          ':bo_table'    => $target_table,
                          ':wr_id'       => $file_copy[$i]['wr_id'],
                          ':bf_no'       => $file_copy[$i]['bf_no'],
                          ':bf_source'   => $file_copy[$i]['bf_source'],
                          ':bf_file'     => $file_copy[$i]['bf_file'],
                          ':bf_download' => $file_copy[$i]['bf_download'],
                          ':bf_content'  => $file_copy[$i]['bf_content'],
                          ':bf_fileurl'  => $file_copy[$i]['bf_fileurl'],
                          ':bf_thumburl' => $file_copy[$i]['bf_thumburl'],
                          ':bf_storage'  => $file_copy[$i]['bf_storage'],
                          ':bf_filesize' => $file_copy[$i]['bf_filesize'],
                          ':bf_width'    => $file_copy[$i]['bf_width'],
                          ':bf_height'   => $file_copy[$i]['bf_height'],
                          ':bf_type'     => $file_copy[$i]['bf_type'],
                          ':bf_datetime' => $file_copy[$i]['bf_datetime'],
                      ], false);
    }
}

delete_cache_latest($bo_table);
delete_cache_latest($target_table);

echo "<script>opener.document.location.reload();</script>";

alert("복사에 성공 했습니다.", G5_ADMIN_URL.'/board_copy?bo_table=' . $bo_table . '&amp;' . $qstr);
