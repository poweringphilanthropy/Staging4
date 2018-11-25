<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * WP_List_Table Abstract
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 441 2015-07-19 20:49:11Z timoreithde $
 * @package  IfwPsn_Wp
 */

if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

abstract class IfwPsn_Wp_Plugin_ListTable_Abstract extends WP_List_Table
{
    /**
     * @var
     */
    protected $_options;

    /**
     * @var string
     */
    protected $_id;

    /**
     * @var IfwPsn_Wp_Plugin_ListTable_Data_Interface
     */
    protected $_data;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;
    
    /**
     * Default items per page
     * @var int
     */
    protected $_itemsPerPage = 10;

    /**
     * @var string
     */
    protected $_formAction = '';

    /**
     * @var
     */
    protected $_wpActionPrefix;

    /**
     * @var bool
     */
    protected $_metaboxEmbedded = false;

    /**
     * @var bool
     */
    protected $_ajax = false;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param array $options
     * @throws Exception
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, $options = array())
    {
        $args = array_merge(array(
            'singular' => $this->getModelMapper()->getSingular(),
            'plural' => $this->getModelMapper()->getPlural()
        ), $options);

        $this->_setOptions($args);

        parent::__construct($this->_options);

        $this->_pm = $pm;

        $this->_init();
    }

    /**
     * @param $args
     * @internal param $options
     */
    protected function _setOptions($args)
    {
        $this->_options = $args;

        if (isset($this->_options['metabox_embedded']) && $this->_options['metabox_embedded'] == true) {
            $this->setMetaboxEmbedded(true);
            unset($this->_options['metabox_embedded']);
        }
        if (isset($this->_options['ajax']) && $this->_options['ajax'] == true) {
            $this->_ajax = true;
        }
    }

    protected function _init()
    {
        $this->_id = $this->getId();

        $data = $this->getData();
        if (!($data instanceof IfwPsn_Wp_Plugin_ListTable_Data_Interface)) {
            throw new Exception('Invalid list table data object');
        }
        $this->_data = $data;

        $this->_wpActionPrefix = $this->_pm->getAbbrLower() . '_listtable_' . $this->_id . '_';

        if (isset($this->_options['ajax']) && $this->_options['ajax'] == true) {
            IfwPsn_Wp_Proxy_Action::add($this->_wpActionPrefix . 'after_display', array($this, 'loadAjaxScript'));
        }
        IfwPsn_Wp_Proxy_Action::add($this->_wpActionPrefix . 'after_display', array($this, 'loadScriptReplaceSearchBox'));

        if (method_exists($this, 'afterDisplay')) {
            IfwPsn_Wp_Proxy_Action::add($this->_wpActionPrefix . 'after_display', array($this, 'afterDisplay'));
        }
    }

    /**
     * WP function
     * Defines two arrays controlling the behaviour of the table: $hidden and $sortable
     *
     * @see WP_List_Table::prepare_items()
     */
    public function prepare_items()
    {
        $this->_initColumnHeaders();
        $this->_initData();
        $this->_initPagination();
    }

    /**
     * WP function
     * Labels the columns on the top and bottom of the table
     *
     * @see WP_List_Table::get_columns()
     */
    public function get_columns()
    {
        return $this->_getColumns();
    }

    /**
     * WP function
     * Defines columns to be sortable
     * @return array
     */
    public function get_sortable_columns()
    {
        return $this->getSortableColumns();
    }

    /**
     * Init the column headers
     */
    protected function _initColumnHeaders()
    {
        $columns = $this->_getColumns();
        $hidden = $this->getHiddenColumns();
        $sortable = $this->getSortableColumns();

        $this->_column_headers = array($columns, $hidden, $sortable);
    }

    /**
     * Assigns the data to $items
     */
    protected function _initData()
    {
        $order = null;
        if (isset($_REQUEST['orderby'])) {
            $order = array($_REQUEST['orderby'] => $_REQUEST['order']);
        }

        $where = null;
        if (isset($_POST['s']) && !empty($_POST['s'])) {
            $where = esc_attr($_POST['s']);
        }

        // assign the data from IfwPsn_Wp_Plugin_ListTable_Data_Interface to the $items property
        $this->items = $this->_data->getItems($this->getItemsPerPage(), $this->getCurrentPage(), $order, $where);
    }

    /**
     * Inits list navigation
     */
    protected function _initPagination()
    {
        $this->set_pagination_args(array(
            'total_items' => $this->_data->getTotalItems(),                  
            'per_page' => $this->getItemsPerPage(),
            'total_pages' => ceil($this->_data->getTotalItems() / $this->getItemsPerPage())
        ));
    }

    /**
     * WP function
     * Handles default column handling if no custom method is provided
     *
     * @param array $item
     * @param string $column_name
     * @return string
     */
    function column_default($item, $column_name)
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Vendor/Zend/Filter/Word/UnderscoreToCamelCase.php';

        $filter = new IfwPsn_Vendor_Zend_Filter_Word_UnderscoreToCamelCase();
        $columnNameCamelCase = $filter->filter($column_name);
        $customColumnMethodName = 'getColumn'. $columnNameCamelCase;

        if (method_exists($this, $customColumnMethodName)) {

            // found a method for custom column handling, call it and pass the item
            return call_user_func(array($this, $customColumnMethodName), $item);

        } elseif (isset($item[$column_name])) {
            // print the value
            return $item[$column_name];

        } else {
            return print_r($item,true);
        }
    }

    /**
     * Renders additional table controls
     * Calls methods: getExtraControlsTop / getExtraControlsBottom
     *
     * @param $pos
     * @return mixed|void
     */
    public function extra_tablenav($pos)
    {
        $methodName = 'getExtraControls' . ucfirst($pos);

        IfwPsn_Wp_Proxy_Action::add($this->_wpActionPrefix . '_before_controls_top', $this);

        if (method_exists($this, $methodName)) {
            call_user_func(array($this, $methodName));
        }

        IfwPsn_Wp_Proxy_Action::add($this->_wpActionPrefix . '_after_controls_top', $this);
    }

    /**
     *
     */
    public function ajax_user_can() {
        die( 'function WP_List_Table::ajax_user_can() must be over-ridden in a sub-class.' );
    }
    
    /**
     * @return int the current page 
     */
    public function getCurrentPage()
    {
        return $this->get_pagenum();
    }
    
    /**
     * @return number $_itemsPerPage 
     */
    public function getItemsPerPage()
    {
        return $this->_itemsPerPage;
    }

    /**
     * @param number $_itemsPerPage
     */
    public function setItemsPerPage($_itemsPerPage)
    {
        $this->_itemsPerPage = $_itemsPerPage;
    }

    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_ListTable_Data_Interface::getHiddenColumns()
     */
    public function getHiddenColumns()
    {
        return array();
    }

    /**
     * @param boolean $metaboxEmbedded
     */
    public function setMetaboxEmbedded($metaboxEmbedded)
    {
        if (is_bool($metaboxEmbedded)) {
            $this->_metaboxEmbedded = $metaboxEmbedded;
        }
    }

    /**
     * @return boolean
     */
    public function isMetaboxEmbedded()
    {
        return $this->_metaboxEmbedded;
    }

    abstract public function getId();
    abstract public function getColumns();
    abstract public function getSortableColumns();
    abstract public function getModelName();

    /**
     * @return IfwPsn_Wp_Model_Mapper_Interface
     */
    abstract public function getModelMapper();
    abstract public function getData();

    /**
     * @return array
     */
    protected function _getColumns()
    {
        return IfwPsn_Wp_Proxy_Filter::apply(
            $this->_pm->getAbbrLower() . '_' . $this->_id . '_get_columns',
            $this->getColumns());
    }

    public function display()
    {
        $this->prepare_items();
        ?>
        <form id="<?php echo $this->_id; ?>" method="post" action="<?php echo $this->_formAction; ?>" class="ifw-listtable">

            <?php if (isset($_REQUEST['page'])): ?>
                <!-- if request has page value submit it for returning to current plugin page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <?php endif; ?>
            <?php if (isset($_REQUEST['appaction'])): ?>
                <!-- if request has appaction value submit it for returning to current plugin page -->
                <input type="hidden" name="appaction" value="<?php echo $_REQUEST['appaction'] ?>" />
            <?php endif; ?>
            <?php if (isset($this->_options['hidden_fields'])):
                foreach ($this->_options['hidden_fields'] as $field): ?>
                    <input type="hidden" name="<?php echo $field['name'] ?>" value="<?php echo $field['value'] ?>" />
                <?php
                endforeach;
                endif;
                ?>
            <?php
            IfwPsn_Wp_Proxy::doAction($this->_wpActionPrefix . 'before_display', $this);
            echo parent::display();
            IfwPsn_Wp_Proxy::doAction($this->_wpActionPrefix .  'after_display', $this);
            ?>
        </form>
        <?php
    }

    /**
     * Fetches the content echoed by self::display()
     *
     * @return string
     */
    public function fetch()
    {
        ob_start();
        $this->display();
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * Displays the code for the reload link
     */
    protected function displayReloadButton()
    {
        if ($this->isMetaboxEmbedded()): ?>
            <div class="reload"><a href="" class="ifw-wp-icon-reload"><?php _e('Reload', 'ifw'); ?></a></div>
        <?php
        endif;
    }

    /**
     * (non-PHPdoc)
     * @see WP_List_Table::display()
     */
    public function loadAjaxScript()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                // fetch search submit
                $('form#<?php echo $this->getId(); ?>').submit(function() {
                    var url = $('form#<?php echo $this->getId(); ?>').attr('action');
                    if (typeof url == 'undefined' || url == '') {
                        var url = $('form#<?php echo $this->getId(); ?> input[name="_wp_http_referer"]').val();
                    }

                    var paramObj = {};
                    $.each($('form#<?php echo $this->getId(); ?>').serializeArray(), function(_, kv) {
                        paramObj[kv.name] = kv.value;
                    });

                    refreshListTableRows(url, paramObj);
                    return false;
                });

                // reload
                if ($('form#<?php echo $this->getId(); ?> .reload').length > 0) {

                    if ($('form#<?php echo $this->getId(); ?> .reload').length > 0) {
                        var reload = $('form#<?php echo $this->getId(); ?> .tablenav.top > .reload').clone();
                        $('form#<?php echo $this->getId(); ?> .tablenav.top .actions').prepend(reload);
                        $('form#<?php echo $this->getId(); ?> .tablenav.top > .reload').remove();
                    }

                    $('form#<?php echo $this->getId(); ?> .reload').click(function(link) {

                        var url = $('form#<?php echo $this->getId(); ?>').attr('action');
                        if (typeof url == 'undefined' || url == '') {
                            var url = $('form#<?php echo $this->getId(); ?> input[name="_wp_http_referer"]').val();
                        }

                        var paramObj = {};
                        $.each($('form#<?php echo $this->getId(); ?>').serializeArray(), function(_, kv) {
                            paramObj[kv.name] = kv.value;
                        });
                        refreshListTableRows(url, paramObj);
                        return false;
                    });
                }

                // init pagination links
                if ($('form#<?php echo $this->getId(); ?> .pagination-links').length > 0) {
                    // prepare navigation links
                    var linkFirstPage = $('form#<?php echo $this->getId(); ?> .pagination-links .first-page');
                    var linkPrevPage = $('form#<?php echo $this->getId(); ?> .pagination-links .prev-page');
                    var linkNextPage = $('form#<?php echo $this->getId(); ?> .pagination-links .next-page');
                    var linkLastPage = $('form#<?php echo $this->getId(); ?> .pagination-links .last-page');
                    var linkCurrentPage = $('form#<?php echo $this->getId(); ?> .pagination-links .current-page');
                    var currentPageBottom = $('.tablenav.bottom .paging-input');
                    var totalPages = parseInt($('form#<?php echo $this->getId(); ?> .pagination-links .total-pages').html());

                    $('form#<?php echo $this->getId(); ?> .pagination-links a').click(function(link) {

                        if ($(link.currentTarget).hasClass('disabled')) {
                            return false;
                        }
                        var url = checkUrl($(link.currentTarget).attr('href'));

                        var response = refreshListTableRows(url);

                        var page = getParameterByName(url, 'paged');
                        if (page == '') {
                            page = 1;
                        }

                        page = parseInt(page);
                        linkCurrentPage.val(page);
                        currentPageBottom.html(currentPageBottom.html().replace(/[\d*] of/, page + ' of'));

                        if (page > 1) {
                            linkFirstPage.removeClass('disabled');
                            linkPrevPage.removeClass('disabled');
                        } else {
                            linkFirstPage.addClass('disabled');
                            linkPrevPage.addClass('disabled');
                        }
                        if (page >= totalPages) {
                            linkNextPage.addClass('disabled');
                            linkLastPage.addClass('disabled');
                        } else {
                            linkNextPage.removeClass('disabled');
                            linkLastPage.removeClass('disabled');
                        }

                        if (page-1 > 0) {
                            linkPrevPage.attr('href', url.replace(/paged=[\d*]/, 'paged=' + (page-1)));
                        }
                        if (page+1 <= totalPages) {
                            if (url.indexOf('paged=') >= 0) {
                                linkNextPage.attr('href', url.replace(/paged=[\d*]/, 'paged=' + (page+1)));
                            } else {
                                linkNextPage.attr('href', url += '&paged=' + (page+1));
                            }
                        }
                        return false;
                    });
                }

                // prepared sortable columns
                $('form#<?php echo $this->getId(); ?> th a').click(function(link) {

                    var url = checkUrl($(link.currentTarget).attr('href'));

                    if ((url.indexOf('=asc') >= 0)) {
                        var order_old = 'desc';
                        var order_new = 'asc';
                    } else {
                        var order_old = 'asc';
                        var order_new = 'desc';
                    }

                    // get the columns classname
                    var parent_classes = $(link.currentTarget).parent().attr('class').split(' ');

                    $(parent_classes).each(function(index, classname) {
                        if (classname.indexOf('column-') >= 0) {
                            $('th.' + classname).removeClass(order_old).addClass(order_new);
                            $('th.' + classname).find('a').attr('href', url.replace('='+ order_new, '='+ order_old));
                            refreshListTableRows(url);
                        }
                    });
                    return false;
                });

                // the ajax request
                function refreshListTableRows(url, data) {

                    var thLength = $('form#<?php echo $this->getId(); ?> table thead tr th').length;
                    var tableRows = $('form#<?php echo $this->getId(); ?> table tbody tr').length;

                    $('form#<?php echo $this->getId(); ?> table tbody#the-list').html('<tr><td colspan="'+ thLength +'" class="ifw-listtable-ajax-reload" style="height: '+(tableRows*24) +'px;">&nbsp;</td></tr>');

                    var postdata = {refresh_rows: true};
                    if (typeof data != 'undefined') {
                        postdata = $.extend(postdata, data);
                    }

                    var jqxhr = $.ajax( url, {
                            type: 'POST',
                            dataType: 'json',
                            data: postdata}
                    );
                    jqxhr.done(function(response) {
                        $('form#<?php echo $this->getId(); ?> table tbody#the-list').html(response.rows);
                    })
                    jqxhr.fail(function(response) { console.log("error in list table reload"); });
                }

                // helper function to get query param
                function getParameterByName(url, name)
                {
                    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
                    var regexS = "[\\?&]" + name + "=([^&#]*)";
                    var regex = new RegExp(regexS);
                    var results = regex.exec(url);
                    if(results == null)
                        return "";
                    else
                        return decodeURIComponent(results[1].replace(/\+/g, " "));
                }

                // rewrite url on dashboard
                function checkUrl(url) {
                    if (url.indexOf('index.php') > 0) {
                        // rewrite url on dashboard
                        var url2 = $('form#<?php echo $this->getId(); ?>').attr('action');
                        if (url2.indexOf('admin-ajax.php') > 0) {
                            url2 = url2.substring(url2.indexOf('admin-ajax.php')) + '&';
                            if (url.indexOf('index.php?') > 0) {
                                url = url.replace('index.php?', url2);
                            } else {
                                url = url.replace('index.php', url2);
                            }
                        }
                    }
                    return url;
                }
            });
        </script>
    <?php
    }

    public function loadScriptReplaceSearchBox()
    {
        ?>
        <script type="text/javascript">
        jQuery(document).ready( function($) {
            if ($('form#<?php echo $this->getId(); ?> .search-box').length > 0) {
                var searchbox = $('form#<?php echo $this->getId(); ?> .tablenav.top > .search-box').clone();
                var searchboxHTML = searchbox.html();
                searchboxHTML = searchboxHTML.replace(/<p>/g, '<div>').replace(/<\/p>/g, '</div>');
                $('form#<?php echo $this->getId(); ?> .tablenav.top .actions').append(searchboxHTML);
                $('form#<?php echo $this->getId(); ?> .tablenav.top > .search-box').remove();
            }


        });
        </script>
        <?php
    }

    /**
     * @param string $formAction
     */
    public function setFormAction($formAction)
    {
        $this->_formAction = $formAction;
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->_formAction;
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        return $this->_ajax === true;
    }

    /**
     * @return bool|string|void
     */
    public function getBulkAction()
    {
        if (isset($_POST['action']) && $_POST['action'] != '-1') {
            $action = esc_attr($_POST['action']);
        } elseif (isset($_POST['action2']) && $_POST['action2'] != '-1') {
            $action = esc_attr($_POST['action2']);
        } else {
            $action = false;
        }
        return $action;
    }

    /**
     * @return false|int
     */
    public function verifyBulk()
    {
        return wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural']);
    }

    /**
     * @return bool
     */
    public function hasValidBulkRequest()
    {
        return $this->getBulkAction() !== false && $this->verifyBulk() !== false;
    }

}
