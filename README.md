# 그누보드5SE

**GNUBOARD5 Second Edition** — gnuboard5 기반 second edition.

## 특징

- PHP 8.x 호환 (PDO named placeholder)
- utf8mb4 + InnoDB (이모지 지원, MySQL strict mode 호환)
- nullable date/datetime (`0000-00-00` 폐기)
- 클린 URL 라우팅 (`/board/{table}/{wr_id}`, `/shop/item/{it_id}` 등)
- 모던 디자인 + 다크모드 + 반응형 (단일 마크업)
- 설치 마법사 모더나이즈 (진행 단계, 다크모드)
- DB 마이그레이션 도구 — `/admin/db_migrate` 에서 utf8mb3 → utf8mb4, zero-date → NULL 일괄 변환

## 요구사항

- PHP 8.0+
- MySQL 5.7+ 또는 MariaDB 10.2+
- Apache + mod_rewrite (또는 nginx 동등 설정)

## 설치

```bash
git clone https://github.com/kagla/gnu5se.git
cd gnu5se
mkdir -p data && chmod 707 data
mysql -u root -e "CREATE DATABASE gnu5se CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

브라우저로 `/install/` 접근 → 마법사 따라 진행.

## 라이센스

MIT License — `LICENSE` 파일 참조.
gnuboard5 (GPL v2) 기반.

## 문서

- [MODERNIZATION.md](MODERNIZATION.md) — 모더나이즈 작업 기록
- [CLAUDE.md](CLAUDE.md) — AI 보조 도구용 가이드라인

---
**기반**: [gnuboard5](https://github.com/gnuboard/gnuboard5)
