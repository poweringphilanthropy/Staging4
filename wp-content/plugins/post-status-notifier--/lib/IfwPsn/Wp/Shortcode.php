<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Regex parser
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Shortcode.php 371 2014-12-20 21:46:34Z timoreithde $
 * @package  IfwPsn_Wp
 */
abstract class IfwPsn_Wp_Shortcode implements IfwPsn_Wp_Interface_Loggable
{
    /**
     * @var string
     */
    protected $_shortcode;
    
    /**
     * @var IfwPsn_Wp_Plugin_Logger
     */
    protected $_logger;

    
    
    /**
     * @param string $shortcode
     * @param array $options
     */
    public function __construct($shortcode, array $options = array())
    {
        $this->_shortcode = $shortcode;
        
        $this->_init($options);
    }

    /**
     * @param array $options
     */
    protected function _init(array $options)
    {
        $this->initShortcode();
        
        // add default filters
        IfwPsn_Wp_Proxy_Filter::addWidgetText('do_shortcode');
        IfwPsn_Wp_Proxy_Filter::addTheExcerpt('do_shortcode');
        IfwPsn_Wp_Proxy_Filter::addTheExcerptFeed('do_shortcode');
        IfwPsn_Wp_Proxy_Filter::addTheExcerptRss('do_shortcode');
        IfwPsn_Wp_Proxy_Filter::addTheContentFeed('do_shortcode');
        IfwPsn_Wp_Proxy_Filter::addTheContentRss('do_shortcode');
        
        // add additional custom filters
        if (isset($options['filters']) && is_array($options['filters'])) {
            foreach ($options['filters'] as $filter) {
                IfwPsn_Wp_Proxy_Filter::add($filter, 'do_shortcode');
            }
        }
    }

    public function initShortcode()
    {
        add_shortcode($this->_shortcode, array($this, 'handle'));
    }
    
    /**
     * 
     * @param array $options
     * @param string $content
     * @param string $code
     * @return string
     */
    public function handle($options, $content='', $code='')
    {
        $replacement = $this->_getReplacement($options, $content, $code);
         
        $replacement = $this->_applyFilters($replacement, $options);
        
        return $replacement;
    }

    /**
     * @param $string
     * @return bool
     */
    public function containsShortcode($string)
    {
        return strpos($string, $this->_shortcode) !== false;
    }

    /**
     * Must be overwritten by child class implementation
     *
     * @param array $options
     * @param string $content
     * @param string $code
     * @return string the replacement
     */
    abstract protected function _getReplacement($options, $content='', $code='');
    
    /**
     * Applies filters to the replacement string
     *
     * @param string $replacement
     * @param array $options
     * @return string
     */
    protected function _applyFilters($replacement, $options)
    {
        if (!empty($options['filters'])) {
            $replacement = IfwPsn_Wp_Tpl::applyFilters($replacement, $options['filters'], $this->_logger);
        }
        
        return $replacement;
    }

    /**
     * Set logger
     * @param IfwPsn_Wp_Plugin_Logger $logger
     */
    public function setLogger(IfwPsn_Wp_Plugin_Logger $logger)
    {
        $this->_logger = $logger;
    }
    
    /**
     * Get logger
     * @return IfwPsn_Wp_Plugin_Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return string
     */
    public function getShortcode()
    {
        return $this->_shortcode;
    }
}
