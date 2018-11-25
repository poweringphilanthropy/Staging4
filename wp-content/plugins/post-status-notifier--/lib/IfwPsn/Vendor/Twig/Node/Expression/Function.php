<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class IfwPsn_Vendor_Twig_Node_Expression_Function extends IfwPsn_Vendor_Twig_Node_Expression_Call
{
    public function __construct($name, IfwPsn_Vendor_Twig_NodeInterface $arguments, $lineno)
    {
        parent::__construct(array('arguments' => $arguments), array('name' => $name), $lineno);
    }

    public function compile(IfwPsn_Vendor_Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $function = $compiler->getEnvironment()->getFunction($name);

        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'function');
        $this->setAttribute('thing', $function);
        $this->setAttribute('needs_environment', $function->needsEnvironment());
        $this->setAttribute('needs_context', $function->needsContext());
        $this->setAttribute('arguments', $function->getArguments());
        if ($function instanceof IfwPsn_Vendor_Twig_FunctionCallableInterface || $function instanceof IfwPsn_Vendor_Twig_SimpleFunction) {
            $this->setAttribute('callable', $function->getCallable());
        }

        $this->compileCallable($compiler);
    }
}
