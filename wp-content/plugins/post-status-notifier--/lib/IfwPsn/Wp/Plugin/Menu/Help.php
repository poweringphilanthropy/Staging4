<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Admin Menu Contextual Help
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version  $Id: Help.php 373 2014-12-28 23:59:13Z timoreithde $
 * @package  IfwPsn_Wp_Plugin_Admin
 */
class IfwPsn_Wp_Plugin_Menu_Help
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var string
     */
    protected $_id;

    /**
     * @var string
     */
    protected $_title;
    
    /**
     * @var string
     */
    protected $_help;
    
    /**
     * @var string
     */
    protected $_sidebar;

    
    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }
    
    /**
     * Loads the appropriate action for adding contextual help
     */
    public function load()
    {
        if (IfwPsn_Wp_Proxy_Blog::isMinimumVersion('3.3')) {
            // since 3.3 use the add_help_method on the screen object
            IfwPsn_Wp_Proxy_Action::addAdminHead(array($this, 'addHelpTab'));
        } else {
            // before 3.3 use the contextual_help action
            IfwPsn_Wp_Proxy_Action::add('contextual_help', array($this, 'getContextualHelp'), 10, 3);
        }
    }
    
    /**
     * Callback for WP >= 3.3
     * @since WP 3.3
     */
    public function addHelpTab() 
    {
        $screen = IfwPsn_Wp_Proxy_Screen::getCurrent();

        $help = array(
            'id' => $this->_id == null ? 1 : $this->_id,
            'title' => $this->_title,
            'content' => sprintf('<div class="ifw-help-tab-content">%s</div>', $this->_help)
        );

        IfwPsn_Wp_Proxy_Screen::addHelpTab($help);

        if (!empty($this->_sidebar)) {
            IfwPsn_Wp_Proxy_Screen::setHelpSidebar($this->_sidebar);
        }
    }
    
    /**
     * Callback for WP < 3.3
     * 
     * @param string $contextual_help
     * @param string $screen_id
     * @param unknown_type $screen
     * @return string
     */    
    public function getContextualHelp($contextual_help, $screen_id, $screen)
    {
        return $this->_help;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * @param string $title
     * @return IfwPsn_Wp_Plugin_Menu_Help
     */
    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    /**
     * @param string $help
     * @return IfwPsn_Wp_Plugin_Menu_Help
     */
    public function setHelp($help)
    {
        $this->_help = $help;
        return $this;
    }

    /**
     * @param $sidebar
     * @return \IfwPsn_Wp_Plugin_Menu_Help
     */
    public function setSidebar($sidebar)
    {
        $this->_sidebar = $sidebar;
        return $this;
    }
}
