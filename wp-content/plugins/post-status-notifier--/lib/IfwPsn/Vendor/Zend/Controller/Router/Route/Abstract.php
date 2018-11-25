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
 * @package    IfwPsn_Vendor_Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Abstract.php 481 2015-11-03 13:28:23Z timoreithde $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see IfwPsn_Vendor_Zend_Controller_Router_Route_Interface
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Controller/Router/Route/Interface.php';

/**
 * Abstract Route
 *
 * Implements interface and provides convenience methods
 *
 * @package    IfwPsn_Vendor_Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class IfwPsn_Vendor_Zend_Controller_Router_Route_Abstract implements IfwPsn_Vendor_Zend_Controller_Router_Route_Interface
{
    /**
     * URI delimiter
     */
    const URI_DELIMITER = '/';

    /**
     * Wether this route is abstract or not
     *
     * @var boolean
     */
    protected $_isAbstract = false;

    /**
     * Path matched by this route
     *
     * @var string
     */
    protected $_matchedPath = null;

    /**
     * Get the version of the route
     *
     * @return integer
     */
    public function getVersion()
    {
        return 2;
    }

    /**
     * Set partially matched path
     *
     * @param  string $path
     * @return void
     */
    public function setMatchedPath($path)
    {
        $this->_matchedPath = $path;
    }

    /**
     * Get partially matched path
     *
     * @return string
     */
    public function getMatchedPath()
    {
        return $this->_matchedPath;
    }

    /**
     * Check or set wether this is an abstract route or not
     *
     * @param  boolean $flag
     * @return boolean
     */
    public function isAbstract($flag = null)
    {
        if ($flag !== null) {
            $this->_isAbstract = $flag;
        }

        return $this->_isAbstract;
    }

    /**
     * Create a new chain
     *
     * @param  IfwPsn_Vendor_Zend_Controller_Router_Route_Abstract $route
     * @param  string                                $separator
     * @return IfwPsn_Vendor_Zend_Controller_Router_Route_Chain
     */
    public function chain(IfwPsn_Vendor_Zend_Controller_Router_Route_Abstract $route, $separator = '/')
    {
        require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Controller/Router/Route/Chain.php';

        $chain = new IfwPsn_Vendor_Zend_Controller_Router_Route_Chain();
        $chain->chain($this)->chain($route, $separator);

        return $chain;
    }
}
