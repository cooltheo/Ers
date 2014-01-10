<?php
/**
 * Type Class
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
use Ers\Core\Exception\FactoryError;

use Ers\Core\Exception\TypeError;

use Ers\Util as Util;
/**
 * Type class
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class Type
{
    private $_config;
    private $_moduleName;
    private $_name;
    private $_mapping;
    private $_error;

    /**
     * 构造方法
     *
     * @param string $moduleName 模块名称
     * @param string $name       类型名称
     */
    public function __construct($moduleName, $name)
    {
        $this->_setName($name);
        $this->_setModuleName($moduleName);
        $this->_makeTypeFolder();
    }

    /**
     * 获取模块名称
     *
     * @return void
     */
    public function getModuleName()
    {
        return $this->_moduleName;
    }

    /**
     * 设置模块名称
     *
     * @param string $moduleName 模块名称
     *
     * @return string
     */
    private function _setModuleName($moduleName)
    {
        $moduleName = trim($moduleName);
        if (preg_match('/\W+/', $moduleName)) {
            throw new Exception\ModuleError("Illegal module name");
        }
        $this->_moduleName = $moduleName;
    }

    /**
     * 获取类型名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }


    /**
     * 设置类型名称
     *
     * @param string $name 类型名称
     *
     * @return array
     */
    private function _setName($name)
    {
        $name = trim($name);
        if (preg_match('/\W+/', $name)) {
            throw new Exception\ModuleError("Illegal type name");
        }
        $this->_name = strtolower($name);
    }

    /**
     * 获取mapping
     *
     * @return Mapping
     */
    public function getMapping()
    {
        if ($this->_mapping === null) {
            $mappingPath = $this->_getMappingPath();
            if (!is_file($mappingPath)) {
                Util\Helper::makeFile($mappingPath);
            }
            $mapping = Mapping::loadFromFile($mappingPath);
            $this->_mapping = $mapping;
        }
        return $this->_mapping;
    }

    /**
     * 获取typeConfig
     *
     * @return typeConfig
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $configPath = $this->_getConfigPath();
            if (!is_file($configPath)) {
                Util\Helper::makeFile($configPath);
            }
            $config = TypeConfig::loadFromFile($configPath);
            $this->_config = $config;
        }
        return $this->_config;
    }

    /**
     * 获取字段名称
     *
     * @return array
     */
    public function getAttrNames()
    {
        //缓存优化
        static $attrNames = null;
        if ($attrNames === null) {
            $attrNames = $this->getMapping()->getAttrNames();
        }
        return $attrNames;
    }

    /**
     * 获取索引字段名称
     *
     * @return array
     */
    public function getIndexAttrNames()
    {
        //缓存优化
        static $indexAttrNames = null;
        if ($indexAttrNames === null) {
            $indexAttrNames = $this->getMapping()->getIndexAttrNames();
        }
        return $indexAttrNames;
    }

    /**
     * 获取required字段名称
     *
     * @return array
     */
    public function getRequiredAttrNames()
    {
        //缓存优化
        static $requiredAttrNames = null;
        if ($requiredAttrNames === null) {
            $requiredAttrNames = $this->getMapping()->getRequiredAttrNames();
        }
        return $requiredAttrNames;
    }

    /**
     * 获取alarm字段名称
     *
     * @return array
     */
    public function getAlarmAttrNames()
    {
        static $alarmAttrNames = null;
        if ($alarmAttrNames === null) {
            $alarmAttrNames = $this->getMapping()->getAlarmAttrNames();
        }
        return $alarmAttrNames;
    }

    /**
     * 验证数据
     *
     * @param array $data     数据
     * @param bool  $isUpdate 是否是更新数据
     *
     * @return bool
     */
    public function valid(array $data , $isUpdate = false)
    {
        //检测数据是否为空
        if (empty($data)) {
            $this->_error = "the target data is empty";
            return false;
        }

        $keys = array_keys($data);
        //检测是否有不存在的数据
        $diffFields = array_diff($keys, $this->getAttrNames());
        if (!empty($diffFields)) {
            $this->_error = "Attribute ". join(',', $diffFields). " is undefined";
            return false;
        }

        if (!$isUpdate) {
            //检测必须的字段是否被遗漏
            $diffFields = array_diff($this->getRequiredAttrNames(), $keys);
            if (!empty($diffFields)) {
                $this->_error= "Attribute ". join(',', $diffFields). " is required";
                return false;
            }
        }

        //检测值的属性类型是否需要转换
        $alarmFields = $this->getAlarmAttrNames();
        try {
            foreach ($data as $name => $value) {
                $type = $this->getMapping()->getAttr($name)->getType();
                if (!Attribute::checkType($type, $value) && in_array($name, $alarmFields)) {
                    $this->_error = "The type of attribute " . $name . " as a " . $type;
                    return false;
                }
                //当TYPE为STRING时,检测是否为UTF-8字符
            }
        } catch (Exception $e) {
            $this->_error = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 添加单条数据
     *
     * @param array $data 数据
     *
     * @return void
     */
    public function add(array $data)
    {
        //检测环境
        $this->_checkEnv();
        //验证数据
        if (!$this->valid($data)) {
            return false;
        }

        try {
            //加载默认值
            $data = $this->_loadDefaultValue($data);

            //转换数据类型
            $data = $this->_convertDataType($data);

            //获取存储对象
            $moduleName = $this->getModuleName();
            $typeName = $this->getName();
            $store = Factory::getInstance()->getStore($this);

            //组装存储数据
            $storeData = array();
            $storeData['_id'] = $store->getIncId($typeName);
            $storeData['_current_version'] = TypeConfig::DEFAULT_REVISION_INIT_NUM;
            $storeData['_history'] = array();
            $data['_created_at_ts'] = time();
            $data['_version'] =  $storeData['_current_version'];
            $storeData['_data'] = $data;
            //存储
            $storeRes = $store->save($storeData);
            if (!$storeRes) {
                throw new TypeError("Store id [". $storeData['_id'] ."]  save failure");
            }

            //索引相关
            //获取索引对象
            $index = Factory::getInstance()->getIndex($this);
            $indexAttrNames = $this->getIndexAttrNames();
            $indexData = Util\Helper::getData($data, $indexAttrNames);
            $indexData['_id'] = $storeData['_id'];
            $indexRes = $index->add($indexData);

            if (!$indexRes) {
                throw new TypeError("Index Error, Reason: " . $index->getError());
            }

            //TODO 是否回滚数据
            return true;
        } catch (Exception $e) {
            $this->_error = $e->getMessage();
            return false;
        }
    }

    /**
     * 更新数据
     *
     * @param int   $id   数据ID
     * @param array $data 数据
     *
     * @return void
     */
    public function update($id, array $data)
    {
        //检测环境
        $this->_checkEnv();

        if (!is_numeric($id) || $id <= 0) {
            $this->_error = "invalid parameter id";
            return false;
        }

        //验证数据
        if (!$this->valid($data, true)) {
            return false;
        }

        try {
            //转换数据类型
            $id = (int)$id;
            $data = $this->_convertDataType($data);

            //获取存储对象
            $moduleName = $this->getModuleName();
            $typeName = $this->getName();
            $store = Factory::getInstance()->getStore($this);

            //获取当条记录
            $query = array('_id' => $id);
            $currentData = $store->findOne($query);
            if (!$currentData) {
                throw new TypeError("Store id[". $id ."] not found");
            }

            //参数相关
            $createdAtTs = time();
            $createdAt = date('Y-m-d H:i:s', $createdAtTs);
            $oldVersion = $currentData['_current_version'];
            $newVersion = $currentData['_current_version'] + 1;
            $data["_version"] = $newVersion;
            $data["_created_at_ts"] = $createdAtTs;
            $newData = array_merge($currentData['_data'], $data);
            $newHistory = $currentData['_history'];
            $newHistory[$oldVersion] = $currentData['_data'];

            //获取用户设置的KEEPNUM
            $keepNum = $this->getConfig()->get('revision', 'keep') ?
                (int) $this->getConfig()->get('revision', 'keep') : (int)TypeConfig::DEFAULT_REVISION_KEEP_NUM;

            //移除超出的部分
            if (count($newHistory) > $keepNum) {
                unset($newHistory[$newVersion - $keepNum]);
            }

            $updateData = array(
                '$set' => array(
                    '_current_version' => $newVersion,
                    '_data' => $newData,
                    '_history'=> $newHistory
                ),
            );

            //更新
            $updateRes = $store->update($query, $updateData);
            if (!$updateRes) {
                throw new TypeError("Store id [". $storeData['_id'] ."] update failure");
            }

            //先获取更新后的数据
            $newQuery['_id'] = $id;
            $newFields = array('_data');
            $newData  = $store->findOne($newQuery, $newFields);

            $index = Factory::getInstance()->getIndex($this);
            $indexAttrNames = $this->getIndexAttrNames();
            $indexData = Util\Helper::getData($newData['_data'], $indexAttrNames);
            $indexData['_id'] = $newData['_id'];

            $indexRes = $index->add($indexData);
            if (!$indexRes) {
                throw new TypeError("Index Error, Reason: " . $index->getError());
            }
            return true;
        } catch (Exception $e) {
            $this->_error = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取单条数据
     *
     * @param int   $id        主键ID
     * @param array $attrNames 属性名称
     * @param int   $version   版本号·
     *
     * @return array
     */
    public function get($id, array $attrNames = array(),  $version = null)
    {

        //检测环境
        $this->_checkEnv();

        //参数
        $id = (int)$id;
        $version = (int)$version;

        if ($id < 0 || $version < 0) {
            return  null;
        }

        //获取存储对象
        $moduleName = $this->getModuleName();
        $typeName = $this->getName();
        $store = Factory::getInstance()->getStore($this);

        $data = null;
        $query = array();
        $fields = array();
        $query['_id'] = $id;
        if ($version) {
            $fields = array('_current_version', "_history.$version");
            $res = $store->findOne($query, $fields);
            if ($res) {
                if (!empty($res['_history'][$version])) {
                    $res['_data'] = $res['_history'][$version];
                    unset($res['_history']);
                } else {
                    $res = null;
                }
            }
        } else {
            $fields = array('_current_version', '_data');
            $res = $store->findOne($query, $fields);
        }

        if ($res) {
            //去除无效字段
            if ($attrNames) {
                $res['_data'] = Util\Helper::getData($res['_data'], $attrNames);
            }
            $data = $res;
        }
        return $data;
    }


    /**
     * 获取多条数据
     *
     * @param array $ids       数组Ids
     * @param array $attrNames 属性名称
     *
     * @return array
     */
    public function gets(array $ids,  array $attrNames = array())
    {
        //检测环境
        $this->_checkEnv();

        //获取存储对象
        $moduleName = $this->getModuleName();
        $typeName = $this->getName();
        $store = Factory::getInstance()->getStore($this);

        $data = null;

        $query = array();
        $query['_id'] = array('$in'=>array_unique($ids));

        $options = array();
        $options['fields'] = array('_data', '_current_version');

        $res = $store->find($query, $options);

        if ($res) {
            if ($attrNames) {
                foreach ($res as $key => $item) {
                    $res[$key] = Util\Helper::getData($item, $attrNames);
                }
            }
            $data = $res;
        }

        return $data;
    }

    /**
     * 删除数据
     *
     * @param number $id 数据ID
     *
     * @return int
     */
    public function del($id)
    {
        //检测环境
        $this->_checkEnv();
        try {
            //参数
            $id = (int)$id;
            //获取存储对象
            $moduleName = $this->getModuleName();
            $typeName = $this->getName();
            $store = Factory::getInstance()->getStore($this);

            $query = array();
            $query['_id'] = $id;
            // $deleteRes = $store->remove($query);
            $deleteRes = true;
            if (!$deleteRes) {
                throw new TypeError("Store id [". $id ."]  delete failure");
            }

            //索引相关
            //获取索引对象
            $index = Factory::getInstance()->getIndex($this);
            $indexRes = $index->del($id);

            if (!$indexRes) {
                throw new TypeError("Index Error, Reason: " . $index->getError());
            }
            return true;
        } catch (Exception $e) {
            $this->_error = $e->getMessage();
            return false;
        }

    }

    /**
     * 删除多条数据
     *
     * @param array $ids 数组Ids
     *
     * @return int
     */
    public function dels(array $ids)
    {
        //检测环境
        $this->_checkEnv();
        try {
            $ids = array_unique($ids);
            //获取存储对象
            $moduleName = $this->getModuleName();
            $typeName = $this->getName();
            $store = Factory::getInstance()->getStore($this);

            $query = array();
            $query['_id'] = array('$in'=>$ids);
            $deleteRes = $store->remove($query);

            if ($deleteRes) {
                throw new TypeError("Store ids [". join(',', $ids) ."]  delete failure");
            }

            //获取索引对象
            $index = Factory::getInstance()->getIndex($this);
            $indexRes = $index->dels($ids);

            if ($indexRes) {
                throw new TypeError("Index Error, Reason: " . $index->getError());
            }
            return true;
        } catch (Exception $e) {
            $this->_error = $e->getMessage();
            return false;
        }
        return $res;
    }


    /**
     * 获取当前数据总数
     *
     * @return int
     */
    public function getCount()
    {
        //检测环境
        $this->_checkEnv();

        //获取存储对象
        $moduleName = $this->getModuleName();
        $typeName = $this->getName();
        $store = Factory::getInstance()->getStore($this);
        $num = $store->count();
        return $num;
    }


    /**
     * 获取minId
     *
     * @return int
     */
    public function getMinId()
    {
        //检测环境
        $this->_checkEnv();

        //获取存储对象
        $moduleName = $this->getModuleName();
        $typeName = $this->getName();
        $store = Factory::getInstance()->getStore($moduleName, $typeName);

        $num = null;
        $query = array();
        $options = array();
        $options['fields'] = array('_id');
        $options['limit'] = 1;
        $options['sort'] = array('_id'=>1);
        $res = $store->find($query, $options);

        if (!empty($res)) {
            $res = array_shift($res);
            $num = $res['_id'];
        }
        return $num;
    }


    /**
     * 获取maxId
     *
     * @return int
     */
    public function getMaxId()
    {
        //检测环境
        $this->_checkEnv();

        //获取存储对象
        $moduleName = $this->getModuleName();
        $typeName = $this->getName();
        $store = Factory::getInstance()->getStore($this);

        $num = null;
        $query = array();
        $options = array();
        $options['fields'] = array('_id');
        $options['limit'] = 1;
        $options['sort'] = array('_id'=>-1);
        $res = $store->find($query, $options);

        if (!empty($res)) {
            $res = array_shift($res);
            $num = $res['_id'];
        }
        return $num;
    }


    /**
     * 获取indexScheme(这个方法写的有点死,后期再改)
     *
     * @param bool $hasSystem 是否包含系统的索引map
     *
     * @return array
     */
    public function getIndexAttrMaps($hasSystem = true)
    {
        $scheme = array();
        $indexAttrNames = $this->getIndexAttrNames();
        foreach ($indexAttrNames as $attrName) {
            $attr = $this->getMapping()->getAttr($attrName);
            $attrType = $attr->getType();
            $scheme[$attrName] = $attrType;
        }
        //加入系统字段
        if ($hasSystem) {
            $scheme['_created_at_ts'] = Attribute::TYPE_TIMESTAMP;
            $scheme['_version'] = Attribute::TYPE_INT;
            $scheme['_id'] = Attribute::TYPE_INT;
        }
        return $scheme;
    }

    /**
     * 返回错误码
     *
     * @return void
     */
    public function getError()
    {
        return $this->_error;
    }


    /**
     * 检测环境信息
     *
     * @return void
     */
    private function _checkEnv()
    {
        //清空异常信息
        $this->_error = null;

        if (!$this->getAttrNames()) {
            throw new TypeError("undefined the type mapping");
        }
        return true;
    }

    /**
     * 加载默认值
     *
     * @param array $data 加载默认数据
     *
     * @return void
     */
    private function _loadDefaultValue($data)
    {
        $tmpData = array();
        foreach ($this->getAttrNames() as $attrName) {
            $defaultValue = $this->getMapping()->getAttr($attrName)->getDefault();
            $tmpData[$attrName] = $defaultValue ;
        }
        return array_merge($tmpData, $data);
    }


    /**
     * 转换数据类型
     *
     * @param array $data 数据
     *
     * @return array
     */
    private function _convertDataType(array $data)
    {
        foreach ($data as $attrName => $val) {
            $type = $this->getMapping()->getAttr($attrName)->getType();
            $data[$attrName]  = Attribute::convertType($type, $val);
        }
        return $data;
    }


    /**
     * 创建类型文件夹
     *
     * @return void
     */
    private function _makeTypeFolder()
    {
        Util\Helper::makeDir($this->_getPath());
    }


    /**
     * 获取路径
     *
     * @return String
     */
    private function _getPath()
    {
        $path = ERS_CONFIG_DIR . DS . $this->_moduleName . DS . $this->_name;
        return $path;
    }

    /**
     * 获取配置文件路径
     *
     * @return String
     */
    private function _getConfigPath()
    {
        $configPath = $this->_getPath() . DS . ERS_TYPE_CONFIG_FILE;
        return $configPath;
    }

    /**
     * 获取mapping文件路径
     *
     * @return String
     */
    private function _getMappingPath()
    {
        $mappingPath  = $this->_getPath() . DS. ERS_TYPE_MAPPING_FILE;
        return $mappingPath;
    }
}