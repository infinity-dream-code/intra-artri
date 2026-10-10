<?php
/**
 * Tes koneksi WebAPI Presensi Sholat
 * Jalankan: php test_presensi_api.php
 * Opsional: php test_presensi_api.php farrelep TEST123
 */

$apiUrl = 'http://vps1.smartpayment.co.id:8888/Data/Malang_Arrohmah_Putri_PresensiSholat/WebAPI.php';
$jwtSecret = 'a7c2a8a9b3c4a5a6a7a8a9b0c1a2a3';
$username = $argv[1] ?? 'farrelep';
$nokartu  = $argv[2] ?? 'TEST123';
$timeout  = 20; // detik — sama seperti app (15s) + buffer

function b64url(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function makeJwt(array $payload, string $secret): string
{
    $header = b64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT'], JSON_UNESCAPED_SLASHES));
    $body   = b64url(json_encode($payload, JSON_UNESCAPED_SLASHES));
    $sig    = b64url(hash_hmac('sha256', "$header.$body", $secret, true));
    return "$header.$body.$sig";
}

function callApi(string $url, string $token, int $timeout): array
{
    $full = $url . '?token=' . urlencode($token);
    $t0 = microtime(true);

    $ch = curl_init($full);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);

    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $errno = curl_errno($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ms   = (int) ((microtime(true) - $t0) * 1000);
    curl_close($ch);

    return [
        'http'   => $code,
        'ms'     => $ms,
        'errno'  => $errno,
        'error'  => $err,
        'body'   => is_string($body) ? $body : '',
        'url'    => $full,
    ];
}

function printResult(string $label, array $r): void
{
    echo str_repeat('=', 60) . PHP_EOL;
    echo "TEST: $label" . PHP_EOL;
    echo "HTTP: {$r['http']} | waktu: {$r['ms']} ms | curl_errno: {$r['errno']}" . PHP_EOL;

    if ($r['error'] !== '') {
        echo "ERROR: {$r['error']}" . PHP_EOL;
        if ($r['errno'] === 28) {
            echo ">>> TIMEOUT — server tidak merespons dalam batas waktu." . PHP_EOL;
        } elseif (in_array($r['errno'], [7, 6, 28], true)) {
            echo ">>> GAGAL KONEKSI / DNS / TIMEOUT." . PHP_EOL;
        }
    } else {
        echo "STATUS: " . ($r['http'] >= 200 && $r['http'] < 300 ? 'OK (ada respons)' : 'Respons non-2xx') . PHP_EOL;
    }

    $preview = substr($r['body'], 0, 500);
    echo "BODY: " . ($preview !== '' ? $preview : '(kosong)') . PHP_EOL;
    echo PHP_EOL;
}

echo "API : $apiUrl" . PHP_EOL;
echo "User: $username | NOKARTU: $nokartu | Timeout: {$timeout}s" . PHP_EOL;
echo PHP_EOL;

// 1) Tanpa token
$t0 = microtime(true);
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => $timeout,
    CURLOPT_CONNECTTIMEOUT => 8,
]);
$body = curl_exec($ch);
$err  = curl_error($ch);
$errno = curl_errno($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$ms   = (int) ((microtime(true) - $t0) * 1000);
curl_close($ch);
printResult('Tanpa token (probe server hidup)', [
    'http' => $code, 'ms' => $ms, 'errno' => $errno, 'error' => $err,
    'body' => is_string($body) ? $body : '', 'url' => $apiUrl,
]);

// 2) Method yang dipakai app
$tests = [
    'LogMarifahTodayRequest' => ['METHOD' => 'LogMarifahTodayRequest', 'USERNAME' => $username],
    'LogMarifahRequest'      => ['METHOD' => 'LogMarifahRequest', 'USERNAME' => $username, 'HARIOUT' => date('Y-m-d', strtotime('-1 day'))],
    'POSTSholat'             => ['METHOD' => 'POSTSholat', 'NOKARTU' => $nokartu, 'USERNAME' => $username],
    'POSTHaid'               => ['METHOD' => 'POSTHaid', 'NOKARTU' => $nokartu, 'USERNAME' => $username],
];

foreach ($tests as $label => $payload) {
    $token = makeJwt($payload, $jwtSecret);
    $r = callApi($apiUrl, $token, $timeout);
    printResult($label, $r);
}

echo "Selesai." . PHP_EOL;
