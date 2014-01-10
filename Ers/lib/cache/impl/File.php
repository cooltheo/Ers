<?php
/**
 * Cache Api
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
namespace Ers\Lib\Cache\Impl;
use Ers\Lib\Cache as Cache;
/**
 * This is a cache API
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class File implements Cache\CacheInterface
{
    protected $cacheDir;

    /**
     * 构造方法 
     * 
     * @param string $cacheDir 缓存文件夹
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = (string)$cacheDir;
        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0777, true) || !is_writable($this->cacheDir)) {
                throw new Exception($this->cacheDir . ' is not writable');
            }
        }
    } 
    
    
    /**
     * 获取缓存数据
     *
     * @param string $key     key
     * @param string $default 默认值
     *
     * @return string|boolean
     */
    public function get($key, $default = null)
    {
        if ($filename = $this->exists($key)) {
            $data = $this->_load($filename);
            if ($data['expired'] > time()) {
                return $data['data'];
            } else {
                @unlink($filename);
                return $default;
            }
        } else {
            return $default;
        }
    }
    
    
    /**
     * 添加缓存数据
     *
     * @param string $key        缓存的key
     * @param mixed  $value      缓存的value
     * @param int    $expireTime 过期时间
     *
     * @return boolean
     */
    public function add($key, $value, $expireTime = 3600)
    {
        if ($this->get($key) == null) {
            return $this->set($key, $value, $expireTime);
        }
    }
    
    
    /**
     * 更新缓存数据
     *
     * @param string $key        缓存的key
     * @param string $value      更新缓存数据
     * @param number $expireTime 过期时间
     *
     * @return boolean
     */
    public function set($key, $value, $expireTime = 3600)
    {   
        $filename = $this->_filename($key);
        $writeData = array(
            'data' => $value,
            'expired' => time() + (int) $expireTime,
        );
        $content = serialize($writeData);
        
        //检测文件夹路径有没有
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return file_put_contents($filename, $content);
    }
    
    /**
     * 删除缓存数据
     *
     * @param string $key 缓存的key
     *
     * @return boolean
     */
    public function del($key)
    {
        return @unlink($this->_filename($key));
    }
    /**
     * 检测缓存key是否存在
     *
     * @param type $key 缓存的key
     *
     * @return boolean
     */
    public function exists($key)
    {
        $filename = $this->_filename($key);
        return file_exists($filename) ? $filename : false;
    }
    
    
    /**
     * 清空所有数据
     * 
     * @return void
     */
    public function clear()
    {
        //先删除目录下的文件：
        $this->_delDir($this->cacheDir);
        //恢复多删的目录
        mkdir($this->cacheDir, 0777, true);
    }
    
    
    /**
     * 加载文件内容
     *
     * @param string $filename 文件名
     *
     * @return string
     */
    private function _load($filename)
    {
        return unserialize(file_get_contents($filename));
    }
    
    /**
     * 获取缓存文件的文件名
     *
     * @param string $key 缓存的key
     *
     * @return string
     */
    private function _filename($key)
    {
        $file = sha1($key) . '.php';
        $filename = $this->cacheDir . DIRECTORY_SEPARATOR . substr($file, 0, 1) . DIRECTORY_SEPARATOR .
            substr($file, 1, 1) . DIRECTORY_SEPARATOR . $file;
        return $filename;
    }
    
    
    /**
     * 删除文件夹 
     * 
     * @param string $path 文件夹路径
     * 
     * @return void
     */
    private function _delDir($path)
    {
        if (!is_dir($path)) {  
            return null;  
        }  
        $fh = opendir($path);  
        while (($row = readdir($fh)) !== false) {  
            if ($row == '.' || $row == '..') {  
                continue;  
            }  
            $fullpath = $path . DIRECTORY_SEPARATOR . $row;
            if (!is_dir($fullpath)) {  
                unlink($fullpath);  
            }  
            $this->_deldir($fullpath);
        }  
        closedir($fh);
        @rmdir($path);
        return true;
    }
}
