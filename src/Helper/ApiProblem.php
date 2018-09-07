<?php
/**
 * ApiProblem Middleware
 *
 * @since     Dec 2015
 * @author    Haydar KULEKCI  <haydarkulekci@gmail.com>
 */
namespace ApiComponent\Helper;

use Zend\Diactoros\Response\JsonResponse;

/**
 * Object describing an API-Problem payload.
 */
class ApiProblem extends JsonResponse implements ApiResponseInterface
{
    /**
     * Content type for api problem response
     */
    public const CONTENT_TYPE = 'application/problem+json';

    /**
     * URL describing the problem type; defaults to HTTP status codes.
     *
     * @var string
     */
    protected $type = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html';

    /**
     * Constructor.
     *
     * Create an instance using the provided information. If nothing is
     * provided for the type field, the class default will be used;
     * if the status matches any known, the title field will be selected
     * from $problemStatusTitles as a result.
     *
     * @param string $detail
     * @param int    $status
     * @param array  $additional
     */
    public function __construct(string $detail, int $status, array $additional = [])
    {

        try {
            parent::__construct(
                array_merge(
                    [
                        'detail' => $detail,
                        'status' => $status,
                        'type'   => $this->type,
                    ],
                    $additional
                    ),
                $status
            );
        } catch (\InvalidArgumentException $e) {
            // TODO: do something to return a invalid argument exception
        }
    }


    /**
     * Create detail message from an exception.
     * @param \Throwable $exception
     * @return ApiProblem
     */
    public static function createDetailFromException(\Throwable $exception): ApiProblem
    {
        return new ApiProblem($exception->getMessage(), $exception->getCode(), ['exception' => $exception->getTraceAsString()]);
    }

    /**
     * Create HTTP status from an exception.
     *
     * @return int
     */
    protected function createStatusFromException(): int
    {
        $e      = $this->detail;
        $status = $e->getCode();

        if (!empty($status)) {
            return $status;
        }

        return 500;
    }

    public static function getTitleFromStatus($status): string
    {
        $problemStatusTitles = [
            // CLIENT ERROR
            400 => __('Bad Request'),
            401 => __('Unauthorized'),
            402 => __('Payment Required'),
            403 => __('Forbidden'),
            404 => __('Not Found'),
            405 => __('Method Not Allowed'),
            406 => __('Not Acceptable'),
            407 => __('Proxy Authentication Required'),
            408 => __('Request Time-out'),
            409 => __('Conflict'),
            410 => __('Gone'),
            411 => __('Length Required'),
            412 => __('Precondition Failed'),
            413 => __('Request Entity Too Large'),
            414 => __('Request-URI Too Large'),
            415 => __('Unsupported Media Type'),
            416 => __('Requested range not satisfiable'),
            417 => __('Expectation Failed'),
            418 => __('I\'m a teapot'),
            422 => __('Unprocessable Entity'),
            423 => __('Locked'),
            424 => __('Failed Dependency'),
            425 => __('Unordered Collection'),
            426 => __('Upgrade Required'),
            428 => __('Precondition Required'),
            429 => __('Too Many Requests'),
            431 => __('Request Header Fields Too Large'),
            // SERVER ERROR
            500 => __('Internal Server Error'),
            501 => __('Not Implemented'),
            502 => __('Bad Gateway'),
            503 => __('Service Unavailable'),
            504 => __('Gateway Time-out'),
            505 => __('HTTP Version not supported'),
            506 => __('Variant Also Negotiates'),
            507 => __('Insufficient Storage'),
            508 => __('Loop Detected'),
            511 => __('Network Authentication Required'),
        ];

        return $problemStatusTitles[$status] ?: '';
    }
}
