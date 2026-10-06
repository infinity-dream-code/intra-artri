<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Catatan Penagihan - Monitoring Kepsek</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg:#f1f5f9; --card:#fff; --text:#0f172a; --muted:#64748b; --accent:#2563eb;
            --border:#e2e8f0; --shadow:0 1px 3px rgba(15,23,42,.06);
            --orange:#ea580c; --orange-bg:#fff7ed; --orange-border:#fdba74;
            --blue:#2563eb; --blue-bg:#eff6ff; --blue-border:#93c5fd;
            --red:#dc2626; --red-bg:#fef2f2; --red-border:#fca5a5;
            --green:#16a34a; --green-bg:#f0fdf4; --green-border:#86efac;
            --slate:#475569; --slate-bg:#f8fafc; --slate-border:#cbd5e1;
        }
        * { box-sizing: border-box; }
        body { margin:0; font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); }
        .topbar { background:#fff; border-bottom:1px solid var(--border); padding:14px 20px; display:flex; align-items:center; gap:14px; }
        .topbar a { color:var(--accent); text-decoration:none; font-weight:600; font-size:.88rem; }
        .topbar h1 { margin:0; font-size:1.05rem; font-weight:800; }
        .wrap { max-width:720px; margin:24px auto; padding:0 16px 40px; }
        .siswa-card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow); padding:16px 18px; margin-bottom:14px; }
        .siswa-card .label { font-size:.72rem; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
        .siswa-card .nama { margin-top:4px; font-size:1.05rem; font-weight:800; }
        .siswa-card .sub { margin-top:4px; font-size:.84rem; color:var(--muted); }
        .history-card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow); padding:20px 22px; }
        .history-card h2 { margin:0 0 18px; font-size:1rem; font-weight:800; color:#1e3a5f; }
        .timeline { position: relative; padding-left: 22px; }
        .timeline::before { content:""; position:absolute; left:7px; top:6px; bottom:6px; width:2px; background:#e2e8f0; }
        .tl-item { position: relative; padding-bottom: 22px; }
        .tl-item:last-child { padding-bottom: 0; }
        .tl-item.hidden-item { display: none; }
        .timeline.show-all .tl-item.hidden-item { display: block; }
        .tl-dot { position:absolute; left:-22px; top:4px; width:14px; height:14px; border-radius:50%; border:2px solid #fff; box-shadow:0 0 0 2px currentColor; background: currentColor; }
        .tl-head { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:8px; }
        .tl-date { font-size:.84rem; font-weight:700; color:var(--text); }
        .tl-media { font-size:.8rem; font-weight:600; color:var(--muted); display:inline-flex; align-items:center; gap:6px; }
        .tl-media.wa { color:#16a34a; }
        .tl-media.tel { color:#475569; }
        .tl-badge { display:inline-block; padding:5px 10px; border-radius:999px; font-size:.74rem; font-weight:700; border:1px solid; margin-bottom:8px; }
        .tl-meta { font-size:.8rem; color:var(--muted); margin-bottom:6px; }
        .tl-note { font-size:.82rem; color:#334155; line-height:1.5; }
        .tl-note b { color:var(--text); }
        .tl-admin { margin-top:6px; font-size:.72rem; color:#94a3b8; }
        .tone-orange { color: var(--orange); }
        .tone-orange .tl-badge { background:var(--orange-bg); color:var(--orange); border-color:var(--orange-border); }
        .tone-blue { color: var(--blue); }
        .tone-blue .tl-badge { background:var(--blue-bg); color:var(--blue); border-color:var(--blue-border); }
        .tone-red { color: var(--red); }
        .tone-red .tl-badge { background:var(--red-bg); color:var(--red); border-color:var(--red-border); }
        .tone-green { color: var(--green); }
        .tone-green .tl-badge { background:var(--green-bg); color:var(--green); border-color:var(--green-border); }
        .tone-slate { color: var(--slate); }
        .tone-slate .tl-badge { background:var(--slate-bg); color:var(--slate); border-color:var(--slate-border); }
        .empty { text-align:center; padding:28px 12px; color:var(--muted); font-size:.9rem; }
        .btn-all {
            margin-top: 18px; width: 100%; border: 1px solid var(--border); background:#fff; color:var(--text);
            border-radius: 10px; padding: 11px 14px; font: inherit; font-size: .86rem; font-weight: 700;
            cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-all:hover { background:#f8fafc; }
        .btn-all[hidden] { display: none; }
    </style>
</head>
<body>
@php
    $previewLimit = 3;
    $totalRiwayat = is_array($riwayat) ? count($riwayat) : 0;
@endphp
    <div class="topbar">
        <a href="{{ route('kepsek.tagihan-periode') }}"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h1>Laporan Catatan Penagihan</h1>
    </div>

    <div class="wrap">
        <div class="siswa-card">
            <div class="label">Santri</div>
            <div class="nama">{{ $nama !== '' ? strtoupper($nama) : '-' }}</div>
            <div class="sub">
                @if($kelas !== '') Kelas {{ $kelas }} · @endif
                CUSTID {{ $custid !== '' ? $custid : '-' }}
                @if($bta !== '') · BTA {{ $bta }} @endif
            </div>
        </div>

        <div class="history-card">
            <h2>Riwayat Penagihan Sebelumnya</h2>

            @if($totalRiwayat === 0)
                <div class="empty">
                    <i class="fas fa-inbox" style="font-size:1.6rem;opacity:.35;display:block;margin-bottom:8px;"></i>
                    Belum ada catatan penagihan untuk siswa ini.
                </div>
            @else
                <div class="timeline" id="timeline">
                    @foreach($riwayat as $i => $row)
                        @php
                            $hasil = (string) ($row['hasil_komunikasi'] ?? '');
                            $tone = match ($hasil) {
                                'Meminta waktu pembayaran' => 'tone-orange',
                                'Akan melakukan pembayaran' => 'tone-blue',
                                'Sudah melakukan pembayaran' => 'tone-green',
                                'Tidak dapat dihubungi' => 'tone-red',
                                'Mengalami kendala pembayaran' => 'tone-slate',
                                default => 'tone-slate',
                            };
                            $media = (string) ($row['media_komunikasi'] ?? '');
                            $tgl = $row['tanggal_komunikasi'] ?? null;
                            $tglFmt = $tgl ? date('d/m/Y', strtotime($tgl)) : '-';
                            $rencana = trim((string) ($row['rencana_pembayaran'] ?? ''));
                            $catatan = trim((string) ($row['catatan'] ?? ''));
                            $admin = trim((string) ($row['admin'] ?? ''));
                            $hidden = $i >= $previewLimit;
                        @endphp
                        <div class="tl-item {{ $tone }} {{ $hidden ? 'hidden-item' : '' }}">
                            <span class="tl-dot"></span>
                            <div class="tl-head">
                                <div class="tl-date">{{ $tglFmt }}</div>
                                <div class="tl-media {{ $media === 'WhatsApp' ? 'wa' : 'tel' }}">
                                    @if($media === 'WhatsApp')
                                        <i class="fab fa-whatsapp"></i> WhatsApp
                                    @else
                                        <i class="fas fa-phone"></i> {{ $media !== '' ? $media : 'Telepon' }}
                                    @endif
                                </div>
                            </div>
                            <div class="tl-badge">{{ $hasil !== '' ? $hasil : '-' }}</div>
                            @if($rencana !== '')
                                <div class="tl-meta">Rencana Bayar: {{ $rencana }}</div>
                            @endif
                            @if($catatan !== '')
                                <div class="tl-note"><b>Catatan:</b> {{ $catatan }}</div>
                            @endif
                            @if($admin !== '')
                                <div class="tl-admin">Dicatat oleh: {{ $admin }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($totalRiwayat > $previewLimit)
                    <button type="button" class="btn-all" id="btnShowAll">
                        <i class="fas fa-list"></i> Lihat Semua Riwayat ({{ $totalRiwayat }})
                    </button>
                @endif
            @endif
        </div>
    </div>

<script>
const btn = document.getElementById('btnShowAll');
const timeline = document.getElementById('timeline');
if (btn && timeline) {
    btn.addEventListener('click', () => {
        timeline.classList.add('show-all');
        btn.hidden = true;
    });
}
</script>
</body>
</html>
