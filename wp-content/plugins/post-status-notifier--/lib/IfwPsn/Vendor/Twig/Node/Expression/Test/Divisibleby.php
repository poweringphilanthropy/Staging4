<?php

/*
 * This file is part of Twig.
 *
 * (c) 2011 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Checks if a variable is divisible by a number.
 *
 * <pre>
 *  {% if loop.index is divisibleby(3) %}
 * </pre>
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IfwPsn_Vendor_Twig_Node_Expression_Test_Divisibleby extends IfwPsn_Vendor_Twig_Node_Expression_Test
{
    public function compile(IfwPsn_Vendor_Twig_Compiler $compiler)
    {
        $compiler
            ->raw('(0 == ')
            ->subcompile($this->getNode('node'))
            ->raw(' % ')
            ->subcompile($this->getNode('arguments')->getNode(0))
            ->raw(')')
        ;
    }
}
