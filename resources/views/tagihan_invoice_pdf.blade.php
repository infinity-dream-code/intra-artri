<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Pembayaran</title>
    <style>
        @page { margin: 14px 16px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        .pdf-header { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .pdf-header td { vertical-align: middle; }
        .pdf-header .logo-cell { width: 52px; }
        .pdf-header .logo-cell img { width: 46px; height: 46px; object-fit: contain; }
        .pdf-header .title-cell { text-align: center; padding-left: 6px; }
        .pdf-org { margin: 0; font-size: 10px; font-weight: bold; color: #1e3a8a; }
        .pdf-title { margin: 1px 0; font-size: 14px; font-weight: bold; color: #0f172a; }
        .pdf-meta { margin: 0; font-size: 8px; color: #64748b; }
        .header-line { border: 0; border-top: 1.5px solid #cbd5e1; margin: 0 0 8px; }
        .badge {
            display: inline-block; padding: 1px 6px; border-radius: 3px;
            font-size: 8px; font-weight: bold; color: #fff; background: #16a34a;
        }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { padding: 2px 4px 2px 0; vertical-align: top; }
        .meta .lbl { width: 78px; color: #64748b; white-space: nowrap; }
        .meta .val { width: 42%; padding-right: 10px; }
        .section { font-size: 10px; font-weight: bold; margin: 8px 0 4px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 4px 6px; }
        table.data th { background: #fef9c3; font-size: 8px; text-transform: uppercase; text-align: left; }
        table.data td.num { text-align: right; white-space: nowrap; }
        table.data tfoot td { font-weight: bold; background: #f8fafc; }
        .foot { margin-top: 10px; font-size: 8px; color: #64748b; }
    </style>
</head>
<body>
@php
    $fmt = function ($n) {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    };
    $fmtDate = function ($val) {
        $val = trim((string) $val);
        if ($val === '') return '-';
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $val, $m)) {
            return $m[3] . '/' . $m[2] . '/' . $m[1];
        }
        return $val;
    };
    $total = 0.0;
    foreach ($detail as $row) {
        $total += (float) ($row['jumlah'] ?? 0);
    }
    if ($total <= 0) {
        $total = (float) ($header['jumlah'] ?? 0);
    }
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
            <div class="pdf-title">INVOICE PEMBAYARAN</div>
            <div class="pdf-meta">
                dicetak {{ $generatedAt }}@if(!empty($admin)) · oleh {{ $admin }}@endif
            </div>
        </td>
    </tr>
</table>
<hr class="header-line">

<table class="meta">
    <tr>
        <td class="lbl">No. Invoice</td>
        <td class="val"><strong>{{ $header['kode_tagihan'] ?? '-' }}</strong></td>
        <td class="lbl">Status</td>
        <td><span class="badge">LUNAS</span></td>
    </tr>
    <tr>
        <td class="lbl">Nama Santri</td>
        <td class="val" colspan="3"><strong>{{ strtoupper($header['nama'] ?? '-') }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">NIS</td>
        <td class="val">{{ ($header['nis'] ?? '') !== '' ? $header['nis'] : '-' }}</td>
        <td class="lbl">Kelas</td>
        <td>{{ ($header['kelas'] ?? '') !== '' ? $header['kelas'] : '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Unit</td>
        <td class="val">{{ ($header['sekolah'] ?? '') !== '' ? $header['sekolah'] : '-' }}</td>
        <td class="lbl">Tahun Ajaran</td>
        <td>{{ ($header['bta'] ?? '') !== '' ? $header['bta'] : '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Tgl Bayar</td>
        <td class="val">{{ $fmtDate($header['tanggal_bayar'] ?? '') }}</td>
        <td class="lbl">No. Ref</td>
        <td>{{ !empty($header['noreff']) ? $header['noreff'] : '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Tagihan</td>
        <td class="val" colspan="3"><strong>{{ $header['nama_tagihan'] ?? '-' }}</strong></td>
    </tr>
</table>

<div class="section">Rincian Pembayaran</div>
<table class="data">
    <thead>
        <tr>
            <th style="width: 80px;">Kode</th>
            <th>Uraian</th>
            <th style="width: 110px; text-align: right;">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @foreach($detail as $row)
            <tr>
                <td>{{ $row['kode_akun'] ?? '-' }}</td>
                <td>{{ $row['nama_akun'] ?? '-' }}</td>
                <td class="num">{{ $fmt($row['jumlah'] ?? 0) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">Total Dibayar</td>
            <td class="num">{{ $fmt($total) }}</td>
        </tr>
    </tfoot>
</table>

<div class="foot">Bukti pembayaran tagihan yang sudah lunas · Monitoring Keuangan Santri</div>
</body>
</html>
