<?php
/**
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Details.php 427 2015-10-29 19:42:20Z timoreithde $
 */ 
class Psn_Module_Logger_ListTable_Ajax_Details extends IfwPsn_Wp_Ajax_Request
{
    public $action = 'load-psn-log-detail';



    /**
     * @return IfwPsn_Wp_Ajax_Response_Abstract
     */
    public function getResponse()
    {
        $id = (int)$_POST['logId'];
        $log = IfwPsn_Wp_ORM_Model::factory('Psn_Module_Logger_Model_Log')->find_one($id);

        $extra = $log->get('extra');

        $output = '';

        $output .=  '<div class="log-detail-dialog">';
        if (strpos($extra, '{') === 0) {
            $extra = json_decode($extra, true);
            $output .= '<p><b>TO:</b><br>' . htmlentities($extra['to']) . '<br>';
            $output .= '<p><b>Headers:</b><br>';
            foreach ($extra['headers'] as $header) {
                $header = htmlentities(html_entity_decode($header));
                $output .= $header . '<br>';
            }
            $output .= '</p>';
            $output .= '<p><b>Subject:</b><br>' . htmlentities($extra['subject']) . '</p>';

            $message = $extra['message'];
            if (isset($extra['html']) && $extra['html'] == false) {
                $message = nl2br($extra['message']);
            }

            $output .= '<b>Text:</b><div class="log-detail-text">' . htmlentities($message) . '</div>';

        } else {
            $output .= nl2br(htmlspecialchars($log->get('extra')));
        }
        $output .= '<div>';

        echo $output;

        exit;
    }
}