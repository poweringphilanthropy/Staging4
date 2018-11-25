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
 * IfwPsn_Vendor_Twig_NodeTraverser is a node traverser.
 *
 * It visits all nodes and their children and calls the given visitor for each.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IfwPsn_Vendor_Twig_NodeTraverser
{
    protected $env;
    protected $visitors;

    /**
     * Constructor.
     *
     * @param IfwPsn_Vendor_Twig_Environment            $env      A IfwPsn_Vendor_Twig_Environment instance
     * @param IfwPsn_Vendor_Twig_NodeVisitorInterface[] $visitors An array of IfwPsn_Vendor_Twig_NodeVisitorInterface instances
     */
    public function __construct(IfwPsn_Vendor_Twig_Environment $env, array $visitors = array())
    {
        $this->env = $env;
        $this->visitors = array();
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    /**
     * Adds a visitor.
     *
     * @param IfwPsn_Vendor_Twig_NodeVisitorInterface $visitor A IfwPsn_Vendor_Twig_NodeVisitorInterface instance
     */
    public function addVisitor(IfwPsn_Vendor_Twig_NodeVisitorInterface $visitor)
    {
        if (!isset($this->visitors[$visitor->getPriority()])) {
            $this->visitors[$visitor->getPriority()] = array();
        }

        $this->visitors[$visitor->getPriority()][] = $visitor;
    }

    /**
     * Traverses a node and calls the registered visitors.
     *
     * @param IfwPsn_Vendor_Twig_NodeInterface $node A IfwPsn_Vendor_Twig_NodeInterface instance
     */
    public function traverse(IfwPsn_Vendor_Twig_NodeInterface $node)
    {
        ksort($this->visitors);
        foreach ($this->visitors as $visitors) {
            foreach ($visitors as $visitor) {
                $node = $this->traverseForVisitor($visitor, $node);
            }
        }

        return $node;
    }

    protected function traverseForVisitor(IfwPsn_Vendor_Twig_NodeVisitorInterface $visitor, IfwPsn_Vendor_Twig_NodeInterface $node = null)
    {
        if (null === $node) {
            return null;
        }

        $node = $visitor->enterNode($node, $this->env);

        foreach ($node as $k => $n) {
            if (false !== $n = $this->traverseForVisitor($visitor, $n)) {
                $node->setNode($k, $n);
            } else {
                $node->removeNode($k);
            }
        }

        return $visitor->leaveNode($node, $this->env);
    }
}
