<?php
/**
 * Attribute Class
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
use Ers\Core\Exception\AttributeError;

use Ers\Util\Validation as Validation;
/**
 * Attribute class
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class Attribute
{
    CONST TYPE_STRING = "STRING";
    CONST TYPE_INT = "INT";
    CONST TYPE_FLOAT = "FLOAT";
    CONST TYPE_DOUBLE = "DOUBLE";
    CONST TYPE_BOOL = "BOOLEAN";
    CONST TYPE_MULTI = "ARRAY";
    const TYPE_TIMESTAMP = "TIMESTAMP";

    private $_name;
    protected $type = self::TYPE_STRING;
    protected $index = true;
    protected $required = false;
    protected $default = null;
    protected $alarm = true;

    public static $typeList = array(
        self::TYPE_STRING, self::TYPE_FLOAT, self::TYPE_DOUBLE,
        self::TYPE_BOOL, self::TYPE_BOOL, self::TYPE_MULTI
    );


    /**
     * 构造方法
     *
     * @param string $name 字段名称
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * 设置属性名称
     *
     * @param string $name 名称
     *
     * @return  void
     */
    public function setName($name)
    {
        $name = trim($name);
        if (!preg_match('/[A-Za-z0-9]+/', $name)) {
            throw new Exception\ModuleError("Illegal module name :". $name);
        }
        $this->_name = strtolower($name);
    }

    /**
     * 返回属性名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }


    /**
     * 设置数据类型
     *
     * @param string $type 数据类型
     *
     * @return void
     */
    public function setType($type)
    {
        if (!in_array($type, self::$typeList)) {
            throw new AttributeError("Invalid type value :" . $type);
        }
        $this->type = $type;
    }


    /**
     * 获取数据类型
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * 是否默认值
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }


    /**
     * 是否必须
     *
     * @param bool $value 值
     *
     * @return void
     */
    public function isRequired($value = null)
    {
        if ($value === null) {
            return (bool)$this->required;
        }
        $this->required = (bool)$value;
    }


    /**
     * 是否索引
     *
     * @param bool $value 值
     *
     * @return void
     */
    public function isIndex($value = null)
    {
        if ($value === null) {
            return (bool)$this->index;
        }
        $this->index = (bool)$value;
    }

    /**
     * 是否报警
     *
     * @param bool $value 值
     *
     * @return void
     */
    public function isAlarm($value = null)
    {
        if ($value == null) {
            return (bool)$this->alarm;
        }
        $this->alarm = (bool)$value;
    }


    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray()
    {
        $reflect = new \ReflectionClass($this);
        $allPros = $reflect->getDefaultProperties();
        $staticPros = $reflect->getStaticProperties();
        $data= array();
        foreach ($allPros as $k=>$v) {
            $reflectionProperty = $reflect->getProperty($k);
            if ($reflectionProperty->isPrivate() || $reflectionProperty->isStatic()) {
                continue;
            }
            $value = $v;
            if (is_bool($value)) {
                $value = (int)$value;
            }
            if (is_array($value)) {
                $value = join(';', $value);
            }
            $data[$k]= $value;
        }
        return $data;
    }


    /**
     * 检验参数类型
     *
     * @param string $type  类型
     * @param mixed  $value 值
     *
     * @return bool
     */
    public static function checkType($type, $value)
    {
        switch ($type)
        {
        case self::TYPE_STRING:
            if (!is_string($value)) {
                return false;
            }
            break;
        case self::TYPE_INT:
            if (!is_int($value)) {
                return false;
            }
            break;
        case self::TYPE_FLOAT:
            if (!is_float($value)) {
                return false;
            }
            break;
        case self::TYPE_DOUBLE:
            if (!is_double($value)) {
                return false;
            }
            break;
        case self::TYPE_MULTI:
            if (!is_array($value)) {
                return false;
            }
            if (Validation::isAssocArray($value)) {
                return false;
            }
            break;
        case self::TYPE_BOOL:
            if (!is_bool($value)) {
                return false;
            }
        case self::TYPE_TIMESTAMP:
            if (!is_int($value)) {
                return false;
            }
            if (Validation::isTimestamp($value)) {
                return false;
            }
            break;
        default:
            throw new AttributeError("Invalid type value :" . $type);
            break;
        }
        return true;
    }

    /**
     * 转换参数类型
     *
     * @param string $type  类型
     * @param mixed  $value 值
     *
     * @return void
     */
    public static function convertType($type, $value)
    {
        switch ($type)
        {
        case self::TYPE_STRING:
            $value = (string)$value;
            break;
        case self::TYPE_INT:
            $value = (int)$value;
            break;
        case self::TYPE_DOUBLE:
            $value = (double)$value;
            break;
        case self::TYPE_FLOAT:
            $value = (float)$value;
            break;
        case self::TYPE_MULTI:
            $value = (array)$value;
            break;
        case self::TYPE_BOOL:
            $value = (bool)$value;
            break;
        case self::TYPE_TIMESTAMP:
            $value = (int)$value;
            break;
        default:
            throw new AttributeError("Invalid type value :" . $type);
            break;
        }
        return $value;
    }


}