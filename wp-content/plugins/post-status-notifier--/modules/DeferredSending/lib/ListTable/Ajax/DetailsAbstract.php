<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: DetailsAbstract.php 401 2015-08-21 20:24:18Z timoreithde $
 * @package
 */
abstract class Psn_Module_DeferredSending_ListTable_Ajax_DetailsAbstract extends IfwPsn_Wp_Ajax_Request
{
    /**
     * @return IfwPsn_Wp_Ajax_Response_Abstract
     */
    public function getResponse()
    {
        $id = (int)$_POST['mailId'];
        $mail = IfwPsn_Wp_ORM_Model::factory($this->_modelName)->find_one($id);

        $output = '';
        $output .= '<p><b>TO:</b><br>' . htmlentities($mail->get('to')) . '<br>';
        $output .= '<p><b>Headers:</b><br>';
        $headers = $mail->get('headers');
        if (!empty($headers)) {
            $headers = unserialize($headers);
            foreach ($headers as $header) {
                //$output .= htmlspecialchars($header) . '<br>';
                $output .= $header . '<br>';
            }
        }
        $output .= '</p>';
        $output .= '<p><b>Subject:</b><br>' . htmlentities($mail->get('subject')) . '</p>';

        $message = $mail->get('message');
        if ($mail->get('html') == '0') {
            $message = nl2br($message);
        }
        $output .= '<b>Text:</b><div class="log-detail-text">' . htmlentities(IfwPsn_Util_Parser_Html::stripScript($message)) . '</div>';

        return new IfwPsn_Wp_Ajax_Response_Html($output);
    }
}