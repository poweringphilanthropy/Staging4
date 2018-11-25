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
 * Flushes the output to the client.
 *
 * @see flush()
 */
class IfwPsn_Vendor_Twig_TokenParser_Flush extends IfwPsn_Vendor_Twig_TokenParser
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
        $this->parser->getStream()->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);

        return new IfwPsn_Vendor_Twig_Node_Flush($token->getLine(), $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'flush';
    }
}
