<?php
/**
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PsnOptionsController.php 416 2015-09-17 10:21:37Z timoreithde $
 */ 
class PsnOptionsController extends PsnApplicationController
{
    public function onBootstrap()
    {
        $this->_pm->getBootstrap()->getOptions()->setRenderer(new IfwPsn_Wp_Options_Renderer_Pills($this->_pm));

        $optionsRenderer = $this->_pm->getBootstrap()->getOptions()->getRenderer();
        if ($optionsRenderer instanceof IfwPsn_Wp_Options_Renderer_Interface) {
            $optionsRenderer->init();
        }
    }

    /**
     *
     */
    public function indexAction()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Menu/Help.php';

        IfwPsn_Wp_Proxy_Script::loadAdmin('ace', $this->_pm->getEnv()->getUrlAdminJs() . 'lib/ace/ace.js', array(), $this->_pm->getEnv()->getVersion());
        IfwPsn_Wp_Proxy_Script::loadAdmin('psn_options', $this->_pm->getEnv()->getUrlAdminJs() . 'options.js', array(), $this->_pm->getEnv()->getVersion());

        // set up contextual help
        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Options', 'psn'))
            ->setHelp($this->_getHelpText())
            ->setSidebar($this->_getHelpSidebar())
            ->load();

        $this->view->options = IfwPsn_Wp_Options::getInstance($this->_pm);
    }

    /**
     *
     * @return string
     */
    protected function _getHelpText()
    {
        return sprintf(__('Please consider the documentation page <a href="%s" target="_blank">%s</a> for more information.', 'ifw'),
            'http://docs.ifeelweb.de/post-status-notifier/options.html',
            __('Options', 'psn'));
    }
}

