<?php

namespace App\Controllers\User;

use App\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Index extends AbstractController
{
    public static function getDefinition(): array
    {
        return [
            'title' => 'Get all Users',
            'description' => '',
            'tags' => ['Users'],
            'deprecated' => false,
            'enabled' => true,
            'method' => 'GET',
            'path' => '/users',
            'pathParams' => [],
            'queryParams' => [
                // [
                //     'name' => 'externalRef',
                //     'description' => '',
                //     'required' => false,
                //     'jsonSchema' => [
                //         'type' => 'string',
                //     ],
                // ],
            ],
            'request' => [
                'description' => '',
                'headers' => [],
                'content' => [],
                'examples' => [],
            ],
            'responses' => [
                [
                    'statusCode' => 200,
                    'description' => '',
                    'headers' => [
                        self::getContentTypeHeaderDefinition(['application/json']),
                    ],
                    'content' => [
                        [
                            'contentType' => 'application/json',
                            'jsonSchema' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => [
                                            'type' => 'string',
                                        ],
                                        'username' => [
                                            'type' => 'string',
                                        ],
                                        'email_address' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'required' => [
                                        'id',
                                        'username',
                                        'email_address',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'examples' => [],
                ],
            ],
        ];
    }

    public function handle(Request $request, Response $response): Response
    {
        $data = [
            [
                'id' => '1',
                'username' => 'Mike',
                'email_address' => 'mike@example.com',
            ],
            [
                'id' => '2',
                'username' => 'John',
                'email_address' => 'john@example.com',
            ],
        ];

        $response = $response->withJson($data, 200);

        return $response;
    }
}
