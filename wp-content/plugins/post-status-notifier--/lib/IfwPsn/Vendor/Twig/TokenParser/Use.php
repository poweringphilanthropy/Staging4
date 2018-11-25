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
 * Imports blocks defined in another template into the current template.
 *
 * <pre>
 * {% extends "base.html" %}
 *
 * {% use "blocks.html" %}
 *
 * {% block title %}{% endblock %}
 * {% block content %}{% endblock %}
 * </pre>
 *
 * @see http://www.twig-project.org/doc/templates.html#horizontal-reuse for details.
 */
class IfwPsn_Vendor_Twig_TokenParser_Use extends IfwPsn_Vendor_Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param IfwPsn_Vendor_Twig_Token $token A IfwPsn_Vendor_Twig_Token instance
     *
     * @return IfwPsn_Vendor_Twig_NodeInterface A IfwPsn_Vendor_Twig_NodeInterface instance
     */
    public function parse(IfwPsn_Vendor_Twig_Token $token)
    {
        $template = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();

        if (!$template instanceof IfwPsn_Vendor_Twig_Node_Expression_Constant) {
            throw new IfwPsn_Vendor_Twig_Error_Syntax('The template references in a "use" statement must be a string.', $stream->getCurrent()->getLine(), $stream->getFilename());
        }

        $targets = array();
        if ($stream->nextIf('with')) {
            do {
                $name = $stream->expect(IfwPsn_Vendor_Twig_Token::NAME_TYPE)->getValue();

                $alias = $name;
                if ($stream->nextIf('as')) {
                    $alias = $stream->expect(IfwPsn_Vendor_Twig_Token::NAME_TYPE)->getValue();
                }

                $targets[$name] = new IfwPsn_Vendor_Twig_Node_Expression_Constant($alias, -1);

                if (!$stream->nextIf(IfwPsn_Vendor_Twig_Token::PUNCTUATION_TYPE, ',')) {
                    break;
                }
            } while (true);
        }

        $stream->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);

        $this->parser->addTrait(new IfwPsn_Vendor_Twig_Node(array('template' => $template, 'targets' => new IfwPsn_Vendor_Twig_Node($targets))));
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'use';
    }
}
