<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Filter.php 479 2015-10-30 17:31:12Z timoreithde $
 */ 
class IfwPsn_Wp_Proxy_Filter
{
    /**
     * Alias for add_filter
     *
     * @param $tag
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function add($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return add_filter($tag, $function_to_add, $priority, $accepted_args);
    }

    /**
     * Alias for add_filter
     *
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $tag
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function addPlugin(IfwPsn_Wp_Plugin_Manager $pm, $tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        $tag = $pm->getAbbrLower() . '_' . $tag;
        return self::add($tag, $function_to_add, $priority, $accepted_args);
    }

    /**
     * @param $tag
     * @param $function_to_remove
     * @param int $priority
     * @return bool
     */
    public static function remove($tag, $function_to_remove, $priority = 10)
    {
        return remove_filter($tag, $function_to_remove, $priority);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $tag
     * @param $function_to_remove
     * @param int $priority
     * @return bool
     */
    public static function removePlugin(IfwPsn_Wp_Plugin_Manager $pm, $tag, $function_to_remove, $priority = 10)
    {
        return remove_filter($pm->getAbbrLower() . '_' . $tag, $function_to_remove, $priority);
    }

    /**
     * Shortcut for add_filter( 'set-screen-option', 'function_name' )
     *
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function addSetScreenOption($function_to_add, $priority = 10, $accepted_args = 1)
    {
        return self::add('set-screen-option', $function_to_add, $priority, $accepted_args);
    }

    /**
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function addTheExcerpt($function_to_add, $priority = 10, $accepted_args = 1)
    {
        return self::add('the_excerpt', $function_to_add, $priority, $accepted_args);
    }

    /**
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function addTheExcerptFeed($function_to_add, $priority = 10, $accepted_args = 1)
    {
        return self::add('the_excerpt_feed', $function_to_add, $priority, $accepted_args);
    }

    /**
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function addTheExcerptRss($function_to_add, $priority = 10, $accepted_args = 1)
    {
        return self::add('the_excerpt_rss', $function_to_add, $priority, $accepted_args);
    }

    /**
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function addTheContentFeed($function_to_add, $priority = 10, $accepted_args = 1)
    {
        return self::add('the_content_feed', $function_to_add, $priority, $accepted_args);
    }

    /**
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function addWidgetText($function_to_add, $priority = 10, $accepted_args = 1)
    {
        return self::add('widget_text', $function_to_add, $priority, $accepted_args);
    }

    /**
     * @param $function_to_add
     * @param int $priority
     * @param int $accepted_args
     * @return bool|void
     */
    public static function addTheContentRss($function_to_add, $priority = 10, $accepted_args = 1)
    {
        return self::add('the_content_rss', $function_to_add, $priority, $accepted_args);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $function_to_add
     * @param int $priority
     * @return bool|void
     */
    public static function addPluginActionLinks(IfwPsn_Wp_Plugin_Manager $pm, $function_to_add, $priority = 10)
    {
        return self::add('plugin_action_links_'. $pm->getPathinfo()->getFilenamePath(), $function_to_add, $priority, 2);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $function_to_add
     * @param int $priority
     * @return bool|void
     */
    public static function addNetworkAdminPluginActionLinks(IfwPsn_Wp_Plugin_Manager $pm, $function_to_add, $priority = 10)
    {
        return self::add('network_admin_plugin_action_links_'. $pm->getPathinfo()->getFilenamePath(), $function_to_add, $priority, 2);
    }

    /**
     * Alias for has_filter
     *
     * @param $tag
     * @param bool $function_to_check
     * @return mixed
     */
    public static function has($tag, $function_to_check = false)
    {
        return has_filter($tag, $function_to_check);
    }

    /**
     * Alias for apply_filters
     *
     * @param $tag
     * @param $value
     * @return mixed|void
     */
    public static function apply($tag, $value)
    {
        $numargs = func_num_args();
        if ($numargs > 2) {
            $args = func_get_args();
            array_shift($args);
            array_shift($args);
            $args = array_merge(array($tag, $value), $args);

            return call_user_func_array('apply_filters', $args);
        } else {
            return apply_filters($tag, $value);
        }
    }

    /**
     * Alias for apply_filters
     *
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $tag
     * @param $value
     * @return mixed|void
     * @deprecated
     */
    public static function applyPlugin(IfwPsn_Wp_Plugin_Manager $pm, $tag, $value)
    {
        $tag = $pm->getAbbrLower() . '_' . $tag;
        return self::apply($tag, $value);
    }
}
