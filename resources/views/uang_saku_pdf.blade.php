<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Uang Saku Santri</title>
    <style>
        @page { margin: 22px 20px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
        .pdf-header { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .pdf-header td { vertical-align: middle; }
        .pdf-header .logo-cell { width: 60px; }
        .pdf-header .logo-cell img { width: 52px; height: 52px; }
        .pdf-header .title-cell { text-align: center; }
        .pdf-org { font-size: 11px; font-weight: bold; }
        .pdf-title { margin: 2px 0; font-size: 15px; font-weight: bold; }
        .pdf-meta { font-size: 8.5px; color: #475569; }

        table.info { border-collapse: collapse; margin: 6px 0 0 0; width: 100%; }
        table.info td { border: 1px solid #000; padding: 4px 6px; background: #00b0f0; font-weight: bold; font-size: 10.5px; }
        table.info td.lbl { width: 150px; }
        table.info td.val { width: 260px; }
        table.info td.num { text-align: right; }
        table.info td.blank { border: none; background: transparent; }

        table.ledger { width: 100%; border-collapse: collapse; }
        table.ledger th {
            background: #00b050; color: #fff; font-size: 9.5px; font-weight: bold;
            border: 1px solid #000; padding: 5px 4px; text-align: center; text-transform: uppercase;
        }
        table.ledger td { padding: 3px 6px; font-size: 10px; border-left: 1px solid #000; border-right: 1px solid #000; }
        table.ledger tr.row-alt td { background: #dce6f1; }
        table.ledger tr.awal td { background: #dce6f1; }
        table.ledger tr.last td { border-bottom: 1px solid #000; }
        table.ledger td.c { text-align: center; }
        table.ledger td.r { text-align: right; white-space: nowrap; }
        table.ledger td.saldo { font-weight: bold; }
        .empty-row td { text-align: center; color: #64748b; padding: 10px; }
    </style>
</head>
<body>
    @php
        $fmt = fn ($n) => ((float) $n) == 0.0 ? '-' : number_format((float) $n, 0, '.', ',');
        $fmtSaldo = fn ($n) => number_format((float) $n, 0, '.', ',');
        $fmtTgl = function ($v) {
            $ts = strtotime((string) $v);
            return $ts ? date('d-m-Y', $ts) : (string) $v;
        };
        $bulan = ['', 'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
        $sebelumLabel = '';
        if ($tglDari !== '' && ($ts = strtotime($tglDari))) {
            $sebelumLabel = date('d', $ts) === '01'
                ? $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts)
                : $fmtTgl($tglDari);
        }
        $periodeLabel = ($tglDari !== '' ? $fmtTgl($tglDari) : 'Awal') . ' s/d ' . ($tglSampai !== '' ? $fmtTgl($tglSampai) : 'Sekarang');
        $kelasUnit = trim(($siswa['kelas'] ?? '') . ' - ' . ($siswa['sekolah'] ?? ''), ' -');
    @endphp

    <table class="pdf-header">
        <tr>
            <td class="logo-cell">@if(!empty($logoSrc))<img src="{{ $logoSrc }}" alt="Logo">@endif</td>
            <td class="title-cell">
                <div class="pdf-org">YPI AR-ROHMAH PUTRI</div>
                <div class="pdf-title">LAPORAN UANG SAKU SANTRI</div>
                <div class="pdf-meta">Periode {{ $periodeLabel }} · dicetak {{ $generatedAt }}</div>
            </td>
            <td class="logo-cell"></td>
        </tr>
    </table>

    <table class="info">
        <tr><td class="lbl">NAMA</td><td class="val">{{ strtoupper($siswa['nama'] ?? '-') }}</td><td class="blank"></td></tr>
        <tr><td class="lbl">KELAS</td><td class="val">{{ $kelasUnit !== '' ? strtoupper($kelasUnit) : '-' }}</td><td class="blank"></td></tr>
        <tr><td class="lbl">VA Uangsaku</td><td class="val">{{ $vaUangSaku }}</td><td class="blank"></td></tr>
        <tr><td class="lbl">SALDO UANG SAKU</td><td class="val num">{{ $fmtSaldo($saldoSekarang) }}</td><td class="blank"></td></tr>
    </table>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th style="width:80px;">Tanggal</th>
                <th>Keterangan</th>
                <th style="width:85px;">Kredit<br>(Pemasukan)</th>
                <th style="width:85px;">Debet<br>(Pengeluaran)</th>
                <th style="width:85px;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr class="awal">
                <td></td>
                <td></td>
                <td>SALDO AWAL{{ $sebelumLabel !== '' ? ' (SEBELUM ' . $sebelumLabel . ')' : '' }}</td>
                <td class="r">{{ $saldoAwal > 0 ? $fmt($saldoAwal) : '-' }}</td>
                <td class="r">{{ $saldoAwal < 0 ? $fmt(abs($saldoAwal)) : '-' }}</td>
                <td class="r saldo">{{ $fmtSaldo($saldoAwal) }}</td>
            </tr>
            @forelse($rows as $i => $r)
                <tr class="{{ $i % 2 === 1 ? 'row-alt' : '' }} {{ $loop->last ? 'last' : '' }}">
                    <td class="c">{{ $i + 1 }}</td>
                    <td class="r">{{ $fmtTgl($r['tanggal'] ?? '') }}</td>
                    <td>{{ ($r['keterangan'] ?? '') !== '' ? $r['keterangan'] : '-' }}</td>
                    <td class="r">{{ $fmt($r['kredit'] ?? 0) }}</td>
                    <td class="r">{{ $fmt($r['debet'] ?? 0) }}</td>
                    <td class="r saldo">{{ $fmtSaldo($r['saldo'] ?? 0) }}</td>
                </tr>
            @empty
                <tr class="empty-row last"><td colspan="6">Tidak ada transaksi uang saku pada periode ini</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
