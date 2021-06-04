<?php

declare(strict_types=1);

namespace App\RouteDefinition\ResponseValidator;

class Result
{
    /**
     * @var array<int,array<string,string>>
     */
    private array $headerErrors;

    /**
     * @var array<int,array<string,string>>
     */
    private array $bodyErrors;

    public function __construct()
    {
        $this->headerErrors = [];
        $this->bodyErrors = [];
    }

    public function isValid(): bool
    {
        return count($this->headerErrors) + count($this->bodyErrors) == 0;
    }

    /**
     * @param array<string,string> $error
     * @return self
     */
    public function addHeaderError(array $error): self
    {
        $this->headerErrors[] = $error;

        return $this;
    }

    /**
     * @return array<int,array<string,string>>
     */
    public function getHeaderErrors(): array
    {
        return $this->headerErrors;
    }

    /**
     * @param array<string,string> $error
     * @return self
     */
    public function addBodyError(array $error): self
    {
        $this->bodyErrors[] = $error;

        return $this;
    }

    /**
     * @return array<int,array<string,string>>
     */
    public function getBodyErrors(): array
    {
        return $this->bodyErrors;
    }
}
