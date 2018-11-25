<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 414 2015-04-12 14:44:06Z timoreithde $
 */
abstract class IfwPsn_Wp_Plugin_Feature_Abstract
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var IfwPsn_Wp_Module_Bootstrap_Abstract
     */
    protected $_module;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param null $module
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, $module = null)
    {
        $this->_pm = $pm;

        if ($module instanceof IfwPsn_Wp_Module_Bootstrap_Abstract) {
            $this->_module = $module;
        }

        $this->init();
    }

    abstract function init();
    abstract function load();

    /**
     * @return IfwPsn_Wp_Plugin_Manager
     */
    public function getPm()
    {
        return $this->_pm;
    }

    /**
     * @return IfwPsn_Wp_Module_Bootstrap_Abstract
     */
    public function getModule()
    {
        return $this->_module;
    }
}
