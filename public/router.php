<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");


require __DIR__ . '/../backend/utils/tools.php';
require __DIR__ . '/../backend/utils/db.php';
require __DIR__ . '/../backend/api/user.php';
require __DIR__ . '/../backend/api/courses.php';



$routes = [
    'GET /api/user/info' => authentication('handleUserInfo'),
    
    
    'POST /api/user/login' => 'handleLogin',
    'POST /api/user/register' => 'handleRegister',
    'GET /api/courses/list' => 'listAllCourses',

    
    'POST /api/purchase/add' => authentication('purchaseAdd'),
    'POST /api/purchase/remove' => authentication('purchaseRemove'),
    'POST /api/purchase/validate' => 'purchaseValidate',
];



$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
if ($requestUri === '/' || $requestUri === '/index.php') {
    include 'home.php';
    exit;
}
$routeKey = "$requestMethod $requestUri";

if (isset($routes[$routeKey])) {
    $handler = $routes[$routeKey];
    
    if (is_callable($handler)) {
        call_user_func($handler);
    } else {
        
        call_user_func_array(explode('@', $handler), []);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
