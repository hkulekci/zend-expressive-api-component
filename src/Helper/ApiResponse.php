<?php
/**
 * Api Response
 *
 * @since     Sep 2016
 * @author    Haydar KULEKCI  <haydarkulekci@gmail.com>
 */
namespace ApiComponent\Helper;

use InvalidArgumentException;
use Zend\Diactoros\MessageTrait;
use Zend\Diactoros\Stream;

/**
 * Object describing an API-Response payload.
 */
class ApiResponse implements ApiResponseInterface
{
    use MessageTrait;

    public const MIN_STATUS_CODE_VALUE = 100;
    public const MAX_STATUS_CODE_VALUE = 599;

    /**
     * Map of standard HTTP status code/reason phrases
     *
     * @var array
     */
    private $phrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated to 306 => '(Unused)'
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        // SERVER ERROR
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * @var string
     */
    private $reasonPhrase = '';

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private $exact;

    /**
     * @var array
     */
    private $meta = [];

    public function __construct(array $data, int $status = 200, $exact = false)
    {
        $this->data = $data;
        $this->statusCode = $status;
        $this->exact = $exact;
        $this->reCreateBody();
    }

    public function setPaginationInfo(int $totalSize, int $page, int $pageSize): self
    {
        $this->meta['pagination'] = (new ApiPaginationResponse($totalSize, $page, $pageSize))->toArray();
        $this->reCreateBody();

        return $this;
    }

    public function setMeta(array $meta, bool $force = false): self
    {
        if ($force) {
            $this->meta = [];
        }
        $this->meta = array_merge($this->meta, $meta);

        $this->reCreateBody();

        return $this;
    }

    private function reCreateBody(): void
    {
        if ($this->exact) {
            $bodyArray = $this->data;
        } else {
            $bodyArray = [
                'result' => true,
                'status' => $this->getStatusCode(),
                'data' => $this->data,
            ];
        }
        if ($this->meta) {
            $bodyArray['meta'] = $this->meta;
        }

        $body = new Stream('php://temp', 'wb+');
        $body->write(json_encode($bodyArray));
        $body->rewind();
        $this->stream = $body;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = ''): ApiResponseInterface
    {
        $new = clone $this;
        $new->setStatusCode($code, $reasonPhrase);
        return $new;
    }

    /**
     * Set a valid status code.
     *
     * @param int $code
     * @param string $reasonPhrase
     * @throws InvalidArgumentException on an invalid status code.
     */
    private function setStatusCode(int $code, string $reasonPhrase = ''): void
    {
        if ($code < static::MIN_STATUS_CODE_VALUE
            || $code > static::MAX_STATUS_CODE_VALUE
        ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between %d and %d, inclusive',
                is_scalar($code) ? $code : \gettype($code),
                static::MIN_STATUS_CODE_VALUE,
                static::MAX_STATUS_CODE_VALUE
            ));
        }

        if ($reasonPhrase === '' && isset($this->phrases[$code])) {
            $reasonPhrase = $this->phrases[$code];
        }

        $this->reasonPhrase = $reasonPhrase;
        $this->statusCode = $code;
    }
}
