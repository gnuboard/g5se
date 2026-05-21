<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);

$list_count = (is_array($list) && $list) ? count($list) : 0;
$thumb_w = 320;
$thumb_h = 240;
?>

<section class="m-latest-gallery">
    <header class="m-latest-head">
        <h2 class="m-latest-title">
            <a href="<?php echo get_pretty_url($bo_table); ?>"><?php echo $bo_subject ?></a>
        </h2>
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="m-latest-more">
            더보기
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </header>

    <ul class="m-latest-gallery-grid">
        <?php for ($i = 0; $i < $list_count; $i++) {
            $thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'], $thumb_w, $thumb_h, false, true);
            $has_thumb = !empty($thumb['src']);
        ?>
        <li class="m-latest-gallery-item">
            <a href="<?php echo get_pretty_url($bo_table, $list[$i]['wr_id']); ?>" class="m-latest-gallery-link">
                <div class="m-latest-gallery-thumb">
                    <?php if ($has_thumb) { ?>
                    <img src="<?php echo $thumb['src'] ?>" alt="<?php echo htmlspecialchars($thumb['alt'] ?? '') ?>" loading="lazy">
                    <?php } else { ?>
                    <div class="m-latest-gallery-thumb-empty">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>
                        </svg>
                    </div>
                    <?php } ?>

                    <?php if ($list[$i]['comment_cnt']) { ?>
                    <span class="m-latest-gallery-cmt">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <?php echo $list[$i]['wr_comment'] ?>
                    </span>
                    <?php } ?>
                </div>
                <div class="m-latest-gallery-body">
                    <p class="m-latest-gallery-subject"><?php if ($list[$i]['is_notice']) { ?><span class="m-latest-notice">공지</span> <?php } ?><?php echo $list[$i]['subject'] ?></p>
                    <div class="m-latest-gallery-meta">
                        <span class="m-latest-gallery-name"><?php echo $list[$i]['name'] ?></span>
                        <span class="m-latest-gallery-date"><?php echo $list[$i]['datetime2'] ?></span>
                    </div>
                </div>
            </a>
        </li>
        <?php } ?>

        <?php if ($list_count == 0) { ?>
        <li class="m-latest-gallery-empty">아직 글이 없습니다.</li>
        <?php } ?>
    </ul>
</section>
