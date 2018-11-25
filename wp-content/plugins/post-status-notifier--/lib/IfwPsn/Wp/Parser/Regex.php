<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Regex parser
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id$
 * @package  IfwPsn_Wp
 */
abstract class IfwPsn_Wp_Parser_Regex extends IfwPsn_Wp_Parser_Abstract
{
    /**
     * @var string
     */
    protected $_regex;
    
    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Parser_Interface::parse()
     */
    public function parse($string)
    {
        $matches = array();
        preg_match_all($this->_regex, $string, $matches);
        
        return $this->_handleMatches($matches, $string);        
    }
    
    /**
     * @param string $regex
     */
    public function setRegex($regex)
    {
        $this->_regex = $regex;
    }

    /**
     * 
     * @param array $matches
     * @param string $string
     */
    abstract protected function _handleMatches($matches, $string);
    
}
