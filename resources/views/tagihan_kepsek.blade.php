<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ session('user.humas') == 1 ? 'Tagihan Daftar Ulang' : 'Tagihan' }} - Monitoring Kepsek</title>
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
        .btn-primary { background: var(--accent); }
        .btn-excel { background: #0f766e; }
        .btn-pdf { background: #b91c1c; }
        .btn-reset { background: #fff; color: var(--text); border: 1px solid var(--border); }
        .btn-row { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; justify-content: flex-end; }
        .table-card { background: var(--card); border-radius: 14px; box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; }
        .table-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 16px 18px; border-bottom: 1px solid var(--border); }
        .table-toolbar h3 { margin: 0; font-size: .96rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .row-count { font-size: .8rem; color: var(--muted); font-weight: 600; }
        .table-scroll { overflow-x: auto; position: relative; }
        table { width: 100%; border-collapse: collapse; min-width: 1080px; }
        thead.main-thead { background: var(--head-bg); }
        thead.main-thead th { color: var(--head-text); }
        th { padding: 11px 14px; text-align: left; font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; border-bottom: 1px solid var(--border); white-space: nowrap; }
        td { padding: 12px 14px; font-size: .86rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tbody tr.data-row:hover { background: #f8fafc; }
        tbody tr.data-row.expanded { background: #eff6ff; }
        .col-no { width: 44px; color: var(--muted); font-weight: 600; }
        .col-money { text-align: right; white-space: nowrap; font-weight: 600; }
        .col-action { text-align: right; white-space: nowrap; }
        .nama-cell .nm { font-weight: 600; }
        .nama-cell .sk { display: block; font-size: .72rem; color: var(--muted); text-transform: uppercase; letter-spacing: .03em; margin-top: 1px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: .72rem; font-weight: 700; }
        .badge-lunas { background: var(--green-bg); color: var(--green-text); }
        .badge-belum { background: var(--red-bg); color: var(--red-text); }
        .badge-saldo { background: var(--blue-bg); color: var(--blue-text); }
        .detail-link { border: none; background: none; color: var(--accent); font-weight: 600; font-size: .84rem; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px; }
        .detail-link i { transition: transform .15s; }
        .detail-link.open i { transform: rotate(90deg); }
        tr.detail-row > td { padding: 18px 20px 22px 20px; background: #f8fafc; border-bottom: 2px solid var(--border); }
        .siswa-panel-head { display: flex; flex-direction: column; gap: 12px; margin-bottom: 14px; }
        .siswa-panel-title-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        .siswa-panel-head .t { font-size: .95rem; font-weight: 700; color: #0f172a; }
        .siswa-panel-period { font-size: .78rem; font-weight: 600; color: var(--accent); background: var(--blue-bg); padding: 4px 12px; border-radius: 999px; white-space: nowrap; }
        .mini-total { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .mini-total .info-box { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 12px 14px; min-width: 0; }
        .mini-total .info-box .ib-label { display: block; font-size: .72rem; font-weight: 600; color: var(--muted); margin-bottom: 4px; }
        .mini-total .info-box .ib-value { display: block; font-size: .95rem; font-weight: 800; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mini-total .info-box.ib-saldo .ib-value { color: var(--blue-text); }
        .mini-total .info-box.ib-bayar .ib-value { color: #166534; }
        .mini-total .info-box.ib-sisa .ib-value { color: #991b1b; }
        @media (max-width: 900px) {
            .mini-total { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .detail-tables { display: flex; align-items: flex-start; gap: 16px; }
        .detail-col { flex: 1; min-width: 0; }
        .detail-col-tagihan { flex: 1.3; }
        .detail-col-uangmasuk { flex: 1; }
        .detail-col-title { font-size: .78rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 8px; }
        .bills-table-wrap { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; overflow-x: auto; }
        .bills-table { width: 100%; border-collapse: collapse; min-width: 600px; }
        .bills-table th { background: #f8fafc; padding: 10px 14px; font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: var(--muted); text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; }
        .bills-table td { padding: 11px 14px; font-size: .82rem; border-bottom: 1px solid #f1f5f9; }
        .bills-table tbody tr:last-child td { border-bottom: none; }
        .uang-masuk-wrap { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; overflow-x: auto; }
        .uang-masuk-table { width: 100%; border-collapse: collapse; min-width: 260px; }
        .uang-masuk-table th { background: #f8fafc; padding: 10px 14px; font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: var(--muted); text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; }
        .uang-masuk-table td { padding: 11px 14px; font-size: .82rem; border-bottom: 1px solid #f1f5f9; }
        .uang-masuk-table tbody tr:last-child td { border-bottom: none; }
        .acc-row > td { padding: 14px 18px 18px 40px; background: #f1f5f9; }
        .acc-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
        .acc-table th { background: #f8fafc; padding: 9px 12px; font-size: .68rem; text-transform: uppercase; color: var(--muted); text-align: left; border-bottom: 1px solid var(--border); }
        .acc-table td { padding: 10px 12px; font-size: .8rem; border-bottom: 1px solid #f1f5f9; }
        .acc-table tbody tr:last-child td { border-bottom: none; }
        .mini-loading { padding: 22px; text-align: center; color: var(--muted); font-size: .82rem; }
        .spinner { width: 30px; height: 30px; border: 3px solid rgba(37,99,235,.15); border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; margin: 0 auto 10px; }
        .spinner-sm { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(37,99,235,.15); border-top-color: var(--accent); border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .empty, .loader { text-align: center; padding: 48px 20px; color: var(--muted); }
        .loader-text { font-size: .9rem; margin-top: 8px; }
        .loader-sub { font-size: .78rem; color: #94a3b8; margin-top: 4px; }
        .pagination { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 12px 16px; border-top: 1px solid var(--border); }
        .pagination-info { font-size: .84rem; color: var(--muted); font-weight: 500; }
        .pagination-btns { display: flex; gap: 8px; }
        .page-btn { border: 1px solid var(--border); background: #fff; color: var(--text); border-radius: 8px; padding: 8px 14px; font-size: .84rem; font-weight: 600; cursor: pointer; font-family: inherit; }
        .page-btn:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
        .page-btn:disabled { opacity: .4; cursor: not-allowed; }
        .summary-bar { margin-top: 16px; background: var(--card); border-radius: 14px; box-shadow: var(--shadow); border: 1px solid var(--border); padding: 18px 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .summary-item .label { font-size: .74rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
        .summary-item .value { font-size: 1.15rem; font-weight: 800; }
        .summary-item.terbayar .value { color: #166534; }
        .summary-item.piutang .value { color: #991b1b; }
        .summary-item.total .value { color: var(--accent); }
        .loading-row td { padding: 30px; text-align: center; color: var(--muted); }
        .field-tagihan { position: relative; }
        .tagihan-select-btn { width:100%; display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border:1px solid var(--border); border-radius:10px; background:#fff; font-size:.88rem; font-family:inherit; color:var(--text); cursor:pointer; }
        .tagihan-select-btn:disabled { background:#f1f5f9; color:#64748b; cursor:not-allowed; }
        .tagihan-select-btn i { color: var(--muted); font-size:.78rem; transition: transform .15s; }
        .tagihan-select-btn.open i { transform: rotate(180deg); }
        .tagihan-dropdown { position:absolute; top:calc(100% + 6px); left:0; right:0; background:#fff; border:1px solid var(--border); border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,.14); z-index:20; display:none; overflow:hidden; }
        .tagihan-dropdown.open { display:block; }
        .tagihan-dropdown-inner { max-height:220px; overflow-y:auto; padding:8px; }
        .tagihan-option { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:8px; font-size:.85rem; cursor:pointer; }
        .tagihan-option:hover { background:#f1f5f9; }
        .tagihan-option input { accent-color: var(--accent); width:16px; height:16px; }
        .tagihan-dropdown-actions { display:flex; justify-content:space-between; gap:8px; padding:10px 12px; border-top:1px solid var(--border); background:#f8fafc; }
        .tagihan-dropdown-actions button { border:none; border-radius:8px; padding:7px 12px; font-size:.78rem; font-weight:600; cursor:pointer; font-family:inherit; }
        .tagihan-clear-btn { background:#fff; border:1px solid var(--border) !important; color:var(--text); }
        .tagihan-apply-btn { background: var(--accent); color:#fff; }
        .tagihan-empty-note { padding:14px; text-align:center; font-size:.8rem; color:var(--muted); }
        @media (max-width: 900px) { .filter-body { grid-template-columns: 1fr 1fr; } .summary-bar { grid-template-columns: 1fr; } .detail-tables { flex-direction: column; } }
        @media (max-width: 600px) { .filter-body { grid-template-columns: 1fr; } .main { padding: 14px; } tr.detail-row > td { padding: 12px; } }
        @media (min-width: 960px) {
            .app.drawer-pinned { margin-left: 280px; }
            .drawer-backdrop { display: none; }
            .main { padding: 28px 32px; max-width: 1320px; }
        }
    </style>
</head>
<body>
<div class="app" id="app">
    <div class="main">
        <header class="header">
            <button class="burger" id="drawerToggle" type="button" aria-label="Buka/tutup menu" title="Menu"><span></span><span></span><span></span></button>
            <h1 class="title">{{ session('user.humas') == 1 ? 'Tagihan Daftar Ulang' : 'Tagihan' }}</h1>
        </header>

        <div class="filter-card">
            <div class="filter-top">
                <div>
                    <h2>Filter</h2>
                    <p>Sesuaikan sekolah, kelas, dan tagihan.</p>
                </div>
                <button type="button" class="filter-toggle" id="filterToggle">
                    <span id="filterToggleText">Sembunyikan</span>
                    <i class="fas fa-chevron-up" id="filterToggleIcon"></i>
                </button>
            </div>

            <div id="filterBody">
                <div class="filter-body">
                    <div class="field">
                        <label for="filterSekolah">Sekolah / Unit</label>
                        <select id="filterSekolah"><option value="">Memuat...</option></select>
                    </div>
                    <div class="field">
                        <label for="filterBta">Tahun Ajaran</label>
                        <select id="filterBta"><option value="">Memuat...</option></select>
                    </div>
                    <div class="field">
                        <label for="filterKelas">Kelas / Program</label>
                        <select id="filterKelas" disabled><option value="">Pilih sekolah dulu</option></select>
                    </div>
                    <div class="field">
                        <label for="filterStatus">Status</label>
                        <select id="filterStatus">
                            <option value="">Semua status</option>
                            <option value="1">Lunas</option>
                            <option value="0">Belum Lunas</option>
                        </select>
                    </div>
                    <div class="field field-tagihan">
                        <label for="filterTagihanBtn">Nama Tagihan</label>
                        <button type="button" id="filterTagihanBtn" class="tagihan-select-btn" {{ ($restrictedTagihan ?? false) ? 'disabled' : '' }}>
                            <span id="filterTagihanLabel">{{ ($restrictedTagihan ?? false) ? 'BIAYA Daftar Ulang Ajaran Baru' : 'Semua tagihan' }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="tagihan-dropdown" id="filterTagihanDropdown">
                            <div class="tagihan-dropdown-inner" id="filterTagihanList">
                                @if($restrictedTagihan ?? false)
                                    <label class="tagihan-option">
                                        <input type="checkbox" value="BIAYA Daftar Ulang Ajaran Baru" checked disabled>
                                        <span>BIAYA Daftar Ulang Ajaran Baru</span>
                                    </label>
                                @else
                                    <div class="tagihan-empty-note">Memuat...</div>
                                @endif
                            </div>
                            <div class="tagihan-dropdown-actions">
                                @unless($restrictedTagihan ?? false)
                                    <button type="button" id="btnTagihanClear" class="tagihan-clear-btn">Bersihkan</button>
                                @endunless
                                <button type="button" id="btnTagihanApply" class="tagihan-apply-btn">Terapkan</button>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label for="filterSearch">Cari nama</label>
                        <div class="search-group">
                            <input type="text" id="filterSearch" placeholder="Nama siswa..." onkeydown="if(event.key==='Enter') loadSiswaList(true)">
                            <button type="button" id="btnSearch"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn btn-reset" id="btnReset">Reset</button>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3><i class="fas fa-file-invoice"></i> Daftar Tagihan per Siswa</h3>
                <div class="btn-row" style="margin:0;">
                    <a href="#" class="btn btn-excel" id="btnExportExcel"><i class="fas fa-file-excel"></i> Export Excel</a>
                    <a href="#" class="btn btn-pdf" id="btnExportPdf"><i class="fas fa-file-pdf"></i> Export PDF</a>
                </div>
            </div>

            <div class="table-scroll">
                <table id="siswaTable">
                    <thead class="main-thead">
                        <tr>
                            <th class="col-no">No</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Sekolah / Unit</th>
                            <th class="col-money">Total Tagihan</th>
                            <th class="col-money">Total Terbayar</th>
                            <th class="col-money">Sisa Tagihan</th>
                            <th>Status</th>
                            <th class="col-action">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="siswaBody"></tbody>
                </table>
                <div class="empty" id="emptyState" style="display:none;">
                    <i class="fas fa-inbox" style="font-size:2.2rem;opacity:.35;display:block;margin-bottom:10px;"></i>
                    Tidak ada data siswa
                </div>
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

        <div class="summary-bar" id="summaryBar">
            <div class="summary-item total">
                <div class="label">Total Tagihan</div>
                <div class="value" id="sumTotal">
                    <span class="spinner-sm"></span> Menghitung...
                </div>
            </div>
            <div class="summary-item terbayar">
                <div class="label">Total Terbayar</div>
                <div class="value" id="sumTerbayar">
                    <span class="spinner-sm"></span> Menghitung...
                </div>
            </div>
            <div class="summary-item piutang">
                <div class="label">Total Piutang</div>
                <div class="value" id="sumPiutang">
                    <span class="spinner-sm"></span> Menghitung...
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
                    @if(session('user.humas') == 1)
                        Humas
                    @elseif((int) session('user.is_superadmin', 0) === 1 || strtolower(trim((string) session('user.kelompok', ''))) === 'superadmin')
                        Superadmin
                    @else
                        Kepala Sekolah
                    @endif
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
        @if(session('user.humas') == 1)
            <li class="drawer-item"><a href="{{ route('kepsek.tagihan') }}" class="drawer-link active"><span class="icon"><i class="fas fa-file-invoice-dollar"></i></span><span>Tagihan Daftar Ulang</span></a></li>
        @else
            <li class="drawer-item"><a href="{{ route('dashboard.monitoring-kepsek') }}" class="drawer-link"><span class="icon"><i class="fas fa-house"></i></span><span>Dashboard</span></a></li>
            <li class="drawer-item"><a href="{{ route('kepsek.tagihan-periode') }}" class="drawer-link"><span class="icon"><i class="fas fa-file-invoice-dollar"></i></span><span>Tagihan Periode</span></a></li>
            @if((int) session('user.is_superadmin', 0) === 1 || strtolower(trim((string) session('user.kelompok', ''))) === 'superadmin')
            <li class="drawer-item"><a href="{{ route('kepsek.kelola-user') }}" class="drawer-link"><span class="icon"><i class="fas fa-users-gear"></i></span><span>Kelola User</span></a></li>
            <li class="drawer-item"><a href="{{ route('kepsek.import-penagihan') }}" class="drawer-link"><span class="icon"><i class="fas fa-file-import"></i></span><span>Import Penagihan</span></a></li>
            @endif
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
        siswaList: @json(route('kepsek.tagihan.siswa')),
        siswaTagihan: @json(route('kepsek.tagihan.siswa-detail')),
        uangMasuk: @json(route('kepsek.tagihan.siswa-uang-masuk')),
        saldo: @json(route('kepsek.tagihan.siswa-saldo')),
        detail: @json(route('kepsek.tagihan.detail')),
        summary: @json(route('kepsek.tagihan.summary')),
        filters: @json(route('kepsek.tagihan.filters')),
        exportExcel: @json(route('kepsek.tagihan.export-excel')),
        exportPdf: @json(route('kepsek.tagihan.export-pdf')),
    };
    const csrf = @json(csrf_token());
    const isDaftarUlangOnly = @json(session('user.humas') == 1);
    const restrictedSekolah = @json($restrictedSekolah ?? null);

    let currentPage = 1;
    let hasMore = false;
    let selectedTagihan = isDaftarUlangOnly ? ['BIAYA Daftar Ulang Ajaran Baru'] : [];
    const siswaBillsCache = new Map();
    const siswaUangMasukCache = new Map();
    const accDetailCache = new Map();
    let siswaRowsCurrent = [];

    function formatRupiah(val) {
        const n = Number(val) || 0;
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
    }

    function escapeHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function getFilterParams() {
        return {
            sekolah: document.getElementById('filterSekolah').value,
            bta: document.getElementById('filterBta').value,
            kelas: document.getElementById('filterKelas').value,
            paidst: document.getElementById('filterStatus').value,
            search: document.getElementById('filterSearch').value.trim(),
            tagihan: selectedTagihan,
        };
    }

    function getDataParams() {
        return {
            ...getFilterParams(),
            limit: 10,
            page: currentPage,
        };
    }

    function buildQuery(params) {
        const q = new URLSearchParams();
        Object.entries(params).forEach(([k, v]) => {
            if (Array.isArray(v)) {
                v.forEach(item => { if (item !== '' && item != null) q.append(k + '[]', item); });
            } else if (v !== '' && v != null) {
                q.set(k, v);
            }
        });
        return q.toString();
    }

    function updateExportLinks() {
        const q = buildQuery(getFilterParams());
        document.getElementById('btnExportExcel').href = routes.exportExcel + (q ? '?' + q : '');
        document.getElementById('btnExportPdf').href = routes.exportPdf + (q ? '?' + q : '');
    }

    function closeAllExpanded() {
        document.querySelectorAll('.acc-row').forEach(r => r.remove());
        document.querySelectorAll('.detail-row').forEach(r => r.remove());
        document.querySelectorAll('.detail-link.open').forEach(b => b.classList.remove('open'));
        document.querySelectorAll('.data-row.expanded').forEach(r => r.classList.remove('expanded'));
    }

    function renderAccPanel(details) {
        const items = details || [];
        if (!items.length) {
            return '<div class="mini-loading">Tidak ada rincian</div>';
        }
        const rows = items.map(d => `
            <tr>
                <td>${escapeHtml(d.kode_akun)}</td>
                <td>${escapeHtml(d.nama_akun)}</td>
                <td>${escapeHtml(d.bta)}</td>
                <td style="text-align:right;">${formatRupiah(d.jumlah)}</td>
            </tr>
        `).join('');
        return `
            <table class="acc-table">
                <thead>
                    <tr><th>Kode Akun</th><th>Nama Akun</th><th>BTA</th><th style="text-align:right;">Jumlah</th></tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        `;
    }

    async function toggleAccDetail(btn, billRow, custid, kode) {
        const cacheKey = custid + '|' + kode;
        const existing = billRow.nextElementSibling;
        if (existing && existing.classList.contains('acc-row')) {
            existing.remove();
            btn.classList.remove('open');
            return;
        }
        document.querySelectorAll('.acc-row').forEach(r => r.remove());
        document.querySelectorAll('.bill-toggle.open').forEach(b => b.classList.remove('open'));
        btn.classList.add('open');

        const tr = document.createElement('tr');
        tr.className = 'acc-row';

        if (accDetailCache.has(cacheKey)) {
            tr.innerHTML = `<td colspan="6">${renderAccPanel(accDetailCache.get(cacheKey))}</td>`;
            billRow.after(tr);
            return;
        }

        tr.innerHTML = `<td colspan="6"><div class="mini-loading"><div class="spinner-sm" style="display:inline-block;margin-right:8px;"></div>Memuat rincian...</div></td>`;
        billRow.after(tr);

        try {
            const res = await fetch(routes.detail, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ custid, kode_tagihan: kode }),
            });
            const json = await res.json();
            const details = json.data?.detail || [];
            if (!tr.isConnected) return;
            if (!res.ok || !json.success || !details.length) {
                tr.innerHTML = `<td colspan="6"><div class="mini-loading" style="color:#991b1b;">${escapeHtml(json.message || 'Rincian tagihan tidak ditemukan')}</div></td>`;
                return;
            }
            accDetailCache.set(cacheKey, details);
            tr.innerHTML = `<td colspan="6">${renderAccPanel(details)}</td>`;
        } catch (e) {
            console.error('toggleAccDetail error', e);
            if (!tr.isConnected) return;
            tr.innerHTML = `<td colspan="6"><div class="mini-loading" style="color:#991b1b;">Gagal memuat rincian.</div></td>`;
        }
    }

    function renderBillsTable(custid, bills) {
        if (!bills.length) {
            return '<div class="mini-loading">Belum ada tagihan</div>';
        }
        const rows = bills.map(b => {
            const paid = Number(b.status_bayar) === 1;
            const tglBayar = paid && b.tanggal_bayar
                ? escapeHtml(b.tanggal_bayar)
                : '<span style="color:#94a3b8;">—</span>';
            return `
                <tr class="bill-row" data-custid="${escapeHtml(custid)}" data-kode="${escapeHtml(b.kode_tagihan)}">
                    <td>${escapeHtml(b.bta || '-')}</td>
                    <td>${escapeHtml(b.nama_tagihan)}</td>
                    <td style="text-align:right;">${formatRupiah(b.jumlah)}</td>
                    <td><span class="badge ${paid ? 'badge-lunas' : 'badge-belum'}">${paid ? 'Lunas' : 'Belum Lunas'}</span></td>
                    <td>${tglBayar}</td>
                    <td style="text-align:right;"><button type="button" class="detail-link bill-toggle" data-custid="${escapeHtml(custid)}" data-kode="${escapeHtml(b.kode_tagihan)}"><i class="fas fa-chevron-right"></i> Detail</button></td>
                </tr>
            `;
        }).join('');
        return `
            <div class="bills-table-wrap">
                <table class="bills-table">
                    <thead>
                        <tr>
                            <th>Tahun Ajaran</th>
                            <th>Nama Tagihan</th>
                            <th style="text-align:right;">Jumlah</th>
                            <th>Status</th>
                            <th>Tgl Bayar</th>
                            <th style="text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    function renderUangMasukTable(rows) {
        if (!rows.length) {
            return '<div class="mini-loading">Belum ada uang masuk</div>';
        }
        const body = rows.map(r => `
            <tr>
                <td>${escapeHtml(r.tanggal ?? '-')}</td>
                <td style="text-align:right;">${formatRupiah(r.nominal)}</td>
            </tr>
        `).join('');
        return `
            <div class="uang-masuk-wrap">
                <table class="uang-masuk-table">
                    <thead>
                        <tr><th>Tanggal</th><th style="text-align:right;">Nominal</th></tr>
                    </thead>
                    <tbody>${body}</tbody>
                </table>
            </div>
        `;
    }

    async function fetchSiswaBills(custid, bta) {
        const res = await fetch(routes.siswaTagihan + '?' + buildQuery({ custid, bta }), { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            throw new Error(json.message || 'Gagal memuat tagihan');
        }
        return json.data || [];
    }

    async function fetchSiswaUangMasuk(custid) {
        const res = await fetch(routes.uangMasuk + '?' + buildQuery({ custid }), { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            throw new Error(json.message || 'Gagal memuat Data Pembayaran Santri');
        }
        return json.data || [];
    }

    async function fetchSiswaSaldo(custid) {
        const res = await fetch(routes.saldo + '?' + buildQuery({ custid }), { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            throw new Error(json.message || 'Gagal memuat saldo');
        }
        return json.data || { saldo: 0, total_debet: 0, total_kredit: 0 };
    }

    async function toggleSiswaDetail(btn, dataRow, siswa) {
        const custid = siswa.custid;
        const bta = document.getElementById('filterBta').value;
        const cacheKey = custid + '|' + bta;
        const existing = dataRow.nextElementSibling;

        if (existing && existing.classList.contains('detail-row')) {
            existing.remove();
            btn.classList.remove('open');
            dataRow.classList.remove('expanded');
            return;
        }

        closeAllExpanded();
        btn.classList.add('open');
        dataRow.classList.add('expanded');

        const tr = document.createElement('tr');
        tr.className = 'detail-row';

        let saldoData = { saldo: 0, total_debet: 0, total_kredit: 0 };
        try {
            saldoData = await fetchSiswaSaldo(custid);
        } catch (e) {
            console.error('fetchSiswaSaldo error', e);
        }

        if (!dataRow.classList.contains('expanded')) return;

        const headHtml = `
            <div class="siswa-panel-head">
                <div class="siswa-panel-title-row">
                    <div class="t">Detail Keuangan — ${escapeHtml(siswa.nama)}</div>
                </div>
                <div class="mini-total">
                    <div class="info-box">
                        <span class="ib-label">Total Tagihan</span>
                        <span class="ib-value">${formatRupiah(siswa.total_tagihan)}</span>
                    </div>
                    <div class="info-box ib-saldo">
                        <span class="ib-label">Saldo Spp</span>
                        <span class="ib-value">${formatRupiah(saldoData.saldo || 0)}</span>
                    </div>
                    <div class="info-box ib-bayar">
                        <span class="ib-label">Total Bayar</span>
                        <span class="ib-value">${formatRupiah(siswa.total_terbayar)}</span>
                    </div>
                    <div class="info-box ib-sisa">
                        <span class="ib-label">Sisa Tagihan</span>
                        <span class="ib-value">${formatRupiah(siswa.sisa_tagihan)}</span>
                    </div>
                </div>
            </div>
        `;

        const bodyHtml = (billsHtml, uangMasukHtml) => `
            <div class="detail-tables">
                <div class="detail-col detail-col-tagihan">
                    <div class="detail-col-title">Tagihan</div>
                    ${billsHtml}
                </div>
                <div class="detail-col detail-col-uangmasuk">
                    <div class="detail-col-title">Data Pembayaran Santri</div>
                    ${uangMasukHtml}
                </div>
            </div>
        `;

        const loadingBills = '<div class="mini-loading"><div class="spinner"></div>Memuat tagihan siswa...</div>';
        const loadingUangMasuk = '<div class="mini-loading"><div class="spinner"></div>Memuat Data Pembayaran Santri...</div>';

        tr.innerHTML = `<td colspan="9">${headHtml}${bodyHtml(loadingBills, loadingUangMasuk)}</td>`;
        dataRow.after(tr);

        const [billsResult, uangMasukResult] = await Promise.allSettled([
            siswaBillsCache.has(cacheKey) ? Promise.resolve(siswaBillsCache.get(cacheKey)) : fetchSiswaBills(custid, bta),
            siswaUangMasukCache.has(custid) ? Promise.resolve(siswaUangMasukCache.get(custid)) : fetchSiswaUangMasuk(custid),
        ]);

        if (!tr.isConnected) return;

        let billsHtml;
        if (billsResult.status === 'fulfilled') {
            siswaBillsCache.set(cacheKey, billsResult.value);
            billsHtml = renderBillsTable(custid, billsResult.value);
        } else {
            billsHtml = `<div class="mini-loading" style="color:#991b1b;">${escapeHtml(billsResult.reason?.message || 'Gagal memuat tagihan siswa.')}</div>`;
        }

        let uangMasukHtml;
        if (uangMasukResult.status === 'fulfilled') {
            siswaUangMasukCache.set(custid, uangMasukResult.value);
            uangMasukHtml = renderUangMasukTable(uangMasukResult.value);
        } else {
            uangMasukHtml = `<div class="mini-loading" style="color:#991b1b;">${escapeHtml(uangMasukResult.reason?.message || 'Gagal memuat Data Pembayaran Santri.')}</div>`;
        }

        tr.innerHTML = `<td colspan="9">${headHtml}${bodyHtml(billsHtml, uangMasukHtml)}</td>`;
    }

    async function loadSummary() {
        try {
            const q = buildQuery(getFilterParams());
            const res = await fetch(routes.summary + (q ? '?' + q : ''), {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();

            if (json.success) {
                document.getElementById('sumTerbayar').textContent = formatRupiah(json.total_terbayar || 0);
                document.getElementById('sumPiutang').textContent = formatRupiah(json.total_piutang || 0);
                document.getElementById('sumTotal').textContent = formatRupiah(json.total_tagihan || 0);
            }
        } catch (e) {
            console.error('loadSummary error', e);
            document.getElementById('sumTerbayar').textContent = 'Gagal memuat';
            document.getElementById('sumPiutang').textContent = 'Gagal memuat';
            document.getElementById('sumTotal').textContent = 'Gagal memuat';
        }
    }

    function updateTagihanLabel() {
        const label = document.getElementById('filterTagihanLabel');
        if (!label) return;
        if (!selectedTagihan.length) {
            label.textContent = 'Semua tagihan';
        } else if (selectedTagihan.length === 1) {
            label.textContent = selectedTagihan[0];
        } else {
            label.textContent = selectedTagihan.length + ' tagihan dipilih';
        }
    }

    function toggleTagihanDropdown(forceState) {
        const btn = document.getElementById('filterTagihanBtn');
        const dd = document.getElementById('filterTagihanDropdown');
        if (!btn || !dd) return;
        if (isDaftarUlangOnly) {
            dd.classList.remove('open');
            btn.classList.remove('open');
            return;
        }
        const open = forceState !== undefined ? forceState : !dd.classList.contains('open');
        dd.classList.toggle('open', open);
        btn.classList.toggle('open', open);
    }

    async function updateKelasSelect() {
        const sekolah = document.getElementById('filterSekolah').value;
        const kelasSel = document.getElementById('filterKelas');

        if (sekolah === '') {
            kelasSel.disabled = true;
            kelasSel.innerHTML = '<option value="">Pilih sekolah dulu</option>';
            kelasSel.value = '';
            return;
        }

        try {
            const params = { sekolah, limit: 1000 };
            const res = await fetch(routes.siswaList + '?' + buildQuery(params), {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();

            const currentKelas = kelasSel.value;
            const kelasSet = new Set();

            if (json.success) {
                (json.data || []).forEach(s => {
                    if (s.kelas) kelasSet.add(s.kelas);
                });
            }

            kelasSel.disabled = false;
            kelasSel.innerHTML = '<option value="">Semua kelas</option>';

            const sortedKelas = Array.from(kelasSet).sort();
            sortedKelas.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                kelasSel.appendChild(opt);
            });

            if (currentKelas && kelasSet.has(currentKelas)) {
                kelasSel.value = currentKelas;
            }
        } catch (e) {
            console.error('updateKelasSelect error', e);
            kelasSel.disabled = false;
            kelasSel.innerHTML = '<option value="">Semua kelas</option>';
        }
    }

    async function loadFilterOptions() {
        try {
            const res = await fetch(routes.filters, {
                headers: { 'Accept': 'application/json' },
                cache: 'no-cache'
            });
            const json = await res.json();

            if (!json.success) {
                console.error('loadFilterOptions gagal', json);
                return;
            }

            const sekolahSel = document.getElementById('filterSekolah');
            const currentSekolah = sekolahSel.value;
            const sekolahList = json.sekolah || [];
            if (restrictedSekolah) {
                sekolahSel.innerHTML = '';
                const opt = document.createElement('option');
                opt.value = restrictedSekolah;
                opt.textContent = restrictedSekolah;
                sekolahSel.appendChild(opt);
                sekolahSel.value = restrictedSekolah;
                sekolahSel.disabled = true;
            } else {
                sekolahSel.disabled = false;
                sekolahSel.innerHTML = '<option value="">Semua sekolah/unit</option>';
                sekolahList.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v;
                    opt.textContent = v;
                    sekolahSel.appendChild(opt);
                });
                if (currentSekolah && sekolahList.includes(currentSekolah)) {
                    sekolahSel.value = currentSekolah;
                }
            }

            const btaSel = document.getElementById('filterBta');
            const currentBta = btaSel.value;
            btaSel.innerHTML = '<option value="">Semua tahun ajaran</option>';
            (json.bta || []).forEach(v => {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                btaSel.appendChild(opt);
            });
            if (currentBta && json.bta.includes(currentBta)) {
                btaSel.value = currentBta;
            }

            const kelasSel = document.getElementById('filterKelas');
            kelasSel.disabled = true;
            kelasSel.innerHTML = '<option value="">Pilih sekolah dulu</option>';
            kelasSel.value = '';

            if (sekolahSel.value !== '') {
                await updateKelasSelect();
            }

            const tagihanList = document.getElementById('filterTagihanList');
            if (tagihanList) {
                if (isDaftarUlangOnly) {
                    selectedTagihan = ['BIAYA Daftar Ulang Ajaran Baru'];
                    tagihanList.innerHTML = `
                        <label class="tagihan-option">
                            <input type="checkbox" value="BIAYA Daftar Ulang Ajaran Baru" checked disabled>
                            <span>BIAYA Daftar Ulang Ajaran Baru</span>
                        </label>
                    `;
                } else {
                    const tagihanOptions = json.tagihan || [];
                    if (!tagihanOptions.length) {
                        tagihanList.innerHTML = '<div class="tagihan-empty-note">Tidak ada data tagihan</div>';
                    } else {
                        tagihanList.innerHTML = tagihanOptions.map(t => `
                            <label class="tagihan-option">
                                <input type="checkbox" value="${escapeHtml(t)}" ${selectedTagihan.includes(t) ? 'checked' : ''}>
                                <span>${escapeHtml(t)}</span>
                            </label>
                        `).join('');
                    }
                }
                updateTagihanLabel();
            }
        } catch (e) {
            console.error('loadFilterOptions error', e);
        }
    }

    function bindSiswaTableDelegation() {
        const tbody = document.getElementById('siswaBody');
        tbody.addEventListener('click', function (e) {
            const btn = e.target.closest('.detail-link');
            if (!btn || !tbody.contains(btn)) return;
            const row = btn.closest('.data-row');
            if (!row) return;
            const custid = btn.dataset.custid;
            const siswa = siswaRowsCurrent.find(r => String(r.custid) === String(custid)) || {};
            toggleSiswaDetail(btn, row, siswa);
        });
    }

    function bindBillToggleDelegation() {
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.bill-toggle');
            if (!btn) return;
            const row = btn.closest('.bill-row');
            if (!row) return;
            const custid = row.dataset.custid || btn.dataset.custid;
            const kode = row.dataset.kode || btn.dataset.kode;
            if (custid && kode) {
                toggleAccDetail(btn, row, custid, kode);
            }
        });
    }

    async function loadSiswaList(resetPage = false) {
        if (resetPage) {
            currentPage = 1;
            closeAllExpanded();
        }

        const table = document.getElementById('siswaTable');
        const empty = document.getElementById('emptyState');
        const tbody = document.getElementById('siswaBody');
        const paginationBar = document.getElementById('paginationBar');

        table.style.display = 'table';
        empty.style.display = 'none';
        paginationBar.style.display = 'none';
        tbody.innerHTML = `
            <tr class="loading-row">
                <td colspan="9">
                    <div class="spinner" style="margin:0 auto 10px;"></div>
                    <div>Memuat data siswa...</div>
                </td>
            </tr>
        `;
        updateExportLinks();

        try {
            const q = buildQuery(getDataParams());
            const res = await fetch(routes.siswaList + '?' + q, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();

            if (!json.success) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: json.message || 'Gagal memuat data', confirmButtonColor: '#2563eb' });
                empty.style.display = 'block';
                tbody.innerHTML = '';
                return;
            }

            const rows = json.data || [];
            siswaRowsCurrent = rows;

            if (!rows.length) {
                table.style.display = 'none';
                empty.style.display = 'block';
                tbody.innerHTML = '';
                paginationBar.style.display = 'none';
                return;
            }

            const startNo = ((json.pagination?.page || 1) - 1) * (json.pagination?.limit || 10) + 1;

            tbody.innerHTML = '';
            rows.forEach((row, i) => {
                const custid = row.custid;
                const paid = Number(row.status) === 1;
                const tr = document.createElement('tr');
                tr.className = 'data-row';
                tr.dataset.custid = custid;
                tr.innerHTML = `
                    <td class="col-no">${startNo + i}</td>
                    <td class="nama-cell">
                        <span class="nm">${escapeHtml(row.nama)}</span>
                        <span class="sk">${escapeHtml(row.sekolah)}</span>
                    </td>
                    <td>${escapeHtml(row.kelas || '-')}</td>
                    <td>${escapeHtml(row.sekolah || '-')}</td>
                    <td class="col-money">${formatRupiah(row.total_tagihan)}</td>
                    <td class="col-money">${formatRupiah(row.total_terbayar)}</td>
                    <td class="col-money">${formatRupiah(row.sisa_tagihan)}</td>
                    <td><span class="badge ${paid ? 'badge-lunas' : 'badge-belum'}">${paid ? 'Lunas' : 'Belum Lunas'}</span></td>
                    <td class="col-action">
                        <button type="button" class="detail-link" data-custid="${custid}">
                            Detail <i class="fas fa-chevron-right"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            table.style.display = 'table';
            empty.style.display = 'none';

            const pagination = json.pagination;
            hasMore = !!pagination.has_more;
            document.getElementById('paginationInfo').textContent = `Menampilkan ${pagination.from}–${pagination.to}`;
            document.getElementById('btnPageNum').textContent = pagination.page;
            document.getElementById('btnPrev').disabled = pagination.page <= 1;
            document.getElementById('btnNext').disabled = !hasMore;
            paginationBar.style.display = 'flex';

            loadSummary();

        } catch (e) {
            console.error('loadSiswaList error', e);
            empty.style.display = 'block';
            tbody.innerHTML = '';
            Swal.fire({ icon: 'error', title: 'Error', text: 'Tidak dapat terhubung ke server', confirmButtonColor: '#2563eb' });
        }
    }

    document.getElementById('btnSearch').addEventListener('click', () => loadSiswaList(true));

    document.getElementById('filterSekolah').addEventListener('change', function() {
        const kelasSel = document.getElementById('filterKelas');
        kelasSel.value = '';
        updateKelasSelect().then(() => {
            loadSiswaList(true);
        });
    });

    document.getElementById('filterKelas').addEventListener('change', function() {
        if (!this.disabled) {
            loadSiswaList(true);
        }
    });

    document.getElementById('filterBta').addEventListener('change', () => loadSiswaList(true));
    document.getElementById('filterStatus').addEventListener('change', () => loadSiswaList(true));

    const filterTagihanBtn = document.getElementById('filterTagihanBtn');
    if (filterTagihanBtn) {
        filterTagihanBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleTagihanDropdown();
        });

        document.getElementById('filterTagihanList').addEventListener('change', (e) => {
            if (e.target.matches('input[type="checkbox"]')) {
                const val = e.target.value;
                if (isDaftarUlangOnly) {
                    // humas: tetap 1 opsi, tidak boleh dikosongkan
                    e.target.checked = true;
                    selectedTagihan = [val];
                    updateTagihanLabel();
                    return;
                }
                if (e.target.checked) {
                    if (!selectedTagihan.includes(val)) selectedTagihan.push(val);
                } else {
                    selectedTagihan = selectedTagihan.filter(v => v !== val);
                }
                updateTagihanLabel();
            }
        });

        const btnTagihanClear = document.getElementById('btnTagihanClear');
        if (btnTagihanClear) {
            btnTagihanClear.addEventListener('click', () => {
                selectedTagihan = [];
                document.querySelectorAll('#filterTagihanList input[type="checkbox"]').forEach(cb => cb.checked = false);
                updateTagihanLabel();
            });
        }

        document.getElementById('btnTagihanApply').addEventListener('click', () => {
            toggleTagihanDropdown(false);
            loadSiswaList(true);
        });
    }

    document.addEventListener('click', (e) => {
        const dd = document.getElementById('filterTagihanDropdown');
        const btn = document.getElementById('filterTagihanBtn');
        if (!dd || !btn) return;
        if (dd.classList.contains('open') && !dd.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
            toggleTagihanDropdown(false);
        }
    });

    document.getElementById('btnPrev').addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadSiswaList(); } });
    document.getElementById('btnNext').addEventListener('click', () => { if (hasMore) { currentPage++; loadSiswaList(); } });

    document.getElementById('btnReset').addEventListener('click', () => {
        if (!restrictedSekolah) {
            document.getElementById('filterSekolah').value = '';
            document.getElementById('filterKelas').value = '';
            document.getElementById('filterKelas').disabled = true;
            document.getElementById('filterKelas').innerHTML = '<option value="">Pilih sekolah dulu</option>';
        }
        document.getElementById('filterBta').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterSearch').value = '';
        if (!isDaftarUlangOnly) {
            selectedTagihan = [];
            document.querySelectorAll('#filterTagihanList input[type="checkbox"]').forEach(cb => cb.checked = false);
            updateTagihanLabel();
        }
        if (restrictedSekolah) {
            updateKelasSelect();
        }
        loadSiswaList(true);
    });

    document.getElementById('filterToggle').addEventListener('click', () => {
        const body = document.getElementById('filterBody');
        const icon = document.getElementById('filterToggleIcon');
        const text = document.getElementById('filterToggleText');
        const hidden = body.style.display === 'none';
        body.style.display = hidden ? 'block' : 'none';
        text.textContent = hidden ? 'Sembunyikan' : 'Tampilkan';
        icon.className = hidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
    });

    const toggleBtn = document.getElementById('drawerToggle');
    const closeBtn = document.getElementById('drawerClose');
    const backdrop = document.getElementById('drawerBackdrop');
    const drawer = document.getElementById('drawer');
    const app = document.getElementById('app');
    const DRAWER_KEY = 'kepsek_drawer_open';

    function isDesktopDrawer() {
        return window.matchMedia('(min-width: 960px)').matches;
    }
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
        bindSiswaTableDelegation();
        bindBillToggleDelegation();
        await loadFilterOptions();
        await loadSiswaList();
    })();
</script>
@if (session('error'))
<script>Swal.fire({ icon: 'warning', title: 'Perhatian', text: @json(session('error')), confirmButtonColor: '#2563eb' });</script>
@endif
</body>
</html>