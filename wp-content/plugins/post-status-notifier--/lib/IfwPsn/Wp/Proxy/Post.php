<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Post.php 463 2015-09-18 10:25:34Z timoreithde $
 */ 
class IfwPsn_Wp_Proxy_Post extends IfwPsn_Wp_Proxy_Abstract
{
    protected static $_customFieldsAndValues;

    /**
     * Alias for get_post
     *
     * @param null $post
     * @param string $output
     * @param string $filter
     * @return null|WP_Post
     */
    public static function get($post = null, $output = OBJECT, $filter = 'raw')
    {
        return get_post($post, $output, $filter);
    }
    /**
     * Retrieves all post statuses
     *
     * @return array
     */
    public static function getAllStatuses()
    {
        global $wp_post_statuses;
        return $wp_post_statuses;
    }

    /**
     * Retrieves all post statuses keys
     *
     * @return array
     */
    public static function getAllStatusKeys()
    {
        return array_keys(self::getAllStatuses());
    }

    /**
     * Retrieves an array of all post statuses with labels and optional values
     * Available options:
     * - show_domain (bool)
     *
     * @param array $options
     * @return array
     */
    public static function getAllStatusesWithLabels($options = null)
    {
        $statuses = array();
        foreach (self::getAllStatuses() as $key => $status) {
            $label = $status->label;
            if (isset($options['show_domain']) && $options['show_domain'] === true &&
                isset($status->label_count['domain']) && $status->label_count['domain'] !== null) {
                $label .= ' ('. $status->label_count['domain'] . ')';
            }
            $statuses[$key] = $label;
        }
        return $statuses;
    }

    /**
     * Retrieves the user label of a post status. If it has no label, the status will be returned
     *
     * @param $status
     * @return string|void
     */
    public static function getStatusLabel($status)
    {
        $wp_post_statuses = self::getAllStatuses();

        if (isset($wp_post_statuses[$status])) {
            $label = $wp_post_statuses[$status]->label;
        } else {
            $label = $status;
        }
        return $label;
    }

    /**
     * Alias for get_post_types
     *
     * @param array $args
     * @param string $output
     * @param string $operator
     * @internal param array $options
     * @return array
     */
    public static function getTypes($args = array(), $output = 'names', $operator = 'and')
    {
        return get_post_types($args, $output, $operator);
    }

    /**
     * Retrieves the builtin post types
     *
     * @param array $args
     * @param string $output
     * @param string $operator
     * @internal param array $options
     * @return array
     */
    public static function getDefaultPostTypes($args = array(), $output = 'names', $operator = 'and')
    {
        $args['_builtin'] = true;

        return self::getTypes($args, $output, $operator);
    }

    /**
     * Retrieves the custom post types
     *
     * @param array $args
     * @param string $output
     * @param string $operator
     * @return array
     */
    public static function getCustomPostTypes($args = array(), $output = 'names', $operator = 'and')
    {
        $args['_builtin'] = false;

        return self::getTypes($args, $output, $operator);
    }

    /**
     * Retrieves the keys of all custom post types
     *
     * @return array
     */
    public static function getCustomPostTypesKeys()
    {
        return array_keys(self::getCustomPostTypes());
    }

    /**
     * Retrieves the keys of all default post types
     *
     * @return array
     */
    public static function getDefaultPostTypesKeys()
    {
        return array_keys(self::getDefaultPostTypes());
    }

    /**
     * Retrieves the keys of all post types
     *
     * @return array
     */
    public static function getAllTypesKeys()
    {
        return array_unique(
            array_merge(
                self::getCustomPostTypesKeys(),
                self::getDefaultPostTypesKeys()
            )
        );
    }

    /**
     * Retrieves an array of all post types with type and label
     *
     * @param array $options
     * @return array
     */
    public static function getAllTypesWithLabels($options = array())
    {
        $types = array();
        // default types
        foreach( self::getDefaultPostTypes($options, 'objects') as $type => $values ) {
            $types[$type] = $values->labels->name;
        }
        // custom post types
        foreach( self::getCustomPostTypes($options, 'objects') as $type => $values ) {
            $types[$type] = $values->labels->name;
        }

        return array_unique($types);
    }

    /**
     * @param $post
     * @return array
     */
    public static function getAttachedCategories($post)
    {
        $result = array();

        if ($post instanceof WP_Post) {

            if (self::isDefaultType($post)) {
                foreach (self::getAttachedCategoriesIds($post) as $catId) {
                    $cat = get_category($catId);
                    array_push($result, $cat);
                }
            } else {
                $result = self::getAllTerms($post);
            }
        }

        return $result;
    }

    /**
     * @param $post
     * @return array
     */
    public static function getAttachedCategoriesIds($post)
    {
        $result = array();

        if ($post instanceof WP_Post) {
            if (self::isDefaultType($post)) {
                $result = wp_get_post_categories($post->ID);
            } else {
                // on custom post type
                foreach (self::getAttachedCategories($post) as $cat) {
                    array_push($result, (int)$cat->term_id);
                }
            }
        }

        return $result;
    }

    /**
     * @param $post
     * @param null $taxonomy
     * @return array
     */
    public static function getAttachedCategoriesNames($post, $taxonomy = null)
    {
        $result = array();

        $categories = self::getAttachedCategories($post);

        if (is_array($categories)) {
            foreach (self::getAttachedCategories($post) as $cat) {
                if (isset($cat->name)) {
                    if ($taxonomy === null or ($taxonomy !== null && $taxonomy == $cat->taxonomy)) {
                        array_push($result, $cat->name);
                    }
                }
            }
        }

        $result = array_map('html_entity_decode', $result);

        return $result;
    }

    /**
     * @param $post
     * @param null $taxonomy
     * @return array
     */
    public static function getAttachedCategoriesSlugs($post, $taxonomy = null)
    {
        $result = array();

        $categories = self::getAttachedCategories($post);

        if (is_array($categories)) {
            foreach (self::getAttachedCategories($post) as $cat) {
                if (isset($cat->slug)) {
                    if ($taxonomy === null or ($taxonomy !== null && $taxonomy == $cat->taxonomy)) {
                        array_push($result, $cat->slug);
                    }
                }
            }
        }

        $result = array_map('html_entity_decode', $result);

        return $result;
    }

    /**
     * Retrieves all post categories
     *
     * @return array
     */
    public static function getAllCategories()
    {
        $categories = array();

        // posts
        foreach (get_terms( 'category', 'hide_empty=0' ) as $term) {
            array_push($categories, array('id' => (int)$term->term_id, 'name' => $term->name));
        }

        return $categories;
    }

    /**
     * Retrieves categories of all custom post types
     *
     * @return array
     */
    public static function getCustomPostTypesCategories()
    {
        $categories = array();

        // custom post types taxonomies
        foreach (self::getCustomPostTypesKeys() as $type) {
            $terms = self::getCustomPostTypeCategories($type);

            if (!empty($terms)) {
                $categories[$type] = $terms;
            }
        }

        return $categories;
    }

    /**
     * @return array
     */
    public static function getAllTypesCategories()
    {
        $result = array();

        foreach ( IfwPsn_Wp_Proxy_Post::getAllTypesWithLabels() as $posttype => $params) {

            $categories = IfwPsn_Wp_Proxy_Categories::getByPosttype($posttype);

            if (!empty($categories)) {
                if (!isset($result[$posttype])) {
                    $result[$posttype] = array();
                }
                foreach ($categories as $cat) {
                    array_push($result[$posttype], array(
                        'id' => (int)$cat->term_id,
                        'name' => $cat->name,
                    ));
                }
            }
        }

//        $result = array_merge($result, array('post' => self::getAllCategories()));
//        $result = array_merge($result, self::getCustomPostTypesCategories());

        return $result;
    }

    /**
     * Retrieves all category id's of a post type
     *
     * @param null $posttype
     * @return array
     */
    public static function getAllCategoryIds($posttype = null)
    {
        $result = array();

        if ($posttype === null || $posttype == 'post') {
            $categories = self::getAllCategories();
        } else {
            $categories = self::getCustomPostTypeCategories($posttype);
        }

        foreach($categories as $cat) {
            array_push($result, $cat['id']);
        }
        sort($result);
        return array_unique($result);
    }

    /**
     * Retrieves categories of one given custom post types
     *
     * @param $posttype
     * @return array
     */
    public static function getCustomPostTypeCategories($posttype)
    {
        $terms = array();

        $args = array(
//            'public' => false,
            '_builtin' => false,
            'hierarchical' => 1,
            'object_type' => array($posttype)
        );

        $taxonomies = get_taxonomies( $args, 'names', 'and' );

        if ( $taxonomies ) {
            foreach ( $taxonomies  as $taxonomy ) {
                foreach(get_terms($taxonomy, 'hide_empty=0') as $term) {
                    array_push($terms, array('id' => (int)$term->term_id, 'name' => $term->name));
                }
            }
        }

        return $terms;
    }

    /**
     * Retrieves categories ids of one given custom post type
     *
     * @param $posttype
     * @return array
     */
    public static function getCustomPostTypeCategoryIds($posttype)
    {
        $result = array();
        $categories = self::getCustomPostTypeCategories($posttype);
        foreach($categories as $cat) {
            array_push($result, $cat['id']);
        }
        return array_unique($result);
    }

    /**
     * Retrieves tags of one given custom post types
     *
     * @param $posttype
     * @return array
     */
    public static function getCustomPostTypeTags($posttype)
    {
        $terms = array();

        $args = array(
//            'public' => false,
            '_builtin' => false,
            'hierarchical' => null,
            'object_type' => array($posttype)
        );

        $taxonomies = get_taxonomies( $args, 'names', 'and' );

        if ( $taxonomies ) {
            foreach ( $taxonomies  as $taxonomy ) {
                foreach(get_terms($taxonomy, 'hide_empty=0') as $term) {
                    array_push($terms, array('id' => (int)$term->term_id, 'name' => $term->name));
                }
            }
        }

        return $terms;
    }

    /**
     * Retrieves tags ids of one given custom post type
     *
     * @param $posttype
     * @return array
     */
    public static function getCustomPostTypeTagIds($posttype)
    {
        $result = array();
        $tags = self::getCustomPostTypeTags($posttype);
        foreach($tags as $tag) {
            array_push($result, $tag['id']);
        }
        return array_unique($result);
    }

    /**
     * Retrieves post type of a given post
     *
     * @param $post
     * @return false|string
     */
    public static function getType($post)
    {
        if ($post instanceof WP_Post) {
            return $post->post_type;
        }
        return false;
    }

    /**
     * @param $post
     * @return bool
     */
    public static function isDefaultType($post)
    {
        return $post instanceof WP_Post && $post->post_type == 'post';
    }

    /**
     * Retrieves taxonomies of a given post
     *
     * @param $post
     * @param string $output
     * @return array|null
     */
    public static function getTaxonomies($post, $output = 'names')
    {
        return get_object_taxonomies($post, $output);
//        return get_object_taxonomies(self::getType($post), $output);
    }

    /**
     * @param $post
     * @return array|null
     */
    public static function getTaxonomiesNames($post)
    {
        $taxonomies = self::getTaxonomies($post);

        if (!empty($taxonomies)) {
            if (!is_array($taxonomies)) {
                $taxonomies = array($taxonomies);
            }
            return array_values($taxonomies);
        }
        return null;
    }

    /**
     * @param $post
     * @return array|null
     */
    public static function getTaxonomiesObjects($post)
    {
        $taxonomies = self::getTaxonomies($post, 'objects');

        if (!empty($taxonomies)) {
            return $taxonomies;
        }
        return null;
    }

    /**
     * Retrieves all terms of a given post
     *
     * @param $post
     * @param string $taxonomy
     * @return array
     */
    public static function getAllTerms($post, $taxonomy = 'category')
    {
        $result = array();

        if (self::getType($post) == 'post') {
            // default post
            $result = get_the_terms($post->ID, $taxonomy);
        } else {
            // custom post type
            $taxonomies = self::getTaxonomiesObjects($post);

            if (!empty($taxonomies)) {

                foreach ($taxonomies as $name => $object) {
                    if ($name == 'post_format') {
                        continue;
                    }

                    if (($taxonomy == 'category' && $object->hierarchical == true) ||
                        ($taxonomy == 'post_tag' && $object->hierarchical == false)) {

                        $tmpTerms = get_the_terms($post->ID, $name);
                        if (is_array($tmpTerms)) {
                            $result = array_merge($result, $tmpTerms);
                        }
                    }
                }
            }
        }

        if (!is_array($result)) {
            $result = array();
        }
        return $result;
    }

    /**
     * Retrieves all term ids of a given post
     *
     * @param $post
     * @return array
     * @deprecated
     */
    public static function getAllTermIds($post)
    {
        $result = array();

        foreach (self::getAllTerms($post) as $term) {
            array_push($result, (int)$term->term_id);
        }
        $result = array_unique($result);
        sort($result);

        return $result;
    }

    /**
     * @param $post
     * @return array
     */
    public static function getAttachedTags($post)
    {
        $result = array();

        if ($post instanceof $post) {
            if (self::isDefaultType($post)) {
                $result = wp_get_post_tags($post->ID);
            } else {
                $result = self::getAllTerms($post, 'post_tag');
            }
        }

        return $result;
    }

    /**
     * @param $post
     * @return array
     */
    public static function getAttachedTagsId($post)
    {
        $result = array();

        if ($post instanceof $post) {

            foreach (self::getAttachedTags($post) as $tag) {
                array_push($result, (int)$tag->term_id);
            }
        }

        return $result;
    }

    /**
     * @param $post
     * @param null $taxonomy
     * @return array
     */
    public static function getAttachedTagsNames($post, $taxonomy = null)
    {
        $result = array();

        $tags = self::getAttachedTags($post);

        if (is_array($tags)) {
            foreach (self::getAttachedTags($post) as $tag) {
                if (isset($tag->name)) {
                    if ($taxonomy === null or ($taxonomy !== null && $taxonomy == $tag->taxonomy)) {
                        array_push($result, $tag->name);
                    }
                }
            }
        }

        $result = array_map('html_entity_decode', $result);

        return $result;
    }

    /**
     * @param $post
     * @param null $taxonomy
     * @return array
     */
    public static function getAttachedTagsSlugs($post, $taxonomy = null)
    {
        $result = array();

        $tags = self::getAttachedTags($post);

        if (is_array($tags)) {
            foreach (self::getAttachedTags($post) as $tag) {
                if (isset($tag->slug)) {
                    if ($taxonomy === null or ($taxonomy !== null && $taxonomy == $tag->taxonomy)) {
                        array_push($result, $tag->slug);
                    }
                }
            }
        }

        $result = array_map('html_entity_decode', $result);

        return $result;
    }

    /**
     * Retrieves the user label of a given post type
     *
     * @param $type
     * @return mixed
     */
    public static function getTypeLabel($type)
    {
        $types = self::getAllTypesWithLabels();

        if (isset($types[$type])) {
            $label = $types[$type];
        } else {
            $label = $type;
        }

        return $label;
    }

    /**
     * Retrieves the slug of a post
     *
     * @param int $postId
     * @return mixed
     */
    public static function getSlug($postId)
    {
        $post_data = self::get($postId, ARRAY_A);
        $slug = $post_data['post_name'];
        return $slug;
    }

    /**
     * Retrieves the permalink of a post
     *
     * @param $post
     * @return bool|string
     */
    public static function getPermalink($post)
    {
        return get_permalink($post->ID);
    }

    /**
     * @param $id The post id
     * @param string $context
     * @return string
     */
    public static function getEditLink($id, $context = '')
    {
        $result = '';

        if (function_exists('get_edit_post_link')) {
            $link = get_edit_post_link($id, $context);
            if (!empty($link)) {
                $result = $link;
            }
        }

        return $result;
    }

    /**
     * Retrieves the post's custom keys
     *
     * @param $post
     * @return array
     */
    public static function getCustomKeys($post)
    {
        $result = array();

        if (isset($post->ID) && $post->ID != null) {
            $customKeys = get_post_custom_keys($post->ID);
            if (is_array($customKeys)) {
                $result = $customKeys;
            }
        }

        return $result;
    }

    /**
     * @param $key
     * @param $post
     * @return string
     */
    public static function getCustomKeyValue($key, $post)
    {
        return implode(',', array_values(get_post_custom_values($key, $post->ID)));
    }

    /**
     * Retrieves the post's custom keys with value
     *
     * @param $post
     * @return array
     */
    public static function getCustomKeysAndValues($post)
    {
        $result = array();

        foreach (self::getCustomKeys($post) as $key) {
            $result[$key] = self::getCustomKeyValue($key, $post);
        }

        return $result;
    }

    /**
     * @param $post
     * @return mixed|null
     */
    public static function getFormat($post)
    {
        if (function_exists('get_post_format')) {
            $format = get_post_format($post);
        }

        if (isset($format) && !empty($format)) {
            return $format;
        }
        return null;
    }

    /**
     * @param $post
     * @param int $limit
     * @param string $appendix
     * @return string
     */
    public static function getWords($post, $limit = 100, $appendix = ' ... ')
    {
        $result = '';

        if ($post instanceof WP_Post) {

            $words = explode(' ', strip_tags($post->post_content));
            $result = implode(' ', array_splice($words, 0, $limit));
            if (count($words) > $limit) {
                $result .= $appendix;
            }
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public static function getCurrentPostType()
    {
        global $post, $typenow;

        $posttype = null;

        if ( $post && $post->post_type ) {
            $posttype = $post->post_type;
        } elseif ($typenow) {
            $posttype = $typenow;
        } elseif (!empty($_GET['post'])) {
            $post = get_post($_GET['post']);
            $posttype = $post->post_type;
        } elseif (!empty($_POST['post_ID'])) {
            $post = get_post($_POST['post_ID']);
            $posttype = $post->post_type;
        }

        return $posttype;
    }
}
