## Zend Expressive API Component

This library solves spesific problems. Not a general solution for APIs. There are helper classes, traits and filters.

Install with composer :

```
composer require hkulekci/expressive-api-component
```

Add the middleware to your config file :

```
    'middleware_pipeline' => [
        'always' => [
            'middleware' => [
                .....
            ],
            'priority' => 10000,
        ],
        'routing' => [
            'middleware' => [
                Zend\Expressive\Router\Middleware\RouteMiddleware::class,
                Zend\Expressive\Helper\UrlHelperMiddleware::class,
                ....
                ApiComponent\Helper\ApiMiddleware::class,
                ....
                Zend\Expressive\Router\Middleware\DispatchMiddleware::class,
                \Zend\Stratigility\Middleware\NotFoundHandler::class,
            ],
            'priority' => 1,
        ],
        'notFound' => [
            'middleware' => 'Application\NotFound',
            'priority'   => -1000,
        ]
    ],
```

Then use `ApiComponent\AbstractResource` class to extends for your API endpoint resource like below:

```
<?php
/**
 * @author      Haydar KULEKCI <haydarkulekci@gmail.com>
 */
namespace CoreApi\Common;

use ApiComponent\AbstractResource;
use ApiComponent\Helper\ApiResponse;
use ApiComponent\Helper\ApiResponseInterface;

class PingAction extends AbstractResource
{
    /**
     * Ping Service
     *
     * @SWG\Get(
     *     path="/ping",
     *     tags={"common"},
     *     @SWG\Response(response="200", description="Ping Service"),
     *     @SWG\Response(response="500", description="Internal Server Error"),
     * )
     *
     * @param array $data
     *
     * @return ApiResponseInterface
     */
    protected function fetchAll(array $data = []): ApiResponseInterface
    {
        return new ApiResponse(['service' => $data['service']]);
    }
}
```

For filtering your input create a route middleware and extend `ApiComponent\AbstractInputFilter` class like below:

```
<?php
/**
 * @author      Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace CoreApi\Common;

use ApiComponent\AbstractInputFilter;

class PingActionInputFilter extends AbstractInputFilter
{
    protected function inputFilterSpecsForFetchAllParameters(): array
    {
        return [
            $this->string('service', true, ['min' => 3, 'max' => 255]),
        ];
    }
}
```

Now, your should router config should be like below :

```
    [
        'name'            => 'coreapi.common.ping',
        'path'            => '/ping',
        'middleware'      => [
            \CoreApi\Common\PingActionInputFilter::class,
            \CoreApi\Common\PingAction::class,
        ],
        'allowed_methods' => ['GET'],
    ],
```
