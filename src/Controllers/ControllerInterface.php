<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface ControllerInterface
{
    public static function getDefinition(): array;

    public function handle(Request $request, Response $response): Response;
}
