<?php
/**
 *
 * @since     Mar 2018
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */
namespace ApiComponent;

use ApiComponent\Helper\ApiMiddleware;
use ApiComponent\Helper\ApiProblem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractInputFilter implements MiddlewareInterface
{
    public const ATTRIBUTE_NAME = 'input-filter-result';
    use BaseInputFilterTrait;
    use InputFilterHelperTrait;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Zend\InputFilter\Exception\RuntimeException
     * @throws \Zend\InputFilter\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method  = $request->getAttribute(ApiMiddleware::REST_METHOD_ATTR_NAME);
        $params  = $request->getQueryParams();
        $rawData = (new RequestDataParser($request))->getData();

        if ($method === AbstractResource::METHOD_FETCH_ALL) {
            $inputFilter = $this->buildInputFilter($this->inputFilterSpecsForFetchAllParameters());
            $inputFilter->setData($params);
            if (!$inputFilter->isValid()) {
                return new ApiProblem('Input filter Error!', 400, ['queryParamsMessages' => $inputFilter->getMessages()]);
            }
            $request = $request->withQueryParams($inputFilter->getValues());
        }

        if ($method === AbstractResource::METHOD_CREATE) {
            $inputFilter = $this->buildInputFilter($this->inputFilterSpecsForCreateParameters());
            $inputFilter->setData($rawData);
            if (!$inputFilter->isValid()) {
                return new ApiProblem('Input filter Error!', 400, ['createParamsMessages' => $inputFilter->getMessages()]);
            }
            $request = $request->withParsedBody($inputFilter->getValues());
        }

        if ($method === AbstractResource::METHOD_UPDATE) {
            $inputFilter = $this->buildInputFilter($this->inputFilterSpecsForUpdateParameters());
            $inputFilter->setData($rawData);
            if (!$inputFilter->isValid()) {
                return new ApiProblem('Input filter Error!', 400, ['updateParamsMessages' => $inputFilter->getMessages()]);
            }
            $request = $request->withParsedBody($inputFilter->getValues());
        }

        return $handler->handle($request);
    }
}
