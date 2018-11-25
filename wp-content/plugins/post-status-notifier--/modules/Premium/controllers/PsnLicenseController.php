<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: PsnLicenseController.php 443 2015-11-28 09:33:20Z timoreithde $
 * @package
 */
class Premium_PsnLicenseController extends PsnApplicationController
{
    /**
     *
     */
    public function preDispatch()
    {

    }

    public function onBootstrap()
    {

    }

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->_initContextualHelp();

        $form = new Psn_Module_Premium_Admin_Form_License();

        $form->setDefaults(array(
            'license' => Psn_Module_Premium_License::getInstance($this->_pm)->getEncryptedLicense()
        ));

        if ($this->_request->isPost()) {

            if (!$form->isValidNonce()) {

                $this->getAdminNotices()->persistError(__('Invalid access.', 'psn'));
                $this->gotoIndex();

            } elseif ($form->isValid($this->_request->getPost())) {

                // request is valid, save the changes

                $values = $form->removeNonceAndGetValues();

                if (isset($values['license'])) {
                    $license = trim(sanitize_text_field($values['license']));

                    Psn_Module_Premium_License::getInstance($this->_pm)->saveLicense($license);

                    $this->getAdminNotices()->persistUpdated('License saved successfully');

                } else {
                    $this->getAdminNotices()->persistError('License could not be saved');
                }

                $this->gotoIndex();
            }
        }

        $this->view->form = $form;
        $this->view->module = $this->_pm->getBootstrap()->getModuleManager()->getModule('psn_mod_prm');
    }

    /**
     * Init the contextual help
     */
    protected function _initContextualHelp()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Menu/Help.php';

        // set up contextual help
        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Licensing', 'psn_prm'))
            ->setHelp($this->_getHelpText())
            ->setSidebar($this->_getHelpSidebar('licensing.html'))
            ->load();
    }

    /**
     * @return string
     */
    protected function _getHelpText()
    {
        return sprintf(__('Check the <a href="%s" target="_blank">licensing documentation</a> for more information.', 'psn_prm'),
            $this->_pm->getConfig()->plugin->docUrl . 'licensing.html');
    }

    /**
     * Redirects to index page
     * @return mixed
     */
    public function gotoIndex()
    {
        $this->_gotoRoute('license', 'index', 'post-status-notifier', array('mod' => 'premium'));
    }
}
