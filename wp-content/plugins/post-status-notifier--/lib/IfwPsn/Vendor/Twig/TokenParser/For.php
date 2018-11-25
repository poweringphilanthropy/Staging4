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
 * Loops over each item of a sequence.
 *
 * <pre>
 * <ul>
 *  {% for user in users %}
 *    <li>{{ user.username|e }}</li>
 *  {% endfor %}
 * </ul>
 * </pre>
 */
class IfwPsn_Vendor_Twig_TokenParser_For extends IfwPsn_Vendor_Twig_TokenParser
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
        $targets = $this->parser->getExpressionParser()->parseAssignmentExpression();
        $stream->expect(IfwPsn_Vendor_Twig_Token::OPERATOR_TYPE, 'in');
        $seq = $this->parser->getExpressionParser()->parseExpression();

        $ifexpr = null;
        if ($stream->nextIf(IfwPsn_Vendor_Twig_Token::NAME_TYPE, 'if')) {
            $ifexpr = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideForFork'));
        if ($stream->next()->getValue() == 'else') {
            $stream->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);
            $else = $this->parser->subparse(array($this, 'decideForEnd'), true);
        } else {
            $else = null;
        }
        $stream->expect(IfwPsn_Vendor_Twig_Token::BLOCK_END_TYPE);

        if (count($targets) > 1) {
            $keyTarget = $targets->getNode(0);
            $keyTarget = new IfwPsn_Vendor_Twig_Node_Expression_AssignName($keyTarget->getAttribute('name'), $keyTarget->getLine());
            $valueTarget = $targets->getNode(1);
            $valueTarget = new IfwPsn_Vendor_Twig_Node_Expression_AssignName($valueTarget->getAttribute('name'), $valueTarget->getLine());
        } else {
            $keyTarget = new IfwPsn_Vendor_Twig_Node_Expression_AssignName('_key', $lineno);
            $valueTarget = $targets->getNode(0);
            $valueTarget = new IfwPsn_Vendor_Twig_Node_Expression_AssignName($valueTarget->getAttribute('name'), $valueTarget->getLine());
        }

        if ($ifexpr) {
            $this->checkLoopUsageCondition($stream, $ifexpr);
            $this->checkLoopUsageBody($stream, $body);
        }

        return new IfwPsn_Vendor_Twig_Node_For($keyTarget, $valueTarget, $seq, $ifexpr, $body, $else, $lineno, $this->getTag());
    }

    public function decideForFork(IfwPsn_Vendor_Twig_Token $token)
    {
        return $token->test(array('else', 'endfor'));
    }

    public function decideForEnd(IfwPsn_Vendor_Twig_Token $token)
    {
        return $token->test('endfor');
    }

    // the loop variable cannot be used in the condition
    protected function checkLoopUsageCondition(IfwPsn_Vendor_Twig_TokenStream $stream, IfwPsn_Vendor_Twig_NodeInterface $node)
    {
        if ($node instanceof IfwPsn_Vendor_Twig_Node_Expression_GetAttr && $node->getNode('node') instanceof IfwPsn_Vendor_Twig_Node_Expression_Name && 'loop' == $node->getNode('node')->getAttribute('name')) {
            throw new IfwPsn_Vendor_Twig_Error_Syntax('The "loop" variable cannot be used in a looping condition', $node->getLine(), $stream->getFilename());
        }

        foreach ($node as $n) {
            if (!$n) {
                continue;
            }

            $this->checkLoopUsageCondition($stream, $n);
        }
    }

    // check usage of non-defined loop-items
    // it does not catch all problems (for instance when a for is included into another or when the variable is used in an include)
    protected function checkLoopUsageBody(IfwPsn_Vendor_Twig_TokenStream $stream, IfwPsn_Vendor_Twig_NodeInterface $node)
    {
        if ($node instanceof IfwPsn_Vendor_Twig_Node_Expression_GetAttr && $node->getNode('node') instanceof IfwPsn_Vendor_Twig_Node_Expression_Name && 'loop' == $node->getNode('node')->getAttribute('name')) {
            $attribute = $node->getNode('attribute');
            if ($attribute instanceof IfwPsn_Vendor_Twig_Node_Expression_Constant && in_array($attribute->getAttribute('value'), array('length', 'revindex0', 'revindex', 'last'))) {
                throw new IfwPsn_Vendor_Twig_Error_Syntax(sprintf('The "loop.%s" variable is not defined when looping with a condition', $attribute->getAttribute('value')), $node->getLine(), $stream->getFilename());
            }
        }

        // should check for parent.loop.XXX usage
        if ($node instanceof IfwPsn_Vendor_Twig_Node_For) {
            return;
        }

        foreach ($node as $n) {
            if (!$n) {
                continue;
            }

            $this->checkLoopUsageBody($stream, $n);
        }
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'for';
    }
}
