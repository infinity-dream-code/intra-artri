<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Santri</title>
    <style>
        @page { margin: 18px 16px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        .pdf-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .pdf-header td { vertical-align: middle; }
        .pdf-header .logo-cell { width: 72px; }
        .pdf-header .logo-cell img { width: 62px; height: 62px; object-fit: contain; }
        .pdf-header .title-cell { text-align: center; padding-left: 8px; }
        .pdf-org {
            margin: 0;
            font-size: 12px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.02em;
        }
        .pdf-title {
            margin: 3px 0 2px;
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.01em;
        }
        .pdf-meta {
            margin: 0;
            font-size: 9px;
            color: #64748b;
        }
        .header-line {
            border: 0;
            border-top: 1.5px solid #cbd5e1;
            margin: 0 0 12px;
        }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta td { padding: 3px 0; vertical-align: top; }
        .meta .lbl { width: 110px; color: #64748b; }
        .boxes { width: 100%; border-collapse: collapse; margin: 10px 0 14px; }
        .boxes td {
            width: 25%; border: 1px solid #e2e8f0; padding: 8px 10px;
            background: #f8fafc;
        }
        .boxes .k { display: block; font-size: 9px; color: #64748b; text-transform: uppercase; margin-bottom: 2px; }
        .boxes .v { font-size: 12px; font-weight: bold; }
        .section { font-size: 12px; font-weight: bold; margin: 14px 0 6px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 5px 6px; }
        table.data th { background: #fef9c3; font-size: 9px; text-transform: uppercase; text-align: left; }
        table.data td.num { text-align: right; white-space: nowrap; }
        .badge {
            display: inline-block; padding: 1px 6px; border-radius: 4px;
            font-size: 9px; font-weight: bold; color: #fff;
        }
        .ok { background: #16a34a; }
        .no { background: #ef4444; }
        .foot { margin-top: 16px; font-size: 9px; color: #64748b; }
        .muted { color: #94a3b8; }
        .sign-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 28px;
        }
        .sign-wrap td { vertical-align: bottom; }
        .sign-left { width: 55%; font-size: 9px; color: #64748b; }
        .sign-right {
            width: 45%;
            text-align: center;
            font-size: 11px;
            color: #0f172a;
        }
        .sign-right .tgl { margin-bottom: 4px; }
        .sign-right .jabatan { font-weight: bold; margin-top: 2px; }
        .sign-right .instansi { font-weight: bold; margin-top: 2px; }
        .sign-right .ttd-img {
            display: block;
            margin: 4px auto 0;
            height: 62px;
            width: auto;
            max-width: 140px;
            object-fit: contain;
        }
        .sign-right .nama-ttd {
            margin: -10px 0 0;
            padding: 0;
            line-height: 1.1;
            font-weight: bold;
            text-decoration: underline;
        }
        .sign-space {
            height: 72px;
            margin: 6px 0 4px;
        }
    </style>
</head>
<body>
    @php
        $fmt = function ($n) {
            return 'Rp ' . number_format((float) $n, 0, ',', '.');
        };
        $tagihanLabel = !empty($periode['tagihan']) ? implode(', ', $periode['tagihan']) : '-';
        $logoSrc = $logoSrc ?? null;
        $ttdSrc = $ttdSrc ?? null;
        $tanggalCetak = $tanggalCetak ?? (isset($generatedAt) ? explode(' ', $generatedAt)[0] : date('d/m/Y'));
    @endphp

    <table class="pdf-header">
        <tr>
            <td class="logo-cell">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo ARTRI">
                @endif
            </td>
            <td class="title-cell">
                <div class="pdf-org">YPI AR-ROHMAH PUTRI</div>
                <div class="pdf-title">LAPORAN KEUANGAN SANTRI</div>
                <div class="pdf-meta">
                    dicetak {{ $generatedAt }}@if(!empty($admin)) · oleh {{ $admin }}@endif
                </div>
            </td>
        </tr>
    </table>
    <hr class="header-line">

    <table class="meta">
        <tr><td class="lbl">Nama</td><td><strong>{{ strtoupper($siswa['nama'] ?? '-') }}</strong></td></tr>
        <tr><td class="lbl">NIS / No Cust</td><td>{{ $siswa['nocust'] !== '' ? $siswa['nocust'] : '-' }}</td></tr>
        <tr><td class="lbl">Kelas</td><td>{{ $siswa['kelas'] !== '' ? $siswa['kelas'] : '-' }}</td></tr>
        <tr><td class="lbl">Unit</td><td>{{ $siswa['sekolah'] !== '' ? $siswa['sekolah'] : '-' }}</td></tr>
        <tr><td class="lbl">Tahun Ajaran</td><td>{{ $periode['bta'] ?? '-' }}</td></tr>
        <tr><td class="lbl">Tagihan Per</td><td>{{ $tagihanLabel }}</td></tr>
    </table>

    <table class="boxes">
        <tr>
            <td><span class="k">Total Tagihan</span><span class="v">{{ $fmt($summary['total_tagihan'] ?? 0) }}</span></td>
            <td><span class="k">Total Bayar</span><span class="v">{{ $fmt($summary['total_terbayar'] ?? 0) }}</span></td>
            <td><span class="k">Saldo SPP</span><span class="v">{{ $fmt($summary['saldo'] ?? 0) }}</span></td>
            <td><span class="k">Sisa Tagihan</span><span class="v">{{ $fmt($summary['sisa_tagihan'] ?? 0) }}</span></td>
        </tr>
    </table>
    @if(((float) ($summary['saldo_terpakai'] ?? 0)) > 0)
        <div style="margin: 0 0 12px; font-size: 9px; color: #1e40af; background: #dbeafe; padding: 6px 8px; border-radius: 4px;">
            Sisa tagihan {{ $fmt($summary['sisa_tagihan'] ?? 0) }} sudah dikurangi saldo sebesar {{ $fmt($summary['saldo_terpakai'] ?? 0) }}
            dari sisa tagihan awal {{ $fmt($summary['sisa_tagihan_sebelum_saldo'] ?? 0) }}.
        </div>
    @endif

    <div class="section">Daftar Tagihan</div>
    <table class="data">
        <thead>
            <tr>
                <th>Tahun Ajaran</th>
                <th>Nama Tagihan</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Tgl Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bills as $b)
                @php $paid = (int) ($b['status_bayar'] ?? 0) === 1; @endphp
                <tr>
                    <td>{{ $b['bta'] ?? '-' }}</td>
                    <td>{{ $b['nama_tagihan'] ?? '-' }}</td>
                    <td class="num">{{ $fmt($b['jumlah'] ?? 0) }}</td>
                    <td>
                        <span class="badge {{ $paid ? 'ok' : 'no' }}">{{ $paid ? 'Lunas' : 'Belum Lunas' }}</span>
                    </td>
                    <td>{{ $paid ? ($b['tanggal_bayar'] ?? '—') : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Tidak ada data tagihan</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section">Riwayat Penagihan</div>
    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Media</th>
                <th>Hasil</th>
                <th>Rencana Bayar</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $r)
                <tr>
                    <td>{{ $r['tanggal_komunikasi'] ?? '-' }}</td>
                    <td>{{ $r['media_komunikasi'] ?? '-' }}</td>
                    <td>{{ $r['hasil_komunikasi'] ?? '-' }}</td>
                    <td>{{ $r['rencana_pembayaran'] ?? '-' }}</td>
                    <td>{{ $r['catatan'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Belum ada catatan penagihan</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign-wrap">
        <tr>
            <td class="sign-left">
                Dokumen ini dihasilkan otomatis dari aplikasi Monitoring Kepsek.
            </td>
            <td class="sign-right">
                <div class="tgl">Malang, {{ $tanggalCetak }}</div>
                <div class="jabatan">Kepala Keuangan</div>
                <div class="instansi">YPI AR-ROHMAH PUTRI</div>
                @if(!empty($ttdSrc))
                    <img class="ttd-img" src="{{ $ttdSrc }}" alt="Tanda tangan">
                @else
                    <div class="sign-space"></div>
                @endif
                <div class="nama-ttd">Ashim Rosyadi, S.Pdi.</div>
            </td>
        </tr>
    </table>
</body>
</html>
