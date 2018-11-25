<?php
/**
 *
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @version     $Id: Rules.php 403 2015-08-21 20:40:03Z timoreithde $
 * @copyright   Copyright (c) ifeelweb.de
 * @package     Psn_Admin
 */
class Psn_Admin_ListTable_Rules extends IfwPsn_Wp_Plugin_ListTable_Abstract
{
    /**
     * @return string
     */
    public function getModelName()
    {
        return 'Psn_Model_Rule';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Interface
     */
    public function getModelMapper()
    {
        return Psn_Model_Mapper_Rule::getInstance();
    }

    /**
     * @return Psn_Admin_ListTable_Data_Rules
     */
    public function getData()
    {
        require_once dirname(__FILE__) . '/Data/Rules.php';
        return new Psn_Admin_ListTable_Data_Rules();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'rules';
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_ListTable_Abstract::getColumns()
     */
    public function getColumns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'active' => __('Active', 'psn'),
            'name' => __('Rule name', 'psn'),
            'services' => __('Services', 'psn'),
            'filters' => __('Filter', 'psn'),
            'posttype' => __('Post type', 'psn'),
            'status_before' => __('Status before', 'psn'),
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
        return 'name';
    }

    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_ListTable_Data_Interface::getSortableColumns()
     */
    public function getSortableColumns()
    {
        return $sortable_columns = array(
            'active' => array('active', false),
            'name' => array('name', true),
            'posttype' => array('posttype', false),
            'status_before' => array('status_before', false),
            'status_after' => array('status_after', false),
        );
    }

    /**
     * Custom column handling for active state
     *
     * @param $item
     * @return string
     */
    public function getColumnActive($item)
    {
        $formatImg = '<img src="%s/%s.png" />';
        $formatLink = '<a href="%s" title="%s">'. $formatImg .'</a>';

        $skinUrl = $this->_pm->getEnv()->getSkinUrl() . 'icons';
        $icon = $item['active'] == '1' ? 'button-white-check' : 'cross';

        if ($this->isMetaboxEmbedded()) {
            $output = sprintf($formatImg, $skinUrl, $icon);
        } else {

            $appaction = $item['active'] == '1' ? 'deactivate' : 'activate';
            $title = $item['active'] == '1' ? __('Deactivate', 'ifw') : __('Activate', 'ifw');
            $urlFormat = '?page=%s&controller=rules&appaction=%s&id=%s';

            $url = wp_nonce_url(sprintf($urlFormat, $_REQUEST['page'], $appaction, $item['id']), $appaction . $item['id']);
            $output = sprintf($formatLink, $url, $title, $skinUrl, $icon);
        }

        return $output;
    }

    /**
     * @param $items
     * @internal param $item
     * @return string
     */
    public function getColumnPosttype($items)
    {
        $posttype = htmlentities($items['posttype']);

        switch($posttype) {
            case 'all':
                $result = __('all types', 'psn');
                break;
            default:
                $result = IfwPsn_Wp_Proxy_Post::getTypeLabel($posttype);
        }

        return $result;
    }

    /**
     * @param $items
     * @internal param $item
     * @return string
     */
    public function getColumnServices($items)
    {
        $services = array();

        $email = $items['service_email'];
        $log = $items['service_log'];

        if ($email == '1') {
            array_push($services, '<a class="rule-service ifw-wp-icon-mail hint--bottom" data-hint="'. __('Email', 'psn') .'"></a>');
        }
        if ($log == '1') {
            array_push($services, '<a class="rule-service ifw-wp-icon-log hint--bottom" data-hint="'. __('Log', 'psn_log') .'"></a>');
        }

        return implode('', $services);
    }

    /**
     * @param $items
     * @internal param $item
     * @return string
     */
    public function getColumnFilters($items)
    {
        $filter = array();

        $categories = $items['categories'];
        $editor_restriction = $items['editor_restriction'];

        if (!empty($categories)) {
            array_push($filter, __('Categories', 'psn'));
        }
        if (!empty($editor_restriction)) {
            array_push($filter, __('Editor restriction', 'psn_rec'));
        }

        if (count($filter) > 0) {
            return '<a class="rule-service ifw-wp-icon-filter hint--bottom" data-hint="'. implode(', ', $filter) .'"></a>';
        }
        return '';
    }

    /**
     * @param $items
     * @internal param $item
     * @return string
     */
    public function getColumnStatusBefore($items)
    {
        return htmlentities($this->_getStatusLabel($items['status_before']));
    }

    /**
     * @param $items
     * @internal param $item
     * @return string
     */
    public function getColumnStatusAfter($items)
    {
        return htmlentities($this->_getStatusLabel($items['status_after']));
    }

    /**
     * @param $status
     * @return string|void
     */
    protected function _getStatusLabel($status)
    {
        $result = '';
        if ($status == 'new') {
            $result = __('New', 'ifw');
        } elseif ($status == 'anything') {
            $result = __('anything', 'psn');
        } elseif ($status == 'not_published') {
            $result = __('Not published', 'psn');
        } elseif ($status == 'not_private') {
            $result = __('Not private', 'psn');
        } elseif ($status == 'not_pending') {
            $result = __('Not pending', 'psn');
        } elseif ($status == 'not_trash') {
            $result = __('Not trash', 'psn');
        } else {
            $result = IfwPsn_Wp_Proxy_Post::getStatusLabel($status);
        }
        return $result;
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
            $actions['edit'] = sprintf('<a href="?page=%s&controller=rules&appaction=edit&id=%s">'. __('Edit', 'psn') .'</a>', $_REQUEST['page'], $item['id']);
            $actions['delete'] = sprintf('<a href="?page=%s&controller=rules&appaction=delete&id=%s&nonce=%s" class="delConfirm">'. __('Delete', 'psn') .'</a>',
                $_REQUEST['page'], $item['id'], wp_create_nonce( IfwPsn_Zend_Controller_ModelBinding::getDeleteNonceAction($this->getModelMapper()->getSingular(), $item['id']) ));

            $actionsFilter = IfwPsn_Wp_Proxy_Filter::apply('psn_rules_col_name_actions', array('actions' => $actions, 'item' => $item));

            $actions = $actionsFilter['actions'];

            //Return the title contents
            $result = sprintf('<b>%1$s</b>%2$s',
                /*$1%s*/ $item['name'],
                /*$2%s*/ $this->row_actions($actions)
            );
        }

        return $result;
    }

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
                'activate' => __('Activate', 'psn'),
                'deactivate' => __('Deactivate', 'psn'),
            );
            $actions = IfwPsn_Wp_Proxy_Filter::apply('psn_rules_bulk_actions', $actions);
        }

        return $actions;
    }

    public function process_bulk_action()
    {

    }

    /**
     * (non-PHPdoc)
     * @see WP_List_Table::display()
     */
    public function afterDisplay()
    {
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

                if (confirm('<?php _e('Do you want to copy this rule?', 'psn'); ?>')) {
                    document.location.href = targetUrl;
                }
            });
        </script>
        <?php
    }
}
