<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Tabs.php 414 2015-04-12 14:44:06Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Options_Renderer_Tabs implements IfwPsn_Wp_Options_Renderer_Interface
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    public function init()
    {
        IfwPsn_Wp_Proxy_Script::loadAdminMinimized($this->_pm, 'opt-render-tabs', $this->_pm->getEnv()->getUrl() . 'lib/IfwPsn/Wp/Options/Renderer/sources/tabs/tabs.js', array('jquery'));
        IfwPsn_Wp_Proxy_Style::loadAdminMinimized($this->_pm, 'opt-render-tabs', $this->_pm->getEnv()->getUrl() . 'lib/IfwPsn/Wp/Options/Renderer/sources/tabs/tabs.css');
    }

    /**
     * @param IfwPsn_Wp_Options $options
     * @param null $pageId
     */
    public function render(IfwPsn_Wp_Options $options, $pageId = null)
    {
        if ($options->getAddedFields() === 0):
            echo '<p>' . __('No options available.', 'ifw') . '</p>';
        else:
            if ($pageId == null) {
                $pageId = $options->getPageId();
            }
            ?>
            <form method="post" action="options.php">
                <?php settings_fields($pageId); ?>
                <?php $this->_doSettingsSections($pageId); ?>
                <?php submit_button(); ?>
            </form>
        <?php
        endif;
    }

    /**
     * @param $page
     */
    protected function _doSettingsSections($page)
    {
        global $wp_settings_sections, $wp_settings_fields;

        if ( ! isset( $wp_settings_sections[$page] ) )
            return;

        echo '<ul class="nav nav-tabs nav-tabs-wp" role="tablist">';

        foreach ( (array) $wp_settings_sections[$page] as $section) {
            if (strpos($section['id'], 'external') !== false) {
                continue;
            }
            printf('<li><a href="#%s" class="nav-tab" role="tab" data-toggle="tab">%s</a></li>', $section['id'], $section['title']);
        }

        echo '</ul>';

        echo '<div class="tab-content">';

        foreach ( (array) $wp_settings_sections[$page] as $section ) {



            if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
                continue;

            if (strpos($section['id'], 'external') !== false) {
                do_settings_fields($page, $section['id']);
            } else {
                printf('<div class="tab-pane" id="%s"><table class="form-table">', $section['id']);
                if ($section['callback']) {
                    call_user_func($section['callback'], $section);
                }
                do_settings_fields($page, $section['id']);
                echo '</table></div>';
            }
        }

        echo '</div>';

    }
}
 