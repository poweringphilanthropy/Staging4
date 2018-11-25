<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: RequestVars.php 356 2014-12-01 22:06:50Z timoreithde $
 * @package  IfwPsn_Wp
 */
class IfwPsn_Zend_Controller_Router_Route_RequestVars implements IfwPsn_Vendor_Zend_Controller_Router_Route_Interface
{
    /**
     * 
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;
    
    protected $_current = array();
 
    /**
     * Instantiates route based on passed IfwPsn_Vendor_Zend_Config structure
     */
    public static function getInstance(IfwPsn_Vendor_Zend_Config $config)
    {
        // not supported
    }
    
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }
 
    /**
     * Matches a user submitted path with a previously defined route.
     * Assigns and returns an array of defaults on a successful match.
     *
     * @param string Path used to match against this routing map
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path)
    {
        $frontController = IfwPsn_Zend_Controller_Front::getInstance();
        $request = $frontController->getRequest();
        /* @var $request IfwPsn_Vendor_Zend_Controller_Request_Http */

        $baseUrl = $request->getBaseUrl();
        if (strpos($baseUrl, 'index.php') !== false) {
            $url = str_replace('index.php', '', $baseUrl);
            $request->setBaseUrl($url);
        }
         
        $params = $request->getParams();

        if (array_key_exists('mod', $params) ||
            array_key_exists('controller', $params) ||
            array_key_exists('action', $params) ||
            array_key_exists('page', $params)
        ) {

            $module = $request->getParam('mod', $frontController->getDefaultModule());
            $controller = $request->getParam('controller', $frontController->getDefaultControllerName());
            $action = $request->getParam('action', $frontController->getDefaultAction());

            $result = array('module' => $module,
                'controller' => $controller,
                'action' => $action,
                );
            $this->_current = $result;

            return $result;
        }
        return false;
    }

    /**
     * Assembles a URL path defined by this route
     *
     * @param array $data An array of variable and value pairs used as parameters
     * @param bool $reset
     * @param bool $encode
     * @return string Route path with user submitted parameters
     */
    public function assemble($data=array(), $reset=false, $encode = false)
    {
        $frontController = IfwPsn_Vendor_Zend_Controller_Front::getInstance();

        if(!array_key_exists('mod', $data) && !$reset
            && array_key_exists('mod', $this->_current)
            && $this->_current['mod'] != $frontController->getDefaultModule()) {
            $data = array_merge(array('mod'=>$this->_current['mod']), $data);
        }
        if(!array_key_exists('controller', $data) && !$reset
            && array_key_exists('controller', $this->_current)
            && $this->_current['controller'] != $frontController->getDefaultControllerName()) {
            $data = array_merge(array('controller'=>$this->_current['controller']), $data);
        }
        if(!array_key_exists('action', $data) && !$reset
            && array_key_exists('action', $this->_current)
            && $this->_current['action'] != $frontController->getDefaultAction()) {
            $data = array_merge(array('action'=>$this->_current['action']), $data);
        }

        if(!empty($data)) {
            $querydata = array();
            if (isset($data['page'])) {
                $url = IfwPsn_Wp_Proxy_Admin::getOptionsBaseUrl();
                $querydata['page'] = $data['page'];
                unset($data['page']);
            } elseif (isset($data['adminpage'])) {
                $url = IfwPsn_Wp_Proxy_Admin::getAdminPageBaseUrl();
                $querydata['page'] = $data['adminpage'];
                unset($data['adminpage']);
            } elseif (isset($data['editpage'])) {
                $url = 'edit.php';
                if (isset($data['posttype'])) {
                    $querydata['post_type'] = $data['posttype'];
                    unset($data['posttype']);
                }
                $querydata['page'] = $data['editpage'];
                unset($data['editpage']);
            }
            if (isset($data['module'])) {
                $querydata['mod'] = $data['module'];
                unset($data['module']);
            }
            if (isset($data['action'])) {
                $querydata[$this->_pm->getConfig()->getActionKey()] = $data['action'];
                unset($data['action']);
            }
            $querydata = array_merge($querydata, $data);

            $url .= '?' . http_build_query($querydata, '', '&');
        }

        return $url;
    }
}