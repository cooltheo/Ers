<?php
include "../Bootstrap.php";
#################APP####################
//获取应用实例
$app = Ers\Core\App::getInstance();
//获取系统配置
$systemConfig = $app::getConfig();
$module = $app->getModule('web');
$type = $module->getType('dict');
$config = $type->getConfig();
$mapping = $type->getMapping();

$fieldNames = $type->getAttrNames();

$data = array('title'=> "411aa111", 'content'=>'dsadsa');
$type->valid($data);


$moduleName = $module->getName();
$typeName = $type->getName();
//Ers\Core\Factory::getInstance()->getStore($moduleName, $typeName);
//$res = $type->add($data);
//$res = $type->update(25, $data);
//var_dump($res, $type->getError());

//$data = $type->get(29, array('title'));
//var_dump($data);
//$data = $type->gets(array(1,2,3), array('title'));
//var_dump($data);
//$count = $type->getCount();
//var_dump($count);
//$res = $type->dels(array(1,2,5));
//$res = $type->getMinId();
//$res = $type->getMaxId();//var_dump($res);
//$cache = Ers\Core\Factory::getInstance()->getCache($moduleName, $typeName);
//$cache->add('aa', 111);
//$res = $cache->get('aa');
//var_dump($res);
//$cache->del('aa');
//$cache->clear();
//$res = $cache->get('aa');
//var_dump($type->getIndexAttrNames());
//$index = Ers\Core\Factory::getInstance()->getIndex($type);
//var_dump($index);
//var_dump($type->getIndexAttrMaps());
//$index = Ers\Core\Factory::getInstance()->getIndex($type);
//var_dump($index);

//$res = $type->del(56);
//$res = $type->dels(array(321, 23, 24));
//var_dump($res, $type->getError());
