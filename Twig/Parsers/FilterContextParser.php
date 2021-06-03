<?php


namespace Parad0xe\Bundle\FilterBundle\Twig\Parsers;


use Parad0xe\Bundle\FilterBundle\Twig\Nodes\FilterContextNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class FilterContextParser extends AbstractTokenParser
{
    /**
     * @inheritDoc
     */
    public function parse(Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $stream->expect(Token::NAME_TYPE, 'for');
        $name = $this->parser->getExpressionParser()->parseExpression();
        $stream->expect(Token::BLOCK_END_TYPE);
        $stream->nextIf(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideCacheEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);

        return new FilterContextNode([
            "body" => $body
        ], [
            "name" => $name->getAttribute("name")
        ], $token->getLine(), $this->getTag());
    }

    /**
     * @inheritDoc
     */
    public function getTag()
    {
        return "filtercontext";
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    public function decideCacheEnd(Token $token)
    {
        return $token->test('endfiltercontext');
    }
}
