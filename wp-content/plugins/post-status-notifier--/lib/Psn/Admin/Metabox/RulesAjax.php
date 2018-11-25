<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: RulesAjax.php 418 2015-09-18 10:25:48Z timoreithde $
 * @package
 */

$GLOBALS['hook_suffix'] = 'psn_rules';
class Psn_Admin_Metabox_RulesAjax extends IfwPsn_Wp_Ajax_Request
{
    public $action = 'load-psn-rules';

    
    /**
     * @return IfwPsn_Wp_Ajax_Response_Abstract
     */
    public function getResponse()
    {
        $listTable = new Psn_Admin_ListTable_Rules(IfwPsn_Wp_Plugin_Manager::getInstance('Psn'), array('metabox_embedded' => true, 'ajax' => true));

        if (isset($_POST['refresh_rows'])) {
            $html = $listTable->ajax_response();
        } else {
            $html = '<p><a href="'.  IfwPsn_Wp_Proxy_Admin::getUrl() . IfwPsn_Wp_Proxy_Admin::getMenuUrl(IfwPsn_Wp_Plugin_Manager::getInstance('Psn'), 'rules', 'create') .'" class="ifw-wp-icon-plus" id="link_create_rule">'.
                __('Create new rule', 'psn') .'</a></p>';
            $html .= $listTable->fetch();
        }

        return new IfwPsn_Wp_Ajax_Response_Json(true, array(
            'html' => $html)
        );
    }
}
