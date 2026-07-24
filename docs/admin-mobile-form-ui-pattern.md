# 관리자 모바일 폼 UI 패턴

기준 화면: `/admin/boardgroup_form?w=u&gr_id=community`

관리자 설정 폼을 모바일로 변환할 때 이 화면의 구조와 스타일을 기본값으로 사용한다.
데스크톱 마크업과 UI는 유지하고, 페이지 전용 클래스 아래의 모바일 CSS로만 재배치한다.

## 기본 구조

```html
<main class="example-form-page ...">
  <form class="example-form">
    <section class="example-form-section">
      <h2 class="h2_frm">기본 설정</h2>
      <div class="example-form-table tbl_frm01 tbl_wrap">
        <table>
          <tbody>
            <tr>
              <th><label for="field_id">항목명</label></th>
              <td><input id="field_id" type="text"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </form>
</main>
```

페이지마다 `example-form-page`처럼 고유한 최상위 클래스를 추가한다. 공통
`.tbl_frm01` 스타일을 직접 변경하지 말고 반드시 페이지 클래스 아래로 범위를 제한한다.

## 모바일 표시 규칙

기준 breakpoint는 `max-width: 640px`이다.

1. 화면 좌우 여백은 `0.875rem`, 하단 플로팅 메뉴가 있으면 하단 여백은
   `6.5rem` 이상 확보한다.
2. 설정 섹션 제목은 `.h2_frm`을 사용하고 `1rem` 크기와 왼쪽 강조선을 유지한다.
3. 표 전체를 하나의 패널로 표시한다.
   - 테두리: `1px solid var(--slate-200)`
   - 모서리: `0.75rem`
   - 각 `tr`은 카드로 분리하지 않고 얇은 하단 구분선으로 나눈다.
4. 기존 표의 `th`는 입력창 위 레이블로 바꾼다.
   - 별도 배경과 세로 경계선을 제거한다.
   - 왼쪽 정렬, 굵기 `700`, 모바일 기준 `0.875rem`
5. `td`는 레이블 바로 아래에 전체 폭으로 표시한다.
6. 일반 텍스트 입력과 셀렉트박스는 전체 폭, 최소 높이 `2.625rem`,
   글자 크기 `0.8125rem`을 기준으로 한다.
7. 읽기 전용 입력은 일반 입력과 형태는 같게 두고 배경색으로만 구분한다.
   - 다크모드: `var(--slate-800)`
   - 글자색: `var(--slate-100)`
8. 바로가기·목록 같은 보조 버튼은 전체 폭으로 늘리지 않고 내용 너비로 표시한다.
   버튼이 여러 개면 자연스럽게 다음 줄로 흐르게 한다.
9. 여분필드는 접지 않고 표시하며, 내부의 `제목`과 `내용` 레이블을 입력 위에 둔다.
10. 다크모드는 패널·구분선·레이블·읽기 전용 필드의 대비를 별도로 지정한다.

## 하단 플로팅 메뉴

- 핵심 동작인 `확인` 또는 `저장`을 가장 넓게 표시한다.
- 단순 폼은 `목록 / 확인` 2열을 사용한다.
- 보조 동작이 많은 폼은 기본 바에 `확인 / 메뉴 / 맨 위로`만 두고,
  메뉴를 펼쳤을 때 보조 동작을 2열로 표시한다.
- 긴 한글 버튼은 `word-break: keep-all`을 적용해 글자 단위 줄바꿈을 방지한다.

## 안내문

`.local_desc` 또는 `.local_desc01`의 정보 아이콘은 절대 위치이므로 모바일 전용
스타일에서 왼쪽 padding을 없애면 안 된다.

```css
.example-form-description {
    padding: 0.875rem 0.875rem 0.875rem 2.75rem;
}

.example-form-description::before {
    top: 0.875rem;
    left: 0.875rem;
    width: 1.125rem;
    height: 1.125rem;
}
```

## 설정 탭이 있는 폼

- 탭은 여러 줄로 접지 않고 한 줄 가로 스크롤로 표시한다.
- 현재 섹션과 같은 탭에 `.active`와 `aria-current="location"`을 적용한다.
- 현재 탭이 가려지지 않도록 탭 바의 가로 스크롤 위치를 자동 조정한다.

## 구현 체크리스트

- 페이지 전용 최상위 클래스를 추가했는가?
- 모바일 CSS가 해당 페이지 클래스 아래로 제한됐는가?
- 레이블, 입력, 적용 옵션이 세로 순서로 읽히는가?
- 입력값과 버튼 문구가 잘리거나 글자 단위로 줄바꿈되지 않는가?
- 정보 아이콘과 안내문이 겹치지 않는가?
- 플로팅 메뉴가 콘텐츠를 가리지 않도록 하단 여백이 있는가?
- 라이트·다크모드를 모두 처리했는가?
- 데스크톱 표 구조와 동작을 유지했는가?
- PHP 문법, JavaScript 문법, `git diff --check`를 통과했는가?

## 현재 참고 구현

- 마크업: `app/admin/boardgroup_form.php`
- 모바일 스타일: `app/admin/css/admin.css`의
  `게시판 그룹 생성·수정 모바일 폼`
- 플로팅 메뉴와 현재 탭 처리: `app/admin/js/admin.js`
