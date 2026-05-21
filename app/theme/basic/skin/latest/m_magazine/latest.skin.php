<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);

$list_count = (is_array($list) && $list) ? count($list) : 0;
$thumb_w = 480;
$thumb_h = 270;

// 첫번째 글이 featured, 나머지는 side
$feat = $list_count ? $list[0] : null;
$sides = $list_count > 1 ? array_slice($list, 1) : [];
?>

<section class="m-latest-mag">
    <header class="m-latest-head">
        <h2 class="m-latest-title">
            <a href="<?php echo get_pretty_url($bo_table); ?>"><?php echo $bo_subject ?></a>
        </h2>
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="m-latest-more">
            더보기
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </header>

    <?php if (!$feat) { ?>
    <p class="m-latest-mag-empty">아직 글이 없습니다.</p>
    <?php } else {
        $thumb = get_list_thumbnail($bo_table, $feat['wr_id'], $thumb_w, $thumb_h, false, true);
        $has_thumb = !empty($thumb['src']);
    ?>
    <div class="m-latest-mag-body<?php echo empty($sides) ? ' m-latest-mag-only' : '' ?>">

        <!-- Featured -->
        <a href="<?php echo get_pretty_url($bo_table, $feat['wr_id']); ?>" class="m-latest-mag-feat">
            <div class="m-latest-mag-feat-thumb">
                <?php if ($has_thumb) { ?>
                <img src="<?php echo $thumb['src'] ?>" alt="<?php echo htmlspecialchars($thumb['alt'] ?? '') ?>" loading="lazy">
                <?php } else { ?>
                <div class="m-latest-mag-feat-thumb-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>
                    </svg>
                </div>
                <?php } ?>

                <?php if ($feat['comment_cnt']) { ?>
                <span class="m-latest-mag-feat-cmt">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <?php echo $feat['wr_comment'] ?>
                </span>
                <?php } ?>
            </div>

            <?php if ($feat['is_notice']) { ?>
            <span class="m-latest-mag-feat-badge">공지</span>
            <?php } elseif (!empty($feat['icon_hot'])) { ?>
            <span class="m-latest-mag-feat-badge" style="background:#fef3c7;color:#d97706;">HOT</span>
            <?php } elseif (!empty($feat['icon_new'])) { ?>
            <span class="m-latest-mag-feat-badge" style="background:#dcfce7;color:#16a34a;">NEW</span>
            <?php } ?>

            <h3 class="m-latest-mag-feat-subject"><?php echo $feat['subject'] ?></h3>
            <div class="m-latest-mag-feat-meta">
                <span class="m-name"><?php echo $feat['name'] ?></span>
                <span class="m-date"><?php echo $feat['datetime2'] ?></span>
            </div>
        </a>

        <!-- Side list -->
        <?php if (!empty($sides)) { ?>
        <ul class="m-latest-mag-side">
            <?php foreach ($sides as $s) { ?>
            <li>
                <a href="<?php echo get_pretty_url($bo_table, $s['wr_id']); ?>" class="m-latest-mag-side-link">
                    <p class="m-latest-mag-side-subject<?php echo $s['is_notice'] ? ' is-notice' : '' ?>"><?php echo $s['subject'] ?></p>
                    <div class="m-latest-mag-side-meta">
                        <span class="m-name"><?php echo $s['name'] ?></span>
                        <?php if ($s['comment_cnt']) { ?>
                        <span class="m-cmt"><?php echo $s['wr_comment'] ?></span>
                        <?php } ?>
                        <span class="m-date"><?php echo $s['datetime2'] ?></span>
                    </div>
                </a>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>

    </div>
    <?php } ?>
</section>
