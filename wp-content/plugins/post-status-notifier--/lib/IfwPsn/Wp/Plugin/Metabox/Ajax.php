<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Ajax Metabox
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Ajax.php 446 2015-07-30 21:57:38Z timoreithde $
 * @package  IfwPsn_Wp_Plugin_Admin_Menu_Metabox
 */
require_once dirname(__FILE__) . '/Abstract.php';

abstract class IfwPsn_Wp_Plugin_Metabox_Ajax extends IfwPsn_Wp_Plugin_Metabox_Abstract
{
    /**
     * @var IfwPsn_Wp_Ajax_Request_Abstract
     */
    protected $_ajaxRequest;

    
    
    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct (IfwPsn_Wp_Plugin_Manager $pm, $ajaxRequest = null)
    {
        parent::__construct($pm);

        if ($ajaxRequest instanceof IfwPsn_Wp_Ajax_Request) {
            $this->_ajaxRequest = $ajaxRequest;
        }
    }

    /**
     * @param IfwPsn_Wp_Ajax_Request $ajaxRequest
     */
    public function setAjaxRequest(IfwPsn_Wp_Ajax_Request $ajaxRequest)
    {
        $this->_ajaxRequest = $ajaxRequest;
    }

    /**
     * Retrieves the ajax request object
     * 
     * @return IfwPsn_Wp_Ajax_Request
     */
    public function getAjaxRequest()
    {
        return $this->_ajaxRequest;
    }
        
    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::render()
     */
    public function render()
    {
        ?>
        <div class="ifw_wp_metabox_loading"></div>
        <script type="text/javascript">
            //<![CDATA[
            function metabox_<?php echo $this->_id; ?>_reload() {

                jQuery('#<?php echo $this->_id; ?>').find('.inside').html('<div class="ifw_wp_metabox_loading"></div>');

                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                if (typeof ajaxurl == 'undefined') {
                    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                }

                var data = {
                    action: '<?php echo $this->getAjaxRequest()->getAction(); ?>',
                    nonce: '<?php echo $this->getAjaxRequest()->getNonce(); ?>'
                };

                jQuery.getJSON(ajaxurl, data, function(response) {
                    jQuery('#<?php echo $this->_id; ?>').find('.inside').html(response.data.html);
                });
            }
            jQuery(document).ready(function($) {
                metabox_<?php echo $this->_id; ?>_reload();
            });
            //]]>
        </script>        
        <?php 
    }
        
}
