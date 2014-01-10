<?php
/**
 * Mapping Class
 *
 * AnyChem Confidential
 * Copyright (c) 2011, AnyChem Corp. <support@anychem.com>.
 * All rights reserved.
 *
 * PHP version 5
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
/**
 * @namespace
 */
namespace Ers\Core;


use Ers\Core\Exception\MappingError;

/**
 * Mapping class
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class Mapping extends Config implements \Iterator
{
    private $_items = array();

    /**
     * 初始化方法
     *
     * @param string $file 文件名
     */
    public function __construct($file)
    {
        parent::__construct($file);
        //从配置文件中加载对象
        $this->_loadItems();
    }

    /**
     * 新增一个属性
     *
     * @param Attribute $attr 属性对象
     *
     * @return void
     */
    public function addAttr(Attribute $attr)
    {
        $name = $attr->getName();
        $this->_items[$name] = $attr;
        return $this;
    }

    /**
     * 删除一个属性
     *
     * @param string $attrName 属性名称
     *
     * @return void
     */
    public function delAttr($attrName)
    {
        if (isset($this->_items[$name])) {
            unset($this->_items[$name]);
        }
        return $this;
    }


    /**
     * 获取一个属性实例
     *
     * @param string $attrName 属性名称
     *
     * @return Attribute
     */
    public function getAttr($attrName)
    {
        if (!isset($this->_items[$attrName])) {
            return null;
        }
        return $this->_items[$attrName];
    }

    /**
     * 存储
     *
     * @return void
     */
    public function save()
    {
        $this->setAll($this->toArray());
        parent::save();
    }

    /**
     * Iterator::current
     *
     * @return MappingField
     */
    public function current()
    {
        return current($this->_items);
    }

    /**
     * Iterator:next()
     *
     * @return MappingField
     */
    public function next()
    {
        next($this->_items);
    }

    /**
     * Iterator:key()
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->_items);
    }


    /**
     * Ieterator:rewind()
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->_items);
    }

    /**
     * Iterator:valid
     *
     * @return void
     */
    public function valid()
    {
        return ($this->current() !== false);
    }


    /**
     * 加载Items
     *
     * @return void
     */
    private function _loadItems()
    {
        try {
            $itemData = $this->getAll();
            foreach ($itemData as $name => $data) {
                $reflect = new \ReflectionClass("Ers\Core\Attribute");
                $attribute = $reflect->newInstance($name);
                foreach ($data as $key => $value) {
                    $prop = $reflect->getProperty($key);
                    if (false !== strpos($value, ";")) {
                        $value = explode(";", $value);
                    }
                    $prop->setAccessible(true);
                    $prop->setValue($attribute, $value);
                }
                $this->addAttr($attribute);
            }
        } catch (Exception $e) {
            throw new MappingError($e->getMessage());
        }
    }

    /**
     * 获取属性值数组
     *
     * @return array
     */
    public function getAttrNames()
    {
        return array_keys($this->_items);
    }


    /**
     * 获取索引属性数组
     *
     * @return array
     */
    public function getIndexAttrNames()
    {
        $indexNames = array();
        foreach ($this->_items as $key => $attr) {
            if ($attr->isIndex()) {
                $indexNames[] = $attr->getName();
            }
        }
        return $indexNames;
    }

    /**
     * 获取必须属性数组
     *
     * @return array
     */
    public function getRequiredAttrNames()
    {
        $indexNames = array();
        foreach ($this->_items as $key => $attr) {
            if ($attr->isRequired()) {
                $indexNames[] = $attr->getName();
            }
        }
        return $indexNames;
    }

    /**
     * 获取警报属性数组
     *
     * @return array
     */
    public function getAlarmAttrNames()
    {
        $indexNames = array();
        foreach ($this->_items as $key => $attr) {
            if ($attr->isAlarm()) {
                $indexNames[] = $attr->getName();
            }
        }
        return $indexNames;
    }


    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->_items as $name => $attribute) {
            $data[$name] = $attribute->toArray();
        }
        return $data;
    }

}