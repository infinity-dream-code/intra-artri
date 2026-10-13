<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UangSakuController extends Controller
{
    private const API_URL = 'http://10.99.23.111/ws_client/Malang_Arrohmah_Putri_Kepsek_Monitoring/index.php';

    /** Prefix VA uang saku per CODE01 siswa (VA SPP memakai 751xxx) */
    private const VA_UANG_SAKU_PREFIX = [
        '101' => '752027',
        '102' => '752028',
        '103' => '752022',
        '104' => '752031',
    ];

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
            'limit' => $limit,
            'offset' => ($page - 1) * $limit,
        ], 60);

        if (($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil data uang saku',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'] ?? [],
            'has_more' => (bool) ($result['has_more'] ?? false),
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
        return $pdf->stream('uang_saku_' . $safeName . '_' . now('Asia/Jakarta')->format('Ymd_His') . '.pdf');
    }

    private function buildVaUangSaku(array $siswa): string
    {
        $nocust = preg_replace('/\D/', '', (string) ($siswa['nis'] ?? ''));
        if ($nocust === '') {
            return '-';
        }
        $prefix = self::VA_UANG_SAKU_PREFIX[trim((string) ($siswa['code01'] ?? ''))] ?? '';
        if ($prefix !== '' && str_starts_with($nocust, $prefix)) {
            return $nocust;
        }
        return $prefix . $nocust;
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
