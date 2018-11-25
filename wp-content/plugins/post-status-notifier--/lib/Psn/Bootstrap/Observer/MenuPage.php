<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: MenuPage.php 149 2014-03-17 23:56:36Z timoreithde $
 * @package   
 */ 
class Psn_Bootstrap_Observer_MenuPage extends IfwPsn_Wp_Plugin_Bootstrap_Observer_Abstract
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'menu_page';
    }

    protected function _preBootstrap()
    {
        if ($this->_pm->getAccess()->isAdmin()) {

            require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Menu/Options.php';

            $optionsPage = new Psn_Menu_Options($this->_pm);

            $optionsPage
                ->setMenuTitle($this->_pm->getEnv()->getName())
                ->setSlug($this->_pm->getPathinfo()->getDirname())
                ->init()
            ;

            $this->_resource = $optionsPage;
        }
    }
}
