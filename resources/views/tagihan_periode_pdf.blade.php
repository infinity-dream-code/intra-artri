<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Piutang Siswa</title>
    <style>
        @page { margin: 14mm 10mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #111827; }
        .header { display: flex; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
        .logo { width: 42px; height: 42px; margin-right: 10px; }
        .logo img { width: 100%; height: 100%; object-fit: contain; }
        .title-block h1 { margin: 0; font-size: 14px; }
        .title-block p { margin: 2px 0 0; font-size: 9px; color: #4b5563; }
        .meta { margin-bottom: 10px; font-size: 9px; }
        .meta span { display: inline-block; margin-right: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 4px 5px; text-align: left; font-size: 9px; }
        th { background: #fef9c3; font-weight: bold; }
        .text-right { text-align: right; }
        .badge-lunas { color: #166534; }
        .badge-belum { background: #ef4444; color: #ffffff; font-weight: bold; }
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
        <h1>Data Piutang Siswa</h1>
        <p>Monitoring Kepala Sekolah — dicetak {{ $printAt }}</p>
    </div>
</div>

<div class="meta">
    <span><strong>Sekolah / Unit:</strong> {{ $filters['sekolah'] ?? 'Semua sekolah/unit' }}</span>
    <span><strong>Tahun Ajaran:</strong> {{ $filters['bta'] ?? 'Semua tahun ajaran' }}</span>
    <span><strong>Kelas:</strong> {{ $filters['kelas'] ?? 'Semua kelas' }}</span>
    <span><strong>Tagihan:</strong> {{ $filters['tagihan'] ?? 'Semua tagihan' }}</span>
    <span><strong>Status:</strong> {{ $filters['status'] ?? 'Semua status' }}</span>
    <span><strong>Pencarian:</strong> {{ $filters['search'] ?? '-' }}</span>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Sekolah / Unit</th>
            <th class="text-right">Total Tagihan</th>
            <th class="text-right">Total Terbayar</th>
            <th class="text-right">Sisa Tagihan</th>
            <th>Status</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $i => $row)
            @php $paid = (int) ($row['status'] ?? 0) === 1; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['nis'] ?? '-' }}</td>
                <td>{{ strtoupper($row['nama'] ?? '-') }}</td>
                <td>{{ $row['kelas'] ?? '-' }}</td>
                <td>{{ $row['sekolah'] ?? '-' }}</td>
                <td class="text-right">{{ $fmtRupiah($row['total_tagihan'] ?? 0) }}</td>
                <td class="text-right">{{ $fmtRupiah($row['total_terbayar'] ?? 0) }}</td>
                <td class="text-right">{{ $fmtRupiah($row['sisa_tagihan'] ?? 0) }}</td>
                <td class="{{ $paid ? 'badge-lunas' : 'badge-belum' }}">{{ $paid ? 'Lunas' : 'Belum Lunas' }}</td>
                <td class="{{ $paid ? 'badge-lunas' : 'badge-belum' }}">{{ $paid ? 'Bisa Ujian' : 'Belum Bisa Ujian' }}</td>
            </tr>
        @empty
            <tr><td colspan="10" style="text-align:center;">Tidak ada data</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="text-right" style="font-weight:bold;">Total</td>
            <td class="text-right" style="font-weight:bold;">{{ $fmtRupiah($totals['total_tagihan'] ?? 0) }}</td>
            <td class="text-right" style="font-weight:bold;">{{ $fmtRupiah($totals['total_terbayar'] ?? 0) }}</td>
            <td class="text-right" style="font-weight:bold;">{{ $fmtRupiah($totals['total_piutang'] ?? 0) }}</td>
            <td></td>
            <td></td>
        </tr>
    </tfoot>
</table>
</body>
</html>