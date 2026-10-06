<?php

namespace App\Http\Controllers;

use App\Exports\TagihanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringKepsekController extends Controller
{
    private const API_URL = 'http://10.99.23.111/ws_client/Malang_Arrohmah_Putri_Kepsek_Monitoring/index.php';
    private const TAGIHAN_DAFTAR_ULANG = 'BIAYA Daftar Ulang Ajaran Baru';

    private function isDaftarUlangOnly(): bool
    {
        if ((int) session('user.humas', 0) === 1) {
            return true;
        }
        $kelompok = strtolower(trim((string) session('user.kelompok', '')));
        return $kelompok === 'humas' || $kelompok === '1';
    }

    private function isSuperAdmin(): bool
    {
        if ((int) session('user.is_superadmin', 0) === 1) {
            return true;
        }
        return strtolower(trim((string) session('user.kelompok', ''))) === 'superadmin';
    }

    public function showDashboard()
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }

        return view('dashboard_monitoring_kepsek', [
            'restrictedSekolah' => trim((string) session('user.code01', '')) ?: null,
            'restrictedTagihan' => $this->isDaftarUlangOnly(),
            'lockedTagihan' => $this->isDaftarUlangOnly() ? self::TAGIHAN_DAFTAR_ULANG : null,
            'canMultiSelectSekolah' => $this->isDaftarUlangOnly() || $this->isSuperAdmin(),
        ]);
    }

    public function dashboardData(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $bta = trim((string) $request->query('bta', ''));
        if ($bta === '') {
            return response()->json([
                'success' => true,
                'summary' => [
                    'total_siswa' => 0,
                    'total_siswa_piutang' => 0,
                    'total_tagihan' => 0,
                    'total_terbayar' => 0,
                    'total_piutang' => 0,
                    'total_saldo' => 0,
                ],
                'by_sekolah' => [],
                'message' => 'Pilih tahun ajaran',
            ]);
        }

        $params = $this->withSessionContext(['bta' => $bta]);

        $sekolah = $this->normalizeMultiValue($request->query('sekolah', []));
        if (!empty($sekolah)) {
            $params['sekolah'] = $sekolah;
        }

        if ($this->isDaftarUlangOnly()) {
            $params['tagihan'] = [self::TAGIHAN_DAFTAR_ULANG];
        } else {
            $tagihan = $this->normalizeMultiValue($request->query('tagihan', []));
            if (!empty($tagihan)) {
                $params['tagihan'] = $tagihan;
            }
        }

        $result = $this->callWs('getDashboardKeuangan', $params, 90);
        if ((int) ($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal memuat dashboard keuangan',
            ], (int) ($result['status'] ?? 500));
        }

        $data = $result['data'] ?? [];
        return response()->json([
            'success' => true,
            'summary' => $data['summary'] ?? [],
            'by_sekolah' => $data['by_sekolah'] ?? [],
        ]);
    }

    public function showTagihan()
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }

        if ($this->isDaftarUlangOnly()) {
            return redirect()->route('kepsek.tagihan-periode');
        }

        return view('tagihan_kepsek', [
            'restrictedTagihan' => false,
            'restrictedSekolah' => trim((string) session('user.code01', '')) ?: null,
        ]);
    }

    public function showTagihanPeriode()
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }

        if ($this->isDaftarUlangOnly()) {
            return redirect()->route('kepsek.tagihan-periode');
        }

        return view('tagihan_periode');
    }

    public function tagihanFilterOptions(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $result = $this->callWs('getFilterOptions', [], 15);

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil data filter',
            ], 422);
        }

        $tagihan = $this->isDaftarUlangOnly()
            ? [self::TAGIHAN_DAFTAR_ULANG]
            : ($result['data']['tagihan'] ?? []);

        return response()->json([
            'success' => true,
            'sekolah' => $result['data']['sekolah'] ?? [],
            'bta'     => $result['data']['bta'] ?? [],
            'kelas'   => $result['data']['kelas'] ?? [],
            'jenjang' => $result['data']['jenjang'] ?? [],
            'tagihan' => $tagihan,
        ]);
    }

    public function siswaList(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        if ($this->isDaftarUlangOnly() && $request->has('page') && $request->query('_periode') === '1') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $limit = $this->resolveLimit($request);
        $page = max((int) $request->query('page', 1), 1);
        $offset = ($page - 1) * $limit;

        $filterParams = $this->buildFilterParams($request);
        $tokenHash = (string) session('user.token');

        $cacheKey = 'kepsek_siswa_agg_' . md5(json_encode($filterParams) . '|' . $page . '|' . $limit . '|' . $tokenHash);

        $result = Cache::remember($cacheKey, 300, function () use ($filterParams, $limit, $offset) {
            return $this->callWs('getSiswaAggregated', array_merge($filterParams, [
                'limit' => $limit,
                'offset' => $offset
            ]), 30);
        });

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil data siswa'
            ], 422);
        }

        $data = $result['data'] ?? [];

        $countCacheKey = 'kepsek_siswa_count_' . md5(json_encode($filterParams) . '|' . $tokenHash);
        $countResult = Cache::remember($countCacheKey, 300, function () use ($filterParams) {
            return $this->callWs('getSiswaCount', $filterParams, 10);
        });

        $totalCount = ($countResult['status'] ?? 0) === 200 ? ($countResult['data']['total'] ?? 0) : count($data);

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
        $cacheKey = 'kepsek_siswa_bills_' . md5($custid . '|' . $bta . '|' . (string) session('user.token'));

        $result = Cache::remember($cacheKey, 300, function () use ($custid, $bta) {
            $params = ['custid' => $custid];
            if ($bta !== '') {
                $params['bta'] = $bta;
            }
            return $this->callWs('getSiswaTagihan', $params, 20);
        });

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil daftar tagihan siswa',
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
        $cacheKey = 'kepsek_summary_' . md5(json_encode($filterParams) . '|' . (string) session('user.token'));

        $result = Cache::remember($cacheKey, 300, function () use ($filterParams) {
            return $this->callWs('getSummaryTagihan', $filterParams, 20);
        });

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil ringkasan tagihan',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'total_siswa' => $result['data']['total_siswa'] ?? 0,
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
        $filename = 'tagihan_kepsek_' . now('Asia/Jakarta')->format('Ymd_His') . '.xlsx';

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

        $pdf = Pdf::loadView('tagihan_kepsek_pdf', [
            'rows'    => $rows,
            'filters' => $filters,
            'totals'  => $totals,
        ])->setPaper('a4', 'landscape');

        $filename = 'tagihan_kepsek_' . now('Asia/Jakarta')->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
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
        $allStudents = [];
        $offset = 0;
        $batchLimit = 1000;

        do {
            $result = $this->callWs('getSiswaAggregated', array_merge($filterParams, [
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

        $sekolah = trim((string) $request->query('sekolah', ''));
        if ($sekolah !== '') {
            $params['sekolah'] = $sekolah;
        }

        $bta = trim((string) $request->query('bta', ''));
        if ($bta !== '') {
            $params['bta'] = $bta;
        }

        $kelas = trim((string) $request->query('kelas', ''));
        if ($kelas !== '') {
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

        if ($this->isDaftarUlangOnly()) {
            $params['tagihan'] = [self::TAGIHAN_DAFTAR_ULANG];
        } else {
            $tagihan = $this->normalizeMultiValue($request->query('tagihan', []));
            if (!empty($tagihan)) {
                $params['tagihan'] = $tagihan;
            }
        }

        return $params;
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

        $tagihan = $this->isDaftarUlangOnly()
            ? [self::TAGIHAN_DAFTAR_ULANG]
            : $this->normalizeMultiValue($request->query('tagihan', []));

        return [
            'sekolah' => trim((string) $request->query('sekolah', '')) ?: 'Semua sekolah',
            'bta'     => trim((string) $request->query('bta', '')) ?: 'Semua tahun ajaran',
            'kelas'   => trim((string) $request->query('kelas', '')) ?: 'Semua kelas',
            'jenjang' => trim((string) $request->query('jenjang', '')) ?: 'Semua jenjang',
            'status'  => $statusLabel,
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

        $humas = $this->isDaftarUlangOnly() ? 1 : (int) session('user.humas', 0);
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
