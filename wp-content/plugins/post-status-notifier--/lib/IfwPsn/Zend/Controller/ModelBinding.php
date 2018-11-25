<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: ModelBinding.php 493 2015-11-24 20:35:42Z timoreithde $
 */ 
abstract class IfwPsn_Zend_Controller_ModelBinding extends IfwPsn_Zend_Controller_Default
{
    /**
     * @var IfwPsn_Wp_Plugin_ListTable_Abstract
     */
    protected $_listTable;

    /**
     * @var IfwPsn_Wp_Plugin_Screen_Option_PerPage
     */
    protected $_perPage;



    public function onBootstrap()
    {
        if ($this->_request->getActionName() == 'index' || $this->_request->getActionName() == null) {
            $this->_perPage = IfwPsn_Wp_Plugin_Screen_Option_PerPage::getInstance($this->getModelMapper()->getPerPageId($this->getPluginAbbr() . '_'));
        }
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Vendor_Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        if ($this->_request->getActionName() == 'index') {

            $this->_listTable = $this->getListTable();

            if ($this->_listTable->hasValidBulkRequest()) {

                $action = $this->_listTable->getBulkAction();

                if ($action == 'bulk_delete' && is_array($this->_request->getPost($this->getSingular()))) {
                    // bulk action delete
                    $this->_bulkDelete($this->_request->getPost($this->getSingular()));

                } elseif ($action == 'bulk_export' && is_array($this->_request->getPost($this->getSingular())) && method_exists($this, '_bulkExport')) {
                    // bulk action export
                    $this->_bulkExport($this->_request->getPost($this->getSingular()));

                } else {
                    if (method_exists($this, 'handleBulkAction')) {
                        $this->handleBulkAction($action);
                    }
                    $actionFormat = '%s_%s_action-%s';
                    IfwPsn_Wp_Proxy_Action::doAction(sprintf($actionFormat, $this->getPluginAbbr(), $this->getPlural(), $action), $this);
                }
            }
        }
    }

    public function _initListTable()
    {
        if ($this->_listTable === null) {
            $this->_listTable = $this->getListTable();
        }

        if ($this->_listTable instanceof IfwPsn_Wp_Plugin_ListTable_Abstract &&
            $this->_perPage instanceof IfwPsn_Wp_Plugin_Screen_Option_PerPage) {

            $this->_listTable->setItemsPerPage($this->_perPage->getOption());
        }
    }

    /**
     * Deletes an item
     *
     * @param null|int $id
     */
    public function deleteAction($id = null, $verifyNonce = true)
    {
        if ( $verifyNonce && !wp_verify_nonce( $this->_request->get('nonce'), self::getDeleteNonceAction($this->getSingular(), $this->_request->get('id')) ) ) {
            $this->getAdminNotices()->persistError($this->getInvalidAccessMessage());
            $this->gotoIndex();
        }

        if (is_null($id)) {
            $id = (int)$this->_request->get('id');
            $return = true;
        } else {
            $return = false;
        }

        $result = false;
        $item = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one($id);

        if (method_exists($this, 'deleteCallback')) {
            $item = $this->deleteCallback($item);
        }

        if (is_a($item, $this->getModelName())) {
            $result = $item->delete();
        }

        if ($return) {
            if ($result) {
                $this->_adminNotices->persistUpdated($this->getDeleteMessageSingular($item));
            } else {
                $this->_adminNotices->persistError($this->getDeleteErrorMessage());
            }
            $this->gotoIndex();
        }
    }

    /**
     * @param array $items
     */
    protected function _bulkDelete(array $items)
    {
        if (count($items) == 1) {
            $singleItemId = $items[0];
            $item = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one($singleItemId);
            $msg = $this->getDeleteMessageSingular($item);
        } elseif (count($items) > 1) {
            $msg = $this->getDeleteMessagePlural();
        }

        foreach($items as $id) {
            $this->deleteAction((int)$id, false);
        }

        if (!$this->_adminNotices->hasMessage() && isset($msg)) {
            $this->_adminNotices->persistUpdated($msg);
        }

        $this->gotoIndex();
    }

    /**
     * @param $identifier
     * @param $id
     * @return string
     */
    public static function getDeleteNonceAction($identifier, $id)
    {
        return sprintf('%s-delete-%d', $identifier, $id);
    }

    /**
     * @param $identifier
     * @return string
     */
    public static function getImportNonceAction($identifier)
    {
        return sprintf('import-%s', $identifier);
    }

    /**
     * @param $identifier
     * @param $id
     * @return string
     */
    public static function getCopyNonceAction($identifier, $id)
    {
        return sprintf('%s-copy-%d', $identifier, $id);
    }

    /**
     * @param $items
     */
    public function handleExport($items, array $options = array())
    {
        $filenamePrefix = $this->_pm->getAbbrLower() . '_';

        if (is_numeric($items)) {
            // single item export
            $item = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one((int)$items);

            if (is_a($item, $this->getModelName())) {

                $exportOptions = $this->getModelMapper()->getExportOptions($filenamePrefix, $item->getSanitizedName());
            }

        } elseif (is_array($items)) {

            $exportOptions = $this->getModelMapper()->getExportOptions($filenamePrefix);

        } else {

            $this->getAdminNotices()->persistError( Asa2_Helper_Translation::getInvalidAccessMessage() );
            $this->gotoIndex();
        }

        $exportOptions = array_merge($exportOptions, $options);

        IfwPsn_Wp_ORM_Model::export($this->getModelName(), $items, $exportOptions);
    }

    /**
     * Options:
     * - file
     * - keep_file
     * - prefix
     * - item_callback
     * - id_col
     * - name_col
     * - skip_col
     * - goto_index
     * @param array $options
     */
    public function handleImport(array $options = array())
    {
        if ( !wp_verify_nonce( $this->_request->get('nonce'), self::getImportNonceAction($this->getSingular()) )) {
            $this->getAdminNotices()->persistError($this->getInvalidAccessMessage());
            $this->gotoIndex();
        }

        if (isset($options['file']) && file_exists($options['file'])) {
            $file = $options['file'];
        } else {
            $file = $_FILES['importfile']['tmp_name'];
        }

        $importer = new IfwPsn_Wp_Data_Importer($file, $this->getModelMapper()->getExportOptions($this->_pm->getAbbrLower() . '_'));

        if (!isset($options['handle_name']) || $options['handle_name'] == true) {
            $item_callback = array(
                array($this, 'handleImportNameCheck')
            );
        } else {
            $item_callback = array();
        }

        if (isset($options['item_callback'])) {
            if (is_callable($options['item_callback'])) {
                $item_callback = array_merge($item_callback, array($options['item_callback']));
            } elseif (is_array($options['item_callback'])) {
                $item_callback = array_merge($item_callback, $options['item_callback']);
            }
            unset($options['item_callback']);
        }

        $options = array_merge(array(
            'prefix' => esc_attr($this->getRequest()->get('import_prefix')),
            'item_callback' => $item_callback,
        ), $options);

        $result = $importer->import($this->getModelName(), $options);

        if (!isset($options['keep_file']) || $options['keep_file'] != true) {
            @unlink($file);
        }

        if (!is_numeric($result)) {
            $this->getAdminNotices()->persistError($importer->getError());
        }

        if (!isset($options['goto_index']) || $options['goto_index'] == true) {
            $this->gotoIndex();
        }
    }

    /**
     * Check if name exists on import
     * @param array $item
     * @return array
     */
    public function handleImportNameCheck(array $item)
    {
        $counter = 2;
        $newNameFormat = $item['name'] . '%d';

        $mapper = $this->getModelMapper();

        while (call_user_func(get_class($mapper) . '::exists', $item['name'])) {
            $item['name'] = sprintf($newNameFormat, $counter);
            $counter++;
        }

        return $item;
    }

    /**
     * @param array $options
     */
    public function handleCopy(array $options = array())
    {
        $id = (int)$this->getRequest()->get('id');

        if (!wp_verify_nonce( $this->getRequest()->get('nonce'), IfwPsn_Zend_Controller_ModelBinding::getCopyNonceAction($this->getModelMapper()->getSingular(), $id) )) {
            // verification failed
            $this->getAdminNotices()->persistError( $this->getInvalidAccessMessage() );
        } else {
            // valid access
            $item = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one($id);

            if ($item instanceof IfwPsn_Wp_ORM_Model) {
                $itemName = $item->getName();
            } else {
                $itemName = '';
            }

            $options = array_merge(array(
                'name_format' => '%s_%s%s'
            ), $options);

            $result = IfwPsn_Wp_ORM_Model::duplicate($this->getModelName(), $id, $options);

            if (!empty($result)) {
                $this->getAdminNotices()->persistUpdated( $this->getCopySuccessMessage($itemName) );
            } else {
                $this->getAdminNotices()->persistError( $this->getCopyErrorMessage($itemName) );
            }
        }

        $this->gotoIndex();
    }

    /**
     * Get the item's singular name
     * @return string
     */
    public function getSingular()
    {
        return $this->getModelMapper()->getSingular();
    }

    /**
     * Get the item's plural name
     * @return string
     */
    public function getPlural()
    {
        return $this->getModelMapper()->getPlural();
    }

    /**
     * @param $label
     * @return string
     */
    public static function getImportItemsButton($label)
    {
        return '<a href="javascript:void(0)" class="button button-dashicons import_items_container_toggle"><span class="dashicons dashicons-plus"></span> '. $label . '</a>';
    }

    /**
     * @return string
     */
    abstract public function getModelName();

    /**
     * @return IfwPsn_Wp_Model_Mapper_Abstract
     */
    abstract public function getModelMapper();

    /**
     * @return IfwPsn_Wp_Plugin_ListTable_Abstract
     */
    abstract public function getListTable();

    /**
     * Redirects to index page
     * @return mixed
     */
    abstract public function gotoIndex();

    /**
     * @return string|void
     */
    public function getDeleteMessagePlural()
    {
        return __('Items have been deleted successfully', 'ifw');
    }

    /**
     * @param null|IfwPsn_Wp_ORM_Model $item
     * @return string|void
     */
    public function getDeleteMessageSingular($item = null)
    {
        if ($item instanceof IfwPsn_Wp_ORM_Model) {
            $itemName = $item->getName();
        }

        if (!isset($itemName) || empty($itemName)) {
            $itemName = '';
        } else {
            $itemName = sprintf('"%s"', $itemName);
        }

        return sprintf(__('Item %s has been deleted successfully', 'ifw'), $itemName);
    }

    /**
     * @param null $item
     * @return string
     */
    public function getDeleteErrorMessage($item = null)
    {
        return __('Item could not be deleted', 'ifw');
    }

    /**
     * @param null $itemName
     * @return string|void
     */
    public function getCopySuccessMessage($itemName = null)
    {
        if (empty($itemName)) {
            $itemName = '';
        } else {
            $itemName = sprintf('"%s"', $itemName);
        }

        return sprintf(__('Item %s has been copied successfully', 'ifw'), $itemName);
    }

    /**
     * @param null $itemName
     * @return string|void
     */
    public function getCopyErrorMessage($itemName = null)
    {
        if (empty($itemName)) {
            $itemName = '';
        } else {
            $itemName = sprintf('"%s"', $itemName);
        }

        return sprintf(__('Item %s could not be copied', 'ifw'), $itemName);
    }

    /**
     * @return string
     */
    public function getInvalidAccessMessage()
    {
        return __('Invalid access.', 'ifw');
    }

    /**
     * @return string
     */
    abstract public function getPluginAbbr();
}
