<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\JsonResponse;

// Example function
function getMessage()
{
    return [
        "status" => "success",
        "message" => "Hello from Core PHP API with Composer!",
        "time" => date("Y-m-d H:i:s")
    ];
}

// Create JSON response
$response = new JsonResponse(getMessage());

// Send it
$response->send();

