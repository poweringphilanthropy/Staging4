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
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Mail.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Resource/ResourceAbstract.php';

/**
 * Resource for setting up Mail Transport and default From & ReplyTo addresses
 *
 * @uses       IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Application_Resource_Mail extends IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
{

    /**
     * @var IfwPsn_Vendor_Zend_Mail_Transport_Abstract
     */
    protected $_transport;

    public function init()
    {
        return $this->getMail();
    }

    /**
     *
     * @return IfwPsn_Vendor_Zend_Mail_Transport_Abstract|null
     */
    public function getMail()
    {
        if (null === $this->_transport) {
            $options = $this->getOptions();
            foreach ($options as $key => $option) {
                $options[strtolower($key)] = $option;
            }
            $this->setOptions($options);

            if (isset($options['transport'])
                && !is_numeric($options['transport'])
            ) {
                $this->_transport = $this->_setupTransport($options['transport']);
                if (!isset($options['transport']['register'])
                    || $options['transport']['register'] == '1'
                    || (isset($options['transport']['register'])
                        && !is_numeric($options['transport']['register'])
                        && (bool)$options['transport']['register'] == true)
                ) {
                    IfwPsn_Vendor_Zend_Mail::setDefaultTransport($this->_transport);
                }
            }

            $this->_setDefaults('from');
            $this->_setDefaults('replyTo');
        }

        return $this->_transport;
    }

    protected function _setDefaults($type)
    {
        $key = strtolower('default' . $type);
        $options = $this->getOptions();

        if (isset($options[$key]['email'])
            && !is_numeric($options[$key]['email'])
        ) {
            $method = array('IfwPsn_Vendor_Zend_Mail', 'setDefault' . ucfirst($type));
            if (isset($options[$key]['name'])
                && !is_numeric(
                    $options[$key]['name']
                )
            ) {
                call_user_func(
                    $method, $options[$key]['email'], $options[$key]['name']
                );
            } else {
                call_user_func($method, $options[$key]['email']);
            }
        }
    }

    protected function _setupTransport($options)
    {
        if (!isset($options['type'])) {
            $options['type'] = 'sendmail';
        }

        $transportName = $options['type'];
        if (!IfwPsn_Vendor_Zend_Loader_Autoloader::autoload($transportName)) {
            $transportName = ucfirst(strtolower($transportName));

            if (!IfwPsn_Vendor_Zend_Loader_Autoloader::autoload($transportName)) {
                $transportName = 'IfwPsn_Vendor_Zend_Mail_Transport_' . $transportName;
                if (!IfwPsn_Vendor_Zend_Loader_Autoloader::autoload($transportName)) {
                    throw new IfwPsn_Vendor_Zend_Application_Resource_Exception(
                        "Specified Mail Transport '{$transportName}'"
                        . 'could not be found'
                    );
                }
            }
        }

        unset($options['type']);
        unset($options['register']); //@see ZF-11022

        switch($transportName) {
            case 'IfwPsn_Vendor_Zend_Mail_Transport_Smtp':
                if (!isset($options['host'])) {
                    throw new IfwPsn_Vendor_Zend_Application_Resource_Exception(
                        'A host is necessary for smtp transport,'
                        . ' but none was given'
                    );
                }

                $transport = new $transportName($options['host'], $options);
                break;
            case 'IfwPsn_Vendor_Zend_Mail_Transport_Sendmail':
            default:
                $transport = new $transportName($options);
                break;
        }

        return $transport;
    }
}
