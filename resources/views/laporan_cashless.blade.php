<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Cashless</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg: #f4f6f8; --card: #ffffff; --shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
            --accent: #2563eb; --text: #0f172a; --muted: #64748b; --border: #e5e9ef;
            --head-bg: #fef9c3; --head-text: #854d0e;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; background: var(--bg); }
        .app { min-height: 100vh; color: var(--text); transition: margin-left .25s ease; }
        .drawer-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.5); opacity: 0; pointer-events: none; transition: opacity .25s; z-index: 50; }
        .drawer-backdrop.open { opacity: 1; pointer-events: auto; }
        .drawer { position: fixed; top: 0; left: 0; width: 280px; height: 100%; background: linear-gradient(180deg,#1e1b4b 0%,#0f0d1e 100%); color: #e2e8f0; transform: translateX(-100%); transition: transform .25s; padding: 24px 20px; display: flex; flex-direction: column; z-index: 51; }
        .drawer.open { transform: translateX(0); }
        .drawer-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 20px; }
        .drawer-header-main { display: flex; align-items: center; min-width: 0; flex: 1; }
        .drawer-logo { width: 48px; height: 48px; border-radius: 12px; overflow: hidden; margin-right: 12px; flex-shrink: 0; }
        .drawer-logo img { width: 100%; height: 100%; object-fit: contain; }
        .drawer-user-name { font-size: .95rem; font-weight: 600; color: #f8fafc; }
        .drawer-user-role { font-size: .78rem; color: #94a3b8; }
        .drawer-close { width: 34px; height: 34px; border: none; border-radius: 10px; flex-shrink: 0; background: rgba(148,163,184,.15); color: #e2e8f0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        .drawer-divider { height: 1px; background: rgba(255,255,255,.1); margin: 16px 0; }
        .drawer-menu-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .12em; color: #64748b; margin-bottom: 10px; padding-left: 14px; font-weight: 600; }
        .drawer-menu { list-style: none; padding: 0; margin: 0; flex: 1; }
        .drawer-item { margin-bottom: 4px; }
        .drawer-link { display: flex; align-items: center; padding: 12px 14px; border-radius: 12px; color: #cbd5e1; text-decoration: none; font-size: .9rem; font-weight: 500; border: none; background: transparent; width: 100%; cursor: pointer; font-family: inherit; }
        .drawer-link span.icon { width: 24px; display: inline-flex; justify-content: center; margin-right: 12px; color: #94a3b8; }
        .drawer-link:hover, .drawer-link.active { background: linear-gradient(135deg,rgba(37,99,235,.3) 0%,rgba(29,78,216,.2) 100%); color: #dbeafe; }
        .drawer-link.active span.icon { color: #93c5fd; }
        .drawer-footer { font-size: .75rem; color: #64748b; margin-top: auto; padding-top: 16px; }
        .main { padding: 20px; max-width: 1280px; margin: 0 auto; }
        .header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .burger { width: 36px; height: 36px; border: none; border-radius: 10px; background: #fff; padding: 0; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; box-shadow: var(--shadow); }
        .burger span { display: block; width: 18px; height: 2.5px; border-radius: 2px; background: var(--accent); }
        .title { font-size: 1.4rem; font-weight: 700; margin: 0; }
        .filter-card { background: var(--card); border-radius: 14px; padding: 18px 20px; margin-bottom: 16px; box-shadow: var(--shadow); border: 1px solid var(--border); }
        .filter-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .filter-top h2 { margin: 0; font-size: 1rem; font-weight: 700; }
        .filter-top p { margin: 3px 0 0; font-size: .8rem; color: var(--muted); }
        .filter-toggle { border: none; background: none; color: var(--muted); font-size: .82rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; font-family: inherit; }
        .filter-body { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px 16px; }
        .field { position: relative; }
        .field label { display: block; font-size: .72rem; font-weight: 700; color: var(--muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }
        .field select, .field input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; font-size: .88rem; font-family: inherit; background: #fff; color: var(--text); }
        .multi-btn { width: 100%; border: 1px solid var(--border); background: #fff; color: var(--text); border-radius: 10px; padding: 10px 12px; font-size: .88rem; font-family: inherit; display: flex; align-items: center; justify-content: space-between; gap: 8px; cursor: pointer; text-align: left; }
        .multi-btn:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .multi-dropdown { display: none; position: absolute; z-index: 20; top: calc(100% + 6px); left: 0; right: 0; background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 12px 28px rgba(15,23,42,.12); }
        .multi-dropdown.open { display: block; }
        .multi-dropdown-inner { max-height: 220px; overflow: auto; padding: 8px; }
        .multi-option { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 8px; cursor: pointer; font-size: .86rem; }
        .multi-option:hover { background: #f1f5f9; }
        .multi-dropdown-actions { display: flex; justify-content: flex-end; gap: 8px; padding: 8px 10px; border-top: 1px solid var(--border); }
        .multi-clear-btn, .multi-apply-btn { border: none; border-radius: 8px; padding: 7px 12px; font-size: .78rem; font-weight: 700; cursor: pointer; font-family: inherit; }
        .multi-clear-btn { background: #fff; color: var(--muted); border: 1px solid var(--border); }
        .multi-apply-btn { background: var(--accent); color: #fff; }
        .multi-empty { color: var(--muted); font-size: .82rem; padding: 12px; }
        .search-group { display: flex; gap: 8px; }
        .search-group input { flex: 1; }
        .search-group button { padding: 10px 16px; border: none; border-radius: 10px; background: var(--accent); color: #fff; font-size: .86rem; font-weight: 600; cursor: pointer; font-family: inherit; }
        .filter-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
        .btn { border: none; border-radius: 10px; padding: 10px 16px; font-size: .86rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; text-decoration: none; color: #fff; }
        .btn-primary { background: var(--accent); }
        .btn-excel { background: #0f766e; }
        .btn-pdf { background: #b91c1c; }
        .btn-reset { background: #fff; color: var(--text); border: 1px solid var(--border); }
        .summary-bar { background: var(--card); border-radius: 14px; padding: 18px 20px; margin-bottom: 16px; box-shadow: var(--shadow); border: 1px solid var(--border); }
        .summary-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .summary-header h3 { margin: 0; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .summary-period { font-size: .82rem; color: var(--muted); font-weight: 600; }
        .summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .summary-item { background: #f8fafc; border: 1px solid var(--border); border-radius: 12px; padding: 14px 16px; }
        .summary-item .label { font-size: .72rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
        .summary-item .value { font-size: 1.25rem; font-weight: 800; color: var(--text); }
        .summary-item.total .value { color: var(--accent); }
        .spinner-sm { display: inline-block; width: 14px; height: 14px; border: 2px solid #cbd5e1; border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; vertical-align: -2px; margin-right: 6px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .table-card { background: var(--card); border-radius: 14px; box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; }
        .table-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 16px 18px; border-bottom: 1px solid var(--border); }
        .table-toolbar h3 { margin: 0; font-size: .96rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .pagination-size { display: flex; align-items: center; gap: 8px; font-size: .82rem; color: var(--muted); }
        .pagination-size select { padding: 6px 8px; border-radius: 8px; border: 1px solid var(--border); font-family: inherit; }
        .btn-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        #cashlessTable { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1200px; }
        thead.main-thead { background: var(--head-bg); }
        #cashlessTable th { padding: 10px 8px; text-align: center; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; border-bottom: 1px solid var(--border); white-space: nowrap; color: var(--head-text); }
        #cashlessTable td { padding: 10px 8px; font-size: .84rem; border-bottom: 1px solid var(--border); vertical-align: middle; background: #fff; }
        #cashlessTable tbody tr.data-row:hover td { background: #f8fafc; }
        #cashlessTable tbody tr.data-row.expanded td { background: #eff6ff; }
        .btn-expand { width: 30px; height: 30px; border: 1px solid var(--border); border-radius: 8px; background: #fff; color: var(--accent); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        .btn-expand.open { background: var(--accent); color: #fff; border-color: var(--accent); }
        tr.detail-row > td { padding: 14px 16px; background: #f8fafc; border-bottom: 2px solid var(--border); }
        .detail-box { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
        .detail-box table { width: 100%; border-collapse: collapse; min-width: 700px; }
        .detail-box th, .detail-box td { padding: 8px 10px; border-bottom: 1px solid #eef2f7; font-size: .78rem; text-align: left; }
        .detail-box th { background: #f1f5f9; font-weight: 700; color: var(--muted); text-transform: uppercase; font-size: .68rem; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .money { font-weight: 700; white-space: nowrap; }
        .empty-state { text-align: center; padding: 40px 20px; color: var(--muted); }
        .pagination-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 14px 18px; border-top: 1px solid var(--border); }
        .page-info { font-size: .82rem; color: var(--muted); font-weight: 600; }
        .page-btns { display: flex; gap: 8px; }
        .page-btns button { border: 1px solid var(--border); background: #fff; border-radius: 8px; padding: 8px 12px; font-size: .82rem; font-weight: 600; cursor: pointer; font-family: inherit; }
        .page-btns button:disabled { opacity: .45; cursor: not-allowed; }
        @media (max-width: 900px) { .filter-body { grid-template-columns: 1fr 1fr; } .summary-grid { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .filter-body { grid-template-columns: 1fr; } .main { padding: 14px; } }
        @media (min-width: 1024px) {
            .app { margin-left: 280px; }
            .drawer { transform: translateX(0); }
            .drawer-backdrop { display: none; }
            .burger { display: none; }
        }
    </style>
</head>
<body>
<div class="app" id="app">
    <div class="main">
        <header class="header">
            <button class="burger" id="drawerToggle" type="button" aria-label="Buka/tutup menu" title="Menu"><span></span><span></span><span></span></button>
            <h1 class="title">Laporan Cashless</h1>
        </header>

        <div class="filter-card">
            <div class="filter-top">
                <div>
                    <h2>Filter</h2>
                    <p>Pilih sekolah dulu untuk memuat kelas. Data dikunci ke akun login Anda.</p>
                </div>
                <button type="button" class="filter-toggle" id="filterToggle">
                    <span id="filterToggleText">Sembunyikan</span>
                    <i class="fas fa-chevron-up" id="filterToggleIcon"></i>
                </button>
            </div>

            <div id="filterBody">
                <div class="filter-body">
                    <div class="field">
                        <label for="filterTglDari">Tanggal Dari</label>
                        <input type="date" id="filterTglDari">
                    </div>
                    <div class="field">
                        <label for="filterTglSampai">Tanggal Sampai</label>
                        <input type="date" id="filterTglSampai">
                    </div>
                    <div class="field">
                        <label for="lockedTeller">Teller (akun Anda)</label>
                        <input type="text" id="lockedTeller" value="{{ session('user.username', '') }}" readonly style="background:#f1f5f9;color:#64748b;">
                    </div>
                    <div class="field">
                        <label>Sekolah</label>
                        <button type="button" id="filterSekolahBtn" class="multi-btn">
                            <span id="filterSekolahLabel">Semua sekolah</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="multi-dropdown" id="filterSekolahDropdown">
                            <div class="multi-dropdown-inner" id="filterSekolahList"><div class="multi-empty">Memuat...</div></div>
                            <div class="multi-dropdown-actions">
                                <button type="button" class="multi-clear-btn" id="btnSekolahClear">Bersihkan</button>
                                <button type="button" class="multi-apply-btn" id="btnSekolahApply">Terapkan</button>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label>Kelas</label>
                        <button type="button" id="filterKelasBtn" class="multi-btn" disabled>
                            <span id="filterKelasLabel">Pilih sekolah dulu</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="multi-dropdown" id="filterKelasDropdown">
                            <div class="multi-dropdown-inner" id="filterKelasList"><div class="multi-empty">Pilih sekolah dulu</div></div>
                            <div class="multi-dropdown-actions">
                                <button type="button" class="multi-clear-btn" id="btnKelasClear">Bersihkan</button>
                                <button type="button" class="multi-apply-btn" id="btnKelasApply">Terapkan</button>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label for="filterKeterangan">Keterangan</label>
                        <select id="filterKeterangan"><option value="">Semua keterangan</option></select>
                    </div>
                    <div class="field">
                        <label for="filterSearch">Cari nama / NIS / TRANSNO</label>
                        <div class="search-group">
                            <input type="text" id="filterSearch" placeholder="Nama, NIS, atau nomor transaksi..." onkeydown="if(event.key==='Enter') loadData(true)">
                            <button type="button" id="btnSearch"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn btn-reset" id="btnReset">Reset</button>
                    <button type="button" class="btn btn-primary" id="btnApply"><i class="fas fa-filter"></i> Terapkan</button>
                </div>
            </div>
        </div>

        <div class="summary-bar" id="summaryBar">
            <div class="summary-header">
                <h3><i class="fas fa-chart-pie"></i> Ringkasan Periode</h3>
                <span class="summary-period" id="summaryPeriod"></span>
            </div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="label">Jumlah Transaksi</div>
                    <div class="value" id="sumTransaksi"><span class="spinner-sm"></span> Menghitung...</div>
                </div>
                <div class="summary-item total">
                    <div class="label">Total Cashless</div>
                    <div class="value" id="sumTotal"><span class="spinner-sm"></span> Menghitung...</div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3><i class="fas fa-wallet"></i> Daftar Transaksi Cashless</h3>
                <div class="pagination-size">
                    <label for="pageSizeSelect">Tampilkan</label>
                    <select id="pageSizeSelect">
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                    </select>
                    <span>per halaman</span>
                </div>
                <div class="btn-row">
                    <a href="#" class="btn btn-excel" id="btnExportExcel"><i class="fas fa-file-excel"></i> Export Excel</a>
                    <a href="#" class="btn btn-pdf" id="btnExportPdf"><i class="fas fa-file-pdf"></i> Export PDF</a>
                </div>
            </div>

            <div class="table-scroll">
                <table id="cashlessTable">
                    <thead class="main-thead">
                        <tr>
                            <th style="width:44px;"></th>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Sekolah</th>
                            <th>Teller</th>
                            <th>Keterangan</th>
                            <th>Jumlah</th>
                            <th>TRANSNO</th>
                            <th>FIDBANK</th>
                        </tr>
                    </thead>
                    <tbody id="cashlessBody">
                        <tr><td colspan="12" class="empty-state">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination-bar">
                <div class="page-info" id="pageInfo">-</div>
                <div class="page-btns">
                    <button type="button" id="btnPrev" disabled>Sebelumnya</button>
                    <button type="button" id="btnNext" disabled>Berikutnya</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="drawer-backdrop" id="drawerBackdrop"></div>
<aside class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-header">
        <div class="drawer-header-main">
            <div class="drawer-logo"><img src="{{ asset('logo.png') }}" alt="Logo"></div>
            <div>
                <div class="drawer-user-name">{{ session('user.nama', session('user.username')) }}</div>
                <div class="drawer-user-role">{{ session('user.kel') ? ucfirst(session('user.kel')) : 'Cashless' }}</div>
            </div>
        </div>
        <button type="button" class="drawer-close" id="drawerClose" aria-label="Tutup menu" title="Tutup">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
    <div class="drawer-divider"></div>
    <div class="drawer-menu-label">Menu</div>
    <ul class="drawer-menu">
        <li class="drawer-item"><a href="{{ route('laporan-cashless') }}" class="drawer-link active"><span class="icon"><i class="fas fa-wallet"></i></span><span>Laporan Cashless</span></a></li>
        <li class="drawer-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="drawer-link"><span class="icon"><i class="fas fa-right-from-bracket"></i></span><span>Log Out</span></button>
            </form>
        </li>
    </ul>
    <div class="drawer-footer">App Ver : 1.0.0</div>
</aside>

<script>
const routes = {
    filters: @json(route('laporan-cashless.filters')),
    kelas: @json(route('laporan-cashless.kelas')),
    detail: @json(route('laporan-cashless.detail')),
    data: @json(route('laporan-cashless.data')),
    summary: @json(route('laporan-cashless.summary')),
    exportExcel: @json(route('laporan-cashless.export-excel')),
    exportPdf: @json(route('laporan-cashless.export-pdf')),
};

let currentPage = 1;
let pageSize = 50;
let hasMore = false;
let loading = false;
let selectedSekolah = [];
let selectedKelas = [];
let sekolahOptions = [];
let kelasOptions = [];

function todayStr() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}
function formatRupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
function formatTanggal(v) {
    if (!v) return '-';
    const s = String(v).replace('T', ' ');
    return s.length > 19 ? s.slice(0, 19) : s;
}
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
async function parseJsonResponse(res) {
    const text = await res.text();
    let json = null;
    try { json = text ? JSON.parse(text) : {}; }
    catch (e) {
        if (res.status === 401 || res.status === 419) throw new Error('Sesi berakhir. Silakan login ulang.');
        if (res.status >= 500) throw new Error('Server error. Coba refresh atau login ulang.');
        throw new Error('Respons bukan JSON (HTTP ' + res.status + ').');
    }
    if (!res.ok && json && json.success === false) throw new Error(json.message || ('Gagal (HTTP ' + res.status + ')'));
    return json || {};
}
function apiHeaders(extra = {}) {
    return { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...extra };
}

function getFilters() {
    return {
        tgl_dari: document.getElementById('filterTglDari').value || todayStr(),
        tgl_sampai: document.getElementById('filterTglSampai').value || todayStr(),
        sekolah: selectedSekolah.slice(),
        kelas: selectedSekolah.length ? selectedKelas.slice() : [],
        keterangan: document.getElementById('filterKeterangan').value || '',
        search: document.getElementById('filterSearch').value.trim() || '',
    };
}
function buildQuery(extra = {}) {
    const f = { ...getFilters(), ...extra };
    const params = new URLSearchParams();
    Object.entries(f).forEach(([k, v]) => {
        if (Array.isArray(v)) v.forEach(item => { if (item !== '' && item != null) params.append(k + '[]', item); });
        else if (v !== '' && v != null && v !== undefined) params.set(k, v);
    });
    return params.toString();
}

function closeAllDropdowns() {
    document.querySelectorAll('.multi-dropdown.open').forEach(el => el.classList.remove('open'));
}
function renderMultiOptions(listEl, options, selected, valueKey = 'value', labelKey = 'label') {
    if (!options.length) {
        listEl.innerHTML = '<div class="multi-empty">Tidak ada pilihan</div>';
        return;
    }
    listEl.innerHTML = options.map(item => {
        const value = typeof item === 'string' ? item : String(item[valueKey] ?? item.value ?? '');
        const label = typeof item === 'string' ? item : String(item[labelKey] ?? item.label ?? value);
        if (!value) return '';
        const checked = selected.includes(value) ? 'checked' : '';
        return `<label class="multi-option"><input type="checkbox" value="${escapeHtml(value)}" ${checked}><span>${escapeHtml(label)}</span></label>`;
    }).join('');
}
function readChecked(listEl) {
    return [...listEl.querySelectorAll('input[type="checkbox"]:checked')].map(el => el.value);
}
function updateSekolahLabel() {
    const el = document.getElementById('filterSekolahLabel');
    if (!selectedSekolah.length) el.textContent = 'Semua sekolah';
    else if (selectedSekolah.length === 1) {
        const opt = sekolahOptions.find(o => String(o.value) === selectedSekolah[0]);
        el.textContent = opt ? (opt.label || opt.value) : selectedSekolah[0];
    } else el.textContent = selectedSekolah.length + ' sekolah dipilih';
}
function updateKelasLabel() {
    const btn = document.getElementById('filterKelasBtn');
    const el = document.getElementById('filterKelasLabel');
    if (!selectedSekolah.length) {
        btn.disabled = true;
        el.textContent = 'Pilih sekolah dulu';
        return;
    }
    btn.disabled = false;
    if (!selectedKelas.length) el.textContent = 'Semua kelas';
    else if (selectedKelas.length === 1) el.textContent = selectedKelas[0];
    else el.textContent = selectedKelas.length + ' kelas dipilih';
}

async function loadKelasOptions() {
    const listEl = document.getElementById('filterKelasList');
    if (!selectedSekolah.length) {
        kelasOptions = [];
        selectedKelas = [];
        listEl.innerHTML = '<div class="multi-empty">Pilih sekolah dulu</div>';
        updateKelasLabel();
        return;
    }
    listEl.innerHTML = '<div class="multi-empty">Memuat kelas...</div>';
    try {
        const qs = new URLSearchParams();
        selectedSekolah.forEach(v => qs.append('sekolah[]', v));
        const res = await fetch(routes.kelas + '?' + qs.toString(), { headers: apiHeaders() });
        const json = await parseJsonResponse(res);
        kelasOptions = (json.data || []).map(v => typeof v === 'string' ? v : String(v.value ?? v));
        selectedKelas = selectedKelas.filter(v => kelasOptions.includes(v));
        renderMultiOptions(listEl, kelasOptions, selectedKelas);
        updateKelasLabel();
    } catch (e) {
        listEl.innerHTML = `<div class="multi-empty">${escapeHtml(e.message || 'Gagal memuat kelas')}</div>`;
    }
}

async function loadFilters() {
    try {
        const res = await fetch(routes.filters, { headers: apiHeaders(), cache: 'no-cache' });
        const json = await parseJsonResponse(res);
        if (!json.success) throw new Error(json.message || 'Gagal memuat filter');
        sekolahOptions = json.sekolah || [];
        renderMultiOptions(document.getElementById('filterSekolahList'), sekolahOptions, selectedSekolah);
        updateSekolahLabel();
        updateKelasLabel();
        const ket = document.getElementById('filterKeterangan');
        const current = ket.value;
        ket.innerHTML = '<option value="">Semua keterangan</option>';
        (json.keterangan || []).forEach(v => {
            const opt = document.createElement('option');
            opt.value = v; opt.textContent = v; ket.appendChild(opt);
        });
        if ([...ket.options].some(o => o.value === current)) ket.value = current;
        if (json.locked_teller) document.getElementById('lockedTeller').value = json.locked_teller;
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Filter', text: e.message || 'Gagal memuat filter' });
    }
}

async function loadSummary() {
    const sumTransaksi = document.getElementById('sumTransaksi');
    const sumTotal = document.getElementById('sumTotal');
    const summaryPeriod = document.getElementById('summaryPeriod');
    sumTransaksi.innerHTML = '<span class="spinner-sm"></span> Menghitung...';
    sumTotal.innerHTML = '<span class="spinner-sm"></span> Menghitung...';
    const f = getFilters();
    summaryPeriod.textContent = `${f.tgl_dari} s/d ${f.tgl_sampai}`;
    try {
        const res = await fetch(routes.summary + '?' + buildQuery(), { headers: apiHeaders() });
        const json = await parseJsonResponse(res);
        if (!json.success) throw new Error(json.message || 'Gagal ringkasan');
        const d = json.data || {};
        sumTransaksi.textContent = Number(d.total_transaksi || 0).toLocaleString('id-ID');
        sumTotal.textContent = formatRupiah(d.total_jumlah || 0);
        if (d.tgl_dari && d.tgl_sampai) summaryPeriod.textContent = `${d.tgl_dari} s/d ${d.tgl_sampai}`;
    } catch (e) {
        sumTransaksi.textContent = '-';
        sumTotal.textContent = '-';
    }
}

async function toggleDetail(btn, row, item) {
    const next = row.nextElementSibling;
    if (next && next.classList.contains('detail-row')) {
        next.remove();
        row.classList.remove('expanded');
        btn.classList.remove('open');
        btn.innerHTML = '<i class="fas fa-plus"></i>';
        return;
    }
    document.querySelectorAll('tr.detail-row').forEach(r => r.remove());
    document.querySelectorAll('tr.data-row.expanded').forEach(r => r.classList.remove('expanded'));
    document.querySelectorAll('.btn-expand.open').forEach(b => {
        b.classList.remove('open');
        b.innerHTML = '<i class="fas fa-plus"></i>';
    });

    row.classList.add('expanded');
    btn.classList.add('open');
    btn.innerHTML = '<i class="fas fa-minus"></i>';

    const detailTr = document.createElement('tr');
    detailTr.className = 'detail-row';
    detailTr.innerHTML = `<td colspan="12"><div class="detail-box"><div style="padding:14px;color:#64748b;"><span class="spinner-sm"></span> Memuat detail sccttran...</div></div></td>`;
    row.after(detailTr);

    try {
        const qs = new URLSearchParams({ transno: item.transno || '', custid: item.custid || 0 });
        const res = await fetch(routes.detail + '?' + qs.toString(), { headers: apiHeaders() });
        const json = await parseJsonResponse(res);
        if (!json.success) throw new Error(json.message || 'Gagal detail');
        const rows = json.data || [];
        if (!rows.length) {
            detailTr.innerHTML = `<td colspan="12"><div class="detail-box"><div style="padding:14px;color:#64748b;">Tidak ada detail transaksi di sccttran untuk TRANSNO ini.</div></div></td>`;
            return;
        }
        detailTr.innerHTML = `<td colspan="12"><div class="detail-box"><table>
            <thead><tr>
                <th>Tanggal</th><th>Metode</th><th>Noreff</th><th>Debet</th><th>Kredit</th><th>Channel</th><th>Reff Bank</th><th>Keterangan</th>
            </tr></thead>
            <tbody>${rows.map(r => `<tr>
                <td>${escapeHtml(formatTanggal(r.tanggal))}</td>
                <td>${escapeHtml(r.metode || '-')}</td>
                <td>${escapeHtml(r.noreff || '-')}</td>
                <td class="text-right">${formatRupiah(r.debet)}</td>
                <td class="text-right">${formatRupiah(r.kredit)}</td>
                <td>${escapeHtml(r.kdchannel || '-')}</td>
                <td>${escapeHtml(r.reffbank || '-')}</td>
                <td>${escapeHtml(r.keterangan || '-')}</td>
            </tr>`).join('')}</tbody>
        </table></div></td>`;
    } catch (e) {
        detailTr.innerHTML = `<td colspan="12"><div class="detail-box"><div style="padding:14px;color:#991b1b;">${escapeHtml(e.message || 'Gagal memuat detail')}</div></div></td>`;
    }
}

async function loadData(resetPage = false) {
    if (loading) return;
    if (resetPage) currentPage = 1;
    loading = true;
    const body = document.getElementById('cashlessBody');
    body.innerHTML = `<tr><td colspan="12" class="empty-state"><span class="spinner-sm"></span> Memuat data...</td></tr>`;
    document.getElementById('btnPrev').disabled = true;
    document.getElementById('btnNext').disabled = true;

    try {
        const res = await fetch(routes.data + '?' + buildQuery({ page: currentPage, limit: pageSize }), { headers: apiHeaders() });
        const json = await parseJsonResponse(res);
        if (!json.success) throw new Error(json.message || 'Gagal memuat data');
        const rows = json.data || [];
        const pag = json.pagination || {};
        hasMore = !!pag.has_more;

        if (!rows.length) {
            body.innerHTML = `<tr><td colspan="12" class="empty-state">Tidak ada transaksi untuk filter ini</td></tr>`;
            document.getElementById('pageInfo').textContent = '0 data';
        } else {
            const startNo = pag.from || ((currentPage - 1) * pageSize + 1);
            body.innerHTML = rows.map((r, i) => `
                <tr class="data-row" data-idx="${i}">
                    <td class="text-center">
                        <button type="button" class="btn-expand" data-transno="${escapeHtml(r.transno || '')}" data-custid="${escapeHtml(r.custid || 0)}" title="Detail transaksi">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                    <td class="text-center">${startNo + i}</td>
                    <td class="text-center">${formatTanggal(r.tanggal)}</td>
                    <td class="text-center">${escapeHtml(r.nis || '-')}</td>
                    <td>${escapeHtml((r.nama || '-').toUpperCase())}</td>
                    <td class="text-center">${escapeHtml(r.kelas || '-')}</td>
                    <td class="text-center">${escapeHtml(r.sekolah || r.unit || '-')}</td>
                    <td class="text-center">${escapeHtml(r.teller || '-')}</td>
                    <td>${escapeHtml(r.keterangan || '-')}</td>
                    <td class="text-right money">${formatRupiah(r.jumlah)}</td>
                    <td class="text-center">${escapeHtml(r.transno || '-')}</td>
                    <td class="text-center">${escapeHtml(r.fidbank || '-')}</td>
                </tr>
            `).join('');

            body.querySelectorAll('.btn-expand').forEach((btn, i) => {
                btn.addEventListener('click', () => toggleDetail(btn, btn.closest('tr'), rows[i]));
            });
            document.getElementById('pageInfo').textContent = `Menampilkan ${pag.from || 0}–${pag.to || 0}`;
        }
        document.getElementById('btnPrev').disabled = currentPage <= 1;
        document.getElementById('btnNext').disabled = !hasMore;
    } catch (e) {
        body.innerHTML = `<tr><td colspan="12" class="empty-state">${escapeHtml(e.message || 'Gagal memuat data')}</td></tr>`;
        document.getElementById('pageInfo').textContent = '-';
        Swal.fire({ icon: 'error', title: 'Data', text: e.message || 'Gagal memuat data' });
    } finally {
        loading = false;
    }
}

function refreshAll(resetPage = true) {
    loadSummary();
    loadData(resetPage);
}

document.addEventListener('DOMContentLoaded', () => {
    const today = todayStr();
    document.getElementById('filterTglDari').value = today;
    document.getElementById('filterTglSampai').value = today;

    const drawer = document.getElementById('drawer');
    const backdrop = document.getElementById('drawerBackdrop');
    document.getElementById('drawerToggle').addEventListener('click', () => { drawer.classList.add('open'); backdrop.classList.add('open'); });
    document.getElementById('drawerClose').addEventListener('click', () => { drawer.classList.remove('open'); backdrop.classList.remove('open'); });
    backdrop.addEventListener('click', () => { drawer.classList.remove('open'); backdrop.classList.remove('open'); });

    const filterBody = document.getElementById('filterBody');
    document.getElementById('filterToggle').addEventListener('click', () => {
        const hidden = filterBody.style.display === 'none';
        filterBody.style.display = hidden ? '' : 'none';
        document.getElementById('filterToggleText').textContent = hidden ? 'Sembunyikan' : 'Tampilkan';
        document.getElementById('filterToggleIcon').className = hidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
    });

    document.getElementById('filterSekolahBtn').addEventListener('click', (e) => {
        e.stopPropagation();
        const dd = document.getElementById('filterSekolahDropdown');
        const open = dd.classList.contains('open');
        closeAllDropdowns();
        if (!open) {
            renderMultiOptions(document.getElementById('filterSekolahList'), sekolahOptions, selectedSekolah);
            dd.classList.add('open');
        }
    });
    document.getElementById('btnSekolahClear').addEventListener('click', () => {
        selectedSekolah = [];
        selectedKelas = [];
        renderMultiOptions(document.getElementById('filterSekolahList'), sekolahOptions, selectedSekolah);
        updateSekolahLabel();
        loadKelasOptions();
    });
    document.getElementById('btnSekolahApply').addEventListener('click', async () => {
        selectedSekolah = readChecked(document.getElementById('filterSekolahList'));
        selectedKelas = [];
        updateSekolahLabel();
        document.getElementById('filterSekolahDropdown').classList.remove('open');
        await loadKelasOptions();
        refreshAll(true);
    });

    document.getElementById('filterKelasBtn').addEventListener('click', (e) => {
        if (!selectedSekolah.length) return;
        e.stopPropagation();
        const dd = document.getElementById('filterKelasDropdown');
        const open = dd.classList.contains('open');
        closeAllDropdowns();
        if (!open) {
            renderMultiOptions(document.getElementById('filterKelasList'), kelasOptions, selectedKelas);
            dd.classList.add('open');
        }
    });
    document.getElementById('btnKelasClear').addEventListener('click', () => {
        selectedKelas = [];
        renderMultiOptions(document.getElementById('filterKelasList'), kelasOptions, selectedKelas);
        updateKelasLabel();
    });
    document.getElementById('btnKelasApply').addEventListener('click', () => {
        selectedKelas = readChecked(document.getElementById('filterKelasList'));
        updateKelasLabel();
        document.getElementById('filterKelasDropdown').classList.remove('open');
        refreshAll(true);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.field')) closeAllDropdowns();
    });

    document.getElementById('btnApply').addEventListener('click', () => refreshAll(true));
    document.getElementById('btnSearch').addEventListener('click', () => refreshAll(true));
    document.getElementById('btnReset').addEventListener('click', async () => {
        document.getElementById('filterTglDari').value = today;
        document.getElementById('filterTglSampai').value = today;
        document.getElementById('filterKeterangan').value = '';
        document.getElementById('filterSearch').value = '';
        selectedSekolah = [];
        selectedKelas = [];
        updateSekolahLabel();
        await loadKelasOptions();
        refreshAll(true);
    });
    document.getElementById('pageSizeSelect').addEventListener('change', (e) => {
        pageSize = parseInt(e.target.value, 10) || 50;
        refreshAll(true);
    });
    document.getElementById('btnPrev').addEventListener('click', () => { if (currentPage > 1) { currentPage -= 1; loadData(false); } });
    document.getElementById('btnNext').addEventListener('click', () => { if (hasMore) { currentPage += 1; loadData(false); } });
    document.getElementById('btnExportExcel').addEventListener('click', (e) => { e.preventDefault(); window.location.href = routes.exportExcel + '?' + buildQuery(); });
    document.getElementById('btnExportPdf').addEventListener('click', (e) => { e.preventDefault(); window.location.href = routes.exportPdf + '?' + buildQuery(); });

    @if(session('login_success'))
        Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('login_success')), timer: 1800, showConfirmButton: false });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
    @endif

    loadFilters().then(() => refreshAll(true));
});
</script>
</body>
</html>
