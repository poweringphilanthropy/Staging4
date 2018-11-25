<?php
/**
 * Logger widget
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Dashboard.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_Logger_Widget_Dashboard extends IfwPsn_Wp_Widget_Dashboard
{
    /**
     * @var IfwPsn_Wp_Module_Bootstrap_Abstract
     */
    protected $_module;



    /**
     * Renders the dashboard widget's output
     * @return mixed
     */
    public function render()
    {
        $listTable = new Psn_Module_Logger_ListTable_Log($this->_pm, array('metabox_embedded' => true, 'ajax' => true));

        if (isset($_POST['refresh_rows'])) {
            $html = $listTable->ajax_response();
        } else {
            $html = $listTable->fetch();

            $this->_module->initTpl();

            require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Admin/Navigation.php';
            $nav = new Psn_Admin_Navigation($this->_pm);

            $html .= IfwPsn_Wp_Tpl::getFilesytemInstance($this->_pm)
                ->render('dashboard_widget_navi.html.twig', array(
                    'admin_url' => IfwPsn_Wp_Proxy_Admin::getUrl(),
                    'nav' => $nav->getPagesWithHrefAndLabel(),
                ));
        }

        echo $html;
    }

    /**
     * Handles dashboard widget's interaction like form submission
     * @return mixed
     */
    public function handle()
    {
        // no handle functionality
    }

    /**
     * @param \IfwPsn_Wp_Module_Bootstrap_Abstract $module
     */
    public function setModule($module)
    {
        $this->_module = $module;
    }

    /**
     * @return \IfwPsn_Wp_Module_Bootstrap_Abstract
     */
    public function getModule()
    {
        return $this->_module;
    }

}
