<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a block call node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IfwPsn_Vendor_Twig_Node_BlockReference extends IfwPsn_Vendor_Twig_Node implements IfwPsn_Vendor_Twig_NodeOutputInterface
{
    public function __construct($name, $lineno, $tag = null)
    {
        parent::__construct(array(), array('name' => $name), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param IfwPsn_Vendor_Twig_Compiler A IfwPsn_Vendor_Twig_Compiler instance
     */
    public function compile(IfwPsn_Vendor_Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("\$this->displayBlock('%s', \$context, \$blocks);\n", $this->getAttribute('name')))
        ;
    }
}
