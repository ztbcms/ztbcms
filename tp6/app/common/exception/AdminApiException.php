<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-18
 * Time: 10:40.
 */

namespace app\common\exception;

use Throwable;

/**
 * 管理后台api异常处理
 * Class AdminApiException
 * @package app\common\exception
 */
class AdminApiException extends \Exception
{
    protected $data = [];

    public function __construct(string $message = "", int $code = 0, $data = [], Throwable $previous = null)
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