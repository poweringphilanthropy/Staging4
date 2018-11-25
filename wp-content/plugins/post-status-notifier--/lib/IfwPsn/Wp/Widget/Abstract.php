<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Abstract Widget Class
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 379 2015-01-02 13:51:00Z timoreithde $
 */
abstract class IfwPsn_Wp_Widget_Abstract extends WP_Widget
{
    /**
     * Stores the widget's options
     * @var array
     */
    protected $_options;
    
    /**
     * Plugin Manager
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;
    
    /**
     * Template object
     * @var IfwPsn_Vendor_Twig_Environment
     */
    protected $_tpl;
    
    
    
    /**
     * 
     */
    public function __construct()
    {
        $this->_pm = $this->_fetchPluginManager();

        $this->_initTemplateEngine();
        $this->_init();
        $this->_initOptions();

        $widgetOptions = array_merge(array(
            'description' => $this->getDescription()
        ), $this->getWidgetOptions());

        parent::__construct($this->getIdBase(), $this->getName(), $widgetOptions, $this->getControlOptions());
    }

    public function load()
    {
        IfwPsn_Wp_Proxy_Action::addWidgetsInit(array($this, 'register'));
    }

    public function register()
    {
        return register_widget(get_class($this));
    }
    
    /**
     * 
     */
    public function getIdBase()
    {
        return preg_replace( '/(wp_)?widget_/', '', strtolower(get_class($this)) );
    }
    
    /**
     * (non-PHPdoc)
     * @see WP_Widget::widget()
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        try {
            $this->_displayWidget($args, $instance);
            
        } catch (Exception $e) {
            $this->_pm->handleException($e);
        }
        
        echo $args['after_widget'];
    }
    
    /**
     * (non-PHPdoc)
     * @see WP_Widget::update()
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        
        // update instance
        foreach ($this->_options as $option) {
            if (isset($option['sanitizer']) && is_callable($option['sanitizer'])) {
                // call defined sanitizer
                $value = call_user_func_array($option['sanitizer'], array($new_instance[$option['name']]));
            } else {
                // default sanitizer
                $value = sanitize_text_field($new_instance[$option['name']]);
            }
            $instance[$option['name']] = $value;
        }
        
        if (method_exists($this, '_customUpdate')) {
            $this->_customUpdate($instance);
        }
        
        return $instance;
    }
    
    /**
     * (non-PHPdoc)
     * @see WP_Widget::form()
     */
    public function form($instance)
    {
        $params = array(
            'instance' => $this->_prepareFormDefaults($instance)
        );

        $field_id = array();
        $field_name = array();
        foreach ($this->_fetchOptions() as $option) {
            $field_id[$option['name']] = $this->get_field_id($option['name']);
            $field_name[$option['name']] = $this->get_field_name($option['name']);
        }

        $params['field_id'] = $field_id;
        $params['field_name'] = $field_name;
        
        $this->_displayForm($params);
    }

    /**
     * Inits the template engine
     */
    protected function _initTemplateEngine()
    {
        $this->_tpl = IfwPsn_Wp_Tpl::getFilesytemInstance($this->_pm);
    }
    
    /**
     * Inits the widget's options
     * 
     */
    protected function _initOptions()
    {
        $options = $this->_fetchOptions();
        
        foreach($options as $k => $opt) {
            if (!isset($opt['name']) || empty($opt['name'])) {
                unset($options[$k]);
            }
        }
        $this->_options = $options;
    }
    
    /**
     * Retrieves the widget's options
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * Populates form fields with default values
     * 
     * @param array $instance
     * @return array
     */
    protected function _prepareFormDefaults($instance)
    {
        $defaults = array();
        foreach ($this->getOptions() as $opt) {
            if (!isset($opt['name']) || empty($opt['name'])) {
                continue;
            }
            $defaults[$opt['name']] = isset($opt['default']) ? $opt['default'] : '';
        }
        
        return wp_parse_args((array)$instance, $defaults);
    }
    
    /**
     * Could be overwritten by widget class
     */
    protected function _init()
    {
    }
    
    abstract public function getName();
    abstract public function getDescription();
    abstract public function getWidgetOptions();
    abstract public function getControlOptions();
    abstract protected function _displayForm($instance);
    abstract protected function _displayWidget($args, $instance);
    abstract protected function _fetchOptions();
    abstract protected function _fetchPluginManager();
}
