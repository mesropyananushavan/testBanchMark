<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Laravel Template API',
    version: '1.0.0',
    description: 'API documentation for manual testing via Swagger UI'
)]
#[OA\Server(
    url: '/',
    description: 'Current environment base URL (resolved by host where Swagger UI is opened)'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Use: Bearer {token}. Works for Sanctum/JWT-style bearer tokens.'
)]
final class OpenApiSpec
{
}
