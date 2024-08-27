<?php


function responseRequest($code, $message, $finishConnection = false, $data = [])
{
    http_response_code($code);
    echo json_encode(["status" => $code, "message" => $message, "data" => $data]);
    if ($finishConnection) {
        die();
    }
}

function getService($name = null)
{
    global $Router, $files, $services;
    $ruta = $name == null ? $Router->getRoute() : $name;
    require_once $files[$ruta];
    return new $services[$ruta];
}
