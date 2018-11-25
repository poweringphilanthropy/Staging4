<?php
/**
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PsnApplicationController.php 397 2015-08-16 20:09:46Z timoreithde $
 */
class PsnApplicationController extends IfwPsn_Zend_Controller_Default
{
    public function init()
    {
        parent::init();

        $this->_pm->getLogger()->logPrefixed('Init controller '. get_class($this));
    }

    public function onAdminInit()
    {
        if ($this->_pm->isPremium() && IfwPsn_Wp_Proxy_Blog::isPluginActive('post-status-notifier-lite/post-status-notifier-lite.php')) {

            // Lite version still activated
            $this->getAdminNotices()->addError(sprintf(
                __('The Lite version of this plugin is still activated. Please deactivate it! Refer to the <a href="%s">Upgrade Howto</a>.', 'psn'),
                'http://docs.ifeelweb.de/post-status-notifier/upgrade_howto.html'));
        }
    }

    /**
     * Defines main navigation items
     */
    protected function _loadNavigationPages()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Admin/Navigation.php';

        $nav = new Psn_Admin_Navigation($this->_pm);

        $this->_navigation = $nav->getNavigation();
    }

    /**
     * @param $identifier
     * @param $headline
     * @param $helpUrl
     * @param $actionUrl
     * @return string
     * @throws IfwPsn_Wp_Plugin_Exception
     */
    public static function getImportForm($identifier, $headline, $helpUrl, $actionUrl)
    {
        $helpText = sprintf(__('Need help? <a href="%s" target="_blank">Check the docs</a>.', 'psn'), IfwPsn_Wp_Plugin_Manager::getInstance('Psn')->getConfig()->plugin->docUrl . $helpUrl);

        $options = array(
            'headline' => $headline,
            'help_text' => $helpText,
            'action_url' => $actionUrl,
            'import_file_label' => __('Import file', 'psn'),
            'import_file_description' => __('Please select a valid .xml export file.', 'psn'),
            'import_prefix_label' => __('Import prefix (optional)', 'psn'),
            'import_prefix_description' => __('Prepend this text to imported items names to identify them.', 'psn'),
            'wait_text_headline' => __('Processing file', 'psn'),
            'wait_text_description' => __('Please wait while the export file is being processed ...', 'psn'),
        );

        return IfwPsn_Wp_Data_Importer::getForm(IfwPsn_Wp_Plugin_Manager::getInstance('Psn'), $identifier, $options);
    }
}
