<?php
if (!defined('_GNUBOARD_')) exit;

/*
|--------------------------------------------------------------------------
| 사용자 라우터 샘플
|--------------------------------------------------------------------------
|
| 이 파일은 app/router.php 가 자동으로 읽습니다.
| 실제로 사용하려면 아래 return [] 안에 clean 또는 regex 라우트를 추가하세요.
| 대상 파일은 app/ 기준 상대 경로의 PHP 파일이어야 합니다.
|
| 예시:
|
| return [
|     'clean' => [
|         '/hello' => 'pages/hello.php',
|         '/company/map' => 'pages/company_map.php',
|     ],
|
|     'regex' => [
|         '#^/hello/(?P<name>[a-zA-Z0-9_-]+)/?$#' => 'pages/hello.php?name={name}',
|         '#^/event/([0-9]+)/?$#' => 'pages/event.php?id={1}',
|         '#^/team/(?P<team>[a-zA-Z0-9_-]+)/member/(?P<member>[a-zA-Z0-9_-]+)/?$#' => 'pages/team_member.php?team={team}&member={member}',
|         '#^/archive/([0-9]{4})/([0-9]{2})/([a-zA-Z0-9_-]+)/?$#' => 'pages/archive.php?year={1}&month={2}&slug={3}',
|     ],
| ];
|
| 위 예시에서 /hello/codex 로 접속하면 pages/hello.php 가 실행되고
| $_GET['name'] 값은 "codex" 가 됩니다.
| /team/core/member/hong 으로 접속하면 $_GET['team'], $_GET['member']가,
| /archive/2026/05/release-note 로 접속하면 $_GET['year'], $_GET['month'], $_GET['slug']가 들어갑니다.
|
*/

return [];
