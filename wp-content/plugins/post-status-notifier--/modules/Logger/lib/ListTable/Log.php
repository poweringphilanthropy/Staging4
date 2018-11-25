<?php
/**
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Log.php 403 2015-08-21 20:40:03Z timoreithde $
 */ 
class Psn_Module_Logger_ListTable_Log extends IfwPsn_Wp_Plugin_ListTable_Abstract
{
    /**
     *
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, $options = array())
    {
        parent::__construct($pm, $options);

        if ($this->isAjax()) {
            require_once dirname(__FILE__) . '/../Metabox/Logs.php';
            $metaBox = new Psn_Module_Logger_Metabox_Logs($pm);

            $this->setFormAction(sprintf(IfwPsn_Wp_Proxy_Blog::getUrl() . '/wp-admin/admin-ajax.php?action=%s&nonce=%s',
                $metaBox->getAjaxRequest()->getAction(),
                $metaBox->getAjaxRequest()->getNonce()));
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'logs';
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_ListTable_Abstract::getColumns()
     */
    public function getColumns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'timestamp' => __('Timestamp', 'ifw'),
            'type' => __('Type', 'ifw'),
            'message' => __('Title', 'ifw'),
            'priority' => __('Priority', 'ifw'),
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
        return 'message';
    }

    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_ListTable_Data_Interface::getSortableColumns()
     */
    public function getSortableColumns()
    {
        return $sortable_columns = array(
            'priority' => array('priority', false),
            'timestamp' => array('timestamp', true),
            'type' => array('type', false),
            'message' => array('message', false),
        );
    }

    /**
     * Custom column handling
     *
     * @param $items
     * @return string
     */
    public function getColumnTimestamp($items)
    {
        return IfwPsn_Wp_Date::format($items['timestamp']);
    }

    /**
     * Custom column handling
     *
     * @param $items
     * @return string
     */
    public function getColumnType($items)
    {
        $result = '';

        switch((int)$items['type']) {
            case Psn_Logger_Bootstrap::LOG_TYPE_INFO:
                $value = __('Info', 'psn_log');
                $icon = 'info';
                break;
            case Psn_Logger_Bootstrap::LOG_TYPE_SENT_MAIL:
                $value = __('Email', 'psn_log');
                $icon = 'mail';
                break;
            case Psn_Logger_Bootstrap::LOG_TYPE_SUCCESS:
                $value = __('Success', 'psn_log');
                $icon = 'true';
                break;
            case Psn_Logger_Bootstrap::LOG_TYPE_FAILURE:
                $value = __('Failure', 'psn_log');
                $icon = 'false';
                break;
            default:
                $value = '';
        }

        if (!empty($value)) {
            $result = sprintf('<span class="ifw-wp-icon-%s">%s</span>', $icon, $value);
        }

        return $result;
    }

    /**
     * Custom column handling
     *
     * @param $items
     * @return string
     */
    public function getColumnPriority($items)
    {
        return IfwPsn_Wp_Plugin_Logger::$priorityInfo[(int)$items['priority']];
    }

    /**
     * Custom column handling for name
     *
     * @param array $items
     * @return string
     */
    public function getColumnMessage($items)
    {
        if (!$this->isMetaboxEmbedded() && !empty($items['extra'])) {
            //Build row actions
            $actions = array(
                'details' => sprintf('<a href="#%s" class="loadDetails">'. __('Show details', 'psn') .'</a>', $items['id']),
            );

            //Return the title contents
            $result = sprintf('%1$s%2$s',
                /*$1%s*/ htmlentities($items['message']),
                /*$2%s*/ $this->row_actions($actions)
            );
        } else {
            $result = $items['message'];
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
                'delete' => __('Delete'),
                'clear_type_mail' => __('Clear type "Email"', 'psn_log'),
                'clear_type_log' => __('Clear type "Info"', 'psn_log'),
                'clear' => __('Clear complete log', 'psn_log'),
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
        require_once dirname(__FILE__) . '/Ajax/Details.php';
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

        jQuery(document).ready(function($) {
            if (typeof ajaxurl == 'undefined') {
                var ajaxurl = 'admin-ajax.php';
            }
            $('.loadDetails').each(function(index) {
                $(this).click(function(e) {
                    e.preventDefault();
                    var logId = $(this).attr('href').substring(1);
                    var url = this.href;
                    // show ajax loading animation
                    var dialog = $('<div style="display:none" class="ifw-dialog-loading-default"></div>').appendTo('body');
                    var data = {
                        action: '<?php echo $ajaxDetails->getAction(); ?>',
                        nonce: '<?php echo $ajaxDetails->getNonce(); ?>',
                        logId: logId,
                        dataType: 'json'
                    };
                    // open the dialog
                    dialog.dialog({
                        dialogClass: 'wp-dialog',
                        // add a close listener to prevent adding multiple divs to the document
                        close: function(event, ui) {
                            // remove div with all data and events
                            dialog.remove();
                        },
                        modal: true,
                        resizable: true,
                        closeOnEscape: true,
                        width: 700,
                        height: 500
                    });
                    // load remote content
                    dialog.load(
                        ajaxurl,
                        data, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
                        function (responseText, textStatus, XMLHttpRequest) {
                            // remove the loading class
                            dialog.removeClass('ifw-dialog-loading-default');
                        }
                    );
                });
            });
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
        return 'Psn_Module_Logger_Model_Log';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Interface
     */
    public function getModelMapper()
    {
        return Psn_Module_Logger_Model_Mapper_Log::getInstance();
    }

    /**
     * @return Psn_Module_Logger_ListTable_Data_Log
     */
    public function getData()
    {
        require_once dirname(__FILE__) . '/Data/Log.php';
        return new Psn_Module_Logger_ListTable_Data_Log();
    }
}
