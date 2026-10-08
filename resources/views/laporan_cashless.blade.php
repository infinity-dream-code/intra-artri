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
            --bg: #eef2f7;
            --card: #ffffff;
            --shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.05);
            --accent: #2563eb;
            --accent-soft: #eff6ff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --head-bg: #f8fafc;
            --head-text: #334155;
            --green: #0f766e;
            --red: #b91c1c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background:
                radial-gradient(1200px 420px at 10% -10%, #dbeafe 0%, transparent 55%),
                radial-gradient(900px 360px at 100% 0%, #e0e7ff 0%, transparent 50%),
                var(--bg);
            color: var(--text);
        }
        .app { min-height: 100vh; transition: margin-left .25s ease; }
        .drawer-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.5); opacity: 0; pointer-events: none; transition: opacity .25s; z-index: 50; }
        .drawer-backdrop.open { opacity: 1; pointer-events: auto; }
        .drawer {
            position: fixed; top: 0; left: 0; width: 280px; height: 100%;
            background: linear-gradient(180deg,#1e1b4b 0%,#0f0d1e 100%);
            color: #e2e8f0; transform: translateX(-100%); transition: transform .25s;
            padding: 24px 20px; display: flex; flex-direction: column; z-index: 51;
        }
        .drawer.open { transform: translateX(0); }
        .drawer-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 20px; }
        .drawer-header-main { display: flex; align-items: center; min-width: 0; flex: 1; }
        .drawer-logo { width: 48px; height: 48px; border-radius: 12px; overflow: hidden; margin-right: 12px; flex-shrink: 0; background: rgba(255,255,255,.06); }
        .drawer-logo img { width: 100%; height: 100%; object-fit: contain; }
        .drawer-user-name { font-size: .95rem; font-weight: 700; color: #f8fafc; }
        .drawer-user-role { font-size: .78rem; color: #94a3b8; margin-top: 2px; }
        .drawer-close { width: 34px; height: 34px; border: none; border-radius: 10px; flex-shrink: 0; background: rgba(148,163,184,.15); color: #e2e8f0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        .drawer-divider { height: 1px; background: rgba(255,255,255,.1); margin: 16px 0; }
        .drawer-menu-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .12em; color: #64748b; margin-bottom: 10px; padding-left: 14px; font-weight: 600; }
        .drawer-menu { list-style: none; padding: 0; margin: 0; flex: 1; }
        .drawer-item { margin-bottom: 4px; }
        .drawer-link { display: flex; align-items: center; padding: 12px 14px; border-radius: 12px; color: #cbd5e1; text-decoration: none; font-size: .9rem; font-weight: 500; border: none; background: transparent; width: 100%; cursor: pointer; font-family: inherit; }
        .drawer-link span.icon { width: 24px; display: inline-flex; justify-content: center; margin-right: 12px; color: #94a3b8; }
        .drawer-link:hover, .drawer-link.active { background: linear-gradient(135deg,rgba(37,99,235,.35) 0%,rgba(29,78,216,.2) 100%); color: #dbeafe; }
        .drawer-link.active span.icon { color: #93c5fd; }
        .drawer-footer { font-size: .75rem; color: #64748b; margin-top: auto; padding-top: 16px; }

        .main { padding: 22px 24px 40px; max-width: 1320px; margin: 0 auto; }
        .header { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
        .burger { width: 40px; height: 40px; border: none; border-radius: 12px; background: #fff; padding: 0; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); }
        .burger span { display: block; width: 18px; height: 2.5px; border-radius: 2px; background: var(--accent); }
        .title-wrap { display: flex; flex-direction: column; gap: 2px; }
        .title { font-size: 1.45rem; font-weight: 800; margin: 0; letter-spacing: -.02em; }
        .subtitle { margin: 0; font-size: .84rem; color: var(--muted); font-weight: 500; }

        .filter-card, .summary-bar, .table-card {
            background: var(--card); border-radius: 18px; margin-bottom: 16px;
            box-shadow: var(--shadow); border: 1px solid rgba(226,232,240,.9);
        }
        .filter-card { padding: 18px 20px 16px; }
        .filter-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
        .filter-top h2 { margin: 0; font-size: 1rem; font-weight: 800; display: flex; align-items: center; gap: 8px; }
        .filter-top h2 i { color: var(--accent); }
        .filter-top p { margin: 4px 0 0; font-size: .8rem; color: var(--muted); line-height: 1.4; }
        .filter-toggle { border: 1px solid var(--border); background: #fff; color: var(--muted); font-size: .78rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; font-family: inherit; border-radius: 999px; padding: 7px 12px; }
        .filter-toggle:hover { background: #f8fafc; color: var(--text); }
        .filter-body { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        .field { position: relative; }
        .field label { display: block; font-size: .7rem; font-weight: 800; color: #64748b; margin-bottom: 7px; text-transform: uppercase; letter-spacing: .05em; }
        .field select, .field input, .multi-btn {
            width: 100%; padding: 11px 12px; border: 1px solid var(--border); border-radius: 12px;
            font-size: .88rem; font-family: inherit; background: #fff; color: var(--text);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .field select:focus, .field input:focus, .multi-btn:focus {
            outline: none; border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }
        .field input[readonly] {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            color: #475569; font-weight: 700; border-style: dashed;
        }
        .multi-btn { display: flex; align-items: center; justify-content: space-between; gap: 8px; cursor: pointer; text-align: left; }
        .multi-btn i { color: #94a3b8; font-size: .75rem; }
        .multi-btn:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
        .multi-dropdown {
            display: none; position: absolute; z-index: 30; top: calc(100% + 6px); left: 0; right: 0;
            background: #fff; border: 1px solid var(--border); border-radius: 14px; overflow: hidden;
            box-shadow: 0 18px 40px rgba(15,23,42,.14);
        }
        .multi-dropdown.open { display: block; animation: dropIn .14s ease; }
        @keyframes dropIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }
        .multi-dropdown-inner { max-height: 230px; overflow: auto; padding: 8px; }
        .multi-option { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 10px; cursor: pointer; font-size: .86rem; font-weight: 500; }
        .multi-option:hover { background: var(--accent-soft); }
        .multi-option input { accent-color: var(--accent); width: 15px; height: 15px; }
        .multi-dropdown-actions { display: flex; justify-content: flex-end; gap: 8px; padding: 8px 10px; border-top: 1px solid var(--border); background: #f8fafc; }
        .multi-clear-btn, .multi-apply-btn { border: none; border-radius: 9px; padding: 8px 12px; font-size: .78rem; font-weight: 700; cursor: pointer; font-family: inherit; }
        .multi-clear-btn { background: #fff; color: var(--muted); border: 1px solid var(--border); }
        .multi-apply-btn { background: var(--accent); color: #fff; }
        .multi-empty { color: var(--muted); font-size: .82rem; padding: 14px; text-align: center; }
        .search-group { display: flex; gap: 8px; }
        .search-group input { flex: 1; }
        .search-group button {
            width: 44px; border: none; border-radius: 12px; background: var(--accent); color: #fff;
            cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 16px rgba(37,99,235,.25);
        }
        .filter-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 12px; border-top: 1px solid #f1f5f9; }
        .btn {
            border: none; border-radius: 12px; padding: 10px 16px; font-size: .86rem; font-weight: 700;
            cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit;
            text-decoration: none; color: #fff; transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); box-shadow: 0 10px 18px rgba(37,99,235,.22); }
        .btn-excel { background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); }
        .btn-pdf { background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%); }
        .btn-reset { background: #fff; color: var(--text); border: 1px solid var(--border); box-shadow: none; }
        .btn-reset:hover { background: #f8fafc; }

        .summary-bar { padding: 18px 20px; }
        .summary-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .summary-header h3 { margin: 0; font-size: 1rem; font-weight: 800; display: flex; align-items: center; gap: 8px; }
        .summary-header h3 i { color: var(--accent); }
        .summary-period {
            font-size: .78rem; color: #1d4ed8; font-weight: 700; background: var(--accent-soft);
            border: 1px solid #bfdbfe; border-radius: 999px; padding: 6px 12px;
        }
        .summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .summary-item {
            position: relative; overflow: hidden;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid var(--border); border-radius: 16px; padding: 16px 18px;
        }
        .summary-item::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
            background: #94a3b8;
        }
        .summary-item.total::before { background: linear-gradient(180deg, #2563eb, #1d4ed8); }
        .summary-item .item-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .summary-item .label { font-size: .72rem; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
        .summary-item .icon-badge {
            width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center;
            background: #f1f5f9; color: #64748b; font-size: .9rem;
        }
        .summary-item.total .icon-badge { background: var(--accent-soft); color: var(--accent); }
        .summary-item .value { font-size: 1.55rem; font-weight: 800; color: var(--text); letter-spacing: -.02em; }
        .summary-item.total .value { color: var(--accent); }

        .spinner-sm { display: inline-block; width: 14px; height: 14px; border: 2px solid #cbd5e1; border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; vertical-align: -2px; margin-right: 6px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .table-card { overflow: hidden; }
        .table-toolbar {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
            padding: 16px 18px; border-bottom: 1px solid #f1f5f9; background: linear-gradient(180deg, #fff 0%, #fafbfc 100%);
        }
        .table-toolbar h3 { margin: 0; font-size: .98rem; font-weight: 800; display: flex; align-items: center; gap: 8px; }
        .table-toolbar h3 i { color: var(--accent); }
        .pagination-size { display: flex; align-items: center; gap: 8px; font-size: .82rem; color: var(--muted); font-weight: 600; }
        .pagination-size select { padding: 7px 10px; border-radius: 10px; border: 1px solid var(--border); font-family: inherit; background: #fff; }
        .btn-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        #cashlessTable { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1180px; }
        thead.main-thead th {
            position: sticky; top: 0; z-index: 2;
            padding: 12px 10px; text-align: center; font-size: .68rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid var(--border);
            white-space: nowrap; color: var(--head-text); background: var(--head-bg);
        }
        #cashlessTable td {
            padding: 12px 10px; font-size: .84rem; border-bottom: 1px solid #f1f5f9;
            vertical-align: middle; background: #fff;
        }
        #cashlessTable tbody tr.data-row:nth-child(even) td { background: #fbfdff; }
        #cashlessTable tbody tr.data-row:hover td { background: #f0f7ff; }
        #cashlessTable tbody tr.data-row.expanded td {
            background: #eff6ff; border-bottom-color: transparent;
        }
        .cell-date { font-weight: 700; color: #334155; white-space: nowrap; line-height: 1.25; }
        .cell-date .d { display: block; }
        .cell-date .t { display: block; font-size: .72rem; font-weight: 600; color: #94a3b8; }
        .cell-name { font-weight: 700; color: #0f172a; }
        .chip {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 42px; padding: 4px 8px; border-radius: 999px; font-size: .72rem; font-weight: 800;
            background: #f1f5f9; color: #475569;
        }
        .chip-ket { background: #ecfeff; color: #0e7490; }
        .money { font-weight: 800; white-space: nowrap; color: #0f172a; }
        .btn-expand {
            width: 32px; height: 32px; border: none; border-radius: 10px;
            background: var(--accent-soft); color: var(--accent); cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            transition: transform .12s ease, background .12s ease, color .12s ease;
            box-shadow: inset 0 0 0 1px #bfdbfe;
        }
        .btn-expand:hover { transform: scale(1.05); background: #dbeafe; }
        .btn-expand.open { background: var(--accent); color: #fff; box-shadow: 0 8px 16px rgba(37,99,235,.25); }
        tr.detail-row > td { padding: 0 14px 16px; background: #eff6ff; border-bottom: 1px solid #dbeafe; }
        .detail-box {
            background: #fff; border: 1px solid #dbeafe; border-radius: 14px; overflow: hidden;
            box-shadow: 0 8px 20px rgba(37,99,235,.08);
        }
        .detail-box .detail-head {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            padding: 12px 14px; background: linear-gradient(180deg, #f8fbff 0%, #eff6ff 100%);
            border-bottom: 1px solid #dbeafe; font-size: .82rem; font-weight: 800; color: #1e40af;
        }
        .detail-box table { width: 100%; border-collapse: collapse; min-width: 720px; }
        .detail-box th, .detail-box td { padding: 9px 12px; border-bottom: 1px solid #eef2f7; font-size: .78rem; text-align: left; }
        .detail-box th { background: #f8fafc; font-weight: 800; color: var(--muted); text-transform: uppercase; font-size: .66rem; letter-spacing: .04em; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .empty-state { text-align: center; padding: 48px 20px; color: var(--muted); font-weight: 600; }
        .pagination-bar {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
            padding: 14px 18px; border-top: 1px solid #f1f5f9; background: #fafbfc;
        }
        .page-info { font-size: .82rem; color: var(--muted); font-weight: 700; }
        .page-btns { display: flex; gap: 8px; }
        .page-btns button {
            border: 1px solid var(--border); background: #fff; border-radius: 10px;
            padding: 8px 14px; font-size: .82rem; font-weight: 700; cursor: pointer; font-family: inherit; color: var(--text);
        }
        .page-btns button:hover:not(:disabled) { background: var(--accent-soft); border-color: #bfdbfe; color: #1d4ed8; }
        .page-btns button:disabled { opacity: .4; cursor: not-allowed; }

        @media (max-width: 1100px) { .filter-body { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 900px) { .filter-body { grid-template-columns: 1fr 1fr; } .summary-grid { grid-template-columns: 1fr; } }
        @media (max-width: 640px) {
            .filter-body { grid-template-columns: 1fr; }
            .main { padding: 14px; }
            .title { font-size: 1.25rem; }
            .filter-actions { justify-content: stretch; }
            .filter-actions .btn { flex: 1; justify-content: center; }
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
            <div class="title-wrap">
                <h1 class="title">Laporan Cashless</h1>
                <p class="subtitle">Monitoring transaksi per teller — data hanya milik akun Anda</p>
            </div>
        </header>

        <div class="filter-card">
            <div class="filter-top">
                <div>
                    <h2><i class="fas fa-sliders"></i> Filter</h2>
                    <p id="filterHint">Pilih sekolah dulu untuk memuat kelas. Data dikunci ke akun login Anda.</p>
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
                        <input type="text" id="lockedTeller" value="{{ session('user.username', '') }}" readonly>
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
                    <div class="item-top">
                        <div class="label">Jumlah Transaksi</div>
                        <span class="icon-badge"><i class="fas fa-receipt"></i></span>
                    </div>
                    <div class="value" id="sumTransaksi"><span class="spinner-sm"></span> Menghitung...</div>
                </div>
                <div class="summary-item total">
                    <div class="item-top">
                        <div class="label">Total Cashless</div>
                        <span class="icon-badge"><i class="fas fa-coins"></i></span>
                    </div>
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
                <div class="drawer-user-role">
                    @php $kelRole = strtolower(trim((string) session('user.kel', ''))); @endphp
                    {{ $kelRole === 'usaku' ? 'Admin Uang Saku' : ($kelRole !== '' ? ucfirst($kelRole) : 'Cashless') }}
                </div>
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

const canMultiFilter = @json(strtolower(trim((string) session('user.kel', ''))) === 'usaku');

let currentPage = 1;
let pageSize = 50;
let hasMore = false;
let loading = false;
let selectedSekolah = [];
let selectedKelas = [];
let sekolahOptions = [];
let kelasOptions = [];

(function initFilterHint() {
    const hint = document.getElementById('filterHint');
    if (!hint) return;
    hint.textContent = canMultiFilter
        ? 'Admin Uang Saku: boleh pilih lebih dari satu sekolah/kelas. Data dikunci ke akun login Anda.'
        : 'Pilih satu sekolah dulu untuk memuat kelas. Multi sekolah/kelas hanya untuk Admin Uang Saku (usaku).';
})();

function todayStr() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}
function formatRupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
function formatTanggal(v, stacked) {
    if (!v) return '-';
    const s = String(v).replace('T', ' ');
    const full = s.length > 19 ? s.slice(0, 19) : s;
    if (!stacked) return full;
    const parts = full.split(' ');
    if (parts.length < 2) return full;
    return `<span class="d">${escapeHtml(parts[0])}</span><span class="t">${escapeHtml(parts[1])}</span>`;
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
    const sekolah = enforceSingleSelection(selectedSekolah.slice());
    const kelas = sekolah.length ? enforceSingleSelection(selectedKelas.slice()) : [];
    return {
        tgl_dari: document.getElementById('filterTglDari').value || todayStr(),
        tgl_sampai: document.getElementById('filterTglSampai').value || todayStr(),
        sekolah,
        kelas,
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
    const inputType = canMultiFilter ? 'checkbox' : 'radio';
    const name = listEl.id === 'filterKelasList' ? 'filterKelasPick' : 'filterSekolahPick';
    listEl.innerHTML = options.map(item => {
        const value = typeof item === 'string' ? item : String(item[valueKey] ?? item.value ?? '');
        const label = typeof item === 'string' ? item : String(item[labelKey] ?? item.label ?? value);
        if (!value) return '';
        const checked = selected.includes(value) ? 'checked' : '';
        return `<label class="multi-option"><input type="${inputType}" name="${name}" value="${escapeHtml(value)}" ${checked}><span>${escapeHtml(label)}</span></label>`;
    }).join('');
}
function enforceSingleSelection(arr) {
    if (canMultiFilter || arr.length <= 1) return arr;
    return arr.slice(0, 1);
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
    detailTr.innerHTML = `<td colspan="12"><div class="detail-box"><div class="detail-head"><span><i class="fas fa-list"></i> Detail Transaksi</span><span>${escapeHtml(item.transno || '-')}</span></div><div style="padding:14px;color:#64748b;"><span class="spinner-sm"></span> Memuat detail...</div></div></td>`;
    row.after(detailTr);

    try {
        const qs = new URLSearchParams({ transno: item.transno || '', custid: item.custid || 0 });
        const res = await fetch(routes.detail + '?' + qs.toString(), { headers: apiHeaders() });
        const json = await parseJsonResponse(res);
        if (!json.success) throw new Error(json.message || 'Gagal detail');
        const rows = json.data || [];
        if (!rows.length) {
            detailTr.innerHTML = `<td colspan="12"><div class="detail-box"><div class="detail-head"><span><i class="fas fa-list"></i> Detail Transaksi</span><span>${escapeHtml(item.transno || '-')}</span></div><div style="padding:16px;color:#64748b;">Tidak ada detail transaksi untuk TRANSNO ini.</div></div></td>`;
            return;
        }
        detailTr.innerHTML = `<td colspan="12"><div class="detail-box">
            <div class="detail-head"><span><i class="fas fa-list"></i> Detail Transaksi</span><span>${escapeHtml(item.transno || '-')} · ${rows.length} baris</span></div>
            <table>
            <thead><tr>
                <th>Tanggal</th><th>Metode</th><th>Noreff</th><th>Debet</th><th>Kredit</th><th>Channel</th><th>Reff Bank</th><th>Keterangan</th>
            </tr></thead>
            <tbody>${rows.map(r => `<tr>
                <td>${escapeHtml(formatTanggal(r.tanggal))}</td>
                <td>${escapeHtml(r.metode || '-')}</td>
                <td>${escapeHtml(r.noreff || '-')}</td>
                <td class="text-right money">${formatRupiah(r.debet)}</td>
                <td class="text-right money">${formatRupiah(r.kredit)}</td>
                <td>${escapeHtml(r.kdchannel || '-')}</td>
                <td>${escapeHtml(r.reffbank || '-')}</td>
                <td>${escapeHtml(r.keterangan || '-')}</td>
            </tr>`).join('')}</tbody>
        </table></div></td>`;
    } catch (e) {
        detailTr.innerHTML = `<td colspan="12"><div class="detail-box"><div class="detail-head"><span><i class="fas fa-list"></i> Detail Transaksi</span></div><div style="padding:16px;color:#991b1b;">${escapeHtml(e.message || 'Gagal memuat detail')}</div></div></td>`;
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
                    <td class="text-center cell-date">${formatTanggal(r.tanggal, true)}</td>
                    <td class="text-center">${escapeHtml(r.nis || '-')}</td>
                    <td class="cell-name">${escapeHtml((r.nama || '-').toUpperCase())}</td>
                    <td class="text-center"><span class="chip">${escapeHtml(r.kelas || '-')}</span></td>
                    <td class="text-center">${escapeHtml(r.sekolah || r.unit || '-')}</td>
                    <td class="text-center">${escapeHtml(r.teller || '-')}</td>
                    <td class="text-center"><span class="chip chip-ket">${escapeHtml(r.keterangan || '-')}</span></td>
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
        selectedSekolah = enforceSingleSelection(readChecked(document.getElementById('filterSekolahList')));
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
        selectedKelas = enforceSingleSelection(readChecked(document.getElementById('filterKelasList')));
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
