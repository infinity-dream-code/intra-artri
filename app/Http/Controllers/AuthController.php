<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    private const API_BASE_URL_PERIZINAN = 'http://vps1.smartpayment.co.id:8888/Data/Malang_Arrohmah_Putri_Perizinan/WebAPI.php';
    private const API_BASE_URL_PRESENSI_SHOLAT = 'http://vps1.smartpayment.co.id:8888/Data/Malang_Arrohmah_Putri_PresensiSholat/WebAPI.php';
    private const API_BASE_URL_MONITORING_KEPSEK = 'http://10.99.23.111/ws_client/Malang_Arrohmah_Putri_Kepsek_Monitoring/index.php';
    private const JWT_SECRET = 'a7c2a8a9b3c4a5a6a7a8a9b0c1a2a3';

    public function showLogin()
    {
        if (session()->has('user') && session('user.username')) {
            return redirect()->route($this->dashboardRouteName(session('user.app')));
        }
        return view('login');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login.form');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'app' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        Log::info('Login attempt received', [
            'app' => $validated['app'],
            'username' => $validated['username'],
        ]);

        if ($validated['app'] === 'monitoring-kepsek') {
            return $this->loginMonitoringKepsek($request, $validated);
        }

        if ($validated['app'] === 'laporan-cashless') {
            return $this->loginLaporanCashless($request, $validated);
        }

        $apiBaseUrl = $this->apiBaseUrlFor($validated['app']);

        $payload = [
            'METHOD'   => 'LoginRequest',
            'USERNAME' => $validated['username'],
            'PASSWORD' => $validated['password'],
        ];

        $token = $this->generateJwt($payload);

        try {
            Log::info('Sending request to external API', [
                'url' => $apiBaseUrl,
                'token_preview' => substr($token, 0, 40) . '...',
            ]);

            $response = Http::timeout(15)
                ->get($apiBaseUrl . '?token=' . urlencode($token));

            Log::info('External API response raw', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error calling external API', [
                'message' => $e->getMessage(),
            ]);
            return back()
                ->withInput($request->except('password'))
                ->with('login_error', 'Tidak dapat terhubung ke server. Silakan coba lagi.');
        }

        if (! $response->ok()) {
            return back()
                ->withInput($request->except('password'))
                ->with('login_error', 'Terjadi kesalahan pada server. Silakan coba lagi.');
        }

        $data = $response->json();

        if (isset($data['KodeRespon']) && (int) $data['KodeRespon'] === 1) {
            $username = $validated['username'];

            $request->session()->put('user', [
                'username' => $username,
                'app'      => $validated['app'],
            ]);

            $izinTypesSingle = $this->fetchIzinTypes('SingleEntryListRequest');
            $request->session()->put('izin_types_single', $izinTypesSingle);

            $izinTypesMultiple = $this->fetchIzinTypes('MultipleEntryListRequest');
            $request->session()->put('izin_types_multiple', $izinTypesMultiple);

            $izinTypesSpecial = $this->fetchIzinTypes('SpecialEntryListRequest');
            $request->session()->put('izin_types_special', $izinTypesSpecial);

            return redirect()
                ->route($this->dashboardRouteName($validated['app']))
                ->with('login_success', 'Login berhasil.');
        }

        $message = $data['PesanRespon'] ?? 'Login gagal. Akses Ditolak.';

        return back()
            ->withInput($request->except('password'))
            ->with('login_error', $message);
    }

    private function generateJwt(array $payload): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));

        $signingInput = $headerEncoded . '.' . $payloadEncoded;
        $signature = hash_hmac('sha256', $signingInput, self::JWT_SECRET, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return $signingInput . '.' . $signatureEncoded;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function fetchIzinTypes(string $method): array
    {
        $now = now()->timestamp;
        $payload = [
            'METHOD' => $method,
            'iat'    => $now,
            'exp'    => $now + 300,
        ];

        $token = $this->generateJwt($payload);

        try {
            $response = Http::timeout(10)
                ->get(self::API_BASE_URL_PERIZINAN . '?token=' . urlencode($token));

            if ($response->ok()) {
                $data = $response->json();

                if (is_array($data) && isset($data[0]['ListRespone'])) {
                    return $data[0]['ListRespone'];
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error fetching izin types', [
                'method' => $method,
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    private function loginMonitoringKepsek(Request $request, array $validated)
    {
        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->asJson()
                ->post(self::API_BASE_URL_MONITORING_KEPSEK, [
                    'method'   => 'login',
                    'username' => $validated['username'],
                    'password' => $validated['password'],
                ]);

            Log::info('Monitoring Kepsek login response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Monitoring Kepsek login error', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->except('password'))
                ->with('login_error', 'Tidak dapat terhubung ke server. Silakan coba lagi.');
        }

        $data = $response->json();

        if (($data['status'] ?? 0) === 200 && ! empty($data['data']['token'])) {
            $kelompok = trim((string) ($data['data']['kelompok'] ?? ''));
            $kelompokNorm = strtolower($kelompok);
            $humas = (int) ($data['data']['humas'] ?? 0);
            if ($humas !== 1 && ($kelompokNorm === 'humas' || $kelompokNorm === '1')) {
                $humas = 1;
            }
            $code01 = $data['data']['code01'] ?? null;
            if (is_string($code01)) {
                $code01 = trim($code01);
                if ($code01 === '') {
                    $code01 = null;
                }
            }

            $isSuperadmin = $kelompokNorm === 'superadmin' ? 1 : 0;

            $request->session()->put('user', [
                'username'      => $validated['username'],
                'nama'          => $data['data']['nama'] ?? $validated['username'],
                'app'           => 'monitoring-kepsek',
                'token'         => $data['data']['token'],
                'code01'        => $code01,
                'kelompok'      => $kelompok,
                'humas'         => $humas,
                'is_superadmin' => $isSuperadmin,
            ]);

            $redirectRoute = $humas === 1
                ? 'kepsek.tagihan-periode'
                : 'dashboard.monitoring-kepsek';

            return redirect()
                ->route($redirectRoute)
                ->with('login_success', 'Login berhasil.');
        }

        $message = $data['message'] ?? 'Login gagal. Akses Ditolak.';

        return back()
            ->withInput($request->except('password'))
            ->with('login_error', $message);
    }

    private function loginLaporanCashless(Request $request, array $validated)
    {
        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->asJson()
                ->post(self::API_BASE_URL_MONITORING_KEPSEK, [
                    'method'   => 'loginCashless',
                    'username' => $validated['username'],
                    'password' => $validated['password'],
                ]);

            Log::info('Laporan Cashless login response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Laporan Cashless login error', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->except('password'))
                ->with('login_error', 'Tidak dapat terhubung ke server. Silakan coba lagi.');
        }

        $data = $response->json();

        if (($data['status'] ?? 0) === 200 && ! empty($data['data']['token'])) {
            $request->session()->put('user', [
                'username' => $validated['username'],
                'nama'     => $data['data']['nama'] ?? $validated['username'],
                'app'      => 'laporan-cashless',
                'token'    => $data['data']['token'],
            ]);

            return redirect()
                ->route('laporan-cashless')
                ->with('login_success', 'Login berhasil.');
        }

        $message = $data['message'] ?? 'Login gagal. Akses Ditolak.';

        return back()
            ->withInput($request->except('password'))
            ->with('login_error', $message);
    }

    private function apiBaseUrlFor(?string $app): string
    {
        return match ($app) {
            'presensi-sholat'   => self::API_BASE_URL_PRESENSI_SHOLAT,
            'monitoring-kepsek', 'laporan-cashless' => self::API_BASE_URL_MONITORING_KEPSEK,
            default             => self::API_BASE_URL_PERIZINAN,
        };
    }

    private function dashboardRouteName(?string $app): string
    {
        return match ($app) {
            'presensi-sholat'   => 'dashboard.presensi-sholat',
            'monitoring-kepsek' => 'dashboard.monitoring-kepsek',
            'laporan-cashless'  => 'laporan-cashless',
            default             => 'dashboard',
        };
    }

    public function showGantiPassword()
    {
        if (session('user.app') === 'presensi-sholat') {
            return redirect()->route('presensi.account.ganti-password');
        }
        return view('ganti_password');
    }

    public function showGantiPasswordPresensi()
    {
        if (session('user.app') !== 'presensi-sholat') {
            return redirect()->route('account.ganti-password');
        }
        return view('ganti_password_presensi');
    }

    public function gantiPassword(Request $request)
    {
        $validated = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:3'],
            'confirm_password' => ['required', 'string', 'same:new_password'],
        ]);

        $username = session('user.username');
        if (!$username) {
            return back()->with('password_error', 'Session tidak valid. Silakan login kembali.');
        }

        $apiBaseUrl = $this->apiBaseUrlFor(session('user.app'));

        $oldPassword = $validated['old_password'];

        $payload = [
            'METHOD'       => 'RequestNewPassword',
            'USERNAME'     => $username,
            'PASSWORD'     => $oldPassword,
            'NEWPASSWORD'  => $validated['new_password'],
            'NEWPASSWORD2' => $validated['confirm_password'],
        ];

        Log::info('Ganti password payload', [
            'payload' => $payload,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_SLASHES),
        ]);

        $token = $this->generateJwt($payload);

        $parts = explode('.', $token);
        if (count($parts) === 3) {
            $decodedPayload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            Log::info('Ganti password decoded token payload', ['decoded' => $decodedPayload]);
        }

        try {
            Log::info('Ganti password request', [
                'username' => $username,
                'payload' => $payload,
                'token_preview' => substr($token, 0, 50) . '...',
                'token_length' => strlen($token),
            ]);

            $url = $apiBaseUrl . '?token=' . urlencode($token);
            Log::info('Ganti password API URL', [
                'url_preview' => substr($url, 0, 100) . '...',
                'url_length' => strlen($url),
            ]);

            $response = Http::timeout(15)
                ->get($url);

            if ($response->status() === 500 && empty($response->body())) {
                Log::info('Trying POST method for ganti password');

                $response = Http::timeout(15)
                    ->post($url);

                if ($response->status() === 500 && empty($response->body())) {
                    Log::info('Trying POST with token in form body');
                    $response = Http::timeout(15)
                        ->asForm()
                        ->post($apiBaseUrl, ['token' => $token]);
                }

                if ($response->status() === 500 && empty($response->body())) {
                    Log::info('Trying POST with token in JSON body');
                    $response = Http::timeout(15)
                        ->asJson()
                        ->post($apiBaseUrl, ['token' => $token]);
                }
            }

            Log::info('Ganti password API response', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
                'body_length' => strlen($response->body()),
                'successful' => $response->ok(),
            ]);

            if (!$response->ok()) {
                $status = $response->status();
                $body = $response->body();

                $errorMsg = 'Terjadi kesalahan pada server (HTTP ' . $status . ').';

                if ($body) {
                    $jsonData = json_decode($body, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['PesanRespon'])) {
                        $errorMsg = $jsonData['PesanRespon'];
                    } else {
                        $errorMsg .= ' ' . substr($body, 0, 150);
                    }
                } else {
                    $errorMsg = 'Server API mengembalikan error tanpa pesan. Kemungkinan: password lama salah, endpoint tidak tersedia, atau server bermasalah. Silakan coba lagi atau hubungi administrator.';
                }

                Log::error('Ganti password failed', [
                    'status' => $status,
                    'body' => $body,
                    'body_length' => strlen($body),
                    'username' => $username,
                ]);

                return back()
                    ->withInput($request->except(['old_password', 'new_password', 'confirm_password']))
                    ->with('password_error', $errorMsg);
            }

            $data = $response->json();

            Log::info('Ganti password response data', ['data' => $data]);

            if (isset($data['KodeRespon']) && (int) $data['KodeRespon'] === 1) {
                return back()->with('password_success', 'Password berhasil diubah.');
            }

            $message = $data['PesanRespon'] ?? 'Gagal mengubah password.';
            return back()
                ->withInput($request->except(['old_password', 'new_password', 'confirm_password']))
                ->with('password_error', $message);
        } catch (\Throwable $e) {
            Log::error('Error changing password', [
                'message' => $e->getMessage(),
            ]);
            return back()
                ->withInput($request->except(['old_password', 'new_password', 'confirm_password']))
                ->with('password_error', 'Tidak dapat terhubung ke server. Silakan coba lagi.');
        }
    }
}
