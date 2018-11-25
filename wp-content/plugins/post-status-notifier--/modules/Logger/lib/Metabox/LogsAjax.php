<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: LogsAjax.php 418 2015-09-18 10:25:48Z timoreithde $
 * @package
 */

$GLOBALS['hook_suffix'] = 'psn_logs';
class Psn_Module_Logger_Metabox_LogsAjax extends IfwPsn_Wp_Ajax_Request
{
    public $action = 'load-psn-logs';

    /**
     * @return IfwPsn_Wp_Ajax_Response_Abstract
     */
    public function getResponse()
    {
        $listTable = new Psn_Module_Logger_ListTable_Log(IfwPsn_Wp_Plugin_Manager::getInstance('Psn'), array('metabox_embedded' => true, 'ajax' => true));

        if (isset($_POST['refresh_rows'])) {
            $html = $listTable->ajax_response();
        } else {
            $html = $listTable->fetch();
        }

        return new IfwPsn_Wp_Ajax_Response_Json(true, array(
            'html' => $html
        ));
    }
}
