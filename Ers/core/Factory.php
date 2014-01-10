<?php
/**
 * ErsFactory Class
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
use Ers\Lib\Index\Impl\Sphinx;

use Ers\Core\Exception\FactoryError;
use Ers\Lib\Db\Impl as DbImpl;
use Ers\Lib\Cache\Impl as CacheImpl;
use Ers\Lib\Index\Impl as IndexImpl;


/**
 * Factory class
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class Factory
{
    protected $dbInstance = array();
    protected $cacheInstance = array();
    protected $indexInstance = array();

    /**
     * 构造方法
     *
     * @retur void
     */
    private function __construct()
    {

    }

    /**
     * 获取一个实例
     *
     * @return Factory
     */
    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
    /**
     * 获取数据存储对象
     *
     * @param Type $type 类型实例
     *
     * @throws FactoryError
     * @return Ambigous <NULL, \Ers\Lib\Db\Impl\Mongodb>
     */
    public function getStore(Type $type)
    {
        $moduleName = $type->getModuleName();
        $typeName = $type->getName();
        try {
            if (empty($this->dbInstance[$moduleName][$typeName])) {
                $config = App::getConfig();
                $server = $config->get('store', 'server');
                $dbType = $config->get('store', 'type');
                switch ($dbType)
                {
                case "mongodb":
                    $options = array();
                    $db = new DbImpl\Mongodb($server, $options);
                    $db->selectDb($moduleName);
                    $db->selectTable($typeName);
                    $this->dbInstance[$moduleName][$typeName]= $db;
                    break;
                default:
                    throw new \Exception("Initial create this type[$dbType] of database");
                }
            }
            return $this->dbInstance[$moduleName][$typeName];
        } catch (Exception $e) {
            throw new FactoryError($e->getMessage());
        }
    }

    /**
     * 获取缓存存储对象
     *
     * @param Type $type 类型实例
     *
     * @throws \Exception
     * @throws FactoryError
     *
     * @return Ambigous <NULL, \Ers\Lib\Cache\Impl\File>
     */
    public function getCache(Type $type)
    {
        $moduleName = $type->getModuleName();
        $typeName = $type->getName();
        try {
            if (empty($this->cacheInstance[$moduleName][$typeName])) {
                $config = App::getConfig();
                $cacheType = $config->get('cache', 'type');
                switch ($cacheType)
                {
                case "file":
                    $cacheDir = ERS_TEMP_DIR . DS . $moduleName. DS . $typeName;
                    $cache = new CacheImpl\File($cacheDir);
                    $this->cacheInstance[$moduleName][$typeName] = $cache;
                    break;
                default:
                    throw new \Exception("Initial create this type[$cacheType] of database");
                }
            }
            return $this->cacheInstance[$moduleName][$typeName];
        } catch (Exception $e) {
            throw new FactoryError($e->getMessage());
        }
    }

    /**
     * 获取类型索引对象
     *
     * @param Type $type 类型实例
     *
     * @throws \Exception
     * @throws FactoryError
     */
    public function getIndex(Type $type)
    {
        $moduleName = $type->getModuleName();
        $typeName = $type->getName();
        try {
            if (empty($this->indexInstance[$moduleName][$typeName])) {
                $config = App::getConfig();
                $indexType = $config->get('index', 'type');
                $baseUrl = $config->get('index', 'server');
                $indexName = $moduleName . "_" . $typeName;
                if (!$type->getIndexAttrNames()) {
                    throw new \Exception("Undefined index attribute field");
                }
                switch ($indexType)
                {
                case "sphinx":
                    $attrMaps = $type->getIndexAttrMaps();
                    $index = new IndexImpl\Sphinx($baseUrl, $indexName);
                    $sphinxScheme =  $this->_makeSphinxScheme($attrMaps);
                    if (!$index->getScheme()) {
                        $res = $index->setScheme($sphinxScheme);
                    }
                    $this->indexInstance[$moduleName][$typeName] = $index;
                    break;
                default:
                    throw new \Exception("Initial create this type[$indexType] of index");
                }
            }
            return $this->indexInstance[$moduleName][$typeName];
        } catch (Exception $e) {
            throw new FactoryError($e->getMessage());
        }
    }

    /**
     * 生成 sphinx scheme
     *
     * @param array  $attrMaps 属性对象数组
     * @param string $primary  主键
     *
     * @return array
     */
    private function _makeSphinxScheme(array $attrMaps, $primary = '_id')
    {
        $scheme = array();
        $scheme[Sphinx::INDEX_SCHEME_PRIMARY] = $primary;
        if (isset($attrMaps[$primary])) {
            unset($attrMaps[$primary]);
        }

        foreach ($attrMaps as $attrName => $attrType)
        {
            switch ($attrType)
            {
            case Attribute::TYPE_STRING:
                $scheme[Sphinx::INDEX_SCHEME_FIELD][] = array(
                    'name' => $attrName
                );
                break;
            case Attribute::TYPE_INT:
                $scheme[Sphinx::INDEX_SCHEME_ATTR][] = array(
                    'name' => $attrName,
                    'type' => Sphinx::INDEX_SCHEME_ATTR_TYPE_INT
                );
                break;
            case Attribute::TYPE_DOUBLE:
            case Attribute::TYPE_FLOAT:
                $scheme[Sphinx::INDEX_SCHEME_ATTR][] = array(
                    'name' => $attrName,
                    'type' => Sphinx::INDEX_SCHEME_ATTR_TYPE_FLOAT
                );
                break;
            case Attribute::TYPE_MULTI:
                $scheme[Sphinx::INDEX_SCHEME_ATTR][] = array(
                    'name' => $attrName,
                    'type' => Sphinx::INDEX_SCHEME_ATTR_TYPE_FLOAT
                );
                break;
            case Attribute::TYPE_BOOL:
                 $scheme[Sphinx::INDEX_SCHEME_ATTR][] = array(
                    'name' => $attrName,
                    'type' => Sphinx::INDEX_SCHEME_ATTR_TYPE_BOOL
                );
                break;
            case Attribute::TYPE_TIMESTAMP:
                $scheme[Sphinx::INDEX_SCHEME_ATTR][] = array(
                    'name' => $attrName,
                    'type' => Sphinx::INDEX_SCHEME_ATTR_TYPE_TIMESTAMP
                );
                break;
            default:
                throw new AttributeError("Invalid type value :" . $type);
                break;
            }
        }
        return $scheme;
    }
}
