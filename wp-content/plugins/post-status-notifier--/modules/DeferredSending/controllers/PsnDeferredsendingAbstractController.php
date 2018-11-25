<?php
/**
 * Abstract controller
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PsnDeferredsendingAbstractController.php 401 2015-08-21 20:24:18Z timoreithde $
 * @package  IfwPsn_Wp
 */
abstract class DeferredSending_PsnDeferredsendingAbstractController extends PsnModelBindingController
{
    /**
     * @param $action
     */
    public function handleBulkAction($action)
    {
        if ( $action == 'reset' ) {
            $this->_reset();
        }
    }

    public function onBootstrap()
    {
        if ($this->_request->getActionName() == 'index') {

            $this->enqueueScripts();

            if ($this->_pm->hasOption('psn_deferred_sending_log_sent')) {
                $this->view->isLog = true;
            } else {
                $this->view->isLog = false;
            }

            $this->_perPage = new IfwPsn_Wp_Plugin_Screen_Option_PerPage($this->_pm, __('Items per page', 'ifw'), $this->getModelMapper()->getPerPageId($this->getPluginAbbr() . '_'));
        }
    }

    /**
     * Resets the queue
     */
    protected function _reset()
    {
        /**
         * @var wpdb
         */
        global $wpdb;

        $r = new ReflectionProperty($this->getModelName(), '_table');
        $table = $r->getValue();

        $wpdb->query(sprintf('TRUNCATE TABLE %s', $wpdb->prefix . $table));

        $this->gotoIndex();
    }

    public function enqueueScripts()
    {
        IfwPsn_Wp_Proxy_Script::loadAdmin('jquery-ui-dialog');
        IfwPsn_Wp_Proxy_Style::loadAdmin('wp-jquery-ui');
        IfwPsn_Wp_Proxy_Style::loadAdmin('wp-jquery-ui-dialog');
    }
}
