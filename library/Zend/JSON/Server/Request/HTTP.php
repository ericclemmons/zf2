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
 * @package    Zend_JSON
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\JSON\Server\Request;

use Zend\JSON\Server\Request as JSONRequest;

/**
 * @uses       \Zend\JSON\Server\Request\Request
 * @category   Zend
 * @package    Zend_JSON
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class HTTP extends JSONRequest
{
    /**
     * Raw JSON pulled from POST body
     * @var string
     */
    protected $_rawJSON;

    /**
     * Constructor
     *
     * Pull JSON request from raw POST body and use to populate request.
     *
     * @return void
     */
    public function __construct()
    {
        $json = file_get_contents('php://input');
        $this->_rawJSON = $json;
        if (!empty($json)) {
            $this->loadJSON($json);
        }
    }

    /**
     * Get JSON from raw POST body
     *
     * @return string
     */
    public function getRawJSON()
    {
        return $this->_rawJSON;
    }
}
