<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Penagihan - Monitoring Kepsek</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg: #f4f6f8; --card: #ffffff; --shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
            --accent: #2563eb; --text: #0f172a; --muted: #64748b; --border: #e5e9ef;
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
        .main { padding: 20px; max-width: 1280px; }
        .header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .burger { width: 36px; height: 36px; border: none; border-radius: 10px; background: #fff; padding: 0; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; box-shadow: var(--shadow); }
        .burger span { display: block; width: 18px; height: 2.5px; border-radius: 2px; background: var(--accent); }
        .title { font-size: 1.4rem; font-weight: 700; margin: 0; }
        .card { background: var(--card); border-radius: 14px; box-shadow: var(--shadow); border: 1px solid var(--border); padding: 20px; margin-bottom: 16px; }
        .card h2 { margin: 0 0 8px; font-size: 1rem; }
        .card p { margin: 0 0 14px; font-size: .86rem; color: var(--muted); line-height: 1.5; }
        .btn { border: none; border-radius: 10px; padding: 10px 16px; font-size: .86rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; color: #fff; text-decoration: none; }
        .btn-primary { background: var(--accent); }
        .btn-excel { background: #0f766e; }
        .btn-secondary { background: #fff; color: var(--text); border: 1px solid var(--border); }
        .btn-sm { padding: 7px 12px; font-size: .78rem; border-radius: 8px; }
        .btn:disabled { opacity: .6; cursor: not-allowed; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .drop {
            border: 2px dashed #cbd5e1; border-radius: 12px; padding: 28px 18px; text-align: center;
            background: #f8fafc; margin-bottom: 14px;
        }
        .drop strong { display: block; margin-bottom: 6px; }
        .drop span { font-size: .82rem; color: var(--muted); }
        .file-name { margin-top: 10px; font-size: .84rem; font-weight: 600; color: var(--accent); }
        .hint-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 14px 16px; font-size: .82rem; color: #1e3a8a; }
        .hint-box ul { margin: 8px 0 0; padding-left: 18px; }
        .hint-box li { margin-bottom: 4px; }
        .result { display: none; }
        .result.show { display: block; }
        .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
        .stat { border: 1px solid var(--border); border-radius: 12px; padding: 12px 14px; background: #fff; }
        .stat .k { font-size: .72rem; font-weight: 700; color: var(--muted); text-transform: uppercase; }
        .stat .v { font-size: 1.2rem; font-weight: 800; margin-top: 4px; }
        .stat.ok .v { color: #166534; }
        .stat.fail .v { color: #991b1b; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .toolbar-left { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1; }
        .search-wrap { position: relative; flex: 1; min-width: 220px; max-width: 360px; }
        .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .85rem; }
        .search-wrap input {
            width: 100%; border: 1px solid var(--border); border-radius: 10px; padding: 10px 12px 10px 36px;
            font-size: .86rem; font-family: inherit; background: #fff; color: var(--text);
        }
        .search-wrap input:focus { outline: none; border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .meta-info { font-size: .8rem; color: var(--muted); white-space: nowrap; }
        .table-wrap { overflow-x: auto; border: 1px solid var(--border); border-radius: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: .82rem; }
        th, td { padding: 10px 12px; border-bottom: 1px solid var(--border); text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: var(--muted); white-space: nowrap; position: sticky; top: 0; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f8fafc; }
        .empty { text-align: center; color: var(--muted); padding: 28px 12px !important; }
        .badge {
            display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 999px;
            font-size: .72rem; font-weight: 700; white-space: nowrap;
        }
        .badge-wa { background: #dcfce7; color: #166534; }
        .badge-tel { background: #e0e7ff; color: #3730a3; }
        .badge-hasil { background: #f1f5f9; color: #334155; }
        .cell-muted { color: var(--muted); font-size: .78rem; }
        .cell-strong { font-weight: 600; }
        .cell-clamp { max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pager {
            display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;
            margin-top: 14px;
        }
        .pager-nav { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .page-btn {
            min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px; border: 1px solid var(--border);
            background: #fff; color: var(--text); font-size: .8rem; font-weight: 600; cursor: pointer; font-family: inherit;
        }
        .page-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
        .page-btn:disabled { opacity: .45; cursor: not-allowed; }
        .per-page { display: flex; align-items: center; gap: 8px; font-size: .8rem; color: var(--muted); }
        .per-page select {
            border: 1px solid var(--border); border-radius: 8px; padding: 6px 8px; font-family: inherit; font-size: .8rem; background: #fff;
        }
        @media (min-width: 960px) {
            .app.drawer-pinned { margin-left: 280px; }
            .drawer-backdrop { display: none !important; }
        }
    </style>
</head>
<body>
<div class="app" id="app">
    <main class="main">
        <div class="header">
            <button class="burger" id="drawerToggle" type="button" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
            <h1 class="title">Import Penagihan</h1>
        </div>

        <div class="card">
            <h2>Upload Excel</h2>
            <p>Import catatan penagihan ke <code>tbl_riwayatpenagihan</code>. NIS wajib cocok dengan data siswa.</p>

            <div class="actions" style="margin-bottom:14px;">
                <a class="btn btn-excel" href="{{ route('kepsek.import-penagihan.template') }}">
                    <i class="fas fa-file-excel"></i> Download Template
                </a>
            </div>

            <form id="importForm">
                @csrf
                <div class="drop">
                    <strong>Pilih file Excel (.xlsx / .xls / .csv)</strong>
                    <span>Kolom: no, nis, nama anak, tanggal komunikasi, media komunikasi, hasil komunikasi, rencana pembayaran, catatan</span>
                    <div style="margin-top:14px;">
                        <input type="file" id="fileInput" name="file" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="file-name" id="fileName"></div>
                </div>
                <div class="actions">
                    <button type="submit" class="btn btn-primary" id="btnImport">
                        <i class="fas fa-upload"></i> Import Sekarang
                    </button>
                </div>
            </form>
        </div>

        <div class="card hint-box">
            <strong>Ketentuan isi Excel</strong>
            <ul>
                <li><b>media komunikasi</b>: WhatsApp atau Telepon</li>
                <li><b>hasil komunikasi</b>: Akan melakukan pembayaran / Sudah melakukan pembayaran / Meminta waktu pembayaran / Mengalami kendala pembayaran / Tidak dapat dihubungi</li>
                <li><b>tanggal komunikasi</b>: contoh 02/09/2026 atau 2026-09-02</li>
                <li><b>nis</b> dipakai untuk mencari siswa; nama anak opsional</li>
            </ul>
        </div>

        <div class="card result" id="resultCard">
            <h2>Hasil Import</h2>
            <div class="stats">
                <div class="stat ok"><div class="k">Berhasil</div><div class="v" id="okCount">0</div></div>
                <div class="stat fail"><div class="k">Gagal</div><div class="v" id="failCount">0</div></div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Baris</th><th>NIS</th><th>Pesan</th></tr>
                    </thead>
                    <tbody id="errorBody">
                        <tr><td colspan="3" style="color:#64748b;">Tidak ada error</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="toolbar">
                <div class="toolbar-left">
                    <h2 style="margin:0;">Data Riwayat Penagihan</h2>
                    <span class="meta-info" id="listMeta">Memuat...</span>
                </div>
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="search" id="searchInput" placeholder="Cari NIS / nama / hasil / catatan..." autocomplete="off">
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>NIS</th>
                            <th>Nama Anak</th>
                            <th>Media</th>
                            <th>Hasil Komunikasi</th>
                            <th>Rencana Pembayaran</th>
                            <th>Catatan</th>
                            <th>Admin</th>
                        </tr>
                    </thead>
                    <tbody id="listBody">
                        <tr><td colspan="9" class="empty">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pager">
                <div class="per-page">
                    <span>Tampilkan</span>
                    <select id="perPageSelect">
                        <option value="10">10</option>
                        <option value="15" selected>15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>baris</span>
                </div>
                <div class="pager-nav" id="pagerNav"></div>
            </div>
        </div>
    </main>
</div>

<div class="drawer-backdrop" id="drawerBackdrop"></div>
<aside class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-header">
        <div class="drawer-header-main">
            <div class="drawer-logo"><img src="{{ asset('logo.png') }}" alt="Logo"></div>
            <div>
                <div class="drawer-user-name">{{ session('user.nama', session('user.username')) }}</div>
                <div class="drawer-user-role">Superadmin</div>
            </div>
        </div>
        <button type="button" class="drawer-close" id="drawerClose" aria-label="Tutup menu" title="Tutup">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
    <div class="drawer-divider"></div>
    <div class="drawer-menu-label">Menu</div>
    <ul class="drawer-menu">
        <li class="drawer-item"><a href="{{ route('dashboard.monitoring-kepsek') }}" class="drawer-link"><span class="icon"><i class="fas fa-house"></i></span><span>Dashboard</span></a></li>
        <li class="drawer-item"><a href="{{ route('kepsek.tagihan-periode') }}" class="drawer-link"><span class="icon"><i class="fas fa-file-invoice-dollar"></i></span><span>Tagihan Periode</span></a></li>
        <li class="drawer-item"><a href="{{ route('kepsek.kelola-user') }}" class="drawer-link"><span class="icon"><i class="fas fa-users-gear"></i></span><span>Kelola User</span></a></li>
        <li class="drawer-item"><a href="{{ route('kepsek.import-penagihan') }}" class="drawer-link active"><span class="icon"><i class="fas fa-file-import"></i></span><span>Import Penagihan</span></a></li>
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
    import: @json(route('kepsek.import-penagihan.store')),
    data: @json(route('kepsek.import-penagihan.data')),
};
const csrf = @json(csrf_token());

let state = { page: 1, perPage: 15, q: '', lastPage: 1, total: 0, loading: false };
let searchTimer = null;

document.getElementById('fileInput').addEventListener('change', function () {
    const f = this.files?.[0];
    document.getElementById('fileName').textContent = f ? f.name : '';
});

document.getElementById('importForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const fileInput = document.getElementById('fileInput');
    if (!fileInput.files?.length) {
        Swal.fire({ icon: 'warning', title: 'File belum dipilih', confirmButtonColor: '#2563eb' });
        return;
    }

    const btn = document.getElementById('btnImport');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengimport...';

    const fd = new FormData();
    fd.append('file', fileInput.files[0]);
    fd.append('_token', csrf);

    try {
        const res = await fetch(routes.import, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: fd,
        });
        const json = await res.json();
        if (!json.success) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: json.message || 'Import gagal', confirmButtonColor: '#2563eb' });
            return;
        }

        document.getElementById('resultCard').classList.add('show');
        document.getElementById('okCount').textContent = String(json.success_count || 0);
        document.getElementById('failCount').textContent = String(json.failed_count || 0);

        const errors = json.errors || [];
        const tbody = document.getElementById('errorBody');
        if (!errors.length) {
            tbody.innerHTML = '<tr><td colspan="3" style="color:#64748b;">Tidak ada error</td></tr>';
        } else {
            tbody.innerHTML = errors.map(err => `
                <tr>
                    <td>${escapeHtml(err.row ?? '-')}</td>
                    <td>${escapeHtml(err.nis ?? '-')}</td>
                    <td>${escapeHtml(err.message ?? '-')}</td>
                </tr>
            `).join('');
        }

        Swal.fire({
            icon: 'success',
            title: 'Import selesai',
            text: `Berhasil ${json.success_count || 0}, gagal ${json.failed_count || 0}`,
            confirmButtonColor: '#2563eb',
        });

        state.page = 1;
        loadList();
    } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Tidak dapat terhubung ke server', confirmButtonColor: '#2563eb' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> Import Sekarang';
    }
});

function escapeHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatDate(val) {
    if (!val) return '-';
    const s = String(val).slice(0, 10);
    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!m) return escapeHtml(s);
    return `${m[3]}/${m[2]}/${m[1]}`;
}

function mediaBadge(media) {
    const m = String(media || '');
    if (m.toLowerCase() === 'whatsapp') return `<span class="badge badge-wa">WhatsApp</span>`;
    if (m.toLowerCase() === 'telepon') return `<span class="badge badge-tel">Telepon</span>`;
    return escapeHtml(m || '-');
}

function renderPager() {
    const nav = document.getElementById('pagerNav');
    const last = state.lastPage || 1;
    const page = state.page || 1;
    let html = '';

    html += `<button type="button" class="page-btn" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}>&laquo;</button>`;

    const windowSize = 5;
    let start = Math.max(1, page - Math.floor(windowSize / 2));
    let end = Math.min(last, start + windowSize - 1);
    start = Math.max(1, end - windowSize + 1);

    if (start > 1) {
        html += `<button type="button" class="page-btn" data-page="1">1</button>`;
        if (start > 2) html += `<span class="cell-muted">...</span>`;
    }
    for (let i = start; i <= end; i++) {
        html += `<button type="button" class="page-btn ${i === page ? 'active' : ''}" data-page="${i}">${i}</button>`;
    }
    if (end < last) {
        if (end < last - 1) html += `<span class="cell-muted">...</span>`;
        html += `<button type="button" class="page-btn" data-page="${last}">${last}</button>`;
    }

    html += `<button type="button" class="page-btn" data-page="${page + 1}" ${page >= last ? 'disabled' : ''}>&raquo;</button>`;
    nav.innerHTML = html;
    nav.querySelectorAll('button[data-page]').forEach(btn => {
        btn.addEventListener('click', () => {
            const p = parseInt(btn.getAttribute('data-page'), 10);
            if (!Number.isFinite(p) || p < 1 || p > state.lastPage || p === state.page) return;
            state.page = p;
            loadList();
        });
    });
}

async function loadList() {
    if (state.loading) return;
    state.loading = true;
    const tbody = document.getElementById('listBody');
    tbody.innerHTML = '<tr><td colspan="9" class="empty"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>';

    const url = new URL(routes.data, window.location.origin);
    url.searchParams.set('page', String(state.page));
    url.searchParams.set('per_page', String(state.perPage));
    if (state.q) url.searchParams.set('q', state.q);

    try {
        const res = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const json = await res.json();
        if (!json.success) {
            tbody.innerHTML = `<tr><td colspan="9" class="empty">${escapeHtml(json.message || 'Gagal memuat data')}</td></tr>`;
            document.getElementById('listMeta').textContent = '';
            document.getElementById('pagerNav').innerHTML = '';
            return;
        }

        const rows = json.data || [];
        const meta = json.meta || {};
        state.page = Number(meta.page || state.page);
        state.perPage = Number(meta.per_page || state.perPage);
        state.total = Number(meta.total || 0);
        state.lastPage = Number(meta.last_page || 1);

        const from = state.total === 0 ? 0 : ((state.page - 1) * state.perPage) + 1;
        const to = Math.min(state.page * state.perPage, state.total);
        document.getElementById('listMeta').textContent = state.total
            ? `Menampilkan ${from}–${to} dari ${state.total} data`
            : 'Belum ada data';

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="empty">Belum ada data riwayat penagihan</td></tr>';
        } else {
            tbody.innerHTML = rows.map((r, idx) => `
                <tr>
                    <td>${from + idx}</td>
                    <td>${formatDate(r.tanggal_komunikasi)}</td>
                    <td class="cell-strong">${escapeHtml(r.nis || '-')}</td>
                    <td>${escapeHtml(r.nama || '-')}</td>
                    <td>${mediaBadge(r.media_komunikasi)}</td>
                    <td><span class="badge badge-hasil">${escapeHtml(r.hasil_komunikasi || '-')}</span></td>
                    <td class="cell-clamp" title="${escapeHtml(r.rencana_pembayaran || '')}">${escapeHtml(r.rencana_pembayaran || '-')}</td>
                    <td class="cell-clamp" title="${escapeHtml(r.catatan || '')}">${escapeHtml(r.catatan || '-')}</td>
                    <td class="cell-muted">${escapeHtml(r.admin || '-')}</td>
                </tr>
            `).join('');
        }
        renderPager();
    } catch (err) {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="9" class="empty">Tidak dapat terhubung ke server</td></tr>';
        document.getElementById('listMeta').textContent = '';
        document.getElementById('pagerNav').innerHTML = '';
    } finally {
        state.loading = false;
    }
}

document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        state.q = this.value.trim();
        state.page = 1;
        loadList();
    }, 350);
});

document.getElementById('perPageSelect').addEventListener('change', function () {
    state.perPage = parseInt(this.value, 10) || 15;
    state.page = 1;
    loadList();
});

const toggleBtn = document.getElementById('drawerToggle');
const closeBtn = document.getElementById('drawerClose');
const backdrop = document.getElementById('drawerBackdrop');
const drawer = document.getElementById('drawer');
const app = document.getElementById('app');
function openDrawer() { drawer.classList.add('open'); backdrop.classList.add('open'); drawer.setAttribute('aria-hidden', 'false'); }
function closeDrawer() { drawer.classList.remove('open'); backdrop.classList.remove('open'); drawer.setAttribute('aria-hidden', 'true'); }
toggleBtn.addEventListener('click', () => drawer.classList.contains('open') ? closeDrawer() : openDrawer());
closeBtn.addEventListener('click', closeDrawer);
backdrop.addEventListener('click', closeDrawer);
if (window.matchMedia('(min-width: 960px)').matches) {
    app.classList.add('drawer-pinned');
    openDrawer();
}

loadList();
</script>
</body>
</html>
