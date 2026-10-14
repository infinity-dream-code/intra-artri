<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UangSakuController extends Controller
{
    private const API_URL = 'http://10.99.23.111/ws_client/Malang_Arrohmah_Putri_Kepsek_Monitoring/index.php';

    /** Prefix VA uang saku per CODE01 siswa (VA SPP memakai 751xxx) */
    private const VA_UANG_SAKU_PREFIX = '752027';

    private function isHumas(): bool
    {
        if ((int) session('user.humas', 0) === 1) {
            return true;
        }
        $kelompok = strtolower(trim((string) session('user.kelompok', '')));
        return $kelompok === 'humas' || $kelompok === '1';
    }

    private function allowed(): bool
    {
        return session('user.app') === 'monitoring-kepsek'
            && !$this->isHumas()
            && strtolower(trim((string) session('user.kelompok', ''))) !== 'kepsek';
    }

    public function show()
    {
        if (!$this->allowed()) {
            return redirect()->route('dashboard');
        }

        return view('uang_saku', [
            'restrictedSekolah' => trim((string) session('user.code01', '')) ?: null,
            'canMultiSelectSekolah' => (int) session('user.is_superadmin', 0) === 1
                || strtolower(trim((string) session('user.kelompok', ''))) === 'superadmin',
        ]);
    }

    public function data(Request $request)
    {
        if (!$this->allowed()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $limit = (int) $request->query('limit', 10);
        if (!in_array($limit, [10, 25, 50, 100], true)) {
            $limit = 10;
        }
        $page = max((int) $request->query('page', 1), 1);

        $result = $this->callWs('getUangSakuSiswa', [
            'sekolah' => $this->normalizeMultiValue($request->query('sekolah', [])),
            'kelas' => $this->normalizeMultiValue($request->query('kelas', [])),
            'search' => trim((string) $request->query('search', '')),
            'bta' => trim((string) $request->query('bta', '')),
            'limit' => $limit,
            'offset' => ($page - 1) * $limit,
        ], 60);

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil data uang saku',
            ], 422);
        }

        $rows = array_map(function ($row) {
            $row['va'] = $this->buildVaUangSaku((array) $row);
            return $row;
        }, $result['data'] ?? []);

        return response()->json([
            'success' => true,
            'data' => $rows,
            'has_more' => (bool) ($result['has_more'] ?? false),
        ]);
    }

    public function filters()
    {
        if (!$this->allowed()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $sekolahKey = 'uang_saku_sekolah_' . md5((string) session('user.code01', ''));
        $sekolah = Cache::get($sekolahKey);
        if (!is_array($sekolah)) {
            $r = $this->callWs('getUangSakuFilters', ['with_bta' => '0'], 30);
            $sekolah = ($r['status'] ?? 0) === 200 ? ($r['sekolah'] ?? []) : null;
            if (is_array($sekolah)) {
                Cache::put($sekolahKey, $sekolah, now()->addHours(6));
            }
        }

        $bta = Cache::get('uang_saku_bta');
        if (!is_array($bta)) {
            $r = $this->callWs('getUangSakuFilters', ['with_bta' => '1'], 60);
            $bta = ($r['status'] ?? 0) === 200 ? ($r['bta'] ?? []) : null;
            if (is_array($bta) && !empty($bta)) {
                Cache::put('uang_saku_bta', $bta, now()->addHours(12));
            }
        }

        if ($sekolah === null && $bta === null) {
            return response()->json(['success' => false, 'message' => 'Gagal memuat filter, coba muat ulang halaman.'], 422);
        }

        return response()->json([
            'success' => true,
            'sekolah' => $sekolah ?? [],
            'bta' => $bta ?? [],
        ]);
    }

    public function summary(Request $request)
    {
        if (!$this->allowed()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $result = $this->callWs('getUangSakuSummary', [
            'sekolah' => $this->normalizeMultiValue($request->query('sekolah', [])),
            'kelas' => $this->normalizeMultiValue($request->query('kelas', [])),
            'search' => trim((string) $request->query('search', '')),
            'bta' => trim((string) $request->query('bta', '')),
        ], 90);

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal menghitung ringkasan',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'summary' => $result['summary'] ?? ['jumlah_santri' => 0, 'total_saldo' => 0],
        ]);
    }

    public function laporan(Request $request)
    {
        if (!$this->allowed()) {
            return redirect()->route('dashboard');
        }

        $custid = trim((string) $request->query('custid', ''));
        $tglDari = trim((string) $request->query('tgl_dari', ''));
        $tglSampai = trim((string) $request->query('tgl_sampai', ''));

        $result = $this->callWs('getUangSakuLedger', [
            'custid' => $custid,
            'tgl_dari' => $tglDari,
            'tgl_sampai' => $tglSampai,
        ], 60);

        if (($result['status'] ?? 0) !== 200) {
            abort(404, $result['message'] ?? 'Data uang saku tidak ditemukan');
        }

        $data = $result['data'] ?? [];
        $siswa = $data['siswa'] ?? [];

        $pdf = Pdf::loadView('uang_saku_pdf', [
            'siswa' => $siswa,
            'vaUangSaku' => $this->buildVaUangSaku($siswa),
            'tglDari' => $data['tgl_dari'] ?? '',
            'tglSampai' => $data['tgl_sampai'] ?? '',
            'saldoAwal' => (float) ($data['saldo_awal'] ?? 0),
            'rows' => $data['rows'] ?? [],
            'totalKredit' => (float) ($data['total_kredit'] ?? 0),
            'totalDebet' => (float) ($data['total_debet'] ?? 0),
            'saldoAkhir' => (float) ($data['saldo_akhir'] ?? 0),
            'saldoSekarang' => (float) ($data['saldo_sekarang'] ?? 0),
            'generatedAt' => now('Asia/Jakarta')->format('d/m/Y H:i'),
            'admin' => (string) session('user.nama', session('user.username', '')),
            'logoSrc' => $this->resolvePdfImageSrc('logoartri.png'),
            'ttdSrc' => $this->resolvePdfImageSrc('ttd.png'),
        ])->setPaper('a4', 'portrait');

        $safeName = preg_replace('/[^A-Za-z0-9_\-]+/', '_', (string) ($siswa['nama'] ?? $custid));
        $filename = 'uang_saku_' . $safeName . '_' . now('Asia/Jakarta')->format('Ymd_His') . '.pdf';
        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }

    private function buildVaUangSaku(array $siswa): string
    {
        $nocust = preg_replace('/\D/', '', (string) ($siswa['nis'] ?? ''));
        if ($nocust === '') {
            return '-';
        }
        if (str_starts_with($nocust, self::VA_UANG_SAKU_PREFIX) && strlen($nocust) > 10) {
            return $nocust;
        }
        return self::VA_UANG_SAKU_PREFIX . $nocust;
    }

    private function normalizeMultiValue($value): array
    {
        $items = is_array($value) ? $value : explode(',', (string) $value);
        $out = [];
        foreach ($items as $v) {
            $v = trim((string) $v);
            if ($v !== '' && !in_array($v, $out, true)) {
                $out[] = $v;
            }
        }
        return $out;
    }

    private function resolvePdfImageSrc(string $filename): ?string
    {
        foreach ([public_path($filename), public_path('images/' . $filename), public_path('img/' . $filename)] as $path) {
            if (!is_file($path)) {
                continue;
            }
            $binary = @file_get_contents($path);
            if ($binary === false || $binary === '') {
                continue;
            }
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = in_array($ext, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode($binary);
        }
        return null;
    }

    private function callWs(string $method, array $params = [], int $timeout = 20): array
    {
        $token = session('user.token');
        if (!$token) {
            return ['status' => 401, 'message' => 'Session tidak valid. Silakan login kembali.'];
        }

        $code01 = session('user.code01');
        if ($code01 !== null && $code01 !== '') {
            $params['code01'] = (string) $code01;
        }
        $kelompok = session('user.kelompok');
        if ($kelompok !== null && $kelompok !== '') {
            $params['kelompok'] = (string) $kelompok;
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout($timeout)
                ->acceptJson()
                ->asJson()
                ->post(self::API_URL, array_merge(['method' => $method, 'token' => $token], $params));

            $json = $response->json();
            if (is_array($json)) {
                return $json;
            }

            return ['status' => $response->status(), 'message' => 'Respons server tidak valid'];
        } catch (\Throwable $e) {
            Log::error('Uang Saku WS error', ['method' => $method, 'message' => $e->getMessage()]);
            return ['status' => 500, 'message' => 'Tidak dapat terhubung ke server'];
        }
    }
}
