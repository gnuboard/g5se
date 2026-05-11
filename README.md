# 그누보드5 SE

**GNUBOARD5 Second Edition** — gnuboard5 기반 second edition.

## 특징

- **파일 하나로 반응형 + 다크모드** — PC/모바일 마크업 분리 없이 단일 파일, `@media` 와 CSS variable + `data-theme` 으로 처리
- PHP 8.x 호환 (PDO named placeholder)
- utf8mb4 + InnoDB (이모지 지원, MySQL strict mode 호환)
- nullable date/datetime (`0000-00-00` 폐기)
- 클린 URL 라우팅 (`/board/{table}/{wr_id}`, `/shop/item/{it_id}` 등)
- 설치 마법사 모더나이즈 (진행 단계, 다크모드)
- DB 마이그레이션 도구 — `/admin/db_migrate` 에서 utf8mb3 → utf8mb4, zero-date → NULL 일괄 변환

## 요구사항

- PHP 8.0+
- MySQL 5.7+ 또는 MariaDB 10.2+
- Apache + mod_rewrite (또는 nginx 동등 설정)

## 설치

```bash
git clone https://github.com/gnuboard/g5se.git
cd g5se
mkdir -p data && chmod 707 data
mysql -u root -e "CREATE DATABASE g5se CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

브라우저로 `/install/` 접근 → 마법사 따라 진행.

## 라이센스

MIT License — `LICENSE` 파일 참조.

## 문서

- [MODERNIZATION.md](MODERNIZATION.md) — 모더나이즈 작업 기록
- [CLAUDE.md](CLAUDE.md) — AI 보조 도구용 가이드라인

---
**기반**: [gnuboard5](https://github.com/gnuboard/gnuboard5)
