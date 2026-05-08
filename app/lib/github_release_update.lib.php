<?php
if (!defined('_GNUBOARD_')) exit;

define('G5SE_UPDATE_REPOSITORY', defined('G5_GITHUB_UPDATE_REPOSITORY') ? G5_GITHUB_UPDATE_REPOSITORY : 'kagla/gnu5se');
$g5se_update_token = defined('G5_GITHUB_UPDATE_TOKEN') ? G5_GITHUB_UPDATE_TOKEN : getenv('G5_GITHUB_UPDATE_TOKEN');
define('G5SE_UPDATE_TOKEN', $g5se_update_token ? $g5se_update_token : '');
unset($g5se_update_token);

function g5se_update_root_path()
{
    return dirname(G5_PATH);
}

function g5se_update_data_path()
{
    return G5_DATA_PATH . '/update';
}

function g5se_update_current_version()
{
    return defined('G5_GNUBOARD_VER') ? G5_GNUBOARD_VER : '';
}

function g5se_update_normalize_version($version)
{
    $version = trim((string) $version);
    $version = preg_replace('/^[^0-9]*/', '', $version);
    return $version ?: trim((string) $version);
}

function g5se_update_log($message)
{
    $dir = g5se_update_data_path();
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    @file_put_contents($dir . '/update.log', '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
}

function g5se_update_http_request($url, $headers = array(), $download_path = '')
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP cURL 확장이 필요합니다.');
    }

    $ch = curl_init($url);
    $default_headers = array(
        'Accept: application/vnd.github+json',
        'User-Agent: gnu5se-release-updater',
        'X-GitHub-Api-Version: 2022-11-28',
    );
    if (G5SE_UPDATE_TOKEN) {
        $default_headers[] = 'Authorization: Bearer ' . G5SE_UPDATE_TOKEN;
    }

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, $download_path ? 300 : 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($default_headers, $headers));

    $fp = null;
    if ($download_path) {
        $fp = fopen($download_path, 'wb');
        if (!$fp) {
            throw new RuntimeException('다운로드 파일을 생성할 수 없습니다: ' . $download_path);
        }
        curl_setopt($ch, CURLOPT_FILE, $fp);
    } else {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    }

    $body = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($fp) {
        fclose($fp);
    }

    if ($error) {
        throw new RuntimeException('GitHub 요청 실패: ' . $error);
    }

    if ($status < 200 || $status >= 300) {
        if ($download_path && file_exists($download_path)) {
            @unlink($download_path);
        }
        throw new RuntimeException('GitHub 응답 오류: HTTP ' . $status);
    }

    return $body;
}

function g5se_update_latest_release()
{
    $url = 'https://api.github.com/repos/' . G5SE_UPDATE_REPOSITORY . '/releases/latest';
    $body = g5se_update_http_request($url);
    $release = json_decode($body, true);

    if (!is_array($release) || empty($release['tag_name'])) {
        throw new RuntimeException('GitHub Releases 정보를 해석할 수 없습니다.');
    }

    $current = g5se_update_current_version();
    $current_norm = g5se_update_normalize_version($current);
    $latest_norm = g5se_update_normalize_version($release['tag_name']);
    $has_update = $current_norm && $latest_norm ? version_compare($latest_norm, $current_norm, '>') : ($release['tag_name'] !== $current);

    return array(
        'repository' => G5SE_UPDATE_REPOSITORY,
        'current_version' => $current,
        'latest_version' => $release['tag_name'],
        'latest_name' => isset($release['name']) ? $release['name'] : '',
        'published_at' => isset($release['published_at']) ? $release['published_at'] : '',
        'html_url' => isset($release['html_url']) ? $release['html_url'] : '',
        'zipball_url' => isset($release['zipball_url']) ? $release['zipball_url'] : '',
        'body' => isset($release['body']) ? $release['body'] : '',
        'has_update' => $has_update,
    );
}

function g5se_update_prepare_directories()
{
    $base = g5se_update_data_path();
    foreach (array($base, $base . '/downloads', $base . '/extract', $base . '/backups') as $dir) {
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            throw new RuntimeException('업데이트 작업 디렉터리를 만들 수 없습니다: ' . $dir);
        }
        if (!is_writable($dir)) {
            throw new RuntimeException('업데이트 작업 디렉터리에 쓰기 권한이 없습니다: ' . $dir);
        }
    }

    $htaccess = $base . '/.htaccess';
    if (!file_exists($htaccess)) {
        @file_put_contents($htaccess, "Order deny,allow\nDeny from all\n");
    }

    $index = $base . '/index.php';
    if (!file_exists($index)) {
        @file_put_contents($index, '');
    }
}

function g5se_update_remove_tree($path)
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir() && !$item->isLink()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }

    @rmdir($path);
}

function g5se_update_validate_zip($zip)
{
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name === false) {
            throw new RuntimeException('ZIP 항목을 읽을 수 없습니다.');
        }
        if (strpos($name, "\0") !== false || preg_match('#(^/|^[A-Za-z]:[\\\\/]|(?:^|/)\.\.(?:/|$))#', $name)) {
            throw new RuntimeException('안전하지 않은 ZIP 경로가 포함되어 있습니다: ' . $name);
        }
    }
}

function g5se_update_extract_release($zip_path, $tag)
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('PHP zip 확장이 필요합니다.');
    }

    $extract_base = g5se_update_data_path() . '/extract';
    $extract_path = $extract_base . '/' . preg_replace('/[^A-Za-z0-9_.-]/', '-', $tag . '-' . date('YmdHis'));
    g5se_update_remove_tree($extract_path);

    if (!@mkdir($extract_path, 0755, true)) {
        throw new RuntimeException('압축 해제 디렉터리를 만들 수 없습니다: ' . $extract_path);
    }

    $zip = new ZipArchive();
    if ($zip->open($zip_path) !== true) {
        throw new RuntimeException('릴리스 ZIP 파일을 열 수 없습니다.');
    }

    g5se_update_validate_zip($zip);

    if (!$zip->extractTo($extract_path)) {
        $zip->close();
        throw new RuntimeException('릴리스 ZIP 압축 해제에 실패했습니다.');
    }
    $zip->close();

    $dirs = glob($extract_path . '/*', GLOB_ONLYDIR);
    if (!$dirs || count($dirs) !== 1) {
        throw new RuntimeException('릴리스 ZIP의 최상위 디렉터리 구조가 예상과 다릅니다.');
    }

    $source = $dirs[0];
    if (!file_exists($source . '/app/version.php') || !file_exists($source . '/index.php')) {
        throw new RuntimeException('릴리스 ZIP에서 필수 파일(app/version.php, index.php)을 찾을 수 없습니다.');
    }

    return $source;
}

function g5se_update_is_excluded($relative)
{
    $relative = str_replace('\\', '/', ltrim($relative, '/'));
    $first = strtok($relative, '/');

    if ($first === false) {
        return false;
    }

    $excluded_roots = array('.git', 'data', 'node_modules', 'vendor');
    if (in_array($first, $excluded_roots, true)) {
        return true;
    }

    $excluded_files = array('.env', 'app/.env');
    return in_array($relative, $excluded_files, true);
}

function g5se_update_check_writable($source, $target)
{
    if (!is_writable($target)) {
        throw new RuntimeException('설치 루트에 쓰기 권한이 없습니다: ' . $target);
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($items as $item) {
        $relative = substr($item->getPathname(), strlen($source) + 1);
        if (g5se_update_is_excluded($relative)) {
            continue;
        }

        $dest = $target . '/' . $relative;
        if (file_exists($dest) && !is_writable($dest)) {
            throw new RuntimeException('업데이트 대상에 쓰기 권한이 없습니다: ' . $dest);
        }

        $parent = dirname($dest);
        while (!file_exists($parent) && $parent !== dirname($parent)) {
            $parent = dirname($parent);
        }

        if (!file_exists($dest) && !is_writable($parent)) {
            throw new RuntimeException('업데이트 대상 디렉터리에 쓰기 권한이 없습니다: ' . $parent);
        }
    }
}

function g5se_update_create_backup($target)
{
    $backup = g5se_update_data_path() . '/backups/gnu5se-backup-' . date('Ymd-His') . '.zip';
    $zip = new ZipArchive();

    if ($zip->open($backup, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('백업 ZIP 파일을 만들 수 없습니다: ' . $backup);
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($items as $item) {
        $path = $item->getPathname();
        $relative = substr($path, strlen($target) + 1);

        if (g5se_update_is_excluded($relative)) {
            continue;
        }

        if ($item->isDir() && !$item->isLink()) {
            $zip->addEmptyDir($relative);
        } elseif ($item->isFile()) {
            $zip->addFile($path, $relative);
        }
    }

    $zip->close();
    return $backup;
}

function g5se_update_copy_release($source, $target)
{
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($items as $item) {
        $path = $item->getPathname();
        $relative = substr($path, strlen($source) + 1);

        if (g5se_update_is_excluded($relative)) {
            continue;
        }

        $dest = $target . '/' . $relative;
        if ($item->isDir() && !$item->isLink()) {
            if (!is_dir($dest) && !@mkdir($dest, 0755, true)) {
                throw new RuntimeException('디렉터리를 만들 수 없습니다: ' . $dest);
            }
            continue;
        }

        if ($item->isFile()) {
            $parent = dirname($dest);
            if (!is_dir($parent) && !@mkdir($parent, 0755, true)) {
                throw new RuntimeException('디렉터리를 만들 수 없습니다: ' . $parent);
            }
            if (!@copy($path, $dest)) {
                throw new RuntimeException('파일을 업데이트할 수 없습니다: ' . $dest);
            }
            @chmod($dest, fileperms($path) & 0777);
        }
    }
}

function g5se_update_apply_latest_release()
{
    @set_time_limit(0);
    @ini_set('memory_limit', '-1');

    g5se_update_prepare_directories();

    $release = g5se_update_latest_release();
    if (empty($release['zipball_url'])) {
        throw new RuntimeException('릴리스 ZIP 다운로드 URL이 없습니다.');
    }

    $download = g5se_update_data_path() . '/downloads/' . preg_replace('/[^A-Za-z0-9_.-]/', '-', $release['latest_version']) . '.zip';
    g5se_update_log('download start ' . $release['latest_version']);
    g5se_update_http_request($release['zipball_url'], array('Accept: application/octet-stream'), $download);

    $source = g5se_update_extract_release($download, $release['latest_version']);
    $target = g5se_update_root_path();
    g5se_update_check_writable($source, $target);

    $backup = g5se_update_create_backup($target);
    g5se_update_log('backup created ' . $backup);

    g5se_update_copy_release($source, $target);
    g5se_update_log('updated to ' . $release['latest_version']);

    return array(
        'release' => $release,
        'backup' => $backup,
    );
}
