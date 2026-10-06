<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Cashless</title>
    <style>
        @page { margin: 12mm 8mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #111827; }
        .header { display: flex; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
        .logo { width: 42px; height: 42px; margin-right: 10px; }
        .logo img { width: 100%; height: 100%; object-fit: contain; }
        .title-block h1 { margin: 0; font-size: 14px; }
        .title-block p { margin: 2px 0 0; font-size: 9px; color: #4b5563; }
        .meta { margin-bottom: 10px; font-size: 9px; }
        .meta span { display: inline-block; margin-right: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 3px 4px; text-align: left; font-size: 8px; }
        th { background: #fef9c3; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; background: #f1f5f9; }
    </style>
</head>
<body>
@php
    $printAt = now('Asia/Jakarta')->format('d/m/Y H:i');
    $fmtRupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp
<div class="header">
    <div class="logo">
        <img src="{{ public_path('logo.png') }}" alt="Logo">
    </div>
    <div class="title-block">
        <h1>Laporan Cashless</h1>
        <p>Transaksi Scctcashout — dicetak {{ $printAt }}</p>
    </div>
</div>

<div class="meta">
    <span><strong>Periode:</strong> {{ $filters['tgl_dari'] ?? '-' }} s/d {{ $filters['tgl_sampai'] ?? '-' }}</span>
    <span><strong>Sekolah / Unit:</strong> {{ $filters['sekolah'] ?? 'Semua' }}</span>
    <span><strong>Kelas:</strong> {{ $filters['kelas'] ?? 'Semua' }}</span>
    <span><strong>Teller:</strong> {{ $filters['teller'] ?? 'Semua' }}</span>
    <span><strong>Keterangan:</strong> {{ $filters['keterangan'] ?? 'Semua' }}</span>
    <span><strong>Pencarian:</strong> {{ $filters['search'] ?? '-' }}</span>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Unit</th>
            <th>Teller</th>
            <th>Keterangan</th>
            <th class="text-right">Jumlah</th>
            <th>TRANSNO</th>
            <th>FIDBANK</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $i => $row)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="text-center">{{ $row['tanggal'] ?? '-' }}</td>
                <td class="text-center">{{ $row['nis'] ?? '-' }}</td>
                <td>{{ strtoupper($row['nama'] ?? '-') }}</td>
                <td class="text-center">{{ $row['kelas'] ?? '-' }}</td>
                <td class="text-center">{{ $row['sekolah'] ?? $row['unit'] ?? '-' }}</td>
                <td class="text-center">{{ $row['teller'] ?? '-' }}</td>
                <td>{{ $row['keterangan'] ?? '-' }}</td>
                <td class="text-right">{{ $fmtRupiah($row['jumlah'] ?? 0) }}</td>
                <td class="text-center">{{ $row['transno'] ?? '-' }}</td>
                <td class="text-center">{{ $row['fidbank'] ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="11" class="text-center">Tidak ada data</td></tr>
        @endforelse
        <tr class="total-row">
            <td colspan="8" class="text-right">TOTAL ({{ (int) ($totals['total_transaksi'] ?? count($rows)) }} transaksi)</td>
            <td class="text-right">{{ $fmtRupiah($totals['total_jumlah'] ?? 0) }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
</body>
</html>
