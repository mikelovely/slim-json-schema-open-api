<?php

declare(strict_types=1);

namespace App\RouteDefinition\RequestValidator;

use App\RouteDefinition\InvalidRouteDefinitionException;
use Psr\Http\Message\RequestInterface;

interface RequestValidatorInterface
{
    /**
     * @param array<string, array<string, mixed>> $routeDefinition
     * @param RequestInterface $request
     * @return Result
     * @throws InvalidRouteDefinitionException
     * @throws \JsonException
     */
    public function validate(array $routeDefinition, RequestInterface $request): Result;
}
