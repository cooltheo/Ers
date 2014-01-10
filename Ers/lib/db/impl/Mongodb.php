<?php
/**
 * Mongodb Api
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
namespace Ers\Lib\Db\Impl;
use Ers\Lib\Db as Db;
/**
 * This is a mongo API
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class Mongodb implements Db\DbInterface
{
    private $_mongo;
    private $_db;
    private $_collection;

    /**
     * 构造方法
     *
     * @param string $server  服务器地址
     * @param array  $options 参数数组
     *
     * @return void
     */
    public function __construct($server, $options = array())
    {
        if (!class_exists("MongoClient")) {
            throw new Exception(
                "not installed mongo extension"
            );
        }
        $this->_mongo = new \MongoClient($server, $options);
    }

    /**
     * 选择Db
     *
     * @param string $dbName 切换db
     *
     * @return void
     */
    public function selectDb($dbName)
    {
        $this->_db = $this->_mongo->selectDB($dbName);
        return $this->_db;
    }

    /**
     * 选择Table
     *
     * @param string $tableName 表名
     *
     * @return void
    */
    public function selectTable($tableName)
    {
        $this->_collection = $this->_db->selectCollection($tableName);
        return $this->_collection;
    }


    /**
     * find one mongo object
     *
     * @param array $query  查询条件
     * @param array $fields 字段名称
     *
     * @return fields
     */
    public function findOne(array $query, array $fields = array())
    {
        return $this->_collection->findOne($query, $fields);
    }


    /**
     * Find and return query result array.
     *
     * @param array $query   查询条件
     * @param array $options 附件查询可选项
     *
     * @return mixed
     **/
    public function find($query = array(), $options = array())
    {
        $options += array('fields' => array(), 'sort' => array(), 'skip' => 0, 'limit' => 0, 'cursor' => false, 'tailable' => false, 'hint' => array());
        extract($options);
        $cur = $this->_collection->find($query, $fields);
        if ($sort) {
            $cur->sort($sort);
        }
        if ($skip) {
            $cur->skip($skip);
        }
        if ($limit) {
            $cur->limit($limit);
        }
        if ($tailable) {
            $cur->tailable($tailable);
        }
        if ($hint) {
            $cur->hint($hint);
        }
        if ($cursor) {
            return $cur;
        }
        return iterator_to_array($cur);

    }

    /**
     * Update a Mongo object
     *
     * @param array $query   查询条件
     * @param array $data    带保存的更新数据
     * @param array $options 附加可选项
     *
     * @return mixed
     */
    public function update(array $query, array $data, array $options = array())
    {
        $res = $this->_collection->update($query, $data, $options);
        return (bool)$res['ok'];
    }



    /**
     * Save a Mongo object
     *
     * @param array $data 待保存的数据
     *
     * @return boolean
     **/
    public function save($data)
    {
        $res = $this->_collection->save($data);
        return (bool)$res['ok'];
    }

    /**
     * remove a Mongo object
     *
     * @param array $query   查询条件
     * @param array $options 附加可选项
     *
     * @return num
     */
    public function remove($query, $options = array())
    {
        $res = $this->_collection->remove($query, $options);
        return (int)$res['n'] > 0 ? true : false;
    }


    /**
     * 获取集合总数
     *
     * @param array $query   查询条件
     * @param array $options 选项数组
     *
     * @return num
     */
    public function count($query = array(),array $options = array())
    {
        $limit = isset($options['limit']) ? (int)$options['limit'] : 0;
        $skip = isset($options['skip']) ? (int)$options['skip'] : 0;
        return $this->_collection->count($query, $limit, $skip);
    }

    /**
     * 生成自增整型id
     *
     * @param string $domain     用于区分当前id所针对的数据，比如product等
     * @param string $collection 用于持久化最后生成id的MongoCollection名称
     *
     * @throws Exception
     * @return number
     */
    public function getIncId($domain, $collection = 'Increment')
    {
        if (empty($domain)) {
            return false;
        }
        $result = $this->_db->command(
            array(
                'findAndModify' => $collection,
                'query' => array('_id' => $domain),
                'update' => array('$inc' => array('val' => 1)),
                'new' => true,
                'upsert' => true
            )
        );
        if ($result['ok'] && $id = intval($result['value']['val'])) {
            return $id;
        }
        throw new \Exception('Mongo: gen auto increment id failed');
    }


    /**
     * 清空自增整形Id
     *
     * @param string $domain     用于区分当前id所针对的数据，比如product等
     * @param string $collection 用于持久化最后生成id的MongoCollection名称
     *
     * @throws Exception
     * @return bool
     */
    public function cleanIncId($domain, $collection = 'Increment')
    {
        if (empty($domain)) {
            return false;
        }
        $result = $this->_db->command(
            array(
                'findAndModify' => $collection,
                'query' => array('_id' => $domain),
                'update' => array('val' => 0),
                'new' => true,
                'upsert' => true
            )
        );

        $res = (bool)$result['ok'];
        return $res;
    }


    /**
     * 链接数据库
     *
     * @return void
     */
    public function connect()
    {
        $this->_mongo->connect();
    }


    /**
     * 关闭链接
     *
     * @return void
     */
    public function close()
    {
        $connections = $this->_mongo->getConnections();
        foreach ($connections as $con) {
            $this->_mongo->close($con['hash']);
        }
    }


    /**
     * 析构方法
     *
     * @return void
     */
    public function __destruct()
    {
        $this->close();
        $this->_mongo = null;
        $this->_db = null;
        $this->_collection = null;
    }

}