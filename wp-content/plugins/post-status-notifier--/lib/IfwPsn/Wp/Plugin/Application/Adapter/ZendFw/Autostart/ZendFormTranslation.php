<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Set default form translator
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: ZendFormTranslation.php 334 2014-09-13 19:35:58Z timoreithde $
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Plugin_Application_Adapter_ZendFw_Autostart_ZendFormTranslation extends IfwPsn_Wp_Plugin_Application_Adapter_ZendFw_Autostart_Abstract
{
    protected $_supportedLanguages = array(
        'en_US', 'de_DE'
    );

    public function execute()
    {
        require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Translate/Adapter/Array.php';
        require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Locale.php';

        try {
            // check if the WP locale is valid otherwise set it to default
            $locale = IfwPsn_Wp_Proxy_Blog::getLanguage();
            if (!in_array($locale, $this->_supportedLanguages)) {
                $locale = 'en_US';
            }

            $translator = new IfwPsn_Vendor_Zend_Translate(
                'IfwPsn_Vendor_Zend_Translate_Adapter_Array',
                $this->_adapter->getPluginManager()->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Form/resources/languages',
                $locale,
                array('scan' => IfwPsn_Vendor_Zend_Translate::LOCALE_DIRECTORY)
            );
            // set the validation translator
            IfwPsn_Vendor_Zend_Validate_Abstract::setDefaultTranslator($translator);

        } catch (Exception $e) {
            // do nothing. if something failed, we just have no translation for Zend_Validate
        }
    }
}
