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
 * Extends a template by another one.
 *
 * <pre>
 *  {% extends "base.html" %}
 * </pre>
 */
class IfwPsn_Vendor_Twig_TokenParser_Extends extends IfwPsn_Vendor_Twig_TokenParser
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
        if (!$this->parser->isMainScope()) {
            throw new IfwPsn_Vendor_Twig_Error_Syntax('Cannot extend from a block', $token->getLine(), $this->parser->getFilename());
        }

        if (null !== $this->parser->getParent()) {
            throw new IfwPsn_Vendor_Twig_Error_Syntax('Multiple extends tags are forbidden', $token->getLine(), $this->parser->getFilename());
        }
        $this->parser->setParent($this->parser->getExpressionParser()->parseExpression());

        $this->parser->getStream()->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'extends';
    }
}
