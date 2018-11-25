<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2012-2013 ifeelweb.de
 * @version   $Id: TestMail.php 257 2014-06-29 15:02:49Z timoreithde $
 * @package   
 */
require_once IFW_PSN_LIB_ROOT . '/IfwPsn/Wp/Plugin/Metabox/Abstract.php';

class Psn_Admin_Metabox_TestMail extends IfwPsn_Wp_Plugin_Metabox_Abstract
{
    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initId()
     */
    protected function _initId()
    {
        return 'test-mail';
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initTitle()
     */
    protected function _initTitle()
    {
        return __('Test Email', 'psn');
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initPriority()
     */
    protected function _initPriority()
    {
        return 'core';
    }

    /**
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::render()
     */
    public function render()
    {
        require_once IFW_PSN_LIB_ROOT . '/IfwPsn/Vendor/Zend/View.php';

        $testMailForm = new Psn_Admin_Form_TestMail(array(
            'action' => IfwPsn_Wp_Proxy_Admin::getUrl() . IfwPsn_Wp_Proxy_Admin::getMenuUrl($this->_pm, 'service', 'send-test-mail')
        ));

        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'testmail_form', $testMailForm);

        $testMailForm->setView(new IfwPsn_Vendor_Zend_View());

        ?>

        <p><?php _e('Send a test email to check the general email functionality of your WordPress installation. Settings in the options section will be considered (e.g. SMTP).', 'psn'); ?></p>
        <?php
        echo $testMailForm;
        ?>
        <?php
    }
}
 