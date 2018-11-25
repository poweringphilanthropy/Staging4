<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Loader.php 414 2015-04-12 14:44:06Z timoreithde $
 */
class IfwPsn_Wp_Plugin_Feature_Loader
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var array
     */
    protected $_features = array();


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;

        if (method_exists($this, '_init')) {
            $this->_init();
        }

        do_action($this->_pm->getAbbrLower() . '_add_feature', $this);
    }

    /**
     * Loads all registered features
     */
    public function load()
    {
        foreach ($this->_features as $feature) {
            if ($feature instanceof IfwPsn_Wp_Plugin_Feature_Abstract) {
                $feature->load();
            }
        }
    }

    /**
     * @param IfwPsn_Wp_Plugin_Feature_Abstract $feature
     */
    public function addFeature(IfwPsn_Wp_Plugin_Feature_Abstract $feature)
    {
        array_push($this->_features, $feature);
    }

}
