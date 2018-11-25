<?php
/**
 * RecipientsLists list table
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: RecipientsLists.php 418 2015-09-18 10:25:48Z timoreithde $
 * @package
 */

class Psn_Module_Recipients_ListTable_RecipientsLists extends IfwPsn_Wp_Plugin_ListTable_Abstract
{
    protected $_mod = 'recipients';



    /**
     * @return string
     */
    public function getId()
    {
        return 'recipientslists';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name', 'ifw'),
        );

        if ($this->isMetaboxEmbedded()) {
            unset($columns['cb']);
        }

        return $columns;
    }

    /**
     * @return array
     */
    public function getSortableColumns()
    {
        return $sortable_columns = array(
            'name' => array('name', false),
        );
    }

    /**
     * Custom column handling for name
     *
     * @param unknown_type $item
     * @return string
     */
    public function getColumnName($item)
    {
        $result = htmlentities($item['name']);

        if (!$this->isMetaboxEmbedded()) {
            //Build row actions
            $actions = array();
            $actions['edit'] = sprintf('<a href="?page=%s&mod=recipients&controller=recipientslists&appaction=edit&id=%s">'. __('Edit', 'psn') .'</a>', $_REQUEST['page'], $item['id']);
            $actions['copy'] = sprintf('<a href="?page=%s&mod=recipients&controller=recipientslists&appaction=copy&nonce=%s&id=%s" class="copyConfirm">'. __('Copy', 'psn') .'</a>',
                $_REQUEST['page'],
                wp_create_nonce(IfwPsn_Zend_Controller_ModelBinding::getCopyNonceAction($this->getModelMapper()->getSingular(), $item['id'])),
                $item['id']
            );
            $actions['export'] = sprintf('<a href="?page=%s&mod=recipients&controller=recipientslists&appaction=export&id=%s">'. __('Export', 'psn') .'</a>', $_REQUEST['page'], $item['id']);
            $actions['delete'] = sprintf('<a href="?page=%s&mod=recipients&controller=recipientslists&appaction=delete&nonce=%s&id=%s" class="delConfirm">'. __('Delete', 'psn') .'</a>',
                $_REQUEST['page'],
                wp_create_nonce(IfwPsn_Zend_Controller_ModelBinding::getDeleteNonceAction($this->getModelMapper()->getSingular(), $item['id'])),
                $item['id']
            );

            //Return the title contents
            $result = sprintf('%1$s%2$s',
                /*$1%s*/ $item['name'],
                /*$2%s*/ $this->row_actions($actions)
            );
        }

        return $result;
    }

    /**
     *
     */
    public function getExtraControlsTop()
    {
        $this->search_box(__('Search'), 'name');
        $this->displayReloadButton();
    }

    /**
     * Renders the checkbox column (hard coded in class-wp-list-table.php)
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item['id']
        );
    }

    /**
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array();

        if (!$this->isMetaboxEmbedded()) {
            $actions = array(
                'bulk_delete' => __('Delete'),
                'bulk_export' => __('Export', 'psn'),
            );
        }

        return $actions;
    }

    public function process_bulk_action()
    {

    }

    /**
     * Init loadDetails link for jquery ui dialog
     *
     * @see WP_List_Table::display()
     */
    public function afterDisplay()
    {
        $ajaxDetails = new Psn_Module_Logger_ListTable_Ajax_Details();

        if (!$this->isMetaboxEmbedded()):
        ?>
        <script type="text/javascript">
        jQuery(".delConfirm").click(function(e) {
            e.preventDefault();
            var targetUrl = jQuery(this).attr("href");

            if (confirm('<?php _e('Are you sure you want to do this?'); ?>')) {
                document.location.href = targetUrl;
            }
        });
        jQuery(".copyConfirm").click(function(e) {
            e.preventDefault();
            var targetUrl = jQuery(this).attr("href");

            if (confirm('<?php _e('Do you want to copy this template?', 'psn_htm'); ?>')) {
                document.location.href = targetUrl;
            }
        });
        </script>
        <?php
        endif;
    }

    public function getModelName()
    {
        return 'Psn_Module_Recipients_Model_RecipientsLists';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Interface
     */
    public function getModelMapper()
    {
        return Psn_Module_Recipients_Model_Mapper_RecipientsLists::getInstance();
    }

    /**
     * @return Psn_Module_Recipients_ListTable_Data_RecipientsLists
     */
    public function getData()
    {
        return new Psn_Module_Recipients_ListTable_Data_RecipientsLists();
    }


}
