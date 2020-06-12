<?php
/**
 * User: jayinton
 * Date: 2020/5/25
 * Time: 23:22
 */

namespace Common\Traits;


trait SerializesObjectTrait
{
    /**
     * 序列化
     * @return array
     * @throws \ReflectionException
     */
    function toSerialize()
    {
        $reflectionClass = new \ReflectionClass($this);
        $properties = $reflectionClass->getProperties();
        $property_keys = array_values(array_filter(array_map(function ($p)
        {
            return $p->isStatic() ? null : $p->getName();
        }, $properties)));

        $result = [];
        foreach ($property_keys as $index => $key) {
            $property = $reflectionClass->getProperty($key);
            $result[$key] = $this->getSerializedPropertyValue(
                $this->getPropertyValue($property)
            );
        }
        return $result;
    }

    /**
     * 反序列化
     * @param  array  $key_value_array
     *
     * @return $this
     * @throws \ReflectionException
     */
    function toUnserialize($key_value_array = [])
    {
        $reflectionClass = new \ReflectionClass($this);
        foreach ($key_value_array as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $this->setPropertyValue($reflectionClass->getProperty($key), $value);
            }
        }
        return $this;
    }

    /**
     * 根据类型获取序列化值
     *
     * @param  mixed  $value
     *
     * @return mixed
     */
    protected function getSerializedPropertyValue($value)
    {
        return $value;
    }

    /**
     * Get the property value for the given property.
     *
     * @param  \ReflectionProperty  $property
     *
     * @return mixed
     */
    protected function getPropertyValue(\ReflectionProperty $property)
    {
        // 让 private 属性的都可以访问获取
        $property->setAccessible(true);

        return $property->getValue($this);
    }

    /**
     * Get the property value for the given property.
     *
     * @param  \ReflectionProperty  $property
     * @param $value
     *
     * @return mixed
     */
    protected function setPropertyValue(\ReflectionProperty $property, $value)
    {
        // 让 private 属性的都可以访问获取
        $property->setAccessible(true);

        $property->setValue($this, $value);
    }
}