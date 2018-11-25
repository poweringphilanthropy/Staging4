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
 * Marks a section of a template as untrusted code that must be evaluated in the sandbox mode.
 *
 * <pre>
 * {% sandbox %}
 *     {% include 'user.html' %}
 * {% endsandbox %}
 * </pre>
 *
 * @see http://www.twig-project.org/doc/api.html#sandbox-extension for details
 */
class IfwPsn_Vendor_Twig_TokenParser_Sandbox extends IfwPsn_Vendor_Twig_TokenParser
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
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $this->parser->getStream()->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);

        // in a sandbox tag, only include tags are allowed
        if (!$body instanceof IfwPsn_Vendor_Twig_Node_Include) {
            foreach ($body as $node) {
                if ($node instanceof IfwPsn_Vendor_Twig_Node_Text && ctype_space($node->getAttribute('data'))) {
                    continue;
                }

                if (!$node instanceof IfwPsn_Vendor_Twig_Node_Include) {
                    throw new IfwPsn_Vendor_Twig_Error_Syntax('Only "include" tags are allowed within a "sandbox" section', $node->getLine(), $this->parser->getFilename());
                }
            }
        }

        return new IfwPsn_Vendor_Twig_Node_Sandbox($body, $token->getLine(), $this->getTag());
    }

    public function decideBlockEnd(IfwPsn_Vendor_Twig_Token $token)
    {
        return $token->test('endsandbox');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'sandbox';
    }
}
