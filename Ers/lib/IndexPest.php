<?php
/**
 * This is a Pest of derived classes
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
namespace Ers\Lib;
/**
 * IndexPest class
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class IndexPest extends Pest
{

    protected $_error;

    /**
     * Process body
     *
     * @param string $body
     *
     * @return string
     */
    protected function processBody($body)
    {
        $body = json_decode($body, true);
        if (isset($body['error_msg'])) {
            $this->setError($body['error_msg']);
            return null;
        } else {
            $this->setError(null);
        }
        return $body;
    }


    /**
     * 设置错误信息
     *
     * @param string $error 错误信息
     *
     * @return void
     */
    private function setError($error)
    {
        $this->_error = $error;
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }
}