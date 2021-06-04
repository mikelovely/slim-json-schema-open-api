<?php

declare(strict_types=1);

namespace App\RouteDefinition\Specification;

interface GeneratorInterface
{
    /**
     * @param string $title
     * @param string $description
     * @param string $version
     * @param string $host
     * @param string $protocol
     * @param array[] $routes An array of route class names
     * @return array<string,mixed> The assoc array version of the specification
     */
    public function generate(
        string $title,
        string $description,
        string $version,
        string $host,
        string $protocol,
        array $routes
    ): array;
}
