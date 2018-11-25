<?php
/**
 * Postponed notification handler
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Postponed.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Notification_Postponed 
{
    const TRANSIENT_ITEM_NAME = 'psn_postponed_post-';
    const MAX_LIFETIME = 60;

    /**
     * @var string
     */
    protected $_postponedAction = 'wp_loaded';

    /**
     * @var null|array
     */
    protected $_resultBuffer;

    /**
     * @var
     */
    protected $_notificationManager;


    /**
     * @param Psn_Notification_Manager $manager
     */
    public function __construct(Psn_Notification_Manager $manager)
    {
        $this->_notificationManager = $manager;
        $this->_init();
    }

    protected function _init()
    {
        add_action($this->_postponedAction, array($this, 'handle'));
    }

    /**
     * @param $statusAfter
     * @param $statusBefore
     * @param $post
     */
    public function add($statusAfter, $statusBefore, $post)
    {
        if ($post instanceof WP_Post) {
            $uid = $this->_getUid($post);
            set_transient($uid, array(
                'statusAfter' => $statusAfter,
                'statusBefore' => $statusBefore,
                'post' => $post
            ), self::MAX_LIFETIME);
        }
    }

    /**
     * @param $uid
     */
    public static function remove($uid)
    {
        delete_transient($uid);
    }

    /**
     * @param WP_Post $post
     * @return string
     */
    protected function _getUid(WP_Post $post)
    {
        return self::TRANSIENT_ITEM_NAME . uniqid($post->ID);
    }

    public function handle()
    {
        $items = $this->_getAll();

        if (is_array($items) && count($items) > 0) {
            foreach ($items as $row) {
                $item = maybe_unserialize($row['option_value']);

                if (isset($item['statusAfter']) && isset($item['statusBefore']) && isset($item['post'])) {
                    $this->_notificationManager->handlePostStatusTransition(
                        $item['statusAfter'], $item['statusBefore'], $item['post']
                    );
                }
            }
            $this->reset();
        }
    }

    /**
     * @return bool
     */
    public function hasPosts()
    {
        $posts = $this->_getAll();
        return !empty($posts);
    }

    /**
     * @return mixed
     */
    protected function _getAll($force = false)
    {
        if ($this->_resultBuffer === null || $force === true) {
            $db = IfwPsn_Wp_Proxy_Db::getObject();

            $sql = $db->prepare("
                SELECT * FROM $db->options
                WHERE option_name LIKE '%s'
            ",
                '_transient_' . self::TRANSIENT_ITEM_NAME . '%'
            );

            $this->_resultBuffer = $db->get_results($sql, ARRAY_A);
        }

        return $this->_resultBuffer;
    }

    protected function _resetResultBuffer()
    {
        $this->_resultBuffer = null;
    }

    /**
     * Resets the stored items
     */
    public function reset()
    {
        $items = $this->_getAll();

        if (is_array($items) && count($items) > 0) {
            foreach ($items as $row) {
                $this->remove(str_replace('_transient_', '', $row['option_name']));
            }
        }

        $this->_resetResultBuffer();
    }

    /**
     * @return bool
     */
    public function applies()
    {
//        return !is_admin() && did_action($this->_postponedAction) === 0;
        return !is_admin();
    }
}
