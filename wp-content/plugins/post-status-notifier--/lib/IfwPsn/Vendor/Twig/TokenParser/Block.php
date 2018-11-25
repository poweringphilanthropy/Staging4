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
 * Marks a section of a template as being reusable.
 *
 * <pre>
 *  {% block head %}
 *    <link rel="stylesheet" href="style.css" />
 *    <title>{% block title %}{% endblock %} - My Webpage</title>
 *  {% endblock %}
 * </pre>
 */
class IfwPsn_Vendor_Twig_TokenParser_Block extends IfwPsn_Vendor_Twig_TokenParser
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
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(IfwPsn_Vendor_Twig_Token::NAME_TYPE)->getValue();
        if ($this->parser->hasBlock($name)) {
            throw new IfwPsn_Vendor_Twig_Error_Syntax(sprintf("The block '$name' has already been defined line %d", $this->parser->getBlock($name)->getLine()), $stream->getCurrent()->getLine(), $stream->getFilename());
        }
        $this->parser->setBlock($name, $block = new IfwPsn_Vendor_Twig_Node_Block($name, new IfwPsn_Vendor_Twig_Node(array()), $lineno));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);

        if ($stream->nextIf(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            if ($token = $stream->nextIf(IfwPsn_Vendor_Twig_Token::NAME_TYPE)) {
                $value = $token->getValue();

                if ($value != $name) {
                    throw new IfwPsn_Vendor_Twig_Error_Syntax(sprintf("Expected endblock for block '$name' (but %s given)", $value), $stream->getCurrent()->getLine(), $stream->getFilename());
                }
            }
        } else {
            $body = new IfwPsn_Vendor_Twig_Node(array(
                new IfwPsn_Vendor_Twig_Node_Print($this->parser->getExpressionParser()->parseExpression(), $lineno),
            ));
        }
        $stream->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);

        $block->setNode('body', $body);
        $this->parser->popBlockStack();
        $this->parser->popLocalScope();

        return new IfwPsn_Vendor_Twig_Node_BlockReference($name, $lineno, $this->getTag());
    }

    public function decideBlockEnd(IfwPsn_Vendor_Twig_Token $token)
    {
        return $token->test('endblock');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'block';
    }
}
