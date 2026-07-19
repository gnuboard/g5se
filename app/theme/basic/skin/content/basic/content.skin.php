<?php
if (!defined('_GNUBOARD_')) exit;

require_once(G5_THEME_PATH.'/modern/_head.inc.php');
?>

<!-- 내용 시작 { -->
<div class="m-shell">
    <?php require G5_THEME_PATH.'/modern/_nav.inc.php'; ?>

    <main class="m-container m-with-sidebar" style="padding: 32px 20px 64px;">
        <div class="m-main-col">
            <article class="m-card m-content">
                <header class="m-content-head">
                    <h1 class="m-content-title"><?php echo get_text($g5['title']) ?></h1>
                </header>
                <div class="m-content-body"><?php echo $str ?></div>
            </article>
        </div>

        <aside class="m-side-col">
            <?php require G5_THEME_PATH.'/modern/_outlogin.inc.php'; ?>
        </aside>
    </main>

    <?php require G5_THEME_PATH.'/modern/_tail.inc.php'; ?>
</div>

<style>
.m-content { padding: 36px 32px; }
.m-content-head { margin: 0 0 22px; padding-bottom: 14px; border-bottom: 1px solid var(--m-border); }
.m-content-title {
    margin: 0;
    font-size: var(--m-text-2xl); font-weight: 700;
    color: var(--m-text); letter-spacing: -0.01em;
}
.m-content-body {
    font-size: var(--m-text-md); line-height: var(--m-leading-relaxed);
    color: var(--m-text); word-break: break-word;
}
.m-content-body h1, .m-content-body h2, .m-content-body h3, .m-content-body h4 {
    margin: 1.5em 0 0.6em; color: var(--m-text); font-weight: 700;
}
.m-content-body h1 { font-size: var(--m-text-xl); }
.m-content-body h2 { font-size: var(--m-text-lg); }
.m-content-body h3, .m-content-body h4 { font-size: var(--m-text-md); }
.m-content-body p { margin: 0 0 1em; }
.m-content-body ul, .m-content-body ol { margin: 0 0 1em; padding-left: 1.5em; }
.m-content-body li { margin: 0.2em 0; }
.m-content-body a { color: var(--m-primary); }
.m-content-body a:hover { text-decoration: underline; }
.m-content-body img { max-width: 100%; height: auto; border-radius: var(--m-radius); }
.m-content-body table { border-collapse: collapse; margin: 1em 0; width: 100%; }
.m-content-body th, .m-content-body td { padding: 8px 12px; border: 1px solid var(--m-border); }
.m-content-body th { background: var(--m-surface-2); font-weight: 600; text-align: left; }
.m-content-body blockquote {
    margin: 1em 0; padding: 10px 16px;
    border-left: 3px solid var(--m-primary);
    background: var(--m-surface-2);
    color: var(--m-text-soft);
}
.m-content-body code {
    padding: 2px 6px; border-radius: var(--m-radius-sm);
    background: var(--m-surface-2);
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 0.9em;
}
.m-content-body pre {
    margin: 1em 0; padding: 14px; overflow-x: auto;
    background: var(--m-surface-2); border: 1px solid var(--m-border);
    border-radius: var(--m-radius);
}
.m-content-body pre code { padding: 0; background: transparent; }
</style>
<!-- } 내용 끝 -->
