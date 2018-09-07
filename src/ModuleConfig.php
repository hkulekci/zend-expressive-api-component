<?php
namespace ApiComponent;

use ApiComponent\Helper\ApiHeader;
use ApiComponent\Helper\ApiMiddleware;
use ApiComponent\Helper\ApiMiddlewareFactory;

/**
 * Api Module
 */
class ModuleConfig
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    ApiMiddleware::class => ApiMiddlewareFactory::class
                ],
            ],
        ];
    }
}
