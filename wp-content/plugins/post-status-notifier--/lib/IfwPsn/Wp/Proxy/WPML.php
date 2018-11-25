<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * WPML abstraction layer
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: WPML.php 153 2013-05-26 11:16:39Z timoreithde $
 */
class IfwPsn_Wp_Proxy_WPML extends IfwPsn_Wp_Proxy_Abstract
{
    /**
     * Functions found, WPML should be installed
     * @var int
     */
    const STATUS_OK = 1;
    
    /**
     * Functions not found, Wpml may not be installed
     * @var int
     */
    const STATUS_ERROR = 2;
    
    /**
     * Status code
     * @var int
     */
    protected $_status;
    
    /**
     * Status message
     * @var string
     */
    protected $_status_message;
    
    /**
     * Keeps the translation information about a page
     * @var array 
     */    
    protected $_page_translation;

    
    
    /**
     * 
     */
    public function __construct(IfwPsn_Wp_Proxy $wpProxy, IfwPsn_Wp_Plugin_Manager $pm)
    {
        parent::__construct($wpProxy, $pm);
        
        $this->_initStatus();
    }
    
    /**
     * Checks if WPML is ready
     * 
     * @return boolean
     */
    public function isReady()
    {
        return $this->_status === self::STATUS_OK;
    }
    
    /**
     * Retrieves the Wpml status
     * 
     * @return number
     */
    public function getStatus()
    {
        return $this->_status;
    }
    
    /**
     * Retrieves the Wpml status message
     * 
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->_status_message;
    }
    
    /**
     * Inits the Wpml status
     * 
     */
    protected function _initStatus()
    {
        try {
            $result = $this->_checkStatus();
            if ($result === true) {
                $this->_status = self::STATUS_OK;
                $this->_status_message = 'Plugin is ready';          
            }
            
        } catch (Exception $e) {
            $this->_status = self::STATUS_ERROR;
            $this->_status_message = $e->getMessage();
        }
    }

    /**
     * Checks if Wpml is installed
     *
     *
     * @throws IfwPsn_Wp_Proxy_WPML_Exception
     * @return boolean
     */
    protected function _checkStatus()
    {
        if (!function_exists('icl_get_default_language')) {
            throw new IfwPsn_Wp_Proxy_WPML_Exception('Wpml function not found: icl_get_default_language');
        } elseif (!function_exists('icl_get_languages')) {
            throw new IfwPsn_Wp_Proxy_WPML_Exception('Wpml function not found: icl_get_languages');
        } elseif (!defined('ICL_LANGUAGE_CODE')) {
            throw new IfwPsn_Wp_Proxy_WPML_Exception('Wpml constant not found: ICL_LANGUAGE_CODE');
        }
        
        return true;
    }

    /**
     * Retrieves the translation info of the current page
     *
     * @param $options
     * @return array
     */
    public function getPageTranslation($options)
    {
        if (!function_exists('icl_get_languages')) {
            
            $this->_page_translation = array();
            
        } else {
            
            if ($this->_page_translation == null) {
                $options = sprintf('skip_missing=%s&orderby=%s&order=%s&link_empty_to=%s',
                    $options['skip_missing'],
                    $options['order_by'],
                    $options['order'],
                    $options['link_empty_to']);
                
                $this->_page_translation = icl_get_languages($options);
            }
        }
        
        return $this->_page_translation;
    }
    
    /**
     * Retrieves the default language
     * 
     * @return string|false
     */
    public function getDefaultLanguage()
    {
        $result = false;
        if (function_exists('icl_get_default_language')) {
            $result = icl_get_default_language();
        }
        return $result;
    }
    
    /**
     * Retrieves the current language code
     * 
     * @return string
     */
    public function getCurrentLanguage()
    {
        $result = '';
        if (defined(ICL_LANGUAGE_CODE)) {
            $result = ICL_LANGUAGE_CODE;
        }
        return $result;
    }

    /**
     * Registers a string for translation
     *
     * @param string $context
     * @param string $name
     * @param string $value
     * @param bool|string $allow_empty_value
     * @return void
     */
    public function registerString($context, $name, $value, $allow_empty_value=false)
    {
        if (function_exists('icl_register_string')) {
            icl_register_string($context, $name, $value, $allow_empty_value);
        }
    }

    /**
     * Retrieves a string registered for translation
     *
     * @param string $context
     * @param string $name
     * @param bool|string $original_value
     * @return bool|string
     */
    public function getRegisteredString($context, $name, $original_value=false)
    {
        if (function_exists('icl_t')) {
            $string = icl_t($context, $name, $original_value);
        } else {
            $string = $original_value;
        }
        
        return $string;
    }

    /**
     * Unregisters a string for translation
     * 
     * @param string $context
     * @param string $name
     */
    public function unregisterString($context, $name)
    {
        if (function_exists('icl_unregister_string')) {
            icl_unregister_string($context, $name);
        }
    }

}
