<?php
/**
 * Tes koneksi WebAPI Presensi Sholat
 * Jalankan: php test_presensi_api.php [username] [nokartu] [timeout_detik]
 */

$apiUrl = 'http://vps1.smartpayment.co.id:8888/Data/Malang_Arrohmah_Putri_PresensiSholat/WebAPI.php';
$jwtSecret = 'a7c2a8a9b3c4a5a6a7a8a9b0c1a2a3';
$username = $argv[1] ?? 'farrelep';
$nokartu  = $argv[2] ?? 'TEST123';
$timeout  = (int) ($argv[3] ?? 20);

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

function callApi(string $url, int $timeout): array
{
    $t0 = microtime(true);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $body  = curl_exec($ch);
    $errno = curl_errno($ch);
    $err   = curl_error($ch);
    $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'http'  => $code,
        'ms'    => (int) ((microtime(true) - $t0) * 1000),
        'errno' => $errno,
        'error' => $err,
        'body'  => is_string($body) ? $body : '',
    ];
}

function printResult(string $label, ?array $payload, ?string $token, string $url, array $r): void
{
    echo str_repeat('=', 70) . PHP_EOL;
    echo "TEST    : $label" . PHP_EOL;
    if ($payload !== null) {
        echo "PAYLOAD : " . json_encode($payload, JSON_UNESCAPED_SLASHES) . PHP_EOL;
        echo "TOKEN   : $token" . PHP_EOL;
    }
    echo "URL     : $url" . PHP_EOL;
    echo "HASIL   : HTTP {$r['http']} | {$r['ms']} ms | curl_errno {$r['errno']}" . PHP_EOL;
    if ($r['error'] !== '') {
        echo "ERROR   : {$r['error']}" . ($r['errno'] === 28 ? '  >>> TIMEOUT' : '') . PHP_EOL;
    }
    $preview = substr($r['body'], 0, 500);
    echo "BODY    : " . ($preview !== '' ? $preview : '(kosong)') . PHP_EOL . PHP_EOL;
}

echo "API: $apiUrl | user: $username | nokartu: $nokartu | timeout: {$timeout}s" . PHP_EOL . PHP_EOL;

printResult('Tanpa token (probe server hidup)', null, null, $apiUrl, callApi($apiUrl, $timeout));

$tests = [
    'LogMarifahTodayRequest' => ['METHOD' => 'LogMarifahTodayRequest', 'USERNAME' => $username],
    'LogMarifahRequest'      => ['METHOD' => 'LogMarifahRequest', 'USERNAME' => $username, 'HARIOUT' => date('Y-m-d', strtotime('-1 day'))],
    'POSTSholat'             => ['METHOD' => 'POSTSholat', 'NOKARTU' => $nokartu, 'USERNAME' => $username],
    'POSTHaid'               => ['METHOD' => 'POSTHaid', 'NOKARTU' => $nokartu, 'USERNAME' => $username],
];

foreach ($tests as $label => $payload) {
    $token = makeJwt($payload, $jwtSecret);
    $url = $apiUrl . '?token=' . urlencode($token);
    printResult($label, $payload, $token, $url, callApi($url, $timeout));
}

echo "Selesai." . PHP_EOL;
