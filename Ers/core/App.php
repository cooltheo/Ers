<?php
/**
 * ErsApp Class
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
use Ers\Util as Util;

/**
 * App class
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class App
{
    protected static $systemConfig;
    private $_moduleNames = array();

    /**
     * 构造方法
     *
     */
    private function __construct()
    {
        self::loadSystemConfig();
        $this->_loadModules();
    }

    /**
     * 获取单例
     *
     * @return void
     */
    public static function getInstance()
    {
        static $instance;
        if ($instance == null) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * 加载配置对象
     *
     * @return ErsConfig
     */
    protected static function loadSystemConfig()
    {
        try{
            if (self::$systemConfig == null) {
                self::$systemConfig = SystemConfig::loadFromFile(ERS_CONFIG_DIR. DS .ERS_SYSTEM_CONFIG_FILE);
            }
        } catch(Exception $e) {
            throw new Exception\AppError("Initialize the system configuration errors");
        }
    }

    /**
     * 获取配置对象
     *
     * @return Config
     */
    public static function getConfig()
    {
        return  self::$systemConfig;
    }

    /**
     * 获取所有的模块名称
     *
     * @return Module
     */
    public function getModuleNames()
    {
        return $this->_moduleNames;
    }


    /**
     * 获取模块对象
     *
     * @param string $name 模块名称
     *
     * @return true
     */
    public function getModule($name)
    {
        return new Module($name);
    }


    /**
     * 删除模块
     *
     * @param string $name 模块名称
     *
     * @return void
     */
    public function delModule($name)
    {



    }

    /**
     * 加载所有的模块名称
     *
     * @return void
     */
    private function _loadModules()
    {
        $path = ERS_CONFIG_DIR . DS;
        $this->_moduleNames = Util\Helper::getDirNames($path);
    }
}