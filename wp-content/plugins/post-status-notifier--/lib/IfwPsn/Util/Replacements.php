<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Replacements.php 348 2014-11-19 18:08:58Z timoreithde $
 */
class IfwPsn_Util_Replacements
{
    const MODE_EAGER = 1;
    const MODE_LAZY = 2;

    /**
     * @var string
     */
    protected $_delimiterFront = '[';

    /**
     * @var string
     */
    protected $_delimiterFrontRegex = '\[';

    /**
     * @var string
     */
    protected $_delimiterBehind = ']';

    /**
     * @var string
     */
    protected $_delimiterBehindRegex = '\]';

    /**
     * @var bool
     */
    protected $_autoDelimiters = false;

    /**
     * @var array
     */
    protected $_replacements = array();

    /**
     * @var
     */
    protected $_replaceMode;

    /**
     * @var string
     */
    protected $_lazyFilterPrefix = '';

    /**
     * @var array
     */
    protected $_skipOnParse = array();





    /**
     * @param null $replacements
     * @param array $options
     */
    public function __construct($replacements = null, $options = array())
    {
        $this->_replaceMode = self::MODE_LAZY;

        if (!empty($options)) {
            $this->setOptions($options);
        }
        if (is_array($replacements)) {
            $this->_replacements['default'] = $replacements;
        }
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if (isset($options['delimiter_front']) && !empty($options['delimiter_front'])) {
            $this->_delimiterFront = $options['delimiter_front'];
        }
        if (isset($options['delimiter_front_regex']) && !empty($options['delimiter_front_regex'])) {
            $this->_delimiterFrontRegex = $options['delimiter_front_regex'];
        }
        if (isset($options['delimiter_behind']) && !empty($options['delimiter_behind'])) {
            $this->_delimiterBehind = $options['delimiter_behind'];
        }
        if (isset($options['delimiter_behind_regex']) && !empty($options['delimiter_behind_regex'])) {
            $this->_delimiterBehindRegex = $options['delimiter_behind_regex'];
        }
        if (isset($options['auto_delimiters']) && $options['auto_delimiters'] === true) {
            $this->_autoDelimiters = true;
        }
        if (isset($options['replace_mode']) && $this->_isValidMode($options['replace_mode'])) {
            $this->_replaceMode = $options['replace_mode'];
        }
        if (isset($options['lazy_filter_prefix']) && is_string($options['lazy_filter_prefix'])) {
            $this->_lazyFilterPrefix = $options['lazy_filter_prefix'];
        }
    }

    /**
     * @param $placeholder
     * @param null $value
     * @param string $group
     * @return $this
     */
    public function addPlaceholder($placeholder, $value = null, $group = 'default')
    {
        $this->_replacements[$group][$placeholder] = $value;
        return $this;
    }

    /**
     * @param $placeholder
     * @param string $group
     * @return $this
     */
    public function removePlaceholder($placeholder, $group = 'default')
    {
        if (isset($this->_replacements[$group][$placeholder])) {
            unset($this->_replacements[$group][$placeholder]);
        }

        return $this;
    }

    /**
     * @param $group
     */
    public function removeGroup($group)
    {
        foreach ($this->getPlaceholders($group, false) as $placeholder) {
            $this->removePlaceholder($placeholder, $group);
        }
    }

    /**
     * @param $placeholder
     * @param $value
     * @param string $group
     * @return $this
     */
    public function setValue($placeholder, $value, $group = 'default')
    {
        $this->_replacements[$group][$placeholder] = $value;
        return $this;
    }

    /**
     * @param $placeholder
     * @param string $group
     * @return null
     */
    public function getValue($placeholder, $group = 'default')
    {
        if (isset($this->_replacements[$group][$placeholder])) {
            return $this->_replacements[$group][$placeholder];
        }
        return null;
    }

    /**
     * @param $placeholder
     * @param string $group
     * @return $this
     */
    public function addSkipPlaceholder($placeholder, $group = 'default')
    {
        $this->_skipOnParse[$group][$placeholder] = true;
        return $this;
    }

    /**
     * @param $placeholder
     * @param string $group
     * @return bool
     */
    public function isSkipPlaceholder($placeholder, $group = 'default')
    {
        return isset($this->_skipOnParse[$group]) && isset($this->_skipOnParse[$group][$placeholder]) && $this->_skipOnParse[$group][$placeholder] == true;
    }

    /**
     * @param string $group
     * @param bool $delimited
     * @param bool $ignoreSkip
     * @return array
     */
    public function getPlaceholders($group = 'default', $delimited = true, $ignoreSkip = false)
    {
        return array_keys($this->getReplacements($group, $delimited, $ignoreSkip));
    }

    /**
     * @param bool $delimited
     * @param bool $ignoreSkip
     * @return array
     */
    public function getDefaultPlaceholders($delimited = true, $ignoreSkip = false)
    {
        return array_keys($this->getReplacements('default', $delimited, $ignoreSkip));
    }

    /**
     * @return bool
     */
    public function isAutoDelimiters()
    {
        return $this->_autoDelimiters;
    }

    /**
     * @param $placeholder
     * @return string
     */
    public function addDelimiters($placeholder)
    {
        return $this->_delimiterFront . $placeholder . $this->_delimiterBehind;
    }

    /**
     * @return array
     */
    protected function _getFlattenedReplacements()
    {
        $result = array();

        foreach ($this->_replacements as $group => $replacements) {
            foreach ($replacements as $key => $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param string $group
     * @param bool $delimited
     * @param bool $ignoreSkip
     * @return array
     */
    public function getReplacements($group = 'default', $delimited = true, $ignoreSkip = false)
    {
        if ($group != 'default' && isset($this->_replacements[$group])) {
            $replacements = $this->_replacements[$group];
        } else {
            $replacements = $this->_getFlattenedReplacements();
        }

        if ($ignoreSkip == false) {
            foreach($replacements as $k => $v) {
                if ($this->isSkipPlaceholder($k, $group)) {
                    unset($replacements[$k]);
                }
            }
        }

        if ($this->isAutoDelimiters() && $delimited === true) {
            foreach($replacements as $k => $v) {
                $replacements[$this->addDelimiters($k)] = $v;
                unset($replacements[$k]);
            }
        }

        return $replacements;
    }

    /**
     * @param null $group
     * @return array
     */
    public function getReplacementsFullyLoaded($group = null)
    {
        $replacements = $this->getReplacements($group, true);

        foreach ($replacements as $placeholder => $value) {
            if (empty($value)) {
                $replacements[$placeholder] = $this->_lazyGetValue($placeholder);
            }
        }

        return $replacements;
    }

    /**
     * @return array
     */
    public function getDefaultReplacements()
    {
        return $this->getReplacements('default');
    }

    /**
     * @param $string
     * @return string
     */
    public function replace($string)
    {
        $result = $string;

        if ($this->_replaceMode == self::MODE_LAZY) {
            $result = $this->_lazyReplace($string);
        } elseif ($this->_replaceMode == self::MODE_EAGER) {
            $result = $this->_eagerReplace($string);
        }

        return $result;
    }

    /**
     * @param $string
     * @return string
     */
    protected function _eagerReplace($string)
    {
        return strtr($string, $this->getReplacements());
    }

    /**
     * @param $string
     * @return string
     */
    protected function _lazyReplace($string)
    {
        $regex = "/". $this->_delimiterFrontRegex .".*?". $this->_delimiterBehindRegex ."/";

        preg_match_all($regex, $string, $matches);
        $usedPlaceholders = $matches[0];

        $registeredReplacements = $this->getReplacements();

        $lazyReplacements = array();

        foreach ($usedPlaceholders as $placeholder) {
            if (array_key_exists($placeholder, $registeredReplacements)) {
                // found a used placeholder in the registered placeholders
                if (!empty($registeredReplacements[$placeholder])) {
                    // placeholder is preloaded with cheap value
                    $value = $registeredReplacements[$placeholder];
                } else {
                    // placeholder is empty eventually because of expensive value, try to load
                    $value = $this->_lazyGetValue($placeholder);
                }
                $lazyReplacements[$placeholder] = $value;
            }
        }

        return strtr($string, $lazyReplacements);
    }

    /**
     * @param $placeholder
     * @return mixed|void
     */
    protected function _lazyGetValue($placeholder)
    {
        return IfwPsn_Wp_Proxy_Filter::apply($this->_lazyFilterPrefix . $placeholder, '', $placeholder, $this);
    }

    /**
     * @param mixed $replaceMode
     */
    public function setReplaceMode($replaceMode)
    {
        if ($this->_isValidMode($replaceMode)) {
            $this->_replaceMode = $replaceMode;
        }
    }

    /**
     * @return mixed
     */
    public function getReplaceMode()
    {
        return $this->_replaceMode;
    }

    /**
     * @param $mode
     * @return bool
     */
    protected function _isValidMode($mode)
    {
        return in_array($mode, array(self::MODE_EAGER, self::MODE_LAZY));
    }

    /**
     * @param string $lazyFilterPrefix
     */
    public function setLazyFilterPrefix($lazyFilterPrefix)
    {
        $this->_lazyFilterPrefix = $lazyFilterPrefix;
    }

    /**
     * @return string
     */
    public function getLazyFilterPrefix()
    {
        return $this->_lazyFilterPrefix;
    }

    /**
     * @param string $delimiterBehindRegex
     */
    public function setDelimiterBehindRegex($delimiterBehindRegex)
    {
        $this->_delimiterBehindRegex = $delimiterBehindRegex;
    }

    /**
     * @return string
     */
    public function getDelimiterBehindRegex()
    {
        return $this->_delimiterBehindRegex;
    }

    /**
     * @param string $delimiterFrontRegex
     */
    public function setDelimiterFrontRegex($delimiterFrontRegex)
    {
        $this->_delimiterFrontRegex = $delimiterFrontRegex;
    }

    /**
     * @return string
     */
    public function getDelimiterFrontRegex()
    {
        return $this->_delimiterFrontRegex;
    }


}
