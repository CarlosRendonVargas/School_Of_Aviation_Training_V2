<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\Http;

$baseUrl = 'http://localhost:8000/api/v1';

echo "Testing login...\n";
$response = Http::post("$baseUrl/auth/login", [
    'email' => 'admin@escuelaviacion.co',
    'password' => 'Rac141*2025'
]);

if ($response->failed()) {
    echo "Login failed: " . $response->body() . "\n";
    exit(1);
}

$token = $response->json()['token'];
echo "Token obtained!\n";

$endpoints = [
    '/programas',
    '/estudiantes',
    '/vuelos',
    '/aeronaves',
    '/instructores'
];

foreach ($endpoints as $ep) {
    echo "Testing $ep... ";
    $res = Http::withToken($token)->get("$baseUrl$ep");
    if ($res->ok()) {
        $count = count($res->json()['data'] ?? []);
        echo "OK ($count items)\n";
    } else {
        echo "FAILED: " . $res->status() . " - " . $res->body() . "\n";
    }
}
