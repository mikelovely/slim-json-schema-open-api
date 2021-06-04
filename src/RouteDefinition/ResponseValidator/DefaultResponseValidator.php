<?php

declare(strict_types=1);

namespace App\RouteDefinition\ResponseValidator;

use App\RouteDefinition\InvalidRouteDefinitionException;
use JsonSchema\Validator;
use Psr\Http\Message\ResponseInterface;

class DefaultResponseValidator implements ResponseValidatorInterface
{
    /**
     * @param array<string,mixed> $jsonSchema
     * @param mixed $data
     * @return array<int, array<string, string>>
     */
    private function getJsonSchemaValidationErrors(array $jsonSchema, $data): array
    {
        $jsonSchemaValidator = new Validator();

        $jsonSchemaValidator->validate($data, $jsonSchema);

        if ($jsonSchemaValidator->isValid()) {
            return [];
        }

        $errors = [];
        foreach ($jsonSchemaValidator->getErrors() as $error) {
            if (empty($error['property'])) {
                $errors[] = [
                    'message' => $error['message'],
                ];
            } else {
                $errors[] = [
                    'property' => $error['property'],
                    'message' => $error['message'],
                ];
            }
        }

        return $errors;
    }

    public function validate(array $routeDefinition, ResponseInterface $response): Result
    {
        $result = new Result();

        $responseContentDefinitionExists = false;

        $selectedResponseHeadersDefinition = [];
        $selectedResponseContentDefinition = null;

        $responseContentType = trim($response->getHeaderLine('Content-Type'));

        // Validate content type only when `Content-Type` header is available.
        if (!empty($responseContentType)) {
            foreach ($routeDefinition['responses'] as $responseDef) {
                if ($responseDef['statusCode'] === $response->getStatusCode()) {
                    foreach($responseDef['content'] as $content) {
                        if ($content['contentType'] === $responseContentType) {
                            $responseContentDefinitionExists = true;
                            $selectedResponseHeadersDefinition = $responseDef['headers'];
                            $selectedResponseContentDefinition = $content;

                            break;
                        }
                    }

                    break;
                }
            }

            if (!$responseContentDefinitionExists) {
                $msg = vsprintf('response definition not found for status code %s and content type %s', [
                    $response->getStatusCode(),
                    $responseContentType,
                ]);

                throw new InvalidRouteDefinitionException($msg);
            }
        }

        // Validate provided headers against the defined headers.
        foreach ($selectedResponseHeadersDefinition as $headerDef) {
            $headerValue = trim($response->getHeaderLine($headerDef['name']));
            if ($headerValue == '') {
                if ($headerDef['required'] == true) {
                    $result->addHeaderError([
                        'property' => $headerDef['name'],
                        'message' => 'This header is required',
                    ]);
                }

                continue;
            }

            $jsonValue = sprintf("\"%s\"", $headerValue);

            $errors = $this->getJsonSchemaValidationErrors($headerDef['jsonSchema'], json_decode($jsonValue));
            foreach ($errors as $error) {
                $result->addHeaderError($error);
            }
        }

        if (!empty($responseContentType) && !$responseContentDefinitionExists) {
            throw new InvalidRouteDefinitionException('correct response content definition missing');
        }

        // Response `Content-Type` is empty, thus no definition for the response can be found.
        if (!empty($responseContentType) && array_key_exists('jsonSchema', $selectedResponseContentDefinition)) {
            $value = (string) $response->getBody();

            if ($responseContentType === 'application/json') {
                $value = json_decode($value);
            }

            $errors = $this->getJsonSchemaValidationErrors(
                $selectedResponseContentDefinition['jsonSchema'],
                $value
            );

            foreach ($errors as $error) {
                $result->addBodyError($error);
            }
        }

        return $result;
    }
}
