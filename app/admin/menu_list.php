<?php
/*
 * /admin/menu_list — 사이트 메뉴 설정 (parent + sub 2단 메뉴).
 *
 * 추가는 popup 대신 in-page 모달로. 모달 내부에서
 * /admin/menu_form_search?type=group|board|content (JSON) 으로 검색해 1-click 추가.
 * 저장은 /admin/menu_list_update (gnuboard 의 update 로직 그대로 재사용).
 */
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();

require_once G5_ADMIN_PATH.'/admin.lib.php';

$sub_menu = '100290';
if ($is_admin !== 'super') {
    admin_layout_start('메뉴 설정', 'menus');
    echo '<main class="flex-1 p-6 max-w-3xl mx-auto"><div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-800 p-6 text-rose-800 dark:text-rose-200">최고관리자만 접근 가능합니다.</div></main>';
    admin_layout_end();
    exit;
}

if (!isset($g5['menu_table'])) {
    die('<meta charset="utf-8">/data/dbconfig.php 파일에 <strong>$g5[\'menu_table\'] = G5_TABLE_PREFIX.\'menu\';</strong> 를 추가해 주세요.');
}

// 메뉴 테이블 보장 — DDL placeholder 불가, sql_pdo_query 로 통일 (params 빈 배열)
sql_pdo_query(
    " CREATE TABLE IF NOT EXISTS `{$g5['menu_table']}` (
          `me_id` int(11) NOT NULL AUTO_INCREMENT,
          `me_code` varchar(255) NOT NULL DEFAULT '',
          `me_name` varchar(255) NOT NULL DEFAULT '',
          `me_link` varchar(255) NOT NULL DEFAULT '',
          `me_target` varchar(255) NOT NULL DEFAULT '0',
          `me_order` int(11) NOT NULL DEFAULT '0',
          `me_use` tinyint(4) NOT NULL DEFAULT '0',
          `me_mobile_use` tinyint(4) NOT NULL DEFAULT '0',
          PRIMARY KEY (`me_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", [], true);

$result = sql_pdo_query(" select * from {$g5['menu_table']} order by me_id ");

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

admin_layout_start('메뉴 설정', 'menus');
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">

    <header class="flex flex-wrap items-center gap-3 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight">메뉴 설정</h2>
            <p class="text-xs text-slate-500 mt-0.5">메인 메뉴 (대분류) + 서브 메뉴 2단 구조 — <strong class="text-amber-600 dark:text-amber-400">저장 누르기 전엔 변경이 반영되지 않습니다</strong></p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <button type="button" id="btn-add-parent" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                메뉴 추가
            </button>
        </div>
    </header>

    <form id="fmenulist" action="/admin/menu_list_update" method="post"
          class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
        <input type="hidden" name="token" value="<?php echo get_admin_token() ?>">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm" id="menu-table">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap min-w-[14rem]">메뉴</th>
                    <th class="px-3 py-2.5 text-left whitespace-nowrap min-w-[16rem]">링크</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap">새창</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap">순서</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap">PC</th>
                    <th class="px-3 py-2.5 text-center whitespace-nowrap">모바일</th>
                    <th class="px-3 py-2.5 text-right whitespace-nowrap">관리</th>
                </tr>
            </thead>
            <tbody id="menu-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            $i = 0;
            $rows_data = [];
            while ($row = sql_fetch_array($result)) {
                $is_sub = strlen($row['me_code']) === 4;
                $code2  = substr($row['me_code'], 0, 2);
                $rows_data[] = ['code'=>$code2, 'is_sub'=>$is_sub, 'name'=>$row['me_name'], 'link'=>$row['me_link'], 'target'=>$row['me_target'], 'order'=>(int)$row['me_order'], 'use'=>(int)$row['me_use'], 'mobile_use'=>(int)$row['me_mobile_use']];
            }

            $input_cls  = 'w-full h-9 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';
            $select_cls = 'h-9 pl-2.5 pr-7 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';
            $num_cls    = 'w-14 h-9 px-2 text-center rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';

            foreach ($rows_data as $r):
                ?>
                <tr class="menu-row <?php echo $r['is_sub']?'is-sub bg-slate-50/50 dark:bg-slate-800/20':'is-parent' ?>" data-code="<?php echo $h($r['code']) ?>">
                    <td class="px-3 py-2 align-middle">
                        <div class="flex items-center gap-2">
                            <?php if ($r['is_sub']): ?>
                                <svg class="shrink-0 w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                <span class="text-xs text-slate-400 font-mono"><?php echo $h($r['code']) ?>·sub</span>
                            <?php else: ?>
                                <svg class="shrink-0 w-4 h-4 text-admin-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                                <span class="text-xs font-mono text-admin-primary-700 dark:text-admin-primary-300"><?php echo $h($r['code']) ?></span>
                            <?php endif; ?>
                            <input type="hidden" name="code[]" value="<?php echo $h($r['code']) ?>">
                            <input type="text" name="me_name[]" value="<?php echo $h($r['name']) ?>" required class="<?php echo $input_cls ?>">
                        </div>
                    </td>
                    <td class="px-3 py-2"><input type="text" name="me_link[]" value="<?php echo $h($r['link']) ?>" required class="<?php echo $input_cls ?> font-mono text-xs"></td>
                    <td class="px-3 py-2 text-center">
                        <select name="me_target[]" class="<?php echo $select_cls ?>">
                            <option value="self"  <?php echo $r['target']==='self'?'selected':'' ?>>현재창</option>
                            <option value="blank" <?php echo $r['target']==='blank'?'selected':'' ?>>새창</option>
                        </select>
                    </td>
                    <td class="px-3 py-2 text-center"><input type="text" name="me_order[]" value="<?php echo (int)$r['order'] ?>" class="<?php echo $num_cls ?>"></td>
                    <td class="px-3 py-2 text-center">
                        <select name="me_use[]" class="<?php echo $select_cls ?>">
                            <option value="1" <?php echo $r['use']==1?'selected':'' ?>>사용</option>
                            <option value="0" <?php echo $r['use']==0?'selected':'' ?>>안함</option>
                        </select>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <select name="me_mobile_use[]" class="<?php echo $select_cls ?>">
                            <option value="1" <?php echo $r['mobile_use']==1?'selected':'' ?>>사용</option>
                            <option value="0" <?php echo $r['mobile_use']==0?'selected':'' ?>>안함</option>
                        </select>
                    </td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">
                        <?php if (!$r['is_sub']): ?>
                        <button type="button" class="btn-add-sub inline-flex items-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">+ 서브</button>
                        <?php endif; ?>
                        <button type="button" class="btn-del-row inline-flex items-center h-8 px-2.5 rounded-md border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/30">삭제</button>
                    </td>
                </tr>
                <?php
                $i++;
            endforeach;
            if ($i === 0): ?>
                <tr id="empty-row"><td colspan="7" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">메뉴가 없습니다 — 우측 상단 "메뉴 추가" 로 시작하세요</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>

        <div class="flex flex-wrap items-center gap-2 px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
            <span class="ml-auto text-xs text-slate-500">변경/삭제/추가 후 저장을 눌러야 반영됩니다 —</span>
            <button type="submit" class="h-9 px-5 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-semibold">저장</button>
        </div>
    </form>

</main>

<!-- 메뉴 추가 모달 -->
<div id="menu-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-start justify-center p-4 sm:p-10 overflow-y-auto">
    <div class="w-full max-w-3xl bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800">
        <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-semibold" id="modal-title">메뉴 추가</h3>
            <button type="button" id="btn-close-modal" class="ml-auto inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="닫기">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="px-5 pt-3 border-b border-slate-200 dark:border-slate-800">
            <nav class="flex gap-1 -mb-px" id="modal-tabs">
                <button type="button" data-tab="manual"  class="tab-btn px-3 py-2 text-sm border-b-2 border-admin-primary-600 text-admin-primary-700 dark:text-admin-primary-300 font-medium">직접입력</button>
                <button type="button" data-tab="group"   class="tab-btn px-3 py-2 text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">게시판 그룹</button>
                <button type="button" data-tab="board"   class="tab-btn px-3 py-2 text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">게시판</button>
                <button type="button" data-tab="content" class="tab-btn px-3 py-2 text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">내용관리</button>
            </nav>
        </div>

        <div class="p-5 max-h-[70vh] overflow-y-auto">
            <div id="tab-manual" class="tab-pane">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">메뉴명 <span class="text-admin-primary-600">*</span></label>
                        <input type="text" id="manual-name" class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">링크 <span class="text-admin-primary-600">*</span></label>
                        <input type="text" id="manual-link" placeholder="/path 또는 https://..." class="w-full h-10 px-3 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono text-sm">
                        <p class="mt-1 text-xs text-slate-500">절대 URL 은 https:// 포함</p>
                    </div>
                    <div class="text-right">
                        <button type="button" id="btn-add-manual" class="h-9 px-4 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-sm font-medium">추가</button>
                    </div>
                </div>
            </div>
            <div id="tab-results" class="tab-pane hidden">
                <div id="modal-loading" class="hidden text-center py-10 text-slate-400 text-sm">불러오는 중…</div>
                <div id="modal-results"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var tbody  = document.getElementById('menu-tbody');
    var modal  = document.getElementById('menu-modal');
    var tabs   = document.getElementById('modal-tabs');
    var paneM  = document.getElementById('tab-manual');
    var paneR  = document.getElementById('tab-results');
    var resBox = document.getElementById('modal-results');
    var loadEl = document.getElementById('modal-loading');
    var modalTitle = document.getElementById('modal-title');
    var pendingCode = null;            // null = parent 추가 (새 코드 발급), '01'/'02' = 해당 그룹의 sub 추가
    var pendingIsSub = false;

    function base36Inc(code) {
        var n = parseInt((code || '').substr(0,2), 36);
        if (isNaN(n)) n = 0;
        n += 1;
        return n.toString(36).padStart(2, '0');
    }
    function newParentCode() {
        var max = 0;
        tbody.querySelectorAll('tr.menu-row').forEach(function (tr) {
            var c = tr.dataset.code;
            var n = parseInt(c || '0', 36);
            if (!isNaN(n) && n > max) max = n;
        });
        return (max + 1).toString(36).padStart(2, '0');
    }

    function escAttr(s) {
        return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function appendRow(name, link, code, isSub) {
        var empty = document.getElementById('empty-row'); if (empty) empty.remove();
        var icon = isSub
            ? '<svg class="shrink-0 w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg><span class="text-xs text-slate-400 font-mono">'+escAttr(code)+'·sub</span>'
            : '<svg class="shrink-0 w-4 h-4 text-admin-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg><span class="text-xs font-mono text-admin-primary-700 dark:text-admin-primary-300">'+escAttr(code)+'</span>';
        var inputCls  = 'w-full h-9 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';
        var selectCls = 'h-9 pl-2.5 pr-7 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';
        var numCls    = 'w-14 h-9 px-2 text-center rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm';
        var html =
            '<td class="px-3 py-2 align-middle"><div class="flex items-center gap-2">'+icon+
              '<input type="hidden" name="code[]" value="'+escAttr(code)+'">'+
              '<input type="text" name="me_name[]" value="'+escAttr(name)+'" required class="'+inputCls+'"></div></td>'+
            '<td class="px-3 py-2"><input type="text" name="me_link[]" value="'+escAttr(link)+'" required class="'+inputCls+' font-mono text-xs"></td>'+
            '<td class="px-3 py-2 text-center"><select name="me_target[]" class="'+selectCls+'"><option value="self">현재창</option><option value="blank">새창</option></select></td>'+
            '<td class="px-3 py-2 text-center"><input type="text" name="me_order[]" value="0" class="'+numCls+'"></td>'+
            '<td class="px-3 py-2 text-center"><select name="me_use[]" class="'+selectCls+'"><option value="1">사용</option><option value="0">안함</option></select></td>'+
            '<td class="px-3 py-2 text-center"><select name="me_mobile_use[]" class="'+selectCls+'"><option value="1">사용</option><option value="0">안함</option></select></td>'+
            '<td class="px-3 py-2 text-right whitespace-nowrap">'+
              (isSub ? '' : '<button type="button" class="btn-add-sub inline-flex items-center h-8 px-2.5 rounded-md border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">+ 서브</button> ')+
              '<button type="button" class="btn-del-row inline-flex items-center h-8 px-2.5 rounded-md border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/30">삭제</button>'+
            '</td>';

        var tr = document.createElement('tr');
        tr.className = 'menu-row ' + (isSub ? 'is-sub bg-slate-50/50 dark:bg-slate-800/20' : 'is-parent');
        tr.dataset.code = code;
        tr.innerHTML = html;

        if (isSub) {
            // 같은 code 의 마지막 row 뒤에 삽입
            var siblings = tbody.querySelectorAll('tr.menu-row[data-code="'+code+'"]');
            var last = siblings.length ? siblings[siblings.length-1] : null;
            if (last && last.nextSibling) tbody.insertBefore(tr, last.nextSibling);
            else tbody.appendChild(tr);
        } else {
            tbody.appendChild(tr);
        }
    }

    function openModal(isSub, parentCode) {
        pendingIsSub = isSub;
        pendingCode  = isSub ? parentCode : null;
        modalTitle.textContent = isSub ? ('서브 메뉴 추가 (그룹 '+parentCode+')') : '메뉴 추가';
        // 직접입력 탭으로 리셋
        switchTab('manual');
        document.getElementById('manual-name').value = '';
        document.getElementById('manual-link').value = '';
        modal.classList.remove('hidden');
        document.documentElement.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.classList.add('hidden');
        document.documentElement.style.overflow = '';
    }

    function switchTab(name) {
        tabs.querySelectorAll('button.tab-btn').forEach(function (b) {
            var on = b.dataset.tab === name;
            b.classList.toggle('border-admin-primary-600', on);
            b.classList.toggle('text-admin-primary-700', on);
            b.classList.toggle('dark:text-admin-primary-300', on);
            b.classList.toggle('font-medium', on);
            b.classList.toggle('border-transparent', !on);
            b.classList.toggle('text-slate-500', !on);
        });
        if (name === 'manual') {
            paneM.classList.remove('hidden');
            paneR.classList.add('hidden');
        } else {
            paneM.classList.add('hidden');
            paneR.classList.remove('hidden');
            loadResults(name);
        }
    }

    function loadResults(type) {
        loadEl.classList.remove('hidden');
        resBox.innerHTML = '';
        fetch('/admin/menu_form_search?type=' + encodeURIComponent(type), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                loadEl.classList.add('hidden');
                if (!data || !data.items || data.items.length === 0) {
                    resBox.innerHTML = '<p class="text-center py-10 text-slate-400 text-sm">자료가 없습니다.</p>';
                    return;
                }
                var rows = data.items.map(function (it) {
                    var sub = it.subject || '';
                    var grp = it.group ? '<span class="text-xs text-slate-500 ml-2">'+escAttr(it.group)+'</span>' : '';
                    return '<tr class="border-b border-slate-100 dark:border-slate-800">'+
                        '<td class="px-3 py-2"><span class="font-medium">'+escAttr(sub)+'</span>'+grp+'</td>'+
                        '<td class="px-3 py-2 font-mono text-xs text-slate-500">'+escAttr(it.link)+'</td>'+
                        '<td class="px-3 py-2 text-right">'+
                            '<button type="button" class="js-pick h-8 px-3 rounded-md bg-admin-primary-600 hover:bg-admin-primary-700 text-white text-xs font-medium" data-name="'+escAttr(sub)+'" data-link="'+escAttr(it.link)+'">선택</button>'+
                        '</td></tr>';
                }).join('');
                resBox.innerHTML = '<table class="min-w-full text-sm"><thead class="text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-3 py-2 text-left">제목</th><th class="px-3 py-2 text-left">링크</th><th class="px-3 py-2"></th></tr></thead><tbody>'+rows+'</tbody></table>';
            })
            .catch(function () {
                loadEl.classList.add('hidden');
                resBox.innerHTML = '<p class="text-center py-10 text-rose-500 text-sm">검색 실패</p>';
            });
    }

    function pick(name, link) {
        var code = pendingIsSub ? pendingCode : newParentCode();
        appendRow(name, link, code, pendingIsSub);
        closeModal();
    }

    // 이벤트 위임
    document.getElementById('btn-add-parent').addEventListener('click', function () { openModal(false, null); });
    tbody.addEventListener('click', function (e) {
        var tr = e.target.closest('tr.menu-row');
        if (!tr) return;
        if (e.target.matches('.btn-add-sub')) {
            openModal(true, tr.dataset.code);
        } else if (e.target.matches('.btn-del-row')) {
            if (!confirm(tr.classList.contains('is-parent') ? '메인 메뉴를 삭제하면 하위 서브 메뉴도 함께 삭제됩니다. 계속할까요?' : '메뉴를 삭제하시겠습니까?')) return;
            if (tr.classList.contains('is-parent')) {
                tbody.querySelectorAll('tr.menu-row[data-code="'+tr.dataset.code+'"]').forEach(function (s) { s.remove(); });
            } else {
                tr.remove();
            }
        }
    });
    document.getElementById('btn-close-modal').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

    tabs.addEventListener('click', function (e) {
        var b = e.target.closest('button.tab-btn');
        if (b) switchTab(b.dataset.tab);
    });
    document.getElementById('btn-add-manual').addEventListener('click', function () {
        var name = (document.getElementById('manual-name').value || '').trim();
        var link = (document.getElementById('manual-link').value || '').trim();
        if (!name || !link) { alert('메뉴명과 링크를 모두 입력해 주세요.'); return; }
        if (/^javascript/i.test(link)) { alert('링크에 javascript: 는 사용할 수 없습니다.'); return; }
        pick(name, link);
    });
    resBox.addEventListener('click', function (e) {
        var b = e.target.closest('button.js-pick');
        if (b) pick(b.dataset.name, b.dataset.link);
    });

    // 폼 제출 검증
    document.getElementById('fmenulist').addEventListener('submit', function (e) {
        var bad = false;
        document.querySelectorAll('input[name="me_link[]"]').forEach(function (i) {
            if (/^javascript/i.test(i.value)) bad = true;
        });
        if (bad) { e.preventDefault(); alert('링크에 javascript: 는 사용할 수 없습니다.'); }
    });
})();
</script>

<?php
admin_layout_end();
