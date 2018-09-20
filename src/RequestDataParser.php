<?php
/**
 * @since     Mar 2018
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace ApiComponent;


use ApiComponent\Helper\ApiProblem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Json\Json;

class RequestDataParser implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $headerLine = $request->getHeaderLine('content-type');
        $headerLineParts = preg_split('/\s*[;,]\s*/', $headerLine);
        $contentType = strtolower($headerLineParts[0]);
        $data = $request->getParsedBody();

        if ($contentType === 'application/json') {
            if (empty($data)) {
                try {
                    $data = Json::decode($request->getBody()->getContents(), Json::TYPE_ARRAY);
                } catch (\Exception $e) {
                    return new ApiProblem('Data Parsing Error.', 400);
                }
            }

            return $handler->handle($request->withParsedBody($data));
        }

        if ($contentType === 'application/x-www-form-urlencoded') {
            $data = [];
            parse_str($request->getBody()->getContents(), $data);

            return $handler->handle($request->withParsedBody($data));
        }

        return $handler->handle($request);
    }
}
