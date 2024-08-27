<?php
ini_set("soap.wsdl_cache_enabled", "0");

header("Access-Control-Allow-Origin: *"); // Allow all origins
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers

// If the request is an OPTIONS request, end it here
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$files = [
    'authentication' => './services/authentication.php',
];
$services = [
    'authentication' => 'AuthenticationService'
];


require_once './router/router.php';
require_once './utilities.php';
require '../vendor/autoload.php';


$Router = new Router();

$Router->post('/authentication/register', function ($req) {
    $authService = getService();
    $deviceId = $req->body->id;
    $result = $authService->register($deviceId);
    if ($result['success'] == true) {
        responseRequest(200, $result['msg'], true,);
    } else {
        responseRequest($result['codigo'], $result['error'], true,);
    }
});


$Router->notFound(function () {
    responseRequest(
        404,
        'API not found',
        true
    );
});
