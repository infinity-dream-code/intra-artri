<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Uang Saku Santri - Monitoring Kepsek</title>
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
        .main { padding: 20px; }
        .header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .burger { width: 36px; height: 36px; border: none; border-radius: 10px; background: #fff; padding: 0; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; box-shadow: var(--shadow); }
        .burger span { display: block; width: 18px; height: 2.5px; border-radius: 2px; background: var(--accent); }
        .title { font-size: 1.4rem; font-weight: 700; margin: 0; }
        .card { background: var(--card); border-radius: 14px; padding: 18px 20px; margin-bottom: 16px; box-shadow: var(--shadow); border: 1px solid var(--border); }
        .card h2 { margin: 0 0 4px; font-size: 1rem; font-weight: 700; }
        .card p.sub { margin: 0 0 14px; font-size: .8rem; color: var(--muted); }
        .filter-body { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px 16px; }
        .field label { display: block; font-size: .72rem; font-weight: 700; color: var(--muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }
        .field select, .field input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; font-size: .88rem; font-family: inherit; background: #fff; color: var(--text); }
        .field select:disabled { background: #f1f5f9; color: #94a3b8; }
        .search-group { display: flex; gap: 8px; }
        .search-group input { flex: 1; }
        .search-group button { padding: 10px 16px; border: none; border-radius: 10px; background: var(--accent); color: #fff; cursor: pointer; }
        .filter-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
        .btn { border: none; border-radius: 10px; padding: 10px 16px; font-size: .86rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; text-decoration: none; color: #fff; }
        .btn-primary { background: var(--accent); }
        .btn-reset { background: #fff; color: var(--text); border: 1px solid var(--border); }
        .summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .summary-grid .lbl { font-size: .72rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
        .summary-grid .val { font-size: 1.3rem; font-weight: 800; margin-top: 4px; }
        .summary-grid .val.green { color: #15803d; }
        .table-card { background: var(--card); border-radius: 14px; box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; }
        .table-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 16px 18px; border-bottom: 1px solid var(--border); }
        .table-toolbar h3 { margin: 0; font-size: .96rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .page-size { display: flex; align-items: center; gap: 8px; font-size: .82rem; color: var(--muted); }
        .page-size select { padding: 6px 10px; border-radius: 8px; border: 1px solid var(--border); font-family: inherit; }
        .table-scroll { overflow-x: auto; }
        table.list { width: 100%; border-collapse: collapse; min-width: 820px; }
        table.list th { background: var(--head-bg); color: var(--head-text); font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; padding: 11px 10px; text-align: center; white-space: nowrap; }
        table.list td { padding: 11px 10px; font-size: .84rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        table.list tbody tr:hover td { background: #f8fafc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .nama { font-weight: 700; }
        .money { font-weight: 700; white-space: nowrap; }
        .money.neg { color: #b91c1c; }
        .btn-cetak { display: inline-flex; align-items: center; gap: 6px; border: none; border-radius: 8px; padding: 7px 12px; background: #b91c1c; color: #fff; font-size: .76rem; font-weight: 700; text-decoration: none; white-space: nowrap; }
        .btn-cetak:hover { background: #991b1b; }
        .empty { text-align: center; padding: 40px 16px; color: var(--muted); }
        .pagination-bar { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 18px; border-top: 1px solid var(--border); }
        .page-info { font-size: .82rem; color: var(--muted); font-weight: 600; }
        .page-btns { display: flex; gap: 8px; }
        .page-btns button { border: 1px solid var(--border); background: #fff; border-radius: 8px; padding: 7px 14px; font-size: .82rem; font-weight: 600; cursor: pointer; font-family: inherit; }
        .page-btns button:disabled { opacity: .4; cursor: not-allowed; }
        @media (max-width: 900px) { .filter-body { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 640px) { .filter-body, .summary-grid { grid-template-columns: 1fr; } }
        @media (min-width: 1024px) {
            .app { margin-left: 280px; }
            .drawer { transform: translateX(0); }
            .drawer-backdrop, .burger, .drawer-close { display: none; }
        }
    </style>
</head>
<body>
@php
    $isSuperadmin = (int) session('user.is_superadmin', 0) === 1
        || strtolower(trim((string) session('user.kelompok', ''))) === 'superadmin';
@endphp
<div class="app">
    <div class="main">
        <header class="header">
            <button class="burger" id="drawerToggle" type="button" aria-label="Menu"><span></span><span></span><span></span></button>
            <h1 class="title">Laporan Uang Saku Santri</h1>
        </header>

        <div class="card">
            <h2>Filter</h2>
            <p class="sub">Periode tanggal dipakai untuk isi laporan per santri (saldo awal dihitung dari transaksi sebelum tanggal awal).</p>
            <div class="filter-body">
                <div class="field">
                    <label for="filterSekolah">Sekolah / Unit</label>
                    <select id="filterSekolah"><option value="">Memuat...</option></select>
                </div>
                <div class="field">
                    <label for="filterKelas">Kelas</label>
                    <select id="filterKelas" disabled><option value="">Pilih sekolah dulu</option></select>
                </div>
                <div class="field">
                    <label for="filterSearch">Cari Nama / NIS</label>
                    <div class="search-group">
                        <input type="text" id="filterSearch" placeholder="Nama atau NIS...">
                        <button type="button" id="btnSearch"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div class="field">
                    <label for="filterTglDari">Laporan Dari Tanggal</label>
                    <input type="date" id="filterTglDari">
                </div>
                <div class="field">
                    <label for="filterTglSampai">Sampai Tanggal</label>
                    <input type="date" id="filterTglSampai">
                </div>
            </div>
            <div class="filter-actions">
                <button type="button" class="btn btn-reset" id="btnReset">Reset</button>
                <button type="button" class="btn btn-primary" id="btnApply"><i class="fas fa-filter"></i> Terapkan</button>
            </div>
        </div>

        <div class="card">
            <div class="summary-grid">
                <div><div class="lbl">Jumlah Santri</div><div class="val" id="sumSantri">-</div></div>
                <div><div class="lbl">Total Saldo Uang Saku</div><div class="val green" id="sumSaldo">-</div></div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3><i class="fas fa-wallet"></i> Daftar Saldo Uang Saku</h3>
                <div class="page-size">
                    <label for="pageSize">Tampilkan</label>
                    <select id="pageSize">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>per halaman</span>
                </div>
            </div>
            <div class="table-scroll">
                <table class="list">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIS</th>
                            <th>Kelas</th>
                            <th>Unit</th>
                            <th>Saldo Uang Saku</th>
                            <th>Laporan</th>
                        </tr>
                    </thead>
                    <tbody id="listBody">
                        <tr><td colspan="7" class="empty">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="pagination-bar">
                <div class="page-info" id="pageInfo">-</div>
                <div class="page-btns">
                    <button type="button" id="btnPrev">Sebelumnya</button>
                    <button type="button" id="btnNext">Berikutnya</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="drawer-backdrop" id="drawerBackdrop"></div>
<aside class="drawer" id="drawer">
    <div class="drawer-header">
        <div class="drawer-header-main">
            <div class="drawer-logo"><img src="{{ asset('logo.png') }}" alt="Logo"></div>
            <div>
                <div class="drawer-user-name">{{ session('user.nama', session('user.username')) }}</div>
                <div class="drawer-user-role">{{ $isSuperadmin ? 'Superadmin' : 'Kepala Sekolah' }}</div>
            </div>
        </div>
        <button type="button" class="drawer-close" id="drawerClose" aria-label="Tutup menu"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="drawer-divider"></div>
    <div class="drawer-menu-label">Menu</div>
    <ul class="drawer-menu">
        <li class="drawer-item"><a href="{{ route('dashboard.monitoring-kepsek') }}" class="drawer-link"><span class="icon"><i class="fas fa-house"></i></span><span>Dashboard</span></a></li>
        <li class="drawer-item"><a href="{{ route('kepsek.tagihan-periode') }}" class="drawer-link"><span class="icon"><i class="fas fa-file-invoice-dollar"></i></span><span>Tagihan Periode</span></a></li>
        <li class="drawer-item"><a href="{{ route('kepsek.uang-saku') }}" class="drawer-link active"><span class="icon"><i class="fas fa-wallet"></i></span><span>Laporan Uang Saku</span></a></li>
        @if($isSuperadmin)
            <li class="drawer-item"><a href="{{ route('kepsek.kelola-user') }}" class="drawer-link"><span class="icon"><i class="fas fa-users-gear"></i></span><span>Kelola User</span></a></li>
            <li class="drawer-item"><a href="{{ route('kepsek.import-penagihan') }}" class="drawer-link"><span class="icon"><i class="fas fa-file-import"></i></span><span>Import Penagihan</span></a></li>
        @endif
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
    filters: @json(route('kepsek.tagihan-periode.filters')),
    kelas: @json(route('kepsek.tagihan-periode.kelas')),
    data: @json(route('kepsek.uang-saku.data')),
    laporan: @json(route('kepsek.uang-saku.laporan')),
};

let currentPage = 1;
let pageSize = 10;
let hasMore = false;
let sekolahOptions = [];

function pad(n) { return String(n).padStart(2, '0'); }
function ymd(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
function defaultDari() { const d = new Date(); return ymd(new Date(d.getFullYear(), d.getMonth(), 1)); }
function formatRupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
function escapeHtml(str) {
    return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
async function getJson(url) {
    const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
    let json = {};
    try { json = await res.json(); } catch (e) {
        throw new Error(res.status === 401 || res.status === 419 ? 'Sesi berakhir. Silakan login ulang.' : 'Respons server tidak valid (HTTP ' + res.status + ').');
    }
    if (!res.ok || json.success === false) throw new Error(json.message || ('Gagal (HTTP ' + res.status + ')'));
    return json;
}

function selectedSekolah() { return document.getElementById('filterSekolah').value; }

function buildListQuery() {
    const q = new URLSearchParams();
    const sekolah = selectedSekolah();
    if (sekolah) q.append('sekolah[]', sekolah);
    const kelas = document.getElementById('filterKelas').value;
    if (sekolah && kelas) q.append('kelas[]', kelas);
    const search = document.getElementById('filterSearch').value.trim();
    if (search) q.set('search', search);
    q.set('page', currentPage);
    q.set('limit', pageSize);
    return q.toString();
}

function buildLaporanUrl(custid) {
    const q = new URLSearchParams({ custid: custid });
    const dari = document.getElementById('filterTglDari').value;
    const sampai = document.getElementById('filterTglSampai').value;
    if (dari) q.set('tgl_dari', dari);
    if (sampai) q.set('tgl_sampai', sampai);
    return routes.laporan + '?' + q.toString();
}

async function loadFilters() {
    const sel = document.getElementById('filterSekolah');
    try {
        const json = await getJson(routes.filters);
        sekolahOptions = (json.sekolah || []).map(s => typeof s === 'string' ? { value: s, label: s } : s);
        const allLabel = @json(!empty($restrictedSekolah)) ? 'Semua unit saya' : 'Semua sekolah';
        sel.innerHTML = `<option value="">${allLabel}</option>` + sekolahOptions.map(s =>
            `<option value="${escapeHtml(s.value)}">${escapeHtml(s.label || s.value)}</option>`).join('');
        if (sekolahOptions.length === 1) {
            sel.value = sekolahOptions[0].value;
            await loadKelas();
        }
    } catch (e) {
        sel.innerHTML = '<option value="">Gagal memuat</option>';
    }
}

async function loadKelas() {
    const sel = document.getElementById('filterKelas');
    const sekolah = selectedSekolah();
    if (!sekolah) {
        sel.innerHTML = '<option value="">Pilih sekolah dulu</option>';
        sel.disabled = true;
        return;
    }
    sel.disabled = true;
    sel.innerHTML = '<option value="">Memuat kelas...</option>';
    try {
        const json = await getJson(routes.kelas + '?' + new URLSearchParams([['sekolah[]', sekolah]]).toString());
        const kelas = json.data || [];
        sel.innerHTML = '<option value="">Semua kelas</option>' + kelas.map(k =>
            `<option value="${escapeHtml(k)}">${escapeHtml(k)}</option>`).join('');
        sel.disabled = false;
    } catch (e) {
        sel.innerHTML = '<option value="">Gagal memuat kelas</option>';
    }
}

async function loadData(resetPage) {
    if (resetPage) currentPage = 1;
    const body = document.getElementById('listBody');
    body.innerHTML = '<tr><td colspan="7" class="empty">Memuat data...</td></tr>';
    document.getElementById('sumSantri').textContent = '...';
    document.getElementById('sumSaldo').textContent = '...';

    try {
        const json = await getJson(routes.data + '?' + buildListQuery());
        const rows = json.data || [];
        hasMore = !!json.has_more;
        const summary = json.summary || {};
        document.getElementById('sumSantri').textContent = Number(summary.jumlah_santri || 0).toLocaleString('id-ID') + ' santri';
        document.getElementById('sumSaldo').textContent = formatRupiah(summary.total_saldo);

        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="7" class="empty">Tidak ada data santri.</td></tr>';
        } else {
            const startNo = (currentPage - 1) * pageSize + 1;
            body.innerHTML = rows.map((r, i) => `
                <tr>
                    <td class="text-center">${startNo + i}</td>
                    <td class="nama">${escapeHtml(String(r.nama || '-').toUpperCase())}</td>
                    <td class="text-center">${escapeHtml(r.nis || '-')}</td>
                    <td class="text-center">${escapeHtml(r.kelas || '-')}</td>
                    <td class="text-center">${escapeHtml(r.sekolah || '-')}</td>
                    <td class="text-right money ${Number(r.saldo) < 0 ? 'neg' : ''}">${formatRupiah(r.saldo)}</td>
                    <td class="text-center">
                        <a class="btn-cetak" href="${escapeHtml(buildLaporanUrl(r.custid))}" target="_blank" rel="noopener">
                            <i class="fas fa-print"></i> Cetak Laporan
                        </a>
                    </td>
                </tr>
            `).join('');
        }
        document.getElementById('pageInfo').textContent = 'Halaman ' + currentPage;
    } catch (e) {
        body.innerHTML = `<tr><td colspan="7" class="empty" style="color:#991b1b;">${escapeHtml(e.message)}</td></tr>`;
        document.getElementById('sumSantri').textContent = '-';
        document.getElementById('sumSaldo').textContent = '-';
        hasMore = false;
    }
    document.getElementById('btnPrev').disabled = currentPage <= 1;
    document.getElementById('btnNext').disabled = !hasMore;
}

document.addEventListener('DOMContentLoaded', async () => {
    const drawer = document.getElementById('drawer');
    const backdrop = document.getElementById('drawerBackdrop');
    document.getElementById('drawerToggle').addEventListener('click', () => { drawer.classList.add('open'); backdrop.classList.add('open'); });
    document.getElementById('drawerClose').addEventListener('click', () => { drawer.classList.remove('open'); backdrop.classList.remove('open'); });
    backdrop.addEventListener('click', () => { drawer.classList.remove('open'); backdrop.classList.remove('open'); });

    document.getElementById('filterTglDari').value = defaultDari();
    document.getElementById('filterTglSampai').value = ymd(new Date());

    document.getElementById('filterSekolah').addEventListener('change', async () => { await loadKelas(); loadData(true); });
    document.getElementById('filterKelas').addEventListener('change', () => loadData(true));
    document.getElementById('btnSearch').addEventListener('click', () => loadData(true));
    document.getElementById('filterSearch').addEventListener('keydown', e => { if (e.key === 'Enter') loadData(true); });
    document.getElementById('btnApply').addEventListener('click', () => loadData(true));
    document.getElementById('pageSize').addEventListener('change', e => { pageSize = parseInt(e.target.value, 10) || 10; loadData(true); });
    document.getElementById('btnPrev').addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadData(false); } });
    document.getElementById('btnNext').addEventListener('click', () => { if (hasMore) { currentPage++; loadData(false); } });
    document.getElementById('btnReset').addEventListener('click', async () => {
        document.getElementById('filterSearch').value = '';
        document.getElementById('filterTglDari').value = defaultDari();
        document.getElementById('filterTglSampai').value = ymd(new Date());
        document.getElementById('filterSekolah').value = sekolahOptions.length === 1 ? sekolahOptions[0].value : '';
        await loadKelas();
        loadData(true);
    });

    await loadFilters();
    loadData(true);
});
</script>
</body>
</html>
