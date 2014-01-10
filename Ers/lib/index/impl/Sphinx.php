<?php
/**
 * Sphinx Index Api
 *
 * AnyChem Confidential
 * Copyright (c) 2011, AnyChem Corp. <support@anychem.com>.
 * All rights reserved.
 *
 * PHP version 5
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <chao.hu@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
/**
 * @namespace
 */
namespace Ers\Lib\Index\Impl;

use Ers\Lib\Index as Index;
use Ers\Lib as Lib;

/**
 * This is a sphinx index API
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class Sphinx implements Index\IndexInterface
{
    const INDEX_SCHEME_FIELD = 'field';
    const INDEX_SCHEME_ATTR = 'attr';
    const INDEX_SCHEME_PRIMARY = 'primary';

    const INDEX_SCHEME_ATTR_TYPE_INT = 'int';
    const INDEX_SCHEME_ATTR_TYPE_TIMESTAMP = 'timestamp';
    const INDEX_SCHEME_ATTR_TYPE_STR2ORDINAL = 'str2ordinal';
    const INDEX_SCHEME_ATTR_TYPE_BOOL = 'bool';
    const INDEX_SCHEME_ATTR_TYPE_FLOAT = 'float';
    const INDEX_SCHEME_ATTR_TYPE_MULTI = 'multi';

    protected $pest;
    private   $_name;

    /**
     * 构造方法
     *
     * @param string $baseUrl   基础URL地址
     * @param stirng $indexName 索引名称
     *
     */
    public function __construct($baseUrl, $indexName)
    {
        $this->_setName($indexName);
        $this->pest =  new Lib\IndexPest($baseUrl);
        //测试index server 是否已经打开
        $this->ping();

    }


    /**
     * 设置scheme
     *
     * @param array scheme数组
     *
     * @return bool
     */
    public function setScheme(array $scheme)
    {
        $uri = '/' . $this->getName() . '/'. '_scheme';
        $res = $this->pest->post($uri, array('scheme'=>json_encode($scheme)));
        return (bool)$res;
    }


    /**
     * 获取scheme
     *
     * @return array
     */
    public function getScheme()
    {
        $uri = '/' . $this->getName() . '/' . '_scheme';
        $res = $this->pest->get($uri);
        return $res;
    }



    /**
     * 设置配置数组
     *
     * @param array $config config数组
     *
     * @return void
     */
    public function setConfig(array $config)
    {


    }


    /**
     * 获取配置数组
     *
     * @param array $config config数组
     *
     * @return void
     */
    public function getConfig(array $config)
    {


    }

    /**
     * 添加索引数据
     *
     * @param array $data 索引数据
     *
     * @return bool
     */
    public function add($data)
    {
        $uri = '/' . $this->getName();
        $res = $this->pest->post($uri, array('data'=>json_encode($data)));
        return (bool)$res;
    }


    /**
     * 删除索引
     *
     * @param number $id 索引ID
     *
     * @return bool
     */
    public function del($id)
    {
        $uri = '/' . $this->getName();
        $res = $this->pest->delete($uri, array('id'=>$id));
        return (bool)$res;
    }


    /**
     * 删除集合索引
     *
     * @param array $ids 索引Ids
     *
     * @return bool
     */
    public function dels(array $ids)
    {
        $uri = '/' . $this->getName();
        $res = $this->pest->delete($uri, array('id'=>json_encode($ids)));
        return (bool)$res;
    }


    /**
     * 设置索引名称
     *
     * @param string $indexName 索引名称
     *
     * @return void
     */
    private function _setName($indexName)
    {
        $indexName = trim($indexName);
        if (!preg_match('/[A-Za-z0-9]+/', $indexName)) {
            throw new Exception\ModuleError("Illegal module name :". $indexName);
        }
        $this->_name = strtolower($indexName);
    }



    /**
     * 获取索引名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }


    /**
     * 检测server是否打开
     *
     * @return false
     */
    public function ping()
    {
        try {
            $this->pest->get('/_ping');
        } catch (Exception $e) {
            throw new \Exception("Unable to connect the index server");
        }
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError()
    {
        return $this->pest->getError();
    }
}