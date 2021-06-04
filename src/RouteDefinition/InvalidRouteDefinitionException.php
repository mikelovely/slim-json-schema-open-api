<?php

declare(strict_types=1);

namespace App\RouteDefinition;

class InvalidRouteDefinitionException extends \Exception
{
    /**
     * @var array<int|string,mixed>
     */
    private array $errors = [];

    /**
     * @param array<int|string,mixed> $errors
     * @return $this
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return array<int|string,mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
