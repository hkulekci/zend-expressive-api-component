<?php
/**
 * ApiProblem Middleware
 *
 * @since     Dec 2015
 * @author    Haydar KULEKCI  <haydarkulekci@gmail.com>
 */
namespace ApiComponent\Helper;

/**
 * Object describing an API-Problem payload.
 */
class ApiPaginationResponse implements \JsonSerializable
{
    protected $totalSize;
    protected $page;
    protected $pageSize;

    public function __construct($totalSize, $page, $pageSize)
    {
        $this->page      = $page;
        $this->totalSize = $totalSize;
        $this->pageSize  = $pageSize;
    }

    public function toArray(): array
    {
        return [
            'page'      => $this->page,
            'totalSize' => $this->totalSize,
            'pageSize'  => $this->pageSize,
        ];
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *        which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
