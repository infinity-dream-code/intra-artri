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
            --bg: #f4f6f8;
            --card: #ffffff;
            --shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
            --accent: #2563eb;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e5e9ef;
            --head-bg: #fef9c3;
            --head-text: #854d0e;
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
        .field label { display: block; font-size: .72rem; font-weight: 700; color: var(--muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }
        .field select, .field input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; font-size: .88rem; font-family: inherit; background: #fff; color: var(--text); }
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
        #cashlessTable { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1100px; }
        thead.main-thead { background: var(--head-bg); }
        #cashlessTable th { padding: 10px 8px; text-align: center; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; border-bottom: 1px solid var(--border); white-space: nowrap; color: var(--head-text); }
        #cashlessTable td { padding: 10px 8px; font-size: .84rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        #cashlessTable tbody tr:hover { background: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .money { font-weight: 700; white-space: nowrap; }
        .empty-state { text-align: center; padding: 40px 20px; color: var(--muted); }
        .pagination-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 14px 18px; border-top: 1px solid var(--border); }
        .page-info { font-size: .82rem; color: var(--muted); font-weight: 600; }
        .page-btns { display: flex; gap: 8px; }
        .page-btns button { border: 1px solid var(--border); background: #fff; border-radius: 8px; padding: 8px 12px; font-size: .82rem; font-weight: 600; cursor: pointer; font-family: inherit; }
        .page-btns button:disabled { opacity: .45; cursor: not-allowed; }
        @media (max-width: 900px) {
            .filter-body { grid-template-columns: 1fr 1fr; }
            .summary-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .filter-body { grid-template-columns: 1fr; }
            .main { padding: 14px; }
        }
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
                    <p>Sesuaikan periode, unit, kelas, teller, dan keterangan.</p>
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
                        <label for="filterSekolah">Sekolah / Unit</label>
                        <select id="filterSekolah"><option value="">Semua sekolah/unit</option></select>
                    </div>
                    <div class="field">
                        <label for="filterKelas">Kelas / Program</label>
                        <select id="filterKelas"><option value="">Semua kelas</option></select>
                    </div>
                    <div class="field">
                        <label for="filterTeller">Teller</label>
                        <select id="filterTeller"><option value="">Semua teller</option></select>
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
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Unit</th>
                            <th>Teller</th>
                            <th>Keterangan</th>
                            <th>Jumlah</th>
                            <th>TRANSNO</th>
                            <th>FIDBANK</th>
                        </tr>
                    </thead>
                    <tbody id="cashlessBody">
                        <tr><td colspan="11" class="empty-state">Memuat data...</td></tr>
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
                <div class="drawer-user-role">Kantin / Cashless</div>
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
    data: @json(route('laporan-cashless.data')),
    summary: @json(route('laporan-cashless.summary')),
    exportExcel: @json(route('laporan-cashless.export-excel')),
    exportPdf: @json(route('laporan-cashless.export-pdf')),
};

let currentPage = 1;
let pageSize = 50;
let hasMore = false;
let loading = false;

function todayStr() {
    const d = new Date();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${m}-${day}`;
}

function formatRupiah(n) {
    const num = Number(n || 0);
    return 'Rp ' + num.toLocaleString('id-ID');
}

function formatTanggal(v) {
    if (!v) return '-';
    const s = String(v).replace('T', ' ');
    return s.length > 19 ? s.slice(0, 19) : s;
}

function getFilters() {
    return {
        tgl_dari: document.getElementById('filterTglDari').value || todayStr(),
        tgl_sampai: document.getElementById('filterTglSampai').value || todayStr(),
        sekolah: document.getElementById('filterSekolah').value || '',
        kelas: document.getElementById('filterKelas').value || '',
        teller: document.getElementById('filterTeller').value || '',
        keterangan: document.getElementById('filterKeterangan').value || '',
        search: document.getElementById('filterSearch').value.trim() || '',
    };
}

function buildQuery(extra = {}) {
    const f = getFilters();
    const params = new URLSearchParams();
    Object.entries({ ...f, ...extra }).forEach(([k, v]) => {
        if (v !== '' && v !== null && v !== undefined) params.set(k, v);
    });
    return params.toString();
}

function fillSelect(el, items, allLabel, valueKey = 'value', labelKey = 'label') {
    const current = el.value;
    el.innerHTML = `<option value="">${allLabel}</option>`;
    (items || []).forEach((item) => {
        let value, label;
        if (typeof item === 'string' || typeof item === 'number') {
            value = String(item);
            label = String(item);
        } else {
            value = String(item[valueKey] ?? item.value ?? '');
            label = String(item[labelKey] ?? item.label ?? value);
        }
        if (!value) return;
        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = label;
        el.appendChild(opt);
    });
    if ([...el.options].some(o => o.value === current)) {
        el.value = current;
    }
}

async function loadFilters() {
    try {
        const res = await fetch(routes.filters, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Gagal memuat filter');
        fillSelect(document.getElementById('filterSekolah'), json.sekolah || [], 'Semua sekolah/unit');
        fillSelect(document.getElementById('filterKelas'), json.kelas || [], 'Semua kelas');
        fillSelect(document.getElementById('filterTeller'), json.teller || [], 'Semua teller');
        fillSelect(document.getElementById('filterKeterangan'), json.keterangan || [], 'Semua keterangan');
    } catch (e) {
        console.error(e);
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
        const res = await fetch(routes.summary + '?' + buildQuery(), { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Gagal ringkasan');
        const d = json.data || {};
        sumTransaksi.textContent = Number(d.total_transaksi || 0).toLocaleString('id-ID');
        sumTotal.textContent = formatRupiah(d.total_jumlah || 0);
        if (d.tgl_dari && d.tgl_sampai) {
            summaryPeriod.textContent = `${d.tgl_dari} s/d ${d.tgl_sampai}`;
        }
    } catch (e) {
        sumTransaksi.textContent = '-';
        sumTotal.textContent = '-';
        console.error(e);
    }
}

async function loadData(resetPage = false) {
    if (loading) return;
    if (resetPage) currentPage = 1;
    loading = true;

    const body = document.getElementById('cashlessBody');
    body.innerHTML = `<tr><td colspan="11" class="empty-state"><span class="spinner-sm"></span> Memuat data...</td></tr>`;
    document.getElementById('btnPrev').disabled = true;
    document.getElementById('btnNext').disabled = true;

    try {
        const qs = buildQuery({ page: currentPage, limit: pageSize });
        const res = await fetch(routes.data + '?' + qs, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Gagal memuat data');

        const rows = json.data || [];
        const pag = json.pagination || {};
        hasMore = !!pag.has_more;

        if (!rows.length) {
            body.innerHTML = `<tr><td colspan="11" class="empty-state">Tidak ada transaksi untuk filter ini</td></tr>`;
            document.getElementById('pageInfo').textContent = '0 data';
        } else {
            const startNo = pag.from || ((currentPage - 1) * pageSize + 1);
            body.innerHTML = rows.map((r, i) => `
                <tr>
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
            document.getElementById('pageInfo').textContent = `Menampilkan ${pag.from || 0}–${pag.to || 0}`;
        }

        document.getElementById('btnPrev').disabled = currentPage <= 1;
        document.getElementById('btnNext').disabled = !hasMore;
    } catch (e) {
        body.innerHTML = `<tr><td colspan="11" class="empty-state">${escapeHtml(e.message || 'Gagal memuat data')}</td></tr>`;
        document.getElementById('pageInfo').textContent = '-';
        Swal.fire({ icon: 'error', title: 'Data', text: e.message || 'Gagal memuat data' });
    } finally {
        loading = false;
    }
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function refreshAll(resetPage = true) {
    loadSummary();
    loadData(resetPage);
}

function exportUrl(base) {
    return base + '?' + buildQuery();
}

document.addEventListener('DOMContentLoaded', () => {
    const today = todayStr();
    document.getElementById('filterTglDari').value = today;
    document.getElementById('filterTglSampai').value = today;

    const drawer = document.getElementById('drawer');
    const backdrop = document.getElementById('drawerBackdrop');
    const openDrawer = () => { drawer.classList.add('open'); backdrop.classList.add('open'); drawer.setAttribute('aria-hidden', 'false'); };
    const closeDrawer = () => { drawer.classList.remove('open'); backdrop.classList.remove('open'); drawer.setAttribute('aria-hidden', 'true'); };
    document.getElementById('drawerToggle').addEventListener('click', openDrawer);
    document.getElementById('drawerClose').addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);

    const filterBody = document.getElementById('filterBody');
    document.getElementById('filterToggle').addEventListener('click', () => {
        const hidden = filterBody.style.display === 'none';
        filterBody.style.display = hidden ? '' : 'none';
        document.getElementById('filterToggleText').textContent = hidden ? 'Sembunyikan' : 'Tampilkan';
        document.getElementById('filterToggleIcon').className = hidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
    });

    document.getElementById('btnApply').addEventListener('click', () => refreshAll(true));
    document.getElementById('btnSearch').addEventListener('click', () => refreshAll(true));
    document.getElementById('btnReset').addEventListener('click', () => {
        document.getElementById('filterTglDari').value = today;
        document.getElementById('filterTglSampai').value = today;
        document.getElementById('filterSekolah').value = '';
        document.getElementById('filterKelas').value = '';
        document.getElementById('filterTeller').value = '';
        document.getElementById('filterKeterangan').value = '';
        document.getElementById('filterSearch').value = '';
        refreshAll(true);
    });

    document.getElementById('pageSizeSelect').addEventListener('change', (e) => {
        pageSize = parseInt(e.target.value, 10) || 50;
        refreshAll(true);
    });

    document.getElementById('btnPrev').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage -= 1;
            loadData(false);
        }
    });
    document.getElementById('btnNext').addEventListener('click', () => {
        if (hasMore) {
            currentPage += 1;
            loadData(false);
        }
    });

    document.getElementById('btnExportExcel').addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = exportUrl(routes.exportExcel);
    });
    document.getElementById('btnExportPdf').addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = exportUrl(routes.exportPdf);
    });

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
