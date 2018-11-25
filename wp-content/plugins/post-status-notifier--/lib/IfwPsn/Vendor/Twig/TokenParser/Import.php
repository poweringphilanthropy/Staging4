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
 * Imports macros.
 *
 * <pre>
 *   {% import 'forms.html' as forms %}
 * </pre>
 */
class IfwPsn_Vendor_Twig_TokenParser_Import extends IfwPsn_Vendor_Twig_TokenParser
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
        $macro = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect('as');
        $var = new IfwPsn_Vendor_Twig_Node_Expression_AssignName($this->parser->getStream()->expect(IfwPsn_Vendor_Twig_Token::NAME_TYPE)->getValue(), $token->getLine());
        $this->parser->getStream()->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);

        $this->parser->addImportedSymbol('template', $var->getAttribute('name'));

        return new IfwPsn_Vendor_Twig_Node_Import($macro, $var, $token->getLine(), $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'import';
    }
}
