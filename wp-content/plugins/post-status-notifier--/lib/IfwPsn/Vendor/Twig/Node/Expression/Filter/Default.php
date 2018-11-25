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
 * Returns the value or the default value when it is undefined or empty.
 *
 * <pre>
 *  {{ var.foo|default('foo item on var is not defined') }}
 * </pre>
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IfwPsn_Vendor_Twig_Node_Expression_Filter_Default extends IfwPsn_Vendor_Twig_Node_Expression_Filter
{
    public function __construct(IfwPsn_Vendor_Twig_NodeInterface $node, IfwPsn_Vendor_Twig_Node_Expression_Constant $filterName, IfwPsn_Vendor_Twig_NodeInterface $arguments, $lineno, $tag = null)
    {
        $default = new IfwPsn_Vendor_Twig_Node_Expression_Filter($node, new IfwPsn_Vendor_Twig_Node_Expression_Constant('default', $node->getLine()), $arguments, $node->getLine());

        if ('default' === $filterName->getAttribute('value') && ($node instanceof IfwPsn_Vendor_Twig_Node_Expression_Name || $node instanceof IfwPsn_Vendor_Twig_Node_Expression_GetAttr)) {
            $test = new IfwPsn_Vendor_Twig_Node_Expression_Test_Defined(clone $node, 'defined', new IfwPsn_Vendor_Twig_Node(), $node->getLine());
            $false = count($arguments) ? $arguments->getNode(0) : new IfwPsn_Vendor_Twig_Node_Expression_Constant('', $node->getLine());

            $node = new IfwPsn_Vendor_Twig_Node_Expression_Conditional($test, $default, $false, $node->getLine());
        } else {
            $node = $default;
        }

        parent::__construct($node, $filterName, $arguments, $lineno, $tag);
    }

    public function compile(IfwPsn_Vendor_Twig_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('node'));
    }
}
