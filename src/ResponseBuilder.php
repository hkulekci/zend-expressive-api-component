<?php
/**
 * @since     Mar 2018
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace ApiComponent;

use ApiComponent\Helper\ApiProblem;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

class ResponseBuilder
{
    /**
     * @param mixed $result
     *
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public static function build($result): ResponseInterface
    {
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        if (\is_object($result) && method_exists($result, 'toArray')) {
            return new JsonResponse($result->toArray());
        }

        if ($result instanceof \Traversable) {
            return new JsonResponse((array)$result);
        }

        if (\is_array($result)) {
            return new JsonResponse($result);
        }

        return new ApiProblem(__('Bad Gateway'), 502);
    }
}
