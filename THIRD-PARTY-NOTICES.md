# 제3자 구성요소 고지 (THIRD-PARTY NOTICES)

이 저장소의 루트 [`LICENSE`](LICENSE)(MIT, 저작권 (주)에스아이알소프트)는 **g5se / 그누보드 자체 코드에만 적용**됩니다.

이 저장소에는 제3자가 저작권을 보유한 여러 구성요소가 함께 번들되어 있습니다. **이들 구성요소는 각자의 원 라이선스가 그대로 유지되며, 루트 MIT 라이선스의 적용을 받지 않습니다.** 아래 표는 카테고리별로 번들된 제3자 구성요소와 그 라이선스·저작권자를 정리한 것입니다.

경로는 저장소 루트(`/home/kagla/g5se`) 기준 상대경로입니다.

---

## A. 제3자 LGPL-2.1

원저작자가 LGPL-2.1로 배포한 구성요소입니다. SIR(에스아이알소프트)이 MIT로 재라이선스할 수 없으며, 원 라이선스가 유지됩니다.

| 구성요소 | 경로 | 라이선스 | 저작권자 |
|---|---|---|---|
| PHPMailer | `vendor/phpmailer/phpmailer/` | LGPL-2.1-only | PHPMailer authors |
| HTML Purifier | `app/plugin/htmlpurifier/` | LGPL-2.1 | Edward Z. Yang |
| SmartEditor2 | `app/plugin/editor/smarteditor2/` | LGPL-2.1 | NAVER Corp. |
| sha256 | `app/shop/inicis/libs/sha256.inc.php` | LGPL-2.1 | Nanolink Solutions |

## B. GPL / LGPL / MPL 삼중 라이선스

| 구성요소 | 경로 | 라이선스 | 저작권자 |
|---|---|---|---|
| CKEditor 4 | `app/plugin/editor/ckeditor4/` | GPL v2+ / LGPL 2.1+ / MPL 1.1+ (삼중, 택일 가능) | CKSource (Frederico Knabben) |

## C. MIT / GPL v2 이중 라이선스 (MIT 택일 가능)

| 구성요소 | 경로 | 라이선스 | 저작권자 |
|---|---|---|---|
| jqPlot | `app/plugin/jqplot/` | MIT / GPL v2 (이중, 택일 가능) | Chris Leonello |
| HTML5 Shiv | `app/js/html5.js` | MIT / GPL v2 (이중, 택일 가능) | Alexander Farkas 외 |

## D. SIR 자기 소유 LGPL (참고 표기)

SIR(에스아이알소프트) 자신이 저작권을 보유하고 LGPL로 배포한 코드입니다. SIR이 MIT로 전환할 수 있으나, 현재 배포본 기준으로 참고 표기합니다.

| 구성요소 | 경로 | 라이선스 | 저작권자 |
|---|---|---|---|
| 그누보드5 코어 | `app/LICENSE.txt` | LGPL | (주)에스아이알소프트 |
| basic 테마 | `app/theme/basic/readme.txt` | LGPL | (주)에스아이알소프트 |

## E. 상용 / 독점 (오픈소스 아님)

아래 구성요소는 오픈소스가 아니며, 루트 MIT 라이선스와 무관합니다. 각 벤더의 이용약관·계약이 적용됩니다. **각 벤더 SDK 및 컴파일된 바이너리는 오픈소스 재배포 대상이 아닙니다.**

| 구성요소 | 경로 | 라이선스 | 저작권자 / 벤더 |
|---|---|---|---|
| CHEditor5 | `app/plugin/editor/cheditor5/` | 상용/독점 (벤더 약관) | CHSOFT |
| 결제(PG) — KCP | `app/shop/kcp/`, `app/plugin/kcpcert/` | 상용/독점 (벤더 약관) | KCP |
| 결제(PG) — KG이니시스 | `app/shop/inicis/`, `app/plugin/inicert/` | 상용/독점 (벤더 약관) | KG이니시스 |
| 결제(PG) — LG U+ | `app/shop/lg/`, `app/plugin/lgxpay/lgdacom/` | 상용/독점 (벤더 약관) | LG U+ |
| 결제(PG) — NICEPAY | `app/shop/nicepay/` | 상용/독점 (벤더 약관) | NICEPAY |
| 결제(PG) — 토스 | `app/shop/toss/` | 상용/독점 (벤더 약관) | 토스페이먼츠 |
| 결제(PG) — 카카오페이 | `app/shop/kakaopay/` | 상용/독점 (벤더 약관) | 카카오페이 |
| 결제(PG) — 네이버페이 | `app/shop/naverpay/` | 상용/독점 (벤더 약관) | 네이버페이 |
| 본인인증 — okname | `app/plugin/okname/` | 상용/독점 (벤더 약관) | 드림시큐리티 |
| 본인인증 — KCP/이니시스 인증 | `app/plugin/kcpcert/`, `app/plugin/inicert/` | 상용/독점 (벤더 약관) | KCP / KG이니시스 |
| KISA SEED | `app/plugin/inicert/libs/KISA_SEED_CBC.php` | 한국인터넷진흥원 배포 조건 | 한국인터넷진흥원(KISA) |
| Adobe swfobject / AC_OETags | `app/plugin/editor/cheditor5/popup/js/AC_OETags.js` 등 | Adobe 배포 조건 | Adobe |

## F. 허용적 라이선스 (MIT / BSD / OFL / Apache / CC-BY)

루트 MIT와 충돌하지 않으나, 각 구성요소의 저작권 고지는 유지되어야 합니다.

### MIT

| 구성요소 | 경로 | 저작권자 |
|---|---|---|
| jQuery + Migrate | `app/js/jquery-1.12.4.min.js`, `app/js/jquery-1.8.3.min.js`, `app/js/jquery-migrate-1.4.1.min.js` | jQuery Foundation / OpenJS Foundation |
| Owl Carousel | `app/js/owlcarousel` | Bartosz Wojciechowski 외 |
| Swiper | `app/js/swiper` | Vladimir Kharlampidi |
| Tooltipster | `app/js/` (tooltipster) | Caleb Jacob 외 |
| niceScroll | `app/js/` (nicescroll) | InuYaksa |
| bxSlider | `app/js/` (bxslider) | Steven Wanderski |
| Modernizr | `app/js/` (modernizr) | Modernizr team (MIT & BSD) |
| Browscap | `app/plugin/browscap` | Browscap authors |
| reCAPTCHA | `app/plugin/recaptcha`, `app/plugin/recaptcha_inv` | Google |
| TwitterOAuth | `app/plugin/sns/twitter` | Abraham Williams |
| blueimp fileupload / SWFUpload | `app/plugin/editor/smarteditor2/` (동봉) | Sebastian Tschan / SWFUpload team |
| PHP-Hook | `app/lib/Hook` | PHP-Hook authors |
| PhpSpreadsheet 외 vendor 다수 | `vendor/` | 각 저작권자 (composer.json 참조) |

### BSD

| 구성요소 | 경로 | 저작권자 |
|---|---|---|
| Services_JSON | `app/plugin/lgxpay/lgdacom/JSON.php`, `app/plugin/sms5/JSON.php`, `app/shop/inicis/libs/JSON.php` 등 | Michal Migurski 외 |
| pbkdf2 (compat) | `app/lib/pbkdf2.compat.php` | Taylor Hornby / Kijin Sung |

### 커스텀 free (저작권 고지 유지)

| 구성요소 | 경로 | 저작권자 |
|---|---|---|
| KCAPTCHA | `app/plugin/kcaptcha/` | Kruglov Sergei |

### CC-BY 3.0

| 구성요소 | 경로 | 저작권자 |
|---|---|---|
| canvasText (jqPlot 내장) | `app/plugin/jqplot/plugins/jqplot.canvasTextRenderer.js` | Fabien Ménager 외 |

### OFL 1.1 + MIT

| 구성요소 | 경로 | 저작권자 |
|---|---|---|
| Font Awesome 4.7 | `app/js/font-awesome/` | Dave Gandy / Fonticons (폰트 OFL 1.1, 코드 MIT) |

### Apache-2.0

| 구성요소 | 경로 | 저작권자 |
|---|---|---|
| excanvas | `app/plugin/jqplot/excanvas.js` | Google |

## G. 미확인 (원배포본 확인 필요)

| 구성요소 | 경로 | 비고 |
|---|---|---|
| HybridAuth | `app/plugin/social/Hybrid/` | 헤더가 외부 `licenses.html`(http://hybridauth.sourceforge.net/licenses.html)를 참조. 통상 허용적(HybridAuth 2.x는 MIT/BSD 계열)으로 알려짐. 정확한 조건은 원배포본 확인 필요. |

---

## 주의

- 위 **E. 상용/독점** 구성요소(전자결제 PG, 본인인증 SDK, KISA SEED, 컴파일된 벤더 바이너리 등)는 **오픈소스가 아니며 오픈소스 재배포 대상이 아닙니다.** 각 벤더와의 계약·이용약관에 따라 사용해야 합니다.
- **A~C, F, G**의 제3자 구성요소는 루트 MIT와 별개로 각자의 원 라이선스가 유지됩니다. 재배포 시 각 구성요소의 저작권 고지 및 라이선스 조건을 준수해야 합니다.
- 루트 [`LICENSE`](LICENSE)(MIT)는 g5se / 그누보드 자체 코드에만 적용됩니다.
