<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\Kernel\Support;

use ArrayAccess;
use ArrayIterator;
use EasyWeChat\Kernel\Contracts\Arrayable;
use IteratorAggregate;

/**
 * Class ArrayAccessible.
 *
 * @author overtrue <i@overtrue.me>
 */
class ArrayAccessible implements ArrayAccess, IteratorAggregate, Arrayable
{
    private $array;

    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->array);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->array);
    }

    public function toArray()
    {
        return $this->array;
    }
}
