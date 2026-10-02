<!-- Google Sheets Integration & Auto-Sync Modal -->
<div id="google-sheet-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl animate-fade-in border border-emerald-100 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-[#0f9d58] text-white flex items-center justify-center shadow-xs">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-2h6v2zm4-4H6v-2h10v2zm0-4H6V7h10v2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Google Sheets Live Sync</h3>
                    <p class="text-xs text-gray-500">Auto-sync report data directly to your online spreadsheet</p>
                </div>
            </div>
            <button onclick="closeGoogleSheetModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto space-y-5 text-sm">
            <!-- Sync Status & Webhook Input -->
            <div class="space-y-3">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                    Google Apps Script Webhook URL
                </label>
                <div class="relative">
                    <input type="url" id="gs-webhook-url" 
                           placeholder="https://script.google.com/macros/s/.../exec"
                           class="w-full h-11 px-3.5 text-xs font-mono border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0f9d58] outline-none" />
                </div>
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="gs-auto-sync" class="w-4 h-4 rounded text-[#0f9d58] focus:ring-[#0f9d58] border-gray-300">
                        <span class="text-xs font-semibold text-gray-700">Automatically sync when device is online</span>
                    </label>
                    <span id="gs-online-indicator" class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Online
                    </span>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="saveGoogleSheetSettings()" id="btn-save-gs-settings"
                        class="flex-1 inline-flex items-center justify-center h-10 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow-xs transition active:scale-[0.98]">
                    Save Settings
                </button>
                <button type="button" onclick="triggerActiveReportSync()" id="btn-sync-now"
                        class="inline-flex items-center justify-center h-10 px-4 py-2 bg-[#0f9d58] hover:bg-[#0b8043] text-white font-bold text-xs rounded-xl shadow-xs transition active:scale-[0.98] gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync Now
                </button>
            </div>

            <!-- Feedback message -->
            <div id="gs-status-message" class="hidden p-3 rounded-xl text-xs font-semibold"></div>

            <!-- Setup Instructions & Script Code -->
            <div class="border-t border-gray-100 pt-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs text-gray-800 uppercase tracking-wider">Turnkey Google Apps Script Code</h4>
                    <button type="button" onclick="copyGoogleAppsScriptCode()" class="text-xs text-[#0f9d58] hover:text-[#0b8043] font-bold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span id="btn-copy-script-text">Copy Script Code</span>
                    </button>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-[11px] text-gray-600 space-y-1.5 font-medium leading-relaxed">
                    <p class="font-bold text-gray-800">Quick 30-Second Setup Guide:</p>
                    <ol class="list-decimal pl-4 space-y-1">
                        <li>Open your <strong>Google Sheet</strong>.</li>
                        <li>Click <strong>Extensions &rarr; Apps Script</strong>.</li>
                        <li>Paste the copied script code and click <strong>Deploy &rarr; New deployment</strong>.</li>
                        <li>Select type: <strong>Web app</strong> &bull; Execute as: <strong>Me</strong> &bull; Who has access: <strong>Anyone</strong>.</li>
                        <li>Copy the <strong>Web app URL</strong>, paste it into the box above, and click <strong>Save Settings</strong>.</li>
                    </ol>
                </div>
                <textarea id="gs-script-source" readonly class="w-full h-24 p-2.5 text-[10px] font-mono bg-gray-900 text-gray-200 rounded-xl outline-none select-all"></textarea>
            </div>
        </div>
    </div>
</div>

<script>
    // Ready-to-use turnkey Apps Script that handles all 4 Heim reports into dedicated formatted tabs
    const HEIM_GOOGLE_APPS_SCRIPT = `/**
 * HEIM COFFEE POS - Google Sheets Live Sync Script
 * Handles automated synchronization of Sales, Inventory, and Consumption
 */
function doPost(e) {
  var lock = LockService.getScriptLock();
  lock.tryLock(15000);
  try {
    var payload = JSON.parse(e.postData.contents);
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var action = payload.action;

    if (action === 'sync_sales') {
      var sheet = getOrCreate(ss, 'Sales_Analytics');
      sheet.clear();
      sheet.appendRow(['HEIM COFFEE - SALES & REVENUE REPORT']);
      sheet.appendRow(['Period:', payload.period || 'Today']);
      sheet.appendRow(['Last Synced:', new Date().toLocaleString()]);
      sheet.appendRow([]);
      sheet.appendRow(['--- EXECUTIVE KPI SUMMARY ---']);
      sheet.appendRow(['Metric', 'Value']);
      (payload.data.summary || []).forEach(function(r) { sheet.appendRow(r); });
      sheet.appendRow([]);
      sheet.appendRow(['--- PAYMENT METHODS ---']);
      sheet.appendRow(['Method', 'Transactions Count', 'Total (PHP)']);
      (payload.data.payments || []).forEach(function(r) { sheet.appendRow(r); });
      sheet.appendRow([]);
      sheet.appendRow(['--- CASHIER PERFORMANCE ---']);
      sheet.appendRow(['Cashier', 'Orders Count', 'Total (PHP)']);
      (payload.data.cashiers || []).forEach(function(r) { sheet.appendRow(r); });
      sheet.appendRow([]);
      sheet.appendRow(['--- TOP SELLING ITEMS ---']);
      sheet.appendRow(['Rank', 'Product Name', 'Size', 'Qty Sold', 'Revenue (PHP)']);
      (payload.data.products || []).forEach(function(r) { sheet.appendRow(r); });
      sheet.getRange(1, 1, 1, 5).setFontWeight('bold').setBackground('#155d49').setFontColor('#ffffff');
    } else if (action === 'sync_inventory') {
      var sheet = getOrCreate(ss, 'Inventory_Movement');
      sheet.clear();
      sheet.appendRow(['HEIM COFFEE - INVENTORY MOVEMENT REPORT']);
      sheet.appendRow(['Period:', payload.period]);
      sheet.appendRow(['Last Synced:', new Date().toLocaleString()]);
      sheet.appendRow([]);
      sheet.appendRow(['Ingredient Name', 'Unit', 'Stock In (+)', 'Sales Use (-)', 'Waste (-)', 'Adjustments', 'Current Stock', 'Status']);
      (payload.data.items || []).forEach(function(r) { sheet.appendRow(r); });
      sheet.getRange(1, 1, 1, 8).setFontWeight('bold').setBackground('#155d49').setFontColor('#ffffff');
    } else if (action === 'sync_consumption') {
      var sheet = getOrCreate(ss, 'Daily_Consumption');
      sheet.clear();
      sheet.appendRow(['HEIM COFFEE - DAILY INGREDIENT USAGE MATRIX']);
      sheet.appendRow(['Date:', payload.period]);
      sheet.appendRow(['Last Synced:', new Date().toLocaleString()]);
      sheet.appendRow([]);
      sheet.appendRow(['Ingredient Name', 'Unit', 'Sales Usage (-)', 'Waste (-)', 'Adjustments', 'Stock In (+)', 'Net Change', 'Current Stock']);
      (payload.data.items || []).forEach(function(r) { sheet.appendRow(r); });
      sheet.getRange(1, 1, 1, 8).setFontWeight('bold').setBackground('#155d49').setFontColor('#ffffff');
    } else if (action === 'sync_audit_logs') {
      var sheet = getOrCreate(ss, 'Audit_Logs');
      sheet.clear();
      sheet.appendRow(['HEIM COFFEE - AUDIT LOGS']);
      sheet.appendRow(['Last Synced:', new Date().toLocaleString()]);
      sheet.appendRow([]);
      sheet.appendRow(['Timestamp', 'Actor Name', 'Role', 'Module', 'Action', 'Description', 'IP Address']);
      (payload.data.items || []).forEach(function(r) { sheet.appendRow(r); });
      sheet.getRange(1, 1, 1, 7).setFontWeight('bold').setBackground('#155d49').setFontColor('#ffffff');
    }

    return ContentService.createTextOutput(JSON.stringify({ status: 'success', message: 'Synced successfully' }))
      .setMimeType(ContentService.MimeType.JSON);
  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({ status: 'error', message: err.toString() }))
      .setMimeType(ContentService.MimeType.JSON);
  } finally {
    lock.releaseLock();
  }
}

function getOrCreate(ss, name) {
  var sheet = ss.getSheetByName(name);
  if (!sheet) { sheet = ss.insertSheet(name); }
  return sheet;
}`;

    // Populate script code on load
    document.addEventListener('DOMContentLoaded', () => {
        const txtArea = document.getElementById('gs-script-source');
        if (txtArea) txtArea.value = HEIM_GOOGLE_APPS_SCRIPT;
        loadGoogleSheetSettings();
        updateOnlineStatusIndicator();
    });

    window.addEventListener('online', () => {
        updateOnlineStatusIndicator();
        checkAndTriggerAutoSync();
    });

    window.addEventListener('offline', () => {
        updateOnlineStatusIndicator();
    });

    function updateOnlineStatusIndicator() {
        const ind = document.getElementById('gs-online-indicator');
        if (!ind) return;
        if (navigator.onLine) {
            ind.className = 'inline-flex items-center gap-1.5 text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800';
            ind.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Online';
        } else {
            ind.className = 'inline-flex items-center gap-1.5 text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800';
            ind.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Offline';
        }
    }

    let currentReportPayloadGetter = null;

    window.registerCurrentReportSyncGetter = function(getterFn) {
        currentReportPayloadGetter = getterFn;
    };

    window.openGoogleSheetModal = function() {
        loadGoogleSheetSettings();
        document.getElementById('google-sheet-modal').classList.remove('hidden');
    };

    window.closeGoogleSheetModal = function() {
        document.getElementById('google-sheet-modal').classList.add('hidden');
    };

    function copyGoogleAppsScriptCode() {
        const code = HEIM_GOOGLE_APPS_SCRIPT;
        navigator.clipboard.writeText(code).then(() => {
            const btnText = document.getElementById('btn-copy-script-text');
            if (btnText) {
                btnText.textContent = '✓ Copied to Clipboard!';
                setTimeout(() => { btnText.textContent = 'Copy Script Code'; }, 2500);
            }
        });
    }

    function loadGoogleSheetSettings() {
        fetch('{{ route("reports.google-sheets.get-settings") }}')
            .then(res => res.json())
            .then(data => {
                const urlInput = document.getElementById('gs-webhook-url');
                const autoCheck = document.getElementById('gs-auto-sync');
                if (urlInput) urlInput.value = data.webhook_url || '';
                if (autoCheck) autoCheck.checked = Boolean(data.auto_sync);
            })
            .catch(() => {});
    }

    function saveGoogleSheetSettings() {
        const url = document.getElementById('gs-webhook-url').value.trim();
        const autoSync = document.getElementById('gs-auto-sync').checked;
        const msgEl = document.getElementById('gs-status-message');

        fetch('{{ route("reports.google-sheets.save-settings") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ webhook_url: url, auto_sync: autoSync })
        })
        .then(res => res.json())
        .then(data => {
            msgEl.className = 'p-3 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200';
            msgEl.textContent = data.message || 'Settings saved successfully!';
            msgEl.classList.remove('hidden');
            setTimeout(() => msgEl.classList.add('hidden'), 4000);
        })
        .catch(err => {
            msgEl.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200';
            msgEl.textContent = 'Error saving settings: ' + err.message;
            msgEl.classList.remove('hidden');
        });
    }

    window.triggerActiveReportSync = function() {
        if (!currentReportPayloadGetter) {
            alert('No active report available to sync.');
            return;
        }

        const payload = currentReportPayloadGetter();
        if (!payload) return;

        const btnSync = document.getElementById('btn-sync-now');
        const originalText = btnSync ? btnSync.innerHTML : '';
        if (btnSync) {
            btnSync.disabled = true;
            btnSync.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Syncing...';
        }

        const msgEl = document.getElementById('gs-status-message');

        fetch('{{ route("reports.google-sheets.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (msgEl) {
                    msgEl.className = 'p-3 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200';
                    msgEl.textContent = '✓ ' + data.message;
                    msgEl.classList.remove('hidden');
                }
                const syncBtnMain = document.getElementById('btn-report-sync-google');
                if (syncBtnMain) {
                    syncBtnMain.classList.add('bg-[#0b8043]');
                    syncBtnMain.title = 'Last synced: ' + new Date().toLocaleTimeString();
                }
            } else {
                if (msgEl) {
                    msgEl.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200';
                    msgEl.textContent = 'Sync Failed: ' + data.message;
                    msgEl.classList.remove('hidden');
                }
            }
        })
        .catch(err => {
            if (msgEl) {
                msgEl.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200';
                msgEl.textContent = 'Network error while syncing: ' + err.message;
                msgEl.classList.remove('hidden');
            }
        })
        .finally(() => {
            if (btnSync) {
                btnSync.disabled = false;
                btnSync.innerHTML = originalText;
            }
        });
    };

    function checkAndTriggerAutoSync() {
        const autoCheck = document.getElementById('gs-auto-sync');
        if (autoCheck && autoCheck.checked && navigator.onLine) {
            console.log('Auto-syncing active report to Google Sheets...');
            window.triggerActiveReportSync();
        }
    }
</script>
