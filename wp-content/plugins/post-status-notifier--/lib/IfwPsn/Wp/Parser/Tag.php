<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Tag parser
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id$
 * @package  IfwPsn_Wp
 */
abstract class IfwPsn_Wp_Parser_Tag extends IfwPsn_Wp_Parser_Regex
{
    /**
     * Regex to find the tag(s) in the string
     * @var string
     */
    protected $_tagRegex = '#\[%s(.[^\]]*|)\](.*)\[/%s\]#Usi';
    
    /**
     * Regex to find the options in opening tag
     * @var string
     */
    protected $_optionsRegex = '/((?:"[^"]*"|[^=,\s])*)=((?:"[^"]*"|[^=,\s])*)/';
    
    /**
     * Tag name
     * @var string
     */
    protected $_tagName;
    
    /**
     * 
     * @param string $tagName
     */
    public function __construct($tagName)
    {
        $this->_tagName = $tagName;
        $this->setRegex(sprintf($this->_tagRegex, $this->_tagName, $this->_tagName));
    }

    /**
     * 
     * @param array $matches
     * @param string $string
     * @return mixed|string
     */
    protected function _handleMatches($matches, $string)
    {
        if ($matches && count($matches[0]) > 0) {
            
            for ($i=0; $i<count($matches[0]); $i++) {
            
                $search = $matches[0][$i];
                $options = $this->_prepareOptions($matches[1][$i]);
                $value = $matches[2][$i];

                $replacement = $this->_getReplacement($value, $options);
                
                $replacement = $this->_applyFilters($replacement, $options);
                
                $string = str_replace($search, $replacement, $string);
            }
        }
        
        return $string;
    }

    /**
     * Must be overwritten by concrete class implementation
     * 
     * @param array $options
     * @param string $value
     * @return string the replacement
     */
    abstract protected function _getReplacement($value, $options);
    
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
     * Prepares options array from tag options
     *
     * @param string $options
     * @return multitype:Ambigous <boolean, number, string>
     */
    protected function _prepareOptions($options)
    {
        $result = array();
    
        $options = trim($options);
        $optionsParts = $this->_extractOptionsParts($options);
    
        if (count($optionsParts) > 0) {
    
            for ($i=0; $i<count($optionsParts[0]); $i++) {
    
                $key = trim($optionsParts[1][$i]);
    
                $result[$key] = $this->_prepareOptionsValue($optionsParts[2][$i]);
            }
        }
    
        return $result;
    }
    
    /**
     * Extracts parameters from tag options string
     *
     * @param string $options
     * @return array
     */
    protected function _extractOptionsParts($options)
    {
        if (empty($options)) {
            return array();
        }
    
        $matches = array();
    
        preg_match_all($this->_optionsRegex, $options, $matches);
    
        return $matches;
    }
    
    /**
     * Prepares param value
     * 
     * @param string $value
     * @return mixed
     */
    protected function _prepareOptionsValue($value)
    {
        $value = trim($value);
    
        if (stripos($value, '"') === 0) {
            $value = substr($value, 1);
        }
        if (strrpos($value, '"') === (strlen($value)-1)) {
            $value = substr($value, 0, -1);
        }
    
        if ($value == 'true') {
            $value = true;
        } else if ($value == 'false') {
            $value = false;
        } else if (preg_match('/^[0-9]*$/', $value)) {
            $value = (int)$value;
        }
    
        return $value;
    }
    
}
