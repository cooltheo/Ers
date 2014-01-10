<?php
/**
 * ErsValidation Class
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
 * Validation class
 *
 * @category System
 * @package  Ers
 * @author   Alex Zhou <alex.zhou@anychem.com>
 * @license  http://www.anychem.com/license  AnyChem Software Distribution
 * @link     http://www.anychem.com
 */
class Validation
{
    /**
    * Checks that a string contains something other than whitespace
    *
    * Returns true if string contains something other than whitespace
    *
    * @param mixed $check Value to check
    *
    * @return boolean
    */
    public static function noBlank($check)
    {
        $regex = '/\S+/m';
        return self::_check($check, $regex);
    }

    /**
     * Check string value wheather no value is to be empty
     *
     * @param mixed $check checked value
     *
     * @return bool
     */
    public static function isEmpty($check)
    {
        if (is_null($check)) {
            return true;
        }
        if (is_string($check) && !isset($check{0})) {
            return true;
        }
        return false;
    }

    /**
     * 确定指定字符串不是空字符串
     *
     * @param mixed $check 待检查的值
     *
     * @return boolean
     */
    public static function isNotEmpty($check)
    {
        if (is_null($check)) {
            return false;
        }
        if (is_string($check) && !isset($check{0})) {
            return false;
        }
        return true;
    }

    /**
     * Validates for an email address
     *
     * @param string $check value to check
     *
     * @return boolean
     */
    public static function email($check)
    {
        $regex = "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@"
                . "([a-z0-9\-]+\.)+[a-z]{2,6}$/ix";
        return self::_check($check, $regex);
    }

    /**
     * Validation of an IP address.
     * both: Check both IPv4 and IPv6,
     *       return true if the supplied address matches either version
     * IPv4: Version 4 (Eg: 127.0.0.1, 192.168.10.123, 203.211.24.8)
     * IPv6: Version 6 (Eg: ::1, 2001:0db8::1428:57ab)
     *
     * @param string $check   The string to test
     * @param string $version The IP Version to test against
     *
     * @return boolean
     */
    public static function ip($check, $version = 'both')
    {
        $success = false;
        $type = strtolower($version);
        if ($type === 'ipv4' || $type === 'both') {
            $success |= self::_ipV4($check);
        }
        if ($type === 'ipv6' || $type === 'both') {
            $success |= self::_ipV6($check);
        }
        return (bool)$success;
    }

    /**
     * Checks that a value is a valid URL according to
     * http://www.w3.org/Addressing/URL/url-spec.txt
     *
     * @param string $check  Value to check
     * @param string $strict Require URL to be prefixed by a valid scheme
     *                       (one of http(s)/ftp(s)/file/news/gopher)
     *
     * @return boolean
     */
    public static function url($check, $strict = false)
    {
        $ipv4 = self::_populateIp('ipv4');
        $ipv6 = self::_populateIp('ipv6');
        $validChars = '([' . preg_quote('!"$&\'()*+,-.@_:;=~') . '\/0-9a-z\p{L}\p{N}]|(%[0-9a-f]{2}))';
        $hostname = '(?:[a-z0-9][-_a-z0-9]*\.)*(?:[a-z0-9][-_a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)';

        $regex = '/^(?:(?:https?|ftps?|file|news|gopher):\/\/)' . (!empty($strict) ? '' : '?') .
                '(?:' . $ipv4 . '|\[' . $ipv6 . '\]|' . $hostname . ')' .
                '(?::[1-9][0-9]{0,4})?' .
                '(?:\/?|\/' . $validChars . '*)?' .
                '(?:\?' . $validChars . '*)?' .
                '(?:#' . $validChars . '*)?$/iu';
        return self::_check($check, $regex);
    }

    /**
     * Checks whether the length of a string is smaller or equal to a maximal length.
     *
     * @param string $check Value to check
     * @param int    $max   The maximal string length
     *
     * @return boolean
     */
    public static function maxLength($check, $max)
    {
        $length = mb_strlen($check);
        return ($length <= $max);
    }

    /**
     * Checks whether the length of a string is greater or equal to a minimal length.
     *
     * @param string $check Value to check
     * @param int    $min   The manimal string length
     *
     * @return boolean
     */
    public static function minLength($check, $min)
    {
        $length = mb_strlen($check);
        return ($length >= $min);
    }

    /**
     * Checks that a string length is within s specified range.
     * Spaces are included in the character count.
     * Returns true is string matches value min, max, or between min and max,
     *
     * @param string $check Value to check for length
     * @param int    $min   Minimum value in range (inclusive)
     * @param int    $max   Maximum value in range (inclusive)
     *
     * @return boolean
     */
    public static function lengthBetween($check, $min, $max)
    {
        $length = mb_strlen($check);
        return ($min <= $length && $length <= $max);
    }

    /**
     * Check a value is within a range
     *
     * @param int $check the number value
     * @param int $min   the mini value
     * @param int $max   the max value
     *
     * @return bool
     */
    public static function valueBetween($check, $min, $max)
    {
        if (self::int($check)) {
            return ($min <= $check && $check <= $max);
        }
        return false;
    }

    /**
     * Checks a value is bigger than the minimum value
     *
     * @param int $check the number value
     * @param int $min   the minimum value
     *
     * @return boolean
     */
    public static function minValue($check, $min)
    {
        if (self::int($check)) {
            return ($min <= $check);
        }
        return false;
    }

    /**
     * Checks a value is smaller than the maximum value
     *
     * @param int $check the number value
     * @param int $max   the minimum value
     *
     * @return boolean
     */
    public static function maxValue($check, $max)
    {
        if (self::int($check)) {
            return ($check <= $max);
        }
        return false;
    }

    /**
     * Checks that if a string is a valid integer string, doesn't consider
     * the overflow situations.
     *
     * @param string $check Value to check
     *
     * @return boolean
     */
    public static function int($check)
    {
        return preg_match('@^[-]?[0-9]+$@', $check) === 1;
    }

    /**
     * Checks that a string contains only integer or letters
     *
     * @param string $check Value to check
     *
     * @return boolean
     */
    public static function alphanumeric($check)
    {
        if (empty($check) && $check != '0') {
            return false;
        }
        $regex = '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu';
        return self::_check($check, $regex);
    }

    /**
     * Checks that a value is a valid uuid - http://tools.ietf.org/html/rfc4122
     *
     * @param string $check Value to check
     *
     * @return boolean
     */
    public static function uuid($check)
    {
        $regex = '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i';
        return self::_check($check, $regex);
    }

    /**
     * Validation for CAS(Chemical Abstracts Service)
     *
     * @param string $check string for check
     *
     * @return boolean
     */
    public static function casNumber($check)
    {
        $regex = '/^(\p{Nd}){2,7}-(\p{Nd}){2}-(\p{Nd}){1}$/mx';
        return self::_check($check, $regex);
    }

    /**
     * Validation for datetime
     *
     * @param string $check string for check
     *
     * @return boolean
     */
    public static function datetime($check)
    {
        $ret = date_parse($check);
        if ($ret["error_count"] == "0") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validation for tel or mobile or fax
     *
     * @param string $check string for check
     *
     * @return bool
     */
    public static function isTel($check)
    {
        $regex = '/\d+([\d -]+)\d+/';
        return self::_check($check, $regex);
    }

    /**
     * Validation of IPv4 addresses.
     *
     * @param string $check Ip v4 to test
     *
     * @return boolean
     */
    private static function _ipV4($check)
    {
        $regex = '/^'. self::_populateIp('ipv4') . '$/';
        return self::_check($check, $regex);
    }

    /**
     * Validation of IPv6 addresses.
     *
     * @param string $check Ip v6 to test
     *
     * @return boolean
     */
    private static function _ipV6($check)
    {
        $regex = '/^'. self::_populateIp('ipv6') . '$/';
        return self::_check($check, $regex);
    }

    /**
     * Lazily popualate the IP address patterns used for validations
     *
     * @param string $version The version of ip
     *
     * @return string
     */
    private static function _populateIp($version = 'ipv4')
    {
        $type = strtolower($version);
        if ($type === 'ipv6') {
            $regex = '((([0-9A-Fa-f]{1,4}:){7}(([0-9A-Fa-f]{1,4})|:))|'
                . '(([0-9A-Fa-f]{1,4}:){6}(:|((25[0-5]|2[0-4]\d|[01]?\d{1,2})'
                . '(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})|(:[0-9A-Fa-f]{1,4})))'
                . '|(([0-9A-Fa-f]{1,4}:){5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})'
                . '(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4})'
                . '{1,2})))|(([0-9A-Fa-f]{1,4}:)'
                . '{4}(:[0-9A-Fa-f]{1,4}){0,1}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})'
                . '(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))'
                . '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)'
                . '{3}(:[0-9A-Fa-f]{1,4}){0,2}'
                . '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|'
                . '2[0-4]\d|[01]?\d{1,2})){3})?)|'
                . '((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)'
                . '{2}(:[0-9A-Fa-f]{1,4}){0,3}'
                . '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?'
                . '\d{1,2}))'
                . '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)'
                . '(:[0-9A-Fa-f]{1,4})'
                . '{0,4}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|'
                . '[01]?\d{1,2})){3})?)'
                . '|((:[0-9A-Fa-f]{1,4}){1,2})))|(:(:[0-9A-Fa-f]{1,4}){0,5}'
                . '((:((25[0-5]|2[0-4]'
                . '\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|'
                . '((:[0-9A-Fa-f]{1,4})'
                . '{1,2})))|(((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d'
                . '|[01]?\d{1,2})){3})))(%.+)?';
            return $regex;
        }
        if ($type === 'ipv4') {
            $regex = '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.)'
                . '{3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])';
            return $regex;
        }
    }

    /**
     * Runs a regular expression match.
     *
     * @param string $check value to check
     * @param string $regex regular expression
     *
     * @return boolean
     */
    private static function _check($check, $regex)
    {
        if (preg_match($regex, $check)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check the $str weather match the reg
     *
     * @param string $str string for match
     * @param string $reg expression for regex
     *
     * @return bool
     */
    public static function match($str, $reg)
    {
        if (preg_match($reg, $str)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check the $str whether a string is utf8 encoding
     *
     * @param string $string string for match
     *
     * @return bool
     */
    public static function isUtf8($string)
    {
        return preg_match(
            '%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
            )*$%xs',
            $string
        );
    }

    /**
     * Checks if an array is associative, returns true for yes, or not.
     * some examples as below,
     *     var_dump(is_assoc_array(array('a', 'b', 'c'))); // false
     *     var_dump(is_assoc_array(array("0" => 'a', "1" => 'b', "2" => 'c'))); // false
     *     var_dump(is_assoc_array(array("1" => 'a', "0" => 'b', "2" => 'c'))); // true
     *     var_dump(is_assoc_array(array("a" => 'a', "b" => 'b', "c" => 'c'))); // true
     *
     * @param array $var the array to be checked
     *
     * @return boolean
     */
    public static function isAssocArray(array $var)
    {
        return array_diff_assoc(array_keys($var), range(0, sizeof($var)))
        ? true
        : false;
    }

    /**
     * 验证是否是时间戳
     *
     * @param string $timestamp 时间戳
     *
     * @return bool
     */
    public static function isTimestamp($timestamp)
    {
        if (strtotime(date('m-d-Y H:i:s', $timestamp)) === $timestamp) {
            return $timestamp;
        }
        return false;
    }
}//end of Validation.php
?>
