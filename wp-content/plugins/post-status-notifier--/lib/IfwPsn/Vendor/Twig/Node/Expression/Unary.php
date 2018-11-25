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
abstract class IfwPsn_Vendor_Twig_Node_Expression_Unary extends IfwPsn_Vendor_Twig_Node_Expression
{
    public function __construct(IfwPsn_Vendor_Twig_NodeInterface $node, $lineno)
    {
        parent::__construct(array('node' => $node), array(), $lineno);
    }

    public function compile(IfwPsn_Vendor_Twig_Compiler $compiler)
    {
        $compiler->raw('(');
        $this->operator($compiler);
        $compiler
            ->subcompile($this->getNode('node'))
            ->raw(')')
        ;
    }

    abstract public function operator(IfwPsn_Vendor_Twig_Compiler $compiler);
}
