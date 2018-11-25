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
class IfwPsn_Vendor_Twig_Node_Expression_Filter extends IfwPsn_Vendor_Twig_Node_Expression_Call
{
    public function __construct(IfwPsn_Vendor_Twig_NodeInterface $node, IfwPsn_Vendor_Twig_Node_Expression_Constant $filterName, IfwPsn_Vendor_Twig_NodeInterface $arguments, $lineno, $tag = null)
    {
        parent::__construct(array('node' => $node, 'filter' => $filterName, 'arguments' => $arguments), array(), $lineno, $tag);
    }

    public function compile(IfwPsn_Vendor_Twig_Compiler $compiler)
    {
        $name = $this->getNode('filter')->getAttribute('value');
        $filter = $compiler->getEnvironment()->getFilter($name);

        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'filter');
        $this->setAttribute('thing', $filter);
        $this->setAttribute('needs_environment', $filter->needsEnvironment());
        $this->setAttribute('needs_context', $filter->needsContext());
        $this->setAttribute('arguments', $filter->getArguments());
        if ($filter instanceof IfwPsn_Vendor_Twig_FilterCallableInterface || $filter instanceof IfwPsn_Vendor_Twig_SimpleFilter) {
            $this->setAttribute('callable', $filter->getCallable());
        }

        $this->compileCallable($compiler);
    }
}
