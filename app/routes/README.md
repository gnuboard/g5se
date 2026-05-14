# User Routes

이 폴더의 `*.php` 파일은 `app/router.php`가 자동으로 읽는 사용자 정의 라우트입니다.
파일명 순서대로 로드되며, 코어 라우트가 먼저 매칭된 뒤 사용자 라우트가 매칭됩니다.

예시:

```php
<?php
if (!defined('_GNUBOARD_')) exit;

return [
    'clean' => [
        '/hello' => 'pages/hello.php',
    ],
    'regex' => [
        '#^/hello/(?P<name>[a-zA-Z0-9_-]+)/?$#' => 'pages/hello.php?name={name}',
    ],
];
```

- `clean`: 정확히 일치하는 클린 URL입니다. 대상은 `app/` 기준 PHP 파일입니다.
- `regex`: 정규식 URL입니다. `(?P<name>...)` 캡처는 `$_GET['name']`으로 주입됩니다.
- `regex` 대상의 query string에는 `{name}` 또는 `{1}` 같은 캡처 placeholder를 쓸 수 있습니다.
- 대상 파일은 반드시 `app/` 아래의 `.php` 파일이어야 하며 `..` 경로는 무시됩니다.
