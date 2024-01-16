<?php

namespace Pis\Framework\Twig\Parsers;

class MinJsTokenParser extends \Twig\TokenParser\AbstractTokenParser
{

    const name = 'minjs';

    public function parse(\Twig\Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        //$name = $stream->expect(\Twig\Token::NAME_TYPE)->getValue();
        if (in_array(self::name, $this->parser->getBlockStack())) {
            throw new \Twig\Error\SyntaxError(sprintf("A block of this type was already opened in line %d", $this->parser->getBlock(self::name)->getLine()), $stream->getCurrent()->getLine(), $stream->getFilename());
        }
        $this->parser->setBlock(self::name, $block = new \Twig\Node\BlockNode(self::name, new \Twig\Node\Node(array()), $lineno));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack(self::name);

        if ($stream->nextIf(\Twig\Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            if ($body instanceof \Twig\Node\TextNode)
                $body->setAttribute('data', \JShrink\Minifier::minify($body->getAttribute('data'), array('flaggedComments' => false)));
            else
                throw new \Exception('Wrong Twig\Node Type: ' . get_class($body));
            if ($token = $stream->nextIf(\Twig\Token::NAME_TYPE)) {
                $value = $token->getValue();

                /*if ($value != $name) {
                    throw new \Twig\Error\SyntaxError(sprintf("Expected endblock for block '$name' (but %s given)", $value), $stream->getCurrent()->getLine(), $stream->getFilename());
                }*/
            }
        } else {
            $body = new \Twig\Node\Node(array(
                new \Twig\Node\PrintNode($this->parser->getExpressionParser()->parseExpression(), $lineno),
            ));
        }
        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        $block->setNode('body', $body);
        $this->parser->popBlockStack();
        $this->parser->popLocalScope();

        return new \Twig\Node\BlockReferenceNode(self::name, $lineno, $this->getTag());
    }

    public function decideBlockEnd(\Twig\Token $token)
    {
        return $token->test('end' . self::name);
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return self::name;
    }
}
