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
 * Checks if a variable is defined in the current context.
 *
 * <pre>
 * {# defined works with variable names and variable attributes #}
 * {% if foo is defined %}
 *     {# ... #}
 * {% endif %}
 * </pre>
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IfwPsn_Vendor_Twig_Node_Expression_Test_Defined extends IfwPsn_Vendor_Twig_Node_Expression_Test
{
    public function __construct(IfwPsn_Vendor_Twig_NodeInterface $node, $name, IfwPsn_Vendor_Twig_NodeInterface $arguments = null, $lineno)
    {
        parent::__construct($node, $name, $arguments, $lineno);

        if ($node instanceof IfwPsn_Vendor_Twig_Node_Expression_Name) {
            $node->setAttribute('is_defined_test', true);
        } elseif ($node instanceof IfwPsn_Vendor_Twig_Node_Expression_GetAttr) {
            $node->setAttribute('is_defined_test', true);

            $this->changeIgnoreStrictCheck($node);
        } else {
            throw new IfwPsn_Vendor_Twig_Error_Syntax('The "defined" test only works with simple variables', $this->getLine());
        }
    }

    protected function changeIgnoreStrictCheck(IfwPsn_Vendor_Twig_Node_Expression_GetAttr $node)
    {
        $node->setAttribute('ignore_strict_check', true);

        if ($node->getNode('node') instanceof IfwPsn_Vendor_Twig_Node_Expression_GetAttr) {
            $this->changeIgnoreStrictCheck($node->getNode('node'));
        }
    }

    public function compile(IfwPsn_Vendor_Twig_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('node'));
    }
}
