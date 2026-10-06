<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KelolaUserController extends Controller
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

    private function guardApi()
    {
        if (session('user.app') !== 'monitoring-kepsek') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }
        if (!$this->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya superadmin.'], 403);
        }
        return null;
    }

    public function show()
    {
        if ($redirect = $this->guardPage()) {
            return $redirect;
        }

        return view('kelola_user');
    }

    public function index()
    {
        if ($resp = $this->guardApi()) {
            return $resp;
        }

        $result = $this->callWs('getKepsekUsers');
        if ((int) ($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal memuat data user',
            ], (int) ($result['status'] ?? 500));
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'] ?? [],
        ]);
    }

    public function store(Request $request)
    {
        if ($resp = $this->guardApi()) {
            return $resp;
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:1', 'max:100'],
            'nama'     => ['required', 'string', 'max:150'],
            'code01'   => ['nullable', 'string', 'max:100'],
            'kelompok' => ['nullable', 'string', 'max:50'],
        ]);

        $result = $this->callWs('createKepsekUser', [
            'target_username' => trim($validated['username']),
            'target_password' => $validated['password'],
            'target_nama'     => trim($validated['nama']),
            'target_code01'   => trim((string) ($validated['code01'] ?? '')),
            'target_kelompok' => trim((string) ($validated['kelompok'] ?? '')),
        ]);

        if ((int) ($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal menambah user',
            ], (int) ($result['status'] ?? 500));
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'User berhasil ditambahkan',
            'data' => $result['data'] ?? null,
        ]);
    }

    public function update(Request $request)
    {
        if ($resp = $this->guardApi()) {
            return $resp;
        }

        $validated = $request->validate([
            'idincrement' => ['required', 'integer', 'min:0'],
            'username'    => ['required', 'string', 'max:100'],
            'password'    => ['nullable', 'string', 'max:100'],
            'nama'        => ['required', 'string', 'max:150'],
            'code01'      => ['nullable', 'string', 'max:100'],
            'kelompok'    => ['nullable', 'string', 'max:50'],
        ]);

        $result = $this->callWs('updateKepsekUser', [
            'idincrement'     => (int) $validated['idincrement'],
            'target_username' => trim($validated['username']),
            'target_password' => (string) ($validated['password'] ?? ''),
            'target_nama'     => trim($validated['nama']),
            'target_code01'   => trim((string) ($validated['code01'] ?? '')),
            'target_kelompok' => trim((string) ($validated['kelompok'] ?? '')),
        ]);

        if ((int) ($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengubah user',
            ], (int) ($result['status'] ?? 500));
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'User berhasil diubah',
            'data' => $result['data'] ?? null,
        ]);
    }

    public function destroy(Request $request)
    {
        if ($resp = $this->guardApi()) {
            return $resp;
        }

        $validated = $request->validate([
            'idincrement' => ['required', 'integer', 'min:0'],
        ]);

        $result = $this->callWs('deleteKepsekUser', [
            'idincrement' => (int) $validated['idincrement'],
        ]);

        if ((int) ($result['status'] ?? 0) !== 200) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal menghapus user',
            ], (int) ($result['status'] ?? 500));
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'User berhasil dihapus',
        ]);
    }

    private function withSessionContext(array $params): array
    {
        $kelompok = session('user.kelompok');
        if ($kelompok !== null && $kelompok !== '' && !isset($params['kelompok'])) {
            $params['kelompok'] = (string) $kelompok;
        }
        return $params;
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
            Log::error('Kelola User WS error', [
                'method'  => $method,
                'message' => $e->getMessage(),
            ]);

            return ['status' => 500, 'message' => 'Tidak dapat terhubung ke server'];
        }
    }
}
