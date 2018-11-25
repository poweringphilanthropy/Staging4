<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Default.php 414 2015-04-12 14:44:06Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Options_Renderer_Default implements IfwPsn_Wp_Options_Renderer_Interface
{
    public function init()
    {
        // nothing to do
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
                <?php do_settings_sections($pageId); ?>
                <?php submit_button(); ?>
            </form>
        <?php
        endif;
    }
}
 