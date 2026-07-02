<?php
/*
 * /admin/theme_update — 테마 적용 POST 핸들러 (text/empty 응답).
 */
$sub_menu = "100280";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if ($is_admin !== 'super') {
    die('최고관리자만 접근 가능합니다.');
}

admin_referer_check();

$theme = isset($_POST['theme']) ? trim($_POST['theme']) : '';
$post_set_default_skin = isset($_POST['set_default_skin']) ? clean_xss_tags($_POST['set_default_skin'], 1, 1) : '';

$theme_dir = get_theme_dir();

// 테마 해제(reset) 액션은 제거됨 — cf_theme 미설정 시 basic 으로 폴백되므로(app/common.php) '사용 안함' 개념이 없음
if(!in_array($theme, $theme_dir))
    die('선택하신 테마가 설치되어 있지 않습니다.');

// 테마적용
sql_pdo_query(" update {$g5['config_table']} set cf_theme = :theme ", [':theme' => $theme]);

// 테마 설정 스킨 적용
if($post_set_default_skin == 1) {
    $keys = 'set_default_skin, cf_member_skin, cf_mobile_member_skin, cf_new_skin, cf_mobile_new_skin, cf_search_skin, cf_mobile_search_skin, cf_connect_skin, cf_mobile_connect_skin, cf_faq_skin, cf_mobile_faq_skin, qa_skin, qa_mobile_skin, de_shop_skin, de_shop_mobile_skin';

    $tconfig = get_theme_config_value($theme, $keys);

    if($tconfig['set_default_skin']) {
        // 컬럼명은 placeholder 불가 — config 키 화이트리스트 통과 후 컬럼으로 사용, 값만 :param
        $cf_set = []; $cf_params = [];
        $qa_set = []; $qa_params = [];
        $de_set = []; $de_params = [];

        foreach($tconfig as $key => $val) {
            if (!preg_match('#^[a-z0-9_]+$#i', $key)) continue; // 컬럼명 가드

            if(preg_match('#^qa_.+$#', $key)) {
                if($val) {
                    if(!preg_match('#^theme/.+$#', $val)) $val = 'theme/'.$val;
                    $qa_set[] = " $key = :$key ";
                    $qa_params[':'.$key] = $val;
                }
                continue;
            }

            if(preg_match('#^de_.+$#', $key)) {
                if(!isset($default[$key])) continue;
                if($val) {
                    if(!preg_match('#^theme/.+$#', $val)) $val = 'theme/'.$val;
                    $de_set[] = " $key = :$key ";
                    $de_params[':'.$key] = $val;
                }
                continue;
            }

            if(!isset($config[$key])) continue;

            if($val) {
                if(!preg_match('#^theme/.+$#', $val)) $val = 'theme/'.$val;
                $cf_set[] = " $key = :$key ";
                $cf_params[':'.$key] = $val;
            }
        }

        if(!empty($cf_set)) {
            sql_pdo_query(" update {$g5['config_table']}            set " . implode(', ', $cf_set), $cf_params);
        }
        if(!empty($qa_set)) {
            sql_pdo_query(" update {$g5['qa_config_table']}         set " . implode(', ', $qa_set), $qa_params);
        }
        if(!empty($de_set)) {
            sql_pdo_query(" update {$g5['g5_shop_default_table']} set " . implode(', ', $de_set), $de_params);
        }
    }
}

run_event('adm_theme_update', $theme, $post_set_default_skin);

die('');