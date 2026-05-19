<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function() {
    try {
        var theme = localStorage.getItem("m-theme");
        if (!theme) {
            theme = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.dataset.theme = theme;
        if (window.parent && window.parent !== window && window.parent.G5PopupLayer) {
            document.documentElement.className += (document.documentElement.className ? " " : "") + "is-layer-popup";
        }
    } catch (e) {}
})();
</script>
<style>
:root {
    --cp-bg: #f8fafc;
    --cp-surface: #ffffff;
    --cp-surface-2: #f1f5f9;
    --cp-border: #e2e8f0;
    --cp-border-hover: #cbd5e1;
    --cp-text: #0f172a;
    --cp-text-soft: #475569;
    --cp-text-muted: #64748b;
    --cp-primary: #2563eb;
    --cp-primary-soft: rgba(37, 99, 235, .12);
    --cp-danger: #ef4444;
    --cp-radius: 10px;
    --cp-shadow: 0 18px 45px rgba(15, 23, 42, .12);
    color-scheme: light;
}
[data-theme="dark"] {
    --cp-bg: #0a0e1a;
    --cp-surface: #131825;
    --cp-surface-2: #1c2230;
    --cp-border: #2a3344;
    --cp-border-hover: #3d4a5e;
    --cp-text: #f1f5f9;
    --cp-text-soft: #cbd5e1;
    --cp-text-muted: #94a3b8;
    --cp-primary: #3b82f6;
    --cp-primary-soft: rgba(59, 130, 246, .20);
    --cp-shadow: 0 18px 45px rgba(0, 0, 0, .35);
    color-scheme: dark;
}
html, body {
    margin: 0;
    min-height: 100%;
    background: var(--cp-bg);
    color: var(--cp-text);
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}
a { color: inherit; }
#hd_login_msg {
    display: none !important;
}
.scp_new_win {
    min-height: 100vh;
    padding: 18px;
    background: var(--cp-bg);
    box-sizing: border-box;
}
.scp_new_win > h1 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 14px;
    padding: 0 0 14px;
    border-bottom: 1px solid var(--cp-border);
    color: var(--cp-text);
    font-size: 18px;
    font-weight: 800;
    letter-spacing: 0;
}
.scp_new_win > h1:before {
    content: "";
    width: 4px;
    height: 24px;
    border-radius: 999px;
    background: var(--cp-primary);
    box-shadow: 0 0 0 3px var(--cp-primary-soft);
}
.is-layer-popup .scp_new_win {
    padding-top: 16px;
}
.is-layer-popup .scp_new_win > h1 {
    position: absolute;
    width: 1px;
    height: 1px;
    margin: -1px;
    padding: 0;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
.is-layer-popup .scp_new_win > h1:before {
    content: none;
}
.scp_new_win .local_desc {
    margin: 0 0 12px;
    padding: 12px 14px;
    border: 1px solid var(--cp-border);
    border-radius: var(--cp-radius);
    background: var(--cp-surface);
    color: var(--cp-text-muted);
    box-shadow: none;
}
.scp_new_win .local_desc p { margin: 0; line-height: 1.55; }
#scp_list_find {
    display: flex;
    align-items: end;
    gap: 8px;
    margin: 0 0 14px;
    padding: 12px;
    border: 1px solid var(--cp-border);
    border-radius: var(--cp-radius);
    background: var(--cp-surface);
}
#scp_list_find label {
    color: var(--cp-text-soft);
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}
.scp_new_win .frm_input {
    height: 38px;
    min-width: 0;
    padding: 0 12px;
    border: 1px solid var(--cp-border);
    border-radius: 8px;
    background: var(--cp-surface-2);
    color: var(--cp-text);
    outline: none;
    box-sizing: border-box;
}
.scp_new_win .frm_input:focus {
    border-color: var(--cp-primary);
    box-shadow: 0 0 0 3px var(--cp-primary-soft);
}
.scp_new_win .btn,
.scp_new_win .btn_frmline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 0 14px;
    border: 1px solid var(--cp-border);
    border-radius: 8px;
    background: var(--cp-surface-2);
    color: var(--cp-text-soft);
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}
.scp_new_win .win_btn .btn,
.scp_new_win .win_btn .btn_close {
    min-height: auto;
    padding: 8px 12px;
    border-color: transparent;
    background: transparent;
    color: var(--cp-text-soft);
    font-weight: 500;
}
.scp_new_win .win_btn .btn:hover,
.scp_new_win .win_btn .btn_close:hover {
    border-color: transparent;
    background: var(--cp-surface-2);
    color: var(--cp-text);
}
.scp_new_win .btn:hover,
.scp_new_win .btn_frmline:hover {
    border-color: var(--cp-border-hover);
    color: var(--cp-text);
}
.scp_new_win .btn_03 {
    border-color: var(--cp-primary);
    background: var(--cp-primary);
    color: #fff;
}
[data-theme="dark"] .scp_new_win .btn_03 { color: #0f172a; }
.scp_new_win .tbl_wrap {
    margin: 0;
    overflow: hidden;
    border: 1px solid var(--cp-border);
    border-radius: var(--cp-radius);
    background: var(--cp-surface);
    box-shadow: var(--cp-shadow);
}
.scp_new_win table {
    width: 100%;
    border-collapse: collapse;
    background: transparent;
}
.scp_new_win caption {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
}
.scp_new_win th,
.scp_new_win td {
    padding: 11px 12px;
    border: 0;
    border-bottom: 1px solid var(--cp-border);
    background: transparent;
    color: var(--cp-text-soft);
    font-size: 14px;
}
.scp_new_win thead th {
    background: var(--cp-surface-2);
    color: var(--cp-text);
    font-size: 12px;
    font-weight: 800;
    text-align: left;
}
.scp_new_win tbody tr:hover td {
    background: var(--cp-primary-soft);
}
.scp_new_win tbody tr:last-child td { border-bottom: 0; }
.scp_new_win .td_left,
.scp_new_win .td_mbname {
    color: var(--cp-text);
    font-weight: 650;
}
.scp_new_win .scp_target_code {
    color: var(--cp-text-muted);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 13px;
}
.scp_new_win .td_mng,
.scp_new_win .td_mng_s,
.scp_new_win .scp_find_select {
    text-align: right;
}
.scp_new_win .empty_table {
    padding: 42px 12px;
    color: var(--cp-text-muted);
    text-align: center;
}
.scp_new_win .win_btn {
    display: flex;
    justify-content: flex-end;
    margin-top: 14px;
    padding: 0;
}
.pg_wrap {
    display: flex;
    justify-content: center;
    margin: 14px 0 0;
}
.pg a,
.pg strong {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    margin: 0 2px;
    border: 1px solid var(--cp-border);
    border-radius: 8px;
    background: var(--cp-surface);
    color: var(--cp-text-soft);
    text-decoration: none;
}
.pg strong {
    border-color: var(--cp-primary);
    background: var(--cp-primary);
    color: #fff;
}
@media (max-width: 560px) {
    .scp_new_win { padding: 14px; }
    #scp_list_find { align-items: stretch; flex-direction: column; }
    .scp_new_win .frm_input { width: 100%; }
}
</style>
