<?php
/**
 * @since     Mar 2018
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace ApiComponent;


use ApiComponent\Helper\ApiProblem;
use Psr\Http\Message\RequestInterface;
use Zend\Json\Json;

class RequestDataParser
{
    protected $data = [];

    /**
     * RequestDataParser constructor.
     * @param RequestInterface $request
     * @throws \RuntimeException
     */
    public function __construct(RequestInterface $request)
    {
        $body = $request->getParsedBody();

        if (!empty($body)) {
            $this->data = $body;
        }

        $contentType = $request->getHeaderLine('content-type');
        if (empty($contentType)) {
            $contentType = 'application/json';
        }

        $this->data = $this->parseRequestData(
            $request->getBody()->getContents(),
            $contentType
        );
    }

    public function getData(array $default = []): array
    {
        return $this->data ?: $default;
    }

    /**
     * @param string $input
     * @param string $contentType
     *
     * @return array
     */
    protected function parseRequestData($input, $contentType)
    {
        $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
        $parser           = $this->returnParserContentType($contentTypeParts[0]);

        return $parser($input);
    }

    /**
     * @param string $contentType
     *
     * @return \Closure
     */
    protected function returnParserContentType($contentType): callable
    {
        if ($contentType === 'application/x-www-form-urlencoded') {
            return function ($input) {
                parse_str($input, $data);

                return $data;
            };
        }

        if ($contentType === 'application/json') {
            return function ($input) {
                if (!$input) {
                    return null;
                }
                try {
                    return Json::decode($input, Json::TYPE_ARRAY);
                } catch (\Exception $e) {
                    return new ApiProblem('Data Parsing Error.', 400);
                }
            };
        }

        if ($contentType === 'multipart/form-data') {
            return function ($input) {
                return $input;
            };
        }

        if ($contentType === 'text/plain') {
            return function ($input) {
                return $input;
            };
        }

        return function ($input) {
            return $input;
        };
    }
}
