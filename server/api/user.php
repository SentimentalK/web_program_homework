<?php

require __DIR__ . '../../../vendor/autoload.php';
define('JWT_SECRET', 'your-256-bit-secret');
define('JWT_ALGORITHM', 'HS256');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateToken($userId)
{
    $issuedAt = time();
    $expire = $issuedAt + 6000; 

    $payload = [
        'iss' => 'xinghanxu', 
        'iat' => $issuedAt,         
        'exp' => $expire,           
        'sub' => $userId            
    ];

    return JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);
}

function validateToken()
{
    
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    } else {
        sendJsonError('Authorization token not found', 401);
    }

    try {
        
        $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGORITHM));

        
        if ($decoded->iss !== 'xinghanxu') {
            sendJsonError('Invalid token issuer', 401);
        }

        
        $userId = $decoded->sub;
        $_SERVER['AUTH_USER_ID'] = $userId;
        return $userId;

    } catch (Firebase\JWT\ExpiredException $e) {
        sendJsonError('Token has expired', 401);
    } catch (Firebase\JWT\SignatureInvalidException $e) {
        sendJsonError('Invalid token signature', 401);
    } catch (Exception $e) {
        
        sendJsonError('Invalid token: ' . $e->getMessage(), 401);
    }
}

function authentication(callable $handler)
{
    return function () use ($handler) {
        try {
            
            validateToken();

            
            return $handler();
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['error' => $e->getMessage()]);
            exit; 
        }
    };
}


function handleRegister()
{
    global $db;
    $data = getJsonData();

    
    $requiredFields = ['username', 'password', 'email', 'phone'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            sendJsonError("Missing required field: $field", 400);
        }
    }

    try {
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        
        $stmt = $db->prepare("
            INSERT INTO users 
            (username, password_hash, email, phone) 
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['username'],
            $hashedPassword,
            $data['email'],
            $data['phone']
        ]);

        $stmt = $db->prepare("
            SELECT * from users WHERE username = :username
        ");

        $stmt->execute([':username' => $data['username']]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        
        $token = generateToken($data['id']);

        echo json_encode([
            'code' => 200,
            'token' => $token,
            'msg' => 'Registration successful'
        ]);

    } catch (PDOException $e) {
        
        $errorMsg = 'Registration failed';
        if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
            preg_match("/UNIQUE constraint failed: \w+\.(\w+)/", $e->getMessage(), $matches);
            $errorField = $matches[1] ?? 'Field';
            $errorMsg = ucfirst($errorField) . ' already exists';
        }
        sendJsonError($errorMsg, 400);
    } catch (Exception $e) {
        sendJsonError($e->getMessage(), $e->getCode());
    }
}


function handleLogin()
{
    global $db;
    $data = getJsonData();

    $stmt = $db->prepare("SELECT username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    try {
        if ($user && password_verify($data['password'], $user['password_hash'])) {
            
            $token = generateToken($user['username']);
            echo json_encode([
                'code' => 200,
                'msg' => 'login success',
                'token' => $token,
                'username' => $user['username']
            ]);
        } else {
            sendJsonError("Username or Password incorrect.", 401);
        }
    } catch (Exception $e) {
        sendJsonError($e->getMessage(), 400);
    }
}


function handleUserInfo()
{
    global $db;
    $user_id = $_SERVER['AUTH_USER_ID'] ?? null;
    $stmt = $db->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        sendJsonError('user not exist', 404);
    }
    echo json_encode($user);
}