<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($restrictedTagihan ?? false) ? 'Tagihan Daftar Ulang' : 'Tagihan Periode' }} - Monitoring Kepsek</title>
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
        .table-scroll { overflow-x: auto; position: relative; -webkit-overflow-scrolling: touch; }
        #siswaTable {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: auto;
            min-width: 1100px;
        }
        thead.main-thead { background: var(--head-bg); }
        thead.main-thead th {
            color: var(--head-text);
            text-align: center !important;
            vertical-align: middle;
            padding: 10px 8px;
        }
        #siswaTable th {
            padding: 10px 8px; text-align: center; font-size: .68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .03em; border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        #siswaTable td {
            padding: 10px 8px; font-size: .78rem; border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            background: #fff;
        }
        tbody tr.data-row:hover td { background: #f8fafc; }
        tbody tr.data-row.expanded td { background: #eff6ff; }
        .col-no { width: 48px; min-width: 48px; color: var(--muted); font-weight: 600; text-align: center; }
        .col-nama { min-width: 160px; max-width: 220px; width: 180px; text-align: left; }
        .col-kelas { min-width: 70px; white-space: nowrap; text-align: center; }
        .col-sekolah { min-width: 100px; max-width: 140px; text-align: center; }
        .col-money { min-width: 100px; text-align: right; white-space: nowrap; font-weight: 600; font-size: .74rem !important; }
        .col-sisa { color: #dc2626 !important; }
        .col-saldo { color: #2563eb !important; }
        .col-status { min-width: 100px; text-align: center; }
        .col-ket { min-width: 110px; text-align: center; }
        .col-action { min-width: 80px; text-align: center; white-space: nowrap; }
        .col-wa { min-width: 90px; text-align: center; white-space: nowrap; }
        .col-penagihan { min-width: 120px; text-align: center; }
        /* Sticky No + Nama saat scroll horizontal */
        #siswaTable th.col-no,
        #siswaTable td.col-no {
            position: sticky;
            left: 0;
            z-index: 3;
            background: #fff;
        }
        #siswaTable th.col-nama,
        #siswaTable td.col-nama {
            position: sticky;
            left: 48px;
            z-index: 3;
            background: #fff;
        }
        #siswaTable thead th.col-no,
        #siswaTable thead th.col-nama {
            z-index: 5;
            background: var(--head-bg);
        }
        tbody tr.data-row:hover td.col-no,
        tbody tr.data-row:hover td.col-nama { background: #f8fafc; }
        tbody tr.data-row.expanded td.col-no,
        tbody tr.data-row.expanded td.col-nama { background: #eff6ff; }
        #siswaTable th.col-nama,
        #siswaTable th.col-money,
        #siswaTable th.col-no,
        #siswaTable th.col-kelas,
        #siswaTable th.col-sekolah,
        #siswaTable th.col-status,
        #siswaTable th.col-ket,
        #siswaTable th.col-action,
        #siswaTable th.col-wa,
        #siswaTable th.col-penagihan { text-align: center !important; }
        #siswaTable th.col-money,
        #siswaTable td.col-money { padding-left: 8px; padding-right: 10px; }
        #siswaTable th.col-action,
        #siswaTable td.col-action,
        #siswaTable th.col-wa,
        #siswaTable td.col-wa,
        #siswaTable th.col-penagihan,
        #siswaTable td.col-penagihan,
        #siswaTable th.col-status,
        #siswaTable td.col-status,
        #siswaTable th.col-ket,
        #siswaTable td.col-ket { padding-left: 6px; padding-right: 6px; }
        .col-status .badge,
        .col-ket .badge {
            display: inline-block;
            max-width: 100%;
            line-height: 1.25;
            white-space: normal;
            text-align: center;
        }
        .col-sekolah {
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .nama-cell { text-align: left; }
        .btn-wa, .btn-catat, .btn-laporan, .btn-detail {
            display: inline-flex; align-items: center; justify-content: center; gap: 4px;
            border-radius: 7px; padding: 5px 7px; font-size: .68rem; font-weight: 700;
            font-family: inherit; cursor: pointer; text-decoration: none; white-space: nowrap;
            max-width: 100%;
        }
        .btn-detail {
            border: 1.5px solid #93c5fd; background: #eff6ff; color: #2563eb;
        }
        .btn-detail:hover { background: #dbeafe; border-color: #60a5fa; }
        .btn-detail.open { background: #dbeafe; border-color: #2563eb; }
        .btn-wa {
            border: 1.5px solid #93c5fd; background: #eff6ff; color: #2563eb;
        }
        .btn-wa:hover { background: #dbeafe; border-color: #60a5fa; color: #1d4ed8; }
        .btn-wa-disabled, .btn-wa:disabled {
            border: 1.5px solid #e2e8f0; background: #f8fafc; color: #94a3b8; cursor: not-allowed;
        }
        .btn-wa-disabled:hover, .btn-wa:disabled:hover { background: #f8fafc; color: #94a3b8; }
        .btn-catat { border: none; background: #166534; color: #fff; }
        .btn-catat:hover { background: #14532d; color: #fff; }
        .btn-laporan {
            border: 1.5px solid #cbd5e1; background: #f8fafc; color: #475569;
            white-space: normal; line-height: 1.25; text-align: center;
            padding: 5px 8px; max-width: 100%;
        }
        .btn-laporan:hover { background: #f1f5f9; color: #0f172a; }
        .aksi-empty { color: #cbd5e1; font-weight: 700; }
        .wa-empty {
            display: inline-block; font-size: .68rem; font-weight: 700;
            color: #94a3b8; line-height: 1.2;
        }
        .action-pair { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
        .penagihan-modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 80; display: none; }
        .penagihan-modal-backdrop.open { display: block; }
        .penagihan-modal { position: fixed; inset: 0; z-index: 90; display: none; align-items: center; justify-content: center; padding: 16px; }
        .penagihan-modal.open { display: flex; }
        .penagihan-modal-card { width: min(560px, 100%); max-height: calc(100vh - 32px); overflow: auto; background: #fff; border-radius: 16px; box-shadow: 0 20px 50px rgba(15,23,42,.25); padding: 20px 22px 18px; }
        .penagihan-modal-card.laporan-card { width: min(640px, 100%); }
        .penagihan-modal-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .penagihan-modal-head h2 { margin: 0; font-size: 1.05rem; font-weight: 800; }
        .penagihan-modal-close { border: none; background: transparent; font-size: 1.4rem; line-height: 1; color: #64748b; cursor: pointer; }
        .laporan-siswa-card {
            background: #f8fafc; border: 1px solid var(--border); border-radius: 12px;
            padding: 12px 14px; margin-bottom: 16px;
        }
        .laporan-siswa-card .label { font-size: .72rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .laporan-siswa-card .nama { margin-top: 4px; font-size: 1rem; font-weight: 800; }
        .laporan-siswa-card .sub { margin-top: 4px; font-size: .82rem; color: var(--muted); }
        .laporan-body { min-height: 120px; }
        .laporan-empty { text-align: center; padding: 28px 12px; color: var(--muted); font-size: .9rem; }
        .penagihan-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 16px; background: #f8fafc; border: 1px solid var(--border); border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; }
        .penagihan-meta span { display: block; font-size: .72rem; color: var(--muted); font-weight: 600; margin-bottom: 2px; }
        .penagihan-meta b { font-size: .88rem; font-weight: 700; }
        .penagihan-meta .pm-sisa { color: #dc2626; }
        .pm-field { margin-bottom: 14px; }
        .pm-field > label { display: block; font-size: .82rem; font-weight: 700; margin-bottom: 8px; }
        .pm-field > label em { color: #dc2626; font-style: normal; }
        .pm-field input[type="date"],
        .pm-field input[type="text"],
        .pm-field textarea { width: 100%; border: 1px solid var(--border); border-radius: 10px; padding: 10px 12px; font: inherit; font-size: .88rem; }
        .pm-field textarea { resize: vertical; min-height: 96px; }
        .pm-radios { display: grid; gap: 8px; }
        .pm-radios-inline { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pm-radios label { display: flex; align-items: center; gap: 8px; font-size: .86rem; font-weight: 500; cursor: pointer; }
        .pm-radios input { accent-color: var(--accent); }
        .pm-counter { text-align: right; font-size: .72rem; color: var(--muted); margin-top: 4px; }
        .pm-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
        .pm-btn-cancel, .pm-btn-save { border-radius: 10px; padding: 10px 14px; font: inherit; font-size: .86rem; font-weight: 700; cursor: pointer; }
        .pm-btn-cancel { border: 1px solid var(--border); background: #fff; color: var(--text); }
        .pm-btn-save { border: none; background: var(--accent); color: #fff; display: inline-flex; align-items: center; gap: 8px; }
        .pm-btn-save:disabled { opacity: .6; cursor: not-allowed; }
        @media (max-width: 640px) {
            .penagihan-meta, .pm-radios-inline { grid-template-columns: 1fr; }
        }
        .nama-cell .nm {
            font-weight: 600; display: block; max-width: 100%;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .nama-cell .sk { display: none; }
        .badge {
            display: inline-block; padding: 4px 7px; border-radius: 6px;
            font-size: .64rem; font-weight: 800; color: #fff; letter-spacing: .01em;
            white-space: nowrap;
        }
        .badge-lunas { background: #16a34a; color: #fff; }
        .badge-belum { background: #ef4444; color: #fff; }
        .badge-saldo { background: var(--blue-bg); color: var(--blue-text); }
        .badge-kader-dalam { background: #dbeafe; color: #1e40af; }
        .badge-kader-luar { background: #f1f5f9; color: #475569; }
        .badge-beasiswa { background: #e0e7ff; color: #3730a3; }
        .badge-beasiswa-0 { background: #f1f5f9; color: #475569; }
        .nama-cell .nm-wrap { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
        .nama-cell .kader-badge { align-self: flex-start; }
        .detail-link { border: none; background: none; color: var(--accent); font-weight: 600; font-size: .84rem; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px; }
        .detail-link.bill-toggle i { transition: transform .15s; }
        .detail-link.bill-toggle.open i { transform: rotate(90deg); }
        .btn-detail.detail-link {
            border: 1.5px solid #93c5fd; background: #eff6ff; color: #2563eb;
            font-size: .68rem; font-weight: 700; gap: 4px; padding: 5px 7px; border-radius: 7px;
        }
        tr.detail-row > td { padding: 18px 20px 22px 20px; background: #f8fafc; border-bottom: 2px solid var(--border); }
        .siswa-panel-head { display: flex; flex-direction: column; gap: 12px; margin-bottom: 0; }
        .siswa-panel-title-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        .siswa-panel-head .t { font-size: .95rem; font-weight: 700; color: #0f172a; }
        .siswa-panel-period { font-size: .78rem; font-weight: 600; color: var(--accent); background: var(--blue-bg); padding: 4px 12px; border-radius: 999px; white-space: nowrap; }
        .siswa-panel-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .btn-laporan-siswa {
            display: inline-flex; align-items: center; gap: 6px;
            border: none; border-radius: 8px; padding: 8px 12px;
            background: #b91c1c; color: #fff; font-size: .78rem; font-weight: 700;
            text-decoration: none; font-family: inherit; cursor: pointer;
        }
        .btn-laporan-siswa:hover { background: #991b1b; color: #fff; }
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
        .detail-layout { display: flex; align-items: flex-start; gap: 16px; }
        .detail-main { flex: 1.55; min-width: 0; display: flex; flex-direction: column; gap: 14px; }
        .detail-col { min-width: 0; }
        .detail-col-title { font-size: .78rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 8px; }
        .detail-tables { display: flex; align-items: flex-start; gap: 14px; }
        .detail-col-tagihan { flex: 1.35; min-width: 0; }
        .detail-col-uangmasuk { flex: 1; min-width: 0; }
        .detail-col-riwayat { flex: 0 0 300px; width: 300px; max-width: 34%; align-self: stretch; }
        .riwayat-panel {
            background: #fff; border: 1px solid var(--border); border-radius: 12px;
            padding: 14px 16px 12px; height: 100%; max-height: none;
            display: flex; flex-direction: column; min-height: 420px;
        }
        .riwayat-panel-title {
            margin: 0 0 14px; font-size: .86rem; font-weight: 800; color: #1e3a5f;
        }
        .riwayat-timeline { position: relative; padding-left: 20px; flex: 1; overflow: auto; }
        .riwayat-timeline::before {
            content: ""; position: absolute; left: 6px; top: 6px; bottom: 6px;
            width: 2px; background: #e2e8f0;
        }
        .rv-item { position: relative; padding-bottom: 18px; }
        .rv-item:last-child { padding-bottom: 0; }
        .rv-item.hidden-item { display: none; }
        .riwayat-timeline.show-all .rv-item.hidden-item { display: block; }
        .rv-dot {
            position: absolute; left: -20px; top: 4px; width: 12px; height: 12px;
            border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 2px currentColor;
            background: currentColor;
        }
        .rv-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; margin-bottom: 6px; }
        .rv-date { font-size: .8rem; font-weight: 700; color: var(--text); display: inline-flex; align-items: center; gap: 6px; }
        .rv-new {
            display: inline-block; padding: 2px 7px; border-radius: 999px;
            font-size: .58rem; font-weight: 800; letter-spacing: .06em;
            background: #ef4444; color: #fff; line-height: 1.3; text-transform: uppercase;
        }
        .rv-media { font-size: .74rem; font-weight: 600; color: var(--muted); display: inline-flex; align-items: center; gap: 5px; }
        .rv-media.wa { color: #16a34a; }
        .rv-badge {
            display: inline-block; padding: 4px 9px; border-radius: 999px;
            font-size: .68rem; font-weight: 700; border: 1px solid; margin-bottom: 6px;
        }
        .rv-meta { font-size: .74rem; color: var(--muted); margin-bottom: 4px; }
        .rv-note { font-size: .76rem; color: #334155; line-height: 1.45; }
        .rv-note b { color: var(--text); }
        .rv-admin { margin-top: 4px; font-size: .68rem; color: #94a3b8; }
        .tone-orange { color: #ea580c; }
        .tone-orange .rv-badge { background: #fff7ed; color: #ea580c; border-color: #fdba74; }
        .tone-blue { color: #2563eb; }
        .tone-blue .rv-badge { background: #eff6ff; color: #2563eb; border-color: #93c5fd; }
        .tone-red { color: #dc2626; }
        .tone-red .rv-badge { background: #fef2f2; color: #dc2626; border-color: #fca5a5; }
        .tone-green { color: #16a34a; }
        .tone-green .rv-badge { background: #f0fdf4; color: #16a34a; border-color: #86efac; }
        .tone-slate { color: #475569; }
        .tone-slate .rv-badge { background: #f8fafc; color: #475569; border-color: #cbd5e1; }
        .btn-riwayat-all {
            margin-top: 12px; width: 100%; border: 1px solid var(--border); background: #fff; color: var(--text);
            border-radius: 10px; padding: 9px 12px; font: inherit; font-size: .8rem; font-weight: 700;
            cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            text-decoration: none; flex-shrink: 0;
        }
        .btn-riwayat-all:hover { background: #f8fafc; color: var(--text); }
        .bills-table-wrap { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; overflow-x: auto; }
        .bills-table { width: 100%; border-collapse: collapse; min-width: 480px; }
        .bills-table th { background: #f8fafc; padding: 10px 14px; font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: var(--muted); text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; }
        .bills-table td { padding: 11px 14px; font-size: .82rem; border-bottom: 1px solid #f1f5f9; }
        .bills-table tbody tr:last-child td { border-bottom: none; }
        .invoice-link {
            color: #1d4ed8; font-weight: 700; text-decoration: underline;
            text-underline-offset: 2px;
        }
        .invoice-link:hover { color: #1e40af; }
        .invoice-link .fa-file-pdf { margin-right: 4px; font-size: .78em; }
        .uang-masuk-wrap { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; overflow-x: auto; max-height: 360px; overflow-y: auto; }
        .uang-masuk-table { width: 100%; border-collapse: collapse; min-width: 220px; }
        .uang-masuk-table th { background: #f8fafc; padding: 10px 14px; font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: var(--muted); text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; }
        .uang-masuk-table td { padding: 11px 14px; font-size: .82rem; border-bottom: 1px solid #f1f5f9; }
        .uang-masuk-table tbody tr:last-child td { border-bottom: none; }
        .uang-masuk-table tbody tr.is-newest { background: #fff7ed; }
        .uang-masuk-date { display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap; }
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
        .pagination-size { display: flex; align-items: center; gap: 8px; font-size: .84rem; color: var(--muted); font-weight: 500; }
        .pagination-size select { padding: 6px 10px; border: 1px solid var(--border); border-radius: 8px; font-size: .84rem; font-family: inherit; background: #fff; color: var(--text); }
        .page-btn { border: 1px solid var(--border); background: #fff; color: var(--text); border-radius: 8px; padding: 8px 14px; font-size: .84rem; font-weight: 600; cursor: pointer; font-family: inherit; }
        .page-btn:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
        .page-btn:disabled { opacity: .4; cursor: not-allowed; }
        .summary-bar { margin-bottom: 16px; background: var(--card); border-radius: 14px; box-shadow: var(--shadow); border: 1px solid var(--border); padding: 18px 20px; }
        .summary-header { display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .summary-header h3 { margin: 0; font-size: .96rem; font-weight: 700; }
        .summary-period { font-size: .8rem; font-weight: 600; color: var(--accent); background: var(--blue-bg); padding: 4px 12px; border-radius: 999px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .summary-item .label { font-size: .74rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
        .summary-item .value { font-size: 1.15rem; font-weight: 800; }
        .summary-item .sub { margin-top: 4px; font-size: .78rem; font-weight: 600; color: var(--muted); }
        .summary-item.terbayar .value { color: #166534; }
        .summary-item.piutang .value { color: #991b1b; }
        .summary-item.piutang .sub { color: #b91c1c; }
        .summary-item.total .value { color: var(--accent); }
        .summary-item.siswa .value { color: #0f172a; }
        .loading-row td { padding: 30px; text-align: center; color: var(--muted); }
        .field-tagihan { position: relative; }
        .tagihan-select-btn { width:100%; display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border:1px solid var(--border); border-radius:10px; background:#fff; font-size:.88rem; font-family:inherit; color:var(--text); cursor:pointer; }
        .tagihan-select-btn:disabled { background: #f1f5f9; color: #64748b; cursor: not-allowed; }
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
        .chip i { margin-right: 4px; }
        .saldo-note-box { display: flex; align-items: flex-start; gap: 8px; background: var(--blue-bg); color: var(--blue-text); border-radius: 10px; padding: 10px 12px; font-size: .8rem; margin-top: 10px; }
        .saldo-note-box i { margin-top: 2px; }
        @media (max-width: 1100px) {
            .detail-layout { flex-direction: column; }
            .detail-col-riwayat { flex: 1 1 auto; width: 100%; max-width: none; }
            .riwayat-panel { min-height: 280px; }
        }
        @media (max-width: 900px) {
            .filter-body { grid-template-columns: 1fr 1fr; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .detail-tables { flex-direction: column; }
            .main { padding: 14px; }
            .title { font-size: 1.15rem; }
            .table-toolbar { flex-direction: column; align-items: stretch; }
            .table-toolbar .btn-row { justify-content: stretch; }
            .table-toolbar .btn { flex: 1; justify-content: center; }
        }
        /* Card list: HP + tablet portrait — harus SETELAH rule .mobile-list { display:none } */
        .mobile-list {
            display: none;
            flex-direction: column;
            gap: 10px;
            padding: 12px;
        }
        .m-card {
            background: #fff; border: 1px solid var(--border); border-radius: 12px;
            padding: 12px 14px; box-shadow: var(--shadow);
        }
        .m-card.expanded { border-color: #93c5fd; background: #f8fbff; }
        .m-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
        .m-card-no { font-size: .72rem; font-weight: 700; color: var(--muted); }
        .m-card-name { font-size: .9rem; font-weight: 800; color: var(--text); line-height: 1.3; word-break: break-word; }
        .m-card-meta { font-size: .75rem; color: var(--muted); margin-top: 2px; }
        .m-card-badges { display: flex; flex-wrap: wrap; gap: 6px; margin: 8px 0 10px; }
        .m-card-money { display: grid; grid-template-columns: 1fr; gap: 8px; margin-bottom: 12px; }
        .m-money-item { background: #f8fafc; border-radius: 8px; padding: 8px 10px; min-width: 0; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .m-money-item span { display: block; font-size: .68rem; font-weight: 700; color: var(--muted); text-transform: uppercase; }
        .m-money-item b { display: block; font-size: .82rem; font-weight: 800; white-space: nowrap; }
        .m-money-item.sisa b { color: #991b1b; }
        .m-money-item.bayar b { color: #166534; }
        .m-money-item.saldo b { color: #2563eb; }
        .m-card-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .m-card-actions .btn-detail,
        .m-card-actions .btn-wa,
        .m-card-actions .btn-catat,
        .m-card-actions .btn-laporan,
        .m-card-actions .wa-empty {
            flex: 1 1 calc(50% - 4px);
            min-width: 130px;
            padding: 9px 10px;
            font-size: .75rem;
            justify-content: center;
            text-align: center;
        }
        .m-card-detail {
            margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border);
        }
        @media (max-width: 900px) {
            .desktop-table-wrap { display: none !important; }
            .mobile-list { display: flex !important; }
            .filter-body { grid-template-columns: 1fr; }
            .summary-grid { grid-template-columns: 1fr; gap: 12px; }
            .summary-item .value { font-size: 1.05rem; }
            .filter-card { padding: 14px; }
            .filter-actions { justify-content: stretch; }
            .filter-actions .btn { flex: 1; justify-content: center; }
            .pagination { flex-direction: column; align-items: stretch; }
            .pagination-btns { width: 100%; }
            .pagination-btns .page-btn { flex: 1; }
            .page-btn { padding: 10px 8px; font-size: .78rem; }
            tr.detail-row > td { padding: 12px; }
            .mini-total { grid-template-columns: 1fr 1fr; }
            .penagihan-modal { padding: 10px; align-items: flex-end; }
            .penagihan-modal-card { width: 100%; max-height: 92vh; border-radius: 16px 16px 0 0; }
            .pm-actions { flex-direction: column-reverse; }
            .pm-btn-cancel, .pm-btn-save { width: 100%; justify-content: center; }
        }
        @media (min-width: 901px) and (max-width: 1280px) {
            .table-scroll::after {
                content: "Geser ke samping untuk melihat semua kolom →";
                display: block;
                padding: 8px 14px 12px;
                font-size: .72rem;
                color: var(--muted);
                font-weight: 600;
            }
        }
        @media (min-width: 960px) {
            .app.drawer-pinned { margin-left: 280px; }
            .drawer-backdrop { display: none; }
            .main { padding: 20px 18px; max-width: none; }
        }
    </style>
</head>
<body>
<div class="app" id="app">
    <div class="main">
        <header class="header">
            <button class="burger" id="drawerToggle" type="button" aria-label="Buka/tutup menu" title="Menu"><span></span><span></span><span></span></button>
            <h1 class="title">{{ ($restrictedTagihan ?? false) ? 'Tagihan Daftar Ulang' : 'Tagihan Periode' }}</h1>
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
                    <div class="field">
                        <label for="filterBta">Tahun Ajaran</label>
                        <select id="filterBta"><option value="">Memuat...</option></select>
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
                        <label for="filterStatus">Status Kelunasan</label>
                        <select id="filterStatus">
                            <option value="">Semua status</option>
                            <option value="1">Lunas</option>
                            <option value="0">Belum Lunas</option>
                        </select>
                    </div>
                    <div class="field field-tagihan">
                        <label for="filterTagihanBtn">Tagihan Per</label>
                        <button type="button" id="filterTagihanBtn" class="tagihan-select-btn" {{ ($restrictedTagihan ?? false) ? 'disabled' : '' }}>
                            <span id="filterTagihanLabel">{{ ($restrictedTagihan ?? false) ? ($lockedTagihan ?? 'BIAYA Daftar Ulang Ajaran Baru') : 'Pilih tagihan' }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="tagihan-dropdown" id="filterTagihanDropdown">
                            <div class="tagihan-dropdown-inner" id="filterTagihanList">
                                @if($restrictedTagihan ?? false)
                                    <label class="tagihan-option">
                                        <input type="radio" name="tagihan_radio" value="{{ $lockedTagihan ?? 'BIAYA Daftar Ulang Ajaran Baru' }}" checked disabled>
                                        <span>{{ $lockedTagihan ?? 'BIAYA Daftar Ulang Ajaran Baru' }}</span>
                                    </label>
                                @else
                                    <div class="tagihan-empty-note">Memuat...</div>
                                @endif
                            </div>
                            @unless($restrictedTagihan ?? false)
                            <div class="tagihan-dropdown-actions">
                                <button type="button" id="btnTagihanClear" class="tagihan-clear-btn">Bersihkan</button>
                                <button type="button" id="btnTagihanApply" class="tagihan-apply-btn">Terapkan</button>
                            </div>
                            @endunless
                        </div>
                    </div>
                    <div class="field field-tagihan">
                        <label for="filterBeasiswaBtn">Kategori Beasiswa</label>
                        <button type="button" id="filterBeasiswaBtn" class="tagihan-select-btn">
                            <span id="filterBeasiswaLabel">Semua kategori</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="tagihan-dropdown" id="filterBeasiswaDropdown">
                            <div class="tagihan-dropdown-inner" id="filterBeasiswaList">
                                <label class="tagihan-option"><input type="checkbox" value="0"><span>NON BEASISWA</span></label>
                                <label class="tagihan-option"><input type="checkbox" value="1"><span>KADER DALAM</span></label>
                                <label class="tagihan-option"><input type="checkbox" value="2"><span>KADER MALANG</span></label>
                                <label class="tagihan-option"><input type="checkbox" value="3"><span>KADER PENGURUS</span></label>
                                <label class="tagihan-option"><input type="checkbox" value="4"><span>KADER AMAL USAHA</span></label>
                                <label class="tagihan-option"><input type="checkbox" value="5"><span>KADER JAMAAH</span></label>
                                <label class="tagihan-option"><input type="checkbox" value="6"><span>KADER PENGABDIAN</span></label>
                                <label class="tagihan-option"><input type="checkbox" value="7"><span>SAUDARA</span></label>
                                <label class="tagihan-option"><input type="checkbox" value="8"><span>TAAWWUN</span></label>
                                <label class="tagihan-option"><input type="checkbox" value="9"><span>ALUMNI</span></label>
                            </div>
                            <div class="tagihan-dropdown-actions">
                                <button type="button" id="btnBeasiswaClear" class="tagihan-clear-btn">Bersihkan</button>
                                <button type="button" id="btnBeasiswaApply" class="tagihan-apply-btn">Terapkan</button>
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

        <div class="summary-bar" id="summaryBar">
            <div class="summary-header">
                <h3><i class="fas fa-chart-pie"></i> Ringkasan</h3>
                <span class="summary-period" id="summaryPeriod"></span>
            </div>
            <div class="summary-grid">
                <div class="summary-item siswa">
                    <div class="label">Jumlah Santri</div>
                    <div class="value" id="sumSiswa">
                        <span class="spinner-sm"></span> Menghitung...
                    </div>
                </div>
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
                    <div class="sub" id="sumSiswaPiutang"></div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-toolbar">
                <h3><i class="fas fa-file-invoice"></i> Daftar Tagihan per Siswa</h3>
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
                <div class="btn-row" style="margin:0;">
                    <a href="#" class="btn btn-excel" id="btnExportExcel"><i class="fas fa-file-excel"></i> Export Excel</a>
                    <a href="#" class="btn btn-pdf" id="btnExportPdf"><i class="fas fa-file-pdf"></i> Export PDF</a>
                </div>
            </div>

            <div class="table-scroll desktop-table-wrap">
                <table id="siswaTable">
                    <thead class="main-thead">
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-nama">Nama</th>
                            <th class="col-kelas">Kelas</th>
                            <th class="col-sekolah">Unit</th>
                            <th class="col-money">Tagihan</th>
                            <th class="col-money">Terbayar</th>
                            <th class="col-money">Saldo SPP</th>
                            <th class="col-money">Sisa tagihan</th>
                            <th class="col-status">Status</th>
                            <th class="col-ket">Ket.</th>
                            <th class="col-action">Detail</th>
                            <th class="col-wa">Aksi</th>
                            <th class="col-penagihan">Penagihan</th>
                        </tr>
                    </thead>
                    <tbody id="siswaBody"></tbody>
                </table>
            </div>
            <div class="mobile-list" id="siswaMobileList"></div>
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

<div class="penagihan-modal-backdrop" id="penagihanModalBackdrop"></div>
<div class="penagihan-modal" id="penagihanModal" role="dialog" aria-modal="true">
    <div class="penagihan-modal-card">
        <div class="penagihan-modal-head">
            <h2>Catat Hasil Penagihan</h2>
            <button type="button" class="penagihan-modal-close" id="btnClosePenagihan" aria-label="Tutup">&times;</button>
        </div>
        <div class="penagihan-meta">
            <div><span>Nama Santri</span><b id="pmNama">-</b></div>
            <div><span>Kelas / Program</span><b id="pmKelas">-</b></div>
            <div><span>Tagihan Per</span><b id="pmTagihan">-</b></div>
            <div><span>Sisa Tagihan</span><b id="pmSisa" class="pm-sisa">-</b></div>
        </div>
        <form id="formCatatPenagihan">
            <input type="hidden" id="pmCustid" name="custid">
            <input type="hidden" id="pmNocust" name="nocust">

            <div class="pm-field">
                <label>Tanggal Komunikasi <em>*</em></label>
                <input type="date" id="pmTanggal" name="tanggal_komunikasi" required>
            </div>

            <div class="pm-field">
                <label>Media Komunikasi <em>*</em></label>
                <div class="pm-radios pm-radios-inline">
                    <label><input type="radio" name="media_komunikasi" value="WhatsApp" checked> WhatsApp</label>
                    <label><input type="radio" name="media_komunikasi" value="Telepon"> Telepon</label>
                </div>
            </div>

            <div class="pm-field">
                <label>Hasil Komunikasi <em>*</em></label>
                <div class="pm-radios">
                    <label><input type="radio" name="hasil_komunikasi" value="Akan melakukan pembayaran" checked> Akan melakukan pembayaran</label>
                    <label><input type="radio" name="hasil_komunikasi" value="Sudah melakukan pembayaran"> Sudah melakukan pembayaran</label>
                    <label><input type="radio" name="hasil_komunikasi" value="Meminta waktu pembayaran"> Meminta waktu pembayaran</label>
                    <label><input type="radio" name="hasil_komunikasi" value="Mengalami kendala pembayaran"> Mengalami kendala pembayaran</label>
                    <label><input type="radio" name="hasil_komunikasi" value="Tidak dapat dihubungi"> Tidak dapat dihubungi</label>
                </div>
            </div>

            <div class="pm-field">
                <label>Rencana Pembayaran</label>
                <input type="text" id="pmRencana" name="rencana_pembayaran" maxlength="255" placeholder="Contoh: 25 Agustus 2026 / Angsuran 2 kali">
            </div>

            <div class="pm-field">
                <label>Catatan</label>
                <textarea id="pmCatatan" name="catatan" maxlength="250" rows="4" placeholder="Tuliskan catatan hasil komunikasi..."></textarea>
                <div class="pm-counter"><span id="pmCatatanCount">0</span>/250</div>
            </div>

            <div class="pm-actions">
                <button type="button" class="pm-btn-cancel" id="btnCancelPenagihan">Batal</button>
                <button type="submit" class="pm-btn-save" id="btnSavePenagihan">
                    <i class="fas fa-floppy-disk"></i> Simpan Hasil Penagihan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="penagihan-modal-backdrop" id="laporanModalBackdrop"></div>
<div class="penagihan-modal" id="laporanModal" role="dialog" aria-modal="true">
    <div class="penagihan-modal-card laporan-card">
        <div class="penagihan-modal-head">
            <h2>Laporan Catatan Penagihan</h2>
            <button type="button" class="penagihan-modal-close" id="btnCloseLaporan" aria-label="Tutup">&times;</button>
        </div>
        <div class="laporan-siswa-card">
            <div class="label">Santri</div>
            <div class="nama" id="lpNama">-</div>
            <div class="sub" id="lpSub">-</div>
        </div>
        <div class="laporan-body" id="laporanBody">
            <div class="mini-loading"><div class="spinner"></div>Memuat riwayat...</div>
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
                    @if($restrictedTagihan ?? false)
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
        @if($restrictedTagihan ?? false)
            <li class="drawer-item"><a href="{{ route('kepsek.tagihan-periode') }}" class="drawer-link active"><span class="icon"><i class="fas fa-file-invoice-dollar"></i></span><span>Tagihan Daftar Ulang</span></a></li>
        @else
            <li class="drawer-item"><a href="{{ route('dashboard.monitoring-kepsek') }}" class="drawer-link"><span class="icon"><i class="fas fa-house"></i></span><span>Dashboard</span></a></li>
            <li class="drawer-item"><a href="{{ route('kepsek.tagihan-periode') }}" class="drawer-link active"><span class="icon"><i class="fas fa-file-invoice-dollar"></i></span><span>Tagihan Periode</span></a></li>
        @endif
        @if((int) session('user.is_superadmin', 0) === 1 || strtolower(trim((string) session('user.kelompok', ''))) === 'superadmin')
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
    siswaList: @json(route('kepsek.tagihan-periode.siswa')),
    kelasBySekolah: @json(route('kepsek.tagihan-periode.kelas')),
    siswaTagihan: @json(route('kepsek.tagihan-periode.siswa-detail')),
    uangMasuk: @json(route('kepsek.tagihan-periode.siswa-uang-masuk')),
    saldo: @json(route('kepsek.tagihan-periode.siswa-saldo')),
    detail: @json(route('kepsek.tagihan-periode.detail')),
    summary: @json(route('kepsek.tagihan-periode.summary')),
    filters: @json(route('kepsek.tagihan-periode.filters')),
    exportExcel: @json(route('kepsek.tagihan-periode.export-excel')),
    exportPdf: @json(route('kepsek.tagihan-periode.export-pdf')),
    siswaLaporan: @json(route('kepsek.tagihan-periode.siswa-laporan')),
    invoice: @json(route('kepsek.tagihan-periode.invoice')),
    catatPenagihan: @json(route('kepsek.tagihan-periode.catat-penagihan')),
    catatPenagihanStore: @json(route('kepsek.tagihan-periode.catat-penagihan.store')),
    laporanPenagihan: @json(route('kepsek.tagihan-periode.laporan-penagihan')),
    riwayatPenagihan: @json(route('kepsek.tagihan-periode.riwayat-penagihan')),
};
const csrf = @json(csrf_token());
const currentAdmin = @json(session('user.username', ''));
const restrictedSekolah = @json($restrictedSekolah ?? null);
const restrictedSekolahCodes = (restrictedSekolah
    ? String(restrictedSekolah).split(',').map(s => s.trim()).filter(Boolean)
    : []);
const canMultiSelectSekolah = @json((bool) ($canMultiSelectSekolah ?? false));
const isDaftarUlangOnly = @json((bool) ($restrictedTagihan ?? false));
const lockedTagihan = @json($lockedTagihan ?? 'BIAYA Daftar Ulang Ajaran Baru');

let currentPage = 1;
let hasMore = false;
let pageSize = 10;
const beasiswaLabels = {
    0: 'NON BEASISWA',
    1: 'KADER DALAM',
    2: 'KADER MALANG',
    3: 'KADER PENGURUS',
    4: 'KADER AMAL USAHA',
    5: 'KADER JAMAAH',
    6: 'KADER PENGABDIAN',
    7: 'SAUDARA',
    8: 'TAAWWUN',
    9: 'ALUMNI',
};
let selectedBeasiswa = [];
let selectedSekolah = [];
let selectedKelas = [];
let selectedTagihan = isDaftarUlangOnly ? [lockedTagihan] : [];
const siswaBillsCache = new Map();
const siswaUangMasukCache = new Map();
const siswaRiwayatCache = new Map();
const accDetailCache = new Map();
let siswaRowsCurrent = [];

function formatRupiah(val) {
    const n = Number(val) || 0;
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
}

function escapeHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function parseJsonResponse(res) {
    const text = await res.text();
    let json = null;
    try {
        json = text ? JSON.parse(text) : {};
    } catch (e) {
        const head = String(text || '').slice(0, 300).toLowerCase();
        if (res.status === 401 || res.status === 419 || head.includes('login') || head.includes('csrf')) {
            throw new Error('Sesi berakhir atau tidak valid. Silakan login ulang.');
        }
        if (res.status === 404) {
            throw new Error('Endpoint tidak ditemukan (404). Cek deploy route/controller.');
        }
        if (res.status >= 500) {
            throw new Error('Server error. Coba refresh atau login ulang.');
        }
        throw new Error(`Respons bukan JSON (HTTP ${res.status}). Biasanya session habis atau route belum ter-deploy.`);
    }
    if (!res.ok && json && json.success === false) {
        throw new Error(json.message || `Gagal memuat data (HTTP ${res.status})`);
    }
    return json || {};
}

function apiHeaders(extra = {}) {
    return {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...extra,
    };
}

function normalizeWaNumber(raw) {
    let n = String(raw ?? '').replace(/\D/g, '');
    if (!n) return '';
    if (n.startsWith('62')) return n;
    if (n.startsWith('0')) return '62' + n.slice(1);
    if (n.startsWith('8')) return '62' + n;
    return '62' + n;
}

function formatAngka(val) {
    const n = Number(val) || 0;
    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(n);
}

/** Prefix BCN VA berdasarkan CODE01 siswa (mst_sekolah) */
const VA_PREFIX_BY_CODE01 = {
    '101': '751027', // ARROHMAH Putri 1 / artri 1
    '102': '751028', // ARROHMAH Putra Tahfidz
    '103': '751022', // ARROHMAH Putri 2 / artri 2
    '104': '751031', // SD TK PG Artri
};

function buildVirtualAccount(siswa) {
    const nocust = String(siswa.nis || siswa.nocust || '').replace(/\D/g, '');
    if (!nocust) return '-';
    const code01 = String(siswa.code01 || '').trim();
    const prefix = VA_PREFIX_BY_CODE01[code01] || '';
    // Hindari dobel prefix jika NOCUST sudah diawali kode BCN
    if (prefix && nocust.startsWith(prefix)) return nocust;
    return prefix ? (prefix + nocust) : nocust;
}

function buildWaMessage(siswa) {
    const nama = String(siswa.nama || '-').toUpperCase();
    const kelas = siswa.kelas || '-';
    const unit = siswa.sekolah || '-';
    const bta = document.getElementById('filterBta').value || '-';
    const tagihan = selectedTagihan.length ? selectedTagihan.join(', ') : 'Tagihan Periode';
    const va = buildVirtualAccount(siswa);
    const jumlah = formatAngka(siswa.sisa_tagihan);
    // Emoji via code point (aman encoding file / Blade)
    const emojiDoa = String.fromCodePoint(0x1F932);
    const emojiPin = String.fromCodePoint(0x1F4CC);
    const emojiDaun = String.fromCodePoint(0x1F33F);

    return [
        'Bismillahirrahmanirrahim',
        '',
        'Assalamu\u2019alaikum warahmatullahi wabarakaatuh.',
        '',
        'Bapak/Ibu Wali Santri yang kami hormati,',
        'Semoga Allah SWT senantiasa melimpahkan kesehatan, keberkahan rezeki, dan kemudahan dalam setiap urusan Bapak/Ibu sekeluarga. Aamiin. ' + emojiDoa,
        '',
        'Dengan hormat, kami menyampaikan pengingat pembiayaan pendidikan ananda sebagai bahan perhatian Bapak/Ibu.',
        '',
        emojiPin + ' *INFORMASI TANGGUNGAN PER ' + tagihan + ' ' + bta + '*',
        '',
        'Nama: ' + nama,
        'Jenjang: ' + unit,
        'Kelas: ' + kelas,
        'Virtual Account: ' + va,
        '*Total Tanggungan: Rp' + jumlah + '*',
        '_Mohon melebihkan Rp2.000 setiap transaksi untuk biaya administrasi VA._',
        '',
        'Pembayaran dapat dilakukan melalui Virtual Account ananda.',
        '',
        'Kami mohon kesediaan Bapak/Ibu untuk memperhatikan tanggungan pendidikan ananda agar proses pendidikan dapat berjalan dengan baik.',
        '',
        'Semoga Allah SWT menjadikan setiap ikhtiar Bapak/Ibu dalam pendidikan ananda sebagai amal jariyah dan keberkahan rezeki. ' + emojiDoa + emojiDaun,
        '',
        'Jazakumullahu khairan katsiran. Barakallahu fiikum.',
        '',
        'Bagian Keuangan YPI Ar-Rohmah Putri Malang',
        '',
        'Apabila pembayaran telah dilakukan, mohon pesan ini dapat diabaikan.',
        '',
        'Wassalamu\u2019alaikum warahmatullahi wabarakaatuh.',
    ].join('\n');
}

function openKirimTagihanWa(siswa) {
    const wa = normalizeWaNumber(siswa.no_wa);
    if (!wa) return;
    const text = encodeURIComponent(buildWaMessage(siswa));
    // Bug WA: wa.me / kadang api.whatsapp merusak emoji jadi � di desktop.
    // Mobile → api.whatsapp.com ; Desktop → web.whatsapp.com
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent || '');
    const url = isMobile
        ? ('https://api.whatsapp.com/send?phone=' + wa + '&text=' + text)
        : ('https://web.whatsapp.com/send?phone=' + wa + '&text=' + text);
    window.open(url, '_blank');
}

function buildLaporanPenagihanUrl(siswa) {
    const q = new URLSearchParams({
        custid: siswa.custid || '',
        nama: siswa.nama || '',
        kelas: siswa.kelas || '',
        bta: document.getElementById('filterBta').value || '',
    });
    return routes.laporanPenagihan + '?' + q.toString();
}

function renderRiwayatTimelineHtml(rows, previewLimit = 0) {
    if (!rows.length) {
        return `
            <div class="laporan-empty">
                <i class="fas fa-inbox" style="font-size:1.6rem;opacity:.35;display:block;margin-bottom:8px;"></i>
                Belum ada catatan penagihan untuk siswa ini.
            </div>
        `;
    }

    const items = rows.map((row, i) => {
        const hasil = String(row.hasil_komunikasi || '');
        const tone = hasilTone(hasil);
        const media = String(row.media_komunikasi || '');
        const isWa = media === 'WhatsApp';
        const rencana = String(row.rencana_pembayaran || '').trim();
        const catatan = String(row.catatan || '').trim();
        const admin = String(row.admin || '').trim();
        const hidden = previewLimit > 0 && i >= previewLimit ? ' hidden-item' : '';
        return `
            <div class="rv-item ${tone}${hidden}">
                <span class="rv-dot"></span>
                <div class="rv-head">
                    <div class="rv-date">${formatTanggalId(row.tanggal_komunikasi)}</div>
                    <div class="rv-media ${isWa ? 'wa' : ''}">
                        ${isWa ? '<i class="fab fa-whatsapp"></i> WhatsApp' : `<i class="fas fa-phone"></i> ${escapeHtml(media || 'Telepon')}`}
                    </div>
                </div>
                <div class="rv-badge">${escapeHtml(hasil || '-')}</div>
                ${rencana ? `<div class="rv-meta">Rencana Bayar: ${escapeHtml(rencana)}</div>` : ''}
                ${catatan ? `<div class="rv-note"><b>Catatan:</b> ${escapeHtml(catatan)}</div>` : ''}
                ${admin ? `<div class="rv-admin">Dicatat oleh: ${escapeHtml(admin)}</div>` : ''}
            </div>
        `;
    }).join('');

    return `<div class="riwayat-timeline" data-riwayat-timeline>${items}</div>`;
}

async function openLaporanPenagihanModal(siswa) {
    const custid = siswa.custid || '';
    const bta = document.getElementById('filterBta').value || '';
    document.getElementById('lpNama').textContent = (siswa.nama || '-').toUpperCase();
    document.getElementById('lpSub').textContent = [
        siswa.kelas ? `Kelas ${siswa.kelas}` : '',
        custid ? `CUSTID ${custid}` : '',
        bta ? `BTA ${bta}` : '',
    ].filter(Boolean).join(' · ') || '-';

    const body = document.getElementById('laporanBody');
    body.innerHTML = '<div class="mini-loading"><div class="spinner"></div>Memuat riwayat...</div>';
    document.getElementById('laporanModalBackdrop').classList.add('open');
    document.getElementById('laporanModal').classList.add('open');

    try {
        let rows;
        if (siswaRiwayatCache.has(custid)) {
            rows = siswaRiwayatCache.get(custid);
        } else {
            rows = await fetchSiswaRiwayat(custid);
            siswaRiwayatCache.set(custid, rows);
        }
        body.innerHTML = renderRiwayatTimelineHtml(rows, 0);
    } catch (err) {
        body.innerHTML = `<div class="laporan-empty" style="color:#991b1b;">${escapeHtml(err.message || 'Gagal memuat riwayat')}</div>`;
    }
}

function closeLaporanPenagihanModal() {
    document.getElementById('laporanModalBackdrop').classList.remove('open');
    document.getElementById('laporanModal').classList.remove('open');
}

function todayISO() {
    const d = new Date();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${m}-${day}`;
}

function openCatatPenagihanModal(siswa) {
    const bta = document.getElementById('filterBta').value || '-';
    const tagihan = selectedTagihan.length ? selectedTagihan.join(', ') : '-';
    document.getElementById('pmCustid').value = siswa.custid || '';
    document.getElementById('pmNocust').value = siswa.nis || siswa.nocust || '';
    document.getElementById('pmNama').textContent = (siswa.nama || '-').toUpperCase();
    document.getElementById('pmKelas').textContent = siswa.kelas || '-';
    document.getElementById('pmTagihan').textContent = `${tagihan} ${bta}`.trim();
    document.getElementById('pmSisa').textContent = formatRupiah(siswa.sisa_tagihan);
    document.getElementById('pmTanggal').value = todayISO();
    document.getElementById('pmRencana').value = '';
    document.getElementById('pmCatatan').value = '';
    document.getElementById('pmCatatanCount').textContent = '0';
    document.querySelector('input[name="media_komunikasi"][value="WhatsApp"]').checked = true;
    document.querySelector('input[name="hasil_komunikasi"][value="Akan melakukan pembayaran"]').checked = true;
    document.getElementById('penagihanModalBackdrop').classList.add('open');
    document.getElementById('penagihanModal').classList.add('open');
}

function closeCatatPenagihanModal() {
    document.getElementById('penagihanModalBackdrop').classList.remove('open');
    document.getElementById('penagihanModal').classList.remove('open');
}

async function submitCatatPenagihan(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSavePenagihan');
    const payload = {
        custid: document.getElementById('pmCustid').value,
        nocust: document.getElementById('pmNocust').value,
        tanggal_komunikasi: document.getElementById('pmTanggal').value,
        media_komunikasi: document.querySelector('input[name="media_komunikasi"]:checked')?.value || '',
        hasil_komunikasi: document.querySelector('input[name="hasil_komunikasi"]:checked')?.value || '',
        rencana_pembayaran: document.getElementById('pmRencana').value.trim(),
        catatan: document.getElementById('pmCatatan').value.trim(),
        admin: currentAdmin,
    };

    if (!payload.custid || !payload.tanggal_komunikasi || !payload.media_komunikasi || !payload.hasil_komunikasi) {
        Swal.fire({ icon: 'warning', title: 'Lengkapi form', text: 'Field bertanda * wajib diisi', confirmButtonColor: '#2563eb' });
        return;
    }

    btn.disabled = true;
    try {
        const res = await fetch(routes.catatPenagihanStore, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(payload),
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok || !json.success) {
            throw new Error(json.message || 'Gagal menyimpan hasil penagihan');
        }
        closeCatatPenagihanModal();
        if (payload.custid) siswaRiwayatCache.delete(payload.custid);
        Swal.fire({ icon: 'success', title: 'Tersimpan', text: json.message || 'Hasil penagihan berhasil disimpan', confirmButtonColor: '#2563eb' });
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Gagal', text: err.message || 'Tidak dapat menyimpan data', confirmButtonColor: '#2563eb' });
    } finally {
        btn.disabled = false;
    }
}

function renderActionButtons(siswa, paid) {
    if (paid) {
        return `
            <button type="button" class="btn-laporan btn-open-laporan" data-custid="${escapeHtml(siswa.custid)}" title="Laporan Catatan Penagihan">
                <i class="fas fa-file-lines"></i> Laporan Catatan Penagihan
            </button>
        `;
    }

    const hasWa = !!normalizeWaNumber(siswa.no_wa);
    const waBtn = hasWa
        ? `<button type="button" class="btn-wa btn-kirim-wa" data-custid="${escapeHtml(siswa.custid)}" title="Kirim Tagihan">
                <i class="fab fa-whatsapp"></i> Kirim
           </button>`
        : `<span class="wa-empty">Tidak ada WA</span>`;

    return `
        ${waBtn}
        <button type="button" class="btn-catat btn-open-catat" data-custid="${escapeHtml(siswa.custid)}" title="Catat Penagihan">
            <i class="fas fa-pen-to-square"></i> Catat
        </button>
    `;
}

function renderActionCells(siswa, paid) {
    if (paid) {
        return `
            <td class="col-wa"><span class="aksi-empty">—</span></td>
            <td class="col-penagihan">${renderActionButtons(siswa, paid)}</td>
        `;
    }
    const hasWa = !!normalizeWaNumber(siswa.no_wa);
    const waCell = hasWa
        ? `<button type="button" class="btn-wa btn-kirim-wa" data-custid="${escapeHtml(siswa.custid)}" title="Kirim Tagihan">
                <i class="fab fa-whatsapp"></i> Kirim
           </button>`
        : `<span class="wa-empty">Tidak ada WA</span>`;
    return `
        <td class="col-wa">${waCell}</td>
        <td class="col-penagihan">
            <button type="button" class="btn-catat btn-open-catat" data-custid="${escapeHtml(siswa.custid)}" title="Catat Penagihan">
                <i class="fas fa-pen-to-square"></i> Catat
            </button>
        </td>
    `;
}

function resolveBeasiswaLabel(code) {
    const n = Number(code);
    if (Number.isFinite(n) && beasiswaLabels[n] != null) return beasiswaLabels[n];
    return 'KODE ' + String(code ?? '-');
}

function kaderBadgeHtml(row) {
    const code = Number(row?.is_anak_pegawai ?? 0);
    const label = row?.beasiswa_label || row?.kader_label || resolveBeasiswaLabel(code);
    const cls = code === 0 ? 'badge-beasiswa-0' : (code === 1 ? 'badge-kader-dalam' : 'badge-beasiswa');
    return `<span class="badge kader-badge ${cls}">${escapeHtml(label)}</span>`;
}

/** Sisa tagihan <= 50rb tetap boleh ujian (pukul rata). */
const SISA_BISA_UJIAN_MAX = 50000;
function bisaUjian(row) {
    const sisa = Number(row?.sisa_tagihan ?? 0);
    return Number.isFinite(sisa) && sisa <= SISA_BISA_UJIAN_MAX;
}

function renderMobileCard(row, no) {
    const custid = row.custid;
    const paid = Number(row.status) === 1;
    const bolehUjian = bisaUjian(row);
    return `
        <div class="m-card data-card" data-custid="${escapeHtml(custid)}">
            <div class="m-card-top">
                <div>
                    <div class="m-card-no">#${no}</div>
                    <div class="m-card-name">${escapeHtml((row.nama || '').toUpperCase())}</div>
                    <div class="m-card-meta">${escapeHtml(row.kelas || '-')} · ${escapeHtml(row.sekolah || '-')}</div>
                </div>
            </div>
            <div class="m-card-badges">
                ${kaderBadgeHtml(row)}
                <span class="badge ${paid ? 'badge-lunas' : 'badge-belum'}">${paid ? 'Lunas' : 'Belum Lunas'}</span>
                <span class="badge ${bolehUjian ? 'badge-lunas' : 'badge-belum'}">${bolehUjian ? 'Bisa Ujian' : 'Belum Bisa Ujian'}</span>
            </div>
            <div class="m-card-money">
                <div class="m-money-item"><span>Tagihan</span><b>${formatRupiah(row.total_tagihan)}</b></div>
                <div class="m-money-item bayar"><span>Terbayar</span><b>${formatRupiah(row.total_terbayar)}</b></div>
                <div class="m-money-item saldo"><span>Saldo SPP</span><b>${formatRupiah(row.saldo)}</b></div>
                <div class="m-money-item sisa"><span>Sisa tagihan</span><b>${formatRupiah(row.sisa_tagihan)}</b></div>
            </div>
            <div class="m-card-actions">
                <button type="button" class="btn-detail detail-link" data-custid="${escapeHtml(custid)}">
                    <i class="fas fa-eye"></i> Detail
                </button>
                ${renderActionButtons(row, paid)}
            </div>
            <div class="m-card-detail" hidden></div>
        </div>
    `;
}

function getSelectedSekolahValues() {
    if (canMultiSelectSekolah) {
        return selectedSekolah.slice();
    }
    const el = document.getElementById('filterSekolah');
    const v = el ? String(el.value || '').trim() : '';
    return v ? [v] : [];
}

function getFilterParams() {
    return {
        sekolah: getSelectedSekolahValues(),
        bta: document.getElementById('filterBta').value,
        kelas: selectedKelas.slice(),
        paidst: document.getElementById('filterStatus').value,
        is_anak_pegawai: selectedBeasiswa,
        search: document.getElementById('filterSearch').value.trim(),
        tagihan: selectedTagihan,
    };
}

function getDataParams() {
    return {
        ...getFilterParams(),
        limit: pageSize,
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

function buildSiswaLaporanUrl(siswa) {
    return routes.siswaLaporan + '?' + buildQuery({
        custid: siswa.custid,
        bta: document.getElementById('filterBta').value,
        tagihan: selectedTagihan,
        nama: siswa.nama || '',
        kelas: siswa.kelas || '',
        sekolah: siswa.sekolah || '',
        nocust: siswa.nis || siswa.nocust || '',
        is_anak_pegawai: siswa.is_anak_pegawai ?? 0,
        kader_label: siswa.beasiswa_label || siswa.kader_label || resolveBeasiswaLabel(siswa.is_anak_pegawai ?? 0),
        beasiswa_label: siswa.beasiswa_label || siswa.kader_label || resolveBeasiswaLabel(siswa.is_anak_pegawai ?? 0),
        total_tagihan: siswa.total_tagihan ?? '',
        total_terbayar: siswa.total_terbayar ?? '',
        sisa_tagihan: siswa.sisa_tagihan ?? '',
        sisa_tagihan_sebelum_saldo: siswa.sisa_tagihan_sebelum_saldo ?? '',
        saldo_terpakai: siswa.saldo_terpakai ?? '',
    });
}

function buildInvoiceUrl(custid, kodeTagihan) {
    return routes.invoice + '?' + buildQuery({
        custid,
        kode_tagihan: kodeTagihan,
    });
}

function updateSummaryPeriodLabel() {
    const bta = document.getElementById('filterBta').value;
    const parts = [];
    if (selectedTagihan.length) parts.push(selectedTagihan.join(', '));
    if (bta) parts.push(bta);
    const el = document.getElementById('summaryPeriod');
    el.textContent = parts.length ? 'Per ' + parts.join(' - ') : '';
}

function closeAllExpanded() {
    document.querySelectorAll('.acc-row').forEach(r => r.remove());
    document.querySelectorAll('.detail-row').forEach(r => r.remove());
    document.querySelectorAll('.m-card-detail').forEach(el => {
        el.hidden = true;
        el.innerHTML = '';
    });
    document.querySelectorAll('.detail-link.open').forEach(b => b.classList.remove('open'));
    document.querySelectorAll('.data-row.expanded, .m-card.expanded').forEach(r => r.classList.remove('expanded'));
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
            headers: apiHeaders({ 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }),
            body: JSON.stringify({ custid, kode_tagihan: kode }),
        });
        const json = await parseJsonResponse(res);
        const details = json.data?.detail || [];
        if (!tr.isConnected) return;
        if (!json.success || !details.length) {
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
        const namaTagihan = escapeHtml(b.nama_tagihan);
        const namaCell = paid
            ? `<a class="invoice-link" href="${escapeHtml(buildInvoiceUrl(custid, b.kode_tagihan))}" target="_blank" rel="noopener" title="Download invoice"><i class="fas fa-file-pdf"></i>${namaTagihan}</a>`
            : namaTagihan;
        return `
            <tr class="bill-row" data-custid="${escapeHtml(custid)}" data-kode="${escapeHtml(b.kode_tagihan)}">
                <td>${escapeHtml(b.bta || '-')}</td>
                <td>${namaCell}</td>
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
    const body = rows.map((r, i) => {
        const newBadge = i === 0 ? '<span class="rv-new">New</span>' : '';
        return `
        <tr class="${i === 0 ? 'is-newest' : ''}">
            <td><span class="uang-masuk-date">${escapeHtml(r.tanggal ?? '-')}${newBadge}</span></td>
            <td style="text-align:right;">${formatRupiah(r.nominal)}</td>
        </tr>
    `;
    }).join('');
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

function formatTanggalId(val) {
    if (!val) return '-';
    const d = new Date(String(val).replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) {
        const m = String(val).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return escapeHtml(String(val));
        return `${m[3]}/${m[2]}/${m[1]}`;
    }
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = d.getFullYear();
    return `${dd}/${mm}/${yyyy}`;
}

function hasilTone(hasil) {
    switch (String(hasil || '')) {
        case 'Meminta waktu pembayaran': return 'tone-orange';
        case 'Akan melakukan pembayaran': return 'tone-blue';
        case 'Sudah melakukan pembayaran': return 'tone-green';
        case 'Tidak dapat dihubungi': return 'tone-red';
        case 'Mengalami kendala pembayaran': return 'tone-slate';
        default: return 'tone-slate';
    }
}

function renderRiwayatPanel(siswa, rows) {
    const previewLimit = 3;
    const custid = escapeHtml(siswa.custid || '');
    const total = rows.length;
    const previewRows = rows.slice(0, previewLimit);

    if (!total) {
        return `
            <div class="riwayat-panel">
                <h4 class="riwayat-panel-title">Riwayat Penagihan Sebelumnya</h4>
                <div class="mini-loading" style="padding:18px 8px;">
                    <i class="fas fa-inbox" style="opacity:.35;display:block;margin-bottom:6px;"></i>
                    Belum ada catatan penagihan
                </div>
                <button type="button" class="btn-riwayat-all btn-open-laporan" data-custid="${custid}">
                    <i class="fas fa-list"></i> Lihat Semua Riwayat
                </button>
            </div>
        `;
    }

    const items = previewRows.map((row, i) => {
        const hasil = String(row.hasil_komunikasi || '');
        const tone = hasilTone(hasil);
        const media = String(row.media_komunikasi || '');
        const isWa = media === 'WhatsApp';
        const rencana = String(row.rencana_pembayaran || '').trim();
        const catatan = String(row.catatan || '').trim();
        const admin = String(row.admin || '').trim();
        const newBadge = i === 0 ? '<span class="rv-new">Baru</span>' : '';
        return `
            <div class="rv-item ${tone}">
                <span class="rv-dot"></span>
                <div class="rv-head">
                    <div class="rv-date">${formatTanggalId(row.tanggal_komunikasi)}${newBadge}</div>
                    <div class="rv-media ${isWa ? 'wa' : ''}">
                        ${isWa ? '<i class="fab fa-whatsapp"></i> WhatsApp' : `<i class="fas fa-phone"></i> ${escapeHtml(media || 'Telepon')}`}
                    </div>
                </div>
                <div class="rv-badge">${escapeHtml(hasil || '-')}</div>
                ${rencana ? `<div class="rv-meta">Rencana Bayar: ${escapeHtml(rencana)}</div>` : ''}
                ${catatan ? `<div class="rv-note"><b>Catatan:</b> ${escapeHtml(catatan)}</div>` : ''}
                ${admin ? `<div class="rv-admin">Dicatat oleh: ${escapeHtml(admin)}</div>` : ''}
            </div>
        `;
    }).join('');

    const btnLabel = total > previewLimit
        ? `Lihat Semua Riwayat (${total})`
        : 'Lihat Semua Riwayat';

    return `
        <div class="riwayat-panel">
            <h4 class="riwayat-panel-title">Riwayat Penagihan Sebelumnya</h4>
            <div class="riwayat-timeline">${items}</div>
            <button type="button" class="btn-riwayat-all btn-open-laporan" data-custid="${custid}">
                <i class="fas fa-list"></i> ${btnLabel}
            </button>
        </div>
    `;
}

async function fetchSiswaBills(custid, bta) {
    const res = await fetch(routes.siswaTagihan + '?' + buildQuery({ custid, bta, tagihan: selectedTagihan }), { headers: apiHeaders() });
    const json = await parseJsonResponse(res);
    if (!json.success) {
        throw new Error(json.message || 'Gagal memuat tagihan');
    }
    return json.data || [];
}

async function fetchSiswaUangMasuk(custid) {
    const res = await fetch(routes.uangMasuk + '?' + buildQuery({ custid }), { headers: apiHeaders() });
    const json = await parseJsonResponse(res);
    if (!json.success) {
        throw new Error(json.message || 'Gagal memuat Data Pembayaran Santri');
    }
    return json.data || [];
}

async function fetchSiswaRiwayat(custid) {
    const res = await fetch(routes.riwayatPenagihan + '?' + buildQuery({ custid }), { headers: apiHeaders() });
    const json = await parseJsonResponse(res);
    if (!json.success) {
        throw new Error(json.message || 'Gagal memuat riwayat penagihan');
    }
    return json.data || [];
}

async function fetchSiswaSaldo(custid) {
    const res = await fetch(routes.saldo + '?' + buildQuery({ custid }), { headers: apiHeaders() });
    const json = await parseJsonResponse(res);
    if (!json.success) {
        throw new Error(json.message || 'Gagal memuat saldo');
    }
    return json.data || { saldo: 0, total_debet: 0, total_kredit: 0 };
}

async function toggleSiswaDetail(btn, dataRow, siswa) {
    const custid = siswa.custid;
    const bta = document.getElementById('filterBta').value;
    const cacheKey = custid + '|' + bta + '|' + selectedTagihan.join(',');
    const isMobileCard = dataRow.classList.contains('data-card');
    const mobileDetail = isMobileCard ? dataRow.querySelector('.m-card-detail') : null;

    if (isMobileCard) {
        if (mobileDetail && !mobileDetail.hidden && mobileDetail.innerHTML.trim() !== '') {
            mobileDetail.hidden = true;
            mobileDetail.innerHTML = '';
            btn.classList.remove('open');
            dataRow.classList.remove('expanded');
            return;
        }
    } else {
        const existing = dataRow.nextElementSibling;
        if (existing && existing.classList.contains('detail-row')) {
            existing.remove();
            btn.classList.remove('open');
            dataRow.classList.remove('expanded');
            return;
        }
    }

    closeAllExpanded();
    btn.classList.add('open');
    dataRow.classList.add('expanded');

    let hostEl;
    if (isMobileCard) {
        hostEl = mobileDetail;
        hostEl.hidden = false;
        hostEl.innerHTML = '<div class="mini-loading"><div class="spinner"></div>Memuat detail...</div>';
    } else {
        hostEl = document.createElement('tr');
        hostEl.className = 'detail-row';
        dataRow.after(hostEl);
    }

    let saldoData = { saldo: 0, total_debet: 0, total_kredit: 0 };
    try {
        saldoData = await fetchSiswaSaldo(custid);
    } catch (e) {
        console.error('fetchSiswaSaldo error', e);
    }

    if (!dataRow.classList.contains('expanded')) return;

    const saldoTerpakai = Number(siswa.saldo_terpakai) || 0;
    const sisaSebelumSaldo = Number(siswa.sisa_tagihan_sebelum_saldo ?? siswa.sisa_tagihan) || 0;
    const periodeParts = [];
    if (selectedTagihan.length) periodeParts.push(selectedTagihan.join(', '));
    if (bta) periodeParts.push(bta);
    const periodeLabel = periodeParts.join(' - ');
    const saldoNoteHtml = saldoTerpakai > 0 ? `
        <div class="saldo-note-box">
            <i class="fas fa-circle-info"></i>
            <div>Sisa tagihan ${formatRupiah(siswa.sisa_tagihan)} sudah dikurangi saldo sebesar ${formatRupiah(saldoTerpakai)} dari sisa tagihan awal ${formatRupiah(sisaSebelumSaldo)}.</div>
        </div>
    ` : '';

    const periodeBadgeHtml = periodeLabel ? `<span class="siswa-panel-period">Per ${escapeHtml(periodeLabel)}</span>` : '';
    const laporanUrl = buildSiswaLaporanUrl(siswa);
    const laporanBtnHtml = `
        <a class="btn-laporan-siswa" href="${escapeHtml(laporanUrl)}" target="_blank" rel="noopener">
            <i class="fas fa-file-pdf"></i> Download Laporan
        </a>
    `;

    const headHtml = `
        <div class="siswa-panel-head">
            <div class="siswa-panel-title-row">
                <div class="t">Detail Keuangan — ${escapeHtml((siswa.nama || '').toUpperCase())} ${kaderBadgeHtml(siswa)}</div>
                <div class="siswa-panel-actions">
                    ${periodeBadgeHtml}
                    ${laporanBtnHtml}
                </div>
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
            ${saldoNoteHtml}
        </div>
    `;

    const bodyHtml = (billsHtml, uangMasukHtml, riwayatHtml) => `
        <div class="detail-layout">
            <div class="detail-main">
                ${headHtml}
                <div class="detail-tables">
                    <div class="detail-col detail-col-tagihan">
                        <div class="detail-col-title">Daftar Tagihan</div>
                        ${billsHtml}
                    </div>
                    <div class="detail-col detail-col-uangmasuk">
                        <div class="detail-col-title">Data Pembayaran Santri</div>
                        ${uangMasukHtml}
                    </div>
                </div>
            </div>
            <div class="detail-col detail-col-riwayat">
                ${riwayatHtml}
            </div>
        </div>
    `;

    const loadingBills = '<div class="mini-loading"><div class="spinner"></div>Memuat tagihan siswa...</div>';
    const loadingUangMasuk = '<div class="mini-loading"><div class="spinner"></div>Memuat Data Pembayaran Santri...</div>';
    const loadingRiwayat = '<div class="riwayat-panel"><div class="mini-loading"><div class="spinner"></div>Memuat riwayat penagihan...</div></div>';
    const loadingContent = bodyHtml(loadingBills, loadingUangMasuk, loadingRiwayat);

    if (isMobileCard) {
        hostEl.innerHTML = loadingContent;
    } else {
        hostEl.innerHTML = `<td colspan="13">${loadingContent}</td>`;
    }

    const [billsResult, uangMasukResult, riwayatResult] = await Promise.allSettled([
        siswaBillsCache.has(cacheKey) ? Promise.resolve(siswaBillsCache.get(cacheKey)) : fetchSiswaBills(custid, bta),
        siswaUangMasukCache.has(custid) ? Promise.resolve(siswaUangMasukCache.get(custid)) : fetchSiswaUangMasuk(custid),
        siswaRiwayatCache.has(custid) ? Promise.resolve(siswaRiwayatCache.get(custid)) : fetchSiswaRiwayat(custid),
    ]);

    if (!hostEl.isConnected && !isMobileCard) return;
    if (isMobileCard && (!dataRow.classList.contains('expanded') || !hostEl.isConnected)) return;

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

    let riwayatHtml;
    if (riwayatResult.status === 'fulfilled') {
        siswaRiwayatCache.set(custid, riwayatResult.value);
        riwayatHtml = renderRiwayatPanel(siswa, riwayatResult.value);
    } else {
        riwayatHtml = `<div class="riwayat-panel"><div class="mini-loading" style="color:#991b1b;">${escapeHtml(riwayatResult.reason?.message || 'Gagal memuat riwayat penagihan.')}</div></div>`;
    }

    const finalContent = bodyHtml(billsHtml, uangMasukHtml, riwayatHtml);
    if (isMobileCard) {
        hostEl.innerHTML = finalContent;
    } else {
        hostEl.innerHTML = `<td colspan="13">${finalContent}</td>`;
    }
}

function setSummaryPlaceholder() {
    document.getElementById('sumSiswa').textContent = '-';
    document.getElementById('sumTotal').textContent = 'Pilih tahun ajaran & tagihan';
    document.getElementById('sumTerbayar').textContent = '-';
    document.getElementById('sumPiutang').textContent = '-';
    document.getElementById('sumSiswaPiutang').textContent = '';
}

function setSummaryLoading() {
    const loadingHtml = '<span class="spinner-sm"></span> Menghitung...';
    document.getElementById('sumSiswa').innerHTML = loadingHtml;
    document.getElementById('sumTotal').innerHTML = loadingHtml;
    document.getElementById('sumTerbayar').innerHTML = loadingHtml;
    document.getElementById('sumPiutang').innerHTML = loadingHtml;
    document.getElementById('sumSiswaPiutang').textContent = '';
}

function setSummaryError() {
    document.getElementById('sumSiswa').textContent = 'Gagal memuat';
    document.getElementById('sumTotal').textContent = 'Gagal memuat';
    document.getElementById('sumTerbayar').textContent = 'Gagal memuat';
    document.getElementById('sumPiutang').textContent = 'Gagal memuat';
    document.getElementById('sumSiswaPiutang').textContent = '';
}

let summaryRequestId = 0;

async function loadSummary() {
    updateSummaryPeriodLabel();
    const filterParams = getFilterParams();
    const periodeReady = !!filterParams.bta && filterParams.tagihan.length > 0;

    if (!periodeReady) {
        setSummaryPlaceholder();
        return;
    }

    const requestId = ++summaryRequestId;
    setSummaryLoading();

    try {
        const q = buildQuery(filterParams);
        const res = await fetch(routes.summary + (q ? '?' + q : ''), {
            headers: apiHeaders()
        });
        const json = await parseJsonResponse(res);

        if (requestId !== summaryRequestId) return;

        if (json.success) {
            const totalSiswa = Number(json.total_siswa) || 0;
            const totalSiswaPiutang = Number(json.total_siswa_piutang) || 0;
            document.getElementById('sumSiswa').textContent = formatAngka(totalSiswa) + ' santri';
            document.getElementById('sumTerbayar').textContent = formatRupiah(json.total_terbayar || 0);
            document.getElementById('sumPiutang').textContent = formatRupiah(json.total_piutang || 0);
            document.getElementById('sumTotal').textContent = formatRupiah(json.total_tagihan || 0);
            document.getElementById('sumSiswaPiutang').textContent =
                formatAngka(totalSiswaPiutang) + ' santri memiliki piutang';
        } else {
            setSummaryError();
        }
    } catch (e) {
        console.error('loadSummary error', e);
        if (requestId !== summaryRequestId) return;
        setSummaryError();
    }
}

function updateTagihanLabel() {
    const label = document.getElementById('filterTagihanLabel');
    if (!selectedTagihan.length) {
        label.textContent = 'Pilih tagihan';
    } else {
        label.textContent = selectedTagihan[0];
    }
}

function updateBeasiswaLabel() {
    const label = document.getElementById('filterBeasiswaLabel');
    if (!label) return;
    if (!selectedBeasiswa.length) {
        label.textContent = 'Semua kategori';
        return;
    }
    if (selectedBeasiswa.length === 1) {
        label.textContent = resolveBeasiswaLabel(selectedBeasiswa[0]);
        return;
    }
    label.textContent = selectedBeasiswa.length + ' kategori dipilih';
}

function syncBeasiswaCheckboxes() {
    document.querySelectorAll('#filterBeasiswaList input[type="checkbox"]').forEach(cb => {
        cb.checked = selectedBeasiswa.map(String).includes(String(cb.value));
    });
}

function updateSekolahLabel() {
    const label = document.getElementById('filterSekolahLabel');
    if (!label) return;
    if (!selectedSekolah.length) {
        label.textContent = 'Semua sekolah/unit';
        return;
    }
    if (selectedSekolah.length === 1) {
        label.textContent = selectedSekolah[0];
        return;
    }
    label.textContent = selectedSekolah.length + ' unit dipilih';
}

function syncSekolahCheckboxes() {
    document.querySelectorAll('#filterSekolahList input[type="checkbox"]').forEach(cb => {
        cb.checked = selectedSekolah.includes(cb.value);
    });
}

function updateKelasLabel() {
    const label = document.getElementById('filterKelasLabel');
    const btn = document.getElementById('filterKelasBtn');
    if (!label || !btn) return;
    if (btn.disabled) {
        label.textContent = 'Pilih sekolah dulu';
        return;
    }
    if (!selectedKelas.length) {
        label.textContent = 'Semua kelas';
        return;
    }
    if (selectedKelas.length === 1) {
        label.textContent = selectedKelas[0];
        return;
    }
    label.textContent = selectedKelas.length + ' kelas dipilih';
}

function syncKelasCheckboxes() {
    document.querySelectorAll('#filterKelasList input[type="checkbox"]').forEach(cb => {
        cb.checked = selectedKelas.includes(cb.value);
    });
}

function resetKelasFilter(message) {
    selectedKelas = [];
    const btn = document.getElementById('filterKelasBtn');
    const list = document.getElementById('filterKelasList');
    if (btn) btn.disabled = true;
    if (list) list.innerHTML = `<div class="tagihan-empty-note">${escapeHtml(message || 'Pilih sekolah dulu')}</div>`;
    updateKelasLabel();
    toggleKelasDropdown(false);
}

function toggleKelasDropdown(forceState) {
    const btn = document.getElementById('filterKelasBtn');
    const dd = document.getElementById('filterKelasDropdown');
    if (!btn || !dd || btn.disabled) return;
    const open = forceState !== undefined ? forceState : !dd.classList.contains('open');
    dd.classList.toggle('open', open);
    btn.classList.toggle('open', open);
}

function toggleSekolahDropdown(forceState) {
    const btn = document.getElementById('filterSekolahBtn');
    const dd = document.getElementById('filterSekolahDropdown');
    if (!btn || !dd) return;
    const open = forceState !== undefined ? forceState : !dd.classList.contains('open');
    dd.classList.toggle('open', open);
    btn.classList.toggle('open', open);
}

function toggleTagihanDropdown(forceState) {
    const btn = document.getElementById('filterTagihanBtn');
    const dd = document.getElementById('filterTagihanDropdown');
    const open = forceState !== undefined ? forceState : !dd.classList.contains('open');
    dd.classList.toggle('open', open);
    btn.classList.toggle('open', open);
}

function toggleBeasiswaDropdown(forceState) {
    const btn = document.getElementById('filterBeasiswaBtn');
    const dd = document.getElementById('filterBeasiswaDropdown');
    if (!btn || !dd) return;
    const open = forceState !== undefined ? forceState : !dd.classList.contains('open');
    dd.classList.toggle('open', open);
    btn.classList.toggle('open', open);
}

async function updateKelasSelect() {
    const sekolahValues = getSelectedSekolahValues();
    const btn = document.getElementById('filterKelasBtn');
    const list = document.getElementById('filterKelasList');

    if (!sekolahValues.length) {
        resetKelasFilter('Pilih sekolah dulu');
        return;
    }

    try {
        const res = await fetch(routes.kelasBySekolah + '?' + buildQuery({ sekolah: sekolahValues }), {
            headers: apiHeaders()
        });
        const json = await parseJsonResponse(res);
        const kelasList = json.success ? (json.data || []) : [];

        if (btn) btn.disabled = false;
        selectedKelas = selectedKelas.filter(v => kelasList.includes(v));

        if (!kelasList.length) {
            if (list) list.innerHTML = '<div class="tagihan-empty-note">Tidak ada kelas untuk unit ini</div>';
            selectedKelas = [];
        } else if (list) {
            list.innerHTML = kelasList.map(v => `
                <label class="tagihan-option">
                    <input type="checkbox" value="${escapeHtml(v)}" ${selectedKelas.includes(v) ? 'checked' : ''}>
                    <span>${escapeHtml(v)}</span>
                </label>
            `).join('');
        }
        updateKelasLabel();
    } catch (e) {
        console.error('updateKelasSelect error', e);
        resetKelasFilter('Gagal memuat kelas');
    }
}

async function loadFilterOptions() {
    try {
        const res = await fetch(routes.filters, {
            headers: apiHeaders(),
            cache: 'no-cache'
        });
        const json = await parseJsonResponse(res);

        if (!json.success) {
            console.error('loadFilterOptions gagal', json);
            return;
        }

        const sekolahList = (json.sekolah || []).map(item => {
            if (item && typeof item === 'object') {
                return {
                    value: String(item.value ?? '').trim(),
                    label: String(item.label ?? item.value ?? '').trim(),
                };
            }
            const v = String(item ?? '').trim();
            return { value: v, label: v };
        }).filter(item => item.value !== '');

        let options = sekolahList;
        if (restrictedSekolahCodes.length && !options.length) {
            options = restrictedSekolahCodes.map(code => ({ value: code, label: code }));
        }

        if (canMultiSelectSekolah) {
            const list = document.getElementById('filterSekolahList');
            if (!options.length) {
                list.innerHTML = '<div class="tagihan-empty-note">Tidak ada data unit</div>';
            } else {
                list.innerHTML = options.map(item => `
                    <label class="tagihan-option">
                        <input type="checkbox" value="${escapeHtml(item.value)}" ${selectedSekolah.includes(item.value) ? 'checked' : ''}>
                        <span>${escapeHtml(item.label || item.value)}</span>
                    </label>
                `).join('');
            }
            selectedSekolah = selectedSekolah.filter(v => options.some(o => o.value === v));
            syncSekolahCheckboxes();
            updateSekolahLabel();
        } else {
            const sekolahSel = document.getElementById('filterSekolah');
            const currentSekolah = sekolahSel.value;
            sekolahSel.innerHTML = '';
            if (restrictedSekolahCodes.length) {
                if (options.length > 1) {
                    const allOpt = document.createElement('option');
                    allOpt.value = '';
                    allOpt.textContent = 'Pilih sekolah/unit';
                    sekolahSel.appendChild(allOpt);
                    sekolahSel.disabled = false;
                } else {
                    sekolahSel.disabled = true;
                }
            } else {
                sekolahSel.disabled = false;
                const allOpt = document.createElement('option');
                allOpt.value = '';
                allOpt.textContent = 'Semua sekolah/unit';
                sekolahSel.appendChild(allOpt);
            }
            options.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.label || item.value;
                sekolahSel.appendChild(opt);
            });
            if (options.length === 1 && restrictedSekolahCodes.length) {
                sekolahSel.value = options[0].value;
            } else if (currentSekolah && options.some(item => item.value === currentSekolah)) {
                sekolahSel.value = currentSekolah;
            } else {
                sekolahSel.value = '';
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

        resetKelasFilter('Pilih sekolah dulu');

        if (getSelectedSekolahValues().length) {
            await updateKelasSelect();
        }

        const tagihanList = document.getElementById('filterTagihanList');
        const tagihanOptions = json.tagihan || [];
        if (isDaftarUlangOnly) {
            selectedTagihan = [lockedTagihan];
            tagihanList.innerHTML = `
                <label class="tagihan-option">
                    <input type="radio" name="tagihan_radio" value="${escapeHtml(lockedTagihan)}" checked disabled>
                    <span>${escapeHtml(lockedTagihan)}</span>
                </label>
            `;
        } else if (!tagihanOptions.length) {
            tagihanList.innerHTML = '<div class="tagihan-empty-note">Tidak ada data tagihan</div>';
        } else {
            tagihanList.innerHTML = tagihanOptions.map(t => `
                <label class="tagihan-option">
                    <input type="radio" name="tagihan_radio" value="${escapeHtml(t)}" ${selectedTagihan.includes(t) ? 'checked' : ''}>
                    <span>${escapeHtml(t)}</span>
                </label>
            `).join('');
        }
        updateTagihanLabel();
        updateSummaryPeriodLabel();
    } catch (e) {
        console.error('loadFilterOptions error', e);
    }
}

function bindSiswaTableDelegation() {
    const root = document.querySelector('.table-card');
    if (!root) return;
    root.addEventListener('click', function (e) {
        const waBtn = e.target.closest('.btn-kirim-wa');
        if (waBtn && root.contains(waBtn)) {
            const custid = waBtn.dataset.custid;
            const siswa = siswaRowsCurrent.find(r => String(r.custid) === String(custid)) || {};
            openKirimTagihanWa(siswa);
            return;
        }

        const catatBtn = e.target.closest('.btn-open-catat');
        if (catatBtn && root.contains(catatBtn)) {
            const custid = catatBtn.dataset.custid;
            const siswa = siswaRowsCurrent.find(r => String(r.custid) === String(custid)) || {};
            openCatatPenagihanModal(siswa);
            return;
        }

        const laporanBtn = e.target.closest('.btn-open-laporan');
        if (laporanBtn && root.contains(laporanBtn)) {
            const custid = laporanBtn.dataset.custid;
            const siswa = siswaRowsCurrent.find(r => String(r.custid) === String(custid)) || { custid };
            openLaporanPenagihanModal(siswa);
            return;
        }

        const btn = e.target.closest('.btn-detail.detail-link');
        if (!btn || !root.contains(btn)) return;
        if (btn.classList.contains('bill-toggle')) return;
        const row = btn.closest('.data-row') || btn.closest('.data-card');
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
    const mobileList = document.getElementById('siswaMobileList');
    const paginationBar = document.getElementById('paginationBar');

    table.style.display = 'table';
    empty.style.display = 'none';
    paginationBar.style.display = 'none';
    mobileList.innerHTML = '<div class="mini-loading"><div class="spinner"></div>Memuat data siswa...</div>';
    tbody.innerHTML = `
        <tr class="loading-row">
            <td colspan="13">
                <div class="spinner" style="margin:0 auto 10px;"></div>
                <div>Memuat data siswa...</div>
            </td>
        </tr>
    `;
    updateExportLinks();
    loadSummary();

    try {
        const q = buildQuery(getDataParams());
        const res = await fetch(routes.siswaList + '?' + q, { headers: apiHeaders() });
        const json = await parseJsonResponse(res);

        if (!json.success) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: json.message || 'Gagal memuat data', confirmButtonColor: '#2563eb' });
            empty.style.display = 'block';
            tbody.innerHTML = '';
            mobileList.innerHTML = '';
            return;
        }

        const rows = json.data || [];
        siswaRowsCurrent = rows;
        document.getElementById('emptyStateText').textContent = json.message || 'Tidak ada data siswa';
        if (!rows.length) {
            table.style.display = 'none';
            empty.style.display = 'block';
            tbody.innerHTML = '';
            mobileList.innerHTML = '';
            paginationBar.style.display = 'none';
            return;
        }

        const startNo = ((json.pagination?.page || 1) - 1) * (json.pagination?.limit || 10) + 1;

        tbody.innerHTML = '';
        mobileList.innerHTML = '';
        rows.forEach((row, i) => {
            const custid = row.custid;
            const paid = Number(row.status) === 1;
            const bolehUjian = bisaUjian(row);
            const tr = document.createElement('tr');
            tr.className = 'data-row';
            tr.dataset.custid = custid;
            tr.innerHTML = `
                <td class="col-no">${startNo + i}</td>
                <td class="nama-cell col-nama" title="${escapeHtml((row.nama || '').toUpperCase())}">
                    <div class="nm-wrap">
                        <span class="nm">${escapeHtml((row.nama || '').toUpperCase())}</span>
                        ${kaderBadgeHtml(row)}
                    </div>
                </td>
                <td class="col-kelas">${escapeHtml(row.kelas || '-')}</td>
                <td class="col-sekolah">${escapeHtml(row.sekolah || '-')}</td>
                <td class="col-money">${formatRupiah(row.total_tagihan)}</td>
                <td class="col-money">${formatRupiah(row.total_terbayar)}</td>
                <td class="col-money col-saldo">${formatRupiah(row.saldo)}</td>
                <td class="col-money col-sisa">${formatRupiah(row.sisa_tagihan)}</td>
                <td class="col-status"><span class="badge ${paid ? 'badge-lunas' : 'badge-belum'}">${paid ? 'Lunas' : 'Belum Lunas'}</span></td>
                <td class="col-ket"><span class="badge ${bolehUjian ? 'badge-lunas' : 'badge-belum'}">${bolehUjian ? 'Bisa Ujian' : 'Belum Bisa Ujian'}</span></td>
                <td class="col-action">
                    <button type="button" class="btn-detail detail-link" data-custid="${custid}">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                </td>
                ${renderActionCells(row, paid)}
            `;
            tbody.appendChild(tr);
            mobileList.insertAdjacentHTML('beforeend', renderMobileCard(row, startNo + i));
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

    } catch (e) {
        console.error('loadSiswaList error', e);
        empty.style.display = 'block';
        tbody.innerHTML = '';
        mobileList.innerHTML = '';
        Swal.fire({ icon: 'error', title: 'Error', text: 'Tidak dapat terhubung ke server', confirmButtonColor: '#2563eb' });
    }
}

document.getElementById('btnSearch').addEventListener('click', () => loadSiswaList(true));

const filterSekolahEl = document.getElementById('filterSekolah');
if (filterSekolahEl) {
    filterSekolahEl.addEventListener('change', function() {
        selectedKelas = [];
        updateKelasSelect().then(() => {
            loadSiswaList(true);
        });
    });
}

const filterSekolahBtnEl = document.getElementById('filterSekolahBtn');
if (filterSekolahBtnEl) {
    filterSekolahBtnEl.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleTagihanDropdown(false);
        toggleBeasiswaDropdown(false);
        toggleKelasDropdown(false);
        toggleSekolahDropdown();
    });
}

const filterSekolahListEl = document.getElementById('filterSekolahList');
if (filterSekolahListEl) {
    filterSekolahListEl.addEventListener('change', (e) => {
        if (!e.target.matches('input[type="checkbox"]')) return;
        selectedSekolah = Array.from(document.querySelectorAll('#filterSekolahList input[type="checkbox"]:checked'))
            .map(cb => cb.value);
        updateSekolahLabel();
    });
}

const btnSekolahClearEl = document.getElementById('btnSekolahClear');
if (btnSekolahClearEl) {
    btnSekolahClearEl.addEventListener('click', () => {
        selectedSekolah = [];
        syncSekolahCheckboxes();
        updateSekolahLabel();
    });
}

const btnSekolahApplyEl = document.getElementById('btnSekolahApply');
if (btnSekolahApplyEl) {
    btnSekolahApplyEl.addEventListener('click', () => {
        toggleSekolahDropdown(false);
        selectedKelas = [];
        updateKelasSelect().then(() => loadSiswaList(true));
    });
}

const filterKelasBtnEl = document.getElementById('filterKelasBtn');
if (filterKelasBtnEl) {
    filterKelasBtnEl.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleTagihanDropdown(false);
        toggleBeasiswaDropdown(false);
        toggleSekolahDropdown(false);
        toggleKelasDropdown();
    });
}

const filterKelasListEl = document.getElementById('filterKelasList');
if (filterKelasListEl) {
    filterKelasListEl.addEventListener('change', (e) => {
        if (!e.target.matches('input[type="checkbox"]')) return;
        selectedKelas = Array.from(document.querySelectorAll('#filterKelasList input[type="checkbox"]:checked'))
            .map(cb => cb.value);
        updateKelasLabel();
    });
}

const btnKelasClearEl = document.getElementById('btnKelasClear');
if (btnKelasClearEl) {
    btnKelasClearEl.addEventListener('click', () => {
        selectedKelas = [];
        syncKelasCheckboxes();
        updateKelasLabel();
    });
}

const btnKelasApplyEl = document.getElementById('btnKelasApply');
if (btnKelasApplyEl) {
    btnKelasApplyEl.addEventListener('click', () => {
        toggleKelasDropdown(false);
        loadSiswaList(true);
    });
}

document.getElementById('filterBta').addEventListener('change', () => loadSiswaList(true));
document.getElementById('filterStatus').addEventListener('change', () => loadSiswaList(true));

document.getElementById('filterTagihanBtn').addEventListener('click', (e) => {
    if (isDaftarUlangOnly) return;
    e.stopPropagation();
    toggleBeasiswaDropdown(false);
    toggleSekolahDropdown(false);
    toggleKelasDropdown(false);
    toggleTagihanDropdown();
});

document.getElementById('filterBeasiswaBtn').addEventListener('click', (e) => {
    e.stopPropagation();
    toggleTagihanDropdown(false);
    toggleSekolahDropdown(false);
    toggleKelasDropdown(false);
    toggleBeasiswaDropdown();
});

document.getElementById('filterTagihanList').addEventListener('change', (e) => {
    if (isDaftarUlangOnly) {
        selectedTagihan = [lockedTagihan];
        updateTagihanLabel();
        return;
    }
    if (e.target.matches('input[type="radio"]')) {
        const val = e.target.value;
        if (e.target.checked) {
            selectedTagihan = [val];
        } else {
            selectedTagihan = [];
        }
        updateTagihanLabel();
    }
});

document.getElementById('filterBeasiswaList').addEventListener('change', (e) => {
    if (!e.target.matches('input[type="checkbox"]')) return;
    selectedBeasiswa = Array.from(document.querySelectorAll('#filterBeasiswaList input[type="checkbox"]:checked'))
        .map(cb => String(cb.value));
    updateBeasiswaLabel();
});

const btnTagihanClearEl = document.getElementById('btnTagihanClear');
if (btnTagihanClearEl) {
    btnTagihanClearEl.addEventListener('click', () => {
        if (isDaftarUlangOnly) return;
        selectedTagihan = [];
        document.querySelectorAll('#filterTagihanList input[type="radio"]').forEach(cb => cb.checked = false);
        updateTagihanLabel();
    });
}

const btnTagihanApplyEl = document.getElementById('btnTagihanApply');
if (btnTagihanApplyEl) {
    btnTagihanApplyEl.addEventListener('click', () => {
        toggleTagihanDropdown(false);
        loadSiswaList(true);
    });
}

const btnBeasiswaClearEl = document.getElementById('btnBeasiswaClear');
if (btnBeasiswaClearEl) {
    btnBeasiswaClearEl.addEventListener('click', () => {
        selectedBeasiswa = [];
        syncBeasiswaCheckboxes();
        updateBeasiswaLabel();
    });
}

const btnBeasiswaApplyEl = document.getElementById('btnBeasiswaApply');
if (btnBeasiswaApplyEl) {
    btnBeasiswaApplyEl.addEventListener('click', () => {
        toggleBeasiswaDropdown(false);
        loadSiswaList(true);
    });
}

document.addEventListener('click', (e) => {
    const ddTagihan = document.getElementById('filterTagihanDropdown');
    const btnTagihan = document.getElementById('filterTagihanBtn');
    if (!isDaftarUlangOnly && ddTagihan && btnTagihan) {
        if (ddTagihan.classList.contains('open') && !ddTagihan.contains(e.target) && e.target !== btnTagihan && !btnTagihan.contains(e.target)) {
            toggleTagihanDropdown(false);
        }
    }

    const ddBeasiswa = document.getElementById('filterBeasiswaDropdown');
    const btnBeasiswa = document.getElementById('filterBeasiswaBtn');
    if (ddBeasiswa && btnBeasiswa) {
        if (ddBeasiswa.classList.contains('open') && !ddBeasiswa.contains(e.target) && e.target !== btnBeasiswa && !btnBeasiswa.contains(e.target)) {
            toggleBeasiswaDropdown(false);
        }
    }

    const ddSekolah = document.getElementById('filterSekolahDropdown');
    const btnSekolah = document.getElementById('filterSekolahBtn');
    if (ddSekolah && btnSekolah) {
        if (ddSekolah.classList.contains('open') && !ddSekolah.contains(e.target) && e.target !== btnSekolah && !btnSekolah.contains(e.target)) {
            toggleSekolahDropdown(false);
        }
    }

    const ddKelas = document.getElementById('filterKelasDropdown');
    const btnKelas = document.getElementById('filterKelasBtn');
    if (ddKelas && btnKelas) {
        if (ddKelas.classList.contains('open') && !ddKelas.contains(e.target) && e.target !== btnKelas && !btnKelas.contains(e.target)) {
            toggleKelasDropdown(false);
        }
    }
});

document.getElementById('btnPrev').addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadSiswaList(); } });
document.getElementById('btnNext').addEventListener('click', () => { if (hasMore) { currentPage++; loadSiswaList(); } });

document.getElementById('pageSizeSelect').addEventListener('change', function() {
    pageSize = parseInt(this.value, 10) || 10;
    loadSiswaList(true);
});

document.getElementById('btnReset').addEventListener('click', () => {
    if (canMultiSelectSekolah) {
        selectedSekolah = [];
        syncSekolahCheckboxes();
        updateSekolahLabel();
        resetKelasFilter('Pilih sekolah dulu');
    } else if (restrictedSekolahCodes.length === 1) {
        selectedKelas = [];
        updateKelasSelect();
    } else {
        const sekolahSel = document.getElementById('filterSekolah');
        if (sekolahSel) sekolahSel.value = '';
        resetKelasFilter('Pilih sekolah dulu');
    }
    document.getElementById('filterBta').value = '';
    document.getElementById('filterStatus').value = '';
    selectedBeasiswa = [];
    syncBeasiswaCheckboxes();
    updateBeasiswaLabel();
    document.getElementById('filterSearch').value = '';
    if (isDaftarUlangOnly) {
        selectedTagihan = [lockedTagihan];
    } else {
        selectedTagihan = [];
        document.querySelectorAll('#filterTagihanList input[type="radio"]').forEach(cb => cb.checked = false);
    }
    updateTagihanLabel();
    pageSize = 10;
    document.getElementById('pageSizeSelect').value = '10';
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

document.getElementById('btnClosePenagihan').addEventListener('click', closeCatatPenagihanModal);
document.getElementById('btnCancelPenagihan').addEventListener('click', closeCatatPenagihanModal);
document.getElementById('penagihanModalBackdrop').addEventListener('click', closeCatatPenagihanModal);
document.getElementById('btnCloseLaporan').addEventListener('click', closeLaporanPenagihanModal);
document.getElementById('laporanModalBackdrop').addEventListener('click', closeLaporanPenagihanModal);
document.getElementById('formCatatPenagihan').addEventListener('submit', submitCatatPenagihan);
document.getElementById('pmCatatan').addEventListener('input', function () {
    document.getElementById('pmCatatanCount').textContent = String(this.value.length);
});

(function init() {
    bindSiswaTableDelegation();
    bindBillToggleDelegation();
    setSummaryPlaceholder();
    loadFilterOptions();
    loadSiswaList();
})();
</script>
@if (session('error'))
<script>Swal.fire({ icon: 'warning', title: 'Perhatian', text: @json(session('error')), confirmButtonColor: '#2563eb' });</script>
@endif
</body>
</html>
</html>

























