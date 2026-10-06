<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Monitoring Kepsek</title>
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
        .toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .toolbar p { margin: 0; font-size: .88rem; color: var(--muted); }
        .btn { border: none; border-radius: 10px; padding: 10px 16px; font-size: .86rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; color: #fff; }
        .btn-primary { background: var(--accent); }
        .btn-secondary { background: #fff; color: var(--text); border: 1px solid var(--border); }
        .btn-danger { background: #b91c1c; }
        .btn-sm { padding: 7px 10px; font-size: .78rem; }
        .table-card { background: var(--card); border-radius: 14px; box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 720px; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid var(--border); font-size: .86rem; }
        th { background: #f8fafc; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); font-weight: 700; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: .72rem; font-weight: 700; background: #e2e8f0; color: #334155; }
        .badge.superadmin { background: #dbeafe; color: #1e40af; }
        .badge.humas { background: #fef3c7; color: #92400e; }
        .muted { color: var(--muted); }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .empty { padding: 40px 20px; text-align: center; color: var(--muted); }
        .modal-backdrop {
            position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 80;
            display: none; align-items: center; justify-content: center; padding: 16px;
        }
        .modal-backdrop.open { display: flex; }
        .modal {
            width: 100%; max-width: 480px; background: #fff; border-radius: 16px;
            box-shadow: 0 20px 50px rgba(15,23,42,.25); overflow: hidden;
        }
        .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 18px; border-bottom: 1px solid var(--border); }
        .modal-header h2 { margin: 0; font-size: 1.05rem; }
        .modal-close { border: none; background: #f1f5f9; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; }
        .modal-body { padding: 18px; display: grid; gap: 12px; }
        .field label { display: block; font-size: .72rem; font-weight: 700; color: var(--muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }
        .field input, .field select { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; font-size: .88rem; font-family: inherit; }
        .field .hint { margin-top: 4px; font-size: .75rem; color: var(--muted); }
        .modal-footer { padding: 14px 18px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 8px; }
        @media (min-width: 960px) {
            .app.drawer-pinned { margin-left: 280px; }
            .drawer-backdrop { display: none !important; }
        }
        @media (max-width: 700px) {
            .desktop-only { display: none; }
            .card-list { display: grid; gap: 10px; padding: 12px; }
            .user-card { border: 1px solid var(--border); border-radius: 12px; padding: 14px; background: #fff; }
            .user-card .row { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 6px; font-size: .84rem; }
            .user-card .label { color: var(--muted); font-size: .72rem; font-weight: 700; text-transform: uppercase; }
        }
        @media (min-width: 701px) {
            .card-list { display: none; }
        }
    </style>
</head>
<body>
@php
    $isSuperadmin = (int) session('user.is_superadmin', 0) === 1
        || strtolower(trim((string) session('user.kelompok', ''))) === 'superadmin';
@endphp
<div class="app" id="app">
    <main class="main">
        <header class="header">
            <button class="burger" id="drawerToggle" type="button" aria-label="Buka/tutup menu" title="Menu">
                <span></span><span></span><span></span>
            </button>
            <h1 class="title">Kelola User</h1>
        </header>

        <div class="toolbar">
            <p>CRUD akun <code>kepsek_user</code> — hanya superadmin</p>
            <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-plus"></i> Tambah User</button>
        </div>

        <div class="table-card">
            <div class="table-wrap desktop-only">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama</th>
                            <th>CODE01</th>
                            <th>Kelompok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <tr><td colspan="6" class="empty">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="card-list" id="userCardList"></div>
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
        <li class="drawer-item"><a href="{{ route('kepsek.kelola-user') }}" class="drawer-link active"><span class="icon"><i class="fas fa-users-gear"></i></span><span>Kelola User</span></a></li>
        <li class="drawer-item"><a href="{{ route('kepsek.import-penagihan') }}" class="drawer-link"><span class="icon"><i class="fas fa-file-import"></i></span><span>Import Penagihan</span></a></li>
        <li class="drawer-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="drawer-link"><span class="icon"><i class="fas fa-right-from-bracket"></i></span><span>Log Out</span></button>
            </form>
        </li>
    </ul>
    <div class="drawer-footer">App Ver : 1.0.0</div>
</aside>

<div class="modal-backdrop" id="userModal">
    <div class="modal" role="dialog" aria-modal="true">
        <div class="modal-header">
            <h2 id="modalTitle">Tambah User</h2>
            <button type="button" class="modal-close" id="modalClose" aria-label="Tutup"><i class="fas fa-xmark"></i></button>
        </div>
        <form id="userForm">
            <div class="modal-body">
                <input type="hidden" id="formId" name="idincrement">
                <div class="field">
                    <label for="formUsername">Username</label>
                    <input type="text" id="formUsername" name="username" required maxlength="100" autocomplete="off">
                </div>
                <div class="field">
                    <label for="formPassword">Password</label>
                    <input type="password" id="formPassword" name="password" maxlength="100" autocomplete="new-password">
                    <div class="hint" id="passwordHint">Wajib diisi untuk user baru</div>
                </div>
                <div class="field">
                    <label for="formNama">Nama</label>
                    <input type="text" id="formNama" name="nama" required maxlength="150">
                </div>
                <div class="field">
                    <label for="formCode01">CODE01 (unit = mst_kelas.unit)</label>
                    <input type="text" id="formCode01" name="code01" maxlength="255" placeholder="kosong = semua unit">
                    <div class="hint">Harus sama persis dengan kolom <b>unit</b> di mst_kelas. Contoh: SMP PUTRI 1, SMA TAHFIDZH. Multi unit: pisah koma.</div>
                </div>
                <div class="field">
                    <label for="formKelompok">Kelompok</label>
                    <select id="formKelompok" name="kelompok">
                        <option value="">(kosong / admin biasa)</option>
                        <option value="0">0 (admin biasa)</option>
                        <option value="superadmin">superadmin</option>
                        <option value="humas">humas</option>
                        <option value="1">1 (humas)</option>
                    </select>
                    <div class="hint">Atau ketik nilai lain di bawah jika perlu</div>
                </div>
                <div class="field">
                    <label for="formKelompokCustom">Kelompok custom (opsional)</label>
                    <input type="text" id="formKelompokCustom" maxlength="50" placeholder="Isi jika tidak ada di daftar">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="modalCancel">Batal</button>
                <button type="submit" class="btn btn-primary" id="modalSave">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
const routes = {
    data: @json(route('kepsek.kelola-user.data')),
    store: @json(route('kepsek.kelola-user.store')),
    update: @json(route('kepsek.kelola-user.update')),
    destroy: @json(route('kepsek.kelola-user.delete')),
};
const csrf = @json(csrf_token());
const currentUsername = @json(session('user.username', ''));

let editMode = false;
let usersCache = [];

const STORAGE_KEY = 'kepsek_drawer_open';
const toggleBtn = document.getElementById('drawerToggle');
const closeBtn = document.getElementById('drawerClose');
const backdrop = document.getElementById('drawerBackdrop');
const drawer = document.getElementById('drawer');
const app = document.getElementById('app');

function isDesktop() {
    return window.matchMedia('(min-width: 960px)').matches;
}
function setDrawer(open) {
    drawer.classList.toggle('open', open);
    backdrop.classList.toggle('open', open && !isDesktop());
    app.classList.toggle('drawer-pinned', open && isDesktop());
    drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    try { localStorage.setItem(STORAGE_KEY, open ? '1' : '0'); } catch (e) {}
}
function toggleDrawer() { setDrawer(!drawer.classList.contains('open')); }
toggleBtn.addEventListener('click', toggleDrawer);
closeBtn.addEventListener('click', () => setDrawer(false));
backdrop.addEventListener('click', () => setDrawer(false));
window.addEventListener('resize', () => setDrawer(drawer.classList.contains('open')));
try { if (localStorage.getItem(STORAGE_KEY) === '1') setDrawer(true); } catch (e) {}

function escapeHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function kelompokBadge(val) {
    const v = String(val ?? '').trim();
    if (!v) return '<span class="muted">—</span>';
    const cls = v.toLowerCase() === 'superadmin' ? 'superadmin' : (v.toLowerCase() === 'humas' || v === '1' ? 'humas' : '');
    return `<span class="badge ${cls}">${escapeHtml(v)}</span>`;
}

function renderUsers(rows) {
    usersCache = rows || [];
    const tbody = document.getElementById('userTableBody');
    const cards = document.getElementById('userCardList');

    if (!usersCache.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty">Belum ada user</td></tr>';
        cards.innerHTML = '<div class="empty">Belum ada user</div>';
        return;
    }

    tbody.innerHTML = usersCache.map(u => {
        const id = u.idincrement;
        const isSelf = String(u.username || '').toLowerCase() === String(currentUsername).toLowerCase();
        return `<tr>
            <td>${escapeHtml(id)}</td>
            <td>${escapeHtml(u.username)}</td>
            <td>${escapeHtml(u.nama)}</td>
            <td>${u.code01 ? escapeHtml(u.code01) : '<span class="muted">—</span>'}</td>
            <td>${kelompokBadge(u.kelompok)}</td>
            <td class="actions">
                <button type="button" class="btn btn-secondary btn-sm" data-edit="${id}"><i class="fas fa-pen"></i> Edit</button>
                ${isSelf ? '' : `<button type="button" class="btn btn-danger btn-sm" data-del="${id}"><i class="fas fa-trash"></i></button>`}
            </td>
        </tr>`;
    }).join('');

    cards.innerHTML = usersCache.map(u => {
        const id = u.idincrement;
        const isSelf = String(u.username || '').toLowerCase() === String(currentUsername).toLowerCase();
        return `<div class="user-card">
            <div class="row"><span class="label">Username</span><strong>${escapeHtml(u.username)}</strong></div>
            <div class="row"><span class="label">Nama</span><span>${escapeHtml(u.nama)}</span></div>
            <div class="row"><span class="label">CODE01</span><span>${u.code01 ? escapeHtml(u.code01) : '—'}</span></div>
            <div class="row"><span class="label">Kelompok</span>${kelompokBadge(u.kelompok)}</div>
            <div class="actions" style="margin-top:10px">
                <button type="button" class="btn btn-secondary btn-sm" data-edit="${id}"><i class="fas fa-pen"></i> Edit</button>
                ${isSelf ? '' : `<button type="button" class="btn btn-danger btn-sm" data-del="${id}"><i class="fas fa-trash"></i></button>`}
            </div>
        </div>`;
    }).join('');
}

async function loadUsers() {
    try {
        const res = await fetch(routes.data, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Gagal memuat');
        renderUsers(json.data || []);
    } catch (e) {
        document.getElementById('userTableBody').innerHTML = `<tr><td colspan="6" class="empty">${escapeHtml(e.message)}</td></tr>`;
        Swal.fire('Gagal', e.message || 'Gagal memuat data', 'error');
    }
}

function openModal(user) {
    editMode = !!user;
    document.getElementById('modalTitle').textContent = editMode ? 'Edit User' : 'Tambah User';
    document.getElementById('formId').value = user ? user.idincrement : '';
    document.getElementById('formUsername').value = user ? (user.username || '') : '';
    document.getElementById('formPassword').value = '';
    document.getElementById('formNama').value = user ? (user.nama || '') : '';
    document.getElementById('formCode01').value = user && user.code01 ? user.code01 : '';
    document.getElementById('formKelompokCustom').value = '';

    const kel = user && user.kelompok != null ? String(user.kelompok) : '';
    const sel = document.getElementById('formKelompok');
    const opts = Array.from(sel.options).map(o => o.value);
    if (kel && opts.includes(kel)) {
        sel.value = kel;
    } else if (kel) {
        sel.value = '';
        document.getElementById('formKelompokCustom').value = kel;
    } else {
        sel.value = '';
    }

    document.getElementById('formPassword').required = !editMode;
    document.getElementById('passwordHint').textContent = editMode
        ? 'Kosongkan jika tidak ingin mengubah password'
        : 'Wajib diisi untuk user baru';

    document.getElementById('userModal').classList.add('open');
}

function closeModal() {
    document.getElementById('userModal').classList.remove('open');
}

document.getElementById('btnAdd').addEventListener('click', () => openModal(null));
document.getElementById('modalClose').addEventListener('click', closeModal);
document.getElementById('modalCancel').addEventListener('click', closeModal);
document.getElementById('userModal').addEventListener('click', (e) => {
    if (e.target.id === 'userModal') closeModal();
});

document.getElementById('app').addEventListener('click', (e) => {
    const editBtn = e.target.closest('[data-edit]');
    const delBtn = e.target.closest('[data-del]');
    if (editBtn) {
        const id = editBtn.getAttribute('data-edit');
        const user = usersCache.find(u => String(u.idincrement) === String(id));
        if (user) openModal(user);
    }
    if (delBtn) {
        const id = delBtn.getAttribute('data-del');
        const user = usersCache.find(u => String(u.idincrement) === String(id));
        if (!user) return;
        Swal.fire({
            title: 'Hapus user?',
            text: `Username: ${user.username}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#b91c1c',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
        }).then(async (r) => {
            if (!r.isConfirmed) return;
            try {
                const res = await fetch(routes.destroy, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ idincrement: Number(user.idincrement) }),
                });
                const json = await res.json();
                if (!json.success) throw new Error(json.message || 'Gagal menghapus');
                Swal.fire('Berhasil', json.message || 'User dihapus', 'success');
                loadUsers();
            } catch (err) {
                Swal.fire('Gagal', err.message || 'Gagal menghapus', 'error');
            }
        });
    }
});

document.getElementById('userForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const customKel = document.getElementById('formKelompokCustom').value.trim();
    const kelompok = customKel || document.getElementById('formKelompok').value;

    const payload = {
        username: document.getElementById('formUsername').value.trim(),
        password: document.getElementById('formPassword').value,
        nama: document.getElementById('formNama').value.trim(),
        code01: document.getElementById('formCode01').value.trim(),
        kelompok: kelompok,
    };

    if (editMode) {
        payload.idincrement = Number(document.getElementById('formId').value);
    }

    const url = editMode ? routes.update : routes.store;
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload),
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Gagal menyimpan');
        closeModal();
        Swal.fire('Berhasil', json.message || 'Tersimpan', 'success');
        loadUsers();
    } catch (err) {
        Swal.fire('Gagal', err.message || 'Gagal menyimpan', 'error');
    }
});

loadUsers();
</script>
</body>
</html>
