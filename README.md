# 그누보드5SE

**GNUBOARD5 Second Edition** — gnuboard5 기반 모던 second edition.

## 무엇이 다른가

원본 gnuboard5 가 PHP 5/MySQL 4 시절에 설계되어 누적된 기술 부채를 정리한 fork:

- **PHP 8.x 호환** — 전수 PDO named placeholder 마이그레이션 (raw `mysql_*` 잔재 제거, SQL injection 방어)
- **utf8mb4 + InnoDB** — 이모지 저장 가능, MySQL strict mode (`NO_ZERO_DATE`) 호환
- **nullable date/datetime** — `0000-00-00 00:00:00` 폐기, NULL 으로 통일
- **클린 URL** — front controller 라우팅 (`/board/{table}/{wr_id}`, `/shop/item/{it_id}` 등 자원형)
- **모던 디자인 시스템** — design token 기반 + 다크모드 + 반응형 (단일 마크업)
- **설치 마법사 모더나이즈** — 진행 단계 표시, 다크모드, MIT 라이센스
- **DB 마이그레이션 도구** — `/admin/db_migrate` 에서 utf8mb3 → utf8mb4, zero-date → NULL 일괄 변환 가능

## 요구사항

- PHP **8.0+**
- MySQL **5.7+** 또는 MariaDB **10.2+** (utf8mb4 + ROW_FORMAT=DYNAMIC 권장)
- Apache + mod_rewrite (또는 nginx 동등 설정)

## 설치

```bash
# 1) 코드 가져오기
git clone https://github.com/kagla/gnu5se.git
cd gnu5se

# 2) data 디렉토리 권한
mkdir -p data && chmod 707 data

# 3) DB 생성 (utf8mb4)
mysql -u root -e "CREATE DATABASE gnu5se CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 4) 브라우저로 /install/ 접근 → 마법사 따라 진행
```

## 라이센스

MIT License — `LICENSE` 파일 참조.

원본 gnuboard5 (GPL v2) 기반 second edition. 이 second edition 자체는 MIT 로 자유롭게 사용·수정·배포 가능합니다.

## 문서

- [MODERNIZATION.md](MODERNIZATION.md) — 모더나이즈 작업 상세 기록 (페이지별 진행 상태)
- [CLAUDE.md](CLAUDE.md) — AI 보조 도구용 가이드라인

---
**기반**: [gnuboard5](https://github.com/gnuboard/gnuboard5) (GPL v2)
