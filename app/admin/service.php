<?php
/*
 * /admin/service — 부가서비스 안내 페이지.
 */
$sub_menu = '100400';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '부가서비스';
admin_layout_start($g5['title'], 'service');
?>
<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
<header class="flex flex-col items-start gap-3 mb-5">
    <h2 class="text-xl font-bold tracking-tight"><?php echo get_text($g5['title']) ?></h2>
    <a href="https://sir.kr/services" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-1.5 h-9 px-3.5 rounded-md bg-blue-600 hover:bg-blue-700 text-sm font-medium text-white no-underline transition-colors" aria-label="PG 서비스 전체보기">
        PG 서비스 전체보기
        <span aria-hidden="true">↗</span>
    </a>
</header>
<div class="legacy-admin-content space-y-4">

<div class="local_desc02 local_desc">
    <p>아래의 서비스들은 영카트에서 이미 지원하는 기능으로 별도의 개발이 필요 없으며 서비스 신청후 바로 사용 할수 있습니다.</p>
</div>

<div class="service_wrap">
    <div class="sevice_1 svc_card">
        <h3>신용카드 전자결제 서비스<br><span>(계좌이체, 가상계좌 결제 포함)</span></h3>

        <ul>
            <li><a href="https://sir.kr/services/pg/inicis" target="_blank" rel="noopener noreferrer"><img src="<?php echo G5_ADMIN_URL ?>/img/service-badge-inicis.svg" alt="KG이니시스 전자결제 서비스"></a></li>
            <li><a href="https://sir.kr/services/pg/kcp" target="_blank" rel="noopener noreferrer"><img src="<?php echo G5_ADMIN_URL ?>/img/service-badge-kcp.svg" alt="NHN KCP 전자결제 서비스"></a></li>
            <li><a href="https://sir.kr/services/pg/nice" target="_blank" rel="noopener noreferrer"><img src="<?php echo G5_ADMIN_URL ?>/img/service-badge-nice.svg" alt="나이스페이먼츠 전자결제 서비스"></a></li>
            <li><a href="https://sir.kr/services/pg/toss" target="_blank" rel="noopener noreferrer"><img src="<?php echo G5_ADMIN_URL ?>/img/service-badge-toss.svg" alt="토스페이먼츠 전자결제 서비스"></a></li>
        </ul>
    </div>

    <div class="sevice_1 svc_phone">
        <h3>본인확인 서비스</h3>

        <ul>
            <li><a href="https://sir.kr/services/auth/kcp" target="_blank" rel="noopener noreferrer"><img src="<?php echo G5_ADMIN_URL ?>/img/service-badge-kcp.svg" alt="NHN KCP 휴대폰 본인확인"></a></li>
            <li><a href="https://sir.kr/services/auth/inicis" target="_blank" rel="noopener noreferrer"><img src="<?php echo G5_ADMIN_URL ?>/img/service-badge-inicis.svg" alt="KG이니시스 통합인증"></a></li>
        </ul>
    </div>

    <div class="service_2">
        <div class="svc_ri svc_sms">
            <div class="svc_a">
                <h3>SMS 문자 서비스</h3>
                <p>주문이나 배송시에 상점운영자 또는 고객에게 휴대폰으로 단문메세지 (최대 한글 40자, 영문 80자)를 발송합니다.</p>
            </div>
            <div class="svc_btn2"><a href="https://sir.kr/services/message/icode" target="_blank" rel="noopener noreferrer" aria-label="아이코드 SMS/LMS 문자서비스 자세히 보기">아이코드 <span aria-hidden="true">→</span></a></div>
        </div>

    </div>
</div>

</div><!-- /.legacy-admin-content -->
</main>
<?php admin_layout_end(); ?>
<?php
// /admin/service — modern shell wrap end
