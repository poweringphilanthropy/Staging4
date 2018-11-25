<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Wp Proxy
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Proxy.php 231 2014-03-04 21:12:53Z timoreithde $
 * @package  IfwPsn_Wp
 */
class IfwPsn_Wp_Proxy
{
    /**
     * @var IfwPsn_Wp_Proxy
     */
    public static $_instances = array();
    
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;
    
    /**
     * @var IfwPsn_Wp_Proxy_Script
     */
    protected $_scriptProxy;
    
    /**
     * @var IfwPsn_Wp_Proxy_Style
     */
    protected $_styleProxy;
    
    /**
     * @var IfwPsn_Wp_Proxy_JQuery
     */
    protected $_jqueryProxy;
    
    /**
     * @var IfwPsn_Wp_Proxy_WPML
     */
    protected $_wpmlProxy;
    
    
    /**
     * Retrieves singleton IfwPsn_Wp_Proxy object
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return IfwPsn_Wp_Proxy
     */
    public static function getInstance(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (!isset(self::$_instances[$pm->getAbbr()])) {
            self::$_instances[$pm->getAbbr()] = new self($pm);
        }
        return self::$_instances[$pm->getAbbr()];
    }
    
    /**
     * @param IfwPsn_Wp_Proxy $pm
     */
    protected function __construct($pm)
    {
        $this->_pm = $pm;
    }
    
    /**
     * @return IfwPsn_Wp_Proxy_Script
     */
    public function script()
    {
        if ($this->_scriptProxy == null) {
            $this->_scriptProxy = new IfwPsn_Wp_Proxy_Script($this, $this->_pm);
        }
        return $this->_scriptProxy;
    }
    
    /**
     * @return IfwPsn_Wp_Proxy_Style
     */
    public function style()
    {
        if ($this->_styleProxy == null) {
            $this->_styleProxy = new IfwPsn_Wp_Proxy_Style($this, $this->_pm);
        }
        return $this->_styleProxy;
    }
    
    /**
     * @return IfwPsn_Wp_Proxy_JQuery
     */
    public function jQuery()
    {
        if ($this->_jqueryProxy == null) {
            $this->_jqueryProxy = new IfwPsn_Wp_Proxy_JQuery($this, $this->_pm);
        }
        return $this->_jqueryProxy;
    }
    
    /**
     * @return IfwPsn_Wp_Proxy_WPML
     */
    public function WPML()
    {
        if ($this->_wpmlProxy == null) {
            $this->_wpmlProxy = new IfwPsn_Wp_Proxy_WPML($this, $this->_pm);
        }
        return $this->_wpmlProxy;
    }
    
    /**
     * Retrieves Wordpress version
     * @deprecated use IfwPsn_Wp_Proxy_Blog::getVersion
     * @return string
     */
    public function getWpVersion()
    {
        return get_bloginfo('version');
    }

    /**
     * Alias for add_filter
     *
     * @deprecated
     * @param $tag
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return add_filter($tag, $function_to_add, $priority, $accepted_args);
    }

    /**
     * Alias for has_filter
     *
     * @deprecated
     * @param $tag
     * @param bool $function_to_check
     * @return mixed
     */
    public static function hasFilter($tag, $function_to_check = false)
    {
        return has_filter($tag, $function_to_check);
    }

    /**
     * Alias for apply_filters
     *
     * @deprecated
     * @param $tag
     * @param $value
     * @return mixed|void
     */
    public static function applyFilters($tag, $value)
    {
        return apply_filters($tag, $value);
    }

    /**
     * Alias for add_action
     *
     * @deprecated
     * @param $tag
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return add_action($tag, $function_to_add, $priority, $accepted_args);
    }

    /**
     * Alias for do_action
     *
     * @deprecated
     * @param $tag
     * @param string $arg
     * @return null
     */
    public static function doAction($tag, $arg = '')
    {
        return do_action($tag, $arg);
    }

    /**
     * Alias for load_plugin_textdomain
     *
     * @param $domain
     * @param bool $abs_rel_path
     * @param bool $plugin_rel_path
     * @return bool
     */
    public static function loadTextdomain($domain, $abs_rel_path = false, $plugin_rel_path = false)
    {
        return load_plugin_textdomain($domain, $abs_rel_path, $plugin_rel_path);
    }

    /**
     * Alias for get_user_meta
     *
     * @deprecated
     * @param int $user_id
     * @param string $key
     * @param bool $single
     * @return mixed
     */
    public static function getUserMeta($user_id, $key = '', $single = false)
    {
        return get_user_meta($user_id, $key, $single);
    }

    /**
     * Alias for update_user_meta
     *
     * @deprecated
     * @param int $user_id
     * @param string $meta_key
     * @param string $meta_value
     * @param string $prev_value
     * @return mixed
     */
    public static function updateUserMeta($user_id, $meta_key, $meta_value, $prev_value = '')
    {
        return update_user_meta($user_id, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Alias for delete_user_meta
     *
     * @deprecated
     * @param int $user_id
     * @param string $meta_key
     * @param string $meta_value
     * @return bool
     */
    public static function deleteUserMeta($user_id, $meta_key, $meta_value = '')
    {
        return delete_user_meta($user_id, $meta_key, $meta_value);
    }

    /**
     * @param int $userId
     * @return bool|object
     */
    public static function getUserdata($userId)
    {
        return get_userdata($userId);
    }

    /**
     * Alias for get_option
     *
     * @param $option
     * @param bool $default
     * @return mixed|void
     */
    public static function getOption($option, $default = false)
    {
        $filterTag = $option;
        return IfwPsn_Wp_Proxy_Filter::apply($filterTag, get_option($option, $default));
    }

    /**
     * @param $option
     * @return bool
     */
    public static function hasOption($option)
    {
        return self::getOption($option) !== false;
    }

    /**
     * Alias for update_option
     *
     * @param $option
     * @param $newvalue
     * @return bool
     */
    public static function updateOption($option, $newvalue)
    {
        return update_option($option, $newvalue);
    }

    /**
     * Alias for delete_option
     *
     * @param $option
     * @return bool
     */
    public static function deleteOption($option)
    {
        return delete_option($option);
    }

    /**
     * Alias for esc_attr
     *
     * @param $text
     * @return string|void
     */
    public static function escAttr($text)
    {
        return esc_attr($text);
    }

    /**
     * @param $to
     * @param $subject
     * @param $message
     * @param array|string $headers
     * @param array $attachments
     * @return bool
     */
    public static function mail($to, $subject, $message, $headers = '', $attachments = array())
    {
        return wp_mail($to, $subject, $message, $headers, $attachments);
    }

    /**
     * @return wpdb
     */
    public static function getDb()
    {
        global $wpdb;
        return $wpdb;
    }

    /**
     * @deprecated
     * @return string
     */
    public static function getLoginUrl()
    {
        return wp_login_url();
    }

    /**
     * @deprecated use IfwPsn_Wp_Proxy_Blog::getSiteUrl
     * @param $path
     * @param $scheme
     * @return string|void
     */
    public static function getSiteUrl($path = null, $scheme = null)
    {
        return site_url($path, $scheme);
    }

    /**
     * @deprecated
     * @param $postId
     * @return mixed
     */
    public static function getPostSlug($postId)
    {
        $post_data = get_post($postId, ARRAY_A);
        $slug = $post_data['post_name'];
        return $slug;
    }
}
