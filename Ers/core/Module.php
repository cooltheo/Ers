<?php
/**
 * ErsModule Class
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
use Ers\Core\Exception as Exception;
use Ers\Util as Util;

/**
 * Module class
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class Module
{
    private $_name;
    private $_typeNames = array();


    /**
     * 构造方法
     *
     * @param String $name 模块名称
     */
    public function __construct($name)
    {
        $this->_setName($name);
        $this->_makeModuleFolder();
        $this->_loadTypes();
    }

    /**
     * 设置模块名称
     *
     * @param string $name 模块名称
     *
     * @return void
     */
    private function _setName($name)
    {
        $name = trim($name);
        if (preg_match('/\W+/', $name)) {
            throw new Exception\ModuleError("Illegal module name");
        }
        $this->_name = strtolower($name);
    }

    /**
     * 获取模块名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }


    /**
     * 获取类型对象
     *
     * @param string $typeName 类型名称
     *
     * @return Type
     */
    public function getType($typeName)
    {
        static $types = array();
        if (!array_key_exists($typeName, $types)) {
            $types[$typeName] = new Type($this->getName(), $typeName);
        }
        return $types[$typeName];
    }

    /**
     * 删除类型对象
     *
     * @param string $typeName 类型名称
     *
     * @return void
     */
    public function delType($typeName)
    {



    }



    /**
     * 获取所有的模块对象名称
     *
     * @return array
     */
    public function getTypeNames()
    {
        return $this->_typeNames;
    }

    /**
     * 加载所有的配置名称
     *
     * @return void
     */
    private function _loadTypes()
    {
        $path = ERS_CONFIG_DIR. DS . $this->_name . DS;
        $this->_typeNames = Util\Helper::getDirNames($path);
    }

    /**
     * 创建模块文件夹
     *
     * @return void
     */
    private function _makeModuleFolder()
    {
        $path = ERS_CONFIG_DIR . DS . $this->_name;
        Util\Helper::makeDir($path);
    }


}