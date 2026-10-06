<?php

namespace App\Http\Controllers;

use App\Exports\ImportPenagihanTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

class ImportPenagihanController extends Controller
{
    private const API_URL = 'http://10.99.23.111/ws_client/Malang_Arrohmah_Putri_Kepsek_Monitoring/index.php';

    private function isSuperAdmin(): bool
    {
        if ((int) session('user.is_superadmin', 0) === 1) {
            return true;
        }
        $kelompok = strtolower(trim((string) session('user.kelompok', '')));
        return $kelompok === 'superadmin';
    }

    private function guardPage()
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return redirect()->route('dashboard');
        }
        if (!$this->isSuperAdmin()) {
            return redirect()->route('dashboard.monitoring-kepsek');
        }
        return null;
    }

    public function show()
    {
        if ($redirect = $this->guardPage()) {
            return $redirect;
        }

        return view('import_penagihan');
    }

    public function data(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }
        if (!$this->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya superadmin.'], 403);
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 15);
        if ($perPage < 5) {
            $perPage = 5;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }
        $q = trim((string) $request->query('q', ''));

        $result = $this->callWs('listRiwayatPenagihan', [
            'page' => $page,
            'per_page' => $perPage,
            'q' => $q,
        ], 30);

        if ((int) ($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal memuat data riwayat penagihan',
            ], (int) ($result['status'] ?? 500));
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'] ?? [],
            'meta' => $result['meta'] ?? [
                'page' => $page,
                'per_page' => $perPage,
                'total' => 0,
                'last_page' => 1,
            ],
        ]);
    }

    public function downloadTemplate()
    {
        if ($redirect = $this->guardPage()) {
            return $redirect;
        }

        $filename = 'template_import_penagihan.xlsx';
        return Excel::download(new ImportPenagihanTemplateExport(), $filename);
    }

    public function import(Request $request)
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }
        if (!$this->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya superadmin.'], 403);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $file = $request->file('file');
        try {
            $import = new class implements ToArray {
                public function array(array $array)
                {
                }
            };
            $sheets = Excel::toArray($import, $file);
        } catch (\Throwable $e) {
            Log::error('Import penagihan parse error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
            ], 422);
        }

        $sheet = $sheets[0] ?? [];
        if (count($sheet) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File kosong atau hanya berisi header',
            ], 422);
        }

        $header = array_map(function ($h) {
            return strtolower(trim((string) $h));
        }, $sheet[0] ?? []);

        $map = $this->resolveHeaderMap($header);
        if (!isset($map['nis']) || !isset($map['tanggal_komunikasi']) || !isset($map['media_komunikasi']) || !isset($map['hasil_komunikasi'])) {
            return response()->json([
                'success' => false,
                'message' => 'Header Excel wajib memuat: nis, tanggal komunikasi, media komunikasi, hasil komunikasi',
            ], 422);
        }

        $rows = [];
        for ($i = 1; $i < count($sheet); $i++) {
            $line = $sheet[$i] ?? [];
            if (!is_array($line)) {
                continue;
            }

            $nis = trim((string) ($line[$map['nis']] ?? ''));
            $tanggal = $line[$map['tanggal_komunikasi']] ?? '';
            $media = trim((string) ($line[$map['media_komunikasi']] ?? ''));
            $hasil = trim((string) ($line[$map['hasil_komunikasi']] ?? ''));
            $nama = isset($map['nama']) ? trim((string) ($line[$map['nama']] ?? '')) : '';
            $rencana = isset($map['rencana_pembayaran']) ? trim((string) ($line[$map['rencana_pembayaran']] ?? '')) : '';
            $catatan = isset($map['catatan']) ? trim((string) ($line[$map['catatan']] ?? '')) : '';
            $no = isset($map['no']) ? trim((string) ($line[$map['no']] ?? '')) : (string) ($i + 1);

            if ($nis === '' && $tanggal === '' && $media === '' && $hasil === '' && $nama === '') {
                continue;
            }

            $rows[] = [
                'no' => $no,
                'nis' => $nis,
                'nama' => $nama,
                'tanggal_komunikasi' => is_numeric($tanggal) ? (string) $tanggal : trim((string) $tanggal),
                'media_komunikasi' => $media,
                'hasil_komunikasi' => $hasil,
                'rencana_pembayaran' => $rencana,
                'catatan' => $catatan,
            ];
        }

        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada baris data yang bisa diimport',
            ], 422);
        }

        $result = $this->callWs('importRiwayatPenagihan', [
            'rows' => $rows,
            'admin' => (string) session('user.nama', session('user.username', '')),
        ], 120);

        if ((int) ($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal import penagihan',
            ], (int) ($result['status'] ?? 500));
        }

        $data = $result['data'] ?? [];
        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Import selesai',
            'success_count' => (int) ($data['success'] ?? 0),
            'failed_count' => (int) ($data['failed'] ?? 0),
            'errors' => $data['errors'] ?? [],
        ]);
    }

    private function resolveHeaderMap(array $header): array
    {
        $aliases = [
            'no' => ['no', 'nomor', 'no.', '#'],
            'nis' => ['nis', 'nocust', 'no cust', 'no_cust', 'nis / no cust'],
            'nama' => ['nama', 'nama anak', 'nama_anak', 'nmcust', 'nama siswa', 'nama santri'],
            'tanggal_komunikasi' => ['tanggal komunikasi', 'tanggal_komunikasi', 'tanggal', 'tgl komunikasi', 'tgl'],
            'media_komunikasi' => ['media komunikasi', 'media_komunikasi', 'media'],
            'hasil_komunikasi' => ['hasil komunikasi', 'hasil_komunikasi', 'hasil'],
            'rencana_pembayaran' => ['rencana pembayaran', 'rencana_pembayaran', 'rencana bayar', 'rencana'],
            'catatan' => ['catatan', 'note', 'keterangan'],
        ];

        $map = [];
        foreach ($header as $idx => $col) {
            $col = preg_replace('/\s+/', ' ', trim((string) $col));
            foreach ($aliases as $key => $names) {
                if (isset($map[$key])) {
                    continue;
                }
                foreach ($names as $name) {
                    if ($col === $name) {
                        $map[$key] = $idx;
                        break 2;
                    }
                }
            }
        }

        return $map;
    }

    private function callWs(string $method, array $params = [], int $timeout = 20): array
    {
        $token = session('user.token');
        if (!$token) {
            return ['status' => 401, 'message' => 'Session tidak valid. Silakan login kembali.'];
        }

        $body = array_merge([
            'method' => $method,
            'token' => $token,
            'kelompok' => (string) session('user.kelompok', ''),
        ], $params);

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
                'status' => $response->status(),
                'message' => 'Respons server tidak valid',
            ];
        } catch (\Throwable $e) {
            Log::error('Import penagihan WS error', [
                'method' => $method,
                'message' => $e->getMessage(),
            ]);

            return ['status' => 500, 'message' => 'Tidak dapat terhubung ke server'];
        }
    }
}
