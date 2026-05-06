# Shop 클린 URL — Design

**작성일:** 2026-05-07
**범위:** gnu5se shop 영역의 자원형 URL 전환 (3단계 점진 적용)

## 동기

board 영역에는 이미 `/board/{bo_table}/{wr_id}` 형태의 클린 URL 패턴이 잡혀 있다 ([app/router.php:131-143](../../../app/router.php#L131-L143), [index.php:44-108](../../../index.php#L44-L108)). shop 영역도 동일한 골격으로 전환해 일관성·SEO·공유 친화성을 확보한다.

## 골격 (board 패턴 재사용)

세 가지 layer 가 맞물려 동작한다:

1. **router.php — 클린 URL → legacy 진입점 매핑** (`extraRoutes` 정규식). 명명 캡처는 자동으로 `$_GET` 에 주입되어 legacy 코드가 query string 으로 받은 것처럼 동작.
2. **router.php — legacy URL → 클린 URL 301 redirect** (GET/HEAD 한정). POST 는 통과시켜 폼 제출 호환.
3. **index.php — `ob_start` HTML 후처리** (`preg_replace_callback`). gnuboard 내부 코드가 만든 legacy 링크를 최종 송출 직전에 클린 URL 로 치환.

이 3-layer 가 양방향 호환을 보장: 외부에서 어느 형태로 들어와도 정답으로 수렴, 내부에서 어떤 링크를 만들어도 사용자에겐 클린 URL 만 노출.

## 단계

### 1단계 — `/shop/item/{it_id}`

**대상 legacy URL:** `/shop/item.php?it_id=item0008`
**클린 URL:** `/shop/item/item0008`

`it_id` 는 `[a-zA-Z0-9_-]+` (영숫자 + `_` + `-`). 'item0008' 처럼 영문+숫자 혼합 ID 도, 순수 숫자 ID 도 통과.

**router.php 변경:**

기존 shop 라우트들([app/router.php:121-124](../../../app/router.php#L121-L124)) 위에 더 구체적인 룰을 먼저 매칭하도록 추가:

```php
'#^/shop/item/(?P<it_id>[a-zA-Z0-9_-]+)/?$#' => 'shop/item.php',
```

**Legacy 301:** board 의 redirect 블록 ([app/router.php:217-239](../../../app/router.php#L217-L239)) 뒤에 shop item 전용 블록 추가:

```php
if (($method === 'GET' || $method === 'HEAD') && $path === '/shop/item.php') {
    parse_str(parse_url($requestUri, PHP_URL_QUERY) ?? '', $params);
    if (!empty($params['it_id']) && preg_match('/^[a-zA-Z0-9_-]+$/', $params['it_id'])) {
        $url = '/shop/item/'.$params['it_id'];
        unset($params['it_id']);
        if (!empty($params)) $url .= '?'.http_build_query($params);
        header('Location: '.$url, true, 301); exit;
    }
}
```

it_id 가 비었거나 형식이 이상하면 통과시켜 legacy 동작 유지 (404 페이지 등이 그대로 떨어지도록).

**ob_start 후처리:** [index.php](../../../index.php) 의 board 치환 블록 뒤에 shop item 블록 추가:

```php
$html = preg_replace_callback(
    '#/shop/item\.php\?([^"\'\s<>]+)#',
    function ($m) {
        $qs = str_replace('&amp;', '&', $m[1]);
        parse_str($qs, $params);
        if (empty($params['it_id']) || !preg_match('/^[a-zA-Z0-9_-]+$/', $params['it_id'])) return $m[0];
        $url = '/shop/item/'.$params['it_id'];
        unset($params['it_id']);
        if (!empty($params)) $url .= '?'.http_build_query($params, '', '&amp;');
        return $url;
    },
    $html
);
```

**검증:**
- `/shop/item/item0008` → 200, 상세 페이지 정상 렌더
- `/shop/item.php?it_id=item0008` → 301 → `/shop/item/item0008`
- `/shop/item.php?it_id=item0008&xxx=1` → 301 → `/shop/item/item0008?xxx=1`
- 출력 HTML 에 `/shop/item.php?it_id=X` 패턴이 남지 않음 (grep)
- 기존 `/shop/cart`, `/shop/orderform`, `/shop/inicis/*` 등 segment 라우트 회귀 없음

### 2단계 — `/shop/category/{ca_id}`

**대상 legacy URL:** `/shop/list.php?ca_id=10`
**클린 URL:** `/shop/category/10`

`ca_id` 는 gnuboard shop 의 카테고리 코드. 일반적으로 숫자지만 사이트별로 영숫자도 사용 가능 → `[a-zA-Z0-9_-]+` 로 잡음.

router 룰 / 301 / ob_start 모두 1단계와 동일 골격.

부가 query (sort, page 등) 는 보존.

**검증:**
- `/shop/category/10` → 200, 카테고리 목록 렌더
- `/shop/list.php?ca_id=10` → 301 → `/shop/category/10`
- `/shop/list.php?ca_id=10&page=2&sort=it_price` → 301 → `/shop/category/10?page=2&sort=it_price`
- 출력 HTML 에 `/shop/list.php?ca_id=X` 가 남지 않음
- 1단계 회귀 없음

### 3단계 — `/shop/{best|recommend|new|popular|sale}`

**대상 legacy URL:** `/shop/listtype.php?type={1..5}`
**클린 URL:** type 별 의미 alias

| type | alias | 의미 |
|------|-------|------|
| 1 | best | 히트상품 |
| 2 | recommend | 추천상품 |
| 3 | new | 최신상품 |
| 4 | popular | 인기상품 |
| 5 | sale | 할인상품 |

[app/shop/listtype.php:6-19](../../../app/shop/listtype.php#L6-L19) 에 type → 제목 매핑이 하드코드되어 있어 alias 와 안전하게 1:1 대응.

**router 룰:** 5개의 alias 를 각각 listtype.php 로 매핑하면서 type 을 inject:

```php
'#^/shop/best/?$#'      => 'shop/listtype.php?type=1',
'#^/shop/recommend/?$#' => 'shop/listtype.php?type=2',
'#^/shop/new/?$#'       => 'shop/listtype.php?type=3',
'#^/shop/popular/?$#'   => 'shop/listtype.php?type=4',
'#^/shop/sale/?$#'      => 'shop/listtype.php?type=5',
```

router 의 `?key=val` query inject 메커니즘([app/router.php:265-272](../../../app/router.php#L265-L272))이 이미 있어 그대로 사용.

**Legacy 301:** `/shop/listtype.php?type=N` → alias 로 redirect. type 이 1~5 범위 밖이면 통과 (legacy 의 'alert' 분기 유지).

**ob_start 후처리:** `/shop/listtype.php?type=N&...` → `/shop/{alias}[?...]` 치환. type 1~5 외에는 변환하지 않음.

**검증:**
- 5개 alias 각각 200 + 정상 렌더
- `/shop/listtype.php?type=4` → 301 → `/shop/popular`
- `/shop/listtype.php?type=4&page=2&sort=it_price` → 301 → `/shop/popular?page=2&sort=it_price`
- type 6 같은 잘못된 값 → 변환 없이 통과 (기존 alert 동작 유지)
- 1·2단계 회귀 없음

## 비목표 (Out of scope)

다음은 이 spec 에 포함하지 않는다:

- 모바일(`G5_IS_MOBILE`) 분기의 별도 URL 디자인 — 단일 마크업 정책에 맞춰 데스크탑/모바일 동일 URL 사용
- shop 의 다른 자원 (`/shop/cart` 등 이미 segment 라우트로 작동 중) 변경
- 어드민 영역 (`/admin/shop_admin/*`) 변경
- 카테고리 hierarchy 표현 (`/shop/category/10/sub/...`) — gnuboard 가 ca_id 단일 키만 사용하므로 불필요

## 위험 요소 / 주의

1. **shop catch-all 라우트와의 순서**: 기존 [app/router.php:124](../../../app/router.php#L124) 의 `[a-zA-Z][a-zA-Z0-9_-]*(?:/...)*` 패턴이 `/shop/item/item0008` 도 매칭한다. 새 룰을 catch-all *위에* 박아야 한다. PHP foreach 는 삽입 순서를 보장하므로 안전.
2. **ob_start 치환 정규식의 greedy 매칭**: `[^"\'\s<>]+` 사용으로 따옴표·공백·HTML 태그 경계까지만 잡음 — board 패턴과 동일.
3. **POST 호환**: 모든 redirect 는 GET/HEAD 한정. shop 의 cartupdate/wishupdate 등 POST 폼은 그대로 legacy URL 로 통과.
4. **단계별 격리 커밋**: 각 단계가 독립적으로 회귀 검증 → 커밋. 문제 발생 시 단계 단위로 revert 가능.

## 단계별 커밋 메시지 (예시)

1. `shop: add /shop/item/{it_id} clean URL + legacy 301 + ob_start rewrite`
2. `shop: add /shop/category/{ca_id} clean URL + legacy 301 + ob_start rewrite`
3. `shop: add /shop/{best|recommend|new|popular|sale} listtype aliases + legacy 301 + ob_start rewrite`
