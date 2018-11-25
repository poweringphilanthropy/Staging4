<?php
/**
 * Limitations list table
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Limitations.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_Limitations_ListTable_Limitations extends IfwPsn_Wp_Plugin_ListTable_Abstract
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'limitations';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'timestamp' => __('Timestamp', 'psn'),
            'rule_name' => __('Rule name', 'psn'),
            'post_title' => __('Post', 'psn'),
            'status_after' => __('Status after', 'psn'),
        );

        if ($this->isMetaboxEmbedded()) {
            unset($columns['cb']);
        }

        return $columns;
    }

    /**
     * @return string
     */
    public function get_default_primary_column_name()
    {
        return 'rule_name';
    }
    
    /**
     * @return array
     */
    public function getSortableColumns()
    {
        return $sortable_columns = array(
            'rule_id' => array('rule_id', false),
            'post_title' => array('post_title', false),
            'timestamp' => array('timestamp', false),
            'status_after' => array('status_after', false),
        );
    }

    /**
     * Custom column handling
     *
     * @param $item
     * @return string
     */
    public function getColumnTimestamp($item)
    {
        $result = IfwPsn_Wp_Date::format($item['timestamp']);

        if (!$this->isMetaboxEmbedded()) {
            //Build row actions
            $actions = array();
            $actions['delete'] = sprintf('<a href="?page=%s&mod=limitations&controller=limitations&appaction=delete&nonce=%s&id=%s" class="delConfirm">'. __('Delete', 'psn') .'</a>',
                $_REQUEST['page'],
                wp_create_nonce( IfwPsn_Zend_Controller_ModelBinding::getDeleteNonceAction($this->getModelMapper()->getSingular(), $item['id']) ),
                $item['id']
            );

            //Return the title contents
            $result = sprintf('%1$s%2$s',
                /*$1%s*/ IfwPsn_Wp_Date::format($item['timestamp']),
                /*$2%s*/ $this->row_actions($actions)
            );
        }

        return $result;
    }

    /**
     * Custom column handling
     *
     * @param $items
     * @return string
     */
    public function getColumnPostTitle($items)
    {
        return htmlentities($items['post_title']);
    }

    /**
     * Custom column handling
     *
     * @param $items
     * @return string
     */
    public function getColumnRuleName($items)
    {
        return htmlentities($items['rule_name']);
    }

    /**
     * Custom column handling
     *
     * @param $items
     * @return string
     */
    public function getColumnStatusAfter($items)
    {
        return htmlentities($items['status_after']);
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
                'clear' => __('Clear'),
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

    /**
     * @return string
     */
    public function getModelName()
    {
        return 'Psn_Module_Limitations_Model_Limitations';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Interface
     */
    public function getModelMapper()
    {
        return Psn_Module_Limitations_Model_Mapper_Limitations::getInstance();
    }

    /**
     * @return Psn_Module_Limitations_ListTable_Data_Limitations
     */
    public function getData()
    {
        return new Psn_Module_Limitations_ListTable_Data_Limitations();
    }
}
