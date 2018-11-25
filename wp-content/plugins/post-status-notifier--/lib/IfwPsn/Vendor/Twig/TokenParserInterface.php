<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface implemented by token parsers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface IfwPsn_Vendor_Twig_TokenParserInterface
{
    /**
     * Sets the parser associated with this token parser
     *
     * @param $parser A IfwPsn_Vendor_Twig_Parser instance
     */
    public function setParser(IfwPsn_Vendor_Twig_Parser $parser);

    /**
     * Parses a token and returns a node.
     *
     * @param IfwPsn_Vendor_Twig_Token $token A IfwPsn_Vendor_Twig_Token instance
     *
     * @return IfwPsn_Vendor_Twig_NodeInterface A IfwPsn_Vendor_Twig_NodeInterface instance
     *
     * @throws IfwPsn_Vendor_Twig_Error_Syntax
     */
    public function parse(IfwPsn_Vendor_Twig_Token $token);

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag();
}
