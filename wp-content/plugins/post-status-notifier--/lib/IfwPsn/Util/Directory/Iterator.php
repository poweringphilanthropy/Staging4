<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Iterator.php 467 2015-10-01 21:34:26Z timoreithde $
 */ 
class IfwPsn_Util_Directory_Iterator extends DirectoryIterator
{
    protected $_classname;

    /**
     * @return string
     */
    public function getExtension()
    {
        $filename = $this->getFilename();
        $fileExtension = strrpos($filename, '.', 1) + 1;
        if ($fileExtension != false)
            return strtolower(substr($filename, $fileExtension, strlen($filename) - $fileExtension));
        else
            return '';
    }

    /**
     * Get basename of file without extension
     * @return string
     */
//    public function getBasename($suffix = NULL)
//    {
//        return parent::getBasename('.'.$this->getExtension());
//    }

    /**
     * Get the name of the class defined in this file
     *
     * @return bool|string
     */
    public function getClassname()
    {
        if ($this->_classname == null && is_file($this->getPathname())) {

            $fp = fopen($this->getPathname(), 'r');

            if (!$fp) {
                return false;
            }

            $class = $buffer = '';
            $i = 0;
            while (!$class) {
                if (feof($fp)) break;

                $buffer .= fread($fp, 512);
                $tokens = @token_get_all($buffer);

                if (strpos($buffer, '{') === false) continue;

                for (;$i<count($tokens);$i++) {
                    if ($tokens[$i][0] === T_CLASS) {
                        for ($j=$i+1;$j<count($tokens);$j++) {
                            if ($tokens[$j] === '{') {
                                $class = $tokens[$i+2][1];
                            }
                        }
                    }
                }
            }

            if (!empty($class)) {
                $this->_classname = $class;
            }
        }

        return $this->_classname;
    }
}
