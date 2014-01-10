<?php
/**
 * Interface Db
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
namespace Ers\Lib\Db;
/**
 * This class is responsible for the cache interface definition
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
Interface DbInterface
{
    /**
     * 切换db
     *
     * @param string $dbName 切换db
     *
     * @return void
     */
    public function selectDb($dbName);

    /**
     * 设置表的名称
     *
     * @param string $tableName 表名
     *
     * @return void
    */
    public function selectTable($tableName);

}