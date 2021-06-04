<?php

declare(strict_types=1);

namespace App\RouteDefinition\RequestValidator;

use App\RouteDefinition\InvalidRouteDefinitionException;
use JsonSchema\Validator;
use Psr\Http\Message\RequestInterface;

class DefaultRequestValidator implements RequestValidatorInterface
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

    public function validate(array $routeDefinition, RequestInterface $request): Result
    {
        $result = new Result();

        if (!array_key_exists('headers', $routeDefinition['request'])) {
            throw new InvalidRouteDefinitionException('request headers definition missing');
        }

        foreach ($routeDefinition['request']['headers'] as $header) {
            $value = trim($request->getHeaderLine($header['name']));

            if ($value == '') {
                if ($header['required']) {
                    $result->addHeaderError([
                        'property' => $header['name'],
                        'message' => 'This header is required'
                    ]);
                }
            } else {
                $errors = [];
                foreach (explode(',', $value) as $item) {
                    // Fix to support headers like:
                    // `multipart\/form-data; boundary=------------------------xxxxx`, or
                    // `text/html; charset=UTF-8`
                    $item = trim(explode(';', $item)[0]);
                    $item = sprintf('"%s"', $item);

                    $errors = $this->getJsonSchemaValidationErrors($header['jsonSchema'], json_decode($item));
                    if (count($errors) === 0) {
                        break;
                    }
                }

                foreach ($errors as $error) {
                    $result->addHeaderError($error);
                }
            }
        }

        parse_str($request->getUri()->getQuery(), $queryParams);

        foreach ($routeDefinition['queryParams'] as $queryParam) {
            if (!isset($queryParams[$queryParam['name']])) {
                if ($queryParam['required'] == true) {
                    $result->addQueryError([
                        'property' => $queryParam['name'],
                        'message' => 'Query param is required',
                    ]);
                }
            } else {
                $queryParamValue = $queryParams[$queryParam['name']];

                switch ($queryParam['jsonSchema']['type']) {
                    case 'integer':
                    case 'number':
                        break;
                    case 'string':
                        $queryParamValue = sprintf("\"%s\"", $queryParamValue);

                        break;
                    case 'boolean':
                        $queryParamValue = $queryParamValue ? 'true' : 'false';

                        break;
                    default:
                        throw new \Exception(sprintf('query string type %s not covered', $queryParam['jsonSchema']['type']));
                }

                $errors = $this->getJsonSchemaValidationErrors($queryParam['jsonSchema'], json_decode($queryParamValue));
                foreach ($errors as $error) {
                    $result->addQueryError($error);
                }
            }
        }

        $requestContentDefinitionExists = false;
        $selectedRequestContentDefinition = null;

        $requestContentType = trim($request->getHeaderLine('Content-Type'));

        // Fix to support headers like:
        // `multipart\/form-data; boundary=------------------------xxxxx`, or
        // `text/html; charset=UTF-8`
        $requestContentType = trim(explode(';', $requestContentType)[0]);

        // Validate content type only when `Content-Type` header is available.
        if (!empty($requestContentType)) {
            foreach($routeDefinition['request']['content'] as $content) {
                if ($content['contentType'] === $requestContentType) {
                    $requestContentDefinitionExists = true;
                    $selectedRequestContentDefinition = $content;

                    break;
                }
            }

            if (!$requestContentDefinitionExists) {
                $msg = vsprintf('request definition not found for content type %s', [
                    $requestContentType,
                ]);

                throw new InvalidRouteDefinitionException($msg);
            }
        }

        if (!empty($requestContentType) && !$requestContentDefinitionExists) {
            throw new InvalidRouteDefinitionException('correct request content definition missing');
        }

        if (!empty($requestContentType) && array_key_exists('jsonSchema', $selectedRequestContentDefinition)) {
            $bodyStr = (string) $request->getBody();

            if ($bodyStr === '') {
                $result->addBodyError([
                    'property' => 'root',
                    'message' => 'body content not provided',
                ]);
            } else {
                $errors = $this->getJsonSchemaValidationErrors(
                    $selectedRequestContentDefinition['jsonSchema'],
                    json_decode($bodyStr)
                );

                foreach ($errors as $error) {
                    $result->addBodyError($error);
                }
            }
        }

        return $result;
    }
}
