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
 * @package    IfwPsn_Vendor_Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Json.php 481 2015-11-03 13:28:23Z timoreithde $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** IfwPsn_Vendor_Zend_Json */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Json.php';

/** IfwPsn_Vendor_Zend_Controller_Front */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Controller/Front.php';

/** IfwPsn_Vendor_Zend_View_Helper_Abstract.php */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Helper/Abstract.php';

/**
 * Helper for simplifying JSON responses
 *
 * @package    IfwPsn_Vendor_Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_View_Helper_Json extends IfwPsn_Vendor_Zend_View_Helper_Abstract
{
    /**
     * Encode data as JSON, disable layouts, and set response header
     *
     * If $keepLayouts is true, does not disable layouts.
     * If $encodeJson is false, does not JSON-encode $data
     *
     * @param  mixed $data
     * @param  bool $keepLayouts
     * NOTE:   if boolean, establish $keepLayouts to true|false
     *         if array, admit params for IfwPsn_Vendor_Zend_Json::encode as enableJsonExprFinder=>true|false
     *         this array can contains a 'keepLayout'=>true|false and/or 'encodeData'=>true|false
     *         that will not be passed to IfwPsn_Vendor_Zend_Json::encode method but will be used here
     * @param  bool $encodeData
     * @return string|void
     */
    public function json($data, $keepLayouts = false, $encodeData = true)
    {
        $options = array();
        if (is_array($keepLayouts)) {
            $options = $keepLayouts;

            $keepLayouts = false;
            if (array_key_exists('keepLayouts', $options)) {
                $keepLayouts = $options['keepLayouts'];
                unset($options['keepLayouts']);
            }

            if (array_key_exists('encodeData', $options)) {
                $encodeData = $options['encodeData'];
                unset($options['encodeData']);
            }
        }

        if ($encodeData) {
            $data = IfwPsn_Vendor_Zend_Json::encode($data, null, $options);
        }
        if (!$keepLayouts) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Layout.php';
            $layout = IfwPsn_Vendor_Zend_Layout::getMvcInstance();
            if ($layout instanceof IfwPsn_Vendor_Zend_Layout) {
                $layout->disableLayout();
            }
        }

        $response = IfwPsn_Vendor_Zend_Controller_Front::getInstance()->getResponse();
        $response->setHeader('Content-Type', 'application/json', true);
        return $data;
    }
}
