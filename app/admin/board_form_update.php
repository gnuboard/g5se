<?php
$sub_menu = "300100";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if ($w == 'u') {
    check_demo();
}

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$gr_id              = isset($_POST['gr_id']) ? preg_replace('/[^a-z0-9_]/i', '', (string)$_POST['gr_id']) : '';
$bo_admin           = isset($_POST['bo_admin']) ? preg_replace('/[^a-z0-9_\, \|\#]/i', '', $_POST['bo_admin']) : '';
$bo_subject         = isset($_POST['bo_subject']) ? strip_tags(clean_xss_attributes($_POST['bo_subject'])) : '';

if (!$gr_id) {
    alert('그룹 ID는 반드시 선택하세요.');
}
if (!$bo_table) {
    alert('게시판 TABLE명은 반드시 입력하세요.');
}
if (!preg_match("/^([A-Za-z0-9_]{1,20})$/", $bo_table)) {
    alert('게시판 TABLE명은 공백없이 영문자, 숫자, _ 만 사용 가능합니다. (20자 이내)');
}
if (!$bo_subject) {
    alert('게시판 제목을 입력하세요.');
}

// 게시판명이 금지된 단어로 되어 있으면
if ($w == '' && in_array($bo_table, get_bo_table_banned_word())) {
    alert('입력한 게시판 TABLE명을 사용할수 없습니다. 다른 이름으로 입력해 주세요.');
}

$bo_include_head = isset($_POST['bo_include_head']) ? preg_replace(array("#[\\\]+$#", "#(<\?php|<\?)#i"), "", substr($_POST['bo_include_head'], 0, 255)) : '';
$bo_include_tail = isset($_POST['bo_include_tail']) ? preg_replace(array("#[\\\]+$#", "#(<\?php|<\?)#i"), "", substr($_POST['bo_include_tail'], 0, 255)) : '';

$check_captcha = false;

// 관리자가 자동등록방지 CAPTCHA를 사용해야 할 경우
// 최고 관리자인 경우에만 수정가능
if ($is_admin === 'super') {
    if ($w === 'u') {
        if (isset($board['bo_include_head'], $board['bo_include_tail']) &&
            ($board['bo_include_head'] !== $bo_include_head || $board['bo_include_tail'] !== $bo_include_tail)) {
            $check_captcha = true;
        }
    } elseif ($w === '') {
        if ($bo_include_head !== '_head.php' || $bo_include_tail !== '_tail.php') {
            $check_captcha = true;
        }
    }
}

// 실제 CAPTCHA 검증
if ($check_captcha) {
    include_once(G5_CAPTCHA_PATH . '/captcha.lib.php');
    
    if (!chk_captcha()) {
        alert('자동등록방지 숫자가 틀렸습니다.');
    }
}

if ($file = $bo_include_head) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);

    if (!$file_ext || !in_array($file_ext, array('php', 'htm', 'html')) || !preg_match('/^.*\.(php|htm|html)$/i', $file)) {
        alert('상단 파일 경로의 확장자는 php, htm, html 만 허용합니다.');
    }
}

if ($file = $bo_include_tail) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);

    if (!$file_ext || !in_array($file_ext, array('php', 'htm', 'html')) || !preg_match('/^.*\.(php|htm|html)$/i', $file)) {
        alert('하단 파일 경로의 확장자는 php, htm, html 만 허용합니다.');
    }
}

if (!is_include_path_check($bo_include_head, 1)) {
    alert('상단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

if (!is_include_path_check($bo_include_tail, 1)) {
    alert('하단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

if (function_exists('filter_input_include_path')) {
    $bo_include_head = filter_input_include_path($bo_include_head);
    $bo_include_tail = filter_input_include_path($bo_include_tail);
}

$board_path = G5_DATA_PATH . '/file/' . $bo_table;

// 게시판 디렉토리 생성
@mkdir($board_path, G5_DIR_PERMISSION);
@chmod($board_path, G5_DIR_PERMISSION);

// 디렉토리에 있는 파일의 목록을 보이지 않게 한다.
$file = $board_path . '/index.php';
if ($f = @fopen($file, 'w')) {
    @fwrite($f, '');
    @fclose($f);
    @chmod($file, G5_FILE_PERMISSION);
}

// 분류에 & 나 = 는 사용이 불가하므로 2바이트로 바꾼다.
$src_char = array('&', '=');
$dst_char = array('＆', '〓');
$bo_category_list = isset($_POST['bo_category_list']) ? str_replace($src_char, $dst_char, $_POST['bo_category_list']) : '';
//https://github.com/gnuboard/gnuboard5/commit/f5f4925d4eb28ba1af728e1065fc2bdd9ce1da58 에 따른 조치
$str_bo_category_list = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", (string)$bo_category_list);

$bo_use_category = isset($_POST['bo_use_category']) ? (int) $_POST['bo_use_category'] : 0;
$bo_use_sideview = isset($_POST['bo_use_sideview']) ? (int) $_POST['bo_use_sideview'] : 0;
$bo_use_dhtml_editor = isset($_POST['bo_use_dhtml_editor']) ? (int) $_POST['bo_use_dhtml_editor'] : 0;
$bo_use_good = isset($_POST['bo_use_good']) ? (int) $_POST['bo_use_good'] : 0;
$bo_use_nogood = isset($_POST['bo_use_nogood']) ? (int) $_POST['bo_use_nogood'] : 0;
$bo_use_name = isset($_POST['bo_use_name']) ? (int) $_POST['bo_use_name'] : 0;
$bo_use_signature = isset($_POST['bo_use_signature']) ? (int) $_POST['bo_use_signature'] : 0;
$bo_use_ip_view = isset($_POST['bo_use_ip_view']) ? (int) $_POST['bo_use_ip_view'] : 0;
$bo_use_list_view = isset($_POST['bo_use_list_view']) ? (int) $_POST['bo_use_list_view'] : 0;
$bo_use_list_file = isset($_POST['bo_use_list_file']) ? (int) $_POST['bo_use_list_file'] : 0;
$bo_use_list_content = isset($_POST['bo_use_list_content']) ? (int) $_POST['bo_use_list_content'] : 0;
$bo_use_email = isset($_POST['bo_use_email']) ? (int) $_POST['bo_use_email'] : 0;
$bo_use_sns = isset($_POST['bo_use_sns']) ? (int) $_POST['bo_use_sns'] : 0;
$bo_use_captcha = isset($_POST['bo_use_captcha']) ? (int) $_POST['bo_use_captcha'] : 0;
$bo_table_width = isset($_POST['bo_table_width']) ? (int) $_POST['bo_table_width'] : 0;
$bo_subject_len = isset($_POST['bo_subject_len']) ? (int) $_POST['bo_subject_len'] : 0;
$bo_page_rows = isset($_POST['bo_page_rows']) ? (int) $_POST['bo_page_rows'] : 0;
$bo_use_rss_view = isset($_POST['bo_use_rss_view']) ? (int) $_POST['bo_use_rss_view'] : 0;
$bo_use_secret = isset($_POST['bo_use_secret']) ? (int) $_POST['bo_use_secret'] : 0;
$bo_use_file_content = isset($_POST['bo_use_file_content']) ? (int) $_POST['bo_use_file_content'] : 0;
$bo_new = isset($_POST['bo_new']) ? (int) $_POST['bo_new'] : 0;
$bo_hot = isset($_POST['bo_hot']) ? (int) $_POST['bo_hot'] : 0;
$bo_image_width = isset($_POST['bo_image_width']) ? (int) $_POST['bo_image_width'] : 0;
$bo_use_search = isset($_POST['bo_use_search']) ? (int) $_POST['bo_use_search'] : 0;
$bo_use_cert = isset($_POST['bo_use_cert']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['bo_use_cert']) : '';
$bo_list_level = isset($_POST['bo_list_level']) ? (int) $_POST['bo_list_level'] : 0;
$bo_read_level = isset($_POST['bo_read_level']) ? (int) $_POST['bo_read_level'] : 0;
$bo_write_level = isset($_POST['bo_write_level']) ? (int) $_POST['bo_write_level'] : 0;
$bo_reply_level = isset($_POST['bo_reply_level']) ? (int) $_POST['bo_reply_level'] : 0;
$bo_comment_level = isset($_POST['bo_comment_level']) ? (int) $_POST['bo_comment_level'] : 0;
$bo_html_level = isset($_POST['bo_html_level']) ? (int) $_POST['bo_html_level'] : 0;
$bo_link_level = isset($_POST['bo_link_level']) ? (int) $_POST['bo_link_level'] : 0;
$bo_count_modify = isset($_POST['bo_count_modify']) ? (int) $_POST['bo_count_modify'] : 0;
$bo_count_delete = isset($_POST['bo_count_delete']) ? (int) $_POST['bo_count_delete'] : 0;
$bo_upload_level = isset($_POST['bo_upload_level']) ? (int) $_POST['bo_upload_level'] : 0;
$bo_download_level = isset($_POST['bo_download_level']) ? (int) $_POST['bo_download_level'] : 0;
$bo_read_point = isset($_POST['bo_read_point']) ? (int) $_POST['bo_read_point'] : 0;
$bo_write_point = isset($_POST['bo_write_point']) ? (int) $_POST['bo_write_point'] : 0;
$bo_comment_point = isset($_POST['bo_comment_point']) ? (int) $_POST['bo_comment_point'] : 0;
$bo_download_point = isset($_POST['bo_download_point']) ? (int) $_POST['bo_download_point'] : 0;
$bo_select_editor = isset($_POST['bo_select_editor']) ? clean_xss_tags(stripslashes($_POST['bo_select_editor']), 1, 1) : '';
$bo_skin = isset($_POST['bo_skin']) ? clean_xss_tags(stripslashes($_POST['bo_skin']), 1, 1) : '';
$bo_content_head = isset($_POST['bo_content_head']) ? stripslashes($_POST['bo_content_head']) : '';
$bo_content_tail = isset($_POST['bo_content_tail']) ? stripslashes($_POST['bo_content_tail']) : '';
$bo_insert_content = isset($_POST['bo_insert_content']) ? stripslashes($_POST['bo_insert_content']) : '';
$bo_gallery_cols = isset($_POST['bo_gallery_cols']) ? (int) $_POST['bo_gallery_cols'] : 0;
$bo_gallery_width = isset($_POST['bo_gallery_width']) ? (int) $_POST['bo_gallery_width'] : 0;
$bo_gallery_height = isset($_POST['bo_gallery_height']) ? (int) $_POST['bo_gallery_height'] : 0;
$bo_upload_count = isset($_POST['bo_upload_count']) ? (int) $_POST['bo_upload_count'] : 0;
$bo_upload_size = isset($_POST['bo_upload_size']) ? (int) $_POST['bo_upload_size'] : 0;
$bo_reply_order = isset($_POST['bo_reply_order']) ? (int) $_POST['bo_reply_order'] : 0;
$bo_order = isset($_POST['bo_order']) ? (int) $_POST['bo_order'] : 0;
$bo_write_min = isset($_POST['bo_write_min']) ? (int) $_POST['bo_write_min'] : 0;
$bo_write_max = isset($_POST['bo_write_max']) ? (int) $_POST['bo_write_max'] : 0;
$bo_comment_min = isset($_POST['bo_comment_min']) ? (int) $_POST['bo_comment_min'] : 0;
$bo_comment_max = isset($_POST['bo_comment_max']) ? (int) $_POST['bo_comment_max'] : 0;
// 정렬 필드는 허용 목록(관리자 드롭다운과 동일)으로만 저장 — 목록 조회 ORDER BY 절에 임의 표현식 유입 차단
$bo_sort_field = isset($_POST['bo_sort_field']) ? trim(stripslashes($_POST['bo_sort_field'])) : '';
$bo_allowed_sort_field = array('');
if (function_exists('get_board_sort_fields')) {
    foreach (get_board_sort_fields(isset($board) ? $board : array()) as $bo_sort_v) {
        $bo_allowed_sort_field[] = $bo_sort_v[0];
    }
}
if (!in_array($bo_sort_field, $bo_allowed_sort_field, true)) {
    $bo_sort_field = '';
}

if (strpbrk($bo_skin, "?%*:|\"<>") !== false) {
    alert('스킨 디렉토리명 오류!');
}

$etcs = array();

for ($i = 1; $i <= 10; $i++) {
    $etcs['bo_' . $i . '_subj'] = ${'bo_' . $i . '_subj'} = isset($_POST['bo_' . $i . '_subj']) ? $_POST['bo_' . $i . '_subj'] : '';
    $etcs['bo_' . $i] = ${'bo_' . $i} = isset($_POST['bo_' . $i]) ? $_POST['bo_' . $i] : '';
}

$sql_common = " gr_id               = :gr_id,
                bo_subject          = :bo_subject,
                bo_admin            = :bo_admin,
                bo_list_level       = :bo_list_level,
                bo_read_level       = :bo_read_level,
                bo_write_level      = :bo_write_level,
                bo_reply_level      = :bo_reply_level,
                bo_comment_level    = :bo_comment_level,
                bo_html_level       = :bo_html_level,
                bo_link_level       = :bo_link_level,
                bo_count_modify     = :bo_count_modify,
                bo_count_delete     = :bo_count_delete,
                bo_upload_level     = :bo_upload_level,
                bo_download_level   = :bo_download_level,
                bo_read_point       = :bo_read_point,
                bo_write_point      = :bo_write_point,
                bo_comment_point    = :bo_comment_point,
                bo_download_point   = :bo_download_point,
                bo_use_category     = :bo_use_category,
                bo_category_list    = :bo_category_list,
                bo_use_sideview     = :bo_use_sideview,
                bo_use_file_content = :bo_use_file_content,
                bo_use_secret       = :bo_use_secret,
                bo_use_dhtml_editor = :bo_use_dhtml_editor,
                bo_select_editor    = :bo_select_editor,
                bo_use_rss_view     = :bo_use_rss_view,
                bo_use_good         = :bo_use_good,
                bo_use_nogood       = :bo_use_nogood,
                bo_use_name         = :bo_use_name,
                bo_use_signature    = :bo_use_signature,
                bo_use_ip_view      = :bo_use_ip_view,
                bo_use_list_view    = :bo_use_list_view,
                bo_use_list_file    = :bo_use_list_file,
                bo_use_list_content = :bo_use_list_content,
                bo_use_email        = :bo_use_email,
                bo_use_cert         = :bo_use_cert,
                bo_use_sns          = :bo_use_sns,
                bo_use_captcha      = :bo_use_captcha,
                bo_table_width      = :bo_table_width,
                bo_subject_len      = :bo_subject_len,
                bo_page_rows        = :bo_page_rows,
                bo_new              = :bo_new,
                bo_hot              = :bo_hot,
                bo_image_width      = :bo_image_width,
                bo_skin             = :bo_skin
                ";

$common_params = [
    ':gr_id'                 => $gr_id,
    ':bo_subject'            => $bo_subject,
    ':bo_admin'              => $bo_admin,
    ':bo_list_level'         => $bo_list_level,
    ':bo_read_level'         => $bo_read_level,
    ':bo_write_level'        => $bo_write_level,
    ':bo_reply_level'        => $bo_reply_level,
    ':bo_comment_level'      => $bo_comment_level,
    ':bo_html_level'         => $bo_html_level,
    ':bo_link_level'         => $bo_link_level,
    ':bo_count_modify'       => $bo_count_modify,
    ':bo_count_delete'       => $bo_count_delete,
    ':bo_upload_level'       => $bo_upload_level,
    ':bo_download_level'     => $bo_download_level,
    ':bo_read_point'         => $bo_read_point,
    ':bo_write_point'        => $bo_write_point,
    ':bo_comment_point'      => $bo_comment_point,
    ':bo_download_point'     => $bo_download_point,
    ':bo_use_category'       => $bo_use_category,
    ':bo_category_list'      => $str_bo_category_list,
    ':bo_use_sideview'       => $bo_use_sideview,
    ':bo_use_file_content'   => $bo_use_file_content,
    ':bo_use_secret'         => $bo_use_secret,
    ':bo_use_dhtml_editor'   => $bo_use_dhtml_editor,
    ':bo_select_editor'      => $bo_select_editor,
    ':bo_use_rss_view'       => $bo_use_rss_view,
    ':bo_use_good'           => $bo_use_good,
    ':bo_use_nogood'         => $bo_use_nogood,
    ':bo_use_name'           => $bo_use_name,
    ':bo_use_signature'      => $bo_use_signature,
    ':bo_use_ip_view'        => $bo_use_ip_view,
    ':bo_use_list_view'      => $bo_use_list_view,
    ':bo_use_list_file'      => $bo_use_list_file,
    ':bo_use_list_content'   => $bo_use_list_content,
    ':bo_use_email'          => $bo_use_email,
    ':bo_use_cert'           => $bo_use_cert,
    ':bo_use_sns'            => $bo_use_sns,
    ':bo_use_captcha'        => $bo_use_captcha,
    ':bo_table_width'        => $bo_table_width,
    ':bo_subject_len'        => $bo_subject_len,
    ':bo_page_rows'          => $bo_page_rows,
    ':bo_new'                => $bo_new,
    ':bo_hot'                => $bo_hot,
    ':bo_image_width'        => $bo_image_width,
    ':bo_skin'               => $bo_skin,
];

// 최고 관리자인 경우에만 수정가능
if ($is_admin === 'super') {
    $sql_common .= " , bo_include_head        = :bo_include_head,
                      bo_include_tail        = :bo_include_tail,
                      bo_content_head        = :bo_content_head,
                      bo_content_tail        = :bo_content_tail
                    ";
    $common_params[':bo_include_head']        = $bo_include_head;
    $common_params[':bo_include_tail']        = $bo_include_tail;
    $common_params[':bo_content_head']        = $bo_content_head;
    $common_params[':bo_content_tail']        = $bo_content_tail;
}

$sql_common .= " , bo_insert_content   = :bo_insert_content,
                  bo_gallery_cols     = :bo_gallery_cols,
                  bo_gallery_width    = :bo_gallery_width,
                  bo_gallery_height   = :bo_gallery_height,
                  bo_upload_count     = :bo_upload_count,
                  bo_upload_size      = :bo_upload_size,
                  bo_reply_order      = :bo_reply_order,
                  bo_use_search       = :bo_use_search,
                  bo_order            = :bo_order,
                  bo_write_min        = :bo_write_min,
                  bo_write_max        = :bo_write_max,
                  bo_comment_min      = :bo_comment_min,
                  bo_comment_max      = :bo_comment_max,
                  bo_sort_field       = :bo_sort_field,
                  bo_1_subj = :bo_1_subj, bo_2_subj = :bo_2_subj, bo_3_subj = :bo_3_subj, bo_4_subj = :bo_4_subj, bo_5_subj = :bo_5_subj,
                  bo_6_subj = :bo_6_subj, bo_7_subj = :bo_7_subj, bo_8_subj = :bo_8_subj, bo_9_subj = :bo_9_subj, bo_10_subj = :bo_10_subj,
                  bo_1 = :bo_1, bo_2 = :bo_2, bo_3 = :bo_3, bo_4 = :bo_4, bo_5 = :bo_5,
                  bo_6 = :bo_6, bo_7 = :bo_7, bo_8 = :bo_8, bo_9 = :bo_9, bo_10 = :bo_10 ";

$common_params[':bo_insert_content']       = $bo_insert_content;
$common_params[':bo_gallery_cols']         = $bo_gallery_cols;
$common_params[':bo_gallery_width']        = $bo_gallery_width;
$common_params[':bo_gallery_height']       = $bo_gallery_height;
$common_params[':bo_upload_count']         = $bo_upload_count;
$common_params[':bo_upload_size']          = $bo_upload_size;
$common_params[':bo_reply_order']          = $bo_reply_order;
$common_params[':bo_use_search']           = $bo_use_search;
$common_params[':bo_order']                = $bo_order;
$common_params[':bo_write_min']            = $bo_write_min;
$common_params[':bo_write_max']            = $bo_write_max;
$common_params[':bo_comment_min']          = $bo_comment_min;
$common_params[':bo_comment_max']          = $bo_comment_max;
$common_params[':bo_sort_field']           = $bo_sort_field;
for ($i = 1; $i <= 10; $i++) {
    $common_params[':bo_'.$i.'_subj'] = $etcs['bo_'.$i.'_subj'];
    $common_params[':bo_'.$i]         = $etcs['bo_'.$i];
}

if ($w == '') {
    $row = sql_pdo_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = :bo_table ",
                        [':bo_table' => $bo_table]);
    if ($row['cnt']) {
        alert($bo_table . ' 은(는) 이미 존재하는 TABLE 입니다.');
    }

    $insert_params = array_merge($common_params, [
        ':bo_table' => $bo_table,
    ]);
    sql_pdo_query(" insert into {$g5['board_table']}
                       set bo_table = :bo_table,
                           bo_count_write = '0',
                           bo_count_comment = '0',
                           $sql_common ",
                  $insert_params);

    // 게시판 테이블 생성
    $file = file('./sql_write.sql');
    $file = get_db_create_replace($file);

    $sql = implode("\n", $file);

    $create_table = $g5['write_prefix'] . $bo_table;

    // sql_board.sql 파일의 테이블명을 변환
    $source = array('/__TABLE_NAME__/', '/;/');
    $target = array($create_table, '');
    $sql = preg_replace($source, $target, $sql);
    sql_query($sql, false);
} elseif ($w == 'u') {
    // 게시판의 글/코멘트 수 — 테이블명에 변수가 들어가므로 (placeholder 불가) bo_table 검증된 영숫자만 보간
    $row = sql_fetch(" select count(*) as cnt from {$g5['write_prefix']}{$bo_table} where wr_is_comment = 0 ");
    $bo_count_write = $row['cnt'];

    $row = sql_fetch(" select count(*) as cnt from {$g5['write_prefix']}{$bo_table} where wr_is_comment = 1 ");
    $bo_count_comment = $row['cnt'];

    // 글수 조정
    /*
        엔피씨님의 팁으로 교체합니다. 130308
        http://sir.kr/g5_tiptech/27207
    */
    if (isset($_POST['proc_count'])) {
        $sql = " select a.wr_id, (count(b.wr_parent) - 1) as cnt from {$g5['write_prefix']}{$bo_table} a, {$g5['write_prefix']}{$bo_table} b where a.wr_id=b.wr_parent and a.wr_is_comment=0 group by a.wr_id ";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            sql_pdo_query(" update {$g5['write_prefix']}{$bo_table} set wr_comment = :cnt where wr_id = :wr_id ",
                          [':cnt' => $row['cnt'], ':wr_id' => $row['wr_id']]);
        }
    }

    // 공지사항에는 등록되어 있지만 실제 존재하지 않는 글 아이디는 삭제합니다.
    $bo_notice = "";
    $lf = "";
    if ($board['bo_notice']) {
        $tmp_array = explode(",", $board['bo_notice']);
        for ($i = 0; $i < count($tmp_array); $i++) {
            $tmp_wr_id = trim($tmp_array[$i]);
            $row = sql_pdo_fetch(" select count(*) as cnt from {$g5['write_prefix']}{$bo_table} where wr_id = :wr_id ",
                                [':wr_id' => $tmp_wr_id]);
            if ($row['cnt']) {
                $bo_notice .= $lf . $tmp_wr_id;
                $lf = ",";
            }
        }
    }

    $update_params = array_merge($common_params, [
        ':bo_notice'        => $bo_notice,
        ':bo_count_write'   => $bo_count_write,
        ':bo_count_comment' => $bo_count_comment,
        ':bo_table'         => $bo_table,
    ]);
    sql_pdo_query(" update {$g5['board_table']}
                       set bo_notice = :bo_notice,
                           bo_count_write = :bo_count_write,
                           bo_count_comment = :bo_count_comment,
                           {$sql_common}
                     where bo_table = :bo_table ",
                  $update_params);
}


// g5se: chk_grp_* / chk_all_* 동일 옵션 적용을 placeholder/params 로 빌드.
// $scope = 'grp' | 'all' — 양쪽 동일 매핑이므로 한 helper 로 처리.
$build_scope = function($scope) use (
    $bo_admin, $bo_list_level, $bo_read_level, $bo_write_level, $bo_reply_level,
    $bo_comment_level, $bo_link_level, $bo_upload_level, $bo_download_level, $bo_html_level,
    $bo_count_modify, $bo_count_delete, $bo_read_point, $bo_write_point, $bo_comment_point,
    $bo_download_point, $str_bo_category_list, $bo_use_category, $bo_use_sideview,
    $bo_use_file_content, $bo_use_secret, $bo_use_dhtml_editor, $bo_select_editor,
    $bo_use_rss_view, $bo_use_good, $bo_use_nogood, $bo_use_name, $bo_use_signature,
    $bo_use_ip_view, $bo_use_list_view, $bo_use_list_file, $bo_use_list_content,
    $bo_use_email, $bo_use_cert, $bo_use_sns, $bo_use_captcha, $bo_skin,
    $bo_gallery_cols, $bo_gallery_width, $bo_gallery_height,
    $bo_table_width, $bo_page_rows,
    $bo_subject_len, $bo_new, $bo_hot,
    $bo_image_width, $bo_reply_order, $bo_sort_field, $bo_write_min, $bo_write_max,
    $bo_comment_min, $bo_comment_max, $bo_upload_count, $bo_upload_size,
    $is_admin, $bo_include_head, $bo_include_tail, $bo_content_head, $bo_content_tail,
    $bo_insert_content, $bo_use_search,
    $bo_order, $etcs
) {
    $fields = '';
    $params = [];
    $add = function($col, $val) use ($scope, &$fields, &$params) {
        $key = ':'.$scope.'_'.$col;
        $fields .= " , $col = $key ";
        $params[$key] = $val;
    };

    if (is_checked('chk_'.$scope.'_admin'))                $add('bo_admin',            $bo_admin);
    if (is_checked('chk_'.$scope.'_list_level'))           $add('bo_list_level',       $bo_list_level);
    if (is_checked('chk_'.$scope.'_read_level'))           $add('bo_read_level',       $bo_read_level);
    if (is_checked('chk_'.$scope.'_write_level'))          $add('bo_write_level',      $bo_write_level);
    if (is_checked('chk_'.$scope.'_reply_level'))          $add('bo_reply_level',      $bo_reply_level);
    if (is_checked('chk_'.$scope.'_comment_level'))        $add('bo_comment_level',    $bo_comment_level);
    if (is_checked('chk_'.$scope.'_link_level'))           $add('bo_link_level',       $bo_link_level);
    if (is_checked('chk_'.$scope.'_upload_level'))         $add('bo_upload_level',     $bo_upload_level);
    if (is_checked('chk_'.$scope.'_download_level'))       $add('bo_download_level',   $bo_download_level);
    if (is_checked('chk_'.$scope.'_html_level'))           $add('bo_html_level',       $bo_html_level);
    if (is_checked('chk_'.$scope.'_count_modify'))         $add('bo_count_modify',     $bo_count_modify);
    if (is_checked('chk_'.$scope.'_count_delete'))         $add('bo_count_delete',     $bo_count_delete);
    if (is_checked('chk_'.$scope.'_read_point'))           $add('bo_read_point',       $bo_read_point);
    if (is_checked('chk_'.$scope.'_write_point'))          $add('bo_write_point',      $bo_write_point);
    if (is_checked('chk_'.$scope.'_comment_point'))        $add('bo_comment_point',    $bo_comment_point);
    if (is_checked('chk_'.$scope.'_download_point'))       $add('bo_download_point',   $bo_download_point);
    if (is_checked('chk_'.$scope.'_category_list')) {
        $add('bo_category_list', $str_bo_category_list);
        $add('bo_use_category',  $bo_use_category);
    }
    if (is_checked('chk_'.$scope.'_use_sideview'))         $add('bo_use_sideview',     $bo_use_sideview);
    if (is_checked('chk_'.$scope.'_use_file_content'))     $add('bo_use_file_content', $bo_use_file_content);
    if (is_checked('chk_'.$scope.'_use_secret'))           $add('bo_use_secret',       $bo_use_secret);
    if (is_checked('chk_'.$scope.'_use_dhtml_editor'))     $add('bo_use_dhtml_editor', $bo_use_dhtml_editor);
    if (is_checked('chk_'.$scope.'_select_editor'))        $add('bo_select_editor',    $bo_select_editor);
    if (is_checked('chk_'.$scope.'_use_rss_view'))         $add('bo_use_rss_view',     $bo_use_rss_view);
    if (is_checked('chk_'.$scope.'_use_good'))             $add('bo_use_good',         $bo_use_good);
    if (is_checked('chk_'.$scope.'_use_nogood'))           $add('bo_use_nogood',       $bo_use_nogood);
    if (is_checked('chk_'.$scope.'_use_name'))             $add('bo_use_name',         $bo_use_name);
    if (is_checked('chk_'.$scope.'_use_signature'))        $add('bo_use_signature',    $bo_use_signature);
    if (is_checked('chk_'.$scope.'_use_ip_view'))          $add('bo_use_ip_view',      $bo_use_ip_view);
    if (is_checked('chk_'.$scope.'_use_list_view'))        $add('bo_use_list_view',    $bo_use_list_view);
    if (is_checked('chk_'.$scope.'_use_list_file'))        $add('bo_use_list_file',    $bo_use_list_file);
    if (is_checked('chk_'.$scope.'_use_list_content'))     $add('bo_use_list_content', $bo_use_list_content);
    if (is_checked('chk_'.$scope.'_use_email'))            $add('bo_use_email',        $bo_use_email);
    if (is_checked('chk_'.$scope.'_use_cert'))             $add('bo_use_cert',         $bo_use_cert);
    if (is_checked('chk_'.$scope.'_use_sns'))              $add('bo_use_sns',          $bo_use_sns);
    if (is_checked('chk_'.$scope.'_use_captcha'))          $add('bo_use_captcha',      $bo_use_captcha);
    if (is_checked('chk_'.$scope.'_skin'))                 $add('bo_skin',             $bo_skin);
    if (is_checked('chk_'.$scope.'_gallery_cols'))         $add('bo_gallery_cols',     $bo_gallery_cols);
    if (is_checked('chk_'.$scope.'_gallery_width'))        $add('bo_gallery_width',    $bo_gallery_width);
    if (is_checked('chk_'.$scope.'_gallery_height'))       $add('bo_gallery_height',   $bo_gallery_height);
    if (is_checked('chk_'.$scope.'_table_width'))          $add('bo_table_width',      $bo_table_width);
    if (is_checked('chk_'.$scope.'_page_rows'))            $add('bo_page_rows',        $bo_page_rows);
    if (is_checked('chk_'.$scope.'_subject_len'))          $add('bo_subject_len',      $bo_subject_len);
    if (is_checked('chk_'.$scope.'_new'))                  $add('bo_new',              $bo_new);
    if (is_checked('chk_'.$scope.'_hot'))                  $add('bo_hot',              $bo_hot);
    if (is_checked('chk_'.$scope.'_image_width'))          $add('bo_image_width',      $bo_image_width);
    if (is_checked('chk_'.$scope.'_reply_order'))          $add('bo_reply_order',      $bo_reply_order);
    if (is_checked('chk_'.$scope.'_sort_field'))           $add('bo_sort_field',       $bo_sort_field);
    if (is_checked('chk_'.$scope.'_write_min'))            $add('bo_write_min',        $bo_write_min);
    if (is_checked('chk_'.$scope.'_write_max'))            $add('bo_write_max',        $bo_write_max);
    if (is_checked('chk_'.$scope.'_comment_min'))          $add('bo_comment_min',      $bo_comment_min);
    if (is_checked('chk_'.$scope.'_comment_max'))          $add('bo_comment_max',      $bo_comment_max);
    if (is_checked('chk_'.$scope.'_upload_count'))         $add('bo_upload_count',     $bo_upload_count);
    if (is_checked('chk_'.$scope.'_upload_size'))          $add('bo_upload_size',      $bo_upload_size);

    if ($is_admin === 'super') {
        if (is_checked('chk_'.$scope.'_include_head'))        $add('bo_include_head',        $bo_include_head);
        if (is_checked('chk_'.$scope.'_include_tail'))        $add('bo_include_tail',        $bo_include_tail);
        if (is_checked('chk_'.$scope.'_content_head'))        $add('bo_content_head',        $bo_content_head);
        if (is_checked('chk_'.$scope.'_content_tail'))        $add('bo_content_tail',        $bo_content_tail);
    }

    if (is_checked('chk_'.$scope.'_insert_content')) $add('bo_insert_content', $bo_insert_content);
    if (is_checked('chk_'.$scope.'_use_search'))     $add('bo_use_search',     $bo_use_search);
    if (is_checked('chk_'.$scope.'_order'))          $add('bo_order',          $bo_order);
    for ($i = 1; $i <= 10; $i++) {
        if (is_checked('chk_'.$scope.'_'.$i)) {
            $add('bo_'.$i.'_subj', $etcs['bo_'.$i.'_subj']);
            $add('bo_'.$i,         $etcs['bo_'.$i]);
        }
    }

    return [$fields, $params];
};

// 같은 그룹내 게시판 동일 옵션 적용
list($grp_fields, $grp_params) = $build_scope('grp');
if ($grp_fields) {
    $grp_params[':gr_id'] = $gr_id;
    sql_pdo_query(" update {$g5['board_table']} set bo_table = bo_table {$grp_fields} where gr_id = :gr_id ", $grp_params);
}

// 모든 게시판 동일 옵션 적용
list($all_fields, $all_params) = $build_scope('all');
if ($all_fields) {
    sql_pdo_query(" update {$g5['board_table']} set bo_table = bo_table {$all_fields} ", $all_params);
}

delete_cache_latest($bo_table);

if (function_exists('get_admin_captcha_by')) {
    get_admin_captcha_by('remove');
}

run_event('admin_board_form_update', $bo_table, $w);

$redirect_qstr = str_replace('&amp;', '&', $qstr);
$redirect_url = G5_ADMIN_URL."/board_form?w=u&bo_table={$bo_table}";
if ($redirect_qstr !== '') {
    $redirect_url .= '&'.$redirect_qstr;
}
header('Location: '.$redirect_url, true, 302); exit;
