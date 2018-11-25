<?php

/*
 * This file is part of Twig.
 *
 * (c) 2012 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Embeds a template.
 */
class IfwPsn_Vendor_Twig_TokenParser_Embed extends IfwPsn_Vendor_Twig_TokenParser_Include
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
        $stream = $this->parser->getStream();

        $parent = $this->parser->getExpressionParser()->parseExpression();

        list($variables, $only, $ignoreMissing) = $this->parseArguments();

        // inject a fake parent to make the parent() function work
        $stream->injectTokens(array(
            new IfwPsn_Vendor_Twig_Token(IfwPsn_Vendor_Twig_Token::BLOCK_START_TYPE, '', $token->getLine()),
            new IfwPsn_Vendor_Twig_Token(IfwPsn_Vendor_Twig_Token::NAME_TYPE, 'extends', $token->getLine()),
            new IfwPsn_Vendor_Twig_Token(IfwPsn_Vendor_Twig_Token::STRING_TYPE, '__parent__', $token->getLine()),
            new IfwPsn_Vendor_Twig_Token(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE, '', $token->getLine()),
        ));

        $module = $this->parser->parse($stream, array($this, 'decideBlockEnd'), true);

        // override the parent with the correct one
        $module->setNode('parent', $parent);

        $this->parser->embedTemplate($module);

        $stream->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);

        return new IfwPsn_Vendor_Twig_Node_Embed($module->getAttribute('filename'), $module->getAttribute('index'), $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }

    public function decideBlockEnd(IfwPsn_Vendor_Twig_Token $token)
    {
        return $token->test('endembed');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'embed';
    }
}
