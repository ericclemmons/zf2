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
 * @package    Zend_Feed_Pubsubhubbub
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\Feed\PubSubHubbub\Model;

/**
 * @uses       \Zend\DB\Table\Table
 * @uses       \Zend\Registry
 * @category   Zend
 * @package    Zend_Feed_Pubsubhubbub
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ModelAbstract
{
    /**
     * Zend_Db_Table instance to host database methods
     *
     * @var \Zend\DB\Table\Table
     */
    protected $_db = null;
 
    /**
     * Constructor
     * 
     * @param  array $data 
     * @param  \Zend\DB\Table\AbstractTable $tableGateway 
     * @return void
     */
    public function __construct(\Zend\DB\Table\AbstractTable $tableGateway = null)
    {
        if (is_null($tableGateway)) {
            $parts = explode('_', get_class($this));
            $table = strtolower(array_pop($parts));
            $this->_db = new \Zend\DB\Table\Table($table);
        } else {
            $this->_db = $tableGateway;
        }
    }

}
