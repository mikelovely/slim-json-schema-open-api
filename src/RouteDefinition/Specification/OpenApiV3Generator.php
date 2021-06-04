<?php

declare(strict_types=1);

namespace App\RouteDefinition\Specification;

use App\RouteDefinition\InvalidRouteDefinitionException;
use App\RouteDefinition\RouteDefinitionValidator;

class OpenApiV3Generator implements GeneratorInterface
{
    private RouteDefinitionValidator $routeDefinitionValidator;

    public function __construct()
    {
        $this->routeDefinitionValidator = new RouteDefinitionValidator();
    }

    /**
     * @param string $title
     * @param string $description
     * @param string $version
     * @param string $host
     * @param string $protocol
     * @param array<int,mixed> $routes
     * @return array<string,mixed>
     * @throws InvalidRouteDefinitionException
     * @throws \JsonException
     */
    public function generate(
        string $title,
        string $description,
        string $version,
        string $host,
        string $protocol,
        array $routes
    ): array {
        $paths = [];

        $structure = $this->getBase($title, $description, $host, $protocol, $version);

        foreach ($routes as $route) {
            $this->routeDefinitionValidator->validate($route);

            if (!$route['enabled']) {
                continue;
            }

            $path = [
                'summary' => $route['title'],
                'description' => $route['description'],
                'tags' => $route['tags'],
            ];

            if ($route['deprecated']) {
                $path['deprecated'] = true;
            }

            $parameters = [];

            foreach ($route['pathParams'] as $pathParam) {
                $this->sanitizeJsonSchema($pathParam['jsonSchema']);

                $parameters[] = [
                    'name' => $pathParam['name'],
                    'in' => 'path',
                    'required' => $pathParam['required'],
                    'description' => $pathParam['description'],
                    'schema' => $pathParam['jsonSchema'],
                    'deprecated' => isset($pathParam['deprecated']) ? $pathParam['deprecated']: false,
                ];
            }

            foreach ($route['queryParams'] as $queryParam) {
                $this->sanitizeJsonSchema($queryParam['jsonSchema']);

                $parameters[] = [
                    'name' => $queryParam['name'],
                    'in' => 'query',
                    'required' => $queryParam['required'],
                    'description' => $queryParam['description'],
                    'schema' => $queryParam['jsonSchema'],
                    'deprecated' => isset($queryParam['deprecated']) ? $queryParam['deprecated']: false,
                ];
            }

            if (isset($route['request'])) {
                foreach ($route['request']['headers'] as $header) {
                    $this->sanitizeJsonSchema($header['jsonSchema']);

                    $parameters[] = [
                        'name' => $header['name'],
                        'in' => 'header',
                        'required' => $header['required'],
                        'description' => $header['description'],
                        'schema' => $header['jsonSchema'],
                    ];
                }

                if (count($route['request']['content']) > 0) {
                    $requestBodyContent = [];

                    foreach ($route['request']['content'] as $content) {
                        if ($content['contentType'] && $content['jsonSchema']) {
                            $this->sanitizeJsonSchema($content['jsonSchema']);

                            $requestBodyContent[$content['contentType']] = [
                                'schema' => $content['jsonSchema'],
                            ];
                        }
                    }

                    $path['requestBody'] = [
                        'required' => true,
                        'content' => $requestBodyContent,
                    ];

                    if (isset($route['request']['examples'])) {
                        foreach ($route['request']['examples'] as $example) {
                            $path['requestBody']['content'][$route['request']['contentType']]['examples'][$example['name']] = [
                                'summary' => $example['name'],
                                'value' => $example['value'],
                            ];
                        }
                    }
                }
            }

            $responses = [];

            foreach ($route['responses'] as $response) {
                $responseResult = [
                    'description' => $response['description'],
                ];

                // adding response headers
                $headers = [];
                foreach ($response['headers'] as $h) {
                    $headers[$h['name']] = [
                        'schema' => $h['jsonSchema'],
                        'description' => $h['description'],
                    ];
                }

                if (count($headers) > 0) {
                    $responseResult['headers'] = $headers;
                }

                if ($response['content']) {
                    $responseResult['content'] = [];

                    foreach ($response['content'] as $content) {
                        $this->sanitizeJsonSchema($content['jsonSchema']);

                        $responseResult['content'][$content['contentType']] = [
                            'schema' => $content['jsonSchema'],
                        ];
                    }
                }

                $responses[(string) $response['statusCode']] = $responseResult;
            }

            $path['responses'] = $responses;

            if (count($parameters) > 0) {
                $path['parameters'] = $parameters;
            }

            $paths[$route['path']][strtolower($route['method'])] = $path;
        }

        $structure['paths'] = $paths;

        return $structure;
    }

    /**
     * @param string $title
     * @param string $description
     * @param string $host
     * @param string $protocol
     * @param string $version
     * @return array<string,mixed>
     */
    private function getBase(string $title, string $description, string $host, string $protocol, string $version): array
    {
        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => $title,
                'description' => $description,
                'version' => $version,
            ],
            'servers' => [
                // Due to cors issues, we do not really need it, left for the future reference.
                //[
                //    'url' => '{protocol}://{host}:{port}{basePath}',
                //   'variables' => [
                //      'host' => [
                //          'default' => $host,
                //      ],
                //      'port' => [
                //          'default' =>  '',
                //      ],
                //      'basePath' =>  [
                //          'default' => '',
                //      ],
                //      'protocol' =>  [
                //          'default' => $protocol,
                //      ],
                //  ]
                //],
            ],
            'tags' => [],
            'paths' => [],
        ];
    }

    /**
     * @param array<string,mixed> $jsonSchema
     */
    private function sanitizeJsonSchema(array &$jsonSchema): void
    {
        if ($jsonSchema == null) {
            return;
        }

        if (!isset($jsonSchema['type'])) {
            return;
        }

        if ($jsonSchema['type'] == 'null') {
            $jsonSchema['nullable'] = true;
            $jsonSchema['type'] = 'string';
        }

        if (is_array($jsonSchema['type']) && in_array('null', $jsonSchema['type'])) {
            $jsonSchema['nullable'] = true;
            unset($jsonSchema['type'][array_search('null', $jsonSchema['type'])]);
        }

        if (is_array($jsonSchema['type']) && count($jsonSchema['type']) == 1) {
            $jsonSchema['type'] = array_pop($jsonSchema['type']);
        }

        if ($jsonSchema['type'] == 'array') {
            $this->sanitizeJsonSchema($jsonSchema['items']);
        }

        if ($jsonSchema['type'] == 'object') {
            if (isset($jsonSchema['properties'])) {
                foreach ($jsonSchema['properties'] as &$property) {
                    $this->sanitizeJsonSchema($property);
                }
            }
        }
    }
}
