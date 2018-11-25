<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * WP pointer abstraction
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Pointer.php 377 2014-12-30 20:10:13Z timoreithde $
 */ 
class IfwPsn_Wp_Plugin_Menu_Pointer
{
    /**
     * @var string
     */
    protected $_id;

    /**
     * @var string
     */
    protected $_header;

    /**
     * @var string
     */
    protected $_content;

    /**
     * top / bottom / left / right
     * @var string
     */
    protected $_edge = 'left';

    /**
     * @var string
     */
    protected $_align = 'top';

    /**
     * @var string
     */
    protected $_target;

    /**
     * @var
     */
    protected $_width;



    /**
     * @param $id
     */
    public function __construct($id)
    {
        $this->_id = $id;
    }

    /**
     * @param $target
     */
    public function renderTo($target)
    {

        $this->_target = $target;

        if ($this->_isValid()) {
            // enqueue scripts and styles
            IfwPsn_Wp_Proxy_Script::loadAdmin('wp-pointer', false, array('jquery'));
            IfwPsn_Wp_Proxy_Style::loadAdmin('wp-pointer');

            IfwPsn_Wp_Proxy_Action::addAdminFooterCurrentScreen(array($this, 'renderScript'));
            IfwPsn_Wp_Proxy_Action::add('admin_footer-post-new.php', array($this, 'renderScript'));
            IfwPsn_Wp_Proxy_Action::add('admin_footer-edit.php', array($this, 'renderScript'));
        }
    }

    protected function _isValid()
    {
        $result = true;

        if (!$this->_isValidBlogVersion() ||
            $this->_isDismissed() ||
            empty($this->_id) || empty($this->_target) || empty($this->_content)) {

            $result = false;
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function _isValidBlogVersion()
    {
        return IfwPsn_Wp_Proxy_Blog::getVersion() >= '3.3';
    }

    /**
     * @return bool
     */
    protected function _isDismissed()
    {
        $dismissed = IfwPsn_Wp_Proxy_User::getCurrentUserMetaSingle('dismissed_wp_pointers');

        if (!is_array($dismissed)) {
            $dismissed = explode(',', $dismissed);
        }

        return in_array($this->_id, $dismissed);
    }

    /**
     * Renders javascript for each pointer
     */
    public function renderScript()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
            $('<?php echo $this->_target; ?>').pointer({
                pointerClass: 'wp-pointer wp-pointer-<?php echo $this->_id; ?>',
                target: '<?php echo $this->_target; ?>',
                content: '<?php printf('<h3>%s</h3><p>%s</p>', $this->_header, $this->_content); ?>',
                position: {
                    edge: '<?php echo $this->_edge; ?>',
                    align: '<?php echo $this->_align; ?>'
                },
                close: function() {
                    $.post( ajaxurl, {
                        pointer: '<?php echo $this->_id; ?>',
                        action: 'dismiss-wp-pointer'
                    });
                }
                <?php if ($this->_width !== null && is_numeric($this->_width)): ?>, pointerWidth: <?php echo $this->_width; ?><?php endif; ?>
            }).pointer('open');
        });
        </script>
        <?php
    }

    /**
     * @param string $header
     * @return $this
     */
    public function setHeader($header)
    {
        $this->_header = $header;
        return $this;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    /**
     * @param $edge
     * @return $this
     */
    public function setEdge($edge)
    {
        $this->_edge = $edge;
        return $this;
    }

    /**
     * @param $align
     * @return $this
     */
    public function setAlign($align)
    {
        $this->_align = $align;
        return $this;
    }

    /**
     * @param mixed $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

}
