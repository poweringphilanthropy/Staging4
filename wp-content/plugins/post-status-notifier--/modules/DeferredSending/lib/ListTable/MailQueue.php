<?php
/**
 * MailQueue list table
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: MailQueue.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_DeferredSending_ListTable_MailQueue extends IfwPsn_Wp_Plugin_ListTable_Abstract
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'mailqueue';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'id' => __('ID', 'psn'),
            'subject' => __('Subject', 'psn'),
            'to' => __('TO', 'psn'),
            'added' => __('Added', 'psn_def'),
            //'scheduled' => __('Scheduled', 'psn_def'),
            'tries' => __('Tries', 'psn_def'),
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
        return 'subject';
    }

    /**
     * @return array
     */
    public function getSortableColumns()
    {
        return $sortable_columns = array(
            'id' => array('id', false),
            'subject' => array('subject', false),
            'to' => array('to', false),
            'added' => array('added', false),
            //'scheduled' => array('Scheduled', false),
            'tries' => array('tries', false),
        );
    }

    /**
     * Custom column handling for TO
     *
     * @param unknown_type $item
     * @return string
     */
    public function getColumnTo($item)
    {
        $result = htmlentities($item['to']);

        if (strlen($result) > 200) {
            $result = substr($result, 0, 200) . ' ... ';
        }

        return $result;
    }

    /**
     * Custom column handling for name
     *
     * @param unknown_type $item
     * @return string
     */
    public function getColumnSubject($item)
    {
        $result = htmlentities($item['subject']);

        if (!$this->isMetaboxEmbedded()) {
            //Build row actions
            $actions = array(
                'details' => sprintf('<a href="#%s" class="loadDetails">'. __('Show details', 'psn') .'</a>', $item['id']),
            );
            $actions['delete'] = sprintf('<a href="?page=%s&mod=deferredsending&controller=deferredsending&appaction=delete&nonce=%s&id=%s" class="delConfirm">'. __('Delete', 'psn') .'</a>',
                $_REQUEST['page'],
                wp_create_nonce(IfwPsn_Zend_Controller_ModelBinding::getDeleteNonceAction($this->getModelMapper()->getSingular(), $item['id'])),
                $item['id']);

            //Return the title contents
            $result = sprintf('%1$s%2$s',
                /*$1%s*/ $item['subject'],
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
    public function getColumnAdded($items)
    {
        return IfwPsn_Wp_Date::format($items['added']);
    }

    /**
     * Custom column handling
     *
     * @param $items
     * @return string
     */
    public function getColumnScheduled($items)
    {
        return IfwPsn_Wp_Date::format($items['scheduled']);
    }

    /**
     *
     */
    public function getExtraControlsTop()
    {
        $this->search_box(__('Search'), 'subject');
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
                'reset' => __('Reset (delete all)', 'psn_def'),
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
        $ajaxDetails = new Psn_Module_DeferredSending_ListTable_Ajax_Details();

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

            $('#mailqueue_run_help_toggle').click(function() {
                $('#mailqueue_run_help').toggle();
            })
            if (typeof ajaxurl == 'undefined') {
                var ajaxurl = 'admin-ajax.php';
            }
            $('.loadDetails').each(function(index) {
                $(this).click(function(e) {
                    e.preventDefault();
                    var mailId = $(this).attr('href').substring(1);
                    var url = this.href;
                    // show ajax loading animation
                    var dialog = $('<div style="display:none" class="ifw-dialog-loading-default"></div>').appendTo('body');
                    var data = {
                        action: '<?php echo $ajaxDetails->getAction(); ?>',
                        nonce: '<?php echo $ajaxDetails->getNonce(); ?>',
                        mailId: mailId,
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
        return 'Psn_Module_DeferredSending_Model_MailQueue';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Interface
     */
    public function getModelMapper()
    {
        return Psn_Module_DeferredSending_Model_Mapper_MailQueue::getInstance();
    }

    /**
     * @return Psn_Module_DeferredSending_ListTable_Data_MailQueue
     */
    public function getData()
    {
        return new Psn_Module_DeferredSending_ListTable_Data_MailQueue();
    }
}
