<?php

namespace App\Controllers\User;

use App\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Get extends AbstractController
{
    public static function getDefinition(): array
    {
        return [
            'title' => 'Get one User',
            'description' => '',
            'tags' => ['Users'],
            'deprecated' => false,
            'enabled' => true,
            'method' => 'GET',
            'path' => '/users/userId',
            'pathParams' => [
                [
                    'name' => 'userId',
                    'description' => '',
                    'required' => true,
                    'jsonSchema' => [
                        'type' => 'string',
                    ],
                    'regex' => '^[a-zA-Z0-9-]+$',
                ],
            ],
            'queryParams' => [],
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
                    'examples' => [],
                ],
            ],
        ];
    }

    public function handle(Request $request, Response $response): Response
    {
        $data = [
            'id' => '1',
            'username' => 'Mike',
            'email_address' => 'mike@example.com',
        ];

        $response = $response->withJson($data, 200);

        return $response;
    }
}
