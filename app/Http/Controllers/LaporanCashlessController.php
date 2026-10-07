<?php

namespace App\Http\Controllers;

use App\Exports\CashlessExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class LaporanCashlessController extends Controller
{
    private const API_URL = 'http://10.99.23.111/ws_client/Malang_Arrohmah_Putri_Kepsek_Monitoring/index.php';

    private function guardPage()
    {
        if (session('user.app') !== 'laporan-cashless') {
            return redirect()->route('dashboard');
        }
        return null;
    }

    private function guardApi()
    {
        if (session('user.app') !== 'laporan-cashless') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }
        return null;
    }

    public function show()
    {
        if ($redirect = $this->guardPage()) {
            return $redirect;
        }

        return view('laporan_cashless');
    }

    public function filters()
    {
        try {
            if ($resp = $this->guardApi()) {
                return $resp;
            }

            $result = $this->callWs('getCashlessFilterOptions', [], 20);
            if (($result['status'] ?? 0) !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal mengambil data filter',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'sekolah' => $result['data']['sekolah'] ?? [],
                'kelas' => [],
                'keterangan' => $result['data']['keterangan'] ?? [],
                'locked_teller' => $result['data']['locked_teller'] ?? session('user.username', ''),
            ]);
        } catch (\Throwable $e) {
            Log::error('Laporan Cashless filters error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat filter',
            ], 500);
        }
    }

    public function kelasBySekolah(Request $request)
    {
        try {
            if ($resp = $this->guardApi()) {
                return $resp;
            }

            $sekolah = $this->normalizeMultiValue($request->query('sekolah', []));
            if (empty($sekolah)) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $result = $this->callWs('getCashlessKelasBySekolah', ['sekolah' => $sekolah], 20);
            if (($result['status'] ?? 0) !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal mengambil kelas',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? [],
            ]);
        } catch (\Throwable $e) {
            Log::error('Laporan Cashless kelas error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal memuat kelas'], 500);
        }
    }

    public function detail(Request $request)
    {
        try {
            if ($resp = $this->guardApi()) {
                return $resp;
            }

            $transno = trim((string) $request->query('transno', ''));
            $custid = (int) $request->query('custid', 0);
            if ($transno === '') {
                return response()->json(['success' => false, 'message' => 'transno wajib diisi'], 422);
            }

            $result = $this->callWs('getCashlessDetail', [
                'transno' => $transno,
                'custid' => $custid,
            ], 30);

            if (($result['status'] ?? 0) !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal mengambil detail transaksi',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? [],
            ]);
        } catch (\Throwable $e) {
            Log::error('Laporan Cashless detail error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal memuat detail'], 500);
        }
    }

    public function data(Request $request)
    {
        try {
            if ($resp = $this->guardApi()) {
                return $resp;
            }

            $limit = max(1, min((int) $request->query('limit', 50), 500));
            $page = max((int) $request->query('page', 1), 1);
            $offset = ($page - 1) * $limit;

            $filterParams = $this->buildFilterParams($request);
            $result = $this->callWs('getCashlessData', array_merge($filterParams, [
                'limit' => $limit,
                'offset' => $offset,
            ]), 55);

            if (($result['status'] ?? 0) !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal mengambil data transaksi',
                ], 422);
            }

            $rows = $result['data'] ?? [];
            $count = count($rows);

            return response()->json([
                'success' => true,
                'data' => $rows,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'offset' => $offset,
                    'count' => $count,
                    'has_more' => $count >= $limit,
                    'from' => $count > 0 ? $offset + 1 : 0,
                    'to' => $offset + $count,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Laporan Cashless data error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function summary(Request $request)
    {
        try {
            if ($resp = $this->guardApi()) {
                return $resp;
            }

            $filterParams = $this->buildFilterParams($request);
            $result = $this->callWs('getCashlessSummary', $filterParams, 55);

            if (($result['status'] ?? 0) !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal mengambil ringkasan',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? [
                    'total_transaksi' => 0,
                    'total_jumlah' => 0,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Laporan Cashless summary error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat ringkasan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        if ($redirect = $this->guardPage()) {
            return $redirect;
        }

        $rows = $this->fetchForExport($request);
        if (empty($rows)) {
            return redirect()
                ->route('laporan-cashless')
                ->with('error', 'Tidak ada data untuk diexport.');
        }

        $filters = $this->filterLabels($request);
        $totals = $this->sumRows($rows);
        $filename = 'laporan_cashless_' . now('Asia/Jakarta')->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new CashlessExport($rows, $filters, $totals),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        if ($redirect = $this->guardPage()) {
            return $redirect;
        }

        $rows = $this->fetchForExport($request);
        if (empty($rows)) {
            return redirect()
                ->route('laporan-cashless')
                ->with('error', 'Tidak ada data untuk diexport.');
        }

        $filters = $this->filterLabels($request);
        $totals = $this->sumRows($rows);

        $pdf = Pdf::loadView('laporan_cashless_pdf', [
            'rows' => $rows,
            'filters' => $filters,
            'totals' => $totals,
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan_cashless_' . now('Asia/Jakarta')->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    private function fetchForExport(Request $request): array
    {
        $filterParams = $this->buildFilterParams($request);
        $all = [];
        $offset = 0;
        $limit = 2000;

        do {
            $result = $this->callWs('getCashlessData', array_merge($filterParams, [
                'limit' => $limit,
                'offset' => $offset,
            ]), 90);

            if (($result['status'] ?? 0) !== 200) {
                break;
            }

            $chunk = $result['data'] ?? [];
            if (!is_array($chunk) || empty($chunk)) {
                break;
            }

            $all = array_merge($all, $chunk);
            $offset += count($chunk);

            if (count($chunk) < $limit || count($all) >= 20000) {
                break;
            }
        } while (true);

        return $all;
    }

    private function sumRows(array $rows): array
    {
        $total = 0.0;
        foreach ($rows as $row) {
            $total += (float) ($row['jumlah'] ?? 0);
        }

        return [
            'total_transaksi' => count($rows),
            'total_jumlah' => $total,
        ];
    }

    private function filterLabels(Request $request): array
    {
        $params = $this->buildFilterParams($request);

        $sekolah = $params['sekolah'] ?? [];
        if (is_array($sekolah)) {
            $sekolah = implode(', ', $sekolah);
        }

        $kelas = $params['kelas'] ?? [];
        if (is_array($kelas)) {
            $kelas = implode(', ', $kelas);
        }

        return [
            'tgl_dari' => $params['tgl_dari'] ?? date('Y-m-d'),
            'tgl_sampai' => $params['tgl_sampai'] ?? date('Y-m-d'),
            'sekolah' => $sekolah !== '' ? $sekolah : 'Semua sekolah/unit',
            'kelas' => $kelas !== '' ? $kelas : 'Semua kelas',
            'teller' => (string) session('user.username', '-'),
            'keterangan' => $params['keterangan'] ?? 'Semua keterangan',
            'search' => $params['search'] ?? '-',
        ];
    }

    private function buildFilterParams(Request $request): array
    {
        $params = [];

        $tglDari = trim((string) $request->query('tgl_dari', ''));
        $tglSampai = trim((string) $request->query('tgl_sampai', ''));
        if ($tglDari === '' && $tglSampai === '') {
            $tglDari = date('Y-m-d');
            $tglSampai = date('Y-m-d');
        } elseif ($tglDari === '') {
            $tglDari = $tglSampai;
        } elseif ($tglSampai === '') {
            $tglSampai = $tglDari;
        }
        $params['tgl_dari'] = $tglDari;
        $params['tgl_sampai'] = $tglSampai;

        $sekolah = $this->normalizeMultiValue($request->query('sekolah', []));
        if (!empty($sekolah)) {
            $params['sekolah'] = $sekolah;
        }

        $kelas = $this->normalizeMultiValue($request->query('kelas', []));
        // kelas hanya dikirim jika sekolah sudah dipilih
        if (!empty($sekolah) && !empty($kelas)) {
            $params['kelas'] = $kelas;
        }

        $keterangan = trim((string) $request->query('keterangan', ''));
        if ($keterangan !== '') {
            $params['keterangan'] = $keterangan;
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $params['search'] = $search;
        }

        return $params;
    }

    private function normalizeMultiValue($value): array
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $v) {
                $v = trim((string) $v);
                if ($v !== '') {
                    $out[] = $v;
                }
            }
            return array_values(array_unique($out));
        }

        $str = trim((string) $value);
        if ($str === '') {
            return [];
        }

        if (str_contains($str, ',')) {
            return $this->normalizeMultiValue(explode(',', $str));
        }

        return [$str];
    }

    private function callWs(string $method, array $params = [], int $timeout = 20): array
    {
        $token = session('user.token');
        if (!$token) {
            return ['status' => 401, 'message' => 'Session tidak valid. Silakan login kembali.'];
        }

        $body = array_merge(['method' => $method, 'token' => $token], $params);

        try {
            $response = Http::connectTimeout(5)
                ->timeout($timeout)
                ->acceptJson()
                ->asJson()
                ->withHeaders(['Accept' => 'application/json'])
                ->post(self::API_URL, $body);

            $raw = (string) $response->body();
            $json = json_decode($raw, true);

            if (is_array($json)) {
                return $json;
            }

            $head = strtolower(substr(ltrim($raw), 0, 80));
            Log::warning('Laporan Cashless WS non-JSON', [
                'method' => $method,
                'http' => $response->status(),
                'body_preview' => substr($raw, 0, 300),
            ]);

            if (str_contains($head, '<!doctype') || str_contains($head, '<html')) {
                return [
                    'status' => 500,
                    'message' => 'WS mengembalikan HTML (method cashless mungkin belum di-deploy ke server WS, atau ada error PHP di ws.php).',
                ];
            }

            return [
                'status' => $response->status() ?: 500,
                'message' => 'Respons server WS tidak valid (bukan JSON).',
            ];
        } catch (\Throwable $e) {
            Log::error('Laporan Cashless WS error', [
                'method' => $method,
                'message' => $e->getMessage(),
            ]);

            return ['status' => 500, 'message' => 'Tidak dapat terhubung ke server WS: ' . $e->getMessage()];
        }
    }
}
