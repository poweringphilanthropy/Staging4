<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Upload.php 304 2014-07-27 17:29:16Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Upload 
{
    /**
     * @var null|string
     */
    protected $_id;

    /**
     * @var null|string
     */
    protected $_error;

    /**
     * @var null|array
     */
    protected $_fileInfo;

    /**
     * @var array
     */
    protected $_mimeTypes = array();



    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->_id = $id;

        $this->_init();
    }

    /**
     * Initializes the file info if the file was uploaded
     */
    protected function _init()
    {
        if ($this->isUploaded() && isset($_REQUEST['_wpnonce']) &&
            wp_verify_nonce($_REQUEST['_wpnonce'], $this->_getNonceName())) {
            $this->_initUploadedFileInfo();
        }
    }

    /**
     * @return bool
     */
    public function isUploaded()
    {
        return isset($_FILES) && isset($_FILES[$this->_id]);
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->_error === null;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * @param $action
     * @param null $submitText
     */
    public function displayForm($action, $submitText = null)
    {
        $bytes = wp_max_upload_size();
        $size = size_format( $bytes );
        $upload_dir = IfwPsn_Wp_Proxy_Blog::getUploadDir();

        if ($submitText == null) {
            $submitText = __('Upload', 'ifw');
        }

        if ( ! empty( $upload_dir['error'] ) ) :
            ?><div class="error"><p><?php _e('Before you can upload your import file, you will need to fix the following error:'); ?></p>
            <p><strong><?php echo $upload_dir['error']; ?></strong></p></div><?php
        else :

            $allowedExtensions = $this->getMimeTypes() != null ? implode(',', array_unique(array_keys($this->getMimeTypes()))) : null;
            $label = sprintf('Choose a file from your computer (Maximum size: %s%s)', $size, $allowedExtensions !== null ? ', ' . __('allowed extensions: ') . $allowedExtensions : '');
            ?>
            <form enctype="multipart/form-data" id="<?php echo $this->_id; ?>-upload-form" method="post" class="wp-upload-form" action="<?php echo esc_url($action); ?>">
                <?php echo wp_nonce_field($this->_getNonceName()); ?>
                <p>
                    <label for="upload-<?php echo $this->_id; ?>"><?php echo $label; ?></label>
                    <input type="file" id="upload-<?php echo $this->_id; ?>" name="<?php echo $this->_id; ?>" size="25" />
                    <input type="hidden" name="action" value="upload" />
                    <input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
                </p>
                <?php submit_button( $submitText, 'button' ); ?>
            </form>
        <?php
        endif;
    }

    /**
     * @return mixed
     */
    public function getFileInfo()
    {
        return $this->_fileInfo;
    }

    public function _initUploadedFileInfo()
    {
        if (!function_exists('wp_handle_upload')) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        $uploadedfile = $_FILES[$this->_id];
        $upload_overrides = array( 'test_form' => false );
        if ($this->hasMimeTypes()) {
            $upload_overrides['mimes'] = $this->getMimeTypes();
        }
        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

        if (isset($movefile['error'])) {
            $this->_error = $movefile['error'];
        } else {
            $this->_fileInfo = array_merge($movefile, pathinfo($movefile['file']));
        }
    }

    public function remove()
    {
        if (file_exists($this->_fileInfo['file'])) {
            unlink($this->_fileInfo['file']);
        }
    }

    /**
     * @return string
     */
    protected function _getNonceName()
    {
        return $this->_id . '-upload';
    }

    /**
     * @param array $mimeTypes
     */
    public function setMimeTypes($mimeTypes)
    {
        $this->_mimeTypes = $mimeTypes;
    }

    /**
     * @return array
     */
    public function getMimeTypes()
    {
        return $this->_mimeTypes;
    }

    /**
     * @return bool
     */
    public function hasMimeTypes()
    {
        return count($this->_mimeTypes) > 0;
    }


}
 