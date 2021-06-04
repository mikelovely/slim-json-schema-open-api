<?php

require 'vendor/autoload.php';

use App\Controllers\User\Create;
use App\Middleware\EventMiddleware;
use App\RouteDefinition\RequestValidator\DefaultRequestValidator;
use App\RouteDefinition\ResponseValidator\DefaultResponseValidator;
use Psr\Container\ContainerInterface;
use Slim\App as SlimApplication;
use Slim\Container;
use Slim\Http\StatusCode;

$container = new Container([
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
    ],
]);

$app = new SlimApplication($container);

$container['RequestValidator'] = function ($container) {
    return new DefaultRequestValidator();
};

$container['ResponseValidator'] = function ($container) {
    return new DefaultResponseValidator();
};

// $container['errorHandler'] = function ($container) {
//     return function($request, $response, $exception) {
//         return $response->withJson(
//             [
//                 'error' => true,
//                 'code' => 400001,
//                 'message' => $exception->getMessage(),
//             ],
//             StatusCode::HTTP_BAD_REQUEST
//         );
//     };
// };

// $container['foundHandler'] = function() {
//     return new \Slim\Handlers\Strategies\RequestResponseArgs();
// };

// Application middleware
$app->add(new EventMiddleware($app->getContainer()));

// Routes
$app->get('/users', \App\Controllers\User\Index::class);
$app->get('/users/{userId}', \App\Controllers\User\Get::class);
$app->post('/users', \App\Controllers\User\Create::class);

return $app;
