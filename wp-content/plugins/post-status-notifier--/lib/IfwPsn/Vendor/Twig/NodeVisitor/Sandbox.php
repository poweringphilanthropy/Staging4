<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * IfwPsn_Vendor_Twig_NodeVisitor_Sandbox implements sandboxing.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IfwPsn_Vendor_Twig_NodeVisitor_Sandbox implements IfwPsn_Vendor_Twig_NodeVisitorInterface
{
    protected $inAModule = false;
    protected $tags;
    protected $filters;
    protected $functions;

    /**
     * Called before child nodes are visited.
     *
     * @param IfwPsn_Vendor_Twig_NodeInterface $node The node to visit
     * @param IfwPsn_Vendor_Twig_Environment   $env  The Twig environment instance
     *
     * @return IfwPsn_Vendor_Twig_NodeInterface The modified node
     */
    public function enterNode(IfwPsn_Vendor_Twig_NodeInterface $node, IfwPsn_Vendor_Twig_Environment $env)
    {
        if ($node instanceof IfwPsn_Vendor_Twig_Node_Module) {
            $this->inAModule = true;
            $this->tags = array();
            $this->filters = array();
            $this->functions = array();

            return $node;
        } elseif ($this->inAModule) {
            // look for tags
            if ($node->getNodeTag()) {
                $this->tags[] = $node->getNodeTag();
            }

            // look for filters
            if ($node instanceof IfwPsn_Vendor_Twig_Node_Expression_Filter) {
                $this->filters[] = $node->getNode('filter')->getAttribute('value');
            }

            // look for functions
            if ($node instanceof IfwPsn_Vendor_Twig_Node_Expression_Function) {
                $this->functions[] = $node->getAttribute('name');
            }

            // wrap print to check __toString() calls
            if ($node instanceof IfwPsn_Vendor_Twig_Node_Print) {
                return new IfwPsn_Vendor_Twig_Node_SandboxedPrint($node->getNode('expr'), $node->getLine(), $node->getNodeTag());
            }
        }

        return $node;
    }

    /**
     * Called after child nodes are visited.
     *
     * @param IfwPsn_Vendor_Twig_NodeInterface $node The node to visit
     * @param IfwPsn_Vendor_Twig_Environment   $env  The Twig environment instance
     *
     * @return IfwPsn_Vendor_Twig_NodeInterface The modified node
     */
    public function leaveNode(IfwPsn_Vendor_Twig_NodeInterface $node, IfwPsn_Vendor_Twig_Environment $env)
    {
        if ($node instanceof IfwPsn_Vendor_Twig_Node_Module) {
            $this->inAModule = false;

            return new IfwPsn_Vendor_Twig_Node_SandboxedModule($node, array_unique($this->filters), array_unique($this->tags), array_unique($this->functions));
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }
}
