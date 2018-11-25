<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Widget Options Abstraction
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Options.php 153 2013-05-26 11:16:39Z timoreithde $
 */
class IfwPsn_Wp_Widget_Options
{
    /**
     * Stores the options
     * @var array
     */
    protected $_options = array();
    

    
    /**
     * 
     * @param array $options
     */
    public function __construct($options)
    {
        $this->_initOptions($options);
    }
    
    /**
     * Init the options 
     * 
     * @param array $options
     */
    protected function _initOptions($options)
    {
        $properties = array();
        
        foreach($options as $opt => $value) {
            $camelCaseOpt = implode('', array_map('ucfirst', explode('_', $opt)));
            
            $filterMethodName = 'filter'. $camelCaseOpt;
            if (method_exists($this, $filterMethodName)) {
                $value = $this->$filterMethodName($value);
            }
            $properties[$camelCaseOpt] = $value;
        }
        
        $this->_options = $properties;
    }

    /**
     * Magic method for accessing the options by get and has methods
     * 
     * @param string $name
     * @param mixed $arguments
     * @return bool
     * @throws IfwPsn_Wp_Widget_Exception
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === 0) {
            // Getter
            $key = substr($name, 3);
            if (array_key_exists($key, $this->_options)) {
                return $this->_options[$key];
            }
        }
        
        if (strpos($name, 'has') === 0) {
            // Has
            $key = substr($name, 3);
            if (array_key_exists($key, $this->_options)) {
                return !empty($this->_options[$key]);
            } else {
                return false;
            }
        }
        
        throw new IfwPsn_Wp_Widget_Exception('Invalid method '. $name);
    }
}
