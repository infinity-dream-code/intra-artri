<?php

namespace App\Http\Controllers;

use App\Exports\TagihanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TagihanPeriodeController extends Controller
{
    private const API_URL = 'http://10.99.23.111/ws_client/Malang_Arrohmah_Putri_Kepsek_Monitoring/index.php';
    private const TAGIHAN_DAFTAR_ULANG = 'BIAYA Daftar Ulang Ajaran Baru';

    private function isHumas(): bool
    {
        if ((int) session('user.humas', 0) === 1) {
            return true;
        }
        $kelompok = strtolower(trim((string) session('user.kelompok', '')));
        return $kelompok === 'humas' || $kelompok === '1';
    }

    public function showTagihan()
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }

        return view('tagihan_periode', [
            'restrictedSekolah' => trim((string) session('user.code01', '')) ?: null,
            'restrictedTagihan' => $this->isHumas(),
            'lockedTagihan' => $this->isHumas() ? self::TAGIHAN_DAFTAR_ULANG : null,
            'canMultiSelectSekolah' => $this->isHumas()
                || (int) session('user.is_superadmin', 0) === 1
                || strtolower(trim((string) session('user.kelompok', ''))) === 'superadmin',
        ]);
    }

    public function showCatatPenagihan(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }

        return view('catat_penagihan', [
            'custid'  => trim((string) $request->query('custid', '')),
            'nocust'  => trim((string) $request->query('nocust', '')),
            'nama'    => trim((string) $request->query('nama', '')),
            'kelas'   => trim((string) $request->query('kelas', '')),
            'bta'     => trim((string) $request->query('bta', '')),
            'tagihan' => trim((string) $request->query('tagihan', '')),
            'sisa'    => trim((string) $request->query('sisa', '')),
        ]);
    }

    public function storeCatatPenagihan(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'custid' => ['required', 'string'],
            'nocust' => ['nullable', 'string'],
            'tanggal_komunikasi' => ['required', 'date'],
            'media_komunikasi' => ['required', 'string', 'in:WhatsApp,Telepon'],
            'hasil_komunikasi' => ['required', 'string'],
            'rencana_pembayaran' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:250'],
        ]);

        $allowedHasil = [
            'Akan melakukan pembayaran',
            'Sudah melakukan pembayaran',
            'Meminta waktu pembayaran',
            'Mengalami kendala pembayaran',
            'Tidak dapat dihubungi',
        ];
        if (!in_array($validated['hasil_komunikasi'], $allowedHasil, true)) {
            return response()->json(['success' => false, 'message' => 'Hasil komunikasi tidak valid'], 422);
        }

        $result = $this->callWs('saveRiwayatPenagihan', [
            'custid' => $validated['custid'],
            'nocust' => $validated['nocust'] ?? '',
            'admin' => (string) session('user.username', ''),
            'tanggal_komunikasi' => $validated['tanggal_komunikasi'],
            'media_komunikasi' => $validated['media_komunikasi'],
            'hasil_komunikasi' => $validated['hasil_komunikasi'],
            'rencana_pembayaran' => $validated['rencana_pembayaran'] ?? '',
            'catatan' => $validated['catatan'] ?? '',
        ], 15);

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal menyimpan hasil penagihan',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Hasil penagihan berhasil disimpan',
            'data' => $result['data'] ?? null,
        ]);
    }

    public function showLaporanPenagihan(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }

        $custid = trim((string) $request->query('custid', ''));
        $nama = trim((string) $request->query('nama', ''));
        $bta = trim((string) $request->query('bta', ''));
        $kelas = trim((string) $request->query('kelas', ''));

        $riwayat = [];
        if ($custid !== '') {
            $result = $this->callWs('getRiwayatPenagihan', ['custid' => $custid], 15);
            if (($result['status'] ?? 0) === 200) {
                $riwayat = $result['data'] ?? [];
            }
        }

        return view('laporan_penagihan', [
            'custid' => $custid,
            'nama' => $nama,
            'bta' => $bta,
            'kelas' => $kelas,
            'riwayat' => $riwayat,
        ]);
    }

    public function riwayatPenagihan(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'custid' => ['required', 'string'],
        ]);

        $result = $this->callWs('getRiwayatPenagihan', [
            'custid' => trim($validated['custid']),
            'limit' => (int) $request->query('limit', 0),
        ], 15);

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil riwayat penagihan',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'] ?? [],
        ]);
    }

    public function tagihanFilterOptions(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $cacheKey = 'periode_filter_options_v6_' . md5((string) session('user.token') . '|' . (string) session('user.code01', ''));

        $result = Cache::remember($cacheKey, 900, function () {
            $r = $this->callWs('getFilterOptions', [], 15);
            return ($r['status'] ?? 0) === 200 ? $r : null;
        });

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data filter',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'sekolah' => $result['data']['sekolah'] ?? [],
            'bta'     => $result['data']['bta'] ?? [],
            'kelas'   => $result['data']['kelas'] ?? [],
            'jenjang' => $result['data']['jenjang'] ?? [],
            'tagihan' => $this->isHumas()
                ? [self::TAGIHAN_DAFTAR_ULANG]
                : ($result['data']['tagihan'] ?? []),
        ]);
    }
    public function siswaList(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $limit = $this->resolveLimit($request);
        $page = max((int) $request->query('page', 1), 1);
        $offset = ($page - 1) * $limit;

        $filterParams = $this->buildFilterParams($request);

        if (empty($filterParams['bta']) || empty($filterParams['tagihan'])) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Pilih tahun ajaran dan tagihan untuk menampilkan Tagihan Periode',
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => 0,
                    'has_more' => false,
                    'from' => 0,
                    'to' => 0,
                ],
            ]);
        }

        $tokenHash = (string) session('user.token');
        $cacheKey = 'periode_siswa_agg_v2_' . md5(json_encode($filterParams) . '|' . $page . '|' . $limit . '|' . $tokenHash);

        $result = Cache::remember($cacheKey, 300, function () use ($filterParams, $limit, $offset) {
            $r = $this->callWs('getSiswaPeriode', array_merge($filterParams, [
                'limit' => $limit,
                'offset' => $offset
            ]), 55);
            return ($r['status'] ?? 0) === 200 ? $r : null;
        });

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data siswa'
            ], 422);
        }

        $data = $result['data'] ?? [];

        $countCacheKey = 'periode_siswa_count_' . md5(json_encode($filterParams) . '|' . $tokenHash);
        $countResult = Cache::remember($countCacheKey, 300, function () use ($filterParams) {
            $r = $this->callWs('getSiswaCount', $filterParams, 10);
            return ($r['status'] ?? 0) === 200 ? $r : null;
        });

        $totalCount = $countResult['data']['total'] ?? count($data);

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'offset' => $offset,
                'total' => (int) $totalCount,
                'has_more' => ($offset + $limit) < $totalCount,
                'from' => $totalCount ? $offset + 1 : 0,
                'to' => min($offset + count($data), $totalCount),
            ],
        ]);
    }

    public function siswaTagihan(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'custid' => ['required', 'string'],
        ]);

        $custid = trim($validated['custid']);
        $bta = trim((string) $request->query('bta', ''));
        $tagihan = $this->isHumas()
            ? [self::TAGIHAN_DAFTAR_ULANG]
            : $this->normalizeMultiValue($request->query('tagihan', []));
        $cacheKey = 'periode_siswa_bills_v2_' . md5($custid . '|' . $bta . '|' . implode(',', $tagihan) . '|' . (string) session('user.token'));

        $result = Cache::remember($cacheKey, 300, function () use ($custid, $bta, $tagihan) {
            $params = ['custid' => $custid];
            if ($bta !== '') {
                $params['bta'] = $bta;
            }
            if (!empty($tagihan)) {
                $params['tagihan'] = $tagihan;
            }
            $r = $this->callWs('getSiswaTagihanPeriode', $params, 20);
            return ($r['status'] ?? 0) === 200 ? $r : null;
        });

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar tagihan siswa',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'] ?? [],
        ]);
    }
    public function tagihanDetail(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'custid' => ['required', 'string'],
            'kode_tagihan' => ['required', 'string'],
        ]);

        $custid = trim($validated['custid']);
        $billcd = trim($validated['kode_tagihan']);
        $cacheKey = 'kepsek_detail_' . md5($custid . '|' . $billcd . '|' . (string) session('user.token'));

        $result = Cache::remember($cacheKey, 3600, function () use ($custid, $billcd) {
            return $this->callWs('getDetailTagihan', [
                'custid' => $custid,
                'kode_tagihan' => $billcd,
            ], 12);
        });

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil rincian tagihan',
            ], 422);
        }

        $data = $result['data'] ?? ['header' => null, 'detail' => []];
        $details = $data['detail'] ?? [];

        if (empty($details)) {
            return response()->json([
                'success' => false,
                'message' => 'Rincian tagihan tidak ditemukan',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function tagihanSummary(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $filterParams = $this->buildFilterParams($request);

        if (empty($filterParams['bta']) || empty($filterParams['tagihan'])) {
            return response()->json([
                'success' => true,
                'total_siswa' => 0,
                'total_siswa_piutang' => 0,
                'total_tagihan' => 0,
                'total_terbayar' => 0,
                'total_piutang' => 0,
            ]);
        }

        $cacheKey = 'periode_summary_v2_' . md5(json_encode($filterParams) . '|' . (string) session('user.token'));

        $result = Cache::remember($cacheKey, 300, function () use ($filterParams) {
            $r = $this->callWs('getSummaryTagihanPeriode', $filterParams, 55);
            if (($r['status'] ?? 0) !== 200) {
                Log::warning('Tagihan summary ws gagal', ['params' => $filterParams, 'response' => $r]);
                return null;
            }
            return $r;
        });

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ringkasan tagihan',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'total_siswa' => $result['data']['total_siswa'] ?? 0,
            'total_siswa_piutang' => $result['data']['total_siswa_piutang'] ?? 0,
            'total_tagihan' => $result['data']['total_tagihan'] ?? 0,
            'total_terbayar' => $result['data']['total_terbayar'] ?? 0,
            'total_piutang' => $result['data']['total_piutang'] ?? 0,
        ]);
    }

    public function exportTagihanExcel(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }

        $rows = $this->fetchSiswaForExport($request);

        if (empty($rows)) {
            return redirect()
                ->route('kepsek.tagihan')
                ->with('error', 'Tidak ada data untuk diexport.');
        }

        $filters = $this->filterLabels($request);
        $totals = $this->sumExportRows($rows);
        $filename = 'tagihan_periode_' . now('Asia/Jakarta')->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new TagihanExport(collect($rows), $filters, $totals),
            $filename
        );
    }

    public function exportTagihanPdf(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }

        $rows = $this->fetchSiswaForExport($request);
        if (empty($rows)) {
            return redirect()
                ->route('kepsek.tagihan')
                ->with('error', 'Tidak ada data untuk diexport.');
        }

        $filters = $this->filterLabels($request);
        $totals = $this->sumExportRows($rows);

        $pdf = Pdf::loadView('tagihan_periode_pdf', [
            'rows'    => $rows,
            'filters' => $filters,
            'totals'  => $totals,
        ])->setPaper('a4', 'landscape');

        $filename = 'tagihan_periode_' . now('Asia/Jakarta')->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportSiswaLaporan(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }

        $custid = trim((string) $request->query('custid', ''));
        $bta = trim((string) $request->query('bta', ''));
        $tagihan = $this->normalizeMultiValue($request->query('tagihan', []));
        if ($this->isHumas()) {
            $tagihan = [self::TAGIHAN_DAFTAR_ULANG];
        }
        $nama = trim((string) $request->query('nama', ''));
        $kelas = trim((string) $request->query('kelas', ''));
        $sekolah = trim((string) $request->query('sekolah', ''));
        $nocust = trim((string) $request->query('nocust', ''));
        $kaderLabel = trim((string) $request->query('kader_label', $request->query('beasiswa_label', '')));
        $isAnakPegawai = (int) $request->query('is_anak_pegawai', 0);
        if ($isAnakPegawai < 0 || $isAnakPegawai > 9) {
            $isAnakPegawai = 0;
        }
        if ($kaderLabel === '') {
            $kaderLabel = $this->resolveBeasiswaLabel($isAnakPegawai);
        }

        if ($custid === '' || $bta === '' || empty($tagihan)) {
            return redirect()
                ->route('kepsek.tagihan-periode')
                ->with('error', 'Data filter tidak lengkap untuk laporan siswa.');
        }

        $billsResult = $this->callWs('getSiswaTagihanPeriode', [
            'custid' => $custid,
            'bta' => $bta,
            'tagihan' => $tagihan,
        ], 30);

        $saldoResult = $this->callWs('getSaldo', ['custid' => $custid], 15);
        $riwayatResult = $this->callWs('getRiwayatPenagihan', ['custid' => $custid], 15);

        $bills = (($billsResult['status'] ?? 0) === 200) ? ($billsResult['data'] ?? []) : [];
        $saldo = (($saldoResult['status'] ?? 0) === 200) ? ($saldoResult['data'] ?? []) : [];
        $riwayat = (($riwayatResult['status'] ?? 0) === 200) ? ($riwayatResult['data'] ?? []) : [];

        usort($bills, function ($a, $b) {
            $fa = (int) ($a['furutan'] ?? 0);
            $fb = (int) ($b['furutan'] ?? 0);
            if ($fa !== $fb) {
                return $fa <=> $fb;
            }
            $btaA = (string) ($a['bta'] ?? '');
            $btaB = (string) ($b['bta'] ?? '');
            if ($btaA !== $btaB) {
                return strcmp($btaA, $btaB);
            }
            return strcmp((string) ($a['kode_tagihan'] ?? ''), (string) ($b['kode_tagihan'] ?? ''));
        });

        $totalTagihan = 0.0;
        $totalTerbayar = 0.0;
        foreach ($bills as $bill) {
            $jumlah = (float) ($bill['jumlah'] ?? 0);
            $totalTagihan += $jumlah;
            if ((int) ($bill['status_bayar'] ?? 0) === 1) {
                $totalTerbayar += $jumlah;
            }
        }

        // Prefer ringkasan dari list (sama seperti web) jika dikirim
        $qTotalTagihan = $request->query('total_tagihan');
        $qTotalTerbayar = $request->query('total_terbayar');
        $qSisa = $request->query('sisa_tagihan');
        $qSaldoTerpakai = $request->query('saldo_terpakai');
        $qSisaSebelum = $request->query('sisa_tagihan_sebelum_saldo');

        if ($qTotalTagihan !== null && $qTotalTagihan !== '') {
            $totalTagihan = (float) $qTotalTagihan;
        }
        if ($qTotalTerbayar !== null && $qTotalTerbayar !== '') {
            $totalTerbayar = (float) $qTotalTerbayar;
        }

        $saldoAmount = (float) ($saldo['saldo'] ?? 0);
        $sisaSebelumSaldo = ($qSisaSebelum !== null && $qSisaSebelum !== '')
            ? (float) $qSisaSebelum
            : max(0, $totalTagihan - $totalTerbayar);
        $saldoTerpakai = ($qSaldoTerpakai !== null && $qSaldoTerpakai !== '')
            ? (float) $qSaldoTerpakai
            : min(max($saldoAmount, 0), $sisaSebelumSaldo);
        $sisaTagihan = ($qSisa !== null && $qSisa !== '')
            ? (float) $qSisa
            : max(0, $sisaSebelumSaldo - max($saldoAmount, 0));

        $logoSrc = $this->resolvePdfLogoSrc();
        $ttdSrc = $this->resolvePdfImageSrc('ttd.png');

        $pdf = Pdf::loadView('tagihan_siswa_pdf', [
            'siswa' => [
                'custid' => $custid,
                'nama' => $nama !== '' ? $nama : ('Siswa #' . $custid),
                'kelas' => $kelas,
                'sekolah' => $sekolah,
                'nocust' => $nocust,
                'kader_label' => $kaderLabel,
                'is_anak_pegawai' => $isAnakPegawai,
            ],
            'periode' => [
                'bta' => $bta,
                'tagihan' => $tagihan,
            ],
            'summary' => [
                'total_tagihan' => $totalTagihan,
                'total_terbayar' => $totalTerbayar,
                'sisa_tagihan' => $sisaTagihan,
                'sisa_tagihan_sebelum_saldo' => $sisaSebelumSaldo,
                'saldo' => $saldoAmount,
                'saldo_terpakai' => $saldoTerpakai,
            ],
            'bills' => $bills,
            'riwayat' => $riwayat,
            'generatedAt' => now('Asia/Jakarta')->format('d/m/Y H:i'),
            'admin' => (string) session('user.nama', session('user.username', '')),
            'logoSrc' => $logoSrc,
            'ttdSrc' => $ttdSrc,
        ])->setPaper('a4', 'portrait');

        $safeName = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $nama !== '' ? $nama : $custid);
        $filename = 'laporan_siswa_' . $safeName . '_' . now('Asia/Jakarta')->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportInvoice(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }

        $custid = trim((string) $request->query('custid', ''));
        $kodeTagihan = trim((string) $request->query('kode_tagihan', ''));

        if ($custid === '' || $kodeTagihan === '') {
            return redirect()
                ->route('kepsek.tagihan-periode')
                ->with('error', 'Data tagihan tidak lengkap untuk invoice.');
        }

        $result = $this->callWs('getTagihanInvoice', [
            'custid' => $custid,
            'kode_tagihan' => $kodeTagihan,
        ], 20);

        if (($result['status'] ?? 0) !== 200) {
            return redirect()
                ->route('kepsek.tagihan-periode')
                ->with('error', $result['message'] ?? 'Gagal membuat invoice.');
        }

        $data = $result['data'] ?? [];
        $header = $data['header'] ?? null;
        $detail = $data['detail'] ?? [];

        if (!$header || (int) ($header['status_bayar'] ?? 0) !== 1) {
            return redirect()
                ->route('kepsek.tagihan-periode')
                ->with('error', 'Invoice hanya untuk tagihan yang sudah lunas.');
        }

        $logoSrc = $this->resolvePdfLogoSrc();
        $nama = trim((string) ($header['nama'] ?? ''));
        $safeName = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $nama !== '' ? $nama : $custid);
        $safeBill = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $kodeTagihan);
        $filename = 'invoice_' . $safeName . '_' . $safeBill . '_' . now('Asia/Jakarta')->format('Ymd_His') . '.pdf';

        $pdf = Pdf::loadView('tagihan_invoice_pdf', [
            'header' => $header,
            'detail' => $detail,
            'generatedAt' => now('Asia/Jakarta')->format('d/m/Y H:i'),
            'admin' => (string) session('user.nama', session('user.username', '')),
            'logoSrc' => $logoSrc,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    private function resolvePdfLogoSrc(): ?string
    {
        return $this->resolvePdfImageSrc('logoartri.png');
    }

    private function resolvePdfImageSrc(string $filename): ?string
    {
        $filename = ltrim($filename, '/\\');
        $candidates = [
            public_path($filename),
            public_path('images/' . $filename),
            public_path('img/' . $filename),
            base_path($filename),
            base_path('public/' . $filename),
        ];

        foreach ($candidates as $path) {
            if (!is_string($path) || $path === '' || !is_file($path)) {
                continue;
            }
            $binary = @file_get_contents($path);
            if ($binary === false || $binary === '') {
                continue;
            }
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = $ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode($binary);
        }

        return null;
    }

    private function sumExportRows(array $rows): array
    {
        $totalTagihan = 0;
        $totalTerbayar = 0;
        $totalPiutang = 0;
        foreach ($rows as $r) {
            $totalTagihan += $r['total_tagihan'];
            $totalTerbayar += $r['total_terbayar'];
            $totalPiutang += $r['sisa_tagihan'];
        }

        return [
            'total_tagihan'  => $totalTagihan,
            'total_terbayar' => $totalTerbayar,
            'total_piutang'  => $totalPiutang,
        ];
    }

    private function fetchSiswaForExport(Request $request): array
    {
        $filterParams = $this->buildFilterParams($request);

        if (empty($filterParams['bta']) || empty($filterParams['tagihan'])) {
            return [];
        }

        $allStudents = [];
        $offset = 0;
        $batchLimit = 1000;

        do {
            $result = $this->callWs('getSiswaPeriode', array_merge($filterParams, [
                'limit' => $batchLimit,
                'offset' => $offset
            ]), 60);

            if (($result['status'] ?? 0) !== 200) {
                break;
            }

            $chunk = $result['data'] ?? [];
            foreach ($chunk as $row) {
                $allStudents[] = $row;
            }

            $offset += $batchLimit;
        } while (count($chunk) === $batchLimit);

        return $allStudents;
    }

    public function kelasBySekolah(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $sekolah = $this->normalizeMultiValue($request->query('sekolah', []));

        if (empty($sekolah)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $cacheKey = 'periode_kelas_v3_' . md5(implode('|', $sekolah) . '|' . (string) session('user.token') . '|' . (string) session('user.code01', ''));

        $result = Cache::remember($cacheKey, 600, function () use ($sekolah) {
            return $this->callWs('getKelasBySekolah', ['sekolah' => $sekolah], 15);
        });

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil data kelas'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'] ?? [],
        ]);
    }

    private function resolveLimit(Request $request): int
    {
        $limit = (int) $request->query('limit', 10);
        return in_array($limit, [10, 25, 50, 100], true) ? $limit : 10;
    }

    private function normalizeMultiValue($value): array
    {
        if (!is_array($value)) {
            $value = $value !== null && $value !== '' ? [$value] : [];
        }

        return array_values(array_filter(
            array_map(fn($v) => trim((string) $v), $value),
            fn($v) => $v !== ''
        ));
    }

    private function buildFilterParams(Request $request): array
    {
        $params = [];

        $sekolah = $this->normalizeMultiValue($request->query('sekolah', []));
        if (!empty($sekolah)) {
            $params['sekolah'] = $sekolah;
        }

        $bta = trim((string) $request->query('bta', ''));
        if ($bta !== '') {
            $params['bta'] = $bta;
        }

        $kelas = $this->normalizeMultiValue($request->query('kelas', []));
        if (!empty($kelas)) {
            $params['kelas'] = $kelas;
        }

        $jenjang = trim((string) $request->query('jenjang', ''));
        if ($jenjang !== '') {
            $params['jenjang'] = $jenjang;
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $params['search'] = $search;
        }

        $paidst = $request->query('paidst');
        if ($paidst === '0' || $paidst === '1') {
            $params['paidst'] = $paidst;
        }

        $beasiswa = $this->normalizeBeasiswaCodes(
            $request->query('is_anak_pegawai', $request->query('beasiswa', []))
        );
        if (!empty($beasiswa)) {
            $params['is_anak_pegawai'] = $beasiswa;
        }

        $tagihan = $this->normalizeMultiValue($request->query('tagihan', []));
        if ($this->isHumas()) {
            $params['tagihan'] = [self::TAGIHAN_DAFTAR_ULANG];
        } elseif (!empty($tagihan)) {
            $params['tagihan'] = $tagihan;
        }

        return $params;
    }

    private function beasiswaLabels(): array
    {
        return [
            0 => 'NON BEASISWA',
            1 => 'KADER DALAM',
            2 => 'KADER MALANG',
            3 => 'KADER PENGURUS',
            4 => 'KADER AMAL USAHA',
            5 => 'KADER JAMAAH',
            6 => 'KADER PENGABDIAN',
            7 => 'SAUDARA',
            8 => 'TAAWWUN',
            9 => 'ALUMNI',
        ];
    }

    private function resolveBeasiswaLabel($code): string
    {
        $code = (int) $code;
        $labels = $this->beasiswaLabels();
        return $labels[$code] ?? ('KODE ' . $code);
    }

    private function normalizeBeasiswaCodes($value): array
    {
        if (!is_array($value)) {
            $value = ($value !== null && $value !== '') ? [$value] : [];
        }

        $out = [];
        foreach ($value as $v) {
            if ($v === null || $v === '') {
                continue;
            }
            if (!is_numeric($v)) {
                continue;
            }
            $n = (int) $v;
            if ($n < 0 || $n > 9) {
                continue;
            }
            $out[$n] = $n;
        }

        return array_values($out);
    }

    private function filterLabels(Request $request): array
    {
        $paidst = $request->query('paidst', '');
        $statusLabel = 'Semua status';
        if ($paidst === '1') {
            $statusLabel = 'Lunas';
        } elseif ($paidst === '0') {
            $statusLabel = 'Belum Lunas';
        }

        $beasiswaCodes = $this->normalizeBeasiswaCodes(
            $request->query('is_anak_pegawai', $request->query('beasiswa', []))
        );
        $beasiswaLabel = 'Semua kategori';
        if (!empty($beasiswaCodes)) {
            $beasiswaLabel = implode(', ', array_map(
                fn ($c) => $this->resolveBeasiswaLabel($c),
                $beasiswaCodes
            ));
        }

        $kelasList = $this->normalizeMultiValue($request->query('kelas', []));
        $kelasLabel = !empty($kelasList) ? implode(', ', $kelasList) : 'Semua kelas';

        $sekolahList = $this->normalizeMultiValue($request->query('sekolah', []));
        $sekolahLabel = !empty($sekolahList) ? implode(', ', $sekolahList) : 'Semua sekolah';

        $tagihan = $this->isHumas()
            ? [self::TAGIHAN_DAFTAR_ULANG]
            : $this->normalizeMultiValue($request->query('tagihan', []));

        return [
            'sekolah' => $sekolahLabel,
            'bta'     => trim((string) $request->query('bta', '')) ?: 'Semua tahun ajaran',
            'kelas'   => $kelasLabel,
            'jenjang' => trim((string) $request->query('jenjang', '')) ?: 'Semua jenjang',
            'status'  => $statusLabel,
            'kader'   => $beasiswaLabel,
            'beasiswa' => $beasiswaLabel,
            'search'  => trim((string) $request->query('search', '')) ?: '-',
            'tagihan' => $tagihan ? implode(', ', $tagihan) : 'Semua tagihan',
        ];
    }

    private function withSessionContext(array $params): array
    {
        $code01 = session('user.code01');
        if ($code01 !== null && $code01 !== '' && !isset($params['code01'])) {
            $params['code01'] = (string) $code01;
        }

        $humas = $this->isHumas() ? 1 : (int) session('user.humas', 0);
        if (!isset($params['humas'])) {
            $params['humas'] = $humas;
        }

        $kelompok = session('user.kelompok');
        if ($kelompok !== null && $kelompok !== '' && !isset($params['kelompok'])) {
            $params['kelompok'] = (string) $kelompok;
        }

        return $params;
    }

    public function siswaUangMasuk(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'custid' => ['required', 'string'],
        ]);

        $custid = trim($validated['custid']);
        $cacheKey = 'kepsek_siswa_uang_masuk_' . md5($custid . '|' . (string) session('user.token'));

        $result = Cache::remember($cacheKey, 300, function () use ($custid) {
            return $this->callWs('getUangMasuk', ['custid' => $custid], 20);
        });

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil data uang masuk',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'] ?? [],
        ]);
    }

    public function siswaSaldo(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'custid' => ['required', 'string'],
        ]);

        $custid = trim($validated['custid']);

        // HAPUS CACHE SEMENTARA AGAR DATA LANGSUNG DARI API
        // $cacheKey = 'kepsek_siswa_saldo_' . md5($custid . '|' . (string) session('user.token'));
        // $result = Cache::remember($cacheKey, 300, function () use ($custid) { ... });

        // PANGGIL LANGSUNG API
        $result = $this->callWs('getSaldo', ['custid' => $custid], 20);

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil saldo',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'] ?? [],
        ]);
    }

    private function callWs(string $method, array $params = [], int $timeout = 20): array
    {
        $token = session('user.token');
        if (!$token) {
            return ['status' => 401, 'message' => 'Session tidak valid. Silakan login kembali.'];
        }

        $body = array_merge(['method' => $method, 'token' => $token], $this->withSessionContext($params));

        try {
            $response = Http::connectTimeout(5)
                ->timeout($timeout)
                ->acceptJson()
                ->asJson()
                ->post(self::API_URL, $body);

            $json = $response->json();
            if (is_array($json)) {
                return $json;
            }

            return [
                'status'  => $response->status(),
                'message' => 'Respons server tidak valid',
            ];
        } catch (\Throwable $e) {
            Log::error('Monitoring Kepsek WS error', [
                'method'  => $method,
                'message' => $e->getMessage(),
            ]);

            return ['status' => 500, 'message' => 'Tidak dapat terhubung ke server'];
        }
    }
}
