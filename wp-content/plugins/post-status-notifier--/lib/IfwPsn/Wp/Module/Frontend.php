<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Frontend.php 324 2014-08-17 18:04:37Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Module_Frontend 
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var string
     */
    protected $_id;

    protected $_page;
    protected $_controller;
    protected $_appaction;

    protected $_optionsUrl;

    protected $_activateNonceName;
    protected $_activateActionName = 'activate';

    protected $_deactivateNonceName;
    protected $_deactivateActionName = 'deactivate';

    protected $_deleteNonceName;
    protected $_deleteActionName = 'delete';

    protected $_extendingDocUrl;



    /**
     * @param $id
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct($id, IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_id = $id;
        $this->_pm = $pm;
    }

    public function init()
    {
        $this->_activateNonceName = $this->_pm->getAbbrLower() . '-activate-module';
        $this->_deactivateNonceName = $this->_pm->getAbbrLower() . '-deactivate-module';
        $this->_deleteNonceName = $this->_pm->getAbbrLower() . '-delete-module';


        if (isset($_GET['activate']) && wp_verify_nonce($_GET[$this->_activateActionName], $this->_activateNonceName)) {
            $id = esc_attr($_GET['id']);
            $module = $this->_pm->getBootstrap()->getModuleManager()->getModule($id);
            if ($module instanceof IfwPsn_Wp_Module_Bootstrap_Abstract) {
                $module->activate();
                if ($this->_pm->hasController()) {
                    $this->_pm->getController()->gotoRoute($this->getController(), $this->getAppaction(), $this->getPage());
                }
            }
        } elseif (isset($_GET['deactivate']) && wp_verify_nonce($_GET[$this->_deactivateActionName], $this->_deactivateNonceName)) {
            $id = esc_attr($_GET['id']);
            $module = $this->_pm->getBootstrap()->getModuleManager()->getModule($id);
            if ($module instanceof IfwPsn_Wp_Module_Bootstrap_Abstract) {
                $module->deactivate();
                if ($this->_pm->hasController()) {
                    $this->_pm->getController()->gotoRoute($this->getController(), $this->getAppaction(), $this->getPage());
                }
            }
        } elseif (isset($_GET['delete']) && wp_verify_nonce($_GET[$this->_deleteActionName], $this->_deleteNonceName)) {
            $id = esc_attr($_GET['id']);
            $module = $this->_pm->getBootstrap()->getModuleManager()->getModule($id);
            if ($module instanceof IfwPsn_Wp_Module_Bootstrap_Abstract) {
                $module->delete();
                if ($this->_pm->hasController()) {
                    $this->_pm->getController()->gotoRoute($this->getController(), $this->getAppaction(), $this->getPage());
                }
            }
        }
    }

    public function render()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Upload.php';
        $uploader = new IfwPsn_Wp_Upload($this->_id);
        $uploader->setMimeTypes(array('zip' => 'application/zip', 'zip' => 'application/octet-stream'));

        echo '<div id="module_upload">';
        if ($this->_extendingDocUrl != '') {
            echo '<div class="extending_doc_link"><a href="'. $this->_extendingDocUrl. '" target="_blank">'.
                sprintf( __('Learn how to extend %s', 'ifw'), $this->_pm->getAbbrUpper())
                . '</a></div>';
        }
        echo '<h3>'. __('Upload module', 'ifw') .'</h3>';
        $uploader->displayForm($this->_optionsUrl);

        if ($uploader->isUploaded()) {

            if ($uploader->isSuccess()) {

                try {
                    // handle the module archive
                    $archive = new IfwPsn_Wp_Module_Archive($uploader->getFileInfo(), $this->_pm);

                    if ($archive->isValid()) {
                        $archive->extractTo($this->_pm->getBootstrap()->getModuleManager()->getCustomModulesLocation());
                        $archive->close();
                    }

                    $this->_pm->getController()->getMessenger()->addMessage(__('Module installed.', 'ifw'));


                } catch (Exception $e) {
                    $this->_pm->getController()->getMessenger()->addMessage($e->getMessage(), 'error');
                }

                $uploader->remove();

            } else {

                $this->_pm->getController()->getMessenger()->addMessage(__('Error', 'ifw') . ': ' . $uploader->getError(), 'error');
            }

            $this->_pm->getController()->gotoRoute($this->getController(), $this->getAppaction(), $this->getPage());
        }
        echo '</div>';


        $tpl = IfwPsn_Wp_Tpl::getFilesytemInstance($this->_pm);
        $moduleManager = $this->_pm->getBootstrap()->getModuleManager();

        $context = array(
            'custom_modules' => $moduleManager->getCustomModules(),
            'activate_url' => wp_nonce_url(admin_url($this->_optionsUrl), $this->_activateNonceName, $this->_activateActionName),
            'deactivate_url' => wp_nonce_url(admin_url($this->_optionsUrl), $this->_deactivateNonceName, $this->_deactivateActionName),
            'delete_url' => wp_nonce_url(admin_url($this->_optionsUrl), $this->_deleteNonceName, $this->_deleteActionName)
        );
        echo $tpl->render('module_frontend.html.twig', $context);
    }

    /**
     * @param $file
     * @return bool
     */
    protected function _isValidModuleFile(array $file)
    {
        $z = new ZipArchive();
        $z->open($file['file']);

        if (!$z->getStream($file['filename'] . '/bootstrap.php')) {
            echo __('Error', 'ifw') . ': ' . __('Could not find module bootstrap.', 'ifw');
            return false;
        }

        return true;
    }

    /**
     * @param mixed $appaction
     * @return $this
     */
    public function setAppaction($appaction)
    {
        $this->_appaction = $appaction;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppaction()
    {
        return $this->_appaction;
    }

    /**
     * @param mixed $controller
     * @return $this
     */
    public function setController($controller)
    {
        $this->_controller = $controller;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * @param mixed $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->_page = $page;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->_page;
    }

    /**
     * @param mixed $optionsUrl
     * @return $this
     */
    public function setOptionsUrl($optionsUrl)
    {
        $this->_optionsUrl = $optionsUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOptionsUrl()
    {
        return $this->_optionsUrl;
    }

    /**
     * @param mixed $extendingDocUrl
     * @return $this
     */
    public function setExtendingDocUrl($extendingDocUrl)
    {
        $this->_extendingDocUrl = $extendingDocUrl;
        return $this;
    }

}
 