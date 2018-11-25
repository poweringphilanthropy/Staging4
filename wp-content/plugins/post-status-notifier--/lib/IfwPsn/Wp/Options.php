<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Options
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Options.php 463 2015-09-18 10:25:34Z timoreithde $
 */
class IfwPsn_Wp_Options
{
    /**
     * @var array
     */
    public static $_instances = array();

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var string
     */
    protected $_pageId;

    /**
     * @var string
     */
    protected $_sectionPrefix;

    /**
     * @var string
     */
    protected $_fieldPrefix;

    /**
     * @var array
     */
    protected $_sections = array();

    /**
     * @var int
     */
    protected $_addedFields = 0;

    /**
     * @var IfwPsn_Wp_Options_Renderer_Interface
     */
    protected $_renderer;


    /**
     * Retrieves singleton object
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return IfwPsn_Wp_Options
     */
    public static function getInstance(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (!isset(self::$_instances[$pm->getAbbr()])) {
            self::$_instances[$pm->getAbbr()] = new self($pm);
        }
        return self::$_instances[$pm->getAbbr()];
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, $renderer = null)
    {
        $this->_pm = $pm;
        $this->_pageId = $pm->getAbbrLower() . '_options';
        $this->_sectionPrefix = $pm->getAbbrLower() . '_options_section_';
        $this->_fieldPrefix = $pm->getAbbrLower() . '_option_';
        if ($renderer !== null && $renderer instanceof IfwPsn_Wp_Options_Renderer_Interface) {
            $this->_renderer = $renderer;
        } else {
            $this->_renderer = new IfwPsn_Wp_Options_Renderer_Default();
        }
    }

    public function init()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Proxy/Action.php';
        IfwPsn_Wp_Proxy_Action::addAdminInit(array($this, 'register'));
        add_filter('pre_update_option_' . $this->_pageId, array($this, 'sanitizeFields'), 10, 2);
    }

    /**
     * Loads the default general options section and triggers an action
     */
    public function load()
    {
        add_action('init', array($this, '_load'));
    }

    public function _load()
    {
        if ($this->_pm->getAccess()->isPlugin() ||
            (isset($_POST['option_page']) && $_POST['option_page'] == $this->_pageId)) {
            // init the option objects only if it is a exact plugin admin page access or save request

            $generalOptions = new IfwPsn_Wp_Options_Section('general', __('General', 'ifw'));
            IfwPsn_Wp_Proxy_Action::doAction($this->_pm->getAbbrLower() . '_general_options_init', $generalOptions);
            $this->addSection($generalOptions);

            $externalOptions = new IfwPsn_Wp_Options_Section('external', '');
            IfwPsn_Wp_Proxy_Action::doAction($this->_pm->getAbbrLower() . '_external_options_init', $externalOptions);
            $this->addSection($externalOptions);

            do_action($this->_pm->getAbbrLower() . '_on_options_load');
        }
    }

    /**
     * @param IfwPsn_Wp_Options_Section $section
     * @param int $priority
     */
    public function addSection(IfwPsn_Wp_Options_Section $section, $priority = 10)
    {
        $this->_sections[$priority][uniqid()] = $section;
    }

    /**
     * Callback for admin_init
     */
    public function register()
    {
        ksort($this->_sections);

        /**
         * @var $section IfwPsn_Wp_Options_Section
         */
        foreach ($this->_sections as $priority) {
            foreach ($priority as $section) {

                if (!$section->hasFields()) {
                    continue;
                }

                $sectionPage = $section->hasPageId() ? $section->getPageId() : $this->_pageId;

                add_settings_section(
                    $this->_sectionPrefix . $section->getId(), // section id
                    $section->getLabel(), // section label
                    array($section, 'render'), // callback to render the section's description
                    $sectionPage // options page id on which to add this section
                );

                /**
                 * @var $field IfwPsn_Wp_Options_Field
                 */
                foreach ($section->getFields() as $field) {

                    $fieldPage = $field->hasPageId() ? $field->getPageId() : $sectionPage;

                    add_settings_field(
                        $this->_fieldPrefix . $field->getId(), // field id
                        $field->getLabel(), // field label
                        array($field, 'render'), // method to render the field
                        $fieldPage, // page id
                        $this->_sectionPrefix . $section->getId(), // section id
                        array($this) // passed to the render method of the field
                    );

                    $this->_addedFields++;
                }

                register_setting($this->_pageId, $this->_pageId);
            }
        }
    }

    /**
     * Default sanitizer
     *
     * @param $new
     * @param $old
     * @return mixed
     */
    public function sanitizeFields($new, $old)
    {
        $customSanitizer = array();

        foreach ($this->_sections as $priority) {
            foreach ($priority as $section) {

                if (!$section->hasFields()) {
                    continue;
                }

                /**
                 * @var $field IfwPsn_Wp_Options_Field
                 */
                foreach ($section->getFields() as $field) {

                    if ($field->hasSanitizer()) {
                        $customSanitizer[$field->getId()] = $field->getSanitizer();
                    }
                }
            }
        }

        foreach ($new as $k => $v) {
            $fieldName = str_replace($this->_fieldPrefix, '', $k);

            if (array_key_exists($fieldName, $customSanitizer)) {
                if (is_callable($customSanitizer[$fieldName])) {
                    $new[$k] = call_user_func($customSanitizer[$fieldName], $v);
                } elseif ($customSanitizer[$fieldName] == 'number') {
                    $new[$k] = preg_replace('/[^\d]/', '', $v);
                } elseif ($customSanitizer[$fieldName] == 'alphanum') {
                    $new[$k] = preg_replace('/[^\w_-]/', '', $v);
                }
            } else {
                $new[$k] = sanitize_text_field($v);
            }
        }

        return $new;
    }

    /**
     * Renders the options form
     */
    public function render($pageId = null)
    {
        $this->_renderer->render($this, $pageId);
    }

    /**
     * @return string
     */
    public function getFieldPrefix()
    {
        return $this->_fieldPrefix;
    }

    /**
     * @return string
     */
    public function getPageId()
    {
        return $this->_pageId;
    }

    /**
     * @return string
     */
    public function getSectionPrefix()
    {
        return $this->_sectionPrefix;
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasOption($id)
    {
        $options = IfwPsn_Wp_Proxy::getOption($this->_pageId);

        return isset($options[$this->getOptionRealId($id)]);
    }

    /**
     * @param $id
     * @return null|mixed
     */
    public function getOption($id)
    {
        $result = null;

        if ($this->hasOption($id)) {
            $options = IfwPsn_Wp_Proxy::getOption($this->_pageId);
            $result = htmlspecialchars_decode($options[$this->getOptionRealId($id)]);
        }

        return $result;
    }

    /**
     * @param $id
     * @param $value
     * @return bool
     */
    public function updateOption($id, $value)
    {
        $options = IfwPsn_Wp_Proxy::getOption($this->_pageId);
        $options[$this->getOptionRealId($id)] = $value;
        return update_option($this->_pageId, $options);
    }

    /**
     * @param $id
     * @return bool
     */
    public function isEmptyOption($id)
    {
        $result = $this->getOption($id);
        return empty($result);
    }

    /**
     * @param $id
     * @return string
     */
    public function getOptionRealId($id)
    {
        return $this->_fieldPrefix . $id;
    }

    /**
     * Retrieves all options
     */
    public function getAll()
    {
        return IfwPsn_Wp_Proxy::getOption($this->_pageId);
    }

    /**
     * Deletes all options
     */
    public function reset()
    {
        IfwPsn_Wp_Proxy::deleteOption($this->_pageId);
    }

    /**
     * @return IfwPsn_Wp_Options_Renderer_Interface
     */
    public function getRenderer()
    {
        return $this->_renderer;
    }

    /**
     * @param IfwPsn_Wp_Options_Renderer_Interface $renderer
     */
    public function setRenderer($renderer)
    {
        $this->_renderer = $renderer;
    }

    /**
     * @return int
     */
    public function getAddedFields()
    {
        return $this->_addedFields;
    }

    /**
     * @return array
     */
    public function getSections()
    {
        return $this->_sections;
    }

    /**
     * @return bool
     */
    public function isAccess()
    {
        return $this->_pm->getAccess()->isPlugin() || $this->_pm->getAccess()->isOptionsSubmit();
    }

    /**
     * @param $callback
     */
    public function addPluginOptions($callback)
    {
        if ($this->isAccess() && is_callable($callback)) {
            add_action($this->_pm->getAbbrLower() . '_on_options_load', $callback);
        }
    }

}
