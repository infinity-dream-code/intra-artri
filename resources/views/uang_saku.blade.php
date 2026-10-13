<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Uang Saku - Monitoring Kepsek</title>
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
            --green-bg: #dcfce7; --green-text: #166534;
            --red-bg: #fee2e2; --red-text: #991b1b;
            --blue-bg: #dbeafe; --blue-text: #1e40af;
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
        .drawer-close {
            width: 34px; height: 34px; border: none; border-radius: 10px; flex-shrink: 0;
            background: rgba(148,163,184,.15); color: #e2e8f0; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .drawer-close:hover { background: rgba(148,163,184,.28); }
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
        .filter-card { background: var(--card); border-radius: 14px; padding: 18px 20px; margin-bottom: 16px; box-shadow: var(--shadow); border: 1px solid var(--border); }
        .filter-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .filter-top h2 { margin: 0; font-size: 1rem; font-weight: 700; }
        .filter-top p { margin: 3px 0 0; font-size: .8rem; color: var(--muted); }
        .filter-toggle { border: none; background: none; color: var(--muted); font-size: .82rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; font-family: inherit; }
        .filter-body { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px 16px; }
        .field label { display: block; font-size: .72rem; font-weight: 700; color: var(--muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }
        .field select, .field input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; font-size: .88rem; font-family: inherit; background: #fff; color: var(--text); }
        .field select:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .search-group { display: flex; gap: 8px; }
        .search-group input { flex: 1; }
        .search-group button { padding: 10px 16px; border: none; border-radius: 10px; background: var(--accent); color: #fff; font-size: .86rem; font-weight: 600; cursor: pointer; font-family: inherit; }
        .filter-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
        .btn { border: none; border-radius: 10px; padding: 10px 16px; font-size: .86rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; text-decoration: none; color: #fff; }
        .btn-reset { background: #fff; color: var(--text); border: 1px solid var(--border); }
        .table-card { background: var(--card); border-radius: 14px; box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; }
        .table-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 16px 18px; border-bottom: 1px solid var(--border); }
        .table-toolbar h3 { margin: 0; font-size: .96rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .table-scroll { overflow-x: auto; position: relative; -webkit-overflow-scrolling: touch; }
        #siswaTable { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: auto; min-width: 900px; }
        thead.main-thead { background: var(--head-bg); }
        #siswaTable th {
            padding: 10px 8px; text-align: center; font-size: .68rem; font-weight: 700; color: var(--head-text);
            text-transform: uppercase; letter-spacing: .03em; border-bottom: 1px solid var(--border); white-space: nowrap;
        }
        #siswaTable td { padding: 10px 8px; font-size: .78rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; background: #fff; }
        #siswaTable tbody tr:hover td { background: #f8fafc; }
        .col-no { width: 48px; min-width: 48px; color: var(--muted); font-weight: 600; text-align: center; }
        .col-nama { min-width: 180px; text-align: left; }
        .col-kelas { min-width: 70px; white-space: nowrap; text-align: center; }
        .col-sekolah { min-width: 100px; text-align: center; }
        .col-va { min-width: 140px; text-align: center; font-variant-numeric: tabular-nums; }
        .col-money { min-width: 120px; text-align: right; white-space: nowrap; font-weight: 700; font-size: .78rem !important; color: #2563eb; }
        .col-money.neg { color: #dc2626; }
        .col-action { min-width: 130px; text-align: center; white-space: nowrap; }
        .nama-cell .nm { font-weight: 600; display: block; }
        .nama-cell .nis { font-size: .7rem; color: var(--muted); }
        .btn-laporan-siswa {
            display: inline-flex; align-items: center; gap: 6px;
            border: none; border-radius: 8px; padding: 7px 11px;
            background: #b91c1c; color: #fff; font-size: .74rem; font-weight: 700;
            text-decoration: none; font-family: inherit; cursor: pointer; white-space: nowrap;
        }
        .btn-laporan-siswa:hover { background: #991b1b; color: #fff; }
        .spinner { width: 30px; height: 30px; border: 3px solid rgba(37,99,235,.15); border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; margin: 0 auto 10px; }
        .spinner-sm { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(37,99,235,.15); border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .empty, .loader { text-align: center; padding: 48px 20px; color: var(--muted); }
        .loader-text { font-size: .9rem; margin-top: 8px; }
        .pagination { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 12px 16px; border-top: 1px solid var(--border); }
        .pagination-info { font-size: .84rem; color: var(--muted); font-weight: 500; }
        .pagination-btns { display: flex; gap: 8px; }
        .pagination-size { display: flex; align-items: center; gap: 8px; font-size: .84rem; color: var(--muted); font-weight: 500; }
        .pagination-size select { padding: 6px 10px; border: 1px solid var(--border); border-radius: 8px; font-size: .84rem; font-family: inherit; background: #fff; color: var(--text); }
        .page-btn { border: 1px solid var(--border); background: #fff; color: var(--text); border-radius: 8px; padding: 8px 14px; font-size: .84rem; font-weight: 600; cursor: pointer; font-family: inherit; }
        .page-btn:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
        .page-btn:disabled { opacity: .4; cursor: not-allowed; }
        .summary-bar { margin-bottom: 16px; background: var(--card); border-radius: 14px; box-shadow: var(--shadow); border: 1px solid var(--border); padding: 18px 20px; }
        .summary-header { display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .summary-header h3 { margin: 0; font-size: .96rem; font-weight: 700; }
        .summary-period { font-size: .8rem; font-weight: 600; color: var(--accent); background: var(--blue-bg); padding: 4px 12px; border-radius: 999px; }
        .summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .summary-item .label { font-size: .74rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
        .summary-item .value { font-size: 1.15rem; font-weight: 800; }
        .summary-item.total .value { color: var(--accent); }
        .field-tagihan { position: relative; }
        .tagihan-select-btn { width:100%; display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border:1px solid var(--border); border-radius:10px; background:#fff; font-size:.88rem; font-family:inherit; color:var(--text); cursor:pointer; }
        .tagihan-select-btn:disabled { background: #f1f5f9; color: #64748b; cursor: not-allowed; }
        .tagihan-select-btn i { color: var(--muted); font-size:.78rem; transition: transform .15s; }
        .tagihan-select-btn.open i { transform: rotate(180deg); }
        .tagihan-dropdown { position:absolute; top:calc(100% + 6px); left:0; right:0; background:#fff; border:1px solid var(--border); border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,.14); z-index:20; display:none; overflow:hidden; }
        .tagihan-dropdown.open { display:block; }
        .tagihan-dropdown-inner { max-height:220px; overflow-y:auto; padding:8px; }
        .tagihan-option { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:8px; font-size:.85rem; cursor:pointer; text-transform: none !important; letter-spacing: 0 !important; color: var(--text) !important; font-weight: 500 !important; margin: 0 !important; }
        .tagihan-option:hover { background:#f1f5f9; }
        .tagihan-option input { accent-color: var(--accent); width:16px; height:16px; }
        .tagihan-dropdown-actions { display:flex; justify-content:space-between; gap:8px; padding:10px 12px; border-top:1px solid var(--border); background:#f8fafc; }
        .tagihan-dropdown-actions button { border:none; border-radius:8px; padding:7px 12px; font-size:.78rem; font-weight:600; cursor:pointer; font-family:inherit; }
        .tagihan-clear-btn { background:#fff; border:1px solid var(--border) !important; color:var(--text); }
        .tagihan-apply-btn { background: var(--accent); color:#fff; }
        .tagihan-empty-note { padding:14px; text-align:center; font-size:.8rem; color:var(--muted); }
        .mobile-list { display: none; flex-direction: column; gap: 10px; padding: 12px; }
        .m-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 12px 14px; box-shadow: var(--shadow); }
        .m-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
        .m-card-no { font-size: .72rem; font-weight: 700; color: var(--muted); }
        .m-card-name { font-size: .9rem; font-weight: 800; color: var(--text); line-height: 1.3; word-break: break-word; }
        .m-card-meta { font-size: .75rem; color: var(--muted); margin-top: 2px; }
        .m-money-item { background: #f8fafc; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
        .m-money-item span { font-size: .68rem; font-weight: 700; color: var(--muted); text-transform: uppercase; }
        .m-money-item b { font-size: .82rem; font-weight: 800; color: #2563eb; white-space: nowrap; }
        .m-card .btn-laporan-siswa { width: 100%; justify-content: center; padding: 9px 10px; }
        @media (max-width: 900px) {
            .main { padding: 14px; }
            .title { font-size: 1.15rem; }
            .table-toolbar { flex-direction: column; align-items: stretch; }
            .desktop-table-wrap { display: none !important; }
            .mobile-list { display: flex !important; }
            .filter-body { grid-template-columns: 1fr; }
            .summary-grid { grid-template-columns: 1fr; gap: 12px; }
            .filter-card { padding: 14px; }
            .filter-actions { justify-content: stretch; }
            .filter-actions .btn { flex: 1; justify-content: center; }
            .pagination { flex-direction: column; align-items: stretch; }
            .pagination-btns { width: 100%; }
            .pagination-btns .page-btn { flex: 1; }
        }
        @media (min-width: 960px) {
            .app.drawer-pinned { margin-left: 280px; }
            .drawer-backdrop { display: none; }
            .main { padding: 20px 18px; max-width: none; }
        }
    </style>
</head>
<body>
@php
    $isSuperadmin = (int) session('user.is_superadmin', 0) === 1
        || strtolower(trim((string) session('user.kelompok', ''))) === 'superadmin';
@endphp
<div class="app" id="app">
    <div class="main">
        <header class="header">
            <button class="burger" id="drawerToggle" type="button" aria-label="Buka/tutup menu" title="Menu"><span></span><span></span><span></span></button>
            <h1 class="title">Laporan Uang Saku</h1>
        </header>

        <div class="filter-card">
            <div class="filter-top">
                <div>
                    <h2>Filter</h2>
                    <p>Sesuaikan sekolah, kelas, dan periode laporan uang saku.</p>
                </div>
                <button type="button" class="filter-toggle" id="filterToggle">
                    <span id="filterToggleText">Sembunyikan</span>
                    <i class="fas fa-chevron-up" id="filterToggleIcon"></i>
                </button>
            </div>

            <div id="filterBody">
                <div class="filter-body">
                    <div class="field field-tagihan" id="fieldSekolahMulti" @unless($canMultiSelectSekolah ?? false) style="display:none" @endunless>
                        <label for="filterSekolahBtn">Sekolah / Unit</label>
                        <button type="button" id="filterSekolahBtn" class="tagihan-select-btn">
                            <span id="filterSekolahLabel">Semua sekolah/unit</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="tagihan-dropdown" id="filterSekolahDropdown">
                            <div class="tagihan-dropdown-inner" id="filterSekolahList">
                                <div class="tagihan-empty-note">Memuat...</div>
                            </div>
                            <div class="tagihan-dropdown-actions">
                                <button type="button" id="btnSekolahClear" class="tagihan-clear-btn">Bersihkan</button>
                                <button type="button" id="btnSekolahApply" class="tagihan-apply-btn">Terapkan</button>
                            </div>
                        </div>
                    </div>
                    <div class="field" id="fieldSekolahSingle" @if($canMultiSelectSekolah ?? false) style="display:none" @endif>
                        <label for="filterSekolah">Sekolah / Unit</label>
                        <select id="filterSekolah"><option value="">Memuat...</option></select>
                    </div>
                    <div class="field field-tagihan">
                        <label for="filterKelasBtn">Kelas / Program</label>
                        <button type="button" id="filterKelasBtn" class="tagihan-select-btn" disabled>
                            <span id="filterKelasLabel">Pilih sekolah dulu</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="tagihan-dropdown" id="filterKelasDropdown">
                            <div class="tagihan-dropdown-inner" id="filterKelasList">
                                <div class="tagihan-empty-note">Pilih sekolah dulu</div>
                            </div>
                            <div class="tagihan-dropdown-actions">
                                <button type="button" id="btnKelasClear" class="tagihan-clear-btn">Bersihkan</button>
                                <button type="button" id="btnKelasApply" class="tagihan-apply-btn">Terapkan</button>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label for="filterSearch">Cari nama</label>
                        <div class="search-group">
                            <input type="text" id="filterSearch" placeholder="Nama / NIS siswa...">
                            <button type="button" id="btnSearch"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    <div class="field">
                        <label for="filterTglDari">Periode Laporan Dari</label>
                        <input type="date" id="filterTglDari">
                    </div>
                    <div class="field">
                        <label for="filterTglSampai">Periode Laporan Sampai</label>
                        <input type="date" id="filterTglSampai">
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn btn-reset" id="btnReset">Reset</button>
                </div>
            </div>
        </div>

        <div class="summary-bar">
            <div class="summary-header">
                <h3><i class="fas fa-chart-pie"></i> Ringkasan</h3>
                <span class="summary-period" id="summaryPeriod"></span>
            </div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="label">Jumlah Santri</div>
                    <div class="value" id="sumSiswa"><span class="spinner-sm"></span> Menghitung...</div>
                </div>
                <div class="summary-item total">
                    <div class="label">Total Saldo Uang Saku</div>
                    <div class="value" id="sumSaldo"><span class="spinner-sm"></span> Menghitung...</div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3><i class="fas fa-wallet"></i> Daftar Uang Saku per Siswa</h3>
                <div class="pagination-size">
                    <label for="pageSizeSelect">Tampilkan</label>
                    <select id="pageSizeSelect">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>per halaman</span>
                </div>
            </div>

            <div class="table-scroll desktop-table-wrap" id="tableWrap">
                <table id="siswaTable">
                    <thead class="main-thead">
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-nama">Nama</th>
                            <th class="col-kelas">Kelas</th>
                            <th class="col-sekolah">Unit</th>
                            <th class="col-va">VA Uang Saku</th>
                            <th class="col-money">Saldo Uang Saku</th>
                            <th class="col-action">Laporan</th>
                        </tr>
                    </thead>
                    <tbody id="siswaBody"></tbody>
                </table>
            </div>
            <div class="mobile-list" id="siswaMobileList"></div>
            <div class="loader" id="loaderState" style="display:none;">
                <div class="spinner"></div>
                <div class="loader-text">Memuat data uang saku...</div>
            </div>
            <div class="empty" id="emptyState" style="display:none;">
                <i class="fas fa-inbox" style="font-size:2.2rem;opacity:.35;display:block;margin-bottom:10px;"></i>
                <span id="emptyStateText">Tidak ada data siswa</span>
            </div>

            <div class="pagination" id="paginationBar" style="display:none;">
                <div class="pagination-info" id="paginationInfo"></div>
                <div class="pagination-btns">
                    <button type="button" class="page-btn" id="btnPrev"><i class="fas fa-chevron-left"></i> Sebelumnya</button>
                    <button type="button" class="page-btn active" id="btnPageNum" disabled>1</button>
                    <button type="button" class="page-btn" id="btnNext">Berikutnya <i class="fas fa-chevron-right"></i></button>
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
                <div class="drawer-user-role">{{ $isSuperadmin ? 'Superadmin' : 'Kepala Sekolah' }}</div>
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
const canMultiSekolah = @json((bool) ($canMultiSelectSekolah ?? false));

let currentPage = 1;
let pageSize = 10;
let hasMore = false;
let sekolahOptions = [];
let selectedSekolahMulti = [];
let kelasOptions = [];
let selectedKelas = [];

function pad(n) { return String(n).padStart(2, '0'); }
function ymd(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
function defaultDari() { const d = new Date(); return ymd(new Date(d.getFullYear(), d.getMonth(), 1)); }
function formatTgl(v) { if (!v) return ''; const p = v.split('-'); return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : v; }
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

function getSelectedSekolah() {
    if (canMultiSekolah) return [...selectedSekolahMulti];
    const v = document.getElementById('filterSekolah').value;
    return v ? [v] : [];
}

function updatePeriodLabel() {
    const dari = document.getElementById('filterTglDari').value;
    const sampai = document.getElementById('filterTglSampai').value;
    document.getElementById('summaryPeriod').textContent = 'Periode laporan: ' + (formatTgl(dari) || 'Awal') + ' – ' + (formatTgl(sampai) || 'Sekarang');
}

/* ---------- Dropdown multi (sekolah & kelas) ---------- */
function closeAllDropdowns(except) {
    document.querySelectorAll('.tagihan-dropdown.open').forEach(dd => {
        if (dd.id === except) return;
        dd.classList.remove('open');
        const btn = document.getElementById(dd.id.replace('Dropdown', 'Btn'));
        if (btn) btn.classList.remove('open');
    });
}
function toggleDropdown(btnId, ddId) {
    const btn = document.getElementById(btnId);
    const dd = document.getElementById(ddId);
    if (btn.disabled) return;
    const willOpen = !dd.classList.contains('open');
    closeAllDropdowns(willOpen ? ddId : null);
    dd.classList.toggle('open', willOpen);
    btn.classList.toggle('open', willOpen);
}
function renderCheckboxList(containerId, options, selected, emptyText) {
    const box = document.getElementById(containerId);
    if (!options.length) {
        box.innerHTML = `<div class="tagihan-empty-note">${escapeHtml(emptyText)}</div>`;
        return;
    }
    box.innerHTML = options.map(o => `
        <label class="tagihan-option">
            <input type="checkbox" value="${escapeHtml(o.value)}" ${selected.includes(o.value) ? 'checked' : ''}>
            <span>${escapeHtml(o.label)}</span>
        </label>`).join('');
}
function readChecked(containerId) {
    return [...document.querySelectorAll('#' + containerId + ' input[type=checkbox]:checked')].map(i => i.value);
}
function multiLabel(selected, allLabel) {
    if (!selected.length) return allLabel;
    if (selected.length === 1) return selected[0];
    return selected.length + ' dipilih';
}

/* ---------- Load filter ---------- */
async function loadFilterOptions() {
    try {
        const json = await getJson(routes.filters);
        sekolahOptions = (json.sekolah || []).map(s => typeof s === 'string' ? { value: s, label: s } : { value: s.value, label: s.label || s.value });
    } catch (e) {
        sekolahOptions = [];
    }

    if (canMultiSekolah) {
        renderCheckboxList('filterSekolahList', sekolahOptions, selectedSekolahMulti, 'Tidak ada sekolah');
    } else {
        const sel = document.getElementById('filterSekolah');
        sel.innerHTML = '<option value="">Semua sekolah/unit</option>' + sekolahOptions.map(s =>
            `<option value="${escapeHtml(s.value)}">${escapeHtml(s.label)}</option>`).join('');
        if (sekolahOptions.length === 1) sel.value = sekolahOptions[0].value;
    }
    await loadKelas();
}

async function loadKelas() {
    const sekolah = getSelectedSekolah();
    const btn = document.getElementById('filterKelasBtn');
    const label = document.getElementById('filterKelasLabel');
    selectedKelas = [];
    kelasOptions = [];
    if (!sekolah.length) {
        btn.disabled = true;
        label.textContent = 'Pilih sekolah dulu';
        renderCheckboxList('filterKelasList', [], [], 'Pilih sekolah dulu');
        return;
    }
    btn.disabled = true;
    label.textContent = 'Memuat kelas...';
    try {
        const q = new URLSearchParams();
        sekolah.forEach(s => q.append('sekolah[]', s));
        const json = await getJson(routes.kelas + '?' + q.toString());
        kelasOptions = (json.data || []).map(k => ({ value: String(k), label: String(k) }));
        renderCheckboxList('filterKelasList', kelasOptions, selectedKelas, 'Tidak ada kelas');
        btn.disabled = false;
        label.textContent = 'Semua kelas';
    } catch (e) {
        label.textContent = 'Gagal memuat kelas';
    }
}

/* ---------- Data ---------- */
function buildLaporanUrl(custid) {
    const q = new URLSearchParams({ custid: custid });
    const dari = document.getElementById('filterTglDari').value;
    const sampai = document.getElementById('filterTglSampai').value;
    if (dari) q.set('tgl_dari', dari);
    if (sampai) q.set('tgl_sampai', sampai);
    return routes.laporan + '?' + q.toString();
}

function setState(state, text) {
    document.getElementById('loaderState').style.display = state === 'loading' ? 'block' : 'none';
    document.getElementById('emptyState').style.display = state === 'empty' ? 'block' : 'none';
    document.getElementById('tableWrap').style.visibility = state === 'data' ? 'visible' : 'hidden';
    document.getElementById('siswaMobileList').style.visibility = state === 'data' ? 'visible' : 'hidden';
    if (state !== 'data') {
        document.getElementById('siswaBody').innerHTML = '';
        document.getElementById('siswaMobileList').innerHTML = '';
    }
    if (text) document.getElementById('emptyStateText').textContent = text;
}

async function loadSiswaList(resetPage) {
    if (resetPage) currentPage = 1;
    updatePeriodLabel();
    setState('loading');
    document.getElementById('paginationBar').style.display = 'none';
    document.getElementById('sumSiswa').innerHTML = '<span class="spinner-sm"></span> Menghitung...';
    document.getElementById('sumSaldo').innerHTML = '<span class="spinner-sm"></span> Menghitung...';

    const q = new URLSearchParams();
    getSelectedSekolah().forEach(s => q.append('sekolah[]', s));
    selectedKelas.forEach(k => q.append('kelas[]', k));
    const search = document.getElementById('filterSearch').value.trim();
    if (search) q.set('search', search);
    q.set('page', currentPage);
    q.set('limit', pageSize);

    try {
        const json = await getJson(routes.data + '?' + q.toString());
        const rows = json.data || [];
        hasMore = !!json.has_more;
        const summary = json.summary || {};
        document.getElementById('sumSiswa').textContent = Number(summary.jumlah_santri || 0).toLocaleString('id-ID');
        document.getElementById('sumSaldo').textContent = formatRupiah(summary.total_saldo);

        if (!rows.length) {
            setState('empty', 'Tidak ada data siswa');
        } else {
            renderRows(rows);
            setState('data');
        }
        const total = Number(summary.jumlah_santri || 0);
        const start = rows.length ? (currentPage - 1) * pageSize + 1 : 0;
        const end = (currentPage - 1) * pageSize + rows.length;
        document.getElementById('paginationInfo').textContent = `Menampilkan ${start}–${end} dari ${total.toLocaleString('id-ID')} siswa`;
        document.getElementById('btnPageNum').textContent = String(currentPage);
        document.getElementById('btnPrev').disabled = currentPage <= 1;
        document.getElementById('btnNext').disabled = !hasMore;
        document.getElementById('paginationBar').style.display = rows.length || currentPage > 1 ? 'flex' : 'none';
    } catch (e) {
        setState('empty', e.message);
        document.getElementById('sumSiswa').textContent = '-';
        document.getElementById('sumSaldo').textContent = '-';
    }
}

function renderRows(rows) {
    const startNo = (currentPage - 1) * pageSize + 1;
    document.getElementById('siswaBody').innerHTML = rows.map((r, i) => {
        const saldo = Number(r.saldo || 0);
        return `
            <tr>
                <td class="col-no">${startNo + i}</td>
                <td class="col-nama nama-cell">
                    <span class="nm">${escapeHtml(String(r.nama || '-').toUpperCase())}</span>
                    <span class="nis">NIS ${escapeHtml(r.nis || '-')}</span>
                </td>
                <td class="col-kelas">${escapeHtml(r.kelas || '-')}</td>
                <td class="col-sekolah">${escapeHtml(r.sekolah || '-')}</td>
                <td class="col-va">${escapeHtml(r.va || '-')}</td>
                <td class="col-money ${saldo < 0 ? 'neg' : ''}">${formatRupiah(saldo)}</td>
                <td class="col-action">
                    <a class="btn-laporan-siswa" href="${escapeHtml(buildLaporanUrl(r.custid))}" target="_blank" rel="noopener">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </a>
                </td>
            </tr>`;
    }).join('');

    document.getElementById('siswaMobileList').innerHTML = rows.map((r, i) => `
        <div class="m-card">
            <div class="m-card-top">
                <div>
                    <div class="m-card-name">${escapeHtml(String(r.nama || '-').toUpperCase())}</div>
                    <div class="m-card-meta">${escapeHtml(r.kelas || '-')} · ${escapeHtml(r.sekolah || '-')}</div>
                    <div class="m-card-meta">VA ${escapeHtml(r.va || '-')}</div>
                </div>
                <span class="m-card-no">#${startNo + i}</span>
            </div>
            <div class="m-money-item"><span>Saldo Uang Saku</span><b>${formatRupiah(r.saldo)}</b></div>
            <a class="btn-laporan-siswa" href="${escapeHtml(buildLaporanUrl(r.custid))}" target="_blank" rel="noopener">
                <i class="fas fa-print"></i> Cetak Laporan
            </a>
        </div>`).join('');
}

/* ---------- Events ---------- */
document.getElementById('filterSekolahBtn').addEventListener('click', () => toggleDropdown('filterSekolahBtn', 'filterSekolahDropdown'));
document.getElementById('filterKelasBtn').addEventListener('click', () => toggleDropdown('filterKelasBtn', 'filterKelasDropdown'));
document.addEventListener('click', e => { if (!e.target.closest('.field-tagihan')) closeAllDropdowns(); });

document.getElementById('btnSekolahClear').addEventListener('click', () => {
    document.querySelectorAll('#filterSekolahList input').forEach(i => { i.checked = false; });
});
document.getElementById('btnSekolahApply').addEventListener('click', async () => {
    selectedSekolahMulti = readChecked('filterSekolahList');
    document.getElementById('filterSekolahLabel').textContent = multiLabel(selectedSekolahMulti, 'Semua sekolah/unit');
    closeAllDropdowns();
    await loadKelas();
    loadSiswaList(true);
});
document.getElementById('filterSekolah').addEventListener('change', async () => { await loadKelas(); loadSiswaList(true); });

document.getElementById('btnKelasClear').addEventListener('click', () => {
    document.querySelectorAll('#filterKelasList input').forEach(i => { i.checked = false; });
});
document.getElementById('btnKelasApply').addEventListener('click', () => {
    selectedKelas = readChecked('filterKelasList');
    document.getElementById('filterKelasLabel').textContent = multiLabel(selectedKelas, 'Semua kelas');
    closeAllDropdowns();
    loadSiswaList(true);
});

document.getElementById('btnSearch').addEventListener('click', () => loadSiswaList(true));
document.getElementById('filterSearch').addEventListener('keydown', e => { if (e.key === 'Enter') loadSiswaList(true); });
document.getElementById('filterTglDari').addEventListener('change', updatePeriodLabel);
document.getElementById('filterTglSampai').addEventListener('change', updatePeriodLabel);
document.getElementById('pageSizeSelect').addEventListener('change', e => { pageSize = parseInt(e.target.value, 10) || 10; loadSiswaList(true); });
document.getElementById('btnPrev').addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadSiswaList(false); } });
document.getElementById('btnNext').addEventListener('click', () => { if (hasMore) { currentPage++; loadSiswaList(false); } });
document.getElementById('btnReset').addEventListener('click', async () => {
    document.getElementById('filterSearch').value = '';
    document.getElementById('filterTglDari').value = defaultDari();
    document.getElementById('filterTglSampai').value = ymd(new Date());
    selectedSekolahMulti = [];
    document.getElementById('filterSekolahLabel').textContent = 'Semua sekolah/unit';
    renderCheckboxList('filterSekolahList', sekolahOptions, [], 'Tidak ada sekolah');
    if (!canMultiSekolah) {
        document.getElementById('filterSekolah').value = sekolahOptions.length === 1 ? sekolahOptions[0].value : '';
    }
    await loadKelas();
    loadSiswaList(true);
});

document.getElementById('filterToggle').addEventListener('click', () => {
    const body = document.getElementById('filterBody');
    const hidden = body.style.display === 'none';
    body.style.display = hidden ? 'block' : 'none';
    document.getElementById('filterToggleText').textContent = hidden ? 'Sembunyikan' : 'Tampilkan';
    document.getElementById('filterToggleIcon').className = hidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
});

const toggleBtn = document.getElementById('drawerToggle');
const closeBtn = document.getElementById('drawerClose');
const backdrop = document.getElementById('drawerBackdrop');
const drawer = document.getElementById('drawer');
const app = document.getElementById('app');
const DRAWER_KEY = 'kepsek_drawer_open';
function isDesktopDrawer() { return window.matchMedia('(min-width: 960px)').matches; }
function setDrawerOpen(open) {
    drawer.classList.toggle('open', open);
    backdrop.classList.toggle('open', open && !isDesktopDrawer());
    app.classList.toggle('drawer-pinned', open && isDesktopDrawer());
    drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    try { localStorage.setItem(DRAWER_KEY, open ? '1' : '0'); } catch (e) {}
}
toggleBtn.addEventListener('click', () => setDrawerOpen(!drawer.classList.contains('open')));
closeBtn.addEventListener('click', () => setDrawerOpen(false));
backdrop.addEventListener('click', () => setDrawerOpen(false));
(function initDrawer() {
    let saved = null;
    try { saved = localStorage.getItem(DRAWER_KEY); } catch (e) {}
    if (saved === null) saved = isDesktopDrawer() ? '1' : '0';
    setDrawerOpen(saved === '1');
    window.addEventListener('resize', () => setDrawerOpen(drawer.classList.contains('open')));
})();

(async function init() {
    document.getElementById('filterTglDari').value = defaultDari();
    document.getElementById('filterTglSampai').value = ymd(new Date());
    updatePeriodLabel();
    await loadFilterOptions();
    loadSiswaList(true);
})();
</script>
@if (session('error'))
<script>Swal.fire({ icon: 'warning', title: 'Perhatian', text: @json(session('error')), confirmButtonColor: '#2563eb' });</script>
@endif
</body>
</html>
