<?php
/**
 * ErsConfig Class
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
use Ers\Lib as Lib;
use Ers\Core\Exception as Exception;

/**
 * Config class
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class Config
{
    private $_iniParse;

    /**
     * 构造方法
     *
     * @param string $file 文件名
     */
    public function __construct($file)
    {
        $this->_iniParse = new Lib\iniParser($file);

    }


    /**
     * 从文件加载返回实例
     *
     * @param string $file 配置文件名
     *
     * @return Ers\Core\Config
     */
    public static function loadFromFile($file)
    {
        if (empty($file) || !is_file($file)) {
            throw new Exception\ConfigError("Invalid configuration file");
        }
        $class = get_called_class();
        return new $class($file);
    }

    /**
     * 魔术方法 __call()
     *
     * @param string $method    方法
     * @param array  $arguments 参数数组
     *
     * @return void
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->_iniParse, $method), $arguments);
    }

    /**
     * 魔术方法 __toString()
     *
     * @return void
     */
    public function __toString()
    {
        return $this->_iniParse->buildString();
    }

}