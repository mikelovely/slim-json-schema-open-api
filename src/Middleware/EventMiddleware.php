<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Controllers\ControllerInterface;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventMiddleware
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $controller = $request->getAttributes()['route']->getCallable();

        var_dump(new $controller());
        exit;

        // if ($controller instanceof ControllerInterface) {

        // var_dump("hell");
        // exit;
            $routeDefinition = $controller::getDefinition();

            $result = $this->container->get('RequestValidator')->validate(
                $routeDefinition,
                $request
            );

            if (false === $result->isValid()) {
                throw new Exception('Request not valid');
            }

            $response = $next($request, $response);

            $result = $this->container->get('ResponseValidator')->validate(
                $routeDefinition,
                $response
            );

            if ($result->isValid() === false) {
                throw new Exception('Response not valid');
            }
        // }

        return $response;
    }
}
