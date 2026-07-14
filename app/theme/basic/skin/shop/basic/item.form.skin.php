<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);
?>
<div id="sit_ov_from">
	<form name="fitem" method="post" action="<?php echo $action_url; ?>" onsubmit="return fitem_submit(this);">
	<input type="hidden" name="it_id[]" value="<?php echo $it_id; ?>">
	<input type="hidden" name="sw_direct">
	<input type="hidden" name="url">
	
	<div id="sit_ov_wrap">
	    <!-- 상품이미지 미리보기 시작 { -->
	    <div id="sit_pvi">
	        <h2 class="sit_title_mobile"><?php echo stripslashes($it['it_name']); ?></h2>
	        <?php
	        $gallery_images = array();
	        for($i=1; $i<=10; $i++) {
	            if(!$it['it_img'.$i])
	                continue;

	            $img = get_it_thumbnail($it['it_img'.$i], $default['de_mimg_width'], $default['de_mimg_height']);

	            if($img) {
	                $gallery_images[] = array(
	                    'display' => $img,
	                    'thumb' => get_it_thumbnail($it['it_img'.$i], 80, 80),
	                    'large' => run_replace('get_item_image_url', G5_DATA_URL.'/item/'.$it['it_img'.$i], $it, $i),
	                    'no' => $i,
	                );
	            }
	        }
	        $gallery_count = count($gallery_images);
	        ?>
	        <div id="sit_pvi_big" class="sit_gallery_stage">
	            <?php if ($gallery_count) { ?>
	                <?php foreach ($gallery_images as $gallery_index => $gallery_image) { ?>
	                <button type="button" class="sit_gallery_slide<?php echo $gallery_index === 0 ? ' is-active' : ''; ?>" data-gallery-index="<?php echo $gallery_index; ?>" data-large-src="<?php echo htmlspecialchars($gallery_image['large'], ENT_QUOTES); ?>" aria-label="<?php echo ($gallery_index + 1); ?>번째 상품 이미지 크게 보기"<?php echo $gallery_index === 0 ? '' : ' hidden'; ?>>
	                    <?php echo $gallery_image['display']; ?>
	                </button>
	                <?php } ?>

	                <?php if ($gallery_count > 1) { ?>
	                <button type="button" class="sit_gallery_nav sit_gallery_prev" aria-label="이전 상품 이미지"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
	                <button type="button" class="sit_gallery_nav sit_gallery_next" aria-label="다음 상품 이미지"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
	                <?php } ?>
	                <span class="sit_gallery_count" aria-live="polite"><b>1</b> / <?php echo $gallery_count; ?></span>
	                <button type="button" class="sit_gallery_zoom" aria-label="현재 상품 이미지 확대"><i class="fa fa-search-plus" aria-hidden="true"></i></button>
	            <?php } else { ?>
	                <img src="<?php echo G5_SHOP_URL; ?>/img/no_image.gif" alt="등록된 상품 이미지가 없습니다">
	            <?php } ?>
	        </div>

	        <?php if ($gallery_count > 1) { ?>
	        <ul id="sit_pvi_thumb" aria-label="상품 이미지 선택">
	            <?php foreach ($gallery_images as $gallery_index => $gallery_image) { ?>
	            <li>
	                <button type="button" class="img_thumb<?php echo $gallery_index === 0 ? ' is-active' : ''; ?>" data-gallery-index="<?php echo $gallery_index; ?>" aria-label="<?php echo ($gallery_index + 1); ?>번째 이미지 보기" aria-pressed="<?php echo $gallery_index === 0 ? 'true' : 'false'; ?>">
	                    <?php echo $gallery_image['thumb']; ?>
	                </button>
	            </li>
	            <?php } ?>
	        </ul>
	        <?php } ?>

	        <?php if ($gallery_count) { ?>
	        <div id="sit_gallery_lightbox" class="sit_gallery_lightbox" role="dialog" aria-modal="true" aria-label="상품 이미지 확대 보기" hidden>
	            <button type="button" class="sit_gallery_lightbox_backdrop" aria-label="확대 보기 닫기"></button>
	            <div class="sit_gallery_lightbox_panel">
	                <button type="button" class="sit_gallery_lightbox_close" aria-label="확대 보기 닫기"><i class="fa fa-times" aria-hidden="true"></i></button>
	                <div class="sit_gallery_lightbox_viewport">
	                    <img src="" alt="<?php echo get_text(stripslashes($it['it_name'])); ?> 확대 이미지" draggable="false">
	                </div>
	                <?php if ($gallery_count > 1) { ?>
	                <button type="button" class="sit_gallery_lightbox_nav sit_gallery_lightbox_prev" aria-label="이전 확대 이미지"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
	                <button type="button" class="sit_gallery_lightbox_nav sit_gallery_lightbox_next" aria-label="다음 확대 이미지"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
	                <?php } ?>
	                <div class="sit_gallery_zoom_controls" aria-label="이미지 확대 조절">
	                    <button type="button" class="sit_gallery_zoom_out" aria-label="축소"><i class="fa fa-minus" aria-hidden="true"></i></button>
	                    <button type="button" class="sit_gallery_zoom_reset" aria-label="원래 크기로">100%</button>
	                    <button type="button" class="sit_gallery_zoom_in" aria-label="확대"><i class="fa fa-plus" aria-hidden="true"></i></button>
	                </div>
	                <span class="sit_gallery_lightbox_count"><b>1</b> / <?php echo $gallery_count; ?></span>
	            </div>
	        </div>
	        <?php } ?>
	    </div>
	    <!-- } 상품이미지 미리보기 끝 -->
	
	    <!-- 상품 요약정보 및 구매 시작 { -->
	    <section id="sit_ov" class="2017_renewal_itemform">
	        <h2 id="sit_title"><?php echo stripslashes($it['it_name']); ?> <span class="sound_only">요약정보 및 구매</span></h2>
	        <p id="sit_desc"><?php echo $it['it_basic']; ?></p>
	        <?php if($is_orderable) { ?>
	        <p id="sit_opt_info">
	            상품 선택옵션 <?php echo $option_count; ?> 개, 추가옵션 <?php echo $supply_count; ?> 개
	        </p>
	        <?php } ?>
	        
	        <div id="sit_star_sns">
	            <?php if ($star_score) { ?>
	            <span class="sound_only">고객평점</span> 
	            <img src="<?php echo G5_SHOP_URL; ?>/img/s_star<?php echo $star_score?>.png" alt="" class="sit_star" width="100">
	            <span class="sound_only">별<?php echo $star_score?>개</span> 
	            <?php } ?>
	            
	            <span class="">사용후기 <?php echo $it['it_use_cnt']; ?> 개</span>
	            
	            <div id="sit_btn_opt">
	            	<span id="btn_wish"><i class="fa fa-heart-o" aria-hidden="true"></i><span class="sound_only">위시리스트</span><span class="btn_wish_num"><?php echo get_wishlist_count_by_item($it['it_id']); ?></span></span>
	            	<button type="button" class="btn_sns_share"><i class="fa fa-share-alt" aria-hidden="true"></i><span class="sound_only">sns 공유</span></button>
	            	<div class="sns_area">
	            		<?php echo $sns_share_links; ?>
	            		<a href="javascript:popup_item_recommend('<?php echo $it['it_id']; ?>');" id="sit_btn_rec"><i class="fa fa-envelope-o" aria-hidden="true"></i><span class="sound_only">추천하기</span></a>
	            	</div>
	        	</div>
	        </div>
	        <script>
	        $(".btn_sns_share").click(function(){
	            $(".sns_area").show();
	        });
	        $(document).mouseup(function (e){
	            var container = $(".sns_area");
	            if( container.has(e.target).length === 0)
	            container.hide();
	        });
	        </script>
	        
	        <div class="sit_info">
	            <table class="sit_ov_tbl">
	            <colgroup>
	                <col class="grid_3">
	                <col>
	            </colgroup>
	            <tbody>
	            
	            <?php if (!$it['it_use']) { // 판매가능이 아닐 경우 ?>
	            <tr>
	                <th scope="row">판매가격</th>
	                <td>판매중지</td>
	            </tr>
	            <?php } else if ($it['it_tel_inq']) { // 전화문의일 경우 ?>
	            <tr>
	                <th scope="row">판매가격</th>
	                <td>전화문의</td>
	            </tr>
	            <?php } else { // 전화문의가 아닐 경우?>
	            <?php if ($it['it_cust_price']) { ?>
	            <tr>
	                <th scope="row">시중가격</th>
	                <td><?php echo display_price($it['it_cust_price']); ?></td>
	            </tr>
	            <?php } // 시중가격 끝 ?>
	
	            <tr class="tr_price">
	                <th scope="row">판매가격</th>
	                <td>
	                    <strong><?php echo display_price(get_price($it)); ?></strong>
	                    <input type="hidden" id="it_price" value="<?php echo get_price($it); ?>">
	                </td>
	            </tr>
	            <?php } ?>
	            	
	            <?php if ($it['it_maker']) { ?>
	            <tr>
	                <th scope="row">제조사</th>
	                <td><?php echo $it['it_maker']; ?></td>
	            </tr>
	            <?php } ?>
	
	            <?php if ($it['it_origin']) { ?>
	            <tr>
	                <th scope="row">원산지</th>
	                <td><?php echo $it['it_origin']; ?></td>
	            </tr>
	            <?php } ?>
	
	            <?php if ($it['it_brand']) { ?>
	            <tr>
	                <th scope="row">브랜드</th>
	                <td><?php echo $it['it_brand']; ?></td>
	            </tr>
	            <?php } ?>
	
	            <?php if ($it['it_model']) { ?>
	            <tr>
	                <th scope="row">모델</th>
	                <td><?php echo $it['it_model']; ?></td>
	            </tr>
	            <?php } ?>

	            <?php
	            /* 재고 표시하는 경우 주석 해제
	            <tr>
	                <th scope="row">재고수량</th>
	                <td><?php echo number_format(get_it_stock_qty($it_id)); ?> 개</td>
	            </tr>
	            */
	            ?>
	
	            <?php if ($config['cf_use_point']) { // 포인트 사용한다면 ?>
	            <tr>
	                <th scope="row">포인트</th>
	                <td>
	                    <?php
	                    if($it['it_point_type'] == 2) {
	                        echo '구매금액(추가옵션 제외)의 '.$it['it_point'].'%';
	                    } else {
	                        $it_point = get_item_point($it);
	                        echo number_format($it_point).'점';
	                    }
	                    ?>
	                </td>
	            </tr>
	            <?php } ?>
	            <?php
	            $ct_send_cost_label = '배송비결제';
	
	            if($it['it_sc_type'] == 1)
	                $sc_method = '무료배송';
	            else {
	                if($it['it_sc_method'] == 1)
	                    $sc_method = '수령후 지불';
	                else if($it['it_sc_method'] == 2) {
	                    $ct_send_cost_label = '<label for="ct_send_cost">배송비결제</label>';
	                    $sc_method = '<select name="ct_send_cost" id="ct_send_cost">
	                                      <option value="0">주문시 결제</option>
	                                      <option value="1">수령후 지불</option>
	                                  </select>';
	                }
	                else
	                    $sc_method = '주문시 결제';
	            }
	            ?>
	            <tr>
	                <th><?php echo $ct_send_cost_label; ?></th>
	                <td><?php echo $sc_method; ?></td>
	            </tr>
	            <?php if($it['it_buy_min_qty']) { ?>
	            <tr>
	                <th>최소구매수량</th>
	                <td><?php echo number_format($it['it_buy_min_qty']); ?> 개</td>
	            </tr>
	            <?php } ?>
	            <?php if($it['it_buy_max_qty']) { ?>
	            <tr>
	                <th>최대구매수량</th>
	                <td><?php echo number_format($it['it_buy_max_qty']); ?> 개</td>
	            </tr>
	            <?php } ?>
	            </tbody>
	            </table>
	        </div>
	        <?php
	        if($option_item) {
	        ?>
	        <!-- 선택옵션 시작 { -->
	        <section class="sit_option">
	            <h3>선택옵션</h3>
	 
	            <?php // 선택옵션
	            echo $option_item;
	            ?>
	        </section>
	        <!-- } 선택옵션 끝 -->
	        <?php
	        }
	        ?>
	
	        <?php
	        if($supply_item) {
	        ?>
	        <!-- 추가옵션 시작 { -->
	        <section  class="sit_option">
	            <h3>추가옵션</h3>
	            <?php // 추가옵션
	            echo $supply_item;
	            ?>
	        </section>
	        <!-- } 추가옵션 끝 -->
	        <?php
	        }
	        ?>
	
	        <?php if ($is_orderable) { ?>
	        <!-- 선택된 옵션 시작 { -->
	        <section id="sit_sel_option">
	            <h3>선택된 옵션</h3>
	            <?php
	            if(!$option_item) {
	                if(!$it['it_buy_min_qty'])
	                    $it['it_buy_min_qty'] = 1;
	            ?>
	            <ul id="sit_opt_added">
	                <li class="sit_opt_list">
	                    <input type="hidden" name="io_type[<?php echo $it_id; ?>][]" value="0">
	                    <input type="hidden" name="io_id[<?php echo $it_id; ?>][]" value="">
	                    <input type="hidden" name="io_value[<?php echo $it_id; ?>][]" value="<?php echo $it['it_name']; ?>">
	                    <input type="hidden" class="io_price" value="0">
	                    <input type="hidden" class="io_stock" value="<?php echo $it['it_stock_qty']; ?>">
	                    <div class="opt_name">
	                        <span class="sit_opt_subj"><?php echo $it['it_name']; ?></span>
	                    </div>
	                    <div class="opt_count">
	                        <label for="ct_qty_<?php echo $i; ?>" class="sound_only">수량</label>
							<button type="button" class="sit_qty_minus"><i class="fa fa-minus" aria-hidden="true"></i><span class="sound_only">감소</span></button>
	                        <input type="text" name="ct_qty[<?php echo $it_id; ?>][]" value="<?php echo $it['it_buy_min_qty']; ?>" id="ct_qty_<?php echo $i; ?>" class="num_input" size="5">
	                        <button type="button" class="sit_qty_plus"><i class="fa fa-plus" aria-hidden="true"></i><span class="sound_only">증가</span></button>
	                        <span class="sit_opt_prc">+0원</span>
	                    </div>
	                </li>
	            </ul>
	            <script>
	            $(function() {
	                price_calculate();
	            });
	            </script>
	            <?php } ?>
	        </section>
	        <!-- } 선택된 옵션 끝 -->
	
	        <!-- 총 구매액 -->
	        <div id="sit_tot_price"></div>
	        <?php } ?>
	
	        <?php if($is_soldout) { ?>
	        <p id="sit_ov_soldout">상품의 재고가 부족하여 구매할 수 없습니다.</p>
	        <?php } ?>
	
	        <div id="sit_ov_btn">
	            <?php if ($is_orderable) { ?>
	            <button type="submit" onclick="document.pressed=this.value;" value="장바구니" class="sit_btn_cart">장바구니</button>
	            <button type="submit" onclick="document.pressed=this.value;" value="바로구매" class="sit_btn_buy">바로구매</button>
	            <?php } ?>
	            <a href="javascript:item_wish(document.fitem, '<?php echo $it['it_id']; ?>');" class="sit_btn_wish"><i class="fa fa-heart-o" aria-hidden="true"></i><span class="sound_only">위시리스트</span></a>
	            	
	            <?php if(!$is_orderable && $it['it_soldout'] && $it['it_stock_sms']) { ?>
	            <a href="javascript:popup_stocksms('<?php echo $it['it_id']; ?>');" id="sit_btn_alm">재입고알림</a>
	            <?php } ?>
	            <?php if ($naverpay_button_js) { ?>
	            <div class="itemform-naverpay"><?php echo $naverpay_request_js.$naverpay_button_js; ?></div>
	            <?php } ?>
	        </div>
	
	        <script>
	        // 상품보관
	        function item_wish(f, it_id)
	        {
	            f.url.value = "<?php echo G5_SHOP_URL; ?>/wishupdate.php?it_id="+it_id;
	            f.action = "<?php echo G5_SHOP_URL; ?>/wishupdate.php";
	            f.submit();
	        }
	
	        // 추천메일
	        function popup_item_recommend(it_id)
	        {
	            if (!g5_is_member)
	            {
	                if (confirm("회원만 추천하실 수 있습니다."))
	                    document.location.href = "<?php echo G5_BBS_URL; ?>/login.php?url=<?php echo urlencode(shop_item_url($it_id)); ?>";
	            }
	            else
	            {
	                url = "<?php echo G5_SHOP_URL; ?>/itemrecommend?it_id=" + it_id;
	                opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
	                popup_window(url, "itemrecommend", opt);
	            }
	        }
	
	        // 재입고SMS 알림
	        function popup_stocksms(it_id)
	        {
	            url = "<?php echo G5_SHOP_URL; ?>/itemstocksms.php?it_id=" + it_id;
	            opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
	            popup_window(url, "itemstocksms", opt);
	        }
	        </script>
	    </section>
	    <!-- } 상품 요약정보 및 구매 끝 -->
	</div>
	<!-- 다른 상품 보기 시작 { -->
    <div id="sit_siblings">
	    <?php
	    if ($prev_href || $next_href) {
	        echo $prev_href.$prev_title.$prev_href2;
	        echo $next_href.$next_title.$next_href2;
	    } else {
	        echo '<span class="sound_only">이 분류에 등록된 다른 상품이 없습니다.</span>';
	    }
	    ?>
	</div>   
    <!-- } 다른 상품 보기 끝 -->
	</form>
</div>

<script>
$(function(){
    var gallery = document.getElementById('sit_pvi');
    if (!gallery) return;

    var slides = Array.prototype.slice.call(gallery.querySelectorAll('.sit_gallery_slide'));
    var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('.img_thumb'));
    var count = gallery.querySelector('.sit_gallery_count b');
    var lightbox = document.getElementById('sit_gallery_lightbox');
    var lightboxImage = lightbox ? lightbox.querySelector('img') : null;
    var lightboxViewport = lightbox ? lightbox.querySelector('.sit_gallery_lightbox_viewport') : null;
    var lightboxCount = lightbox ? lightbox.querySelector('.sit_gallery_lightbox_count b') : null;
    var shell = document.querySelector('.m-shell');
    var current = 0;
    var lastFocus = null;
    var imageScale = 1;
    var imageX = 0;
    var imageY = 0;
    var pointers = new Map();
    var pinchDistance = 0;
    var pinchScale = 1;

    function renderZoom() {
        if (!lightboxImage) return;
        lightboxImage.style.transform = 'translate3d(' + imageX + 'px,' + imageY + 'px,0) scale(' + imageScale + ')';
        lightboxImage.classList.toggle('is-zoomed', imageScale > 1);
        var reset = lightbox ? lightbox.querySelector('.sit_gallery_zoom_reset') : null;
        if (reset) reset.textContent = Math.round(imageScale * 100) + '%';
    }

    function setZoom(scale, x, y) {
        imageScale = Math.max(1, Math.min(4, scale));
        imageX = imageScale === 1 ? 0 : x;
        imageY = imageScale === 1 ? 0 : y;
        renderZoom();
    }

    function resetZoom() {
        setZoom(1, 0, 0);
    }

    function show(index) {
        if (!slides.length) return;
        current = (index + slides.length) % slides.length;
        slides.forEach(function (slide, slideIndex) {
            var active = slideIndex === current;
            slide.hidden = !active;
            slide.classList.toggle('is-active', active);
        });
        thumbs.forEach(function (thumb, thumbIndex) {
            var active = thumbIndex === current;
            thumb.classList.toggle('is-active', active);
            thumb.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        if (count) count.textContent = current + 1;
        if (lightbox && !lightbox.hidden) syncLightbox();
    }

    function syncLightbox() {
        if (!lightboxImage || !slides[current]) return;
        lightboxImage.src = slides[current].getAttribute('data-large-src');
        resetZoom();
        if (lightboxCount) lightboxCount.textContent = current + 1;
    }

    function openLightbox() {
        if (!lightbox) return;
        lastFocus = document.activeElement;
        syncLightbox();
        lightbox.hidden = false;
        if (shell) shell.style.overflowY = 'hidden';
        lightbox.querySelector('.sit_gallery_lightbox_close').focus();
    }

    function closeLightbox() {
        if (!lightbox || lightbox.hidden) return;
        lightbox.hidden = true;
        if (shell) shell.style.overflowY = '';
        if (lastFocus) lastFocus.focus();
    }

    thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            show(parseInt(thumb.getAttribute('data-gallery-index'), 10));
        });
    });
    slides.forEach(function (slide) {
        slide.addEventListener('click', openLightbox);
    });

    var prev = gallery.querySelector('.sit_gallery_prev');
    var next = gallery.querySelector('.sit_gallery_next');
    var zoom = gallery.querySelector('.sit_gallery_zoom');
    if (prev) prev.addEventListener('click', function () { show(current - 1); });
    if (next) next.addEventListener('click', function () { show(current + 1); });
    if (zoom) zoom.addEventListener('click', openLightbox);

    if (lightbox) {
        lightbox.querySelector('.sit_gallery_lightbox_close').addEventListener('click', closeLightbox);
        lightbox.querySelector('.sit_gallery_lightbox_backdrop').addEventListener('click', closeLightbox);
        var lightboxPrev = lightbox.querySelector('.sit_gallery_lightbox_prev');
        var lightboxNext = lightbox.querySelector('.sit_gallery_lightbox_next');
        if (lightboxPrev) lightboxPrev.addEventListener('click', function () { show(current - 1); });
        if (lightboxNext) lightboxNext.addEventListener('click', function () { show(current + 1); });
        lightbox.querySelector('.sit_gallery_zoom_in').addEventListener('click', function () { setZoom(imageScale + .5, imageX, imageY); });
        lightbox.querySelector('.sit_gallery_zoom_out').addEventListener('click', function () { setZoom(imageScale - .5, imageX, imageY); });
        lightbox.querySelector('.sit_gallery_zoom_reset').addEventListener('click', resetZoom);
    }

    function addSwipe(element) {
        if (!element || slides.length < 2) return;
        var startX = 0;
        element.addEventListener('touchstart', function (event) {
            startX = event.changedTouches[0].clientX;
        }, {passive: true});
        element.addEventListener('touchend', function (event) {
            var distance = event.changedTouches[0].clientX - startX;
            if (Math.abs(distance) < 45) return;
            show(current + (distance < 0 ? 1 : -1));
        }, {passive: true});
    }
    addSwipe(gallery.querySelector('.sit_gallery_stage'));

    if (lightboxViewport) {
        lightboxViewport.addEventListener('dblclick', function () {
            setZoom(imageScale > 1 ? 1 : 2, 0, 0);
        });
        lightboxViewport.addEventListener('wheel', function (event) {
            event.preventDefault();
            setZoom(imageScale + (event.deltaY < 0 ? .25 : -.25), imageX, imageY);
        }, {passive: false});
        lightboxViewport.addEventListener('pointerdown', function (event) {
            pointers.set(event.pointerId, {x: event.clientX, y: event.clientY});
            lightboxViewport.setPointerCapture(event.pointerId);
            if (pointers.size === 2) {
                var points = Array.from(pointers.values());
                pinchDistance = Math.hypot(points[0].x - points[1].x, points[0].y - points[1].y);
                pinchScale = imageScale;
            }
        });
        lightboxViewport.addEventListener('pointermove', function (event) {
            var previous = pointers.get(event.pointerId);
            if (!previous) return;
            pointers.set(event.pointerId, {x: event.clientX, y: event.clientY});
            if (pointers.size === 2) {
                var points = Array.from(pointers.values());
                var distance = Math.hypot(points[0].x - points[1].x, points[0].y - points[1].y);
                setZoom(pinchScale * distance / Math.max(pinchDistance, 1), imageX, imageY);
            } else if (imageScale > 1) {
                imageX += event.clientX - previous.x;
                imageY += event.clientY - previous.y;
                renderZoom();
            }
        });
        function releasePointer(event) {
            pointers.delete(event.pointerId);
            if (pointers.size < 2) pinchDistance = 0;
        }
        lightboxViewport.addEventListener('pointerup', releasePointer);
        lightboxViewport.addEventListener('pointercancel', releasePointer);
    }

    document.addEventListener('keydown', function (event) {
        if (!lightbox || lightbox.hidden) return;
        if (event.key === 'Escape') closeLightbox();
        if (event.key === 'ArrowLeft') show(current - 1);
        if (event.key === 'ArrowRight') show(current + 1);
    });

    show(0);
});

function fsubmit_check(f)
{
    // 판매가격이 0 보다 작다면
    if (document.getElementById("it_price").value < 0) {
        alert("전화로 문의해 주시면 감사하겠습니다.");
        return false;
    }

    if($(".sit_opt_list").length < 1) {
        alert("상품의 선택옵션을 선택해 주십시오.");
        return false;
    }

    var val, io_type, result = true;
    var sum_qty = 0;
    var min_qty = parseInt(<?php echo $it['it_buy_min_qty']; ?>);
    var max_qty = parseInt(<?php echo $it['it_buy_max_qty']; ?>);
    var $el_type = $("input[name^=io_type]");

    $("input[name^=ct_qty]").each(function(index) {
        val = $(this).val();

        if(val.length < 1) {
            alert("수량을 입력해 주십시오.");
            result = false;
            return false;
        }

        if(val.replace(/[0-9]/g, "").length > 0) {
            alert("수량은 숫자로 입력해 주십시오.");
            result = false;
            return false;
        }

        if(parseInt(val.replace(/[^0-9]/g, "")) < 1) {
            alert("수량은 1이상 입력해 주십시오.");
            result = false;
            return false;
        }

        io_type = $el_type.eq(index).val();
        if(io_type == "0")
            sum_qty += parseInt(val);
    });

    if(!result) {
        return false;
    }

    if(min_qty > 0 && sum_qty < min_qty) {
        alert("선택옵션 개수 총합 "+number_format(String(min_qty))+"개 이상 주문해 주십시오.");
        return false;
    }

    if(max_qty > 0 && sum_qty > max_qty) {
        alert("선택옵션 개수 총합 "+number_format(String(max_qty))+"개 이하로 주문해 주십시오.");
        return false;
    }

    return true;
}

// 바로구매, 장바구니 폼 전송
function fitem_submit(f)
{
    f.action = "<?php echo $action_url; ?>";
    f.target = "";

    if (document.pressed == "장바구니") {
        f.sw_direct.value = 0;
    } else { // 바로구매
        f.sw_direct.value = 1;
    }

    // 판매가격이 0 보다 작다면
    if (document.getElementById("it_price").value < 0) {
        alert("전화로 문의해 주시면 감사하겠습니다.");
        return false;
    }

    if($(".sit_opt_list").length < 1) {
        alert("상품의 선택옵션을 선택해 주십시오.");
        return false;
    }

    var val, io_type, result = true;
    var sum_qty = 0;
    var min_qty = parseInt(<?php echo $it['it_buy_min_qty']; ?>);
    var max_qty = parseInt(<?php echo $it['it_buy_max_qty']; ?>);
    var $el_type = $("input[name^=io_type]");

    $("input[name^=ct_qty]").each(function(index) {
        val = $(this).val();

        if(val.length < 1) {
            alert("수량을 입력해 주십시오.");
            result = false;
            return false;
        }

        if(val.replace(/[0-9]/g, "").length > 0) {
            alert("수량은 숫자로 입력해 주십시오.");
            result = false;
            return false;
        }

        if(parseInt(val.replace(/[^0-9]/g, "")) < 1) {
            alert("수량은 1이상 입력해 주십시오.");
            result = false;
            return false;
        }

        io_type = $el_type.eq(index).val();
        if(io_type == "0")
            sum_qty += parseInt(val);
    });

    if(!result) {
        return false;
    }

    if(min_qty > 0 && sum_qty < min_qty) {
        alert("선택옵션 개수 총합 "+number_format(String(min_qty))+"개 이상 주문해 주십시오.");
        return false;
    }

    if(max_qty > 0 && sum_qty > max_qty) {
        alert("선택옵션 개수 총합 "+number_format(String(max_qty))+"개 이하로 주문해 주십시오.");
        return false;
    }

    return true;
}
</script>
<?php /* 2017 리뉴얼한 테마 적용 스크립트입니다. 기존 스크립트를 오버라이드 합니다. */ ?>
<script src="<?php echo G5_JS_URL; ?>/shop.override.js"></script>
