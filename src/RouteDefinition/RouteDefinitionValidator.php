<?php

declare(strict_types=1);

namespace App\RouteDefinition;

use JsonSchema\Validator;

class RouteDefinitionValidator
{
    /**
     * @param array<string, array<string, mixed>> $route
     * @throws InvalidRouteDefinitionException
     * @throws MissingRouteDefinitionSchemaFileException
     * @throws \JsonException
     */
    public function validate(array $route): void
    {
        $jsonSchemaValidator = new Validator();

        $fileContents = file_get_contents(__DIR__ . '/routedef-json-schema.json');
        if ($fileContents === false) {
            throw new MissingRouteDefinitionSchemaFileException('missing json schema file');
        }

        $jsonSchema = json_decode($fileContents);
        if ($jsonSchema === false) {
            throw new \JsonException("invalid route definition json used in `jsonSchema`");
        }

        $value = json_encode($route);
        if ($value === false) {
            throw new InvalidRouteDefinitionException('invalid route definition');
        }

        try {
            $value = json_decode($value, false, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidRouteDefinitionException('invalid route definition');
        }

        $jsonSchemaValidator->validate($value, $jsonSchema);
        if ($jsonSchemaValidator->isValid()) {
            return;
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

        throw (new InvalidRouteDefinitionException('invalid route definition'))->setErrors($errors);
    }
}
