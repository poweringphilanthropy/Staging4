<?php
/**
 * This class handles the placeholders replacement
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @version     $Id: Placeholders.php 451 2015-12-13 14:35:21Z timoreithde $
 * @copyright   Copyright (c) ifeelweb.de
 * @package     Psn_Notification
 */
class Psn_Notification_Placeholders extends IfwPsn_Util_Replacements
{
    /**
     * @var array
     */
    protected static $_instances = array();

    /**
     * The post object the notification is related to
     * @var object|WP_Post
     */
    protected $_post;

    /**
     * @var bool
     */
    protected $_isMockUpPost = false;

    /**
     * @var array
     */
    protected $_twigContext = array();



    /**
     * @param $post
     * @return Psn_Notification_Placeholders
     */
    public static function getInstance($post)
    {
        if (!isset(self::$_instances[$post->ID])) {
            self::$_instances[$post->ID] = new self($post);
        }

        return self::$_instances[$post->ID];
    }

    /**
     * @param WP_Post $post
     */
    public function __construct($post = null)
    {
        if ($post === null) {
            $this->_post = $this->_getPostMockup();
        } else {
            // real post object with data
            $this->_post = $post;
        }

        $options = array(
            'auto_delimiters' => true,
            'lazy_filter_prefix' => 'psn_load_placeholder_value_'
        );

        parent::__construct($this->_getNotificationPlaceholders(), $options);

        $this->_addDynamicPlaceholders();
        $this->_addArrayData();

        IfwPsn_Wp_Proxy_Action::doAction('psn_notification_placeholders_loaded', $this);

        if (!$this->isMockUpPost()) {
            $this->prepareTwigContext();
        }
    }

    /**
     *
     */
    protected function _addDynamicPlaceholders()
    {
        $dynamicPlaceholders = array();

        $group = 'dynamic';

        foreach (IfwPsn_Wp_Proxy_Taxonomy::getCategoriesNames() as $category) {
            $dynamicPlaceholders['post_category-' . $category] = implode(', ', IfwPsn_Wp_Proxy_Post::getAttachedCategoriesNames($this->_post, $category));
        }
        foreach (IfwPsn_Wp_Proxy_Taxonomy::getTagsNames() as $tag) {
            if ($tag == 'post_format') {
                continue;
            }
            $dynamicPlaceholders['post_tag-' . $tag] = implode(', ', IfwPsn_Wp_Proxy_Post::getAttachedTagsNames($this->_post, $tag));
        }

        // custom keys
        if (!$this->isMockUpPost()) {
            foreach (IfwPsn_Wp_Proxy_Post::getCustomKeys($this->_post) as $key) {
                $dynamicPlaceholders['post_custom_field-' . $key] = IfwPsn_Wp_Proxy_Post::getCustomKeyValue($key, $this->_post);
            }
        }

        foreach (IfwPsn_Wp_Proxy_Filter::apply('psn_notification_dynamic_placeholders', $dynamicPlaceholders) as $key => $value) {
            $this->addPlaceholder($key, $value, $group);
        }
    }

    protected function _addArrayData()
    {
        $arrayPlaceholders = array();
        $group = 'arrays';

        $arrayPlaceholders['post_categories_array'] = IfwPsn_Wp_Proxy_Post::getAttachedCategoriesNames($this->_post);
        $arrayPlaceholders['post_tags_array'] = IfwPsn_Wp_Proxy_Post::getAttachedTagsNames($this->_post);
        $arrayPlaceholders['post_custom_fields_array'] = IfwPsn_Wp_Proxy_Post::getCustomKeysAndValues($this->_post);

        foreach (IfwPsn_Wp_Proxy_Filter::apply('psn_notification_array_placeholders', $arrayPlaceholders) as $key => $value) {
            $this->addPlaceholder($key, $value, $group);
        }
    }

    /**
     * @return mixed|void
     */
    protected function _getNotificationPlaceholders()
    {
        $placeholders = array_merge(
            $this->_getPostData(),
            $this->_getPostDiff(),
            $this->_getAuthorData(),
            $this->_getCurrentUserData(),
            $this->_getBloginfo()
        );

        return IfwPsn_Wp_Proxy_Filter::apply('psn_notification_placeholders', $placeholders);
    }

    /**
     * @return array
     */
    protected function _getPostData()
    {
        $result = array();

        foreach (get_object_vars($this->_post) as $k => $v) {
            if (strpos($k, 'post_') === false) {
                $k = 'post_' . $k;
            }
            $result[$k] = $v;
        }

        $result['post_permalink'] = IfwPsn_Wp_Proxy_Post::getPermalink($this->_post);
        $result['post_editlink'] = IfwPsn_Wp_Proxy_Post::getEditLink($this->_post->ID);
        $result['post_format'] = IfwPsn_Wp_Proxy_Post::getFormat($this->_post);
        $result['post_preview_25'] = IfwPsn_Wp_Proxy_Post::getWords($this->_post, 25);
        $result['post_preview_50'] = IfwPsn_Wp_Proxy_Post::getWords($this->_post, 50);
        $result['post_preview_75'] = IfwPsn_Wp_Proxy_Post::getWords($this->_post, 75);
        $result['post_preview_100'] = IfwPsn_Wp_Proxy_Post::getWords($this->_post, 100);

        $strippedContent = strip_tags($this->_post->post_content);
        $result['post_content_strip_tags'] = trim(preg_replace('/\[.*?\]/U', '', $strippedContent));

        // get the post's categories
        $categories = IfwPsn_Wp_Proxy_Post::getAttachedCategoriesNames($this->_post);
        $result['post_categories'] = implode(', ', $categories);

        $categoriesSlugs = IfwPsn_Wp_Proxy_Post::getAttachedCategoriesSlugs($this->_post);
        $result['post_categories_slugs_array'] = $categoriesSlugs;
        $result['post_categories_slugs'] = implode(', ', $categoriesSlugs);

        // get the post's tags
        $tags = IfwPsn_Wp_Proxy_Post::getAttachedTagsNames($this->_post);
        $result['post_tags'] = implode(', ', $tags);

        $tagsSlugs = IfwPsn_Wp_Proxy_Post::getAttachedTagsSlugs($this->_post);
        $result['post_tags_slugs_array'] = $tagsSlugs;
        $result['post_tags_slugs'] = implode(', ', $tagsSlugs);

        // custom keys
        $customKeys = IfwPsn_Wp_Proxy_Post::getCustomKeys($this->_post);
        $result['post_custom_fields'] = implode(', ', $customKeys);

        // custom keys and values
        $customFields = IfwPsn_Wp_Proxy_Post::getCustomKeysAndValues($this->_post);

        $custom_keys_and_values = array();
        foreach ($customFields as $key => $value) {
            array_push($custom_keys_and_values, $key . ': ' . $value);
        }
        $result['post_custom_fields_and_values'] = implode(', ', $custom_keys_and_values);

        // featured image
        if (has_post_thumbnail($this->_post->ID)) {
            $featuredImgData = wp_get_attachment_image_src( get_post_thumbnail_id( $this->_post->ID ));
            if ($featuredImgData != false) {
                $result['post_featured_image_url'] = $featuredImgData[0];
                $result['post_featured_image_width'] = $featuredImgData[1];
                $result['post_featured_image_height'] = $featuredImgData[2];
            }
        } else {
            $result['post_featured_image_url'] = '';
            $result['post_featured_image_width'] = '';
            $result['post_featured_image_height'] = '';
        }

        return $result;
    }

    protected function _getPostDiff()
    {
        $result = array();
        $result['post_diff_title'] = '';
        $result['post_diff_content'] = '';

        if (wp_revisions_enabled($this->_post) && function_exists('wp_get_post_revisions')) {

            $revs = wp_get_post_revisions($this->_post);

            if (is_array($revs) && count($revs) > 0) {

                // get the latest revision, exclude autosaves
                foreach ($revs as $rev) {
                    if (wp_is_post_revision($rev) !== false && !wp_is_post_autosave($rev)) {
                        $latestRevision = $rev;
                        break;
                    }
                }

                if (isset($latestRevision) && $latestRevision instanceof WP_Post) {

                    require_once ABSPATH . 'wp-admin/includes/revision.php';
                    if (function_exists('wp_prepare_revisions_for_js')) {
                        $data = wp_prepare_revisions_for_js($this->_post, $latestRevision->ID);

                        if (isset($data['to']) && (int)$data['to'] == $latestRevision->ID && isset($data['diffData'])) {
                            $diffData = $data['diffData'];

                            if (is_array($diffData) && isset($diffData[0]['fields'])) {

                                if (isset($diffData[0]['fields'][0]) && isset($diffData[0]['fields'][0]['diff'])) {
                                    $diffDataTitle = $diffData[0]['fields'][0]['diff'];
                                } else {
                                    $diffDataTitle = __('No change', 'psn');
                                }
                                if (isset($diffData[0]['fields'][0]) && isset($diffData[0]['fields'][1]['diff'])) {
                                    $diffDataContent = $diffData[0]['fields'][1]['diff'];
                                } else {
                                    $diffDataContent = __('No change', 'psn');
                                }

                                $result['post_diff_title'] = $diffDataTitle;
                                $result['post_diff_content'] = $diffDataContent;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function _getAuthorData()
    {
        $result = array();

        $whitelist = IfwPsn_Wp_Proxy_Filter::apply('psn_notification_placeholders_author_data_whitelist',
            array('ID', 'user_login', 'user_email', 'user_url', 'user_registered', 'display_name',
                  'user_firstname', 'user_lastname', 'nickname', 'user_description'));

        if (empty($this->_post->post_author)) {
            // for generating placeholder list on backend help pages (just for the placeholders)
            $userId = IfwPsn_Wp_Proxy_User::getCurrentUserId();
        } else {
            $userId = (int)$this->_post->post_author;
        }

        $userdata = IfwPsn_Wp_Proxy_User::getData($userId);

        if ($userdata instanceof WP_User) {
            foreach($whitelist as $prop) {
                if (!$userdata->has_prop($prop)) {
                    continue;
                }
                if (strpos($prop, 'user_') === 0) {
                    $placeholder = str_replace('user_', 'author_', $prop);
                } else {
                    $placeholder = 'author_' . $prop;
                }
                $result[$placeholder] = $userdata->get($prop);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function _getCurrentUserData()
    {
        $result = array();

        $whitelist = IfwPsn_Wp_Proxy_Filter::apply('psn_notification_placeholders_current_user_data_whitelist',
            array('ID', 'user_login', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_status',
                'display_name', 'user_firstname', 'user_lastname', 'nickname', 'user_description'));

        $userdata = IfwPsn_Wp_Proxy_User::getCurrentUserData();

        if ($userdata instanceof WP_User) {
            foreach($whitelist as $prop) {
                if (!$userdata->has_prop($prop)) {
                    continue;
                }
                if (strpos($prop, 'user_') === 0) {
                    $placeholder = str_replace('user_', 'current_user_', $prop);
                } else {
                    $placeholder = 'current_user_' . $prop;
                }
                $result[$placeholder] = $userdata->get($prop);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function _getBloginfo()
    {
        $result = array();

        $whitelist = IfwPsn_Wp_Proxy_Filter::apply('psn_notification_placeholders_bloginfo_whitelist',
            array('name', 'description', 'wpurl', 'url', 'admin_email', 'version'));

        foreach($whitelist as $v) {
            $result['blog_' . $v] = get_bloginfo($v);
        }

        //$result['blog_admin_display_name'] = IfwPsn_Wp_Proxy_User::getAdminDisplayName();

        return $result;
    }

    /**
     * @return WP_Post|object
     */
    protected function _getPostMockup()
    {
        $this->_isMockUpPost = true;

        if (IfwPsn_Wp_Proxy_Blog::isMinimumVersion('3.5')) {
            // WP_Post since 3.5
            return new WP_Post(new stdClass());
        } else {
            // before 3.5
            global $wpdb;
            return $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts LIMIT 1"));
        }
    }

    /**
     * @return bool
     */
    public function isMockUpPost()
    {
        return $this->_isMockUpPost === true;
    }

    /**
     * @return string
     */
    public function getOnScreenHelp()
    {
        $this->addPlaceholder('post_status_before')->addPlaceholder('post_status_after');

        $placeholdersResult = $this->getDefaultPlaceholders(true, true);
        asort($placeholdersResult);
        $placeholdersDynamic = $this->getPlaceholders('dynamic');
        asort($placeholdersDynamic);
        $placeholdersArray = $this->getPlaceholders('arrays');
        asort($placeholdersArray);


        $context = array(
            'placeholders' => $placeholdersResult,
            'placeholdersDynamic' => $placeholdersDynamic,
            'placeholdersArray' => $placeholdersArray,
        );

        return IfwPsn_Wp_WunderScript_Parser::getFileInstance(IfwPsn_Wp_Plugin_Manager::getInstance('Psn'))->parse('admin_help_placeholders.html.twig', $context);
    }

    /**
     * @return array
     */
    public function prepareTwigContext()
    {
        // transfer the undelimited array placeholders to $context
        $context = $this->getReplacements('arrays', false);

        $this->removeGroup('arrays');

        foreach ($context as $key => $value) {
            $this->addPlaceholder($key, $key, 'arrays');
        }

        $this->_twigContext = $context;
    }

    /**
     * @param null $string
     * @return array
     */
    public function getTwigContext($string = null)
    {
        if (is_string($string)) {

            // only return the used placeholders
            $result = array();
            foreach ($this->_twigContext as $placeholder => $value) {
                if (strstr($string, $placeholder) !== false) {
                    // placeholder is used
                    if (empty($value)) {
                        // value is empty, try to lazy load
                        $value = $this->_lazyGetValue($this->addDelimiters($placeholder));
                    }
                    $result[$placeholder] = $value;
                }
            }

            return $result;

        } else {
            // return all placeholders
            return $this->_twigContext;
        }
    }

    /**
     *
     */
    public function revertTwigContext()
    {
        $this->removeGroup('arrays');

        foreach ($this->getTwigContext() as $key => $value) {
            $this->addPlaceholder($key, $value, 'arrays');
        }
    }
}
