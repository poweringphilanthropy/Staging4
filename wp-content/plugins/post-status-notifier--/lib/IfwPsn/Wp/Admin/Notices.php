<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Notices.php 449 2015-08-09 21:33:19Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Admin_Notices 
{
    /**
     * @var string
     */
    protected $_namespace;

    /**
     * @var string
     */
    protected $_metaKey;

    /**
     * @var array
     */
    protected $_notices = array();



    /**
     * @param $namespace
     */
    public function __construct($namespace)
    {
        $this->_namespace = $namespace;
        $this->_metaKey = $this->_namespace . '_admin_notices';
    }

    /**
     * @param $message
     * @param string $type
     * @param string $code
     * @param int $hops
     */
    public function add($message, $type = 'error', $code = 'error', $hops = 0, $persist = false)
    {
        if (!in_array($type, array('error', 'updated'))) {
            $type = 'error';
        }

        $notice = array(
            'code' => $this->_namespace . '-' . $code,
            'message' => $message,
            'type' => $type,
            'hops' => $hops
        );

        array_push($this->_notices, $notice);

        if ($persist) {
            $this->_persist($this->_notices);
        }
    }

    /**
     * @return bool
     */
    public function hasMessage()
    {
        return count($this->_notices) > 0;
    }

    /**
     * @return bool
     */
    public function hasErrorMessage()
    {
        foreach ($this->_notices as $notice) {
            if ($notice['type'] == 'error') {
                return true;
            }
        }
    }

    /**
     * @return bool
     */
    public function hasUpdatedMessage()
    {
        foreach ($this->_notices as $notice) {
            if ($notice['type'] == 'updated') {
                return true;
            }
        }
    }

    /**
     * Adds an error notice (red)
     * @param $message
     * @param string $code
     */
    public function addError($message, $code = 'error')
    {
        $this->add($message, 'error', $code);
    }

    /**
     * Adds an error notice (red)
     * @param $message
     * @param string $code
     */
    public function persistError($message, $code = 'error', $hops = 0)
    {
        $this->add($message, 'error', $code, $hops, true);
    }

    /**
     * Adds an update notice (green)
     * @param $message
     * @param string $code
     */
    public function addErrorRedirected($message, $code = 'error')
    {
        $this->add($message, 'error', $code, 1);
    }

    /**
     * Adds an update notice (green)
     * @param $message
     * @param string $code
     */
    public function addUpdated($message, $code = 'updated')
    {
        $this->add($message, 'updated', $code);
    }

    /**
     * Adds an update notice (green)
     * @param $message
     * @param string $code
     */
    public function persistUpdated($message, $code = 'updated', $hops = 0)
    {
        $this->add($message, 'updated', $code, $hops, true);
    }

    /**
     * Adds an update notice (green)
     * @param $message
     * @param string $code
     */
    public function addUpdatedRedirected($message, $code = 'updated')
    {
        $this->add($message, 'updated', $code, 1);
    }

    /**
     * For backwards compat
     * @param $msg
     * @param $type
     * @deprecated
     */
    public function addMessage($msg, $type = null)
    {
        if ($type == 'error') {
            $this->addError($msg);
        } else {
            $this->addUpdated($msg);
        }
    }

    /**
     * Shows the admin notices
     */
    public function show()
    {
        $notices = $this->_get();

        if (!is_array($notices)) {
            return;
        }

        for ($i = 0; $i < count($notices); $i++) {
            if (isset($notices[$i]['hops']) && $notices[$i]['hops'] > 0) {
                $notices[$i]['hops']--;
                continue;
            } else {
                add_settings_error(
                    $this->_namespace,
                    $notices[$i]['code'],
                    $notices[$i]['message'],
                    $notices[$i]['type']
                );
                unset($notices[$i]);
            }
        }

        $sceen = get_current_screen();
        if (strstr($sceen->id, 'settings_page') === false) {
            settings_errors($this->_namespace, true);
        }

        if (!empty($notices)) {
            $this->_persist($notices);
        } else {
            $this->_delete();
        }
    }

    /**
     * @param boolean $autoShow
     */
    public function setAutoShow($autoShow)
    {
        if (is_bool($autoShow)) {
            add_action('admin_notices', array($this, 'show'));
        }
    }

    protected function _get()
    {
        return array_merge($this->_getPersisted(), $this->_notices);
    }

    protected function _getPersisted()
    {
        $result = get_user_meta(get_current_user_id(), $this->_metaKey, true);
        if (!is_array($result)) {
            $result = array();
        }
        return $result;
    }

    protected function _delete()
    {
        $this->_notices = array();
        delete_user_meta(get_current_user_id(), $this->_metaKey);
    }

    protected function _persist(array $notices)
    {
        if (!empty($notices)) {
            update_user_meta(get_current_user_id(), $this->_metaKey, $notices);
        }
    }
}
