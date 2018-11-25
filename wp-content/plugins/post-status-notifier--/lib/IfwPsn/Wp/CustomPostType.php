<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Custom post type abstraction layer
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: CustomPostType.php 430 2015-06-03 23:32:54Z timoreithde $
 * @package   IfwPsn_Wp
 */ 
class IfwPsn_Wp_CustomPostType 
{
    /**
     * @var string
     */
    protected $_id;

    protected $_label;

    protected $_labelNameSingular;
    protected $_labelNamePlural;
    protected $_labelMenuName;
    protected $_labelAllItems;
    protected $_labelAddNew;
    protected $_labelAddNewItem;
    protected $_labelEditItem;
    protected $_labelNewItem;
    protected $_labelViewItem;
    protected $_labelSearchItems;
    protected $_labelNotFound;
    protected $_labelNotFoundInTrash;
    protected $_labelParentItemColon;

    protected $_description;

    protected $_public = true;
    protected $_excludeFromSearch = false;
    protected $_publiclyQueryable = true;
    protected $_showUi = true;
    protected $_showInNavMenus = true;
    protected $_showInMenu = true;
    protected $_showInAdminBar = true;
    protected $_menuPosition;
    protected $_menuIcon;
    protected $_capabilityType = 'post';
    protected $_capabilities;
    protected $_mapMetaCap = false;
    protected $_hierarchical = false;

    protected $_supportsTitle = true;
    protected $_supportsEditor = true;
    protected $_supportsAuthor = false;
    protected $_supportsThumbnail = false;
    protected $_supportsExcerpt = false;
    protected $_supportsTrackbacks = false;
    protected $_supportsCustomFields = false;
    protected $_supportsComments = false;
    protected $_supportsRevisions = false;
    protected $_supportsPageAttributes = false;
    protected $_supportsPostFormats = false;
    protected $_supportsCategories = false;
    protected $_supportsTags = false;

    protected $_metaBoxCallback;

    protected $_taxonomies = array();

    protected $_hasArchive = false;

    protected $_permalinkEpmask = EP_PERMALINK;

    protected $_rewriteSlug;
    protected $_rewriteWithFront = true;
    protected $_rewriteFeeds = false;
    protected $_rewritePages = false;
    protected $_rewriteEpMask;

    protected $_queryVar = true;

    protected $_canExport = true;

    protected $_feedInclude = false;


    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->setId($id);

        $this->_init();
    }

    private function _init()
    {
        IfwPsn_Wp_Proxy_Action::addInit(array($this, '_register'));

        if (method_exists($this, 'getCustomColums')) {
            IfwPsn_Wp_Proxy_Action::add('manage_'. $this->getId() .'_posts_columns', array($this, 'getCustomColums'));
        }
        if (method_exists($this, 'getColumsContent')) {
            IfwPsn_Wp_Proxy_Action::add('manage_'. $this->getId() .'_posts_custom_column', array($this, 'getColumsContent'), 10, 2);
        }
        if (method_exists($this, 'getSortableColumns')) {
            IfwPsn_Wp_Proxy_Action::add('manage_edit-'. $this->getId() .'_sortable_columns', array($this, 'getSortableColumns'));
        }


        if (method_exists($this, 'restrictManagePosts')) {
            IfwPsn_Wp_Proxy_Action::add('restrict_manage_posts', array($this, 'restrictManagePosts'));
        }

    }

    /**
     * Registers the custom post type
     */
    public function _register()
    {

        if (post_type_exists($this->getId())) {
            return;
        }

        foreach ($this->_taxonomies as $id => $args) {
            if (!empty($args)) {
                register_taxonomy($id, $this->getId(), $args);
            }
        }

        $result = register_post_type($this->getId(), $this->_getArgs());

        if (!is_wp_error($result)) {

            /**
             * Feed include
             */
            if ($this->isFeedInclude() && method_exists($this, 'addToFeed')) {
                IfwPsn_Wp_Proxy_Action::add('request', array($this, 'addToFeed'));
            }
        }
    }

    /**
     * Returns the custom post type's arguments
     * @see http://codex.wordpress.org/Function_Reference/register_post_type
     */
    protected function _getArgs()
    {
        $args = array(
            'labels' => $this->_getLabels(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
            'public' => $this->getPublic(),
            'exclude_from_search' => $this->getExcludeFromSearch(),
            'publicly_queryable' => $this->getPubliclyQueryable(),
            'show_ui' => $this->showUi(),
            'show_in_nav_menus' => $this->showInNavMenus(),
            'show_in_menu' => $this->showInMenu(),
            'show_in_admin_bar' => $this->showInAdminBar(),
            'capability_type' => $this->getCapabilityType(),
            'hierarchical' => $this->getHierarchical(),
            'supports' => $this->_supports(),
            'has_archive' => $this->_hasArchive,
            'permalink_epmask' => $this->getPermalinkEpmask(),
            'rewrite' => $this->_getRewrite(),
            'query_var' => $this->getQueryVar(),
            'can_export' => $this->getCanExport()
        );

        if ($this->getMenuPosition() !== null) {
            $args['menu_position'] = $this->getMenuPosition();
        }
        if ($this->getMenuIcon() !== null) {
            $args['menu_icon'] = $this->getMenuIcon();
        }
        if (is_array($this->getCapabilities())) {
            $args['capabilities'] = $this->getCapabilities();
        }
        if ($this->getMetaBoxCallback() !== null) {
            $args['register_meta_box_cb'] = $this->getMetaBoxCallback();
        }
        if (count($this->_getTaxonomies()) > 0) {
            $args['taxonomies'] = $this->_getTaxonomies();
        }

        return $args;
    }

    /**
     * Returns the labels settings
     * @return array
     */
    protected function _getLabels()
    {
        $result = array();

        $labels = array(
            'name' => 'getLabelNamePlural',
            'singular_name' => 'getLabelNameSingular',
            'add_new' => 'getLabelAddNew',
            'add_new_item' => 'getLabelAddNewItem',
            'edit_item' => 'getLabelEditItem',
            'new_item' => 'getLabelEditItem',
            'all_items' => 'getLabelAllItems',
            'view_item' => 'getLabelViewItem',
            'search_items' => 'getLabelSearchItems',
            'not_found' =>  'getLabelNotFound',
            'not_found_in_trash' => 'getLabelNotFoundInTrash',
            'parent_item_colon' => 'getLabelParentItemColon',
            'menu_name' => 'getLabelMenuName'
        );

        foreach($labels as $id => $method) {
            $value = $this->$method();
            if (!empty($value)) {
                $result[$id] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns the supports settings
     * @return array
     */
    protected function _supports()
    {
        $result = array();

        $supports = array(
            'title' => 'supportsTitle',
            'editor' => 'supportsEditor',
            'author' => 'supportsAuthor',
            'thumbnail' => 'supportsThumbnail',
            'excerpt' => 'supportsExcerpt',
            'trackbacks' => 'supportsTrackbacks',
            'custom-fields' => 'supportsCustomFields',
            'comments' => 'supportsComments',
            'revisions' => 'supportsRevisions',
            'page-attributes' => 'supportsPageAttributes',
            'post-formats' => 'supportsPostFormats'
        );

        foreach($supports as $id => $method) {
            if ($this->$method() == true) {
                array_push($result, $id);
            }
        }

        return $result;
    }

    /**
     * Returns the rewrite settings
     * @return array|bool
     */
    protected function _getRewrite()
    {
        if ($this->getRewriteSlug() === false) {
            return false;
        } elseif ($this->getRewriteSlug() !== null) {
            return array(
                'slug' => $this->getRewriteSlug(),
                'with_front' => $this->getRewriteWithFront(),
                'feeds' => $this->getRewriteFeeds(),
                'pages' => $this->getRewritePages(),
                'ep_mask' => $this->getRewriteEpMask()
            );
        } else {
            // Default: true and use $post_type as slug
            return true;
        }
    }

    /**
     * Returns the registered taxonomies
     * @return array
     */
    protected function _getTaxonomies()
    {
        if ($this->supportsCategories()) {
            $this->_taxonomies['category'] = null;
        }
        if ($this->supportsTags()) {
            $this->_taxonomies['post_tag'] = null;
        }

        return array_keys($this->_taxonomies);
    }

    /**
     * @param boolean $hasArchive
     * @return $this
     */
    public function setHasArchive($hasArchive)
    {
        $this->_hasArchive = $hasArchive;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasArchive()
    {
        return $this->_hasArchive;
    }

    /**
     * @param string $id A maximum of 20 characters is allowed
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param mixed $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->_label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @param mixed $labelAddNew
     * @return $this
     */
    public function setLabelAddNew($labelAddNew)
    {
        $this->_labelAddNew = $labelAddNew;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelAddNew()
    {
        return $this->_labelAddNew;
    }

    /**
     * @param mixed $labelAddNewItem
     * @return $this
     */
    public function setLabelAddNewItem($labelAddNewItem)
    {
        $this->_labelAddNewItem = $labelAddNewItem;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelAddNewItem()
    {
        return $this->_labelAddNewItem;
    }

    /**
     * @param mixed $labelAllItems
     * @return $this
     */
    public function setLabelAllItems($labelAllItems)
    {
        $this->_labelAllItems = $labelAllItems;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelAllItems()
    {
        return $this->_labelAllItems;
    }

    /**
     * @param mixed $labelEditItem
     * @return $this
     */
    public function setLabelEditItem($labelEditItem)
    {
        $this->_labelEditItem = $labelEditItem;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelEditItem()
    {
        return $this->_labelEditItem;
    }

    /**
     * @param mixed $labelMenuName
     * @return $this
     */
    public function setLabelMenuName($labelMenuName)
    {
        $this->_labelMenuName = $labelMenuName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelMenuName()
    {
        return $this->_labelMenuName;
    }

    /**
     * @param mixed $labelNamePlural
     * @return $this
     */
    public function setLabelNamePlural($labelNamePlural)
    {
        $this->_labelNamePlural = $labelNamePlural;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelNamePlural()
    {
        return $this->_labelNamePlural;
    }

    /**
     * @param mixed $labelNameSingular
     * @return $this
     */
    public function setLabelNameSingular($labelNameSingular)
    {
        $this->_labelNameSingular = $labelNameSingular;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelNameSingular()
    {
        return $this->_labelNameSingular;
    }

    /**
     * @param mixed $labelNewItem
     * @return $this
     */
    public function setLabelNewItem($labelNewItem)
    {
        $this->_labelNewItem = $labelNewItem;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelNewItem()
    {
        return $this->_labelNewItem;
    }

    /**
     * @param mixed $labelNotFound
     * @return $this
     */
    public function setLabelNotFound($labelNotFound)
    {
        $this->_labelNotFound = $labelNotFound;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelNotFound()
    {
        return $this->_labelNotFound;
    }

    /**
     * @param mixed $labelNotFoundInTrash
     * @return $this
     */
    public function setLabelNotFoundInTrash($labelNotFoundInTrash)
    {
        $this->_labelNotFoundInTrash = $labelNotFoundInTrash;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelNotFoundInTrash()
    {
        return $this->_labelNotFoundInTrash;
    }

    /**
     * @param mixed $labelParentItemColon
     * @return $this
     */
    public function setLabelParentItemColon($labelParentItemColon)
    {
        $this->_labelParentItemColon = $labelParentItemColon;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelParentItemColon()
    {
        return $this->_labelParentItemColon;
    }

    /**
     * @param mixed $labelSearchItems
     * @return $this
     */
    public function setLabelSearchItems($labelSearchItems)
    {
        $this->_labelSearchItems = $labelSearchItems;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelSearchItems()
    {
        return $this->_labelSearchItems;
    }

    /**
     * @param mixed $labelViewItem
     * @return $this
     */
    public function setLabelViewItem($labelViewItem)
    {
        $this->_labelViewItem = $labelViewItem;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelViewItem()
    {
        return $this->_labelViewItem;
    }

    /**
     * @param boolean $public
     * @return $this
     */
    public function setPublic($public)
    {
        if (is_bool($public)) {
            $this->_public = $public;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPublic()
    {
        return $this->_public;
    }

    /**
     * @param mixed $slug
     * @return $this
     */
    public function setRewriteSlug($slug)
    {
        $this->_rewriteSlug = $slug;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRewriteSlug()
    {
        return $this->_rewriteSlug;
    }

    /**
     * @param mixed $capabilities
     * @return $this
     */
    public function setCapabilities($capabilities)
    {
        $this->_capabilities = $capabilities;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCapabilities()
    {
        return $this->_capabilities;
    }

    /**
     * @param mixed $capabilityType
     * @return $this
     */
    public function setCapabilityType($capabilityType)
    {
        $this->_capabilityType = $capabilityType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCapabilityType()
    {
        return $this->_capabilityType;
    }

    /**
     * @param boolean $excludeFromSearch
     * @return $this
     */
    public function setExcludeFromSearch($excludeFromSearch)
    {
        $this->_excludeFromSearch = $excludeFromSearch;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getExcludeFromSearch()
    {
        return $this->_excludeFromSearch;
    }

    /**
     * @param boolean $hierarchical
     * @return $this
     */
    public function setHierarchical($hierarchical)
    {
        $this->_hierarchical = $hierarchical;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHierarchical()
    {
        return $this->_hierarchical;
    }

    /**
     * @param boolean $mapMetaCap
     * @return $this
     */
    public function setMapMetaCap($mapMetaCap)
    {
        $this->_mapMetaCap = $mapMetaCap;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getMapMetaCap()
    {
        return $this->_mapMetaCap;
    }

    /**
     * @param mixed $menuIcon
     * @return $this
     */
    public function setMenuIcon($menuIcon)
    {
        $this->_menuIcon = $menuIcon;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMenuIcon()
    {
        return $this->_menuIcon;
    }

    /**
     * @param mixed $menuPosition
     * @return $this
     */
    public function setMenuPosition($menuPosition)
    {
        $this->_menuPosition = $menuPosition;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMenuPosition()
    {
        return $this->_menuPosition;
    }

    /**
     * @param mixed $metaBoxCallback
     * @return $this
     */
    public function setMetaBoxCallback($metaBoxCallback)
    {
        $this->_metaBoxCallback = $metaBoxCallback;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMetaBoxCallback()
    {
        return $this->_metaBoxCallback;
    }

    /**
     * @param mixed $permalinkEpmask
     * @return $this
     */
    public function setPermalinkEpmask($permalinkEpmask)
    {
        $this->_permalinkEpmask = $permalinkEpmask;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPermalinkEpmask()
    {
        return $this->_permalinkEpmask;
    }

    /**
     * @param boolean $publiclyQueryable
     * @return $this
     */
    public function setPubliclyQueryable($publiclyQueryable)
    {
        $this->_publiclyQueryable = $publiclyQueryable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPubliclyQueryable()
    {
        return $this->_publiclyQueryable;
    }

    /**
     * @param mixed $rewriteEpMask
     * @return $this
     */
    public function setRewriteEpMask($rewriteEpMask)
    {
        $this->_rewriteEpMask = $rewriteEpMask;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRewriteEpMask()
    {
        return $this->_rewriteEpMask;
    }

    /**
     * @param boolean $rewriteFeeds
     * @return $this
     */
    public function setRewriteFeeds($rewriteFeeds)
    {
        $this->_rewriteFeeds = $rewriteFeeds;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRewriteFeeds()
    {
        return $this->_rewriteFeeds;
    }

    /**
     * @param boolean $rewritePages
     * @return $this
     */
    public function setRewritePages($rewritePages)
    {
        $this->_rewritePages = $rewritePages;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRewritePages()
    {
        return $this->_rewritePages;
    }

    /**
     * @param boolean $rewriteWithFront
     * @return $this
     */
    public function setRewriteWithFront($rewriteWithFront)
    {
        $this->_rewriteWithFront = $rewriteWithFront;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRewriteWithFront()
    {
        return $this->_rewriteWithFront;
    }

    /**
     * @param boolean $showInAdminBar
     * @return $this
     */
    public function setShowInAdminBar($showInAdminBar)
    {
        if (is_bool($showInAdminBar)) {
            $this->_showInAdminBar = $showInAdminBar;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function showInAdminBar()
    {
        return $this->_showInAdminBar;
    }

    /**
     * @param boolean $showInMenu
     * @return $this
     */
    public function setShowInMenu($showInMenu)
    {
        if (is_bool($showInMenu)) {
            $this->_showInMenu = $showInMenu;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function showInMenu()
    {
        return $this->_showInMenu;
    }

    /**
     * @param boolean $showInNavMenus
     * @return $this
     */
    public function setShowInNavMenus($showInNavMenus)
    {
        if (is_bool($showInNavMenus)) {
            $this->_showInNavMenus = $showInNavMenus;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function showInNavMenus()
    {
        return $this->_showInNavMenus;
    }

    /**
     * @param boolean $showUi
     * @return $this
     */
    public function setShowUi($showUi)
    {
        if (is_bool($showUi)) {
            $this->_showUi = $showUi;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function showUi()
    {
        return $this->_showUi;
    }

    /**
     * @param boolean $supportsAuthor
     * @return $this
     */
    public function setSupportsAuthor($supportsAuthor)
    {
        if (is_bool($supportsAuthor)) {
            $this->_supportsAuthor = $supportsAuthor;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsAuthor()
    {
        return $this->_supportsAuthor;
    }

    /**
     * @param boolean $supportsComments
     * @return $this
     */
    public function setSupportsComments($supportsComments)
    {
        if (is_bool($supportsComments)) {
            $this->_supportsComments = $supportsComments;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsComments()
    {
        return $this->_supportsComments;
    }

    /**
     * @param boolean $supportsCustomFields
     * @return $this
     */
    public function setSupportsCustomFields($supportsCustomFields)
    {
        if (is_bool($supportsCustomFields)) {
            $this->_supportsCustomFields = $supportsCustomFields;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsCustomFields()
    {
        return $this->_supportsCustomFields;
    }

    /**
     * @param boolean $supportsEditor
     * @return $this
     */
    public function setSupportsEditor($supportsEditor)
    {
        if (is_bool($supportsEditor)) {
            $this->_supportsEditor = $supportsEditor;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsEditor()
    {
        return $this->_supportsEditor;
    }

    /**
     * @param boolean $supportsExcerpt
     * @return $this
     */
    public function setSupportsExcerpt($supportsExcerpt)
    {
        if (is_bool($supportsExcerpt)) {
            $this->_supportsExcerpt = $supportsExcerpt;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsExcerpt()
    {
        return $this->_supportsExcerpt;
    }

    /**
     * @param boolean $supportsPageAttributes
     * @return $this
     */
    public function setSupportsPageAttributes($supportsPageAttributes)
    {
        if (is_bool($supportsPageAttributes)) {
            $this->_supportsPageAttributes = $supportsPageAttributes;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsPageAttributes()
    {
        return $this->_supportsPageAttributes;
    }

    /**
     * @param boolean $supportsPostFormats
     * @return $this
     */
    public function setSupportsPostFormats($supportsPostFormats)
    {
        if (is_bool($supportsPostFormats)) {
            $this->_supportsPostFormats = $supportsPostFormats;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsPostFormats()
    {
        return $this->_supportsPostFormats;
    }

    /**
     * @param boolean $supportsRevisions
     * @return $this
     */
    public function setSupportsRevisions($supportsRevisions)
    {
        if (is_bool($supportsRevisions)) {
            $this->_supportsRevisions = $supportsRevisions;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsRevisions()
    {
        return $this->_supportsRevisions;
    }

    /**
     * @param boolean $supportsThumbnail
     * @return $this
     */
    public function setSupportsThumbnail($supportsThumbnail)
    {
        if (is_bool($supportsThumbnail)) {
            $this->_supportsThumbnail = $supportsThumbnail;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsThumbnail()
    {
        return $this->_supportsThumbnail;
    }

    /**
     * @param boolean $supportsTitle
     * @return $this
     */
    public function setSupportsTitle($supportsTitle)
    {
        if (is_bool($supportsTitle)) {
            $this->_supportsTitle = $supportsTitle;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsTitle()
    {
        return $this->_supportsTitle;
    }

    /**
     * @param boolean $supportsTrackbacks
     * @return $this
     */
    public function setSupportsTrackbacks($supportsTrackbacks)
    {
        if (is_bool($supportsTrackbacks)) {
            $this->_supportsTrackbacks = $supportsTrackbacks;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsTrackbacks()
    {
        return $this->_supportsTrackbacks;
    }

    /**
     * Alias for setting taxonomy 'category'
     * @param boolean $supportsCategories
     * @return $this
     */
    public function setSupportsCategories($supportsCategories)
    {
        if (is_bool($supportsCategories)) {
            $this->_supportsCategories = $supportsCategories;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsCategories()
    {
        return $this->_supportsCategories;
    }

    /**
     * Alias for setting taxonomy 'post_tag'
     * @param boolean $supportsTags
     * @return $this
     */
    public function setSupportsTags($supportsTags)
    {
        if (is_bool($supportsTags)) {
            $this->_supportsTags = $supportsTags;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function supportsTags()
    {
        return $this->_supportsTags;
    }

    /**
     * @param $id
     * @param $args
     * @return $this
     */
    public function addTaxonomy($id, $args)
    {
        $this->_taxonomies[$id] = $args;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTaxonomies()
    {
        return $this->_taxonomies;
    }

    /**
     * @param boolean $canExport
     * @return $this
     */
    public function setCanExport($canExport)
    {
        if (is_bool($canExport)) {
            $this->_canExport = $canExport;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCanExport()
    {
        return $this->_canExport;
    }

    /**
     * @param boolean $queryVar
     * @return $this
     */
    public function setQueryVar($queryVar)
    {
        $this->_queryVar = $queryVar;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getQueryVar()
    {
        return $this->_queryVar;
    }

    /**
     * @return boolean
     */
    public function isFeedInclude()
    {
        return $this->_feedInclude;
    }

    /**
     * @param boolean $feedInclude
     */
    public function setFeedInclude($feedInclude)
    {
        if (is_bool($feedInclude)) {
            $this->_feedInclude = $feedInclude;
        }
    }

}
