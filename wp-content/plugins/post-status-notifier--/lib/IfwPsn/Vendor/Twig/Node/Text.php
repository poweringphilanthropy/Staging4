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
 * Represents a text node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IfwPsn_Vendor_Twig_Node_Text extends IfwPsn_Vendor_Twig_Node implements IfwPsn_Vendor_Twig_NodeOutputInterface
{
    public function __construct($data, $lineno)
    {
        parent::__construct(array(), array('data' => $data), $lineno);
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
            ->write('echo ')
            ->string($this->getAttribute('data'))
            ->raw(";\n")
        ;
    }
}
