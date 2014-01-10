<?php
include "../Bootstrap.php";
#################APP####################
//获取应用实例
$app = Ers\Core\App::getInstance();

//获取所有的模块名称
$moduleNames = $app->getModuleNames();

//获取单个模块实例
$module = $app->getModoule($modoluleName);

//删除一个模块
$app->delModule($moduleName);

//获取系统配置
$systemConfig = $app::getConfig(); 

//记录日志
$app::log($msg, $logError);
#################Module##################

//获取所有模块下的类型名称
$typeNames = $module->getTypeNames();

//获取单个类型实例
$type  = $module->getType($typeName);

//删除一个类型
$module->delType($typeName);
#################Type####################           
#-----------------------------mapping attribute  ----------------------#
//获取类型下mapping
$mapping = $type->getMapping();

//mapping增加属性
$mapping->addAttr($attrbute);

//删除一个属性
$mapping->delAttr($attrName);

//获取mapping下的一个属性
$mapping->getAttr();

//设置一个属性的值
$attribute->set($key, $value); || $attribute->setKey($value);

//获取一个属性的值
$attribute->get($key); || $attribute->getKey(); 

//获取属性数组
$attrData = $attribute->toArray();


//获取mapping数组
$mapping->toArray();

//存储mapping
$mapping->save();


//遍历mapping中的属性
foreach ($mapping as $key => $attribute) {
	
}
#-------------------------------config--------------------------------#
//获取type Config
$config = $type->getConfig();

//获取config中某一个值()
$config->get($key); || $config->getKey(); 

//设置config中的某一个值
$config->set($key); || $config->set($key, $value);

//存储配置
$config->save();
#------------------------------data-----------------------------------#
//获取字段名称
$type->getFieldNames();

//获取单个数据
$type->get($id, array $fieldNames, $version);

//获取数个数据
$type->gets($ids, array $fieldNames);

//获取一个范围区间的数据
$type->getRange($minId, $maxId, $fieldNames);

//获取总数
$type->getCount();

//删除单个数据
$type->del($id);

//删除多个数据
$type->dels(array $ids);

//增加数据
$type->add(array $data);

//更新数据
$type->update($data);

//验证数据
$type->valid($data);

//获取数据版本
$type->getVersion($id);

//获取最小ID
$type->minId()

//获取最大ID
$type->maxId();

//获取最近一次的版本变更情况
$type->getLastChange($id);

//获取全局状态
$type->status();

//重建索引
$type->rebuildIndex();

//清空索引
$type->clearIndex();

//清空数据缓存
$type->clearCache()
#------------------------------Search----------------------------------#
//获取search对象
$search = $type->getSearch();

//设置取值范围
$search->setLimit(1, 20);

//设置过滤条件
$search->setFilter($attr, $value);

//新建查询对象
$query = new BoolQuery();
$query->must($field, $value);
$query->mustNot($field, $value);

$search->query($query);