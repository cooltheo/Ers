<?php
/**
 * Interface Cache
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
namespace Ers\Lib\Cache;
/**
 * This class is responsible for the cache interface definition
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
Interface CacheInterface
{
    /**
     * 添加缓存数据
     *
     * @param string $key        key
     * @param mixed  $value      value
     * @param int    $expireTime 过期时间
     *
     * @return boolean
     */
    public function add($key, $value, $expireTime);
    /**
     * 获取缓存数据
     *
     * @param string $key     缓存的key
     * @param string $default 默认值
     *
     * @return string|boolean
    */
    public function get($key, $default = null);
    /**
     * 更新缓存数据
     *
     * @param string $key        缓存的key
     * @param string $value      更新缓存数据
     * @param string $expireTime 过期时间
     *
     * @return bool
    */
    public function set($key, $value, $expireTime = null);
    /**
     * 删除缓存数据
     *
     * @param string $key 缓存的key
     *
     * @return boolean
    */
    public function del($key);
    /**
     * 检查key是否存在
     * 
     * @param string $key 缓存的key
     * 
     * @return bool
     */
    public function exists($key);
    /**
     * 清空所有缓存数据
     *
     * @return void
     */
    public function clear();
}