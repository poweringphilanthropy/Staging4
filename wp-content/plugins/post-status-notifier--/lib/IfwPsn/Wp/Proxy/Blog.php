<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Blog.php 338 2014-09-15 18:59:26Z timoreithde $
 */ 
class IfwPsn_Wp_Proxy_Blog
{
    /**
     * @var
     */
    protected static $_themeData;


    /**
     * @return string|void
     */
    public static function getCharset()
    {
        return get_bloginfo('charset');
    }

    /**
     * @return string|void
     */
    public static function getName()
    {
        return get_bloginfo('blogname');
    }

    /**
     * @return string|void
     */
    public static function getAdminEmail()
    {
        return get_bloginfo('admin_email');
    }

    /**
     * @return string|void
     */
    public static function getLanguage()
    {
        return get_bloginfo('language');
    }

    /**
     * Retrieve the first language segment, like "en" or "de"
     * @return mixed
     */
    public static function getLanguageShort()
    {
        $lang = self::getLanguage();
        $langParts = explode('-', $lang);
        return $langParts[0];
    }

    /**
     * @return float
     */
    public static function getGmtOffset()
    {
        return (float)get_option('gmt_offset');
    }

    /**
     * @return string|void
     */
    public static function getVersion()
    {
        return get_bloginfo('version');
    }

    /**
     * @param $version
     * @return bool
     */
    public static function isVersionGreaterThan($version)
    {
        return version_compare(self::getVersion(), $version) > 0;
    }

    /**
     * @param $version
     * @return bool
     */
    public static function isMinimumVersion($version)
    {
        return version_compare(self::getVersion(), $version) >= 0;
    }

    /**
     * @param null $path
     * @param null $scheme
     * @return string|void
     */
    public static function getUrl($path = null, $scheme = null)
    {
        return site_url($path, $scheme);
    }

    /**
     * @return mixed|void
     */
    public static function getDateFormat()
    {
        return get_option('date_format');
    }

    /**
     * @return mixed|void
     */
    public static function getTimeFormat()
    {
        return get_option('time_format');
    }

    /**
     * @return mixed|void
     */
    public static function getTimezone()
    {
        return get_option('timezone_string');
    }

    /**
     * @return array|WP_Theme
     */
    public static function getThemeData()
    {
        if (empty(self::$_themeData)) {
            if (self::isMinimumVersion('3.4')) {
                self::$_themeData = wp_get_theme();
            } else {
                self::$_themeData = get_theme_data(get_stylesheet());
            }
        }

        return self::$_themeData;
    }

    /**
     * @return sting|null
     */
    public static function getThemeName()
    {
        $theme = self::getThemeData();
        $result = $theme->get('Name');

        return !empty($result) ? $result : null;
    }

    /**
     * @return sting|null
     */
    public static function getThemeURI()
    {
        $theme = self::getThemeData();
        $result = $theme->get('ThemeURI');

        return !empty($result) ? $result : null;
    }

    /**
     * @return sting|null
     */
    public static function getThemeDescription()
    {
        $theme = self::getThemeData();
        $result = $theme->get('Description');

        return !empty($result) ? $result : null;
    }

    /**
     * @return sting|null
     */
    public static function getThemeAuthor()
    {
        $theme = self::getThemeData();
        $result = $theme->get('Author');

        return !empty($result) ? $result : null;
    }

    /**
     * @return sting|null
     */
    public static function getThemeAuthorURI()
    {
        $theme = self::getThemeData();
        $result = $theme->get('AuthorURI');

        return !empty($result) ? $result : null;
    }

    /**
     * @return sting|null
     */
    public static function getThemeVersion()
    {
        $theme = self::getThemeData();
        $result = $theme->get('Version');

        return !empty($result) ? $result : null;
    }

    /**
     * The folder name of the current theme
     * @return sting|null
     */
    public static function getThemeTemplate()
    {
        $theme = self::getThemeData();
        $result = $theme->get('Template');

        return !empty($result) ? $result : null;
    }

    /**
     * If the theme is published
     *
     * @return sting|null
     */
    public static function getThemeStatus()
    {
        $theme = self::getThemeData();
        $result = $theme->get('Status');

        return !empty($result) ? $result : null;
    }

    /**
     * Tags used to describe the theme
     *
     * @return sting|null
     */
    public static function getThemeTags()
    {
        $theme = self::getThemeData();
        $result = $theme->get('Tags');

        return !empty($result) ? $result : null;
    }

    /**
     * @param $plugin
     * @return bool
     */
    public static function isPluginActive($plugin)
    {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        return is_plugin_active($plugin);
    }

    /**
     * @return array
     */
    public static function getPlugins()
    {
        $result = array();

        if (IfwPsn_Wp_Proxy_Action::didPluginsLoaded()) {
            $result = get_plugins();
        }

        return $result;
    }

    /**
     * @return string
     */
    public static function getLoginUrl()
    {
        return wp_login_url();
    }

    /**
     * @param $path
     * @param $scheme
     * @return string|void
     */
    public static function getSiteUrl($path = null, $scheme = null)
    {
        return site_url($path, $scheme);
    }

    /**
     * Checks if multisite / network is active
     * @return bool
     */
    public static function isMultisite()
    {
        return is_multisite();
    }

    /**
     * @return int
     */
    public static function getBlogId()
    {
        global $wpdb;
        return (int)$wpdb->blogid;
    }

    /**
     * @return array
     */
    public static function getMultisiteBlogIds()
    {
        global $wpdb;
        return $wpdb->get_col('SELECT blog_id FROM '. $wpdb->blogs);
    }

    /**
     * @param $blogId
     * @return bool
     */
    public static function switchToBlog($blogId)
    {
        return switch_to_blog($blogId);
    }

    /**
     * @return array
     */
    public static function getUploadDir()
    {
        return wp_upload_dir();
    }

    /**
     * @param bool $wp_default
     * @return string
     */
    public static function getDefaultEmailFrom($wp_default = true)
    {
        if ($wp_default) {
            // WP default (pluggable.php::wp_mail())
            $sitename = strtolower( $_SERVER['SERVER_NAME'] );
            if ( substr( $sitename, 0, 4 ) == 'www.' ) {
                $sitename = substr( $sitename, 4 );
            }

            $from_email = 'wordpress@' . $sitename;
            $from_name = 'WordPress';

        } else {

            $from_name = self::getName();
            $from_email = self::getAdminEmail();
        }

        $result = sprintf('%s <%s>', $from_name, $from_email);
        return $result;
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return string
     */
    public static function getServerEnvironment(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $tpl = IfwPsn_Wp_Tpl::getFilesytemInstance($pm);

        $count_users = count_users();
        $mysql_server_info = @mysql_get_server_info();
        $mysql_client_info = @mysql_get_client_info();

        $phpAutoloadFunctions = array();
        foreach (IfwPsn_Wp_Autoloader::getAllRegisteredAutoloadFunctions() as $function) {
            try {
                if (is_string($function)) {
                    array_push($phpAutoloadFunctions, $function);
                } elseif (is_array($function) && count($function) == 2) {
                    $autoloadObject = $function[0];
                    if (is_object($autoloadObject)) {
                        $autoloadObject = get_class($autoloadObject);
                    }
                    $autoloadMethod = $function[1];
                    if (!is_scalar($autoloadMethod)) {
                        $autoloadMethod = var_export($autoloadMethod, true);
                    }
                    array_push($phpAutoloadFunctions, $autoloadObject . '::' . $autoloadMethod);
                } elseif (is_object($function)) {
                    array_push($phpAutoloadFunctions, get_class($function));
                }
            } catch (Exception $e) {
                // no action
            }
        }

        $context = array(
            'plugin_name' => $pm->getEnv()->getName(),
            'plugin_version' => $pm->getEnv()->getVersion(),
            'plugin_build_number' => $pm->getEnv()->getBuildNumber(),
            'plugin_modules' => $pm->getBootstrap()->getModuleManager()->getModules(),
            'plugin_modules_initialized' => $pm->getBootstrap()->getModuleManager()->getInitializedModules(),
            'plugin_modules_custom_dir' => $pm->getBootstrap()->getModuleManager()->getCustomModulesLocation(),
            'OS' => PHP_OS,
            'uname' => php_uname(),
            'wp_version' => IfwPsn_Wp_Proxy_Blog::getVersion(),
            'wp_charset' => IfwPsn_Wp_Proxy_Blog::getCharset(),
            'wp_count_users' => $count_users['total_users'],
            'wp_debug' => WP_DEBUG == true ? 'true' : 'false',
            'wp_debug_log' => WP_DEBUG_LOG == true ? 'true' : 'false',
            'wp_debug_display' => WP_DEBUG_DISPLAY == true ? 'true' : 'false',
            'plugins' => IfwPsn_Wp_Proxy_Blog::getPlugins(),
            'theme_name' => IfwPsn_Wp_Proxy_Blog::getThemeName(),
            'theme_version' => IfwPsn_Wp_Proxy_Blog::getThemeVersion(),
            'theme_author' => IfwPsn_Wp_Proxy_Blog::getThemeAuthor(),
            'theme_uri' => IfwPsn_Wp_Proxy_Blog::getThemeURI(),
            'php_version' => phpversion(),
            'php_memory_limit' => ini_get('memory_limit'),
            'php_extensions' => IfwPsn_Wp_Server_Php::getExtensions(),
            'php_include_path' => get_include_path(),
            'php_open_basedir' => ini_get('open_basedir'),
            'php_autoload_functions' => $phpAutoloadFunctions,
            'mysql_version' => !empty($mysql_server_info) ? $mysql_server_info : '',
            'mysql_client' => !empty($mysql_client_info) ? $mysql_client_info : '',
            'server_software' => $_SERVER['SERVER_SOFTWARE'],
        );

        if (function_exists('apache_get_version')) {
            $context['apache_version'] = apache_get_version();
        }
        if (function_exists('apache_get_modules')) {
            $context['apache_modules'] = apache_get_modules();
        }

        return $tpl->render('server_env.html.twig', $context);
    }
}
