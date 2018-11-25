<?php
/**
 * Limitations list table data
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Limitations.php 418 2015-09-18 10:25:48Z timoreithde $
 * @package
 */
class Psn_Module_Limitations_ListTable_Data_Limitations implements IfwPsn_Wp_Plugin_ListTable_Data_Interface
{
    /**
     * @var string
     */
    protected $_model = 'Psn_Module_Limitations_Model_Limitations';



    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_ListTable_Data_Interface::getItems()
     */
    public function getItems($limit, $page, $order = null, $where = null)
    {
        global $table_prefix;

        $offset = ($page-1) * $limit;
        if (empty($order)) {
            $order = array('timestamp' => 'desc');
        }

        $data = IfwPsn_Wp_ORM_Model::factory($this->_model)
            ->table_alias('limits')
            ->select('limits.*')
            ->select('rules.name', 'rule_name')
            ->select('rules.posttype', 'rule_posttype')
            ->select('posts.post_title', 'post_title')
            ->inner_join(
                $table_prefix . Psn_Model_Rule::$_table,
                array('limits.rule_id', '=', 'rules.id'),
                'rules')
            ->inner_join($table_prefix . 'posts',
                array('limits.post_id', '=', 'posts.ID'),
                'posts')
            ->limit($limit)
            ->offset($offset)
        ;

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
            $data->where('rule_name', '%' . $where . '%');
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
