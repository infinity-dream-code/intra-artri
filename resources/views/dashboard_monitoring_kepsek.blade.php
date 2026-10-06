<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Keuangan - Monitoring Kepsek</title>
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
            --green: #059669;
            --green-soft: #d1fae5;
            --pink: #e11d48;
            --pink-soft: #ffe4e6;
            --blue: #1d4ed8;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; background: var(--bg); color: var(--text); }
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
        .drawer-logo { width: 48px; height: 48px; border-radius: 12px; overflow: hidden; margin-right: 12px; flex-shrink: 0; }
        .drawer-logo img { width: 100%; height: 100%; object-fit: contain; }
        .drawer-user-name { font-size: .95rem; font-weight: 600; color: #f8fafc; }
        .drawer-user-role { font-size: .78rem; color: #94a3b8; }
        .drawer-close { width: 34px; height: 34px; border: none; border-radius: 10px; flex-shrink: 0; background: rgba(148,163,184,.15); color: #e2e8f0; cursor: pointer; }
        .drawer-divider { height: 1px; background: rgba(255,255,255,.1); margin: 16px 0; }
        .drawer-menu-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .12em; color: #64748b; margin-bottom: 10px; padding-left: 14px; font-weight: 600; }
        .drawer-menu { list-style: none; padding: 0; margin: 0; flex: 1; }
        .drawer-item { margin-bottom: 4px; }
        .drawer-link { display: flex; align-items: center; padding: 12px 14px; border-radius: 12px; color: #cbd5e1; text-decoration: none; font-size: .9rem; font-weight: 500; border: none; background: transparent; width: 100%; cursor: pointer; font-family: inherit; }
        .drawer-link span.icon { width: 24px; display: inline-flex; justify-content: center; margin-right: 12px; color: #94a3b8; }
        .drawer-link:hover, .drawer-link.active { background: linear-gradient(135deg,rgba(37,99,235,.3) 0%,rgba(29,78,216,.2) 100%); color: #dbeafe; }
        .drawer-link.active span.icon { color: #93c5fd; }
        .drawer-footer { font-size: .75rem; color: #64748b; margin-top: auto; padding-top: 16px; }

        .main { padding: 20px 24px 40px; max-width: 1180px; margin: 0 auto; }
        .topbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .burger { width: 36px; height: 36px; border: none; border-radius: 10px; background: #fff; padding: 0; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; box-shadow: var(--shadow); }
        .burger span { display: block; width: 18px; height: 2.5px; border-radius: 2px; background: var(--accent); }
        .title { margin: 0; font-size: 1.4rem; font-weight: 700; }
        .asof { font-size: .84rem; color: var(--muted); white-space: nowrap; }

        .filters {
            display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px;
            background: var(--card); border: 1px solid var(--border); border-radius: 14px;
            padding: 16px 18px; margin-bottom: 16px; box-shadow: var(--shadow);
        }
        .field label { display: block; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); margin-bottom: 6px; font-weight: 700; }
        .field select, .tagihan-select-btn {
            width: 100%; border: 1px solid var(--border); background: #fff; color: var(--text);
            border-radius: 10px; padding: 10px 12px; font-size: .88rem; font-family: inherit;
            appearance: none; -webkit-appearance: none;
        }
        .field select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 34px;
        }
        .field select option { background: #fff; color: var(--text); }
        .field-tagihan { position: relative; }
        .tagihan-select-btn { display: flex; align-items: center; justify-content: space-between; gap: 8px; cursor: pointer; text-align: left; }
        .tagihan-select-btn:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .tagihan-dropdown {
            display: none; position: absolute; z-index: 20; top: calc(100% + 6px); left: 0; right: 0;
            background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden;
            box-shadow: 0 12px 28px rgba(15,23,42,.12);
        }
        .tagihan-dropdown.open { display: block; }
        .tagihan-dropdown-inner { max-height: 240px; overflow: auto; padding: 8px; }
        .tagihan-option { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 8px; cursor: pointer; font-size: .86rem; }
        .tagihan-option:hover { background: #f1f5f9; }
        .tagihan-dropdown-actions { display: flex; justify-content: flex-end; gap: 8px; padding: 8px 10px; border-top: 1px solid var(--border); }
        .tagihan-clear-btn, .tagihan-apply-btn { border: none; border-radius: 8px; padding: 7px 12px; font-size: .78rem; font-weight: 700; cursor: pointer; font-family: inherit; }
        .tagihan-clear-btn { background: #fff; color: var(--muted); border: 1px solid var(--border); }
        .tagihan-apply-btn { background: var(--accent); color: #fff; }
        .tagihan-empty-note { color: var(--muted); font-size: .82rem; padding: 12px; }

        .summary-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 14px;
            padding: 20px 20px 16px; margin-bottom: 16px; box-shadow: var(--shadow);
        }
        .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 16px; }
        .kpi { min-width: 0; }
        .kpi .k {
            display: flex; align-items: center; gap: 8px;
            font-size: .72rem; color: var(--muted); font-weight: 700;
            text-transform: uppercase; letter-spacing: .04em;
        }
        .kpi .k::before { content: ''; width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; background: var(--accent); }
        .kpi.bayar .k::before { background: var(--green); }
        .kpi.sisa .k::before { background: var(--pink); }
        .kpi .v { margin-top: 8px; font-size: clamp(1.25rem, 2.4vw, 1.85rem); font-weight: 800; letter-spacing: -.03em; color: var(--text); line-height: 1.15; }
        .kpi.bayar .v { color: var(--green); }
        .kpi.sisa .v { color: var(--pink); }
        .kpi .s { margin-top: 6px; font-size: .84rem; color: var(--muted); }
        .kpi-saldo {
            display: inline-flex; align-items: center; gap: 6px; margin-top: 10px;
            padding: 5px 10px; border-radius: 999px; background: #eff6ff; color: var(--blue);
            font-size: .78rem; font-weight: 600;
        }

        .progress-bar { display: flex; height: 34px; border-radius: 999px; overflow: hidden; background: var(--pink-soft); }
        .progress-paid {
            background: linear-gradient(90deg, #047857, #10b981);
            display: flex; align-items: center; padding: 0 14px;
            font-size: .8rem; font-weight: 800; color: #fff; white-space: nowrap;
            min-width: 0; transition: width .35s ease;
        }
        .progress-remain {
            flex: 1; display: flex; align-items: center; justify-content: flex-end; padding: 0 14px;
            font-size: .8rem; font-weight: 800; color: #9f1239; white-space: nowrap;
        }

        .panel { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 16px 16px 8px; box-shadow: var(--shadow); }
        .panel-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
        .panel-head h2 { margin: 0; font-size: 1rem; font-weight: 700; }
        .panel-note { font-size: .78rem; color: var(--muted); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .88rem; }
        th, td { padding: 12px 10px; text-align: left; border-bottom: 1px solid var(--border); vertical-align: middle; }
        th {
            color: var(--muted); font-size: .7rem; text-transform: uppercase; letter-spacing: .04em;
            font-weight: 700; cursor: pointer; user-select: none; white-space: nowrap; background: #f8fafc;
        }
        th.num, td.num { text-align: right; }
        th:hover { color: var(--text); }
        td.num { font-variant-numeric: tabular-nums; white-space: nowrap; }
        td.piutang { color: var(--pink); font-weight: 700; }
        .mini-bar { margin-top: 6px; height: 6px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
        .mini-bar > span { display: block; height: 100%; background: linear-gradient(90deg,#047857,#10b981); border-radius: 999px; }
        .pct { font-size: .72rem; color: var(--muted); margin-top: 4px; }
        tfoot td { font-weight: 800; border-bottom: none; background: #f8fafc; }
        .empty { text-align: center; color: var(--muted); padding: 28px 12px !important; }

        @media (max-width: 900px) {
            .filters, .kpi-grid { grid-template-columns: 1fr; }
            .main { padding: 16px; }
            .asof { width: 100%; padding-left: 48px; }
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
        <div class="topbar">
            <div class="topbar-left">
                <button class="burger" id="drawerToggle" type="button" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
                <h1 class="title">Dashboard keuangan</h1>
            </div>
            <div class="asof" id="asOfLabel">Memuat...</div>
        </div>

        <div class="filters">
            <div class="field">
                <label for="filterBta">Tahun ajaran</label>
                <select id="filterBta"><option value="">Memuat...</option></select>
            </div>
            <div class="field field-tagihan">
                <label for="filterTagihanBtn">Tagihan Per</label>
                <button type="button" id="filterTagihanBtn" class="tagihan-select-btn" {{ ($restrictedTagihan ?? false) ? 'disabled' : '' }}>
                    <span id="filterTagihanLabel">{{ ($restrictedTagihan ?? false) ? ($lockedTagihan ?? 'BIAYA Daftar Ulang Ajaran Baru') : 'Pilih tagihan' }}</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="tagihan-dropdown" id="filterTagihanDropdown">
                    <div class="tagihan-dropdown-inner" id="filterTagihanList">
                        <div class="tagihan-empty-note">Memuat...</div>
                    </div>
                    @unless($restrictedTagihan ?? false)
                    <div class="tagihan-dropdown-actions">
                        <button type="button" id="btnTagihanClear" class="tagihan-clear-btn">Bersihkan</button>
                        <button type="button" id="btnTagihanApply" class="tagihan-apply-btn">Terapkan</button>
                    </div>
                    @endunless
                </div>
            </div>
            <div class="field field-tagihan" id="fieldSekolahMulti" @unless($canMultiSelectSekolah ?? false) style="display:none" @endunless>
                <label for="filterSekolahBtn">Sekolah</label>
                <button type="button" id="filterSekolahBtn" class="tagihan-select-btn">
                    <span id="filterSekolahLabel">Semua sekolah</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="tagihan-dropdown" id="filterSekolahDropdown">
                    <div class="tagihan-dropdown-inner" id="filterSekolahList">
                        <div class="tagihan-empty-note">Memuat...</div>
                    </div>
                    <div class="tagihan-dropdown-actions">
                        <button type="button" id="btnSekolahClear" class="tagihan-clear-btn">Semua</button>
                        <button type="button" id="btnSekolahApply" class="tagihan-apply-btn">Terapkan</button>
                    </div>
                </div>
            </div>
            <div class="field" id="fieldSekolahSingle" @if($canMultiSelectSekolah ?? false) style="display:none" @endif>
                <label for="filterSekolah">Sekolah</label>
                <select id="filterSekolah"><option value="">Memuat...</option></select>
            </div>
        </div>

        <div class="summary-card">
            <div class="kpi-grid">
                <div class="kpi tagihan">
                    <div class="k">Total tagihan</div>
                    <div class="v" id="kpiTagihan">-</div>
                    <div class="s" id="kpiSiswa">untuk - siswa/santri</div>
                    <div class="kpi-saldo"><i class="fas fa-wallet"></i> Jml saldo: <strong id="kpiSaldo">-</strong></div>
                </div>
                <div class="kpi bayar">
                    <div class="k">Total terbayar</div>
                    <div class="v" id="kpiTerbayar">-</div>
                    <div class="s" id="kpiPctBayar">- dari total tagihan</div>
                </div>
                <div class="kpi sisa">
                    <div class="k">Sisa tagihan</div>
                    <div class="v" id="kpiSisa">-</div>
                    <div class="s" id="kpiSiswaPiutang">- siswa/santri belum lunas</div>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-paid" id="barPaid" style="width:0%">Terbayar 0%</div>
                <div class="progress-remain" id="barRemain">Sisa 100%</div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Rincian per sekolah</h2>
                <div class="panel-note">Klik judul kolom untuk mengurutkan</div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th data-sort="sekolah">Jenjang / sekolah</th>
                            <th data-sort="total_siswa" class="num">Siswa/santri</th>
                            <th data-sort="total_tagihan" class="num">Total tagihan</th>
                            <th data-sort="total_terbayar" class="num">Total terbayar</th>
                            <th data-sort="total_piutang" class="num">Total piutang</th>
                            <th data-sort="total_siswa_piutang" class="num">Siswa berpiutang</th>
                        </tr>
                    </thead>
                    <tbody id="rincianBody">
                        <tr><td colspan="6" class="empty">Memuat data...</td></tr>
                    </tbody>
                    <tfoot id="rincianFoot" style="display:none;"></tfoot>
                </table>
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
        <button type="button" class="drawer-close" id="drawerClose" aria-label="Tutup menu"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="drawer-divider"></div>
    <div class="drawer-menu-label">Menu</div>
    <ul class="drawer-menu">
        <li class="drawer-item"><a href="{{ route('dashboard.monitoring-kepsek') }}" class="drawer-link active"><span class="icon"><i class="fas fa-chart-pie"></i></span><span>Dashboard</span></a></li>
        <li class="drawer-item"><a href="{{ route('kepsek.tagihan-periode') }}" class="drawer-link"><span class="icon"><i class="fas fa-file-invoice-dollar"></i></span><span>Tagihan Per</span></a></li>
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
    filters: @json(route('kepsek.tagihan-periode.filters')),
    data: @json(route('dashboard.monitoring-kepsek.data')),
};
const restrictedSekolah = @json($restrictedSekolah ?? null);
const restrictedSekolahCodes = (restrictedSekolah ? String(restrictedSekolah).split(',').map(s => s.trim()).filter(Boolean) : []);
const canMultiSelectSekolah = @json((bool) ($canMultiSelectSekolah ?? false));
const isDaftarUlangOnly = @json((bool) ($restrictedTagihan ?? false));
const lockedTagihan = @json($lockedTagihan ?? 'BIAYA Daftar Ulang Ajaran Baru');

let selectedTagihan = isDaftarUlangOnly ? [lockedTagihan] : [];
let selectedSekolah = [];
let rowsCache = [];
let sortKey = 'sekolah';
let sortAsc = true;

function escapeHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatRupiah(val) {
    return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(val) || 0);
}
function formatAngka(val) {
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(val) || 0);
}
function buildQuery(params) {
    const q = new URLSearchParams();
    Object.entries(params).forEach(([k, v]) => {
        if (Array.isArray(v)) v.forEach(item => { if (item !== '' && item != null) q.append(k + '[]', item); });
        else if (v !== '' && v != null) q.set(k, v);
    });
    return q.toString();
}
function getSelectedSekolahValues() {
    if (canMultiSelectSekolah) return selectedSekolah.slice();
    const el = document.getElementById('filterSekolah');
    const v = el ? String(el.value || '').trim() : '';
    return v ? [v] : [];
}
function currentMonthTagihanName() {
    const months = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER'];
    return 'BULAN ' + months[new Date().getMonth()];
}
function pickCurrentMonthTagihan(tags) {
    const list = (tags || []).map(t => String(t ?? '').trim()).filter(Boolean);
    if (!list.length) return null;
    const target = currentMonthTagihanName();
    const exact = list.find(t => t.toUpperCase() === target);
    if (exact) return exact;
    const monthOnly = target.replace(/^BULAN\s+/, '');
    return list.find(t => t.toUpperCase().includes(monthOnly)) || null;
}
function updateTagihanLabel() {
    const label = document.getElementById('filterTagihanLabel');
    if (!selectedTagihan.length) label.textContent = 'Pilih tagihan';
    else label.textContent = selectedTagihan[0];
}
function updateSekolahLabel() {
    const label = document.getElementById('filterSekolahLabel');
    if (!label) return;
    if (!selectedSekolah.length) label.textContent = 'Semua sekolah';
    else if (selectedSekolah.length === 1) label.textContent = selectedSekolah[0];
    else label.textContent = selectedSekolah.length + ' sekolah dipilih';
}
function toggleDropdown(btnId, ddId, forceState) {
    const btn = document.getElementById(btnId);
    const dd = document.getElementById(ddId);
    if (!btn || !dd || btn.disabled) return;
    const open = forceState !== undefined ? forceState : !dd.classList.contains('open');
    dd.classList.toggle('open', open);
    btn.classList.toggle('open', open);
}

async function loadFilters() {
    const res = await fetch(routes.filters, { headers: { 'Accept': 'application/json' }, cache: 'no-cache' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal memuat filter');

    const btaSel = document.getElementById('filterBta');
    btaSel.innerHTML = '<option value="">Pilih tahun ajaran</option>';
    (json.bta || []).forEach(v => {
        const opt = document.createElement('option');
        opt.value = v; opt.textContent = v;
        btaSel.appendChild(opt);
    });
    if ((json.bta || []).length) btaSel.value = json.bta[0];

    const sekolahList = (json.sekolah || []).map(item => {
        if (item && typeof item === 'object') return { value: String(item.value ?? '').trim(), label: String(item.label ?? item.value ?? '').trim() };
        const v = String(item ?? '').trim();
        return { value: v, label: v };
    }).filter(i => i.value);
    let options = sekolahList;
    if (restrictedSekolahCodes.length && !options.length) {
        options = restrictedSekolahCodes.map(c => ({ value: c, label: c }));
    }

    if (canMultiSelectSekolah) {
        const list = document.getElementById('filterSekolahList');
        list.innerHTML = options.length
            ? options.map(i => `<label class="tagihan-option"><input type="checkbox" value="${escapeHtml(i.value)}"><span>${escapeHtml(i.label)}</span></label>`).join('')
            : '<div class="tagihan-empty-note">Tidak ada data unit</div>';
        updateSekolahLabel();
    } else {
        const sel = document.getElementById('filterSekolah');
        sel.innerHTML = '';
        if (restrictedSekolahCodes.length === 1) {
            const opt = document.createElement('option');
            opt.value = options[0]?.value || restrictedSekolahCodes[0];
            opt.textContent = options[0]?.label || restrictedSekolahCodes[0];
            sel.appendChild(opt);
            sel.value = opt.value;
            sel.disabled = true;
        } else {
            sel.innerHTML = '<option value="">Semua sekolah</option>';
            options.forEach(i => {
                const opt = document.createElement('option');
                opt.value = i.value; opt.textContent = i.label;
                sel.appendChild(opt);
            });
        }
    }

    const tagihanList = document.getElementById('filterTagihanList');
    if (isDaftarUlangOnly) {
        selectedTagihan = [lockedTagihan];
        tagihanList.innerHTML = `<label class="tagihan-option"><input type="radio" name="tagihan_radio" value="${escapeHtml(lockedTagihan)}" checked disabled><span>${escapeHtml(lockedTagihan)}</span></label>`;
    } else {
        const tags = json.tagihan || [];
        if (!selectedTagihan.length) {
            const current = pickCurrentMonthTagihan(tags);
            if (current) selectedTagihan = [current];
        }
        tagihanList.innerHTML = tags.length
            ? tags.map(t => `<label class="tagihan-option"><input type="radio" name="tagihan_radio" value="${escapeHtml(t)}" ${selectedTagihan.includes(t) ? 'checked' : ''}><span>${escapeHtml(t)}</span></label>`).join('')
            : '<div class="tagihan-empty-note">Tidak ada data tagihan</div>';
    }
    updateTagihanLabel();
}

function renderDashboard(summary, rows) {
    const totalTagihan = Number(summary.total_tagihan || 0);
    const totalTerbayar = Number(summary.total_terbayar || 0);
    const totalPiutang = Number(summary.total_piutang || 0);
    const totalSiswa = Number(summary.total_siswa || 0);
    const totalSiswaPiutang = Number(summary.total_siswa_piutang || 0);
    const totalSaldo = Number(summary.total_saldo || 0);
    const pct = totalTagihan > 0 ? Math.round((totalTerbayar / totalTagihan) * 100) : 0;
    const pctSisa = Math.max(0, 100 - pct);

    document.getElementById('kpiTagihan').textContent = formatRupiah(totalTagihan);
    document.getElementById('kpiTerbayar').textContent = formatRupiah(totalTerbayar);
    document.getElementById('kpiSisa').textContent = formatRupiah(totalPiutang);
    document.getElementById('kpiSiswa').textContent = `untuk ${formatAngka(totalSiswa)} siswa/santri`;
    document.getElementById('kpiPctBayar').textContent = `${pct}% dari total tagihan`;
    document.getElementById('kpiSiswaPiutang').textContent = `${formatAngka(totalSiswaPiutang)} siswa/santri belum lunas`;
    document.getElementById('kpiSaldo').textContent = formatRupiah(totalSaldo);

    const barPaid = document.getElementById('barPaid');
    barPaid.style.width = pct + '%';
    barPaid.textContent = `Terbayar ${pct}%`;
    document.getElementById('barRemain').textContent = `Sisa ${pctSisa}%`;

    rowsCache = rows.slice();
    renderTable();
}

function renderTable() {
    const tbody = document.getElementById('rincianBody');
    const tfoot = document.getElementById('rincianFoot');
    const rows = rowsCache.slice().sort((a, b) => {
        const av = a[sortKey], bv = b[sortKey];
        if (typeof av === 'string') return sortAsc ? av.localeCompare(bv) : bv.localeCompare(av);
        return sortAsc ? (av - bv) : (bv - av);
    });

    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty">Belum ada data. Pilih tahun ajaran.</td></tr>';
        tfoot.style.display = 'none';
        return;
    }

    tbody.innerHTML = rows.map(r => `
        <tr>
            <td>${escapeHtml(r.sekolah)}</td>
            <td class="num">${formatAngka(r.total_siswa)}</td>
            <td class="num">${formatRupiah(r.total_tagihan)}</td>
            <td class="num">
                ${formatRupiah(r.total_terbayar)}
                <div class="mini-bar"><span style="width:${Number(r.pct_terbayar || 0)}%"></span></div>
                <div class="pct">${Number(r.pct_terbayar || 0)}%</div>
            </td>
            <td class="num piutang">${formatRupiah(r.total_piutang)}</td>
            <td class="num">${formatAngka(r.total_siswa_piutang)}</td>
        </tr>
    `).join('');

    const sum = rows.reduce((acc, r) => {
        acc.siswa += Number(r.total_siswa || 0);
        acc.tagihan += Number(r.total_tagihan || 0);
        acc.bayar += Number(r.total_terbayar || 0);
        acc.piutang += Number(r.total_piutang || 0);
        acc.siswaPiutang += Number(r.total_siswa_piutang || 0);
        return acc;
    }, { siswa: 0, tagihan: 0, bayar: 0, piutang: 0, siswaPiutang: 0 });
    const pct = sum.tagihan > 0 ? Math.round((sum.bayar / sum.tagihan) * 100) : 0;

    tfoot.style.display = '';
    tfoot.innerHTML = `
        <tr>
            <td>Total</td>
            <td class="num">${formatAngka(sum.siswa)}</td>
            <td class="num">${formatRupiah(sum.tagihan)}</td>
            <td class="num">${formatRupiah(sum.bayar)} <div class="pct">${pct}%</div></td>
            <td class="num piutang">${formatRupiah(sum.piutang)}</td>
            <td class="num">${formatAngka(sum.siswaPiutang)}</td>
        </tr>
    `;
}

async function loadDashboard() {
    const bta = document.getElementById('filterBta').value;
    document.getElementById('asOfLabel').textContent = 'Data per ' + new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    document.getElementById('rincianBody').innerHTML = '<tr><td colspan="6" class="empty">Memuat data...</td></tr>';

    const q = buildQuery({ bta, tagihan: selectedTagihan, sekolah: getSelectedSekolahValues() });
    const res = await fetch(routes.data + (q ? '?' + q : ''), { headers: { 'Accept': 'application/json' } });
    const json = await res.json();
    if (!json.success) {
        document.getElementById('rincianBody').innerHTML = `<tr><td colspan="6" class="empty">${escapeHtml(json.message || 'Gagal memuat data')}</td></tr>`;
        return;
    }
    renderDashboard(json.summary || {}, json.by_sekolah || []);
}

document.getElementById('filterBta').addEventListener('change', loadDashboard);
document.getElementById('filterSekolah')?.addEventListener('change', loadDashboard);
document.getElementById('filterTagihanBtn').addEventListener('click', (e) => {
    if (isDaftarUlangOnly) return;
    e.stopPropagation();
    toggleDropdown('filterSekolahBtn', 'filterSekolahDropdown', false);
    toggleDropdown('filterTagihanBtn', 'filterTagihanDropdown');
});
document.getElementById('filterSekolahBtn')?.addEventListener('click', (e) => {
    e.stopPropagation();
    toggleDropdown('filterTagihanBtn', 'filterTagihanDropdown', false);
    toggleDropdown('filterSekolahBtn', 'filterSekolahDropdown');
});
document.getElementById('filterTagihanList').addEventListener('change', (e) => {
    if (isDaftarUlangOnly) {
        selectedTagihan = [lockedTagihan];
        updateTagihanLabel();
        return;
    }
    if (!e.target.matches('input[type="radio"]')) return;
    const val = String(e.target.value || '').trim();
    selectedTagihan = val ? [val] : [];
    updateTagihanLabel();
});
document.getElementById('filterSekolahList')?.addEventListener('change', (e) => {
    if (!e.target.matches('input[type="checkbox"]')) return;
    selectedSekolah = Array.from(document.querySelectorAll('#filterSekolahList input[type="checkbox"]:checked')).map(cb => cb.value);
    updateSekolahLabel();
});
document.getElementById('btnTagihanClear')?.addEventListener('click', () => {
    selectedTagihan = [];
    document.querySelectorAll('#filterTagihanList input[type="radio"]').forEach(cb => cb.checked = false);
    updateTagihanLabel();
});
document.getElementById('btnTagihanApply')?.addEventListener('click', () => {
    toggleDropdown('filterTagihanBtn', 'filterTagihanDropdown', false);
    loadDashboard();
});
document.getElementById('btnSekolahClear')?.addEventListener('click', () => {
    selectedSekolah = [];
    document.querySelectorAll('#filterSekolahList input[type="checkbox"]').forEach(cb => cb.checked = false);
    updateSekolahLabel();
});
document.getElementById('btnSekolahApply')?.addEventListener('click', () => {
    toggleDropdown('filterSekolahBtn', 'filterSekolahDropdown', false);
    loadDashboard();
});
document.addEventListener('click', (e) => {
    ['filterTagihan', 'filterSekolah'].forEach(prefix => {
        const dd = document.getElementById(prefix + 'Dropdown');
        const btn = document.getElementById(prefix + 'Btn');
        if (dd && btn && dd.classList.contains('open') && !dd.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
            toggleDropdown(prefix + 'Btn', prefix + 'Dropdown', false);
        }
    });
});
document.querySelectorAll('th[data-sort]').forEach(th => {
    th.addEventListener('click', () => {
        const key = th.getAttribute('data-sort');
        if (sortKey === key) sortAsc = !sortAsc;
        else { sortKey = key; sortAsc = true; }
        renderTable();
    });
});

const toggleBtn = document.getElementById('drawerToggle');
const closeBtn = document.getElementById('drawerClose');
const backdrop = document.getElementById('drawerBackdrop');
const drawer = document.getElementById('drawer');
const app = document.getElementById('app');
function openDrawer() { drawer.classList.add('open'); backdrop.classList.add('open'); drawer.setAttribute('aria-hidden', 'false'); app.classList.toggle('drawer-pinned', window.matchMedia('(min-width: 960px)').matches); }
function closeDrawer() { drawer.classList.remove('open'); backdrop.classList.remove('open'); drawer.setAttribute('aria-hidden', 'true'); app.classList.remove('drawer-pinned'); }
toggleBtn.addEventListener('click', () => drawer.classList.contains('open') ? closeDrawer() : openDrawer());
closeBtn.addEventListener('click', closeDrawer);
backdrop.addEventListener('click', closeDrawer);
if (window.matchMedia('(min-width: 960px)').matches) openDrawer();

(async function init() {
    try {
        await loadFilters();
        await loadDashboard();
    } catch (e) {
        console.error(e);
        document.getElementById('rincianBody').innerHTML = `<tr><td colspan="6" class="empty">${escapeHtml(e.message || 'Gagal memuat')}</td></tr>`;
    }
})();
</script>
@if (session('login_success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('login_success')), confirmButtonColor: '#2563eb' });
</script>
@endif
</body>
</html>
