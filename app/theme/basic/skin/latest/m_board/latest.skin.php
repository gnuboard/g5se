<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);

$list_count = (is_array($list) && $list) ? count($list) : 0;
?>

<section class="m-latest m-latest-board">
    <header class="m-latest-head">
        <h2 class="m-latest-title">
            <a href="<?php echo get_pretty_url($bo_table); ?>"><?php echo $bo_subject ?></a>
        </h2>
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="m-latest-more">
            더보기
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </header>

    <ul class="m-latest-list">
        <?php for ($i = 0; $i < $list_count; $i++) { ?>
        <li class="m-latest-item">
            <a href="<?php echo get_pretty_url($bo_table, $list[$i]['wr_id']); ?>" class="m-latest-link">
                <?php if ($list[$i]['icon_secret']) { ?>
                <svg class="m-latest-secret" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <?php } ?>
                <span class="m-latest-subject<?php echo $list[$i]['is_notice'] ? ' is-notice' : '' ?>"><?php echo $list[$i]['subject'] ?></span>
                <?php if ($list[$i]['comment_cnt']) { ?>
                <span class="m-latest-cmt"><?php echo $list[$i]['wr_comment'] ?></span>
                <?php } ?>
                <?php if ($list[$i]['icon_new']) { ?><span class="m-latest-tag m-latest-tag-new">N</span><?php } ?>
                <?php if ($list[$i]['icon_hot']) { ?><span class="m-latest-tag m-latest-tag-hot">HOT</span><?php } ?>
                <?php if (!empty($list[$i]['icon_file'])) { ?>
                <svg class="m-latest-mark" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                <?php } ?>
                <?php if (!empty($list[$i]['icon_link'])) { ?>
                <svg class="m-latest-mark" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                <?php } ?>
            </a>
            <div class="m-latest-meta">
                <span class="m-latest-name"><?php echo $list[$i]['name'] ?></span>
                <span class="m-latest-date"><?php echo $list[$i]['datetime2'] ?></span>
            </div>
        </li>
        <?php } ?>

        <?php if ($list_count == 0) { ?>
        <li class="m-latest-empty">아직 글이 없습니다.</li>
        <?php } ?>
    </ul>
</section>
