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
 * Filters a section of a template by applying filters.
 *
 * <pre>
 * {% filter upper %}
 *  This text becomes uppercase
 * {% endfilter %}
 * </pre>
 */
class IfwPsn_Vendor_Twig_TokenParser_Filter extends IfwPsn_Vendor_Twig_TokenParser
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
        $name = $this->parser->getVarName();
        $ref = new IfwPsn_Vendor_Twig_Node_Expression_BlockReference(new IfwPsn_Vendor_Twig_Node_Expression_Constant($name, $token->getLine()), true, $token->getLine(), $this->getTag());

        $filter = $this->parser->getExpressionParser()->parseFilterExpressionRaw($ref, $this->getTag());
        $this->parser->getStream()->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $this->parser->getStream()->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);

        $block = new IfwPsn_Vendor_Twig_Node_Block($name, $body, $token->getLine());
        $this->parser->setBlock($name, $block);

        return new IfwPsn_Vendor_Twig_Node_Print($filter, $token->getLine(), $this->getTag());
    }

    public function decideBlockEnd(IfwPsn_Vendor_Twig_Token $token)
    {
        return $token->test('endfilter');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'filter';
    }
}
