<?php
/**
 * Api Middleware
 *
 * @since     Dec 2015
 * @author    Haydar KULEKCI  <haydarkulekci@gmail.com>
 */
namespace ApiComponent\Helper;

use ApiComponent\AbstractResource;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

/**
 * This class attach rest_api_method attribute to request
 *
 * @package Api\Helper
 */
class ApiMiddleware implements MiddlewareInterface
{
    public const REST_RESOURCE_ATTR_NAME = 'rest_api_name';
    public const REST_METHOD_ATTR_NAME   = 'rest_api_method_name';

    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method     = $request->getMethod();
        $attributes = $request->getAttributes();
        $methodName = null;

        if ($method === 'GET') {
            if (isset($attributes['id'])) {
                $methodName = AbstractResource::METHOD_FETCH;
            } else {
                $methodName = AbstractResource::METHOD_FETCH_ALL;
            }
        } elseif ($method === 'POST') {
            $methodName = AbstractResource::METHOD_CREATE;
        } elseif ($method === 'PUT') {
            if (isset($attributes['id'])) {
                $methodName = AbstractResource::METHOD_UPDATE;
            }
        } elseif ($method === 'PATCH') {
            if (isset($attributes['id'])) {
                $methodName = AbstractResource::METHOD_PATCH;
            }
        } elseif ($method === 'DELETE') {
            if (isset($attributes['id'])) {
                $methodName = AbstractResource::METHOD_DELETE;
            }
        }

        $routeResult = $request->getAttribute(RouteResult::class, false);
        if ($routeResult) {
            /**
             * @var RouteResult $routeResult
             */
            $middleware = $routeResult->getMatchedRouteName();
            $request    = $request->withAttribute(self::REST_RESOURCE_ATTR_NAME, $middleware . '.' . $methodName)
                ->withAttribute(self::REST_METHOD_ATTR_NAME, $methodName);
        }

        return $handler->handle($request)
            ->withHeader('content-type', 'application/json');
    }
}
