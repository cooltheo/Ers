<?php
/**
 * ErsHelper Class
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
namespace Ers\Util;

/**
 * Helper class
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class Helper
{
    /**
     * 获取子文件夹名称
     *
     * @param string $path 文件夹路径
     *
     * @return array
     */
    public static function getDirNames($path)
    {
        $data = array();
        $path .= "*";
        foreach (glob($path) as $filename) {
            if (is_dir($filename)) {
                $dirname  = basename($filename);
                $data[] = $dirname;
            }
        }
        return $data;
    }

    /**
     * 创建文件夹
     *
     * @param string $path 文件夹路径
     * @param string $per  权限
     *
     * @return array
     */
    public static function makeDir($path, $per = 0777)
    {
        if (!file_exists($path)) {
            return mkdir($path, $per);
        }
        return false;
    }

    /**
     * 创建文件
     *
     * @param string $path 文件路径
     * @param string $data 文件内容
     *
     * @return void
     */
    public static function makeFile($path, $data='')
    {
        return file_put_contents($path, $data);
    }


    /**
     * 获取指定字段数据
     *
     * @param array  $sourceData 来源数据
     * @param array  $fields     字段数据
     * @param string $perfix     忽略前缀
     *
     * @return array
     */
    public static function getData(array $sourceData, array $fields, $perfix = '_')
    {
        if (empty($sourceData) || empty($fields)) {
            return $sourceData;
        }

        foreach ($sourceData as $field => $value) {
            if (!empty($perfix)) {
                if (strpos($field, $perfix) === 0) {
                    continue;
                }
            }
            if (!in_array($field, $fields)) {
                unset($sourceData[$field]);
            }
        }
        return $sourceData;
    }
}