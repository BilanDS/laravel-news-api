<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Laravel News API",
    description: "Документація для тестового завдання REST API новин"
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Локальний сервер"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Введіть токен, отриманий при логіні"
)]
abstract class Controller
{
    //
}
