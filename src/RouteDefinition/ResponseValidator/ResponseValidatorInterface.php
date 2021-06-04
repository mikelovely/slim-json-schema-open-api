<?php

declare(strict_types=1);

namespace App\RouteDefinition\ResponseValidator;

use App\RouteDefinition\InvalidRouteDefinitionException;
use Psr\Http\Message\ResponseInterface;

interface ResponseValidatorInterface
{
    /**
     * @param array<string, array<string, mixed>> $routeDefinition
     * @param ResponseInterface $response
     * @return Result
     * @throws \JsonException
     * @throws InvalidRouteDefinitionException
     */
    public function validate(array $routeDefinition, ResponseInterface $response): Result;
}
