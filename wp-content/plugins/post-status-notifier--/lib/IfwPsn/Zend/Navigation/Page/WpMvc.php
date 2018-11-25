<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: WpMvc.php 356 2014-12-01 22:06:50Z timoreithde $
 * @package  IfwPsn_Wp
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Navigation/Page/Mvc.php';

class IfwPsn_Zend_Navigation_Page_WpMvc extends IfwPsn_Vendor_Zend_Navigation_Page_Mvc
{
    /**
     * @var string
     */
    protected $_page;

    /**
     * @var string
     */
    protected $_adminpage;

    /**
     * @var string
     */
    protected $_editpage;

    /**
     * @var string
     */
    protected $_posttype;



    /**
     * Returns href for this page
     *
     * This method uses {@link IfwPsn_Vendor_Zend_Controller_Action_Helper_Url} to assemble
     * the href based on the page's properties.
     *
     * @return string  page href
     */
    public function getHref()
    {
        if ($this->_hrefCache) {
            return $this->_hrefCache;
        }

        if (null === self::$_urlHelper) {
            self::$_urlHelper =
                IfwPsn_Vendor_Zend_Controller_Action_HelperBroker::getStaticHelper('Url');
        }

        $params = $this->getParams();

        if ($param = $this->getModule()) {
            $params['module'] = $param;
        }

        if ($param = $this->getController()) {
            $params[IfwPsn_Zend_Controller_Front::getInstance()->getRequest()->getControllerKey()] = $param;
        }

        if ($param = $this->getAction()) {
            $params[IfwPsn_Zend_Controller_Front::getInstance()->getRequest()->getActionKey()] = $param;
        }
        
        if ($param = $this->getPage()) {
            $params['page'] = $param;
        }

        if ($param = $this->getAdminpage()) {
            $params['adminpage'] = $param;
        }

        if ($param = $this->getEditpage()) {
            $params['editpage'] = $param;
        }

        if ($param = $this->getPosttype()) {
            $params['posttype'] = $param;
        }

        $url = self::$_urlHelper->url($params,
                                      $this->getRoute(),
                                      $this->getResetParams(),
                                      $this->getEncodeUrl());

        // Add the fragment identifier if it is set
        $fragment = $this->getFragment();       
        if (null !== $fragment) {
            $url .= '#' . $fragment;
        }         

        return $this->_hrefCache = $url;
    }
        


    /**
     * Returns whether page should be considered active or not
     *
     * This method will compare the page properties against the request object
     * that is found in the front controller.
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          active if any child pages are active. Default is
     *                          false.
     * @return bool             whether page should be considered active or not
     */
    public function isActive($recursive = false)
    {
        if (null === $this->_active) {
            $front     = IfwPsn_Vendor_Zend_Controller_Front::getInstance();
            $request   = $front->getRequest();
            $reqParams = array();
            if ($request) {
                $reqParams = $request->getParams();
                if (!array_key_exists('module', $reqParams)) {
                    $reqParams['module'] = $front->getDefaultModule();
                }
            }

            $myParams = $this->_params;

            if ($this->_route
                && method_exists($front->getRouter(), 'getRoute')
            ) {
                $route = $front->getRouter()->getRoute($this->_route);
                if (method_exists($route, 'getDefaults')) {
                    $myParams = array_merge($route->getDefaults(), $myParams);
                }
            }

            if (null !== $this->_module) {
                $myParams['module'] = $this->_module;
            } elseif (!array_key_exists('module', $myParams)) {
                $myParams['module'] = $front->getDefaultModule();
            }

            if (null !== $this->_controller) {
                $myParams['controller'] = $this->_controller;
            } elseif (!array_key_exists('controller', $myParams)) {
                $myParams['controller'] = $front->getDefaultControllerName();
            }

            if (null !== $this->_action) {
                $myParams[$request->getActionKey()] = $this->_action;
            } elseif (!array_key_exists($request->getActionKey(), $myParams)) {
                $myParams[$request->getActionKey()] = $front->getDefaultAction();
            }

            if (!isset($reqParams[$request->getActionKey()])) {
                $reqParams[$request->getActionKey()] = $front->getDefaultAction();
            }

            foreach ($myParams as $key => $value) {
                if (null === $value) {
                    unset($myParams[$key]);
                }
            }

            $exactActiveMatch = $this->get('exactActiveMatch');

            if (
                count(array_intersect_assoc($reqParams, $myParams)) == count($myParams)
                || (empty($exactActiveMatch) && $reqParams['module'] == $myParams['module'] && isset($reqParams['controller']) && $reqParams['controller'] == $myParams['controller'])
            ) {
                $this->_active = true;

                return true;
            }

            $this->_active = false;
        }

        return parent::isActive($recursive);
    }

    /**
     * @param string $page
     */
    public function setPage($page)
    {
        $this->_page = $page;
    }

    /**
     * @return string
     */
    public function getPage()
    {
        return $this->_page;
    }

    /**
     * @param string $adminpage
     */
    public function setAdminpage($adminpage)
    {
        $this->_adminpage = $adminpage;
    }

    /**
     * @return string
     */
    public function getAdminpage()
    {
        return $this->_adminpage;
    }

    /**
     * @return string
     */
    public function getEditpage()
    {
        return $this->_editpage;
    }

    /**
     * @param string $editpage
     */
    public function setEditpage($editpage)
    {
        $this->_editpage = $editpage;
    }

    /**
     * @return string
     */
    public function getPosttype()
    {
        return $this->_posttype;
    }

    /**
     * @param string $posttype
     */
    public function setPosttype($posttype)
    {
        $this->_posttype = $posttype;
    }

}
