<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class AbstractController implements ControllerInterface
{
    protected $container;

    final public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    final public function __invoke(Request $request, Response $response): Response
    {
        $response = $this->handle($request, $response);

        return $response;
    }

    /**
     * @param string[] $types
     * @return array<string, mixed>
     */
    protected static function getContentTypeHeaderDefinition(array $types): array
    {
        return [
            'name' => 'Content-Type',
            'description' => '',
            'jsonSchema' => [
                'type' => 'string',
                'enum' => $types,
            ],
            'required' => true,
        ];
    }
}
