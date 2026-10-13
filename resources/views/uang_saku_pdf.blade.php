<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Uang Saku Santri</title>
    <style>
        @page { margin: 18px 16px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        .pdf-header { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .pdf-header td { vertical-align: middle; }
        .pdf-header .logo-cell { width: 72px; }
        .pdf-header .logo-cell img { width: 62px; height: 62px; }
        .pdf-header .title-cell { text-align: center; padding-left: 8px; }
        .pdf-org { font-size: 12px; font-weight: bold; color: #1e3a8a; }
        .pdf-title { margin: 3px 0 2px; font-size: 17px; font-weight: bold; }
        .pdf-meta { font-size: 9px; color: #64748b; }
        .header-line { border: 0; border-top: 1.5px solid #cbd5e1; margin: 0 0 12px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .meta td { padding: 3px 0; vertical-align: top; }
        .meta .lbl { width: 120px; color: #64748b; }
        .boxes { width: 100%; border-collapse: collapse; margin: 8px 0 12px; }
        .boxes td { width: 25%; border: 1px solid #e2e8f0; padding: 7px 9px; background: #f8fafc; }
        .boxes .k { display: block; font-size: 8px; color: #64748b; text-transform: uppercase; margin-bottom: 2px; }
        .boxes .v { font-size: 11px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 4px 5px; }
        table.data th { background: #15803d; color: #fff; font-size: 8.5px; text-transform: uppercase; text-align: center; }
        table.data td.num { text-align: right; white-space: nowrap; }
        table.data td.c { text-align: center; white-space: nowrap; }
        table.data tr.awal td { background: #e0e7ff; font-weight: bold; }
        table.data tr.total td { background: #f1f5f9; font-weight: bold; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        .saldo { font-weight: bold; color: #1e3a8a; }
        .muted { color: #94a3b8; }
        .sign-wrap { width: 100%; border-collapse: collapse; margin-top: 24px; page-break-inside: avoid; }
        .sign-wrap td { vertical-align: bottom; }
        .sign-left { width: 55%; font-size: 9px; color: #64748b; }
        .sign-right { width: 45%; text-align: center; font-size: 10.5px; }
        .sign-right .jabatan, .sign-right .instansi { font-weight: bold; margin-top: 2px; }
        .sign-right .ttd-img { display: block; margin: 4px auto 0; height: 60px; width: auto; }
        .sign-right .nama-ttd { margin: -8px 0 0; font-weight: bold; text-decoration: underline; }
        .sign-space { height: 68px; }
    </style>
</head>
<body>
    @php
        $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
        $fmtTgl = function ($v) {
            $ts = strtotime((string) $v);
            return $ts ? date('d-m-Y', $ts) : (string) $v;
        };
        $periodeLabel = ($tglDari !== '' ? $fmtTgl($tglDari) : 'Awal') . ' s/d ' . ($tglSampai !== '' ? $fmtTgl($tglSampai) : 'Sekarang');
        $tanggalCetak = explode(' ', $generatedAt)[0];
    @endphp

    <table class="pdf-header">
        <tr>
            <td class="logo-cell">
                @if(!empty($logoSrc))<img src="{{ $logoSrc }}" alt="Logo">@endif
            </td>
            <td class="title-cell">
                <div class="pdf-org">YPI AR-ROHMAH PUTRI</div>
                <div class="pdf-title">LAPORAN UANG SAKU SANTRI</div>
                <div class="pdf-meta">dicetak {{ $generatedAt }}@if(!empty($admin)) · oleh {{ $admin }}@endif</div>
            </td>
        </tr>
    </table>
    <hr class="header-line">

    <table class="meta">
        <tr><td class="lbl">Nama</td><td><strong>{{ strtoupper($siswa['nama'] ?? '-') }}</strong></td></tr>
        <tr><td class="lbl">NIS / No Cust</td><td>{{ ($siswa['nis'] ?? '') !== '' ? $siswa['nis'] : '-' }}</td></tr>
        <tr><td class="lbl">Kelas</td><td>{{ ($siswa['kelas'] ?? '') ?: '-' }} - {{ ($siswa['sekolah'] ?? '') ?: '-' }}</td></tr>
        <tr><td class="lbl">VA Uang Saku</td><td>{{ $vaUangSaku }}</td></tr>
        <tr><td class="lbl">Periode</td><td>{{ $periodeLabel }}</td></tr>
    </table>

    <table class="boxes">
        <tr>
            <td><span class="k">Saldo Awal</span><span class="v">Rp {{ $fmt($saldoAwal) }}</span></td>
            <td><span class="k">Total Pemasukan</span><span class="v">Rp {{ $fmt($totalKredit) }}</span></td>
            <td><span class="k">Total Pengeluaran</span><span class="v">Rp {{ $fmt($totalDebet) }}</span></td>
            <td><span class="k">Saldo Uang Saku Saat Ini</span><span class="v">Rp {{ $fmt($saldoSekarang) }}</span></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:28px;">No</th>
                <th style="width:70px;">Tanggal</th>
                <th>Keterangan</th>
                <th style="width:78px;">Kredit<br>(Pemasukan)</th>
                <th style="width:78px;">Debet<br>(Pengeluaran)</th>
                <th style="width:80px;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr class="awal">
                <td></td>
                <td></td>
                <td>SALDO AWAL{{ $tglDari !== '' ? ' (SEBELUM ' . $fmtTgl($tglDari) . ')' : '' }}</td>
                <td class="num">{{ $saldoAwal > 0 ? $fmt($saldoAwal) : '-' }}</td>
                <td class="num">{{ $saldoAwal < 0 ? $fmt(abs($saldoAwal)) : '-' }}</td>
                <td class="num saldo">{{ $fmt($saldoAwal) }}</td>
            </tr>
            @forelse($rows as $i => $r)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td class="c">{{ $fmtTgl($r['tanggal'] ?? '') }}</td>
                    <td>{{ ($r['keterangan'] ?? '') !== '' ? $r['keterangan'] : '-' }}</td>
                    <td class="num">{{ ((float) ($r['kredit'] ?? 0)) > 0 ? $fmt($r['kredit']) : '-' }}</td>
                    <td class="num">{{ ((float) ($r['debet'] ?? 0)) > 0 ? $fmt($r['debet']) : '-' }}</td>
                    <td class="num saldo">{{ $fmt($r['saldo'] ?? 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted" style="text-align:center;">Tidak ada transaksi uang saku pada periode ini</td></tr>
            @endforelse
            <tr class="total">
                <td colspan="3" style="text-align:right;">TOTAL</td>
                <td class="num">{{ $fmt($totalKredit) }}</td>
                <td class="num">{{ $fmt($totalDebet) }}</td>
                <td class="num saldo">{{ $fmt($saldoAkhir) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="sign-wrap">
        <tr>
            <td class="sign-left">Dokumen ini dihasilkan otomatis dari aplikasi Monitoring Kepsek.</td>
            <td class="sign-right">
                <div>Malang, {{ $tanggalCetak }}</div>
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
