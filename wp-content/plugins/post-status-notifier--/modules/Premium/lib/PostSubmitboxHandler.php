<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: PostSubmitboxHandler.php 368 2015-04-10 15:02:07Z timoreithde $
 * @package
 */

class Psn_Module_Premium_PostSubmitboxHandler 
{
    const FORM_FIELD_NAME_BLOCK_NOTIFICATIONS = 'psn-block-notifications';


    /**
     * @var
     */
    protected $_module;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var bool
     */
    protected $_blockNotifications = false;


    /**
     * @param IfwPsn_Wp_Module_Bootstrap_Abstract $module
     */
    public function __construct(IfwPsn_Wp_Module_Bootstrap_Abstract $module)
    {
        $this->_module = $module;
        $this->_pm = $module->getPluginManager();

        $this->_init();
    }

    protected function _init()
    {
        if ($this->_pm->getAccess()->isAdmin() && !$this->_pm->getAccess()->isAjax() && !$this->_pm->getAccess()->isHeartbeat()) {

            if (isset($_POST['post_ID'])) {

                require_once $this->_pm->getPathinfo()->getRootLib() . '/Psn/Notification/Manager.php';
                $postId = (int)esc_attr($_POST['post_ID']);

                if (isset($_POST[self::FORM_FIELD_NAME_BLOCK_NOTIFICATIONS])) {
                    update_post_meta($postId, Psn_Notification_Manager::POST_CUSTOM_FIELD_KEY_BLOCK_NOTIFICATIONS, 1);
                } else {
                    delete_post_meta($postId, Psn_Notification_Manager::POST_CUSTOM_FIELD_KEY_BLOCK_NOTIFICATIONS, 1);
                }

            }


            // register action to add submit box content
            IfwPsn_Wp_Proxy_Action::add('post_submitbox_misc_actions', array($this, 'addSubmitBoxBlockFeature'));

            // add admin css
            if ($this->_pm->getAccess()->isPostNew() || $this->_pm->getAccess()->isPostEdit()) {
                IfwPsn_Wp_Proxy_Style::loadAdmin('psn-admin', $this->_module->getEnv()->getUrlCss() . 'admin.css');
            }
        }
    }

    /**
     * Renders the option to the post submit box
     */
    public function addSubmitBoxBlockFeature()
    {
        $disable = $this->_pm->getOptionsManager()->getOption('psn_disable_submitbox_block');
        $adminsOnly = $this->_pm->getOptionsManager()->getOption('psn_submitbox_block_admins_only');

        if (// show the block feature if:
            // - it's only activated for admins and the user is an admin
            (!empty($adminsOnly) && IfwPsn_Wp_User::isAdmin()) ||
            // - or it's not disabled
            empty($disable) && empty($adminsOnly)) {

            $nm = new Psn_Notification_Manager($this->_pm);
            $checked = ($nm->isBlockNotifications() || $this->_pm->getOptionsManager()->getOption('psn_submitbox_block_selected_by_default') != null) ? true : false;
            ?>
        <div class="misc-pub-section misc-pub-psn-notifications">
            <label title="<?php _e('Block notifications Post Status Notifier could generate for this post.', 'psn'); ?>"><input type="checkbox" name="<?php echo self::FORM_FIELD_NAME_BLOCK_NOTIFICATIONS; ?>" id="<?php echo self::FORM_FIELD_NAME_BLOCK_NOTIFICATIONS; ?>" value="1" <?php if ($checked): ?>checked="checked"<?php endif; ?>> <?php _e('Block notifications', 'psn'); ?></label>
        </div>
        <?php
        }
    }

}
 