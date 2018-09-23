<?php
/**
 * Abstract Resource
 *
 * @since     Dec 2015
 * @author    Haydar KULEKCI  <haydarkulekci@gmail.com>
 */
namespace ApiComponent;

use ApiComponent\Helper\ApiMiddleware;
use ApiComponent\Helper\ApiProblem;
use ApiComponent\Helper\ApiResponseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractResource implements RequestHandlerInterface
{
    public const METHOD_FETCH     = 'fetch';
    public const METHOD_FETCH_ALL = 'fetchAll';
    public const METHOD_CREATE    = 'create';
    public const METHOD_DELETE    = 'delete';
    public const METHOD_UPDATE    = 'update';
    public const METHOD_PATCH     = 'patch';

    use AuthInfoTrait;

    protected $pathIdentifiers = [];

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method      = $request->getAttribute(ApiMiddleware::REST_METHOD_ATTR_NAME);
        $attributes  = $this->pathIdentifiers = $request->getAttributes();
        $data        = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];
        $this->buildAuthenticateDataFromRequest($request);

        if ($method === self::METHOD_FETCH) {
            return ResponseBuilder::build($this->fetch($attributes['id']));
        }

        if ($method === self::METHOD_FETCH_ALL) {
            return ResponseBuilder::build($this->fetchAll($queryParams));
        }

        if ($method === self::METHOD_CREATE) {
            return ResponseBuilder::build($this->create($data));
        }

        if ($method === self::METHOD_UPDATE) {
            return ResponseBuilder::build($this->update($attributes['id'], $data));
        }

        if ($method === self::METHOD_PATCH) {
            return ResponseBuilder::build($this->patch($attributes['id'], $data));
        }

        if ($method === self::METHOD_DELETE) {
            return ResponseBuilder::build($this->delete($attributes['id']));
        }

        return ResponseBuilder::build($this->methodNotImplementedResponse());
    }

    /**
     * @param array $data
     * @return ApiResponseInterface
     */
    protected function create(array $data = []): ApiResponseInterface
    {
        return $this->methodNotImplementedResponse();
    }

    /**
     * @param $id
     * @return ApiResponseInterface
     */
    protected function fetch($id): ApiResponseInterface
    {
        return $this->methodNotImplementedResponse();
    }

    /**
     * @param array $data
     * @return ApiResponseInterface
     */
    protected function fetchAll(array $data = []): ApiResponseInterface
    {
        return $this->methodNotImplementedResponse();
    }

    /**
     * @param $id
     * @param array $data
     * @return ApiResponseInterface
     */
    protected function patch($id, array $data = []): ApiResponseInterface
    {
        return $this->methodNotImplementedResponse();
    }

    /**
     * @param $id
     * @param array $data
     * @return ApiResponseInterface
     */
    protected function update($id, array $data = []): ApiResponseInterface
    {
        return $this->methodNotImplementedResponse();
    }

    /**
     * @param $id
     * @return ApiResponseInterface
     */
    protected function delete($id): ApiResponseInterface
    {
        return $this->methodNotImplementedResponse();
    }

    private function methodNotImplementedResponse(): ApiProblem
    {
        return new ApiProblem(__('Method not implemented!'), 405);
    }
}
