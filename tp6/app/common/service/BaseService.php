<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-08
 * Time: 09:18.
 */

namespace app\common\service;


class BaseService
{
    protected $error;

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error): void
    {
        $this->error = $error;
    }
}