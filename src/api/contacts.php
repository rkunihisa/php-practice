<?php
require_once __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

define('JWT_SECRET', 'your_jwt_secret_key');
// Helper function to get Authorization header reliably
function getAuthorizationHeader(): string {
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            return $headers['Authorization'];
        }
        // Some servers use lowercase keys
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                return $value;
            }
        }
    }
    return '';
}

$authHeader = getAuthorizationHeader();
if (preg_match('/^Bearer\s+(\S+)/', $authHeader, $m)) {
    $jwt = $m[1];
    try {
        $payload = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
    } catch (Exception $e) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
} else {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// CORS（開発用。必要に応じて調整）
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../shared/db.php';

// 共通応答関数
function respond(int $code, array $payload): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// 入力JSON受け取り
function getJsonInput(): array {
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === null) return [];
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        respond(400, ['status' => 'error', 'message' => 'Invalid JSON']);
    }
    return $data;
}

// シンプルバリデーション
function validateContact(array $data, bool $requireAll = true): array {
    $errors = [];
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $inquiry = $data['inquiry'] ?? '';

    if ($requireAll || isset($data['name'])) {
        if (trim($name) === '') $errors[] = 'name is required';
    }
    if ($requireAll || isset($data['email'])) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'valid email required';
    }
    if ($requireAll || isset($data['inquiry'])) {
        if (trim($inquiry) === '') $errors[] = 'inquiry is required';
    }
    return $errors;
}

// DB操作関数群（DI対応）
function getContactById(PDO $pdo, int $id): ?array {
    $st = $pdo->prepare('SELECT * FROM contacts WHERE id=?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function getContacts(PDO $pdo): array {
    $st = $pdo->query('SELECT * FROM contacts ORDER BY created_at DESC LIMIT 100');
    return $st->fetchAll();
}

function insertContact(PDO $pdo, array $input): int {
    $st = $pdo->prepare('INSERT INTO contacts (name,email,inquiry) VALUES (?,?,?)');
    $st->execute([
        trim($input['name']),
        trim($input['email']),
        trim($input['inquiry'])
    ]);
    return (int)$pdo->lastInsertId();
}

function updateContact(PDO $pdo, int $id, array $input): void {
    $st = $pdo->prepare('UPDATE contacts SET name=?, email=?, inquiry=? WHERE id=?');
    $st->execute([
        trim($input['name']),
        trim($input['email']),
        trim($input['inquiry']),
        $id
    ]);
}

function patchContact(PDO $pdo, int $id, array $input): void {
    $fields = []; $params = [];
    foreach (['name','email','inquiry'] as $f) {
        if (array_key_exists($f, $input)) { $fields[] = "$f=?"; $params[] = trim($input[$f]); }
    }
    if (!$fields) respond(400, ['status'=>'error','message'=>'no updatable fields']);
    $params[] = $id;
    $sql = 'UPDATE contacts SET '.implode(', ', $fields).' WHERE id=?';
    $st = $pdo->prepare($sql);
    $st->execute($params);
}

function deleteContact(PDO $pdo, int $id): void {
    $st = $pdo->prepare('DELETE FROM contacts WHERE id=?');
    $st->execute([$id]);
    if ($st->rowCount() === 0) respond(404, ['status'=>'error','message'=>'Not found']);
}

$pdo    = pdo();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : null;

try {
    switch ($method) {
        case 'GET':
            if ($id !== null) {
                $row = getContactById($pdo, $id);
                if (!$row) respond(404, ['status'=>'error','message'=>'Not found']);
                respond(200, ['status'=>'ok','data'=>$row]);
            } else {
                $rows = getContacts($pdo);
                respond(200, ['status'=>'ok', 'count'=>count($rows), 'data'=>$rows]);
            }
            break;

        case 'POST':
            $input  = getJsonInput();
            $errors = validateContact($input, true);
            if ($errors) respond(400, ['status'=>'error','errors'=>$errors]);
            $newId = insertContact($pdo, $input);
            header('Location: /api/contacts.php?id='.$newId);
            respond(201, ['status'=>'ok','id'=>$newId]);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) respond(400, ['status'=>'error','message'=>'id is required']);
            $input  = getJsonInput();
            $requireAll = ($method === 'PUT');
            $errors = validateContact($input, $requireAll);
            if ($errors) respond(400, ['status'=>'error','errors'=>$errors]);

            if ($method === 'PUT') {
                updateContact($pdo, $id, $input);
            } else {
                patchContact($pdo, $id, $input);
            }
            // 更新件数チェック
            $chk = $pdo->prepare('SELECT 1 FROM contacts WHERE id=?');
            $chk->execute([$id]);
            if (!$chk->fetchColumn()) respond(404, ['status'=>'error','message'=>'Not found']);
            respond(200, ['status'=>'ok','id'=>$id]);
            break;

        case 'DELETE':
            if ($id === null) respond(400, ['status'=>'error','message'=>'id is required']);
            deleteContact($pdo, $id);
            http_response_code(204); exit;
            break;

        default:
            header('Allow: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            respond(405, ['status'=>'error','message'=>'Method Not Allowed']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    respond(500, ['status'=>'error','message'=>'Internal Server Error']);
}
