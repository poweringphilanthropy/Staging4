<?php
/**
 * MailTemplates list table data
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: MailTemplates.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_HtmlMails_ListTable_Data_MailTemplates implements IfwPsn_Wp_Plugin_ListTable_Data_Interface
{
    /**
     * @var string
     */
    protected $_model = 'Psn_Module_HtmlMails_Model_MailTemplates';



    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_ListTable_Data_Interface::getItems()
     */
    public function getItems($limit, $page, $order = null, $where = null)
    {
        $offset = ($page-1) * $limit;
        if (empty($order)) {
            $order = array('name' => 'asc');
        }

        $data = IfwPsn_Wp_ORM_Model::factory($this->_model)->limit($limit)->offset($offset);

        if (!empty($order)) {
            $orderBy = key($order);
            $orderDir = $order[$orderBy];
            if ($orderDir == 'desc') {
                $data->order_by_desc($orderBy);
            } else {
                $data->order_by_asc($orderBy);
            }
        }

        if (!empty($where)) {
            $data->where_like('name', '%' . $where . '%');
        }

        return $data->find_array();
    }

    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_ListTable_Data_Interface::getTotalItems()
     */
    public function getTotalItems()
    {
        return IfwPsn_Wp_ORM_Model::factory($this->_model)->count();
    }

}
