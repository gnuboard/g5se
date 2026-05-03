# CLAUDE.md

Behavioral guidelines to reduce common LLM coding mistakes. Merge with project-specific instructions as needed.

**Tradeoff:** These guidelines bias toward caution over speed. For trivial tasks, use judgment.

## 1. Think Before Coding

**Don't assume. Don't hide confusion. Surface tradeoffs.**

Before implementing:
- State your assumptions explicitly. If uncertain, ask.
- If multiple interpretations exist, present them - don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear, stop. Name what's confusing. Ask.

## 2. Simplicity First

**Minimum code that solves the problem. Nothing speculative.**

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that wasn't requested.
- No error handling for impossible scenarios.
- If you write 200 lines and it could be 50, rewrite it.

Ask yourself: "Would a senior engineer say this is overcomplicated?" If yes, simplify.

## 3. Surgical Changes

**Touch only what you must. Clean up only your own mess.**

When editing existing code:
- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style, even if you'd do it differently.
- If you notice unrelated dead code, mention it - don't delete it.

When your changes create orphans:
- Remove imports/variables/functions that YOUR changes made unused.
- Don't remove pre-existing dead code unless asked.

The test: Every changed line should trace directly to the user's request.

## 4. Goal-Driven Execution

**Define success criteria. Loop until verified.**

Transform tasks into verifiable goals:
- "Add validation" → "Write tests for invalid inputs, then make them pass"
- "Fix the bug" → "Write a test that reproduces it, then make it pass"
- "Refactor X" → "Ensure tests pass before and after"

For multi-step tasks, state a brief plan:
```
1. [Step] → verify: [check]
2. [Step] → verify: [check]
3. [Step] → verify: [check]
```

Strong success criteria let you loop independently. Weak criteria ("make it work") require constant clarification.

## 5. Auto-Commit on Task Completion

**작업 한 단위가 끝날 때마다** 자동으로 git 커밋한다 (세션 종료 시점이 아님).

**"작업 한 단위" 의 정의:**
- 파일을 한 곳 이상 수정했음
- 검증 (curl 테스트 / syntax check / 회귀 확인) 통과했음
- 사용자에게 "변경 요약" 응답을 보내려는 시점
- 즉, 응집된 의미 있는 변경 하나가 완료된 시점 (예: "회원가입 폼 모던화", "outlogin 레이아웃 수정", "폰트 토큰 추가")

**절차 (응답 보내기 직전에):**
1. 회귀 확인 (이미 했다면 skip)
2. `git status` 로 staged 안된 변경 확인
3. 자동 커밋:
   ```
   git add -A
   git commit -m "<해당 작업 한두 줄 요약>"
   ```
4. 커밋 메시지는 그 한 작업만 요약 (예: "fix outlogin layout: move auto-login + ID/PW link to single row")
5. push 는 하지 않음 (사용자 명시 요청 시만)
6. **파일 변경 없는 작업은 커밋 X** (단순 질문 답변, 설명, 검증만 한 경우)
7. 저장소가 git 으로 관리 안 되면 (`.git` 없음) 안내만 출력하고 그대로 종료

**주의:**
- staged 파일에 `.env`, `data/dbconfig.php`, `*credentials*` 같은 민감 파일 보이면 멈추고 사용자에게 확인
- 사용자가 같은 작업의 후속 수정을 요청하면 별도 커밋으로 쌓임 (squash 는 사용자가 나중에)

---

**These guidelines are working if:** fewer unnecessary changes in diffs, fewer rewrites due to overcomplication, and clarifying questions come before implementation rather than after mistakes.
