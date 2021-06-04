<?php

declare(strict_types=1);

namespace App\RouteDefinition\RequestValidator;

class Result
{
    /**
     * @var array<int,array<string,string>>
     */
    private array $headerErrors;

    /**
     * @var array<int,array<string,string>>
     */
    private array $queryErrors;

    /**
     * @var array<int,array<string,string>>
     */
    private array $pathErrors;

    /**
     * @var array<int,array<string,string>>
     */
    private array $bodyErrors;

    public function __construct()
    {
        $this->headerErrors = [];
        $this->queryErrors = [];
        $this->pathErrors = [];
        $this->bodyErrors = [];
    }

    public function isValid(): bool
    {
        return count($this->headerErrors) +
            count($this->queryErrors) +
            count($this->pathErrors) +
            count($this->bodyErrors) == 0;
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
    public function addQueryError(array $error): self
    {
        $this->queryErrors[] = $error;

        return $this;
    }

    /**
     * @return array<int,array<string,string>>
     */
    public function getQueryErrors(): array
    {
        return $this->queryErrors;
    }

    /**
     * @param array<string,string> $error
     * @return self
     */
    public function addPathError(array $error): self
    {
        $this->pathErrors[] = $error;

        return $this;
    }

    /**
     * @return array<int,array<string,string>>
     */
    public function getPathErrors(): array
    {
        return $this->pathErrors;
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
