<?php
namespace app\common\exception;

use Throwable;

/**
 * 前端API处理
 * Class BaseApiException
 * @package app\common\exception
 */
class BaseApiException extends \Exception
{
    protected $data = [];

    public function __construct($message = "", int $code = 400, $data = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setData($data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

}