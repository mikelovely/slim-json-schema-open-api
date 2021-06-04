<?php

namespace App\Controllers\User;

use App\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Create extends AbstractController
{
    public static function getDefinition(): array
    {
        /** @var array $jsonSchema */
        $jsonSchema = json_decode(
            (string) file_get_contents(__DIR__ . '/../../../assets/json-schemas/create_user.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return [
            'title' => 'Create new User',
            'description' => '',
            'tags' => ['Users'],
            'deprecated' => false,
            'enabled' => true,
            'method' => 'POST',
            'path' => '/users',
            'pathParams' => [],
            'queryParams' => [],
            'request' => [
                'description' => '',
                'headers' => [
                    self::getContentTypeHeaderDefinition(['application/json']),
                ],
                'content' => [
                    [
                        'contentType' => 'application/json',
                        'jsonSchema' => $jsonSchema,
                    ],
                ],
                'examples' => [],
            ],
            'responses' => [
                [
                    'statusCode' => 201,
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
        $payload = json_decode(
            (string) $request->getBody(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        $data = [
            'id' => '1',
            'username' => $payload->username,
            'email_address' => $payload->email_address,
        ];

        $response = $response->withJson($data, 201);

        return $response;
    }
}
