<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\Serializer\Adapter;

use Zend\Serializer\Adapter as SerializationAdapter;

/**
 * @uses       \Zend\Serializer\Adapter
 * @uses       \Zend\Serializer\Exception
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class AbstractAdapter implements SerializationAdapter
{
    /**
     * Serializer options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Constructor
     *
     * @param array|Zend\Config\Config $opts Serializer options
     */
    public function __construct($opts = array()) 
    {
        $this->setOptions($opts);
    }

    /**
     * Set serializer options
     *
     * @param  array|Zend\Config\Config $opts Serializer options
     * @return Zend\Serializer\Adapter\AbstractAdapter
     */
    public function setOptions($opts) 
    {
        if ($opts instanceof \Zend\Config\Config) {
            $opts = $opts->toArray();
        } else {
            $opts = (array) $opts;
        }

        foreach ($opts as $k => $v) {
            $this->setOption($k, $v);
        }
        return $this;
    }

    /**
     * Set a serializer option
     *
     * @param  string $name Option name
     * @param  mixed $value Option value
     * @return Zend\Serializer\Adapter\AbstractAdapter
     */
    public function setOption($name, $value) 
    {
        $this->_options[(string) $name] = $value;
        return $this;
    }

    /**
     * Get serializer options
     *
     * @return array
     */
    public function getOptions() 
    {
        return $this->_options;
    }

    /**
     * Get a serializer option
     *
     * @param  string $name
     * @return mixed
     * @throws Zend\Serializer\Exception
     */
    public function getOption($name) 
    {
        $name = (string) $name;
        if (!array_key_exists($name, $this->_options)) {
            throw new \Zend\Serializer\Exception('Unknown option name "'.$name.'"');
        }

        return $this->_options[$name];
    }
}
