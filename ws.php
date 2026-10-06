<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set("Asia/Jakarta");

require __DIR__ . "/config/DbClass.php";
require __DIR__ . "/config/conn.php";
require __DIR__ . "/config/jwt.php";

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

function writeLog($data): void
{
    $line = "[" . date("Y-m-d H:i:s") . "] ";
    $line .= is_array($data) || is_object($data)
        ? json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        : (string) $data;
    file_put_contents(__DIR__ . "/error.log", $line . "\n", FILE_APPEND);
}

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        writeLog(["level" => "ERROR", "event" => "ENV_NOT_FOUND", "path" => $path]);
        http_response_code(500);
        echo json_encode(["status" => 500, "message" => "ENV tidak ditemukan"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === "" || str_starts_with($line, "#")) continue;
        if (!str_contains($line, "=")) continue;
        [$name, $value] = explode("=", $line, 2);
        $name = trim($name);
        $value = trim($value);
        $value = trim($value, "\"'");
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

function getJsonInput(): array
{
    $raw = file_get_contents("php://input");
    $json = json_decode($raw, true);
    if (is_array($json)) return $json;
    if (!empty($_POST)) return $_POST;
    return [];
}

function dbConnectPdo(): PDO
{
    $host = (string) ($_ENV["DB_HOST"] ?? "");
    $user = (string) ($_ENV["DB_USERNAME"] ?? "");
    $pass = (string) ($_ENV["DB_PASSWORD"] ?? "");
    $port = (string) ($_ENV["DB_PORT"] ?? "3306");
    $name = (string) ($_ENV["DB_DATABASE"] ?? "");

    if ($host === "" || $user === "" || $name === "") {
        throw new RuntimeException("ENV_DB_INCOMPLETE");
    }

    $conn = new conn();
    $pdo = $conn->DBConnect([
        "host" => $host,
        "user" => $user,
        "pass" => $pass,
        "port" => $port,
        "name" => $name,
    ]);

    if (!$pdo instanceof PDO) {
        throw new RuntimeException("DBConnect tidak mengembalikan PDO");
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    return $pdo;
}

function login(array $req): void
{
    $username = trim((string) ($req["username"] ?? ""));
    $password = trim((string) ($req["password"] ?? ""));

    if ($username === "" || $password === "") {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Username dan password wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();

    $stmt = $pdo->prepare("SELECT * FROM kepsek_user WHERE username = :username LIMIT 1");
    $stmt->bindValue(":username", $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(["status" => 401, "message" => "Username atau password salah"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $passwordHash = (string) ($user["password"] ?? "");
    $valid = false;

    if (strlen($passwordHash) === 32 && ctype_xdigit($passwordHash)) {
        $valid = md5($password) === $passwordHash;
    } elseif (strlen($passwordHash) === 64 && ctype_xdigit($passwordHash)) {
        $valid = hash("sha256", $password) === $passwordHash;
    } elseif (str_starts_with($passwordHash, "$2")) {
        $valid = password_verify($password, $passwordHash);
    } else {
        $valid = $password === $passwordHash;
    }

    if (!$valid) {
        http_response_code(401);
        echo json_encode(["status" => 401, "message" => "Username atau password salah"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $key = (string) ($_ENV["JWT_KEY"] ?? "");
    if ($key === "") {
        http_response_code(500);
        echo json_encode(["status" => 500, "message" => "JWT_KEY belum di set"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $code01Raw = isset($user["CODE01"]) ? trim((string) $user["CODE01"]) : "";
    $code01 = $code01Raw !== "" ? $code01Raw : null;

    // kelompok: "humas" / 1 = humas (lock daftar ulang)
    //           "superadmin" / 0 / kosong = admin biasa (filter mengikuti CODE01)
    $kelompokRaw = trim((string) ($user["kelompok"] ?? ""));
    $kelompokNorm = strtolower($kelompokRaw);
    $humas = ($kelompokNorm === "humas" || $kelompokNorm === "1") ? 1 : 0;

    $payload = [
        "user_id"  => $user["idincrement"],
        "username" => $user["username"],
        "nama"     => $user["nama"],
        "code01"   => $code01,
        "kelompok" => $kelompokRaw,
        "humas"    => $humas,
        "iat"      => time(),
        "exp"      => time() + 86400,
    ];

    $jwt = new JWT();
    $token = $jwt->encode($payload, $key, "HS256");

    http_response_code(200);
    echo json_encode([
        "status"  => 200,
        "message" => "Login berhasil",
        "data"    => [
            "token"    => $token,
            "nama"     => $user["nama"],
            "username" => $user["username"],
            "code01"   => $code01,
            "kelompok" => $kelompokRaw,
            "humas"    => $humas,
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function normalizeMultiValue($value): array
{
    if (!is_array($value)) {
        $value = $value !== null && $value !== "" ? [$value] : [];
    }

    return array_values(array_filter(
        array_map(fn($v) => trim((string) $v), $value),
        fn($v) => $v !== ""
    ));
}

function parseCode01List(?string $code01): array
{
    if ($code01 === null) {
        return [];
    }
    $raw = trim($code01);
    if ($raw === "") {
        return [];
    }
    $parts = preg_split('/\s*,\s*/', $raw) ?: [];
    $out = [];
    foreach ($parts as $p) {
        $p = trim((string) $p);
        if ($p !== "" && !in_array($p, $out, true)) {
            $out[] = $p;
        }
    }
    return $out;
}

/**
 * kepsek_user.CODE01 = nama unit (sama dengan mst_kelas.unit), bisa multi dipisah koma.
 * Request sekolah bisa string atau array (multi-centang).
 */
function buildSekolahFilterSql(array $req, ?string $code01, array &$params, string $prefix = "sek"): string
{
    $allowed = parseCode01List($code01);
    $selected = normalizeMultiValue($req["sekolah"] ?? []);

    if (!empty($allowed)) {
        $use = $allowed;
        if (!empty($selected)) {
            $use = array_values(array_intersect($selected, $allowed));
            if (empty($use)) {
                // pilihan di luar izin → kosongkan hasil
                $params[":{$prefix}_none"] = "__none__";
                return " AND 1=0 ";
            }
        }
        $ph = [];
        foreach ($use as $i => $val) {
            $key = ":{$prefix}_$i";
            $ph[] = $key;
            $params[$key] = $val;
        }
        return " AND mk.unit IN (" . implode(",", $ph) . ") ";
    }

    if (empty($selected)) {
        return "";
    }

    $ph = [];
    foreach ($selected as $i => $val) {
        $key = ":{$prefix}_$i";
        $ph[] = $key;
        $params[$key] = $val;
    }
    return " AND mk.unit IN (" . implode(",", $ph) . ") ";
}

function resolveSekolahRestricted(array $req, ?string $code01): string
{
    $allowed = parseCode01List($code01);
    $selected = normalizeMultiValue($req["sekolah"] ?? []);

    if (!empty($allowed)) {
        if (!empty($selected)) {
            $use = array_values(array_intersect($selected, $allowed));
            return $use[0] ?? "";
        }
        if (count($allowed) === 1) {
            return $allowed[0];
        }
        return "";
    }

    return $selected[0] ?? "";
}

function isDaftarUlangOnly(array $req): bool
{
    if (isset($req["humas"]) && (int) $req["humas"] === 1) {
        return true;
    }
    $kelompok = strtolower(trim((string) ($req["kelompok"] ?? "")));
    return $kelompok === "humas" || $kelompok === "1";
}

function isSuperAdmin(array $req): bool
{
    $kelompok = strtolower(trim((string) ($req["kelompok"] ?? "")));
    return $kelompok === "superadmin";
}

function requireSuperAdmin(array $req): void
{
    if (!isSuperAdmin($req)) {
        http_response_code(403);
        echo json_encode(["status" => 403, "message" => "Akses ditolak. Hanya superadmin."], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function hashKepsekPassword(string $plain): string
{
    return md5($plain);
}

function getKepsekUsers(array $req): void
{
    requireSuperAdmin($req);

    $pdo = dbConnectPdo();
    $stmt = $pdo->query("
        SELECT
            idincrement,
            username,
            nama,
            CODE01 AS code01,
            kelompok
        FROM kepsek_user
        ORDER BY idincrement ASC
    ");
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $code01 = isset($row["code01"]) ? trim((string) $row["code01"]) : "";
        $row["code01"] = $code01 !== "" ? $code01 : null;
        $kelompok = isset($row["kelompok"]) ? trim((string) $row["kelompok"]) : "";
        $row["kelompok"] = $kelompok !== "" ? $kelompok : null;
    }
    unset($row);

    http_response_code(200);
    echo json_encode(["status" => 200, "data" => $rows], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function createKepsekUser(array $req): void
{
    requireSuperAdmin($req);

    $username = trim((string) ($req["target_username"] ?? $req["user_username"] ?? ""));
    $password = (string) ($req["target_password"] ?? $req["user_password"] ?? "");
    $nama = trim((string) ($req["target_nama"] ?? $req["user_nama"] ?? ""));
    $code01Raw = trim((string) ($req["target_code01"] ?? $req["user_code01"] ?? ""));
    $kelompokRaw = trim((string) ($req["target_kelompok"] ?? $req["user_kelompok"] ?? ""));

    if ($username === "" || $password === "" || $nama === "") {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Username, password, dan nama wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();

    $check = $pdo->prepare("SELECT idincrement FROM kepsek_user WHERE username = :username LIMIT 1");
    $check->bindValue(":username", $username, PDO::PARAM_STR);
    $check->execute();
    if ($check->fetch()) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Username sudah digunakan"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $code01 = $code01Raw !== "" ? $code01Raw : null;
    $kelompok = $kelompokRaw !== "" ? $kelompokRaw : null;

    $stmt = $pdo->prepare("
        INSERT INTO kepsek_user (username, password, nama, CODE01, kelompok)
        VALUES (:username, :password, :nama, :code01, :kelompok)
    ");
    $stmt->bindValue(":username", $username, PDO::PARAM_STR);
    $stmt->bindValue(":password", hashKepsekPassword($password), PDO::PARAM_STR);
    $stmt->bindValue(":nama", $nama, PDO::PARAM_STR);
    if ($code01 === null) {
        $stmt->bindValue(":code01", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":code01", $code01, PDO::PARAM_STR);
    }
    if ($kelompok === null) {
        $stmt->bindValue(":kelompok", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":kelompok", $kelompok, PDO::PARAM_STR);
    }
    $stmt->execute();

    $id = (int) $pdo->lastInsertId();

    http_response_code(200);
    echo json_encode([
        "status"  => 200,
        "message" => "User berhasil ditambahkan",
        "data"    => [
            "idincrement" => $id,
            "username"    => $username,
            "nama"        => $nama,
            "code01"      => $code01,
            "kelompok"    => $kelompok,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function updateKepsekUser(array $req): void
{
    requireSuperAdmin($req);

    if (!array_key_exists("idincrement", $req) && !array_key_exists("id", $req)) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "ID user tidak valid"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $id = (int) ($req["idincrement"] ?? $req["id"]);
    $username = trim((string) ($req["target_username"] ?? $req["user_username"] ?? ""));
    $password = (string) ($req["target_password"] ?? $req["user_password"] ?? "");
    $nama = trim((string) ($req["target_nama"] ?? $req["user_nama"] ?? ""));
    $code01Raw = trim((string) ($req["target_code01"] ?? $req["user_code01"] ?? ""));
    $kelompokRaw = trim((string) ($req["target_kelompok"] ?? $req["user_kelompok"] ?? ""));

    if ($username === "" || $nama === "") {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Username dan nama wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();

    $exist = $pdo->prepare("SELECT idincrement FROM kepsek_user WHERE idincrement = :id LIMIT 1");
    $exist->bindValue(":id", $id, PDO::PARAM_INT);
    $exist->execute();
    if (!$exist->fetch()) {
        http_response_code(404);
        echo json_encode(["status" => 404, "message" => "User tidak ditemukan"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $check = $pdo->prepare("SELECT idincrement FROM kepsek_user WHERE username = :username AND idincrement <> :id LIMIT 1");
    $check->bindValue(":username", $username, PDO::PARAM_STR);
    $check->bindValue(":id", $id, PDO::PARAM_INT);
    $check->execute();
    if ($check->fetch()) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Username sudah digunakan"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $code01 = $code01Raw !== "" ? $code01Raw : null;
    $kelompok = $kelompokRaw !== "" ? $kelompokRaw : null;

    if ($password !== "") {
        $stmt = $pdo->prepare("
            UPDATE kepsek_user
            SET username = :username, password = :password, nama = :nama, CODE01 = :code01, kelompok = :kelompok
            WHERE idincrement = :id
        ");
        $stmt->bindValue(":password", hashKepsekPassword($password), PDO::PARAM_STR);
    } else {
        $stmt = $pdo->prepare("
            UPDATE kepsek_user
            SET username = :username, nama = :nama, CODE01 = :code01, kelompok = :kelompok
            WHERE idincrement = :id
        ");
    }
    $stmt->bindValue(":username", $username, PDO::PARAM_STR);
    $stmt->bindValue(":nama", $nama, PDO::PARAM_STR);
    if ($code01 === null) {
        $stmt->bindValue(":code01", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":code01", $code01, PDO::PARAM_STR);
    }
    if ($kelompok === null) {
        $stmt->bindValue(":kelompok", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":kelompok", $kelompok, PDO::PARAM_STR);
    }
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    http_response_code(200);
    echo json_encode([
        "status"  => 200,
        "message" => "User berhasil diubah",
        "data"    => [
            "idincrement" => $id,
            "username"    => $username,
            "nama"        => $nama,
            "code01"      => $code01,
            "kelompok"    => $kelompok,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function deleteKepsekUser(array $req): void
{
    requireSuperAdmin($req);

    if (!array_key_exists("idincrement", $req) && !array_key_exists("id", $req)) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "ID user tidak valid"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $id = (int) ($req["idincrement"] ?? $req["id"]);

    if (array_key_exists("user_id", $req) && (int) $req["user_id"] === $id) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Tidak dapat menghapus akun sendiri"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();

    $exist = $pdo->prepare("SELECT idincrement, username FROM kepsek_user WHERE idincrement = :id LIMIT 1");
    $exist->bindValue(":id", $id, PDO::PARAM_INT);
    $exist->execute();
    $row = $exist->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(["status" => 404, "message" => "User tidak ditemukan"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $selfUsername = trim((string) ($req["username"] ?? ""));
    if ($selfUsername !== "" && strcasecmp($selfUsername, (string) $row["username"]) === 0) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Tidak dapat menghapus akun sendiri"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM kepsek_user WHERE idincrement = :id");
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    http_response_code(200);
    echo json_encode(["status" => 200, "message" => "User berhasil dihapus"], JSON_UNESCAPED_UNICODE);
    exit;
}

function resolveTagihanFilter(array $req): array
{
    if (isDaftarUlangOnly($req)) {
        return ["BIAYA Daftar Ulang Ajaran Baru"];
    }
    return normalizeMultiValue($req["tagihan"] ?? []);
}

/** SQL fragment: AND {column} IN (:billnm_0,...) — empty if no tagihan filter */
function buildBillNmInSql(array $req, array &$params, string $column = "b.BILLNM", string $prefix = "billnm"): string
{
    $tagihan = resolveTagihanFilter($req);
    if (empty($tagihan)) {
        return "";
    }

    $ph = [];
    foreach ($tagihan as $i => $t) {
        $key = ":" . $prefix . "_" . $i;
        $ph[] = $key;
        $params[$key] = $t;
    }

    return " AND $column IN (" . implode(",", $ph) . ") ";
}

function beasiswaLabels(): array
{
    return [
        0 => "NON BEASISWA",
        1 => "KADER DALAM",
        2 => "KADER MALANG",
        3 => "KADER PENGURUS",
        4 => "KADER AMAL USAHA",
        5 => "KADER JAMAAH",
        6 => "KADER PENGABDIAN",
        7 => "SAUDARA",
        8 => "TAAWWUN",
        9 => "ALUMNI",
    ];
}

function resolveBeasiswaLabel($code): string
{
    $code = (int) $code;
    $labels = beasiswaLabels();
    return $labels[$code] ?? ("KODE " . $code);
}

function normalizeBeasiswaCodes($raw): array
{
    if (!is_array($raw)) {
        $raw = ($raw !== null && $raw !== "") ? [$raw] : [];
    }

    $out = [];
    foreach ($raw as $v) {
        if ($v === null || $v === "") {
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

function buildFilterSql(array $req, ?string $code01, array &$params): string
{
    $sql = "";

    $sql .= buildSekolahFilterSql($req, $code01, $params);

    $kelasList = normalizeMultiValue($req["kelas"] ?? []);
    if (!empty($kelasList)) {
        $ph = [];
        foreach ($kelasList as $i => $val) {
            $key = ":kelas_$i";
            $ph[] = $key;
            $params[$key] = $val;
        }
        $sql .= " AND c.DESC02 IN (" . implode(",", $ph) . ") ";
    }

    $jenjang = trim((string) ($req["jenjang"] ?? ""));
    if ($jenjang !== "") {
        $sql .= " AND mk.kelas = :jenjang ";
        $params[":jenjang"] = $jenjang;
    }

    $search = trim((string) ($req["search"] ?? ""));
    if ($search !== "") {
        $like = "%" . $search . "%";
        $sql .= " AND (c.NMCUST LIKE :search_nama OR c.NOCUST LIKE :search_nis) ";
        $params[":search_nama"] = $like;
        $params[":search_nis"] = $like;
    }

    $beasiswaCodes = normalizeBeasiswaCodes(
        $req["is_anak_pegawai"] ?? $req["beasiswa"] ?? $req["kader"] ?? []
    );
    if (!empty($beasiswaCodes)) {
        $ph = [];
        foreach ($beasiswaCodes as $i => $code) {
            $key = ":beasiswa_$i";
            $ph[] = $key;
            $params[$key] = $code;
        }
        $sql .= " AND COALESCE(c.is_anak_pegawai, 0) IN (" . implode(",", $ph) . ") ";
    }

    $tagihan = resolveTagihanFilter($req);
    if (!empty($tagihan)) {
        $ph = [];
        foreach ($tagihan as $i => $t) {
            $key = ":tagihan_$i";
            $ph[] = $key;
            $params[$key] = $t;
        }
        $sql .= " AND EXISTS (
            SELECT 1 FROM scctbill tb
            WHERE tb.CUSTID = c.CUSTID
              AND tb.FSTSBolehBayar = 1
              AND tb.BILLNM IN (" . implode(",", $ph) . ")
        ) ";
    }

    return $sql;
}

function buildHavingSql(array $req): string
{
    $paidst = trim((string) ($req["paidst"] ?? ""));
    if ($paidst === "0" || $paidst === "1") {
        return " HAVING sisa_tagihan " . ($paidst === "1" ? "<= 0" : "> 0") . " ";
    }
    return "";
}

function bindParams(\PDOStatement $stmt, array $params): void
{
    foreach ($params as $k => $v) {
        if (is_int($v)) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
    }
}

function resolveFurutanCutoffForCustid(PDO $pdo, int $custid, string $bta, array $tagihanNames): ?int
{
    if ($bta === "" || empty($tagihanNames)) {
        return null;
    }

    $ph = [];
    $params = [":custid" => $custid, ":bta" => $bta];
    foreach ($tagihanNames as $i => $t) {
        $key = ":tg_$i";
        $ph[] = $key;
        $params[$key] = $t;
    }

    $sql = "
        SELECT MAX(FUrutan) AS max_furutan
        FROM scctbill
        WHERE CUSTID = :custid
          AND BTA = :bta
          AND FSTSBolehBayar = 1
          AND BILLNM IN (" . implode(",", $ph) . ")
    ";

    $stmt = $pdo->prepare($sql);
    bindParams($stmt, $params);
    $stmt->execute();
    $row = $stmt->fetch();

    if (!$row || $row["max_furutan"] === null) {
        return null;
    }

    return (int) $row["max_furutan"];
}

function getSiswaPeriode(array $req, ?string $code01): void
{
    $limit = (int) ($req["limit"] ?? 500);
    $offset = (int) ($req["offset"] ?? 0);
    if ($limit <= 0) $limit = 500;
    if ($limit > 5000) $limit = 5000;
    if ($offset < 0) $offset = 0;

    $bta = trim((string) ($req["bta"] ?? ""));
    $tagihan = resolveTagihanFilter($req);

    if ($bta === "" || empty($tagihan)) {
        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "data" => [],
            "message" => "Pilih tahun ajaran dan tagihan untuk menampilkan Tagihan Periode"
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $pdo = dbConnectPdo();
    $params = [":bta" => $bta];

    $ph = [];
    foreach ($tagihan as $i => $t) {
        $key = ":tg_$i";
        $ph[] = $key;
        $params[$key] = $t;
    }
    $tagihanInList = implode(",", $ph);

    $paidst = trim((string) ($req["paidst"] ?? ""));
    $hasPaidst = ($paidst === "0" || $paidst === "1");

    $sqlIdsWhere = " WHERE c.STCUST = '1'
        AND EXISTS (
            SELECT 1 FROM scctbill b3
            WHERE b3.CUSTID = c.CUSTID
              AND b3.BTA = :bta
              AND b3.FSTSBolehBayar = 1
              AND b3.BILLNM IN ($tagihanInList)
        )
    ";
    $sqlIdsWhere .= buildFilterSql($req, $code01, $params);

    $custWhere = $sqlIdsWhere;
    $orderLimit = "";

    if (!$hasPaidst) {
        $sqlIds = "
            SELECT c.CUSTID
            FROM scctcust c
            LEFT JOIN mst_kelas mk ON mk.id = c.CODE03
            $sqlIdsWhere
            ORDER BY c.NMCUST ASC
            LIMIT $limit OFFSET $offset
        ";

        $stmtIds = $pdo->prepare($sqlIds);
        bindParams($stmtIds, $params);
        $stmtIds->execute();
        $custids = array_column($stmtIds->fetchAll(), "CUSTID");

        if (empty($custids)) {
            http_response_code(200);
            echo json_encode(["status" => 200, "data" => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $idPh = [];
        foreach ($custids as $i => $cid) {
            $key = ":cid_$i";
            $idPh[] = $key;
            $params[$key] = (int) $cid;
        }
        $custWhere = " WHERE c.CUSTID IN (" . implode(",", $idPh) . ") ";
        $orderLimit = " ORDER BY c.NMCUST ASC ";
    }

    $cutoffJoin = "
        LEFT JOIN (
            SELECT b2.CUSTID, MAX(b2.FUrutan) AS max_furutan
            FROM scctbill b2
            WHERE b2.BTA = :bta
              AND b2.FSTSBolehBayar = 1
              AND b2.BILLNM IN ($tagihanInList)
            GROUP BY b2.CUSTID
        ) cut ON cut.CUSTID = c.CUSTID
    ";

    $saldoJoin = "
        LEFT JOIN (
            SELECT t.CUSTID, COALESCE(SUM(t.KREDIT),0) - COALESCE(SUM(t.DEBET),0) AS saldo
            FROM sccttran t
            WHERE t.REFFBANK IN ('27','28','22','31')
            GROUP BY t.CUSTID
        ) sal ON sal.CUSTID = c.CUSTID
    ";

    $sqlSelect = "
        SELECT 
            c.CUSTID AS custid,
            c.NOCUST AS nis,
            c.NMCUST AS nama,
            c.DESC02 AS kelas,
            mk.unit AS sekolah,
            mk.kelas AS jenjang,
            c.NO_WA AS no_wa,
            c.CODE01 AS code01,
            COALESCE(c.is_anak_pegawai, 0) AS is_anak_pegawai,
            COALESCE(SUM(
                CASE 
                    WHEN b.FSTSBolehBayar = 1 
                        AND (
                            (b.BTA = :bta AND b.FUrutan <= cut.max_furutan)
                            OR (b.BTA < :bta AND b.PAIDST = '0' AND b.FSTSBolehBayar = 1)
                        )
                    THEN b.BILLAM 
                    ELSE 0 
                END
            ), 0) AS total_tagihan,
            COALESCE(SUM(
                CASE 
                    WHEN b.FSTSBolehBayar = 1 AND b.PAIDST = '1'
                        AND (
                            (b.BTA = :bta AND b.FUrutan <= cut.max_furutan)
                            OR (b.BTA < :bta AND b.PAIDST = '0' AND b.FSTSBolehBayar = 1)
                        )
                    THEN b.BILLAM 
                    ELSE 0 
                END
            ), 0) AS total_terbayar,
            (COALESCE(SUM(
                CASE 
                    WHEN b.FSTSBolehBayar = 1 
                        AND (
                            (b.BTA = :bta AND b.FUrutan <= cut.max_furutan)
                            OR (b.BTA < :bta AND b.PAIDST = '0' AND b.FSTSBolehBayar = 1)
                        )
                    THEN b.BILLAM 
                    ELSE 0 
                END
            ), 0) - COALESCE(SUM(
                CASE 
                    WHEN b.FSTSBolehBayar = 1 AND b.PAIDST = '1'
                        AND (
                            (b.BTA = :bta AND b.FUrutan <= cut.max_furutan)
                            OR (b.BTA < :bta AND b.PAIDST = '0' AND b.FSTSBolehBayar = 1)
                        )
                    THEN b.BILLAM 
                    ELSE 0 
                END
            ), 0)) AS sisa_tagihan_sebelum_saldo,
            COALESCE(sal.saldo, 0) AS saldo
    ";

    $sqlFrom = "
        FROM scctcust c
        LEFT JOIN mst_kelas mk ON mk.id = c.CODE03
        LEFT JOIN scctbill b ON b.CUSTID = c.CUSTID AND b.BTA <= :bta
        $cutoffJoin
        $saldoJoin
    ";

    $sqlGroupBy = " GROUP BY c.CUSTID, c.NOCUST, c.NMCUST, c.DESC02, mk.unit, mk.kelas, c.NO_WA, c.CODE01, c.is_anak_pegawai, sal.saldo ";

    if (!$hasPaidst) {
        $sqlInner = $sqlSelect . $sqlFrom . $custWhere . $sqlGroupBy . $orderLimit;

        $sqlOuter = "
            SELECT
                x.custid, x.nis, x.nama, x.kelas, x.sekolah, x.jenjang, x.no_wa, x.code01, x.is_anak_pegawai,
                x.total_tagihan, x.total_terbayar, x.sisa_tagihan_sebelum_saldo, x.saldo,
                LEAST(GREATEST(x.saldo, 0), x.sisa_tagihan_sebelum_saldo) AS saldo_terpakai,
                GREATEST(x.sisa_tagihan_sebelum_saldo - GREATEST(x.saldo, 0), 0) AS sisa_tagihan
            FROM ($sqlInner) x
            ORDER BY x.nama ASC
        ";

        $stmt = $pdo->prepare($sqlOuter);
    } else {
        $sqlInner = $sqlSelect . $sqlFrom . $custWhere . $sqlGroupBy;

        $sqlOuter = "
            SELECT
                x.custid, x.nis, x.nama, x.kelas, x.sekolah, x.jenjang, x.no_wa, x.code01, x.is_anak_pegawai,
                x.total_tagihan, x.total_terbayar, x.sisa_tagihan_sebelum_saldo, x.saldo,
                LEAST(GREATEST(x.saldo, 0), x.sisa_tagihan_sebelum_saldo) AS saldo_terpakai,
                GREATEST(x.sisa_tagihan_sebelum_saldo - GREATEST(x.saldo, 0), 0) AS sisa_tagihan
            FROM ($sqlInner) x
        ";

        $sqlHaving = buildHavingSql($req);
        $sqlOrderLimit = " ORDER BY x.nama ASC LIMIT $limit OFFSET $offset ";
        $stmt = $pdo->prepare($sqlOuter . $sqlHaving . $sqlOrderLimit);
    }

    bindParams($stmt, $params);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row["total_tagihan"] = (float) $row["total_tagihan"];
        $row["total_terbayar"] = (float) $row["total_terbayar"];
        $row["sisa_tagihan_sebelum_saldo"] = (float) $row["sisa_tagihan_sebelum_saldo"];
        $row["saldo"] = (float) $row["saldo"];
        $row["saldo_terpakai"] = (float) $row["saldo_terpakai"];
        $row["sisa_tagihan"] = (float) $row["sisa_tagihan"];
        $row["status"] = $row["sisa_tagihan"] <= 0 ? 1 : 0;
        $row["sekolah"] = $row["sekolah"] ?? "-";
        $row["jenjang"] = $row["jenjang"] ?? "-";
        $row["no_wa"] = trim((string) ($row["no_wa"] ?? ""));
        $row["code01"] = trim((string) ($row["code01"] ?? ""));
        $row["is_anak_pegawai"] = (int) ($row["is_anak_pegawai"] ?? 0);
        if ($row["is_anak_pegawai"] < 0 || $row["is_anak_pegawai"] > 9) {
            $row["is_anak_pegawai"] = 0;
        }
        $row["kader_label"] = resolveBeasiswaLabel($row["is_anak_pegawai"]);
        $row["beasiswa_label"] = $row["kader_label"];
    }
    unset($row);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getSiswaTagihanPeriode(array $req, ?string $code01): void
{
    $custid = trim((string) ($req["custid"] ?? ""));
    $bta = trim((string) ($req["bta"] ?? ""));
    $tagihan = resolveTagihanFilter($req);

    if ($custid === "" || $bta === "" || empty($tagihan)) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "custid, bta, dan tagihan wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();
    $furutanMax = resolveFurutanCutoffForCustid($pdo, (int) $custid, $bta, $tagihan);

    if ($furutanMax === null) {
        http_response_code(200);
        echo json_encode(["status" => 200, "data" => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $params = [
        ":custid" => (int) $custid,
        ":bta" => $bta,
        ":furutan_max" => $furutanMax,
    ];

    $sql = "
        SELECT
            b.CUSTID AS custid,
            b.BILLCD AS kode_tagihan,
            b.BILLNM AS nama_tagihan,
            b.BILLAM AS jumlah,
            b.PAIDST AS status_bayar,
            b.FTGLTagihan AS tanggal_tagihan,
            b.PAIDDT AS tanggal_bayar,
            b.FUrutan AS furutan,
            b.NOREFF AS noreff,
            b.FSTSBolehBayar AS boleh_bayar,
            b.BTA AS bta
        FROM scctbill b
        WHERE b.CUSTID = :custid
          AND b.FSTSBolehBayar = 1
          AND b.BTA <= :bta
          AND (
              (b.BTA = :bta AND b.FUrutan <= :furutan_max)
              OR (b.BTA < :bta AND b.PAIDST = '0')
          )
        ORDER BY b.BTA DESC, b.FUrutan DESC, b.BILLCD ASC
    ";

    $stmt = $pdo->prepare($sql);
    bindParams($stmt, $params);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row["jumlah"] = (float) $row["jumlah"];
        $row["status_bayar"] = (int) $row["status_bayar"];
    }

    http_response_code(200);
    echo json_encode(["status" => 200, "data" => $rows], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getSummaryTagihanPeriode(array $req, ?string $code01): void
{
    $bta = trim((string) ($req["bta"] ?? ""));
    $tagihan = resolveTagihanFilter($req);

    if ($bta === "" || empty($tagihan)) {
        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "data" => ["total_siswa" => 0, "total_tagihan" => 0, "total_terbayar" => 0, "total_piutang" => 0]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $pdo = dbConnectPdo();
    $params = [":bta" => $bta];

    $ph = [];
    foreach ($tagihan as $i => $t) {
        $key = ":tg_$i";
        $ph[] = $key;
        $params[$key] = $t;
    }
    $tagihanInList = implode(",", $ph);

    $sqlWhere = " WHERE c.STCUST = '1'
        AND EXISTS (
            SELECT 1 FROM scctbill b3
            WHERE b3.CUSTID = c.CUSTID
              AND b3.BTA = :bta
              AND b3.FSTSBolehBayar = 1
              AND b3.BILLNM IN ($tagihanInList)
        )
    ";
    $sqlWhere .= buildFilterSql($req, $code01, $params);

    $cutoffJoin = "
        LEFT JOIN (
            SELECT b2.CUSTID, MAX(b2.FUrutan) AS max_furutan
            FROM scctbill b2
            WHERE b2.BTA = :bta
              AND b2.FSTSBolehBayar = 1
              AND b2.BILLNM IN ($tagihanInList)
            GROUP BY b2.CUSTID
        ) cut ON cut.CUSTID = c.CUSTID
    ";

    $saldoJoin = "
        LEFT JOIN (
            SELECT t.CUSTID, COALESCE(SUM(t.KREDIT),0) - COALESCE(SUM(t.DEBET),0) AS saldo
            FROM sccttran t
            WHERE t.REFFBANK IN ('27','28','22','31')
            GROUP BY t.CUSTID
        ) sal ON sal.CUSTID = c.CUSTID
    ";

    $sqlBillExpr = "
        CASE
            WHEN b.FSTSBolehBayar = 1
                AND (
                    (b.BTA = :bta AND b.FUrutan <= cut.max_furutan)
                    OR (b.BTA < :bta AND b.PAIDST = '0' AND b.FSTSBolehBayar = 1)
                )
            THEN b.BILLAM ELSE 0
        END
    ";
    $sqlPaidExpr = "
        CASE
            WHEN b.FSTSBolehBayar = 1 AND b.PAIDST = '1'
                AND (
                    (b.BTA = :bta AND b.FUrutan <= cut.max_furutan)
                    OR (b.BTA < :bta AND b.PAIDST = '0' AND b.FSTSBolehBayar = 1)
                )
            THEN b.BILLAM ELSE 0
        END
    ";

    $sqlSelect = "
        SELECT
            c.CUSTID AS custid,
            COALESCE(SUM($sqlBillExpr), 0) AS total_tagihan,
            COALESCE(SUM($sqlPaidExpr), 0) AS total_terbayar,
            COALESCE(sal.saldo, 0) AS saldo
    ";

    $sqlFrom = "
        FROM scctcust c
        LEFT JOIN mst_kelas mk ON mk.id = c.CODE03
        LEFT JOIN scctbill b ON b.CUSTID = c.CUSTID AND b.BTA <= :bta
        $cutoffJoin
        $saldoJoin
    ";

    $sqlGroupBy = " GROUP BY c.CUSTID, sal.saldo ";

    $sqlInner = $sqlSelect . $sqlFrom . $sqlWhere . $sqlGroupBy;

    $paidst = trim((string) ($req["paidst"] ?? ""));
    $hasPaidst = ($paidst === "0" || $paidst === "1");

    $sqlOuter = "
        SELECT
            COUNT(*) AS total_siswa_all,
            COALESCE(SUM(CASE WHEN x.sisa_tagihan > 0 THEN 1 ELSE 0 END), 0) AS total_siswa_piutang,
            COALESCE(SUM(CASE WHEN x.sisa_tagihan <= 0 THEN 1 ELSE 0 END), 0) AS total_siswa_lunas,
            COALESCE(SUM(x.total_tagihan), 0) AS total_tagihan_all,
            COALESCE(SUM(x.total_terbayar), 0) AS total_terbayar_all,
            COALESCE(SUM(x.sisa_tagihan), 0) AS total_piutang_all,
            COALESCE(SUM(CASE WHEN x.sisa_tagihan > 0 THEN x.total_tagihan ELSE 0 END), 0) AS total_tagihan_piutang,
            COALESCE(SUM(CASE WHEN x.sisa_tagihan > 0 THEN x.total_terbayar ELSE 0 END), 0) AS total_terbayar_piutang,
            COALESCE(SUM(CASE WHEN x.sisa_tagihan > 0 THEN x.sisa_tagihan ELSE 0 END), 0) AS total_piutang_piutang,
            COALESCE(SUM(CASE WHEN x.sisa_tagihan <= 0 THEN x.total_tagihan ELSE 0 END), 0) AS total_tagihan_lunas,
            COALESCE(SUM(CASE WHEN x.sisa_tagihan <= 0 THEN x.total_terbayar ELSE 0 END), 0) AS total_terbayar_lunas
        FROM (
            SELECT
                x2.total_tagihan,
                x2.total_terbayar,
                GREATEST(x2.total_tagihan - x2.total_terbayar - GREATEST(x2.saldo, 0), 0) AS sisa_tagihan
            FROM ($sqlInner) x2
        ) x
    ";

    $stmt = $pdo->prepare($sqlOuter);
    bindParams($stmt, $params);
    $stmt->execute();
    $row = $stmt->fetch();

    $totalSiswaAll = (int) ($row["total_siswa_all"] ?? 0);
    $totalSiswaPiutang = (int) ($row["total_siswa_piutang"] ?? 0);
    $totalSiswaLunas = (int) ($row["total_siswa_lunas"] ?? 0);

    if ($hasPaidst && $paidst === "1") {
        $totalSiswa = $totalSiswaLunas;
        $totalTagihan = (float) ($row["total_tagihan_lunas"] ?? 0);
        $totalTerbayar = (float) ($row["total_terbayar_lunas"] ?? 0);
        $totalPiutang = 0.0;
    } elseif ($hasPaidst && $paidst === "0") {
        $totalSiswa = $totalSiswaPiutang;
        $totalTagihan = (float) ($row["total_tagihan_piutang"] ?? 0);
        $totalTerbayar = (float) ($row["total_terbayar_piutang"] ?? 0);
        $totalPiutang = (float) ($row["total_piutang_piutang"] ?? 0);
    } else {
        $totalSiswa = $totalSiswaAll;
        $totalTagihan = (float) ($row["total_tagihan_all"] ?? 0);
        $totalTerbayar = (float) ($row["total_terbayar_all"] ?? 0);
        $totalPiutang = (float) ($row["total_piutang_all"] ?? 0);
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => [
            "total_siswa" => $totalSiswa,
            "total_siswa_piutang" => $totalSiswaPiutang,
            "total_tagihan" => $totalTagihan,
            "total_terbayar" => $totalTerbayar,
            "total_piutang" => $totalPiutang,
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getDashboardKeuangan(array $req, ?string $code01): void
{
    $bta = trim((string) ($req["bta"] ?? ""));
    if ($bta === "") {
        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "data" => [
                "summary" => [
                    "total_siswa" => 0,
                    "total_siswa_piutang" => 0,
                    "total_tagihan" => 0,
                    "total_terbayar" => 0,
                    "total_piutang" => 0,
                    "total_saldo" => 0,
                ],
                "by_sekolah" => [],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $pdo = dbConnectPdo();
    $tagihan = resolveTagihanFilter($req);
    if (empty($tagihan)) {
        $stmtTg = $pdo->query("
            SELECT DISTINCT tagihan AS value
            FROM mst_tagihan
            WHERE tagihan IS NOT NULL AND tagihan != ''
            ORDER BY urut ASC
        ");
        $tagihan = array_column($stmtTg->fetchAll(), "value");
    }
    if (empty($tagihan)) {
        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "data" => [
                "summary" => [
                    "total_siswa" => 0,
                    "total_siswa_piutang" => 0,
                    "total_tagihan" => 0,
                    "total_terbayar" => 0,
                    "total_piutang" => 0,
                    "total_saldo" => 0,
                ],
                "by_sekolah" => [],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $params = [":bta" => $bta];
    $ph = [];
    foreach ($tagihan as $i => $t) {
        $key = ":tg_$i";
        $ph[] = $key;
        $params[$key] = $t;
    }
    $tagihanInList = implode(",", $ph);

    $sqlWhere = " WHERE c.STCUST = '1'
        AND EXISTS (
            SELECT 1 FROM scctbill b3
            WHERE b3.CUSTID = c.CUSTID
              AND b3.BTA = :bta
              AND b3.FSTSBolehBayar = 1
              AND b3.BILLNM IN ($tagihanInList)
        )
    ";
    $sqlWhere .= buildFilterSql($req, $code01, $params);

    $cutoffJoin = "
        LEFT JOIN (
            SELECT b2.CUSTID, MAX(b2.FUrutan) AS max_furutan
            FROM scctbill b2
            WHERE b2.BTA = :bta
              AND b2.FSTSBolehBayar = 1
              AND b2.BILLNM IN ($tagihanInList)
            GROUP BY b2.CUSTID
        ) cut ON cut.CUSTID = c.CUSTID
    ";

    $saldoJoin = "
        LEFT JOIN (
            SELECT t.CUSTID, COALESCE(SUM(t.KREDIT),0) - COALESCE(SUM(t.DEBET),0) AS saldo
            FROM sccttran t
            WHERE t.REFFBANK IN ('27','28','22','31')
            GROUP BY t.CUSTID
        ) sal ON sal.CUSTID = c.CUSTID
    ";

    $sqlBillExpr = "
        CASE
            WHEN b.FSTSBolehBayar = 1
                AND (
                    (b.BTA = :bta AND b.FUrutan <= cut.max_furutan)
                    OR (b.BTA < :bta AND b.PAIDST = '0' AND b.FSTSBolehBayar = 1)
                )
            THEN b.BILLAM ELSE 0
        END
    ";
    $sqlPaidExpr = "
        CASE
            WHEN b.FSTSBolehBayar = 1 AND b.PAIDST = '1'
                AND (
                    (b.BTA = :bta AND b.FUrutan <= cut.max_furutan)
                    OR (b.BTA < :bta AND b.PAIDST = '0' AND b.FSTSBolehBayar = 1)
                )
            THEN b.BILLAM ELSE 0
        END
    ";

    $sqlInner = "
        SELECT
            COALESCE(NULLIF(mk.unit, ''), 'Lainnya') AS sekolah,
            c.CUSTID AS custid,
            COALESCE(SUM($sqlBillExpr), 0) AS total_tagihan,
            COALESCE(SUM($sqlPaidExpr), 0) AS total_terbayar,
            COALESCE(sal.saldo, 0) AS saldo
        FROM scctcust c
        LEFT JOIN mst_kelas mk ON mk.id = c.CODE03
        LEFT JOIN scctbill b ON b.CUSTID = c.CUSTID AND b.BTA <= :bta
        $cutoffJoin
        $saldoJoin
        $sqlWhere
        GROUP BY COALESCE(NULLIF(mk.unit, ''), 'Lainnya'), c.CUSTID, sal.saldo
    ";

    $sql = "
        SELECT
            x.sekolah,
            COUNT(*) AS total_siswa,
            COALESCE(SUM(CASE WHEN x.sisa_tagihan > 0 THEN 1 ELSE 0 END), 0) AS total_siswa_piutang,
            COALESCE(SUM(x.total_tagihan), 0) AS total_tagihan,
            COALESCE(SUM(x.total_terbayar), 0) AS total_terbayar,
            COALESCE(SUM(x.sisa_tagihan), 0) AS total_piutang,
            COALESCE(SUM(GREATEST(x.saldo, 0)), 0) AS total_saldo
        FROM (
            SELECT
                i.sekolah,
                i.total_tagihan,
                i.total_terbayar,
                i.saldo,
                GREATEST(i.total_tagihan - i.total_terbayar - GREATEST(i.saldo, 0), 0) AS sisa_tagihan
            FROM ($sqlInner) i
        ) x
        GROUP BY x.sekolah
        ORDER BY x.sekolah ASC
    ";

    $stmt = $pdo->prepare($sql);
    bindParams($stmt, $params);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $bySekolah = [];
    $sumSiswa = 0;
    $sumSiswaPiutang = 0;
    $sumTagihan = 0.0;
    $sumTerbayar = 0.0;
    $sumPiutang = 0.0;
    $sumSaldo = 0.0;

    foreach ($rows as $row) {
        $totalTagihan = (float) ($row["total_tagihan"] ?? 0);
        $totalTerbayar = (float) ($row["total_terbayar"] ?? 0);
        $totalPiutang = (float) ($row["total_piutang"] ?? 0);
        $totalSiswa = (int) ($row["total_siswa"] ?? 0);
        $totalSiswaPiutang = (int) ($row["total_siswa_piutang"] ?? 0);
        $totalSaldo = (float) ($row["total_saldo"] ?? 0);
        $pct = $totalTagihan > 0 ? round(($totalTerbayar / $totalTagihan) * 100) : 0;

        $bySekolah[] = [
            "sekolah" => (string) ($row["sekolah"] ?? "Lainnya"),
            "total_siswa" => $totalSiswa,
            "total_siswa_piutang" => $totalSiswaPiutang,
            "total_tagihan" => $totalTagihan,
            "total_terbayar" => $totalTerbayar,
            "total_piutang" => $totalPiutang,
            "total_saldo" => $totalSaldo,
            "pct_terbayar" => $pct,
        ];

        $sumSiswa += $totalSiswa;
        $sumSiswaPiutang += $totalSiswaPiutang;
        $sumTagihan += $totalTagihan;
        $sumTerbayar += $totalTerbayar;
        $sumPiutang += $totalPiutang;
        $sumSaldo += $totalSaldo;
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => [
            "summary" => [
                "total_siswa" => $sumSiswa,
                "total_siswa_piutang" => $sumSiswaPiutang,
                "total_tagihan" => $sumTagihan,
                "total_terbayar" => $sumTerbayar,
                "total_piutang" => $sumPiutang,
                "total_saldo" => $sumSaldo,
            ],
            "by_sekolah" => $bySekolah,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getSiswaAggregated(array $req, ?string $code01): void
{
    $limit = (int) ($req["limit"] ?? 500);
    $offset = (int) ($req["offset"] ?? 0);

    if ($limit <= 0) $limit = 500;
    if ($limit > 5000) $limit = 5000;
    if ($offset < 0) $offset = 0;

    $pdo = dbConnectPdo();
    $params = [];

    $bta = trim((string) ($req["bta"] ?? $req["tahun_akademik"] ?? ""));

    $btaSumCond = "";
    if ($bta !== "") {
        $btaSumCond = " AND b.BTA = :bta_sum ";
        $params[":bta_sum"] = $bta;
    }

    // Saat filter tagihan / humas=1, jumlahkan hanya bill yang cocok (bukan semua tagihan siswa)
    $billNmSumCond = buildBillNmInSql($req, $params, "b.BILLNM", "billnm_sum");

    $sqlSelect = "
        SELECT 
            c.CUSTID AS custid,
            c.NOCUST AS nis,
            c.NMCUST AS nama,
            c.DESC02 AS kelas,
            mk.unit AS sekolah,
            mk.kelas AS jenjang,
            COALESCE(SUM(CASE WHEN b.FSTSBolehBayar = 1 $btaSumCond $billNmSumCond THEN b.BILLAM ELSE 0 END), 0) AS total_tagihan,
            COALESCE(SUM(CASE WHEN b.FSTSBolehBayar = 1 AND b.PAIDST = '1' $btaSumCond $billNmSumCond THEN b.BILLAM ELSE 0 END), 0) AS total_terbayar,
            (COALESCE(SUM(CASE WHEN b.FSTSBolehBayar = 1 $btaSumCond $billNmSumCond THEN b.BILLAM ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN b.FSTSBolehBayar = 1 AND b.PAIDST = '1' $btaSumCond $billNmSumCond THEN b.BILLAM ELSE 0 END), 0)) AS sisa_tagihan
    ";

    $sqlFrom = "
        FROM scctcust c
        LEFT JOIN mst_kelas mk ON mk.id = c.CODE03
        LEFT JOIN scctbill b ON b.CUSTID = c.CUSTID
    ";

    $sqlWhere = " WHERE c.STCUST = '1' ";

    if ($bta !== "") {
        $sqlWhere .= " AND EXISTS (
            SELECT 1 FROM scctbill b_inner 
            WHERE b_inner.CUSTID = c.CUSTID 
              AND b_inner.FSTSBolehBayar = 1
              AND b_inner.BTA = :bta
        ) ";
        $params[":bta"] = $bta;
    }

    $sqlWhere .= buildFilterSql($req, $code01, $params);

    $sqlGroupBy = " GROUP BY c.CUSTID, c.NOCUST, c.NMCUST, c.DESC02, mk.unit, mk.kelas ";

    $sqlHaving = "";
    $paidst = trim((string) ($req["paidst"] ?? ""));
    if ($paidst === "0" || $paidst === "1") {
        $sqlHaving = " HAVING sisa_tagihan " . ($paidst === "1" ? "<= 0" : "> 0") . " ";
    }

    $sqlOrderLimit = " ORDER BY c.NMCUST ASC LIMIT $limit OFFSET $offset ";

    $sqlPage = $sqlSelect . $sqlFrom . $sqlWhere . $sqlGroupBy . $sqlHaving . $sqlOrderLimit;

    $stmt = $pdo->prepare($sqlPage);
    bindParams($stmt, $params);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row["total_tagihan"] = (float) $row["total_tagihan"];
        $row["total_terbayar"] = (float) $row["total_terbayar"];
        $row["sisa_tagihan"] = (float) $row["sisa_tagihan"];
        $row["status"] = $row["sisa_tagihan"] <= 0 ? 1 : 0;
        $row["sekolah"] = $row["sekolah"] ?? "-";
        $row["jenjang"] = $row["jenjang"] ?? "-";
    }
    unset($row);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getKelasBySekolah(array $req, ?string $code01): void
{
    $allowed = parseCode01List($code01);
    $selected = normalizeMultiValue($req["sekolah"] ?? []);

    if (empty($selected)) {
        http_response_code(200);
        echo json_encode(["status" => 200, "data" => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if (!empty($allowed)) {
        $selected = array_values(array_intersect($selected, $allowed));
        if (empty($selected)) {
            http_response_code(200);
            echo json_encode(["status" => 200, "data" => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    $pdo = dbConnectPdo();
    $params = [];
    $ph = [];
    foreach ($selected as $i => $val) {
        $key = ":sekolah_$i";
        $ph[] = $key;
        $params[$key] = $val;
    }

    $sql = "
        SELECT DISTINCT c.DESC02 AS value
        FROM scctcust c
        INNER JOIN mst_kelas mk ON mk.id = c.CODE03
        WHERE c.STCUST = '1'
          AND c.DESC02 IS NOT NULL AND c.DESC02 != ''
          AND mk.unit IN (" . implode(",", $ph) . ")
        ORDER BY c.DESC02 ASC
    ";

    $stmt = $pdo->prepare($sql);
    bindParams($stmt, $params);
    $stmt->execute();
    $kelas = array_column($stmt->fetchAll(), "value");

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => $kelas
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getSiswaCount(array $req, ?string $code01): void
{
    $pdo = dbConnectPdo();
    $params = [];

    $sql = "
        SELECT COUNT(DISTINCT c.CUSTID) AS total
        FROM scctcust c
        LEFT JOIN mst_kelas mk ON mk.id = c.CODE03
        WHERE c.STCUST = '1'
    ";

    $bta = trim((string) ($req["bta"] ?? $req["tahun_akademik"] ?? ""));
    if ($bta !== "") {
        $sql .= " AND EXISTS (
            SELECT 1 FROM scctbill b_inner 
            WHERE b_inner.CUSTID = c.CUSTID 
              AND b_inner.FSTSBolehBayar = 1
              AND b_inner.BTA = :bta
        ) ";
        $params[":bta"] = $bta;
    }

    $sql .= buildFilterSql($req, $code01, $params);

    $stmt = $pdo->prepare($sql);
    bindParams($stmt, $params);
    $stmt->execute();
    $row = $stmt->fetch();

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => ["total" => (int) ($row["total"] ?? 0)]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getSiswaTagihan(array $req, ?string $code01): void
{
    $custid = trim((string) ($req["custid"] ?? ""));

    if ($custid === "") {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "custid wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();
    $params = [];
    $params[":custid"] = (int) $custid;

    $sql = "
        SELECT
            b.CUSTID AS custid,
            b.BILLCD AS kode_tagihan,
            b.BILLNM AS nama_tagihan,
            b.BILLAM AS jumlah,
            b.PAIDST AS status_bayar,
            b.FTGLTagihan AS tanggal_tagihan,
            b.PAIDDT AS tanggal_bayar,
            b.FUrutan AS furutan,
            b.NOREFF AS noreff,
            b.FSTSBolehBayar AS boleh_bayar,
            b.BTA AS bta
        FROM scctbill b
        WHERE b.CUSTID = :custid AND b.FSTSBolehBayar = 1
    ";

    if (isDaftarUlangOnly($req)) {
        $sql .= " AND b.BILLNM = :billnm_daftar_ulang ";
        $params[":billnm_daftar_ulang"] = "BIAYA Daftar Ulang Ajaran Baru";
    }

    $bta = trim((string) ($req["bta"] ?? $req["tahun_akademik"] ?? ""));
    if ($bta !== "") {
        $sql .= " AND b.BTA = :bta ";
        $params[":bta"] = $bta;
    }

    $sql .= " ORDER BY b.FUrutan ASC, b.BILLCD ASC ";

    $stmt = $pdo->prepare($sql);
    bindParams($stmt, $params);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row["jumlah"] = (float) $row["jumlah"];
        $row["status_bayar"] = (int) $row["status_bayar"];
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getDetailTagihan(array $req, ?string $code01): void
{
    $custid = trim((string) ($req["custid"] ?? ""));
    $billcd = trim((string) ($req["kode_tagihan"] ?? $req["billcd"] ?? ""));

    if ($custid === "" || $billcd === "") {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "custid dan kode_tagihan wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();

    try {
        $check = $pdo->query("SHOW TABLES LIKE 'scctbill_detail'")->fetch();

        if ($check) {
            $sql = "
                SELECT
                    d.KodePost AS kode_akun,
                    d.BILLAM AS jumlah,
                    COALESCE(ua.NamaAkun, d.KodePost) AS nama_akun
                FROM scctbill_detail d
                INNER JOIN scctbill b ON b.CUSTID = d.CUSTID AND b.BILLCD = d.BILLCD
                LEFT JOIN u_akun ua ON ua.KodeAkun = d.KodePost
                WHERE d.CUSTID = :custid AND d.BILLCD = :billcd
                ORDER BY d.KodePost ASC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":custid", (int) $custid, PDO::PARAM_INT);
            $stmt->bindValue(":billcd", $billcd, PDO::PARAM_STR);
            $stmt->execute();
            $detail = $stmt->fetchAll();

            if (!empty($detail)) {
                http_response_code(200);
                echo json_encode([
                    "status" => 200,
                    "data" => [
                        "header" => null,
                        "detail" => $detail
                    ]
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }

        $sql = "
            SELECT
                b.BILLCD AS kode_akun,
                b.BILLNM AS nama_akun,
                b.BILLAM AS jumlah
            FROM scctbill b
            WHERE b.CUSTID = :custid AND b.BILLCD = :billcd
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":custid", (int) $custid, PDO::PARAM_INT);
        $stmt->bindValue(":billcd", $billcd, PDO::PARAM_STR);
        $stmt->execute();
        $detail = $stmt->fetchAll();

        if (empty($detail)) {
            http_response_code(404);
            echo json_encode(["status" => 404, "message" => "Rincian tagihan tidak ditemukan"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "data" => [
                "header" => null,
                "detail" => $detail
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    } catch (PDOException $e) {
        $sql = "
            SELECT
                b.BILLCD AS kode_akun,
                b.BILLNM AS nama_akun,
                b.BILLAM AS jumlah
            FROM scctbill b
            WHERE b.CUSTID = :custid AND b.BILLCD = :billcd
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":custid", (int) $custid, PDO::PARAM_INT);
        $stmt->bindValue(":billcd", $billcd, PDO::PARAM_STR);
        $stmt->execute();
        $detail = $stmt->fetchAll();

        if (empty($detail)) {
            http_response_code(404);
            echo json_encode(["status" => 404, "message" => "Rincian tagihan tidak ditemukan"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "data" => [
                "header" => null,
                "detail" => $detail
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

function getTagihanInvoice(array $req, ?string $code01): void
{
    $custid = trim((string) ($req["custid"] ?? ""));
    $billcd = trim((string) ($req["kode_tagihan"] ?? $req["billcd"] ?? ""));

    if ($custid === "" || $billcd === "") {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "custid dan kode_tagihan wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();

    $sql = "
        SELECT
            b.CUSTID AS custid,
            b.BILLCD AS kode_tagihan,
            b.BILLNM AS nama_tagihan,
            b.BILLAM AS jumlah,
            b.PAIDST AS status_bayar,
            b.FTGLTagihan AS tanggal_tagihan,
            b.PAIDDT AS tanggal_bayar,
            b.NOREFF AS noreff,
            b.BTA AS bta,
            c.NOCUST AS nis,
            c.NMCUST AS nama,
            c.DESC02 AS kelas,
            COALESCE(mk.unit, c.CODE01, '') AS sekolah,
            COALESCE(c.is_anak_pegawai, 0) AS is_anak_pegawai
        FROM scctbill b
        INNER JOIN scctcust c ON c.CUSTID = b.CUSTID
        LEFT JOIN mst_kelas mk ON mk.id = c.CODE03
        WHERE b.CUSTID = :custid AND b.BILLCD = :billcd
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":custid", (int) $custid, PDO::PARAM_INT);
    $stmt->bindValue(":billcd", $billcd, PDO::PARAM_STR);
    $stmt->execute();
    $header = $stmt->fetch();

    if (!$header) {
        http_response_code(404);
        echo json_encode(["status" => 404, "message" => "Tagihan tidak ditemukan"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $header["jumlah"] = (float) ($header["jumlah"] ?? 0);
    $header["status_bayar"] = (int) ($header["status_bayar"] ?? 0);
    $header["is_anak_pegawai"] = (int) ($header["is_anak_pegawai"] ?? 0);
    if ($header["is_anak_pegawai"] < 0 || $header["is_anak_pegawai"] > 9) {
        $header["is_anak_pegawai"] = 0;
    }
    $header["kader_label"] = resolveBeasiswaLabel($header["is_anak_pegawai"]);
    $header["beasiswa_label"] = $header["kader_label"];

    if ($header["status_bayar"] !== 1) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Invoice hanya tersedia untuk tagihan yang sudah lunas"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $detail = [];
    try {
        $check = $pdo->query("SHOW TABLES LIKE 'scctbill_detail'")->fetch();
        if ($check) {
            $sqlDetail = "
                SELECT
                    d.KodePost AS kode_akun,
                    d.BILLAM AS jumlah,
                    COALESCE(ua.NamaAkun, d.KodePost) AS nama_akun
                FROM scctbill_detail d
                LEFT JOIN u_akun ua ON ua.KodeAkun = d.KodePost
                WHERE d.CUSTID = :custid AND d.BILLCD = :billcd
                ORDER BY d.KodePost ASC
            ";
            $stmtDetail = $pdo->prepare($sqlDetail);
            $stmtDetail->bindValue(":custid", (int) $custid, PDO::PARAM_INT);
            $stmtDetail->bindValue(":billcd", $billcd, PDO::PARAM_STR);
            $stmtDetail->execute();
            $detail = $stmtDetail->fetchAll();
        }
    } catch (PDOException $e) {
        $detail = [];
    }

    if (empty($detail)) {
        $detail = [[
            "kode_akun" => $header["kode_tagihan"],
            "nama_akun" => $header["nama_tagihan"],
            "jumlah" => $header["jumlah"],
        ]];
    }

    foreach ($detail as &$row) {
        $row["jumlah"] = (float) ($row["jumlah"] ?? 0);
    }
    unset($row);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => [
            "header" => $header,
            "detail" => $detail,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getFilterOptions(array $req, ?string $code01): void
{
    $pdo = dbConnectPdo();
    $allowed = parseCode01List($code01);
    $restricted = !empty($allowed);
    $daftarUlangOnly = isDaftarUlangOnly($req);

    $sekolah = [];
    if ($restricted) {
        // CODE01 user = daftar unit (mst_kelas.unit), tampilkan apa adanya
        foreach ($allowed as $unit) {
            $sekolah[] = [
                "value" => $unit,
                "label" => $unit,
            ];
        }
    } else {
        $stmtSekolah = $pdo->prepare("
            SELECT DISTINCT unit AS value
            FROM mst_kelas
            WHERE unit IS NOT NULL AND unit != ''
            ORDER BY unit ASC
        ");
        $stmtSekolah->execute();
        foreach ($stmtSekolah->fetchAll() as $row) {
            $val = trim((string) ($row["value"] ?? ""));
            if ($val === "") {
                continue;
            }
            $sekolah[] = [
                "value" => $val,
                "label" => $val,
            ];
        }
    }

    $sqlBta = "
        SELECT DISTINCT b.BTA AS value
        FROM scctbill b
        WHERE b.FSTSBolehBayar = 1
          AND b.BTA IS NOT NULL AND b.BTA != ''
        ORDER BY b.BTA DESC
        LIMIT 50
    ";
    $stmtBta = $pdo->prepare($sqlBta);
    $stmtBta->execute();
    $bta = array_column($stmtBta->fetchAll(), "value");

    $paramsKelas = [];
    $sqlKelas = "
        SELECT DISTINCT c.DESC02 AS value
        FROM scctcust c
        INNER JOIN mst_kelas mk ON mk.id = c.CODE03
        WHERE c.STCUST = '1'
          AND c.DESC02 IS NOT NULL AND c.DESC02 != ''
    ";
    if ($restricted) {
        $ph = [];
        foreach ($allowed as $i => $val) {
            $key = ":k_$i";
            $ph[] = $key;
            $paramsKelas[$key] = $val;
        }
        $sqlKelas .= " AND mk.unit IN (" . implode(",", $ph) . ") ";
    }
    $sqlKelas .= " ORDER BY c.DESC02 ASC LIMIT 100 ";
    $stmtKelas = $pdo->prepare($sqlKelas);
    bindParams($stmtKelas, $paramsKelas);
    $stmtKelas->execute();
    $kelas = array_column($stmtKelas->fetchAll(), "value");

    $paramsJenjang = [];
    $sqlJenjang = "
        SELECT DISTINCT mk.kelas AS value
        FROM mst_kelas mk
        WHERE mk.kelas IS NOT NULL AND mk.kelas != ''
    ";
    if ($restricted) {
        $ph = [];
        foreach ($allowed as $i => $val) {
            $key = ":j_$i";
            $ph[] = $key;
            $paramsJenjang[$key] = $val;
        }
        $sqlJenjang .= " AND mk.unit IN (" . implode(",", $ph) . ") ";
    }
    $sqlJenjang .= " ORDER BY mk.id ASC ";
    $stmtJenjang = $pdo->prepare($sqlJenjang);
    bindParams($stmtJenjang, $paramsJenjang);
    $stmtJenjang->execute();
    $jenjang = array_column($stmtJenjang->fetchAll(), "value");

    if ($daftarUlangOnly) {
        $tagihanList = ["BIAYA Daftar Ulang Ajaran Baru"];
    } else {
        $sqlTagihan = "
            SELECT DISTINCT tagihan AS value
            FROM mst_tagihan
            WHERE tagihan IS NOT NULL AND tagihan != ''
            ORDER BY urut ASC
        ";
        $stmtTagihan = $pdo->prepare($sqlTagihan);
        $stmtTagihan->execute();
        $tagihanList = array_column($stmtTagihan->fetchAll(), "value");
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => [
            "sekolah" => $sekolah,
            "bta" => $bta,
            "kelas" => $kelas,
            "jenjang" => $jenjang,
            "tagihan" => $tagihanList,
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getUangMasuk(array $req, ?string $code01): void
{
    $custid = trim((string) ($req["custid"] ?? ""));

    if ($custid === "") {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "custid wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();
    $params = [];
    $params[":custid"] = (int) $custid;

    $sql = "
        SELECT
            TRXDATE AS tanggal,
            KREDIT AS nominal,
            METODE AS metode,
            NOREFF AS noreff
        FROM sccttran
        WHERE CUSTID = :custid 
          AND KREDIT > 0
          AND REFFBANK != '29'
          AND (METODE IS NULL OR UPPER(METODE) != 'JURNAL SALDO')
        ORDER BY TRXDATE DESC
    ";

    $stmt = $pdo->prepare($sql);
    bindParams($stmt, $params);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row["nominal"] = (float) $row["nominal"];
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getSaldo(array $req, ?string $code01): void
{
    $custidRaw = trim((string) ($req["custid"] ?? ""));

    if ($custidRaw === "" || !ctype_digit($custidRaw)) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "custid tidak valid: '$custidRaw'"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $custid = (int) $custidRaw;

    $pdo = dbConnectPdo();

    $sql = "
        SELECT
            COALESCE(SUM(KREDIT), 0) - COALESCE(SUM(DEBET), 0) AS saldo,
            COALESCE(SUM(KREDIT), 0) AS total_kredit,
            COALESCE(SUM(DEBET), 0) AS total_debet,
            COUNT(*) AS jumlah_baris
        FROM sccttran
        WHERE CUSTID = :custid
          AND REFFBANK IN ('27','28','22','31')
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":custid", $custid, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();

    $saldo = (float) ($row["saldo"] ?? 0);
    $totalKredit = (float) ($row["total_kredit"] ?? 0);
    $totalDebet = (float) ($row["total_debet"] ?? 0);
    $jumlahBaris = (int) ($row["jumlah_baris"] ?? 0);

    writeLog([
        "level" => "DEBUG",
        "event" => "GET_SALDO",
        "custid_raw" => $custidRaw,
        "custid_int" => $custid,
        "jumlah_baris_ditemukan" => $jumlahBaris,
        "saldo" => $saldo,
    ]);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => [
            "custid" => $custid,
            "saldo" => $saldo,
            "total_debet" => $totalDebet,
            "total_kredit" => $totalKredit,
            "jumlah_transaksi" => $jumlahBaris
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function saveRiwayatPenagihan(array $req, ?string $code01): void
{
    $custid = trim((string) ($req["custid"] ?? ""));
    $nocust = trim((string) ($req["nocust"] ?? ""));
    $admin = trim((string) ($req["admin"] ?? $req["username"] ?? ""));
    $tanggal = trim((string) ($req["tanggal_komunikasi"] ?? ""));
    $media = trim((string) ($req["media_komunikasi"] ?? ""));
    $hasil = trim((string) ($req["hasil_komunikasi"] ?? ""));
    $rencana = trim((string) ($req["rencana_pembayaran"] ?? ""));
    $catatan = trim((string) ($req["catatan"] ?? ""));

    $allowedMedia = ["WhatsApp", "Telepon"];
    $allowedHasil = [
        "Akan melakukan pembayaran",
        "Sudah melakukan pembayaran",
        "Meminta waktu pembayaran",
        "Mengalami kendala pembayaran",
        "Tidak dapat dihubungi",
    ];

    if ($custid === "" || !ctype_digit($custid)) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "custid wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($tanggal === "") {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Tanggal komunikasi wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!in_array($media, $allowedMedia, true)) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Media komunikasi tidak valid"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!in_array($hasil, $allowedHasil, true)) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Hasil komunikasi tidak valid"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (mb_strlen($catatan) > 250) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Catatan maksimal 250 karakter"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();
    $stmt = $pdo->prepare("
        INSERT INTO tbl_riwayatpenagihan
            (custid, nocust, admin, tanggal_komunikasi, media_komunikasi, hasil_komunikasi, rencana_pembayaran, catatan, created_at)
        VALUES
            (:custid, :nocust, :admin, :tanggal_komunikasi, :media_komunikasi, :hasil_komunikasi, :rencana_pembayaran, :catatan, NOW())
    ");
    $stmt->bindValue(":custid", (int) $custid, PDO::PARAM_INT);
    $stmt->bindValue(":nocust", $nocust, PDO::PARAM_STR);
    $stmt->bindValue(":admin", $admin, PDO::PARAM_STR);
    $stmt->bindValue(":tanggal_komunikasi", $tanggal, PDO::PARAM_STR);
    $stmt->bindValue(":media_komunikasi", $media, PDO::PARAM_STR);
    $stmt->bindValue(":hasil_komunikasi", $hasil, PDO::PARAM_STR);
    $stmt->bindValue(":rencana_pembayaran", $rencana, PDO::PARAM_STR);
    $stmt->bindValue(":catatan", $catatan, PDO::PARAM_STR);
    $stmt->execute();

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "message" => "Hasil penagihan berhasil disimpan",
        "data" => ["id" => (int) $pdo->lastInsertId()],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function normalizePenagihanDate(string $raw): string
{
    $raw = trim($raw);
    if ($raw === "") {
        return "";
    }

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $raw, $m)) {
        return $m[1] . "-" . $m[2] . "-" . $m[3];
    }
    if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/', $raw, $m)) {
        return sprintf("%04d-%02d-%02d", (int) $m[3], (int) $m[2], (int) $m[1]);
    }
    if (is_numeric($raw)) {
        $n = (float) $raw;
        // Excel serial date
        if ($n > 20000 && $n < 80000) {
            $unix = (int) (($n - 25569) * 86400);
            return gmdate("Y-m-d", $unix);
        }
    }

    $ts = strtotime($raw);
    if ($ts !== false) {
        return date("Y-m-d", $ts);
    }

    return "";
}

function normalizePenagihanMedia(string $raw): string
{
    $raw = trim($raw);
    $norm = strtolower($raw);
    if ($norm === "whatsapp" || $norm === "wa" || $norm === "whats app") {
        return "WhatsApp";
    }
    if ($norm === "telepon" || $norm === "telpon" || $norm === "telephone" || $norm === "phone" || $norm === "tlp" || $norm === "telp") {
        return "Telepon";
    }
    return $raw;
}

function normalizePenagihanHasil(string $raw): string
{
    $raw = trim($raw);
    $allowed = [
        "Akan melakukan pembayaran",
        "Sudah melakukan pembayaran",
        "Meminta waktu pembayaran",
        "Mengalami kendala pembayaran",
        "Tidak dapat dihubungi",
    ];
    foreach ($allowed as $opt) {
        if (strcasecmp($opt, $raw) === 0) {
            return $opt;
        }
    }
    return $raw;
}

function importRiwayatPenagihan(array $req): void
{
    requireSuperAdmin($req);

    $admin = trim((string) ($req["admin"] ?? $req["username"] ?? ""));
    $rows = $req["rows"] ?? [];
    if (!is_array($rows) || empty($rows)) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Data import kosong"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (count($rows) > 2000) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Maksimal 2000 baris per import"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $allowedMedia = ["WhatsApp", "Telepon"];
    $allowedHasil = [
        "Akan melakukan pembayaran",
        "Sudah melakukan pembayaran",
        "Meminta waktu pembayaran",
        "Mengalami kendala pembayaran",
        "Tidak dapat dihubungi",
    ];

    $pdo = dbConnectPdo();
    $findStmt = $pdo->prepare("
        SELECT CUSTID, NOCUST, NMCUST
        FROM scctcust
        WHERE NOCUST = :nis
        LIMIT 1
    ");
    $insertStmt = $pdo->prepare("
        INSERT INTO tbl_riwayatpenagihan
            (custid, nocust, admin, tanggal_komunikasi, media_komunikasi, hasil_komunikasi, rencana_pembayaran, catatan, created_at)
        VALUES
            (:custid, :nocust, :admin, :tanggal_komunikasi, :media_komunikasi, :hasil_komunikasi, :rencana_pembayaran, :catatan, NOW())
    ");

    $success = 0;
    $failed = 0;
    $errors = [];

    foreach ($rows as $i => $row) {
        if (!is_array($row)) {
            $failed++;
            $errors[] = ["row" => $i + 2, "message" => "Format baris tidak valid"];
            continue;
        }

        $excelNo = trim((string) ($row["no"] ?? ($i + 1)));
        $nis = trim((string) ($row["nis"] ?? $row["nocust"] ?? ""));
        $nama = trim((string) ($row["nama"] ?? $row["nama_anak"] ?? ""));
        $tanggal = normalizePenagihanDate(trim((string) ($row["tanggal_komunikasi"] ?? $row["tanggal"] ?? "")));
        $media = normalizePenagihanMedia(trim((string) ($row["media_komunikasi"] ?? $row["media"] ?? "")));
        $hasil = normalizePenagihanHasil(trim((string) ($row["hasil_komunikasi"] ?? $row["hasil"] ?? "")));
        $rencana = trim((string) ($row["rencana_pembayaran"] ?? $row["rencana"] ?? ""));
        $catatan = trim((string) ($row["catatan"] ?? ""));

        $rowLabel = $excelNo !== "" ? $excelNo : (string) ($i + 2);

        if ($nis === "") {
            $failed++;
            $errors[] = ["row" => $rowLabel, "nis" => $nis, "message" => "NIS kosong"];
            continue;
        }
        if ($tanggal === "") {
            $failed++;
            $errors[] = ["row" => $rowLabel, "nis" => $nis, "message" => "Tanggal komunikasi tidak valid"];
            continue;
        }
        if (!in_array($media, $allowedMedia, true)) {
            $failed++;
            $errors[] = ["row" => $rowLabel, "nis" => $nis, "message" => "Media harus WhatsApp atau Telepon"];
            continue;
        }
        if (!in_array($hasil, $allowedHasil, true)) {
            $failed++;
            $errors[] = ["row" => $rowLabel, "nis" => $nis, "message" => "Hasil komunikasi tidak valid"];
            continue;
        }
        if (mb_strlen($catatan) > 250) {
            $failed++;
            $errors[] = ["row" => $rowLabel, "nis" => $nis, "message" => "Catatan maksimal 250 karakter"];
            continue;
        }

        $findStmt->bindValue(":nis", $nis, PDO::PARAM_STR);
        $findStmt->execute();
        $siswa = $findStmt->fetch();
        if (!$siswa) {
            $failed++;
            $errors[] = ["row" => $rowLabel, "nis" => $nis, "message" => "NIS tidak ditemukan di scctcust"];
            continue;
        }

        try {
            $insertStmt->bindValue(":custid", (int) $siswa["CUSTID"], PDO::PARAM_INT);
            $insertStmt->bindValue(":nocust", (string) ($siswa["NOCUST"] ?? $nis), PDO::PARAM_STR);
            $insertStmt->bindValue(":admin", $admin, PDO::PARAM_STR);
            $insertStmt->bindValue(":tanggal_komunikasi", $tanggal, PDO::PARAM_STR);
            $insertStmt->bindValue(":media_komunikasi", $media, PDO::PARAM_STR);
            $insertStmt->bindValue(":hasil_komunikasi", $hasil, PDO::PARAM_STR);
            $insertStmt->bindValue(":rencana_pembayaran", $rencana, PDO::PARAM_STR);
            $insertStmt->bindValue(":catatan", $catatan, PDO::PARAM_STR);
            $insertStmt->execute();
            $success++;
        } catch (PDOException $e) {
            $failed++;
            $errors[] = ["row" => $rowLabel, "nis" => $nis, "message" => "Gagal insert: " . $e->getMessage()];
        }
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "message" => "Import selesai",
        "data" => [
            "success" => $success,
            "failed" => $failed,
            "errors" => array_slice($errors, 0, 100),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getRiwayatPenagihan(array $req, ?string $code01): void
{
    $custid = trim((string) ($req["custid"] ?? ""));

    if ($custid === "" || !ctype_digit($custid)) {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "custid wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $limit = (int) ($req["limit"] ?? 0);
    if ($limit < 0) $limit = 0;
    if ($limit > 500) $limit = 500;

    $pdo = dbConnectPdo();
    $sql = "
        SELECT
            idincrement AS id,
            custid,
            nocust,
            admin,
            tanggal_komunikasi,
            media_komunikasi,
            hasil_komunikasi,
            rencana_pembayaran,
            catatan,
            created_at
        FROM tbl_riwayatpenagihan
        WHERE custid = :custid
        ORDER BY tanggal_komunikasi DESC, idincrement DESC
    ";
    if ($limit > 0) {
        $sql .= " LIMIT " . $limit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":custid", (int) $custid, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => $rows,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function listRiwayatPenagihan(array $req): void
{
    requireSuperAdmin($req);

    $page = (int) ($req["page"] ?? 1);
    $perPage = (int) ($req["per_page"] ?? 15);
    if ($page < 1) $page = 1;
    if ($perPage < 5) $perPage = 5;
    if ($perPage > 100) $perPage = 100;
    $offset = ($page - 1) * $perPage;

    $q = trim((string) ($req["q"] ?? ""));

    $pdo = dbConnectPdo();
    $where = " WHERE 1=1 ";
    $params = [];

    if ($q !== "") {
        $where .= " AND (
            r.nocust LIKE :q
            OR r.admin LIKE :q
            OR r.hasil_komunikasi LIKE :q
            OR r.media_komunikasi LIKE :q
            OR r.catatan LIKE :q
            OR c.NMCUST LIKE :q
        ) ";
        $params[":q"] = "%" . $q . "%";
    }

    $countSql = "
        SELECT COUNT(*) AS total
        FROM tbl_riwayatpenagihan r
        LEFT JOIN scctcust c ON c.CUSTID = r.custid
        $where
    ";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $k => $v) {
        $countStmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $total = (int) ($countStmt->fetchColumn() ?: 0);
    $lastPage = max(1, (int) ceil($total / $perPage));
    if ($page > $lastPage) {
        $page = $lastPage;
        $offset = ($page - 1) * $perPage;
    }

    $sql = "
        SELECT
            r.idincrement AS id,
            r.custid,
            r.nocust AS nis,
            COALESCE(c.NMCUST, '') AS nama,
            r.admin,
            r.tanggal_komunikasi,
            r.media_komunikasi,
            r.hasil_komunikasi,
            r.rencana_pembayaran,
            r.catatan,
            r.created_at
        FROM tbl_riwayatpenagihan r
        LEFT JOIN scctcust c ON c.CUSTID = r.custid
        $where
        ORDER BY r.tanggal_komunikasi DESC, r.idincrement DESC
        LIMIT {$perPage} OFFSET {$offset}
    ";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => $rows,
        "meta" => [
            "page" => $page,
            "per_page" => $perPage,
            "total" => $total,
            "last_page" => $lastPage,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getSummaryTagihan(array $req, ?string $code01): void
{
    $pdo = dbConnectPdo();
    $params = [];

    $sql = "
        SELECT
            COALESCE(SUM(b.BILLAM), 0) AS total_tagihan,
            COALESCE(SUM(CASE WHEN b.PAIDST = '1' THEN b.BILLAM ELSE 0 END), 0) AS total_terbayar,
            COUNT(DISTINCT c.CUSTID) AS total_siswa
        FROM scctcust c
        INNER JOIN scctbill b ON b.CUSTID = c.CUSTID
        WHERE c.STCUST = '1'
            AND b.FSTSBolehBayar = 1
    ";

    $bta = trim((string) ($req["bta"] ?? $req["tahun_akademik"] ?? ""));
    if ($bta !== "") {
        $sql .= " AND b.BTA = :bta ";
        $params[":bta"] = $bta;
    }

    $allowed = parseCode01List($code01);
    $selected = normalizeMultiValue($req["sekolah"] ?? []);
    if (!empty($allowed)) {
        $use = $allowed;
        if (!empty($selected)) {
            $use = array_values(array_intersect($selected, $allowed));
            if (empty($use)) {
                $sql .= " AND 1=0 ";
                $use = [];
            }
        }
        if (!empty($use)) {
            $ph = [];
            foreach ($use as $i => $val) {
                $key = ":sumsek_$i";
                $ph[] = $key;
                $params[$key] = $val;
            }
            $sql .= " AND EXISTS (
                SELECT 1 FROM mst_kelas mk
                WHERE mk.id = c.CODE03
                  AND mk.unit IN (" . implode(",", $ph) . ")
            ) ";
        }
    } elseif (!empty($selected)) {
        $ph = [];
        foreach ($selected as $i => $val) {
            $key = ":sumsek_$i";
            $ph[] = $key;
            $params[$key] = $val;
        }
        $sql .= " AND EXISTS (
            SELECT 1 FROM mst_kelas mk
            WHERE mk.id = c.CODE03
              AND mk.unit IN (" . implode(",", $ph) . ")
        ) ";
    }

    $jenjang = trim((string) ($req["jenjang"] ?? ""));
    if ($jenjang !== "") {
        $sql .= " AND EXISTS (
            SELECT 1 FROM mst_kelas mk 
            WHERE mk.id = c.CODE03 
              AND mk.kelas = :jenjang
        ) ";
        $params[":jenjang"] = $jenjang;
    }

    $kelasList = normalizeMultiValue($req["kelas"] ?? []);
    if (!empty($kelasList)) {
        $ph = [];
        foreach ($kelasList as $i => $val) {
            $key = ":kelas_$i";
            $ph[] = $key;
            $params[$key] = $val;
        }
        $sql .= " AND c.DESC02 IN (" . implode(",", $ph) . ") ";
    }

    $search = trim((string) ($req["search"] ?? ""));
    if ($search !== "") {
        $like = "%" . $search . "%";
        $sql .= " AND (c.NMCUST LIKE :search_nama OR c.NOCUST LIKE :search_nis) ";
        $params[":search_nama"] = $like;
        $params[":search_nis"] = $like;
    }

    $sql .= buildBillNmInSql($req, $params, "b.BILLNM", "billnm_sum");

    $stmt = $pdo->prepare($sql);
    bindParams($stmt, $params);
    $stmt->execute();
    $row = $stmt->fetch();

    $totalTagihan = (float) ($row["total_tagihan"] ?? 0);
    $totalTerbayar = (float) ($row["total_terbayar"] ?? 0);
    $totalPiutang = $totalTagihan - $totalTerbayar;

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => [
            "total_siswa" => (int) ($row["total_siswa"] ?? 0),
            "total_tagihan" => $totalTagihan,
            "total_terbayar" => $totalTerbayar,
            "total_piutang" => $totalPiutang,
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function cashlessRowValue(array $row, array $keys, $default = null)
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null && (string) $row[$key] !== "") {
            return $row[$key];
        }
        $upper = strtoupper($key);
        if (array_key_exists($upper, $row) && $row[$upper] !== null && (string) $row[$upper] !== "") {
            return $row[$upper];
        }
        $lower = strtolower($key);
        if (array_key_exists($lower, $row) && $row[$lower] !== null && (string) $row[$lower] !== "") {
            return $row[$lower];
        }
    }
    return $default;
}

function verifyCashlessPassword(string $plain, string $passwordHash): bool
{
    if ($passwordHash === "") {
        return false;
    }
    if (strlen($passwordHash) === 32 && ctype_xdigit($passwordHash)) {
        return md5($plain) === strtolower($passwordHash) || md5($plain) === $passwordHash;
    }
    if (strlen($passwordHash) === 64 && ctype_xdigit($passwordHash)) {
        return hash("sha256", $plain) === strtolower($passwordHash) || hash("sha256", $plain) === $passwordHash;
    }
    if (str_starts_with($passwordHash, "$2")) {
        return password_verify($plain, $passwordHash);
    }
    return $plain === $passwordHash;
}

function loginCashless(array $req): void
{
    $username = trim((string) ($req["username"] ?? ""));
    $password = trim((string) ($req["password"] ?? ""));

    if ($username === "" || $password === "") {
        http_response_code(422);
        echo json_encode(["status" => 422, "message" => "Username dan password wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = dbConnectPdo();

    $stmt = $pdo->prepare("
        SELECT *
        FROM Sm_kantin
        WHERE USERNAME = :username
        LIMIT 1
    ");
    $stmt->bindValue(":username", $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user || !is_array($user)) {
        // Fallback untuk kolom lowercase
        try {
            $stmt2 = $pdo->prepare("SELECT * FROM Sm_kantin WHERE username = :username LIMIT 1");
            $stmt2->bindValue(":username", $username, PDO::PARAM_STR);
            $stmt2->execute();
            $user = $stmt2->fetch();
        } catch (Throwable $e) {
            $user = false;
        }
    }

    if (!$user || !is_array($user)) {
        http_response_code(401);
        echo json_encode(["status" => 401, "message" => "Username atau password salah"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $passwordHash = (string) cashlessRowValue($user, ["PASSWORD", "password", "Password"], "");
    if (!verifyCashlessPassword($password, $passwordHash)) {
        http_response_code(401);
        echo json_encode(["status" => 401, "message" => "Username atau password salah"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $key = (string) ($_ENV["JWT_KEY"] ?? "");
    if ($key === "") {
        http_response_code(500);
        echo json_encode(["status" => 500, "message" => "JWT_KEY belum di set"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $uname = (string) cashlessRowValue($user, ["USERNAME", "username"], $username);
    $nama = (string) cashlessRowValue($user, ["NAMA", "nama", "NAME", "name"], $uname);
    $userId = cashlessRowValue($user, ["idincrement", "ID", "id", "CUSTID", "urut"], $uname);

    $payload = [
        "user_id"  => $userId,
        "username" => $uname,
        "nama"     => $nama,
        "app"      => "laporan-cashless",
        "iat"      => time(),
        "exp"      => time() + 86400,
    ];

    $jwt = new JWT();
    $token = $jwt->encode($payload, $key, "HS256");

    http_response_code(200);
    echo json_encode([
        "status"  => 200,
        "message" => "Login berhasil",
        "data"    => [
            "token"    => $token,
            "nama"     => $nama,
            "username" => $uname,
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function resolveCashlessTellerUsername(array $req): string
{
    $username = trim((string) ($req["username"] ?? ""));
    if ($username === "") {
        http_response_code(401);
        echo json_encode(["status" => 401, "message" => "Sesi teller tidak valid"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    return $username;
}

function buildCashlessFilterSql(array $req, array &$params): string
{
    $sql = "";

    // Kunci data per user login (Sm_kantin.USERNAME = scctcashout.Teller)
    // Client tidak bisa override untuk melihat teller lain.
    $lockedTeller = resolveCashlessTellerUsername($req);
    $sql .= " AND o.Teller = :locked_teller ";
    $params[":locked_teller"] = $lockedTeller;

    $tglDari = trim((string) ($req["tgl_dari"] ?? $req["tanggal_dari"] ?? ""));
    $tglSampai = trim((string) ($req["tgl_sampai"] ?? $req["tanggal_sampai"] ?? ""));

    if ($tglDari === "" && $tglSampai === "") {
        $tglDari = date("Y-m-d");
        $tglSampai = date("Y-m-d");
    } elseif ($tglDari === "") {
        $tglDari = $tglSampai;
    } elseif ($tglSampai === "") {
        $tglSampai = $tglDari;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglDari) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglSampai)) {
        throw new InvalidArgumentException("Format tanggal tidak valid. Gunakan YYYY-MM-DD");
    }

    if ($tglDari > $tglSampai) {
        $tmp = $tglDari;
        $tglDari = $tglSampai;
        $tglSampai = $tmp;
    }

    $params[":tgl_dari"] = $tglDari . " 00:00:00";
    $params[":tgl_sampai"] = $tglSampai . " 23:59:59";
    $sql .= " AND o.TanggalKeluar >= :tgl_dari AND o.TanggalKeluar <= :tgl_sampai ";

    $sekolahList = normalizeMultiValue($req["sekolah"] ?? $req["unit"] ?? []);
    if (!empty($sekolahList)) {
        $ph = [];
        foreach ($sekolahList as $i => $val) {
            $key = ":sek_$i";
            $ph[] = $key;
            $params[$key] = $val;
        }
        $in = implode(",", $ph);
        $sql .= " AND (c.CODE01 IN ($in) OR c.CODE02 IN ($in)) ";
    }

    $kelasList = normalizeMultiValue($req["kelas"] ?? []);
    if (!empty($kelasList)) {
        $ph = [];
        foreach ($kelasList as $i => $val) {
            $key = ":kel_$i";
            $ph[] = $key;
            $params[$key] = $val;
        }
        $sql .= " AND c.DESC02 IN (" . implode(",", $ph) . ") ";
    }

    $keterangan = trim((string) ($req["keterangan"] ?? ""));
    if ($keterangan !== "") {
        $sql .= " AND o.KETERANGAN = :keterangan ";
        $params[":keterangan"] = $keterangan;
    }

    $search = trim((string) ($req["search"] ?? ""));
    if ($search !== "") {
        $sql .= " AND (c.NMCUST LIKE :search_nama OR c.NOCUST LIKE :search_nis OR o.TRANSNO LIKE :search_trans) ";
        $like = "%" . $search . "%";
        $params[":search_nama"] = $like;
        $params[":search_nis"] = $like;
        $params[":search_trans"] = $like;
    }

    return $sql;
}

function getCashlessFilterOptions(array $req): void
{
    $pdo = dbConnectPdo();
    $lockedTeller = resolveCashlessTellerUsername($req);

    $sekolah = [];
    try {
        $stmtSekolah = $pdo->query("
            SELECT DISTINCT unit AS value
            FROM mst_kelas
            WHERE unit IS NOT NULL AND unit != ''
            ORDER BY unit ASC
        ");
        foreach ($stmtSekolah->fetchAll() as $row) {
            $val = trim((string) ($row["value"] ?? ""));
            if ($val === "") {
                continue;
            }
            $sekolah[] = ["value" => $val, "label" => $val];
        }
    } catch (Throwable $e) {
        $stmtSekolah = $pdo->query("
            SELECT DISTINCT COALESCE(NULLIF(c.CODE02, ''), c.CODE01) AS value
            FROM scctcust c
            WHERE COALESCE(NULLIF(c.CODE02, ''), c.CODE01) IS NOT NULL
              AND COALESCE(NULLIF(c.CODE02, ''), c.CODE01) != ''
            ORDER BY value ASC
            LIMIT 200
        ");
        foreach ($stmtSekolah->fetchAll() as $row) {
            $val = trim((string) ($row["value"] ?? ""));
            if ($val === "") {
                continue;
            }
            $sekolah[] = ["value" => $val, "label" => $val];
        }
    }

    $kelas = [];
    $stmtKelas = $pdo->query("
        SELECT DISTINCT c.DESC02 AS value
        FROM scctcust c
        WHERE c.DESC02 IS NOT NULL AND c.DESC02 != ''
        ORDER BY c.DESC02 ASC
        LIMIT 300
    ");
    foreach ($stmtKelas->fetchAll() as $row) {
        $val = trim((string) ($row["value"] ?? ""));
        if ($val !== "") {
            $kelas[] = $val;
        }
    }

    // Hanya keterangan dari transaksi teller yang sedang login
    $keterangan = [];
    $stmtKet = $pdo->prepare("
        SELECT DISTINCT KETERANGAN AS value
        FROM scctcashout
        WHERE Teller = :teller
          AND KETERANGAN IS NOT NULL AND KETERANGAN != ''
        ORDER BY KETERANGAN ASC
        LIMIT 200
    ");
    $stmtKet->bindValue(":teller", $lockedTeller, PDO::PARAM_STR);
    $stmtKet->execute();
    foreach ($stmtKet->fetchAll() as $row) {
        $val = trim((string) ($row["value"] ?? ""));
        if ($val !== "") {
            $keterangan[] = $val;
        }
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => [
            "sekolah" => $sekolah,
            "kelas" => $kelas,
            "teller" => [$lockedTeller],
            "keterangan" => $keterangan,
            "locked_teller" => $lockedTeller,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getCashlessData(array $req): void
{
    $limit = (int) ($req["limit"] ?? 50);
    $offset = (int) ($req["offset"] ?? 0);
    if ($limit <= 0) {
        $limit = 50;
    }
    if ($limit > 5000) {
        $limit = 5000;
    }
    if ($offset < 0) {
        $offset = 0;
    }

    $pdo = dbConnectPdo();
    $params = [];
    $filterSql = buildCashlessFilterSql($req, $params);

    $sql = "
        SELECT
            o.urut,
            o.CUSTID AS custid,
            o.TanggalKeluar AS tanggal,
            o.Teller AS teller,
            CAST(o.BILLAM AS DECIMAL(18,2)) AS jumlah,
            o.BILLAM AS billam_raw,
            o.TRANSNO AS transno,
            o.FIDBANK AS fidbank,
            o.KETERANGAN AS keterangan,
            c.NOCUST AS nis,
            c.NMCUST AS nama,
            c.CODE01 AS code01,
            c.CODE02 AS unit,
            c.DESC02 AS kelas
        FROM scctcashout o
        LEFT JOIN scctcust c ON c.CUSTID = o.CUSTID
        WHERE 1=1
        $filterSql
        ORDER BY o.TanggalKeluar DESC, o.urut DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    bindParams($stmt, $params);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $out = [];
    foreach ($rows as $row) {
        $out[] = [
            "urut" => (int) ($row["urut"] ?? 0),
            "custid" => (int) ($row["custid"] ?? 0),
            "tanggal" => (string) ($row["tanggal"] ?? ""),
            "teller" => trim((string) ($row["teller"] ?? "")),
            "jumlah" => (float) ($row["jumlah"] ?? 0),
            "billam_raw" => (string) ($row["billam_raw"] ?? ""),
            "transno" => trim((string) ($row["transno"] ?? "")),
            "fidbank" => trim((string) ($row["fidbank"] ?? "")),
            "keterangan" => trim((string) ($row["keterangan"] ?? "")),
            "nis" => trim((string) ($row["nis"] ?? "")),
            "nama" => trim((string) ($row["nama"] ?? "")),
            "code01" => trim((string) ($row["code01"] ?? "")),
            "unit" => trim((string) ($row["unit"] ?? "")),
            "kelas" => trim((string) ($row["kelas"] ?? "")),
            "sekolah" => trim((string) (($row["unit"] ?? "") !== "" ? $row["unit"] : ($row["code01"] ?? ""))),
        ];
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => $out,
        "pagination" => [
            "limit" => $limit,
            "offset" => $offset,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getCashlessSummary(array $req): void
{
    $pdo = dbConnectPdo();
    $params = [];
    $filterSql = buildCashlessFilterSql($req, $params);

    $sql = "
        SELECT
            COUNT(*) AS total_transaksi,
            COALESCE(SUM(CAST(o.BILLAM AS DECIMAL(18,2))), 0) AS total_jumlah
        FROM scctcashout o
        LEFT JOIN scctcust c ON c.CUSTID = o.CUSTID
        WHERE 1=1
        $filterSql
    ";

    $stmt = $pdo->prepare($sql);
    bindParams($stmt, $params);
    $stmt->execute();
    $row = $stmt->fetch() ?: ["total_transaksi" => 0, "total_jumlah" => 0];

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "data" => [
            "total_transaksi" => (int) ($row["total_transaksi"] ?? 0),
            "total_jumlah" => (float) ($row["total_jumlah"] ?? 0),
            "tgl_dari" => substr((string) ($params[":tgl_dari"] ?? ""), 0, 10),
            "tgl_sampai" => substr((string) ($params[":tgl_sampai"] ?? ""), 0, 10),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER["REQUEST_METHOD"] ?? "") === "OPTIONS") {
    http_response_code(204);
    exit;
}

try {
    loadEnv(__DIR__ . "/.env");

    $req = getJsonInput();

    $method = trim((string) ($req["method"] ?? ""));

    if ($method === "login") {
        login($req);
        exit;
    }

    if ($method === "loginCashless") {
        loginCashless($req);
        exit;
    }

    $token = null;
    if (isset($req["token"]) && is_string($req["token"]) && $req["token"] !== "") {
        $token = $req["token"];
    } elseif (isset($_SERVER["HTTP_AUTHORIZATION"])) {
        $authHeader = $_SERVER["HTTP_AUTHORIZATION"];
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }
    }

    if (!$token) {
        http_response_code(401);
        echo json_encode(["status" => 401, "message" => "Token wajib diisi"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $jwt = new JWT();
    $key = (string) ($_ENV["JWT_KEY"] ?? "");
    if ($key === "") {
        http_response_code(500);
        echo json_encode(["status" => 500, "message" => "JWT_KEY belum di set"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $decoded = $jwt->decode($token, $key, ["HS256"]);
        if (is_object($decoded)) $decoded = (array) $decoded;
        $req = array_merge($req, (array) $decoded);
    } catch (Throwable $e) {
        http_response_code(401);
        echo json_encode(["status" => 401, "message" => "Token JWT tidak valid"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $code01 = isset($req["code01"]) && (string)$req["code01"] !== "" ? (string)$req["code01"] : null;

    if ($method === "getSiswaAggregated") {
        getSiswaAggregated($req, $code01);
        exit;
    }

    if ($method === "getSiswaCount") {
        getSiswaCount($req, $code01);
        exit;
    }

    if ($method === "getSiswaTagihan") {
        getSiswaTagihan($req, $code01);
        exit;
    }

    if ($method === "getUangMasuk") {
        getUangMasuk($req, $code01);
        exit;
    }

    if ($method === "getDetailTagihan") {
        getDetailTagihan($req, $code01);
        exit;
    }

    if ($method === "getTagihanInvoice") {
        getTagihanInvoice($req, $code01);
        exit;
    }

    if ($method === "getFilterOptions") {
        getFilterOptions($req, $code01);
        exit;
    }

    if ($method === "getSiswaPeriode") {
        getSiswaPeriode($req, $code01);
        exit;
    }

    if ($method === "getSiswaTagihanPeriode") {
        getSiswaTagihanPeriode($req, $code01);
        exit;
    }

    if ($method === "getSummaryTagihanPeriode") {
        getSummaryTagihanPeriode($req, $code01);
        exit;
    }

    if ($method === "getDashboardKeuangan") {
        getDashboardKeuangan($req, $code01);
        exit;
    }

    if ($method === "getKelasBySekolah") {
        getKelasBySekolah($req, $code01);
        exit;
    }

    if ($method === "getSummaryTagihan") {
        getSummaryTagihan($req, $code01);
        exit;
    }

    if ($method === "getSaldo") {
        getSaldo($req, $code01);
        exit;
    }

    if ($method === "saveRiwayatPenagihan") {
        saveRiwayatPenagihan($req, $code01);
        exit;
    }

    if ($method === "importRiwayatPenagihan") {
        importRiwayatPenagihan($req);
        exit;
    }

    if ($method === "listRiwayatPenagihan") {
        listRiwayatPenagihan($req);
        exit;
    }

    if ($method === "getRiwayatPenagihan") {
        getRiwayatPenagihan($req, $code01);
        exit;
    }

    if ($method === "getKepsekUsers") {
        getKepsekUsers($req);
        exit;
    }

    if ($method === "createKepsekUser") {
        createKepsekUser($req);
        exit;
    }

    if ($method === "updateKepsekUser") {
        updateKepsekUser($req);
        exit;
    }

    if ($method === "deleteKepsekUser") {
        deleteKepsekUser($req);
        exit;
    }

    if ($method === "getCashlessFilterOptions") {
        getCashlessFilterOptions($req);
        exit;
    }

    if ($method === "getCashlessData") {
        getCashlessData($req);
        exit;
    }

    if ($method === "getCashlessSummary") {
        getCashlessSummary($req);
        exit;
    }

    if ($method === "getSiswaListOnly") {
        http_response_code(410);
        echo json_encode(["status" => 410, "message" => "Method getSiswaListOnly sudah diganti dengan getSiswaAggregated"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === "getDataTagihan") {
        http_response_code(410);
        echo json_encode(["status" => 410, "message" => "Method getDataTagihan sudah diganti dengan getSiswaAggregated"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(422);
    echo json_encode(["status" => 422, "message" => "Metode '$method' tidak valid"], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    writeLog([
        "level"   => "ERROR",
        "event"   => "EXCEPTION",
        "type"    => get_class($e),
        "message" => $e->getMessage(),
        "file"    => $e->getFile(),
        "line"    => $e->getLine()
    ]);

    http_response_code(500);
    echo json_encode(["status" => 500, "message" => "Gagal mengambil data: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
